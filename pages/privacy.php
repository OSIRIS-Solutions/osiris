<div class="privacy container">
<?php
    $privacy = $Settings->get('privacy');
    if (empty($privacy)) {
        $privacy = file_get_contents(BASEPATH . '/pages/privacy.html');
    }
    if (empty($privacy)) {
        $privacy = "<p>" . lang('No privacy statement available.', 'Keine Datenschutzerklärung verfügbar.') . "</p>";
    }
    echo $privacy;
?>
</div>