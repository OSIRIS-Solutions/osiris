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

class OrcidParser
{
    private $orcid_api_url = 'https://pub.sandbox.orcid.org/v3.0/';

    private $username;
    private $osiris;

    private $orcid;
    private $token;




    function __construct($username) {
        $DB = new DB();
        $this->osiris = $DB->db;

        $this->username = $username;
        
        $user = $this->osiris->persons->findOne(['username' => $username]);

        $this->orcid = $user['orcid'] ?? null;

        $ACCOUNT = $this->osiris->accounts->findOne(['username' => $username]);
        $this->token = $ACCOUNT['orcid_access_token'] ?? null;
    }


    function getWorks() {
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

}

?>