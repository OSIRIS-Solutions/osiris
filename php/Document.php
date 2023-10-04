<?php
require_once "DB.php";
require_once "Settings.php";
require_once "Schema.php";
require_once "Country.php";

class Document extends Settings
{

    public $doc = array();
    public $type = "";
    public $subtype = "";
    public $typeArr = array();
    public $subtypeArr = array();

    private $highlight = true;
    private $appendix = '';

    private $modules = array();

    public $title = "";
    public $subtitle = "";
    public $usecase = "web";
    public $full = false;

    private $schemaType = null;
    public $schema = [];
    private $db =null;


    function __construct($highlight = true, $usecase = 'web')
    {
        parent::__construct();
        $this->highlight = $highlight;
        $this->usecase = $usecase;
        $this->db = new DB;
    }

    public function setDocument($doc)
    {
        if (!is_array($doc)) {
            $doc = iterator_to_array($doc);
        }
        // dump(($doc));
        $this->doc = $doc;
        $this->getActivityType();
        $this->initSchema();

        $this->modules = [];
        foreach ($this->subtypeArr['modules'] as $m) {
            $this->modules[] = str_replace('*', '', $m);
        }
    }

    public function schema()
    {
        if (!$this->hasSchema()) return "";
        $this->schema = [
            "@context" => "https://schema.org",
            "@graph" => []
        ];

        // shorten the expressions
        $d = $this->doc;

        $main = [
            "@id" => "#record",
            "@type" => $this->schemaType,
            "name" => $d['title'],
            "author" => Schema::authors($d['authors']),
            "datePublished" => Schema::date($d),
            "identifier" => [],
        ];
        if (isset($d['doi']))
            $main['identifier'][] = Schema::identifier("DOI", $d['doi']);
        if (isset($d['pubmed']))
            $main['identifier'][] = Schema::identifier("Pubmed-ID", $d['pubmed']);
        if (isset($d['isbn']))
            $main['identifier'][] = Schema::identifier("ISBN", $d['isbn']);


        switch ($this->schemaType) {
            case 'ScholarlyArticle':
                if (isset($d['pages']))
                    $main['pagination'] = $d['pages'];

                $main["isPartOf"] = [];
                if (isset($d['issue'])) {
                    $main['isPartOf'][] = ['@id' => '#issue'];
                    $issue = Schema::issue($d['issue']);
                    if (isset($d['volume'])) {
                        $issue['isPartOf'] = ['@id' => '#volume'];
                    }
                    $this->schema['@graph'][] = $issue;
                }

                if (isset($d['volume'])) {
                    $main['isPartOf'][] = ['@id' => '#volume'];
                    $volume = [
                        "@id" => "#volume",
                        "@type" => "PublicationVolume",
                        "volumeNumber" => $d['volume'],
                        "datePublished" => Schema::date($d),
                    ];
                    $this->schema['@graph'][] = $volume;
                }

                if (isset($d['journal_id'])) {
                    $j = $this->db->getConnected('journal', $d['journal_id']);
                    if (!empty($j)) {
                        $journal = Schema::journal($j);
                        if (!empty($d['volume'])) {
                            $journal['hasPart'] = ['@id' => "#volume"];
                        }
                        $this->schema['@graph'][] = $journal;
                        $main['isPartOf'][] = ['@id' => '#journal'];
                    }
                }
                break;

            case "Thesis":
                $main['sourceOrganization'] = Schema::organisation($d['publisher'], $d['city'] ?? null);
                break;

            case "Book":
                if (isset($d['isbn']))
                    $main['isbn'] = $d['isbn'];
                if (isset($d['pages']))
                    $main['numberOfPages'] = $d['pages'];
                if (isset($d['publisher']))
                    $main['publisher'] = Schema::organisation($d['publisher'], $d['city'] ?? null);
                break;

            case "Chapter":
                $book = [
                    "@id" => "#book",
                    "@type" => 'Book',
                    "name" => $d['book'] ?? null
                ];
                if (isset($d['editors'])) {
                    $book['editor'] = Schema::authors($d['editors']);
                }

                if (isset($d['isbn']))
                    $book['isbn'] = $d['isbn'];
                if (isset($d['pages']))
                    $book['numberOfPages'] = $d['pages'];
                if (isset($d['publisher']))
                    $book['publisher'] = Schema::organisation($d['publisher'], $d['city'] ?? null);
                $this->schema['@graph'][] = $book;

                $main['isPartOf'] = "#book";
                if (isset($d['pages']))
                    $main['pagination'] = $d['pages'];
                break;

            case "Poster":
            case "PresentationDigitalDocument":
                $event = Schema::event($d);
                $event['@id'] = '#conference';
                $this->schema['@graph'][] = $event;
                $main['releasedEvent'] = ['@id' => '#conference'];
                break;

            default:
                break;
        }
        $this->schema['@graph'][] = $main;

        $s = '<script type="application/ld+json">';
        $s .= json_encode($this->schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $s .= '</script>';
        return $s;
    }

    public function hasSchema()
    {
        return $this->schemaType !== null;
    }

    private function initSchema()
    {
        $this->schemaType = null;
        switch ($this->type) {
            case 'publication':
                switch ($this->subtype) {
                    case 'article':
                    case 'magazine':
                    case 'preprint':
                        $this->schemaType = "ScholarlyArticle";
                        return;
                    case 'thesis':
                        $this->schemaType = "Thesis";
                        return;
                    case 'book':
                        $this->schemaType = "Book";
                        return;
                    case 'chapter':
                        $this->schemaType = "Chapter";
                        return;
                    default:
                        return;
                }
            case 'poster':
                $this->schemaType = "Poster";
                return;
            case 'lecture':
                $this->schemaType = "PresentationDigitalDocument";
                return;
                // case 'project':
                //     $this->schemaType = "ResearchProject";
                //     return;

            default:
                break;
        }
    }

    public function print_schema()
    {
        echo '<script type="application/ld+json">';
        echo json_encode($this->schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        echo '</script>';
    }

    function activity_icon($tooltip = true)
    {
        $icon = 'placeholder';

        if (!empty($this->subtypeArr) && isset($this->subtypeArr['icon'])) {
            $icon = $this->subtypeArr['icon'];
        } elseif (!empty($this->typeArr) && isset($this->typeArr['icon'])) {
            $icon = $this->typeArr['icon'];
        }
        if (empty($this->typeArr)) {
            return "<i class='ph text-danger ph-warning'></i>";
        }
        $type = $this->typeArr['id'];
        $icon = "<i class='ph text-$type ph-$icon'></i>";
        if ($tooltip) {
            $name = $this->activity_title();
            return "<span data-toggle='tooltip' data-title='$name'>$icon</span>";
        }

        return $icon;
    }


    function activity_title()
    {
        $name = lang("Other", "Sonstiges");
        if (!empty($this->subtypeArr) && isset($this->subtypeArr['name'])) {
            $name = lang(
                $this->subtypeArr['name'],
                $this->subtypeArr['name_de'] ?? $this->subtypeArr['name']
            );
        } elseif (!empty($this->typeArr) && isset($this->typeArr['name'])) {
            $name = lang(
                $this->typeArr['name'],
                $this->typeArr['name_de'] ?? $this->typeArr['name']
            );
        } else {
            return "ERROR: doc is not defined!";
        }
        return $name;
    }

    private function getActivityType()
    {
        if (is_string($this->doc)) {
            $type = strtolower(trim($this->doc));
        } else {
            $type = strtolower(trim($this->doc['type'] ?? $this->doc['subtype'] ?? ''));
        }
        $this->type = $type;
        $this->typeArr = $this->get('activities')[$type];

        if (!isset($this->get('activities')[$type])) return;
        if (!isset($this->doc['subtype'])) {

            $subtype = $type;
            switch ($type) {
                case 'publication':
                    $subtype = strtolower(trim($this->doc['pubtype'] ?? $type));
                    switch ($subtype) {
                        case 'journal article':
                        case 'journal-article':
                            $subtype = "article";
                            break 2;
                        case 'magazine article':
                            $subtype = "magazine";
                            break 2;
                        case 'book-chapter':
                        case 'book chapter':
                        case 'book-editor':
                        case 'publication':
                            $subtype = "chapter";
                            break 2;
                        default:
                            break 2;
                    }
                case 'review':
                    switch (strtolower($this->doc['role'] ?? '')) {
                        case 'editorial':
                        case 'editor':
                            $subtype = "editorial";
                            break 2;
                        case 'grant-rev':
                            $subtype = "grant-rev";
                            break 2;
                        case 'thesis-rev':
                            $subtype = "thesis-rev";
                            break 2;
                        default:
                            $subtype = "review";
                            break 2;
                    }

                case 'misc':
                    $subtype = $this->doc['iteration'] ?? '';
                    if ($subtype == 'once' || $subtype == 'annual') {
                        $subtype = "misc-" . $subtype;
                    }
                    break;
                case 'students':
                    $cat = strtolower(trim($this->doc['category'] ?? 'thesis'));
                    if (str_contains($cat, "thesis") || $cat == 'doktorand:in' || $cat == 'students') {
                        $subtype = "students";
                        break;
                    }
                    $subtype = "guests";
                    break;
                default:
                    break;
            }
        } else {
            $subtype = $this->doc['subtype'];
        }

        $this->subtype = $subtype;
        if (isset($this->typeArr['subtypes'][$subtype])) {
            $this->subtypeArr = $this->typeArr['subtypes'][$subtype];
            return;
        }
        foreach ($this->typeArr['subtypes'] as $st) {
            if ($st['id'] == $subtype) {
                $this->subtypeArr = $st;
                return;
            }
        }
    }

    function activity_badge()
    {
        $name = $this->activity_title();
        $icon = $this->activity_icon(false);
        $type = $this->doc['type'];
        return "<span class='badge badge-$type'>$icon $name</span>";
    }


    private static function commalist($array, $sep = "and")
    {
        if (empty($array)) return "";
        if (count($array) < 3) return implode(" $sep ", $array);
        $str = implode(", ", array_slice($array, 0, -1));
        return $str . " $sep " . end($array);
    }

    public static function abbreviateAuthor($last, $first, $reverse = true, $space = '&nbsp;')
    {
        $fn = "";
        if ($first) :
            foreach (preg_split("/(\s+| |-|\.)/u", $first, -1, PREG_SPLIT_DELIM_CAPTURE) as $name) {
                if (empty($name) || $name == '.' || $name == ' ') continue;
                if ($name == '-')
                    $fn .= '-';
                else
                    $fn .= "" . mb_substr($name, 0, 1) . ".";
            }
        endif;
        if (empty(trim($fn))) return $last;
        if ($reverse) return $last . "," . $space . $fn;
        return $fn . $space . $last;
    }

    function formatAuthors($raw_authors)
    {
        $this->appendix = '';
        if (empty($raw_authors)) return '';
        $n = 0;
        $authors = array();

        $first = 1;
        $last = 1;
        $corresponding = false;

        if (!empty($raw_authors) && $raw_authors instanceof MongoDB\Model\BSONArray) {
            $raw_authors = $raw_authors->bsonSerialize();
        }
        if (!empty($raw_authors) && is_array($raw_authors)) {
            $pos = array_count_values(array_column($raw_authors, 'position'));
            // dump($pos);
            $first = $pos['first'] ?? 1;
            $last = $pos['last'] ?? 1;
            $corresponding = array_key_exists('corresponding', $pos);
        }
        foreach ($raw_authors as $n => $a) {

            if (!$this->full) {
                if ($n > 9) break;
                $author = Document::abbreviateAuthor($a['last'], $a['first']);
                if ($this->highlight === true) {
                    //if (($a['aoi'] ?? 0) == 1) $author = "<b>$author</b>";
                } else if ($this->highlight && $a['user'] == $this->highlight) {
                    $author = "<u>$author</u>";
                }
                $authors[] = $author;
            } else {

                $author = Document::abbreviateAuthor($a['last'], $a['first']);

                if ($this->highlight === true) {
                    if (($a['aoi'] ?? 0) == 1) $author = "<b>$author</b>";
                } else if ($this->highlight && $a['user'] == $this->highlight) {
                    $author = "<b>$author</b>";
                }

                if ($first > 1 && $a['position'] == 'first') {
                    $author .= "<sup>#</sup>";
                }
                if ($last > 1 && $a['position'] == 'last') {
                    $author .= "<sup>*</sup>";
                }
                if (isset($a['position']) && $a['position'] == 'corresponding') {
                    $author .= "<sup>§</sup>";
                    $corresponding = true;
                }

                $authors[] = $author;
            }
        }

        if ($first > 1) {
            if ($this->typeArr['id'] == 'poster' || $this->typeArr['id'] == 'lecture')
                $this->appendix .= " <sup>#</sup> Presenting authors";
            else
                $this->appendix .= " <sup>#</sup> Shared first authors";
        }
        if ($last > 1) {
            $this->appendix .= " <sup>*</sup> Shared last authors";
        }
        if ($corresponding) {
            $this->appendix .= " <sup>§</sup> Corresponding author";
        }

        $append = "";
        $separator = 'and';
        if (!$this->full && $n > 9) {
            $append = " et al.";
            $separator = ", ";
        }
        return Document::commalist($authors, $separator) . $append;
    }


    private static function getDateTime($date)
    {
        if (isset($date['year'])) {
            //date instanceof MongoDB\Model\BSONDocument
            $d = new DateTime();
            $d->setDate(
                $date['year'],
                $date['month'] ?? 1,
                $date['day'] ?? 1
            );
        } else {
            try {
                $d = date_create($date);
            } catch (TypeError $th) {
                $d = null;
            }
        }
        return $d;
    }

    public static function format_date($date)
    {
        // dump($date);
        $d = Document::getDateTime($date);
        return date_format($d, "d.m.Y");
    }

    private static function fromToDate($from, $to)
    {
        if (empty($to) || $from == $to) {
            return Document::format_date($from);
        }
        // $to = date_create($to);
        $from = Document::format_date($from);
        $to = Document::format_date($to);

        $f = explode('.', $from, 3);
        $t = explode('.', $to, 3);

        $from = $f[0] . ".";
        if ($f[1] != $t[1] || $f[2] != $t[2]) {
            $from .= $f[1] . ".";
        }
        if ($f[2] != $t[2]) {
            $from .= $f[2];
        }

        return $from . '-' . $to;
    }


    public static function format_month($month)
    {
        if (empty($month)) return '';
        $month = intval($month);
        $array = [
            1 => lang("January", "Januar"),
            2 => lang("February", "Februar"),
            3 => lang("March", "März"),
            4 => lang("April"),
            5 => lang("May", "Mai"),
            6 => lang("June", "Juni"),
            7 => lang("July", "Juli"),
            8 => lang("August"),
            9 => lang("September"),
            10 => lang("October", "Oktober"),
            11 => lang("November"),
            12 => lang("December", "Dezember")
        ];
        return $array[$month];
    }


    function getUserAuthor($authors, $user)
    {
        if ($authors instanceof MongoDB\Model\BSONArray) {
            $authors = $authors->bsonSerialize();
        }
        $author = array_filter($authors, function ($author) use ($user) {
            return $author['user'] == $user;
        });
        if (empty($author)) return array();
        return reset($author);
    }

    function is_approved($user)
    {
        if (!isset($this->doc['authors'])) return true;
        $authors = $this->doc['authors'];
        if (isset($this->doc['editors'])) {
            $editors = $this->doc['editors'];
            if (!is_array($authors)) {
                $authors = $authors->bsonSerialize();
            }
            if (!is_array($editors)) {
                $editors = $editors->bsonSerialize();
            }
            $authors = array_merge($authors, $editors);
        }
        return $this->getUserAuthor($authors, $user)['approved'] ?? false;
    }


    function has_issues($user = null)
    {
        if ($user === null) $user = $_SESSION['username'];
        $issues = array();
        $type = $this->typeArr['id'];
        $subtypeArr = $this->subtypeArr['id'];

        if (!$this->is_approved($user)) $issues[] = "approval";

        // check EPUB issue
        $epub = ($this->doc['epub'] ?? false);
        // set epub to false if user has delayed the warning
        if ($epub && isset($this->doc['epub-delay'])) {
            if (new DateTime() < new DateTime($this->doc['epub-delay'])) {
                $epub = false;
            }
        }
        if ($epub) $issues[] = "epub";

        // CHECK student status issue
        if ($type == "students" && isset($this->doc['status']) && $this->doc['status'] == 'in progress' && new DateTime() > getDateTime($this->doc['end'])) $issues[] = "students";

        // check ongoing reminder
        if (in_array('date-range-ongoing', $this->modules) && is_null($this->doc['end'])) {
            if (!isset($this->doc['end-delay']) || (new DateTime() > new DateTime($this->doc['end-delay']))) {
                $issues[] = "openend";
            }
        }

        // check if journal is standardized
        if (isset($this->doc['journal']) && (!isset($this->doc['journal_id']) || empty($this->doc['journal_id']))) {
            $issues[] = 'journal_id';
        }

        return $issues;
    }

    public static function translateCategory($cat)
    {
        switch ($cat) {
            case 'doctoral thesis':
                return "Doktorand:in";
            case 'master thesis':
                return "Master-Thesis";
            case 'bachelor thesis':
                return "Bachelor-Thesis";
            case 'guest scientist':
                return "Gastwissenschaftler:in";
            case 'lecture internship':
                return "Pflichtpraktikum im Rahmen des Studium";
            case 'student internship':
                return "Schülerpraktikum";
            case 'lecture':
                return lang('Lecture', 'Vorlesung');
            case 'practical':
                return lang('Practical course', 'Praktikum');
            case 'practical-lecture':
                return lang('Lecture and practical course', 'Vorlesung und Praktikum');
            case 'lecture-seminar':
                return lang('Lecture and seminar', 'Vorlesung und Seminar');
            case 'practical-seminar':
                return lang('Practical course and seminar', 'Praktikum und Seminar');
            case 'lecture-practical-seminar':
                return lang('Lecture, seminar, practical course', 'Vorlesung, Seminar und Praktikum');
            case 'seminar':
                return lang('Seminar');
            case 'other':
                return lang('Other', 'Sonstiges');
            default:
                return $cat;
        }
    }

    private function getVal($field, $default = '')
    {
        return $this->doc[$field] ?? $default;
    }

    public function get_field($module)
    {
        switch ($module) {
            case "affiliation": // ["book"],
                return $this->getVal('affiliation');
            case "authors": // ["authors"],
                return $this->formatAuthors($this->getVal('authors'));
            case "book-series": // ["series"],
                return $this->getVal('series');
            case "book-title": // ["book"],
                return $this->getVal('book');
            case "category": // ["category"],
                return $this->translateCategory($this->getVal('category'));
            case "city": // ["city"],
                return $this->getVal('city');
            case "conference": // ["conference"],
                return $this->getVal('conference');
            case "correction": // ["correction"],
                $val = $this->getVal('correction', false);
                if ($this->usecase == 'list')
                    return bool_icon($val);
                if ($val)
                    return "<span style='color:#B61F29;'>[Correction]</span>";
                else return '';
            case "date": // ["year", "month", "day"],
            case "date-range": // ["start", "end"],
                // return $this->fromToDate($this->getVal('start'), $this->getVal('end') ?? null);
                return $this->fromToDate($this->getVal('start', $this->doc), $this->getVal('end', null));
            case "date-range-ongoing":
                if (!empty($this->doc['start'])) {
                    if (!empty($this->doc['end'])) {
                        $date = lang("from ", "von ");
                        $date .= Document::format_month($this->doc['start']['month']) . ' ' . $this->doc['start']['year'];
                        $date .= lang(" until ", " bis ");
                        $date .= Document::format_month($this->doc['end']['month']) . ' ' . $this->doc['end']['year'];
                    } else {
                        $date = lang("since ", "seit ");
                        $date .= Document::format_month($this->doc['start']['month']) . ' ' . $this->doc['start']['year'];
                    }
                }
                return $date;
            case "year": // ["year", "month", "day"],
                return $this->getVal('year');
            case "month": // ["year", "month", "day"],
                return Document::format_month($this->getVal('month'));
            case "details": // ["details"],
                return $this->getVal('details');
            case "doctype": // ["doc_type"],
                return $this->getVal('doc_type');
            case "doi": // ["doi"],
                $val = $this->getVal('doi');
                if (empty($val)) return '';
                return "DOI: <a target='_blank' href='https://doi.org/$val'>$val</a>";
            case "edition": // ["edition"],
                $val = $this->getVal('edition');
                if ($val == 1) $val .= "st";
                elseif ($val == 2) $val .= "nd";
                elseif ($val == 3) $val .= "rd";
                else $val .= "th";
                return $val;
            case "editor": // ["editors"],
                return $this->formatAuthors($this->getVal('editors'));
            case "editorial": // ["editor_type"],
                return $this->getVal('editor_type');
            case "file-icons":
                $files = '';
                foreach ($this->getVal('files', array()) as $file) {
                    $icon = getFileIcon($file['filetype']);
                    $files .= " <a href='$file[filepath]' target='_blank' data-toggle='tooltip' data-title='$file[filetype]: $file[filename]' class='file-link'><i class='ph ph-file ph-$icon'></i></a>";
                }
                return $files;
            case "guest": // ["category"],
                return $this->translateCategory($this->getVal('category'));
            case "isbn": // ["isbn"],
                return $this->getVal('isbn');
            case "issn": // ["issn"],
                $issn = $this->getVal('issn');
                if (empty($issn)) return '';
                return implode(', ', DB::doc2Arr($issn));
            case "issue": // ["issue"],
                return $this->getVal('issue');
            case "iteration": // ["iteration"],
                return $this->getVal('iteration');
            case "journal": // ["journal", "journal_id"],
                $val = $this->doc['journal_id'] ?? '';
                if (!empty($val)) {
                    $j = $this->db->getConnected('journal', $this->getVal('journal_id'));
                    return $j['journal'];
                }
                return $this->getVal('journal');
            case "journal-abbr":
                $val = $this->doc['journal_id'] ?? '';
                if (!empty($val)) {
                    $j = $this->db->getConnected('journal', $this->getVal('journal_id'));
                    return $j['abbr'];
                }
                return $this->getVal('journal');
            case "lecture-invited": // ["invited_lecture"],
                $val = $this->getVal('invited_lecture', false);
                if ($this->usecase == 'list')
                    return bool_icon($val);
                if ($val)
                    return "Invited lecture";
                else return '';
            case "lecture-type": // ["lecture_type"],
                return $this->getVal('lecture_type');
            case "link": // ["link"],
            case "software-link": // ["link"],
                $val = $this->getVal('link');
                return "<a target='_blank' href='$val'>$val</a>";
            case "location": // ["location"],
                return $this->getVal('location');
            case "magazine": // ["magazine"],
                return $this->getVal('magazine');
            case "online-ahead-of-print": // ["epub"],
                if ($this->usecase == 'list')
                    return bool_icon($this->getVal('epub', false));
                if ($this->getVal('epub', false))
                    return "<span style='color:#B61F29;'>[Online ahead of print]</span>";
                else return '';
            case "openaccess": // ["open_access"],
            case "openaccess-status": // ["open_access"],
                $status = $this->getVal('oa_status', 'Unknown Status');
                if (!empty($this->getVal('open_access', false))) {
                    $oa = '<i class="icon-open-access text-success" title="Open Access (' . $status . ')"></i>';
                } else {
                    $oa = '<i class="icon-closed-access text-danger" title="Closed Access"></i>';
                }
                if ($this->usecase == 'list') return $oa . " " . $status;
                return $oa;
                
            case "open_access": // ["open_access"],
                if (!empty($this->getVal('open_access', false))) {
                    return '<i class="icon-open-access text-success" title="Open Access"></i> yes';
                } else {
                    return '<i class="icon-closed-access text-danger" title="Closed Access"></i> no';
                }
            case "oa_status": // ["open_access"],
                return $this->getVal('oa_status', 'Unknown Status');

            case "pages": // ["pages"],
                return $this->getVal('pages');
            case "person": // ["name", "affiliation", "academic_title"],
                return $this->getVal('name');
            case "publisher":; // ["publisher"],
                return $this->getVal('publisher');
            case "pubmed": // ["pubmed"],
                $val = $this->getVal('pubmed');
                if (empty($val)) return '';
                return "<a target='_blank' href='https://pubmed.ncbi.nlm.nih.gov/$val'>$val</a>";
            case "pubtype": // ["pubtype"],
                return $this->getVal('pubtype');
            case "review-description": // ["title"],
                return $this->getVal('title');
            case "review-type": // ["title"],
                return $this->getVal('review-type');
            case "scientist": // ["authors"],
                return $this->formatAuthors($this->getVal('authors'));
            case "semester-select": // [],
                return '';
            case "subtype":
                return $this->activity_title();
            case "software-type": // ["software_type"],
                $val = $this->getVal('software_type');
                switch ($val) {
                    case 'software':
                        return "Computer software";
                    case 'database':
                        return "Database";
                    case 'webtool':
                        return "Webpage";
                    case 'dataset':
                        return "Dataset";
                    default:
                        return "Computer software";
                }
            case "software-venue": // ["software_venue"],
                return $this->getVal('software_venue');
            case "status": // ["status"],
                return $this->getVal('status');
            case "student-category": // ["category"],
                return $this->translateCategory($this->getVal('category'));
            case "supervisor": // ["authors"],
                return $this->formatAuthors($this->getVal('authors'));
            case "teaching-category": // ["category"],
                return $this->translateCategory($this->getVal('category'));
            case "teaching-course": // ["title", "module", "module_id"],
                if (isset($this->doc['module_id'])) {
                    $m = $this->db->getConnected('teaching', $this->getVal('module_id'));
                    return $m['module'] . ': ' . $m['title'];
                }
                return $this->getVal('title');
            case "teaching-course-short": // ["title", "module", "module_id"],
                if (isset($this->doc['module_id'])) {
                    $m = $this->db->getConnected('teaching', $this->getVal('module_id'));
                    return $m['module'];
                }
                return 'Unknown';
            case "title": // ["title"],
                return $this->getVal('title');
            case "university": // ["publisher"],
                return $this->getVal('publisher');
            case "version": // ["version"],
                return $this->getVal('version');
            case "volume": // ["volume"],
                return $this->getVal('volume');
            case "country":
            case "nationality":
                $code = $this->getVal('country');
                return Country::get($code);
            case "gender":
                switch ($this->getVal('gender')) {
                    case 'f':
                        return lang('female', 'weiblich');
                    case 'm':
                        return lang('male', 'männlich');
                    case 'd':
                        return lang('non-binary', 'divers');
                    case '-':
                        return lang('not specified', 'keine Angabe');
                    default:
                        return '';
                }
            case "volume-issue-pages": // ["volume"],
                $val = '';
                if (!empty($this->getVal('volume'))) {
                    $val .= " " . $this->getVal('volume');
                }
                if (!empty($this->getVal('issue'))) {
                    $val .= "(" . $this->getVal('issue') . ')';
                }
                if (!empty($this->getVal('pages'))) {
                    $val .= ": " . $this->getVal('pages');
                }
                return $val;
            default:
                return $this->getVal($module);
        }
    }

    public function format()
    {
        $this->full = true;
        $template = '{title}';
        $template = $this->subtypeArr['template']['print'] ?? $template;

        $line = $this->template($template);
        if ($this->usecase == 'web')
            $line .= $this->get_field('file-icons');

        if (!empty($this->appendix)) {
            $line .= "<br><small style='color:#878787;'>" . $this->appendix . "</small>";
        }
        return $line;
    }

    public function formatShort($link = true)
    {
        $this->full = false;
        $line = "";
        $template = $this->subtypeArr['template']['title'] ?? '{title}';
        $title = $this->template($template);

        if ($link) {
            $id = strval($this->doc['_id']);
            $line = "<a class='colorless' href='" . ROOTPATH . "/activities/view/$id'>$title</a>";
        } else {
            $line = $title;
        }


        $template = $this->subtypeArr['template']['subtitle'] ?? '{authors}';
        $line .= "<br><small class='text-muted d-block'>";
        $line .= $this->template($template);
        $line .= $this->get_field('file-icons');
        $line .= "</small>";

        // if (!empty($this->appendix)) {
        //     $line .= "<br><small style='color:#878787;'>" . $this->appendix . "</small>";
        // }
        return $line;
    }

    private function template($template)
    {

        $vars = array();

        $pattern = "/{([^}]*)}/";
        preg_match_all($pattern, $template, $matches);
        // dump($matches[1], true);

        foreach ($matches[1] as $module) {
            $m = explode('|', $module, 2);
            $value = $this->get_field($m[0]);

            if (empty($value) && count($m) == 2) {
                $value = $m[1];
            }
            $vars['{' . $module . '}'] = ($value);
        }

        $line = strtr($template, $vars);

        $line = preg_replace('/\(\s*\)/', '', $line);
        $line = preg_replace('/\[\s*\]/', '', $line);
        $line = preg_replace('/\s+[,]+/', ',', $line);
        $line = preg_replace('/([?!,])\.+/', '$1', $line);
        $line = preg_replace('/,\s\./', '.', $line);
        $line = preg_replace('/,\s:/', ',', $line);
        $line = preg_replace('/\s\./', '.', $line);
        $line = preg_replace('/\.+/', '.', $line);
        $line = preg_replace('/\(:\s*/', '(', $line);
        $line = preg_replace('/\s+/', ' ', $line);
        $line = preg_replace('/,\s*$/', '', $line);

        return $line;
    }
}
