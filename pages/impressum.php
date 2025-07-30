<div class="impressum container">
<?php
    $impress = $Settings->get('impress');
    if (empty($impress)) {
        $impress = file_get_contents(BASEPATH . '/pages/impressum.html');
    }
    if (empty($impress)) {
        $impress = "<p>" . lang('No legal notice available.', 'Kein Impressum verfügbar.') . "</p>";
    }
    echo $impress;
?>
</div>