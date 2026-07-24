<?php

/**
* See import-openalex.php for reference
* 
* Trigger once the function to get all works form ORCID and parse
*
* Visualize a list of all works of the user found in ORCID that are not yet in Osiris
* Make import/reject buttons for each work
*/

$user = $osiris->persons->findOne(['username' => $_SESSION['username']]);

if (!($user['orcid_validated'] ?? false)) {
    echo '<div class="alert error">' . lang('Your ORCID is not yet validated. Please validate your ORCID before importing works.', 'Deine ORCID ist noch nicht validiert. Bitte validiere deine ORCID, bevor du Werke importierst.') . '</div>';
    exit;
}

require_once BASEPATH . '/php/OrcidParser.php';
$username = $_SESSION['username'];
$orcid_parser = new OrcidParser($username);


if (isset($_POST['import'])){
    $work = json_decode($_POST['import'], true);
    $work_id = $orcid_parser->importWork($work);
    # redirect to the work page after import
    header('Location: ' . ROOTPATH . '/activities/edit/' . $work_id);
    exit;
}

try {
    $works_to_import= $orcid_parser->getWorksForImport();
} catch (Exception $e) {
    echo '<div class="alert error">' . lang('Error fetching works from ORCID ', 'Fehler beim Abrufen der Werke von ORCID ') . '</div>';
    exit;
}
if ($works_to_import) {
    foreach ($works_to_import as $doc) {
?>
    <div class="alert mb-10">
        <?= lang('Ready to import', 'Bereit zum Importieren')?> - <em><?= $doc['type'], ' ', $doc['subtype']?></em>:
        <br>
        <strong><?= $doc['title'] ?></strong>
        <?php if (!empty($doc['year'])) { ?>
            <br><?= (int) $doc['year'] ?><?php if (!empty($doc['month'])) { ?>-<?= str_pad((string) ((int) $doc['month']), 2, '0', STR_PAD_LEFT) ?><?php } ?><?php if (!empty($doc['day'])) { ?>-<?= str_pad((string) ((int) $doc['day']), 2, '0', STR_PAD_LEFT) ?><?php } ?>
        <?php } ?>
        <?php if (!empty($doc['journal'])) { ?><br>in <em><?= $doc['journal'] ?></em> <?php } ?>
        <br>
        <?php foreach ($doc['authors'] as $author) { ?>
            <span><?= $author['first'] ?? '', ' ', $author['last'] ?? '', ', ' ?></span>
        <?php } ?>
        <br>
        <form method="post">
            <button type="submit" name="import" value="<?= htmlspecialchars(json_encode($doc), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success">
                <?= lang('Import', 'Importieren')?>
            </button>
        </form>
    </div>
<?php } } else { ?>
    <div class="alert info">
        <?= lang('No further works to import found', 'Keine weiteren Werke zum Importieren gefunden') ?>
    </div>
<?php } ?>