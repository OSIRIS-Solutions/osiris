<?php

// data import from ORCID API
$username = $_SESSION['username'];
$user = $osiris->persons->findOne(['username' => $username]);

$curl = curl_init();

$user_orcid = $user['orcid'];

$ACCOUNT = $osiris->accounts->findOne(['username' => $username]);
$user_token = $ACCOUNT['orcid_access_token'];

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://pub.sandbox.orcid.org/v3.0/' . $user_orcid,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
    'Content-type: application/json',
    'Authorization: Bearer ' . $user_token
    ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;


?>