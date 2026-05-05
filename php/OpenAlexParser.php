<?php

require_once 'DB.php';
require_once 'FullNameParser.php';
require_once 'Settings.php';

// use Diophila\OpenAlex;
// use HumanNameParser\Parser as HumanName;
// use Nlevenshtein\Levenshtein;

class OpenAlexParser
{
    public const TYPES = [
        "book-section" => "chapter",
        "monograph" => "book",
        "report-component" => "others",
        "report" => "others",
        "peer-review" => "others",
        "book-track" => "book",
        "journal-article" => "article",
        "article" => "article",
        "book-part" => "book",
        "other" => "others",
        "book" => "book",
        "journal-volume" => "article",
        "book-set" => "book",
        "reference-entry" => "others",
        "proceedings-article" => "others",
        "journal" => "others",
        "component" => "others",
        "book-chapter" => "chapter",
        "proceedings-series" => "others",
        "report-series" => "others",
        "proceedings" => "others",
        "database" => "others",
        "standard" => "others",
        "reference-book" => "book",
        "posted-content" => "others",
        "journal-issue" => "others",
        "dissertation" => "dissertation",
        "grant" => "others",
        "dataset" => "others",
        "book-series" => "book",
        "edited-book" => "book",
    ];
    private $inst_id;
    private $startyear;
    private $osiris;

    private $base_url = "https://api.openalex.org/";
    private $work_url = "works";
    private $source_url = "sources/";

    private $mail;
    private $NameParser;

    public function __construct($mail)
    {

        $this->mail = $mail;
        $DB = new DB();
        $this->osiris = $DB->db;

        $this->NameParser = new FullNameParser();

        $Settings = new Settings();
        $affiliation = $Settings->get('affiliation_details');

        $this->inst_id = $affiliation['openalex'] ?? null;
        if (!$this->inst_id) {
            throw new Exception("OpenAlex ID is missing.");
        }
        $this->startyear = $Settings->get('startyear');
    }

    private function getUserId($name, $orcid = null)
    {
        if ($orcid) {
            $user = $this->osiris->persons->findOne(['orcid' => $orcid]);
            if ($user) {
                return $user->_id;
            }
        }
        $user = $this->osiris->persons->findOne([
            'last' => $name['lname'],
            'first' => ['$regex' => '^' . $name['fname'] . '.*']
        ]);
        if ($user) {
            return $user->_id;
        }
        return null;
    }

    private function getAbstract($inverted_abstract)
    {
        if (!$inverted_abstract) return null;

        $abstract = [];
        foreach ($inverted_abstract as $word => $occurrence) {
            foreach ($occurrence as $oc) {
                $abstract[] = [$oc, $word];
            }
        }
        usort($abstract, function ($a, $b) {
            return $a[0] - $b[0];
        });
        return implode(" ", array_map(function ($i) {
            return $i[1];
        }, $abstract));
    }

    private function getJournal($issn)
    {
        $journal = $this->osiris->journals->findOne(['issn' => ['$in' => $issn]]);
        if ($journal) {
            return $journal;
        }

        $source = $this->get_single_source(end($issn), "issn");
        if (!$source) {
            return null;
        }
        if ($source['type'] != 'journal') {
            return [
                'magazine' => $source['display_name'],
                'publisher' => $source['host_organization_name'],
                'issn' => $source['issn'],
                'oa' => $source['is_oa'],
                // 'openalex' => str_replace('https://openalex.org/', '', $source['id'])
            ];
        }

        $new_journal = [
            'journal' => $source['display_name'],
            'abbr' => $source['abbreviated_title'],
            'publisher' => $source['host_organization_name'],
            'issn' => $source['issn'],
            'oa' => $source['is_oa'],
            'openalex' => str_replace('https://openalex.org/', '', $source['id'])
        ];
        $new_doc = $this->osiris->journals->insertOne($new_journal);
        $new_journal['_id'] = $new_doc->getInsertedId();
        return $new_journal;
    }

