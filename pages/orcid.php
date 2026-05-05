<?php

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $orcid = $Settings->get('orcid');
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    $ch = curl_init('https://sandbox.orcid.org/oauth/token');

    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => http_build_query(array(
        'code' => $code,
        'client_id' => $orcid['client_id'],
        'client_secret' => $orcid['client_secret'],
        'grant_type' => 'authorization_code',
        'redirect_uri' => $protocol . $_SERVER['HTTP_HOST'] . ROOTPATH . '/orcid'
      )),
      CURLOPT_HTTPHEADER => array(
        'Accept: application/json'
      ),
    ));

    $response = curl_exec($ch);

    curl_close($ch);

    $orcid_data = json_decode($response, true);

    if (empty($orcid_data) || !isset($orcid_data['orcid'])) {
        // Handle error, e.g. log it and show an error message to the user
        error_log('ORCID authentication failed: ' . $response);
        include BASEPATH . "/header.php";
        echo "<div class='alert alert-danger'>ORCID authentication failed. Please try again.</div>";
        include BASEPATH . "/footer.php";
        exit;
    }

    $username = $_SESSION['username'];
    
    $osiris->persons->updateOne(
        ['username' => $username],
        ['$set' => [
            'orcid' => $orcid_data['orcid'],
            'orcid_validated' => $orcid_data['orcid'],
            'orcid_access_token' => $orcid_data['access_token'], 
            'orcid_refresh_token' => $orcid_data['refresh_token']
          ]
        ]
    );
}

?>

<div class="row">
    <div class="col">
        <h1><?= lang('ORCID Authentication', 'ORCID Authentifizierung') ?></h1>
        <p><?= lang('You have successfully authenticated with ORCID. You can now close this window and return to the user settings.', 'Sie haben sich erfolgreich mit ORCID authentifiziert. Sie können dieses Fenster jetzt schließen und zu den Benutzereinstellungen zurückkehren.') ?></p>
    </div>
</div>