<?php
$username = $_SESSION['username'];
$user = $osiris->persons->findOne(['username' => $username]);

$last_code = $osiris->accounts->findOne(['username' => $username])['orcid_activation_code'] ?? null;


/**
 * If there is an orcid authentication code in the URL
 */

if (isset($_GET['code']) && $_GET['code'] !== $last_code) {
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
        'redirect_uri' => $protocol . $_SERVER['HTTP_HOST'] . ROOTPATH . '/orcid/validate'
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
        echo "<div class='alert alert-danger'>" . lang('ORCID authentication failed. Please try again.', 'ORCID-Authentifizierung fehlgeschlagen. Bitte versuchen Sie es erneut.') . "</div>";
        exit;
    }

    $osiris->persons->updateOne(
        ['username' => $username],
        ['$set' => [
            'orcid' => $orcid_data['orcid'],
            'orcid_validated' => true,
          ]
        ]
    );

    $osiris->accounts->updateOne(
      ['username' => $username],
      ['$set' => [
            'orcid_access_token' => $orcid_data['access_token'], 
            'orcid_refresh_token' => $orcid_data['refresh_token'],
            'orcid_token_scope' => $orcid_data['scope'],
            'orcid_activation_code' => $code
        ]
      ],
      ['upsert' => true]
    );

}

?>

<div class="content">
  <?php if (isset($user['orcid']) && isset($user['orcid_validated'])) { ?>
    <h1><?= lang('ORCID Authentication', 'ORCID Authentifizierung') ?></h1>
    <p><?= lang('You have successfully authenticated with ORCID.', 'Sie haben sich erfolgreich mit ORCID authentifiziert.') ?></p>
  
    <a href="<?= ROOTPATH ?>/orcid/import" class="btn">
        <?= lang('Import activities from ORCID', 'Aktivitäten von ORCID importieren') ?>
    </a>
    <?php } else {?>
        <h1><?= lang('ORCID not authenticated', 'Noch nicht mit ORCID authentifiziert') ?></h1>
        <?php $orcid = $Settings->get('orcid');
            if ($data['username'] == $_SESSION['username'] && !empty($orcid['client_id']) && !empty($orcid['client_secret'])) { 
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                // TODO change the sandbox link to production when going live
                ?>    
                <a href="https://sandbox.orcid.org/oauth/authorize?client_id=<?= $orcid['client_id'] ?>&response_type=code&scope=/authenticate&redirect_uri=<?= $protocol . $_SERVER['HTTP_HOST'] . ROOTPATH ?>/orcid/validate" id="orcid-validation" class="btn">
                    <i class="ph ph-user-circle-check" aria-hidden="true"></i>
                    <?= lang('Connect ORCID', 'ORCID verknüpfen') ?>
                </a>
        <?php } ?>
    <?php } ?>

    <button class="btn">
        <a href="<?= ROOTPATH ?>/profile/<?= $_SESSION['username'] ?>#section-general">
            <?= lang('Back to Profile', 'Zurück zum Profil') ?>
        </a>
    </button>
</div>