    public function parseWork($work)
    {
        // $result = ['msg' => '', 'status' => 'signal', 'link' => '', 'data' => []];

        if (!$work['doi'] || strpos($work['doi'], 'https://doi.org/') === false) {
            return ['title'=> $work['title'], 'msg' => 'DOI is missing or invalid.', 'status' => 'danger', 'link' => $work['id']];
        }

        if ($work['is_retracted']) {
            return ['title'=> $work['title'], 'msg' => 'Activity is retracted and was omitted.', 'status' => 'danger', 'link' => $work['doi']];
        }

        $pubmed = isset($work['ids']['pmid']) ? str_replace('https://pubmed.ncbi.nlm.nih.gov/', '', $work['ids']['pmid']) : null;
        $doi = str_replace('https://doi.org/', '', $work['doi']);

        if ($doi && $this->osiris->activities->countDocuments(['doi' => $doi]) > 0) {
            return ['title'=> $work['title'], 'msg' => "DOI exists and was omitted.", 'status' => 'danger', 'link' => $work['doi']];
        }
        if ($pubmed && $this->osiris->activities->countDocuments(['pubmed' => $pubmed]) > 0) {
            return ['title'=> $work['title'], 'msg' => "PubMed ID exists and was omitted.", 'status' => 'danger', 'link' => $work['ids']['pmid']];
        }

        // if ($this->osiris->queue->countDocuments(['doi' => $doi]) > 0) {
        //     return ['msg'=> "DOI is already in the queue.", 'status' => 'danger', 'link' => $work['doi']];
        // }
        if (!isset(OpenAlexParser::TYPES[$work['type']])) {
            if (isset(OpenAlexParser::TYPES[$work['type_crossref']])) {
                $work['type'] = $work['type_crossref'];
            } else {
                return ['title'=> $work['title'], 'msg' => "Activity type {$work['type']} is unknown.", 'status' => 'danger', 'link' => $work['doi']];
            }
        }

        $type = OpenAlexParser::TYPES[$work['type']];

        $authors = [];
        foreach ($work['authorships'] as $a) {
            $name = $this->NameParser->parse_name($a['author']['display_name']);
            $orcid = isset($a['author']['orcid']) ? str_replace('https://orcid.org/', '', $a['author']['orcid']) : null;
            $user = $this->getUserId($name, $orcid);
            $pos = $a['author_position'];
            if ($pos == 'middle' && isset($a['is_corresponding'])) {
                $pos = 'corresponding';
            }

            $authors[] = [
                'last' => $name['lname'],
                'first' => $name['fname'] . ($name['initials'] ? ' ' . $name['initials'] : ''),
                'position' => $pos,
                'aoi' => in_array('https://openalex.org/' . $this->inst_id, array_column($a['institutions'], 'id')),
                'orcid' => $orcid,
                'user' => $user,
                'approved' => false
            ];
        }

        $pages = null;
        if (!empty($work['biblio']['first_page'])) {
            $pages = $work['biblio']['first_page'];
            if (!empty($work['biblio']['last_page']) && $work['biblio']['last_page'] != $pages) {
                $pages .= '-' . $work['biblio']['last_page'];
            }
        }

        $loc = $work['primary_location']['source'];

        $date = explode('-', $work['publication_date']);
        $month = isset($date[1]) ? intval($date[1]) : null;
        $day = isset($date[2]) ? intval($date[2]) : null;

        $abstract = $this->getAbstract($work['abstract_inverted_index']);
        $element = [
            'doi' => $doi,
            'type' => 'publication',
            'subtype' => $type,
            'title' => $work['title'],
            'year' => $work['publication_year'],
            'abstract' => $abstract,
            'month' => $month,
            'day' => $day,
            'authors' => $authors,
            'pages' => $pages,
            'openalex_id' => str_replace('https://openalex.org/', '', $work['id']),
            'pubmed' => $pubmed,
            'open_access' => $work['open_access']['is_oa'],
            'oa_status' => $work['open_access']['oa_status'],
            'correction' => false,
            'epub' => false
        ];
        if ($type == 'others') {
            $element['doc_type'] = ucfirst($work['type']);
        }

        if ($type == 'article') {
            if (!$loc || empty($loc['issn'])) {
                $element['subtype'] = 'magazine';
                $element['magazine'] = $loc ? $loc['display_name'] : null;
            } else {
                $journal = $this->getJournal($loc['issn']);
                if (isset($journal['magazine'])) {
                    $element['subtype'] = 'magazine';
                    $element = array_merge($element, $journal);
                } else {
                    $element = array_merge($element, [
                        'volume' => $work['biblio']['volume'],
                        'issue' => $work['biblio']['issue'],
                        'journal' => $journal['journal'],
                        'journal_id' => (string) $journal['_id']
                    ]);
                    if (empty($element['volume']) && empty($element['issue'])) {
                        $element['epub'] = true;
                    }
                }
            }
        }

        if ($type == 'chapter') {
            $element['book'] = $loc['display_name'];
        }

        $element['history'] = [
            ['date' => date('Y-m-d'), 'type' => 'imported', 'user' => $_SESSION['username']]
        ];

        $add = $this->osiris->activities->insertOne($element);
        $id = $add->getInsertedId();

        return [
            'msg' => 'Activity was added to OSIRIS.',
            'status' => 'success',
            'link' => $work['doi'],
            'title' => $work['title'],
            'id' => $id,
            'data' => $element
        ];
    }

    public function get_work($id, $idtype = 'doi', $ignoreDupl = true)
    {
        $work = $this->get_single_work($id, $idtype);
        return $this->parseWork($work);
    }

    public function get_works_from_author($id)
    {
        $filters = [
            'author.id:' . $id,
        ];

        $works = $this->get_list_of_works($filters);

        $i = 0;
        foreach ($works['results'] as $work) {
            $element = $this->parseWork($work);
            if ($element === false) continue;
            $i++;
            yield $element;
        }
    }


    private function callAPI($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // important for redirects, e.g. issn search of source
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: mailto:' . $this->mail]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function get_single_source($id, $idtype = null)
    {
        $filter = ($idtype !== null) ? $idtype . ':' . $id : $id;
        $url = $this->base_url . $this->source_url . $filter;
        return $this->callAPI($url);
    }
    public function get_single_work($id, $idtype = null)
    {
        $filter = ($idtype !== null) ? $idtype . ':' . $id : $id;
        $url = $this->base_url . $this->work_url . $filter;
        return $this->callAPI($url);
    }
    public function get_list_of_works($filters)
    {
        $url = $this->base_url . $this->work_url . '?filter=' . implode(',', $filters) . '&per-page=200';
        return $this->callAPI($url);
    }
}
//'https://api.openalex.org/works?filter=author.id:A5022180345'
