<?php /** The same event form in an isolated document, so activity widget IDs cannot collide. */ ?>
<!DOCTYPE html>
<html lang="<?= e(lang('common.this_language')) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(translate('action.add_event')) ?></title>
    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/main.css?v=<?= OSIRIS_BUILD ?>">
    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/phosphoricons/regular/style.css?v=<?= OSIRIS_BUILD ?>">
    <?= $Settings->renderAdditionalStylesheetLinks() ?>
    <link rel="stylesheet" href="<?= ROOTPATH ?>/custom_style.css?v=<?= OSIRIS_BUILD ?>">
    <script>const ROOTPATH = <?= json_encode(ROOTPATH) ?>;</script>
    <script src="<?= ROOTPATH ?>/js/jquery-3.3.1.min.js"></script>
    <?php include BASEPATH . '/php/js_translations.php'; ?>
    <script src="<?= ROOTPATH ?>/js/script.js?v=<?= OSIRIS_BUILD ?>"></script>
    <script src="<?= ROOTPATH ?>/js/osiris.js?v=<?= OSIRIS_BUILD ?>"></script>
</head>
<body class="p-10"><div class="sticky-alerts"></div>
    <?php include BASEPATH . '/pages/events/edit.php'; ?>
    <script>
    document.getElementById('conference-form').addEventListener('submit', async function(event) {
        event.preventDefault();
        const button = this.querySelector('button[type="submit"]');
        button.disabled = true;
        try {
            const response = await fetch(this.action, { method: 'POST', body: new FormData(this), headers: { Accept: 'application/json' } });
            const result = await response.json();
            if (!response.ok || !result.id) throw new Error(result.msg || response.statusText);
            window.parent.postMessage({ type: 'osiris:event-created', result }, window.location.origin);
        } catch (error) {
            toastError(error.message);
        } finally {
            button.disabled = false;
        }
    });
    </script>
</body>
</html>
