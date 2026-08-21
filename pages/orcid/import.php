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

try {
    $works_to_import = $orcid_parser->getWorksForImport();
} catch (Exception $e) {
    echo '<div class="alert error"><h3 class="title">' . lang('Error fetching works from ORCID ', 'Fehler beim Abrufen der Werke von ORCID ') . '</h3><pre class="overflow-auto">' . $e . '</pre></div>';
    exit;
}
if ($works_to_import) { ?>
    <h1>
        <img src="<?= ROOTPATH ?>/img/orcid.svg" alt="ORCID iD" width="24" height="24">
        <?= lang('Ready to import', 'Bereit zum Importieren') ?>:
    </h1>

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
                <?php foreach ($doc['authors'] as $author) { ?>
                    <span><?= $author['first'] ?? '', ' ', $author['last'] ?? '', ', ' ?></span>
                <?php } ?>
            </p>
            <form method="post">
                <button type="submit" name="import" value="<?= htmlspecialchars(json_encode($doc), ENT_QUOTES, 'UTF-8') ?>" class="btn success small">
                    <?= lang('Import', 'Importieren') ?>
                </button>
            </form>
        </div>
    <?php }
} else { ?>
    <div class="alert info">
        <?= lang('No further works to import found', 'Keine weiteren Werke zum Importieren gefunden') ?>
    </div>
<?php } ?>