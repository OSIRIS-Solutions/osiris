<?php
/**
 * See OpenAlexParser.php and adapt to ORCID API
 * 
 * Make a queue for all works of the user found in ORCID that are not yet in Osiris
 * 
 * Make "click to import/reject" functions for works in the queue
 * 
 */

require_once 'DB.php';
require_once 'FullNameParser.php';
require_once 'Orcid.php';

class OrcidParser
{
     public const TYPES = [
        // according to https://info.orcid.org/ufaqs/what-work-types-does-orcid-support/
        // Academic Publications
        "book" => ["publication", "book"],
        "book-chapter" => ["publication", "chapter"],
        "conference-paper" => ["publication", "article"],
        "conference-output" => ["publication", "others"],
        "conference-presentation" => ["publication", "others"],
        "conference-poster" => ["poster", "poster"],
        "conference-proceedings" => ["publication", "others"],
        "journal-article" => ["publication", "article"],
        "preprint" => ["publication", "preprint"],
        "dissertation-thesis" => ["publication", "dissertation"],
        "working-paper" => ["publication", "others"],
        "other" => ["publication", "others"],
        //Review and editing
        "annotation" => ["publication", "others"],
        "book-review" => ["publication", "others"],
        "journal-issue" => ["publication", "others"],
        "review" => ["review", "review"],
        "transcription" => ["publication", "others"],
        "translation" => ["publication", "others"],
        //Dissemination
        "blog-post" => ["publication", "others"],
        "dictionary-entry" => ["publication", "others"],
        "encyclopedia-entry" => ["publication", "others"],
        "magazine-article" => ["publication", "article"],
        "newspaper-article" => ["publication", "article"],
        "report" => ["publication", "report"],
        "public-speech" => ["publication", "others"],
        "website" => ["publication", "others"],
        // Creative
        "artistic-performance" => ["publication", "others"],
        "design" => ["publication", "others"],
        "image" => ["publication", "others"],
        "online-resource" => ["publication", "others"],
        "moving-image" => ["publication", "others"],
        "musical-composition" => ["publication", "others"],
        "sound" => ["publication", "others"],
        // Data and process
        "cartographic-material" => ["publication", "others"],
        "clinical-study" => ["publication", "others"],
        "data-set" => ["publication", "dataset"],
        "data-management-plan" => ["publication", "others"],
        "physical-object" => ["publication", "others"],
        "research-technique" => ["publication", "others"],
        "research-tool" => ["publication", "others"],
        "software" => ["software", "software"],
        // Legal and IP
        "invention" => ["publication", "others"],
        "license" => ["publication", "others"],
        "patent" => ["publication", "others"],
        "registered-copyright" => ["publication", "others"],
        "standards-and-policy" => ["publication", "others"],
        "trademark" => ["publication", "others"],
        // Teaching and supervision
        "lecture-speech" => ["lecture", "lecture"],
        "learning-object" => ["publication", "others"],
        "supervised-student-publication" => ["teaching", "theses"],
        //Legacy Worktypes
        "conference-abstract" => ["publication", "others"],
        "disclosure" => ["publication", "others"],
        "edited-book" => ["publication", "book"],
        "manual" => ["publication", "others"],
        "newsletter-article" => ["publication", "others"],
        "spin-off-company" => ["publication", "others"],
        "technical-standards" => ["publication", "others"],
        "test" => ["publication", "others"],
    ];


    private $orcid_settings;

    private $username;
    private $osiris;

    private $orcid;
    private $token;
    private $NameParser;


    function __construct($username) {
        $this->NameParser = new FullNameParser();
        $DB = new DB();
        $this->osiris = $DB->db;

        $this->username = $username;
        
        $user = $this->osiris->persons->findOne(['username' => $username]);
        $this->orcid = $user['orcid'] ?? null;
        $this->orcid_settings = new Orcid_Settings();
        

        $ACCOUNT = $this->osiris->accounts->findOne(['username' => $username]);
        $this->token = $ACCOUNT['orcid_access_token'] ?? null;

        
    }


    function getWorks() {
        // get list of works from orcid
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->orcid_settings->api_base_url . $this->orcid . '/works',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-type: application/json',
                'Authorization: Bearer ' . $this->token
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $works = json_decode($response, true);
        if (!isset($works['group'])) {
            print_r($response);
            throw new Exception('No works found for ORCID: ' . $this->orcid . '. Response: ' . $response);
        }
        
        return $works;
    }

