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


if (isset($_POST['import'])) {
    $work = json_decode($_POST['import'], true);
    $work_id = $orcid_parser->importWork($work);
    # redirect to the work page after import
    header('Location: ' . ROOTPATH . '/activities/edit/' . $work_id . '?redirect=' . urlencode(ROOTPATH . '/orcid/import'));
    exit;
}

if (isset($_POST['skip'])) {
    $work = json_decode($_POST['skip'], true);
    $orcid_parser->skipWork($work['orcid_put_code']);
    # reload page
    header('Location: ' . ROOTPATH . '/orcid/import');
    exit;
}

try {
    $all_works = $orcid_parser->getWorksForImport();
    $skipped_docs = $user['skipped_orcid_works'] ?? [];
    // Convert MongoDB BSONArray to a regular PHP array
    if ($skipped_docs instanceof \MongoDB\Model\BSONArray) {
        $skipped_docs = $skipped_docs->getArrayCopy();
    }
    $skipped_works = [];
    $works_to_import = [];
    foreach ($all_works as $doc) {
        $putCode = $doc['orcid_put_code'] ?? null;
        if ($putCode!==null && $skipped_docs && in_array($putCode, $skipped_docs)) {
            $skipped_works[] = $doc;
        } else {
            $works_to_import[] = $doc;
        }
    }

} catch (Exception $e) {
    echo '<div class="alert error"><h3 class="title">' . lang('Error fetching works from ORCID ', 'Fehler beim Abrufen der Werke von ORCID ') . '</h3><pre class="overflow-auto">' . $e . '</pre></div>';
    exit;
}

if (!empty($works_to_import)) { ?>
    <h1>
        <img src="<?= ROOTPATH ?>/img/orcid.svg" alt="ORCID iD" width="24" height="24">
        <?= lang('Ready to import', 'Bereit zum Importieren') ?>:
    </h1>

    <?php    
    foreach ($works_to_import as $doc) {
        $type = $doc['type'] ?? '';
        $subtype = $doc['subtype'] ?? '';
    ?>
        <div class="box padded">
            <ul class="breadcrumb category" style="--highlight-color:var(--primary-color);">
                <li><?= $Settings->title($type) ?></li>
                <li><?= $Settings->title(null, $doc['subtype']) ?></li>
            </ul>
            <h2><?= $doc['title'] ?></h2>
            <ul class="metadata horizontal">
                <?php if (isset($doc['start_date'])) { ?>
                    <li class="time">
                        <i class="ph ph-calendar"></i>
                        <?= format_date($doc['start_date']) ?>
                    </li>
                <?php } ?>

                <?php if (!empty($doc['journal'] ?? null)) { ?>
                    <li class="journal">
                        <i class="ph ph-newspaper"></i>
                        <?= $doc['journal'] ?>
                    </li>
                <?php } ?>
                <?php if (isset($doc['doi'])) { ?>
                    <li class="doi">
                        <i class="ph ph-link"></i>
                        <a href="https://doi.org/<?= $doc['doi'] ?>" target="_blank" rel="noopener noreferrer" class="colorless link"><?= $doc['doi'] ?></a>
                    </li>
                <?php } ?>

            </ul>
            <p class="font-size-12 text-muted">
                <span>
                    <?php
                    $authors = $doc['authors'] instanceof \MongoDB\Model\BSONArray ? $doc['authors']->getArrayCopy(): $doc['authors'];
                    echo implode(', ', array_map(
                        fn($author) => trim(($author['first'] ?? '') . ' ' . ($author['last'] ?? '')),
                        $authors
                    ));
                    ?>
                </span>
            </p>
            <form method="post">
                <button type="submit" name="import" value="<?= htmlspecialchars(json_encode($doc), ENT_QUOTES, 'UTF-8') ?>" class="btn success small">
                    <?= lang('Import', 'Importieren') ?>
                </button>
                <button type="submit" name="skip" value="<?= htmlspecialchars(json_encode($doc), ENT_QUOTES, 'UTF-8') ?>" class="btn secondary small">
                    <?= lang('Skip', 'Überspringen') ?>
                </button>
            </form>
        </div>
    <?php } ?>
<?php } else { ?>
    <div class="alert info">
        <?= lang('No further works to import found', 'Keine weiteren Werke zum Importieren gefunden') ?>
    </div>
<?php } ?>

<?php if(!empty($skipped_works)) { ?>
    <br>
    <button class="btn secondary small" type="button" onclick="document.querySelector('.skipped-works').style.display = document.querySelector('.skipped-works').style.display === 'none' ? 'block' : 'none';">
        <?= lang('Show skipped works', 'Übersprungene Werke anzeigen') . ' ' . '[' . count($skipped_works) . ']' ?> 
    </button>
    <div class="skipped-works" style="display:none;">
        <?php    
        foreach ($skipped_works as $doc) {
            $type = $doc['type'] ?? '';
            $subtype = $doc['subtype'] ?? '';
        ?>
            <div class="box padded">
                <ul class="breadcrumb category" style="--highlight-color:var(--primary-color);">
                    <li><?= $Settings->title($type) ?></li>
                    <li><?= $Settings->title(null, $doc['subtype']) ?></li>
                </ul>
                <h2><?= $doc['title'] ?></h2>
                <ul class="metadata horizontal">
                    <?php if (isset($doc['start_date'])) { ?>
                        <li class="time">
                            <i class="ph ph-calendar"></i>
                            <?= format_date($doc['start_date']) ?>
                        </li>
                    <?php } ?>

                    <?php if (!empty($doc['journal'] ?? null)) { ?>
                        <li class="journal">
                            <i class="ph ph-newspaper"></i>
                            <?= $doc['journal'] ?>
                        </li>
                    <?php } ?>
                    <?php if (isset($doc['doi'])) { ?>
                        <li class="doi">
                            <i class="ph ph-link"></i>
                            <a href="https://doi.org/<?= $doc['doi'] ?>" target="_blank" rel="noopener noreferrer" class="colorless link"><?= $doc['doi'] ?></a>
                        </li>
                    <?php } ?>

                </ul>
                <p class="font-size-12 text-muted">
                    <span>
                        <?php
                        $authors = $doc['authors'] instanceof \MongoDB\Model\BSONArray ? $doc['authors']->getArrayCopy(): $doc['authors'];
                        echo implode(', ', array_map(
                            fn($author) => trim(($author['first'] ?? '') . ' ' . ($author['last'] ?? '')),
                            $authors
                        ));
                        ?>
                    </span>
                </p>
                <form method="post">
                    <button type="submit" name="import" value="<?= htmlspecialchars(json_encode($doc), ENT_QUOTES, 'UTF-8') ?>" class="btn success small">
                        <?= lang('Import', 'Importieren') ?>
                    </button>
                </form>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<style>
    .metadata {
        font-size: small;
    }

    .metadata i {
        margin-right: 0.25rem;
        color: var(--primary-color);
    }

    .box h2 {
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 1.5rem;
    }

    .box p {
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
</style>