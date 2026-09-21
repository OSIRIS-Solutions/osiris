<?php
$jsTranslations = [];
foreach (require BASEPATH . '/lang/js_keys.php' as $key) {
    $jsTranslations[$key] = lang($key);
}
?>
<script>
    window.OSIRIS_JS_TRANSLATIONS = <?= json_encode($jsTranslations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
</script>