    function getWork($put_code) {
        // TODO set function private after development

        // get single work from orcid
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->orcid_settings->api_base_url . $this->orcid . '/work/' . $put_code,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-type: application/json',
                'Authorization: Bearer ' . $this->token
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    private function filterWorksNotInOsiris($works) {
        // Checking which works are not yet in Osiris
        // Comparing works based on DOI

        $not_in_osiris = [];

        if (!isset($works['group'])) {
            throw new Exception('No works found for ORCID: ' . $this->orcid);
        }

        foreach ($works['group'] as $work) {
            $work_summary = $work['work-summary'][0];
            $doi = null;
            foreach ($work_summary['external-ids']['external-id'] as $external_id) {
                if ($external_id['external-id-type'] === 'doi') {
                    $doi = $external_id['external-id-value'];
                    break;
                }
            }
            if ($doi) {
                $existing_work = $this->osiris->activities->findOne(['doi' => $doi]);
                if (!$existing_work) {
                    $not_in_osiris[] = $work;
                }
            }
            // If there is no DOI, we could use other identifiers or metadata to check for duplicates
            // For now, we will just add it to the list of works not in Osiris
                
            
        }

        return $not_in_osiris;
    }

    private function getUsername($name, $orcid = null)
    // Copy pasted from OpenAlexParser, could be moved to a helper class
    {
        if ($orcid) {
            $user = $this->osiris->persons->findOne(['orcid' => $orcid]);
            if ($user) {
                return $user->username;
            }
        }
        $user = $this->osiris->persons->findOne([
            'last' => $name['lname'],
            'first' => ['$regex' => '^' . $name['fname'] . '.*']
        ]);
        if ($user) {
            return $user->username;
        }
        return null;
    }

    function parseWork($work) {
        // TODO set function private after development

        // TODO parse the work details to get the relevant information for Osiris
        // e.g. title, authors, publication date, DOI, etc.

        $parsed_work = [];

        // type
        [$type, $subtype] = self::TYPES[$work['type']] ?? ['publication', 'others'];
        $parsed_work['type'] = $type;
        $parsed_work['subtype'] = $subtype;
        if (!$subtype) {
            error_log('Warning: Unknown ORCID work type: ' . $work['type']);
            $subtype = 'unknown';
        }
        $parsed_work['subtype'] = $subtype;

        // title
        $parsed_work['title'] = $work['title']['title']['value'] ?? null;

        // doi
        $parsed_work['doi'] = null;
        $parsed_work['pubmed'] = null;
        if (isset($work['external-ids']['external-id'])) {
            foreach ($work['external-ids']['external-id'] as $external_id) {
                if ($external_id['external-id-type'] === 'doi') {
                    $parsed_work['doi'] = $external_id['external-id-value'];
                }
                if ($external_id['external-id-type'] === 'pmid') {
                    $parsed_work['pubmed'] = $external_id['external-id-value'];
                }
            }
        }

        //date
        $parsed_work['year'] = $work['publication-date']['year']['value'] ?? null;
        $parsed_work['month'] = $work['publication-date']['month']['value'] ?? null;
        $parsed_work['day'] = $work['publication-date']['day']['value'] ?? null;
        $parsed_work['start_date'] = $parsed_work['year'] . '-' . ($parsed_work['month'] ?? '01') . '-' . ($parsed_work['day'] ?? '01');

        $parsed_work['journal'] = $work['journal-title']['value'] ?? null;

        $parsed_work['authors'] = [];
        $last_contributor = null;
        foreach ($work['contributors']['contributor'] as $contributor) {
            # skip duplicate contributors (sometimes the same contributor is listed multiple times with different roles, e.g. as author and as editor)

            if (isset($last_contributor['credit-name']['value']) 
                && isset($contributor['credit-name']['value']) 
                && $contributor['credit-name']['value'] === $last_contributor['credit-name']['value']) {
                continue; 
            }
            $last_contributor = $contributor;

            $name = $this->NameParser->parse_name($contributor['credit-name']['value'] ?? null);
            $orcid = $contributor['contributor-orcid']['path'] ?? null;
            $username = $this->getUsername($name, $orcid);

            $parsed_work['authors'][] = [
                'last' => $name['lname'] ?? null,
                'first' => $name['fname'] ?? null,
                'user' => $username,
                'aoi' => (is_string($username) && strlen($username) > 0) ? true : false,
                'position' => ($contributor === reset($work['contributors']['contributor'])) 
                            ? 'first' 
                            : (($contributor === end($work['contributors']['contributor'])) 
                                ? 'last' 
                                : 'middle'),
                'orcid' => $orcid,
                'approved' => false
            ];
        }

        $parsed_work["history"] = [
            ['date' => date('Y-m-d'), 'type' => 'imported', 'source' => 'ORCID', 'user' => $this->username]
        ];

        $parsed_work['created_by'] = $this->username;

        $parsed_work['raw_input'] = $work;
        return $parsed_work;
    }

    function getWorksForImport() {
        $works = $this->getWorks();
        $not_in_osiris = $this->filterWorksNotInOsiris($works);
        
        $parsed_work = [];
        foreach ($not_in_osiris as $work) {
            $put_code = $work['work-summary'][0]['put-code'];
            $work_details = $this->getWork($put_code);
            $parsed_work[] = $this->parseWork($work_details);
        }
        return $parsed_work;
    }

    function importWork($work) {
        // TODO implement function to import the work into Osiris
        // This would involve checking if the work already exists (e.g. by DOI), and if not, inserting it into the database
        $add = $this->osiris->activities->insertOne($work);
        $id = $add->getInsertedId();
        return $id;
    }

}