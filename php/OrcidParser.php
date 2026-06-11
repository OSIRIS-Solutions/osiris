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
    private $types;
    private $DB;
    private $orcid_settings;
    private $username;
    private $osiris;
    private $orcid;
    private $token;
    private $NameParser;

    function __construct($username) {
        $this->NameParser = new FullNameParser();
        $this->DB = new DB();
        $this->osiris = $this->DB->db;

        $this->username = $username;
        
        $user = $this->osiris->persons->findOne(['username' => $username]);
        $this->orcid = $user['orcid'] ?? null;
        $this->orcid_settings = new Orcid_Settings();

        $ACCOUNT = $this->osiris->accounts->findOne(['username' => $username]);
        $this->token = $ACCOUNT['orcid_access_token'] ?? null;

        $this->types = array_filter(
            $this->orcid_settings->getDOImappings(),
            fn($value, $key) => str_starts_with($key, 'orcid.'),
            ARRAY_FILTER_USE_BOTH
        );
        if (empty($this->types)) {
            throw new Exception('No ORCID types found in DOI mappings. Please ask your admin to set the ORCID settings.');
        }

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

        
        $key = 'orcid.' . $work['type'];
        if (isset($this->types[$key]) && !empty($this->types[$key])) {
            $parsed_work['subtype'] = $this->types[$key];
            $parsed_work['type'] = $this->osiris->adminTypes->findOne(['id' => $parsed_work['subtype']])['parent'];
        }
        else {
            return null; // skip works that are not in the ORCID types defined in the settings
        }

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
        $parsed_work['year'] = (int)($work['publication-date']['year']['value'] ?? null);
        $parsed_work['month'] = (int)($work['publication-date']['month']['value'] ?? null);
        $parsed_work['day'] = (int)($work['publication-date']['day']['value'] ?? null);
        $parsed_work['start_date'] = $parsed_work['year'] . '-' . ($parsed_work['month'] ?? '01') . '-' . ($parsed_work['day'] ?? '01');
        $parsed_work['start'] = [
                'year' => (int)$parsed_work['year'] ?? null,
                'month' => (int)$parsed_work['month'] ?? null,
                'day' => (int)$parsed_work['day'] ?? null,
        ];

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
                'approved' => $username == $this->username ? true : false, // if the contributor is the same as the user importing, we can approve it directly
            ];
        }

        $parsed_work['journal'] = $work['journal-title']['value'] ?? null;
        $journal = $this->DB->getJournal($parsed_work);
        if (!empty($journal)) {
            if (isset($journal['_id'])) {
                $parsed_work['journal_id'] = (string)($journal['_id']);
            }
            $parsed_work['journal'] = $this->DB->getJournalName($parsed_work);
        };
        $parsed_work['open_access'] = $this->DB->get_oa($parsed_work);

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
        
        $parsed_works = [];
        foreach ($not_in_osiris as $work) {
            $put_code = $work['work-summary'][0]['put-code'];
            $work_details = $this->getWork($put_code);
            if ($parsed_work = $this->parseWork($work_details)) {
                $parsed_works[] = $parsed_work;
            }
        }
        return $parsed_works;
    }

    function importWork($work) {
        // TODO implement function to import the work into Osiris
        // This would involve checking if the work already exists (e.g. by DOI), and if not, inserting it into the database
        $add = $this->osiris->activities->insertOne($work);
        $id = $add->getInsertedId();
        return $id;
    }

}