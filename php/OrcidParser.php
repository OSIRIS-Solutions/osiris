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

class OrcidParser
{
     public const TYPES = [
        "book-section" => "chapter",
        "monograph" => "book",
        "report-component" => "others",
        "report" => "others",
        "peer-review" => "others",
        "book-track" => "book",
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
        # Used by ORCID
        
        
        "book" => "book",
        "book-chapter" => "chapter",
        "conference-paper" => "others",
        "conference-output" => "others",
        "conference-presentation" => "others",
        "conference-poster" => "others",
        "conference-proceedings" => "others",
        "journal-article" => "article",
        "preprint" => "others",
        "dissertation-thesis" => "dissertation",
        "working-paper" => "others",
        "other" => "others",
    ];


    private $orcid_api_url = 'https://pub.sandbox.orcid.org/v3.0/';

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

        $ACCOUNT = $this->osiris->accounts->findOne(['username' => $username]);
        $this->token = $ACCOUNT['orcid_access_token'] ?? null;

        
    }


    function getWorks() {
        // get list of works from orcid
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->orcid_api_url . $this->orcid . '/works',
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

    function getWork($put_code) {
        // TODO set function private after development

        // get single work from orcid
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->orcid_api_url . $this->orcid . '/work/' . $put_code,
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
        // TODO filter the works to get only those that are not yet in Osiris
        // e.g. by checking the DOI or other identifiers against the database

        $not_in_osiris = [];

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
                $existing_work = $this->osiris->works->findOne(['doi' => $doi]);
                if (!$existing_work) {
                    $not_in_osiris[] = $work;
                }
            } else {
                // If there is no DOI, we could use other identifiers or metadata to check for duplicates
                // For now, we will just add it to the list of works not in Osiris
                $not_in_osiris[] = $work;
            }
        }

        return $not_in_osiris;
    }

    private function getUserId($name, $orcid = null)
    // Copy pasted from OpenAlexParser, could be moved to a helper class
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

    function parseWork($work) {
        // TODO set function private after development

        // TODO parse the work details to get the relevant information for Osiris
        // e.g. title, authors, publication date, DOI, etc.

        $parsed_work = [];

        // type
        $parsed_work['type'] = 'publication';
        $parsed_work['subtype'] = self::TYPES[$work['type']] ?? null;

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
        foreach ($work['contributors']['contributor'] as $contributor) {
            $name = $this->NameParser->parse_name($contributor['credit-name']['value'] ?? null);
            $orcid = $contributor['contributor-orcid']['path'] ?? null;
            $parsed_work['authors'][] = [
                'last' => $name['lname'] ?? null,
                'first' => $name['fname'] ?? null,
                'user' => $this->getUserId($name, $orcid),
                'position' => ($contributor === reset($work['contributors']['contributor'])) 
                            ? 'first' 
                            : (($contributor === end($work['contributors']['contributor'])) 
                                ? 'last' 
                                : 'middle'),
                'orcid' => $orcid,
            ];
        }

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

}
