<?php
$username = $_SESSION['username'];
$user = $osiris->persons->findOne(['username' => $username]);

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $orcid = $Settings->get('orcid');
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    // TODO change the sandbox link to production when going live
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
    // echo ($response);

    curl_close($ch);

    $orcid_data = json_decode($response, true);

  
    if (empty($orcid_data) || !isset($orcid_data['orcid'])) {
        // Handle error, e.g. log it and show an error message to the user
        error_log('ORCID authentication failed: ' . $response);
        echo "<div class='alert alert-danger'>ORCID authentication failed. Please try again.</div>";
        exit;
    }

    $osiris->persons->updateOne(
        ['username' => $username],
        ['$set' => [
            'orcid' => $orcid_data['orcid'],
            'orcid_validated' => true,
            'orcid_access_token' => $orcid_data['access_token'], 
            'orcid_refresh_token' => $orcid_data['refresh_token'],
            'orcid_token_scope' => $orcid_data['scope'],
          ]
        ]
    );
}

// import from ORCID API
if (isset($_POST['import_orcid'])) {
    $curl = curl_init();
    
    $user_orcid = $user['orcid'];
    $user_token = $user['orcid_access_token'];

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
}

?>

<div class="content">
  <?php if (isset($user['orcid']) && isset($user['orcid_validated'])) { ?>
    <h1><?= lang('ORCID Authentication', 'ORCID Authentifizierung') ?></h1>
    <p><?= lang('You have successfully authenticated with ORCID.', 'Sie haben sich erfolgreich mit ORCID authentifiziert.') ?></p>
  
    <form method="post">
        <button type="submit" class="btn" name="import_orcid">
            <?= lang('Import activities from ORCID', 'Aktivitäten von ORCID importieren') ?>
        </button>
    </form>
  <?php } else {?>
    <h1><?= lang('ORCID not authenticated', 'Noch nicht mit ORCID authentifiziert') ?></h1>
  <?php } ?>

    <button class="btn">
        <a href="<?= ROOTPATH ?>/profile/<?= $_SESSION['username'] ?>#section-general">
            <?= lang('Back to Profile', 'Zurück zum Profil') ?>
        </a>
    </button>
</div>