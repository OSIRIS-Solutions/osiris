<?php

$departments = [];
if (!empty($doc['units'])) {
    foreach ($doc['units'] as $d) {
        $dept = $Groups->getGroup($d);
        if ($dept['level'] !== 1) continue;
        $departments[$d] = [
            'en' => $dept['name'],
            'de' => $dept['name_de']
        ];
    }
}

$hidden_fields = ['authors', "editors", "supervisors", "semester-select", 'abstract', 'depts', 'projects', 'title'];
$empty_fields = [];
$sections = [];
$Format->usecase = 'list';
foreach ($fields as $field_id) {
    if (!array_key_exists($field_id, $typeFields)) {
        $section = 'others';
    } else {
        $section = $Modules->all_modules[$field_id]['section'] ?? '';
    }
    if (empty($section)) continue; // if no section is defined, do not show the field
    if (in_array($field_id, $hidden_fields)) continue;

    $names = $Modules->all_modules[$field_id] ?? [];
    $field = [
        'key_en' => $names['name'] ?? ucfirst($field_id),
        'key_de' => $names['name_de'] ?? ucfirst($field_id),
        'value' => null,
    ];
    if ($field_id == 'teaching-course' && isset($doc['module_id'])) :
        $module = $DB->getConnected('teaching', $doc['module_id']);
        $field['value'] = '<a class="link font-weight-bold" href="' . ROOTPATH . '/teaching/view/' . ($module['_id'] ?? '#') . '">' . ($module['module'] ?? '-') . '</a>';
    elseif ($field_id == 'journal' && isset($doc['journal_id'])) :
        $journal = $DB->getConnected('journal', $doc['journal_id']);
        $field['value'] = '<a class="link font-weight-bold" href="' . ROOTPATH . '/journal/view/' . ($journal['_id'] ?? '#') . '">' . ($journal['journal'] ?? '-') . '</a>';
    elseif ($field_id == 'conference' && isset($doc['conference_id'])) :
        $conference = $DB->getConnected('conference', $doc['conference_id']);
        $field['value'] = '<a class="link font-weight-bold" href="' . ROOTPATH . '/conferences/view/' . ($doc['conference_id'] ?? '#') . '">' . ($conference['title'] ?? '-') . '</a>';
    else :
        $field['value'] = $Format->get_field($field_id);
    endif;
    if ($field['value'] === null || $field['value'] === '' || $field['value'] === '-') {
        $empty_fields[] = $field_id;
        continue;
    }
    $sections[$section][] = $field;
}

$author_keys = [
    "authors",
    "editors",
    "supervisors",
];
$count_authors = 0;
foreach ($author_keys as $k) {
    if (isset($doc[$k]) && is_array($doc[$k])) {
        $count_authors += count($doc[$k]);
    }
}

$highlights = DB::doc2Arr($USER['highlighted'] ?? []);
$is_favorite = $user_activity && in_array($id, $highlights);
if ($Settings->featureEnabled('portal')) :
    $doc['hide'] = $doc['hide'] ?? false;
    $visible_subtypes = $Settings->getActivitiesPortfolio(true);
    if (!in_array($doc['subtype'], $visible_subtypes)) {
        $visible_badge = 'status-not-visible';
    } else if ($doc['hide']) {
        $visible_badge = 'status-hidden';
    } else if ($is_favorite) {
        $visible_badge = 'status-highlight';
    } else {
        $visible_badge = 'status-visible';
    }
endif;

if ($edit_perm) {
    include_once BASEPATH . '/pages/activities/activity-modals.php';
}

?>


<link rel="stylesheet" href="<?= ROOTPATH ?>/css/activity.css?v=<?= OSIRIS_BUILD ?>">

<script>
    const ACTIVITY_ID = '<?= $id ?>';
    const TYPE = '<?= $doc['type'] ?>';
</script>

<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>

<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
<script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
<script src="<?= ROOTPATH ?>/js/activity.js?v=<?= OSIRIS_BUILD ?>"></script>


<div class="content-container">
    <div class="container-lg">
        <?php
        if (isset($_SESSION['msg'])) {
            printMsg();
        }
        ?>

        <div class="btn-toolbar mb-20 ml-10">
            <?php if ($canEdit) { ?>
                <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn secondary filled">
                    <i class="ph ph-pencil-simple-line mr-5"></i>
                    <?= lang('Edit', 'Bearbeiten') ?>
                </a>
            <?php } ?>
            <?php if ($user_activity && $locked && empty($doc['end'] ?? null) && $ongoing) { ?>
                <div class="dropdown">
                    <button class="btn secondary outline" data-toggle="dropdown" type="button" id="update-end-date" aria-haspopup="true" aria-expanded="false">
                        <i class="ph ph-calendar-check"></i>
                        <?= lang('End activity', 'Beenden') ?> <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu w-200" aria-labelledby="update-end-date">
                        <form action="<?= ROOTPATH . "/crud/activities/update/" . $id ?>" method="POST" class="content">
                            <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities/view/" . $id ?>">
                            <div class="form-group">
                                <label for="date_end"><?= lang('Activity ended at:', 'Aktivität beendet am:') ?></label>
                                <input type="date" class="form-control" name="values[end]" id="date_end" value="<?= valueFromDateArray($doc['end'] ?? null) ?>" required>
                            </div>
                            <button class="btn btn-block" type="submit"><?= lang('Save', 'Speichern') ?></button>
                        </form>
                    </div>
                </div>
            <?php } ?>


            <?php if ($Settings->featureEnabled('portal')) { ?>
                <a class="btn secondary outline" href="<?= ROOTPATH ?>/preview/activity/<?= $id ?>">
                    <i class="ph ph-eye mr-5"></i>
                    <?= lang('Preview', 'Vorschau') ?>
                </a>
            <?php } ?>


            <div class="dropdown">
                <button class="btn" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                    <i class="ph ph-download mr-5"></i>
                    <?= lang('Download', 'Herunterladen') ?>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdown-1">
                    <div class="content">
                        <button class="btn block primary" onclick="addToCart(this, '<?= $id ?>')">
                            <i class="<?= (in_array($id, $cart)) ? 'ph ph-duotone ph-basket ph-basket-plus text-success' : 'ph ph-basket ph-basket-plus' ?>"></i>
                            <?= lang('Collect', 'Sammeln') ?>
                        </button>
                    </div>
                    <div class="divider"></div>
                    <form action="<?= ROOTPATH ?>/download" method="post" class="content">
                        <input type="hidden" name="filter[id]" value="<?= $id ?>">
                        <div class="form-group">
                            <b><?= lang('Download as', 'Herunterladen als') ?></b>
                            <div class="custom-radio ml-10">
                                <input type="radio" name="format" id="format-word" value="word" checked="checked" onclick="$('#highlight-options').show()">
                                <label for="format-word">Word</label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="format" id="format-bibtex" value="bibtex" onclick="$('#highlight-options').hide()">
                                <label for="format-bibtex">BibTeX</label>
                            </div>
                        </div>

                        <div class="form-group" id="highlight-options">
                            <b><?= lang('Highlight', 'Hervorheben') ?></b>
                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-user" value="user" checked="checked">
                                <label for="highlight-user"><?= lang('Me', 'Mich') ?></label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-aoi" value="aoi">
                                <label for="highlight-aoi"><?= $Settings->get('affiliation') ?><?= lang(' Authors', '-Autoren') ?></label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-none" value="">
                                <label for="highlight-none"><?= lang('None', 'Nichts') ?></label>
                            </div>
                        </div>

                        <button class="btn block primary">
                            <i class="ph ph-download mr-5"></i>
                            <?= lang('Download', 'Herunterladen') ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="dropdown">
                <button class="btn" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only"><?= lang('More Actions', 'Weitere Aktionen') ?></span><i class="ph ph-dots-three" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdown-1">
                    <div class="content">
                        <a href="?view=old" class="btn block">
                            <i class="ph ph-lightning-slash m-0"></i>
                            <?= lang('Classic View', 'Klassische Ansicht') ?>
                        </a>
                        <?php if (!in_array($doc['type'], ['publication'])) { ?>
                            <hr>
                            <a href="<?= ROOTPATH ?>/activities/copy/<?= $id ?>" class="btn block">
                                <i class="ph ph-copy"></i>
                                <?= lang("Copy", "Kopie") ?>
                            </a>
                        <?php } ?>

                        <?php if ($Settings->hasPermission('activities.lock')) { ?>
                            <hr>
                            <form action="<?= ROOTPATH ?>/crud/activities/<?= $id ?>/lock" method="post">
                                <?php if ($doc['locked'] ?? false) { ?>
                                    <button class="btn success block" type="submit">
                                        <i class="ph ph-lock-open"></i>
                                        <?= lang('Unlock', 'Entsperren') ?>
                                    </button>
                                <?php } else { ?>
                                    <button class="btn danger block" type="submit">
                                        <i class="ph ph-lock"></i>
                                        <?= lang('Lock', 'Sperren') ?>
                                    </button>
                                <?php } ?>
                            </form>
                        <?php } ?>

                        <?php if ($canDelete) { ?>
                            <hr>
                            <form action="<?= ROOTPATH ?>/crud/activities/delete/<?= $id ?>" method="post" onsubmit="return confirm('<?= lang('Are you sure you want to delete this activity?', 'Möchtest du diese Aktivität wirklich löschen?') ?>')">
                                <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities" ?>">
                                <button class="btn danger block" type="submit">
                                    <i class="ph ph-trash"></i>
                                    <?= lang('Delete activity', 'Lösche Aktivität') ?>
                                </button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-container">
            <nav id="navigation" class="new-pills mt-20">

                <a onclick="navigate('general')" id="btn-general" class="btn active">
                    <?= lang('Overview', 'Übersicht') ?>
                </a>

                <a onclick="navigate('citations')" id="btn-citations" class="btn">
                    <?= lang('Citation', 'Zitat') ?>
                </a>

                <?php if ($count_authors > 0) { ?>
                    <a onclick="navigate('coauthors')" id="btn-coauthors" class="btn">
                        <?= lang('Contributors', 'Mitwirkende') ?>
                        <span class="index"><?= $count_authors ?></span>
                    </a>
                <?php } ?>

                <?php if ($guests_involved) { ?>
                    <a onclick="navigate('guests')" id="btn-guests" class="btn">
                        <?= lang('Guests', 'Gäste') ?>
                        <span class="index"><?= count($guests) ?></span>
                    </a>
                <?php } ?>

                <?php
                if (!empty($doc['history'])) :
                ?>
                    <a onclick="navigate('history')" id="btn-history" class="btn">
                        <?= lang('History', 'Historie') ?>
                    </a>
                <?php endif; ?>

                <?php if ($Settings->hasPermission('raw-data') || isset($_GET['verbose'])) { ?>
                    <a onclick="navigate('raw')" id="btn-raw" class="btn">
                        <i class="ph ph-brackets-curly"></i>
                    </a>
                <?php } ?>
            </nav>

            <div id="status-board">
                <?php if ($doc['affiliated'] ?? true) { ?>
                    <div class="badge success" data-toggle="tooltip" data-title="<?= lang('At least on author of this activity has an affiliation with the institute.', 'Mindestens ein Autor dieser Aktivität ist mit dem Institut affiliiert.') ?>">
                        <i class="ph-duotone ph-push-pin m-0"></i>
                        <?= lang('Affiliated', 'Affiliiert') ?>
                    </div>
                <?php } else { ?>
                    <div class="badge danger" data-toggle="tooltip" data-title="<?= lang('None of the authors has an affiliation to the Institute.', 'Keiner der Autoren ist mit dem Institut affiliiert.') ?>">
                        <i class="ph-duotone ph-push-pin-slash m-0"></i>
                        <?= lang('Not affiliated', 'Nicht affiliiert') ?>
                    </div>
                <?php } ?>
                <?php if ($doc['locked'] ?? false) { ?>
                    <span id="status-locked" class="badge danger" data-toggle="tooltip" data-title="<?= lang('This activity has been locked.', 'Diese Aktivität wurde gesperrt.') ?>">
                        <i class="ph-duotone ph-lock"></i>
                        <?= lang('Locked', 'Gesperrt') ?>
                    </span>
                <?php } ?>

                <span id="status-not-visible" class="badge <?= $visible_badge !== 'status-not-visible' ? 'hidden' : '' ?>" data-toggle="tooltip" data-title="<?= lang('This activity subtype is not visible in the Portfolio due to general settings of your institute.', 'Dieser Aktivitätstyp ist aufgrund genereller Instituts-Einstellungen im Portfolio nicht sichtbar.') ?>">
                    <i class="ph-duotone ph-eye-slash m-0"></i>
                    <?= lang('Activity type not visible', 'Aktivitätstyp nicht sichtbar') ?>
                </span>

                <span id="status-hidden" class="badge danger <?= $visible_badge !== 'status-hidden' ? 'hidden' : '' ?>" data-toggle="tooltip" data-title="<?= lang('This activity is hidden in the Portfolio.', 'Diese Aktivität ist im Portfolio versteckt.') ?>">
                    <i class="ph-duotone ph-eye-slash"></i>
                    <?= lang('Hidden', 'Versteckt') ?>
                </span>

                <span id="status-visible" class="badge success <?= $visible_badge !== 'status-visible' ? 'hidden' : '' ?>" data-toggle="tooltip" data-title="<?= lang('This activity is visible in the Portfolio.', 'Diese Aktivität ist im Portfolio sichtbar.') ?>">
                    <i class="ph-duotone ph-eye"></i>
                    <?= lang('Visible', 'Sichtbar') ?>
                </span>

                <span id="status-highlight" class="badge signal <?= $visible_badge !== 'status-highlight' ? 'hidden' : '' ?>" data-toggle="tooltip" data-title="<?= lang('This activity is highlighted in the Portfolio.', 'Diese Aktivität ist im Portfolio hervorgehoben.') ?>">
                    <i class="ph-duotone ph-star"></i>
                    <?= lang('Highlighted', 'Hervorgehoben') ?>
                </span>

                <span id="status-ongoing" class="badge blue <?= !$ongoing ? 'hidden' : '' ?>" data-toggle="tooltip" data-title="<?= lang('This activity is currently ongoing.', 'Diese Aktivität läuft derzeit.') ?>">
                    <i class="ph-duotone ph-infinity"></i>
                    <?= lang('Ongoing', 'Laufend') ?>
                </span>
            </div>
        </div>

        <section id="raw" style="display:none" class="box padded tab-box">

            <h2 class="title">
                <?= lang('Raw data', 'Rohdaten') ?>
            </h2>

            <?= lang('Raw data as they are stored in the database.', 'Die Rohdaten, wie sie in der Datenbank gespeichert werden.') ?>

            <div class="overflow-x-scroll">
                <pre><?= e(json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            </div>

        </section>

        <section id="general">
            <div class="row row-eq-spacing my-0">
                <div class="col-md-8">

                    <div class="box tab-box">
                        <div class="content">
                            <ul class="breadcrumb category" style="--highlight-color:<?= $Format->typeArr['color'] ?? '' ?>">
                                <li><?= $Format->activity_type() ?></li>
                                <li><?= $Format->activity_subtype() ?></li>
                            </ul>


                            <h1 class="title"> <?= $Format->getTitle('web') ?></h1>
                            <p class="font-size-16"><?= $Format->getSubtitle('web') ?></p>


                            <div class="font-size-16 mt-10 mb-20">
                                <?php if (!empty($doc['doi'])): ?>
                                    <a href="https://doi.org/<?= $doc['doi']; ?>" target="_blank" class="identifier">
                                        <span class="label"><?= lang("DOI"); ?></span> <?= $doc['doi']; ?>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($doc['pubmed'])): ?>
                                    <a href="https://pubmed.ncbi.nlm.nih.gov/<?= $doc['pubmed']; ?>" target="_blank" class="identifier">
                                        <span class="label"><?= lang("PubMed"); ?></span> <?= $doc['pubmed']; ?>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($doc['isbn'])): ?>
                                    <span class="identifier">
                                        <span class="label"><?= lang("ISBN"); ?></span> <?= $doc['isbn']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr>
                        <div class="content">
                            <?php if ($count_authors > 0): ?>
                                <h3 class="section-title">
                                    <?= lang("Contributors", "Mitwirkende") ?>
                                    <span class="data-index"><?= $count_authors ?></span>
                                    <a onclick="navigate('coauthors')">
                                        <i class="ph ph-arrow-square-right ml-5" title="<?= lang('View all contributors', 'Alle Mitwirkenden anzeigen') ?>"></i>
                                    </a>
                                </h3>
                            <?php else: ?>
                                <div class="alert danger mb-20">
                                    <h4 class="title">
                                        <?= lang('No authors or editors', 'Keine Autoren oder Herausgeber') ?>
                                    </h4>
                                    <p>
                                        <?= lang(
                                            'This activity has no authors or editors assigned. Please add at least one author or editor to this activity, otherwise it cannot be linked to persons.',
                                            'Diese Aktivität hat keine Autoren oder Herausgeber zugeordnet. Bitte füge mindestens einen Autor, Herausgeber oder Betreuenden zu dieser Aktivität hinzu, ansonsten lässt sie sich nicht mit Personen verknüpfen.'
                                        ) ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($author_keys as $role) : ?>
                                <?php if (!empty($doc[$role] ?? null)) : ?>
                                    <ul class="authors">
                                        <?php foreach ($doc[$role] as $i => $author):
                                            if ($i > 9) break;
                                        ?>
                                            <li>
                                                <?php if (!empty($author['user'])): ?>
                                                    <a href="<?= ROOTPATH ?>/profile/<?= $author['user'] ?>">
                                                        <?= $author['first'] ?> <?= $author['last'] ?>
                                                    </a>
                                                <?php else: ?>
                                                    <?= $author['first'] ?> <?= $author['last'] ?>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php if (count($doc[$role]) > 10): ?>
                                            <li class="more-authors">
                                                <a onclick="navigate('coauthors');">
                                                    <?= lang("and " . (count($doc[$role]) - 10) . " more", "und " . (count($doc[$role]) - 10) . " weitere"); ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                <?php endif; ?>

                            <?php endforeach; ?>

                            <?php if (!empty($departments)): ?>
                                <p>
                                    <?php foreach ($departments as $deptId => $d): ?>
                                        <a href="<?= ROOTPATH ?>/groups/view/<?= $deptId; ?>" class="badge primary mr-5 mb-5">
                                            <?= lang($d['en'], $d['de'] ?? null); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </p>
                            <?php endif; ?>

                        </div>
                        <?php
                        $displayAltmetric = true;
                        if ($Settings->featureEnabled('altmetrics')) {
                            $details = [
                                'data-badge-type' => 'medium-donut',
                                'data-badge-popover' => 'left',
                                'data-link-target' => '_blank'
                            ];
                            if (isset($doc['doi']) && !empty($doc['doi'])) {
                                $details['data-doi'] = $doc['doi'];
                            } elseif (isset($doc['isbn']) && !empty($doc['isbn'])) {
                                $details['data-isbn'] = $doc['isbn'];
                            } elseif (isset($doc['pubmed']) && !empty($doc['pubmed'])) {
                                $details['data-pmid'] = $doc['pubmed'];
                            } else {
                                $displayAltmetric = false;
                            }
                        } else {
                            $displayAltmetric = false;
                        }
                        if (!empty($doc['abstract']) || ($displayAltmetric)): ?>
                            <hr>
                            <div class="content">

                                <h3 class="section-title"><?= lang("Abstract", "Zusammenfassung"); ?></h3>
                                <!-- floating container for altmetric badge -->
                                <?php if ($displayAltmetric) { ?>
                                    <div id="altmetric-container" class="float-right ml-20">
                                        <?php
                                        $detailsAttr = '';
                                        foreach ($details as $k => $v) {
                                            $detailsAttr .= " $k='$v' ";
                                        }
                                        ?>
                                        <script type='text/javascript' src='https://embed.altmetric.com/assets/embed.js'></script>
                                        <div class='altmetric-embed' <?= $detailsAttr ?>></div>
                                    </div>
                                <?php
                                } ?>
                                <?php if (!empty($doc['abstract'])) { ?>
                                    <div id="abstract" class="text-justify">
                                        <?php
                                        // show only first 400 characters of abstract if it is longer, with option to show more
                                        if (strlen($doc['abstract']) > 400) {
                                            echo '<div id="short-abstract">' . get_preview($doc['abstract'], 400) .
                                                '<a id="show-more-abstract" class="ml-20">' . lang('Read more', 'Mehr lesen') . '</a>' . '</div>';

                                            echo '<div id="full-abstract" style="display:none;">' . $doc['abstract'] . '</div>';
                                        } else {
                                            echo $doc['abstract'];
                                        }
                                        ?>
                                    </div>
                                <?php } else { ?>
                                    <p><?= lang('No abstract available.', 'Keine Zusammenfassung verfügbar.') ?></p>
                                <?php } ?>

                                <!-- </div> -->

                                <script>
                                    $('#show-more-abstract').click(function() {
                                        $('#short-abstract').hide();
                                        $('#full-abstract').show();
                                    });
                                </script>
                            </div>
                        <?php endif; ?>


                        <?php if ($Settings->featureEnabled('tags')) : ?>
                            <hr>
                            <div class="content">
                                <h3 class="section-title">
                                    <?= $tagLabel ?>
                                    <span class="data-index"><?= count($tags) ?></span>
                                    <?php if ($edit_perm && $Settings->hasPermission('activities.tags')) { ?>
                                        <a href="#edit-tags" class="ml-10">
                                            <i class="ph ph-edit"></i>
                                            <span class="sr-only"><?= lang('Edit', 'Bearbeiten') ?></span>
                                        </a>
                                    <?php } ?>
                                </h3>
                                <div id="tag-list">
                                    <?php
                                    $tags = $doc['tags'] ?? [];
                                    if (count($tags)) {
                                        foreach ($tags as $tag) {
                                    ?>
                                            <a class="badge primary" href="<?= ROOTPATH ?>/activities#tags=<?= urlencode($tag) ?>">
                                                <i class="ph ph-tag"></i>
                                                <?= $tag ?>
                                            </a>
                                        <?php }
                                    } else { ?>
                                        <p class="text-muted"><?= lang('No ' . $tagLabel . ' assigned yet.', 'Noch keine ' . $tagLabel . ' vergeben.'); ?></p>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php endif; ?>


                        <?php if ($upload_possible): ?>
                            <hr>
                            <div class="content">
                                <h3 class="section-title">
                                    <?= lang("Files", "Dateien"); ?>
                                    <span class="data-index"><?= count($files) ?></span>
                                    <?php if ($canEdit): ?>
                                        <a href="#edit-files" class="ml-10">
                                            <i class="ph ph-edit"></i>
                                            <span class="sr-only"><?= lang("Edit", "Bearbeiten") ?></span>
                                        </a>
                                    <?php endif; ?>
                                </h3>

                                <?php if (empty($files)): ?>
                                    <p class="text-muted"><?= lang('No files uploaded yet.', 'Noch keine Dateien hochgeladen.') ?></p>
                                <?php else: ?>
                                    <div id="files" class="files">
                                        <?php foreach ($files as $file) {
                                            $file_url = ROOTPATH . '/uploads/' . $file['_id'] . '.' . $file['extension'];
                                            $file_size = formatBytes($file['size']);
                                        ?>
                                            <a href="<?= $file_url ?>" target="_blank" rel="noopener" class="file-item">
                                                <div class="file-icon">
                                                    <i class='ph ph-file ph-<?= getFileIcon($file['extension'] ?? '') ?>'></i>
                                                </div>
                                                <div>
                                                    <h5>
                                                        <?= $file['filename'] ?>
                                                    </h5>
                                                    <small class="badge muted"><?= $Vocabulary->getValue('activity-document-types', $file['name'] ?? '', lang('Other', 'Sonstiges')); ?></small>
                                                    <p>
                                                        <?= $file['description'] ?? '' ?>
                                                    </p>

                                                    <ul class="horizontal">
                                                        <li><?= $file_size ?></li>
                                                        <li><?= lang('Uploaded by', 'Hochgeladen von') ?> <?= $DB->getNameFromId($file['uploaded_by']) ?></li>
                                                        <li><?= lang('on', 'am') ?> <?= date('d.m.Y', strtotime($file['uploaded'])) ?></li>
                                                    </ul>
                                                </div>
                                                <div class="ml-auto">
                                                    <span class="btn blue square">
                                                        <i class="ph ph-download"></i>
                                                    </span>
                                                </div>
                                            </a>
                                        <?php } ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>


                        <hr>
                        <div class="content">
                            <?php
                            $connections = [];
                            if ($Settings->featureEnabled('projects')) {
                                $connections['projects'] = count($projects);
                            }
                            if ($Settings->featureEnabled('infrastructures')) {
                                $connections['infrastructures'] = count($infrastructures);
                            }
                            $connections['activities'] = count($connected_activities);
                            $count_connections = array_sum($connections);
                            ?>
                            <h3 class="section-title">
                                <?= lang('Connections', 'Verknüpfungen') ?>
                                <span class="data-index"><?= $count_connections ?></span>
                                <?php if ($edit_perm) { ?>
                                    <a href="<?= ROOTPATH ?>/activities/edit-connections/<?= $id ?>" class="ml-10">
                                        <i class="ph ph-edit"></i>
                                        <span class="sr-only"><?= lang("Edit", "Bearbeiten") ?></span>
                                    </a>
                                <?php } ?>
                            </h3>

                            <?php if ($count_connections === 0) { ?>
                                <div class="text-muted">
                                    <?= lang('This activity has no connections to other entities yet.', 'Diese Aktivität hat noch keine Verknüpfungen zu anderen Entitäten.') ?>
                                    <?php if ($edit_perm) { ?>
                                        <?= lang('You can connect', 'Du kannst folgendes verknüpfen') ?>:
                                        <ul class="horizontal mb-10">
                                            <?php if (isset($connections['projects'])) { ?>
                                                <li><?= lang('Projects', 'Projekte') ?></li>
                                            <?php } ?>
                                            <?php if (isset($connections['infrastructures'])) { ?>
                                                <li><?= lang('Infrastructures', 'Infrastrukturen') ?></li>
                                            <?php } ?>
                                            <li><?= lang('Other activities', 'Andere Aktivitäten') ?></li>
                                        </ul>
                                        <a href="<?= ROOTPATH ?>/activities/edit-connections/<?= $activity['_id']; ?>" class="btn small">
                                            <i class="ph ph-edit"></i>
                                            <?= lang("Connect now", "Jetzt verknüpfen") ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <p>
                                    <?php if (isset($connections['projects'])) { ?>
                                        <span class="badge project-badge"><i class="ph ph-tree-structure"></i> <?= lang('Projects', 'Projekte') ?> <b><?= $connections['projects'] ?></b></span>
                                    <?php } ?>
                                    <?php if (isset($connections['infrastructures'])) { ?>
                                        <span class="badge infrastructure-badge"><i class="ph ph-cube-transparent"></i> <?= lang('Infrastructures', 'Infrastrukturen') ?> <b><?= $connections['infrastructures'] ?></b></span>
                                    <?php } ?>
                                    <span class="badge activity-badge"><i class="ph ph-folder"></i> <?= lang('Activities', 'Aktivitäten') ?> <b><?= $connections['activities'] ?></b></span>
                                </p>
                            <?php } ?>


                            <div class="connections">
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $project): ?>
                                        <div class="connection">
                                            <span class="badge project-badge"><i class="ph ph-tree-structure"></i> <?= lang("Project", "Projekt") ?></span>
                                            <h5>
                                                <a href="<?= ROOTPATH ?>/project/<?= $project['_id']; ?>"> <?= $project['name']; ?> </a>
                                            </h5>
                                            <ul class="horizontal">
                                                <li><?= $project['funding_organization'] ?? $project['funder'] ?? $project['scholarship'] ?? "" ?></li>
                                                <li><?= fromToDate($project['start'], $project['end']) ?></li>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>


                                <?php if (!empty($infrastructures)): ?>
                                    <?php foreach ($infrastructures as $infrastructure): ?>
                                        <div class="connection">
                                            <span class="badge infrastructure-badge"><i class="ph ph-cube-transparent"></i> <?= lang("Infrastructure", "Infrastruktur") ?></span>
                                            <h5>
                                                <a href="<?= ROOTPATH ?>/infrastructure/<?= $infrastructure['_id']; ?>"> <?= $infrastructure['name']; ?> </a>
                                            </h5>
                                            <p><?= $infrastructure['subtitle'] ?? '' ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (!empty($connected_activities)) : ?>
                                    <?php foreach ($connected_activities as $con) { ?>
                                        <?php
                                        // check if activity is target or source
                                        $reverse = ($con['target_id'] == $id);
                                        $activity = $osiris->activities->findOne(['_id' => $reverse ? $con['source_id'] : $con['target_id']], ['projection' => [
                                            'rendered' => 1,
                                        ]]);
                                        if (!$activity) continue;
                                        $conLabel = $Format->getRelationshipLabel($con['relationship'], $reverse);
                                        ?>
                                        <div class="connection">
                                            <span class="badge activity-badge"><?= $activity['rendered']['icon'] ?> <?= lang("Activity", "Aktivität") ?></span>
                                            <div><?= lang($conLabel['en'], $conLabel['de']) ?></div>
                                            <?= $activity['rendered']['web'] ?? '' ?>
                                        </div>
                                    <?php } ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-md-4">
                    <table class="table" id="info-table">
                        <tbody>
                            <!-- topics -->
                            <?php if ($Settings->featureEnabled('topics')) { ?>
                                <tr>
                                    <td>
                                        <span class="key"><?= $Settings->topicLabel() ?></span>
                                        <?= $Settings->printTopics($doc['topics'] ?? []) ?: lang('None', 'Keine') ?>
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr>
                                <td>
                                    <span class="key"><?= lang('Date', 'Datum') ?>: </span>
                                    <?php if (!isset($doc['year']) || empty($doc['year']) || !isset($doc['month']) || empty($doc['month'])) { ?>
                                        <div class="alert danger">
                                            <h3 class="title">
                                                <?= lang('No time specified', 'Keine Zeitangabe') ?>
                                            </h3>
                                            <p>
                                                <?= lang(
                                                    'This activity has no time specified. Please add at least month and year to this activity, otherwise it cannot be properly assigned to a quarter and will be falsely sorted and shown.',
                                                    'Diese Aktivität hat keine Zeitangabe. Bitte füge mindestens Monat und Jahr zu dieser Aktivität hinzu, ansonsten kann sie nicht richtig einem Quartal zugeordnet werden und wird falsch sortiert und angezeigt.'
                                                ) ?>
                                            </p>
                                        </div>
                                    <?php } else { ?>
                                        <?= $Format->format_date($doc) ?>
                                    <?php } ?>
                                </td>
                            </tr>

                            <?php if ($doc['impact'] ?? false || (isset($openalex) && isset($openalex['cited_by_count'])) ?? false) { ?>
                                <tr>
                                    <td>
                                        <div class="d-flex" style="gap:4rem;">

                                            <?php if ($doc['impact'] ?? false) { ?>
                                                <div>
                                                    <span class="key"><?= lang('Impact', 'Impact') ?>: </span>
                                                    <span class="badge"><?= $doc['impact'] ?></span>
                                                </div>
                                            <?php } ?>

                                            <?php if ($doc['quartile'] ?? false) { ?>
                                                <div>
                                                    <span class="key"><?= lang('Quartile', 'Quartil') ?>: </span>
                                                    <span class="quartile <?= $doc['quartile'] ?>"><?= $doc['quartile'] ?></span>
                                                </div>
                                            <?php } ?>

                                            <?php if (!empty($openalex) && isset($openalex['cited_by_count'])) {
                                                $fetched_at = isset($openalex['fetched_at']) ? date('d.m.Y', strtotime($openalex['fetched_at'])) : '-';
                                            ?>
                                                <div>
                                                    <span class="key"><?= lang('Citations', 'Zitationen') ?>: </span>
                                                    <span class="badge" data-toggle="tooltip" data-title="<?= lang('Last updated', 'Zuletzt aktualisiert') ?>: <?= $fetched_at ?>"><?= $openalex['cited_by_count'] ?></span>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>


                            <?php if ($Settings->featureEnabled('portal') && $edit_perm) {
                                // $states = ['hidden', 'visible'];
                                $selected_state = 'visible';
                                if ($doc['hide']) {
                                    $selected_state = 'hidden';
                                } elseif ($is_favorite) {
                                    $selected_state = 'highlight';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <span class="key"><?= lang('Online Visibility', 'Online-Sichtbarkeit') ?>: </span>
                                        <div id="visibility-toggle" class="btn-group" role="group" aria-label="Visibility toggle">
                                            <button type="button" class="btn small <?= ($selected_state == 'hidden') ? 'active' : '' ?>" id="btn-hidden" onclick="toggleVisibility('hidden')" data-toggle="tooltip" data-title="<?= lang('The activity will be hidden from the public portal.', 'Die Aktivität wird im öffentlichen Portal verborgen sein.') ?>">
                                                <?= lang('Hidden', 'Versteckt') ?>
                                            </button>
                                            <button type="button" class="btn small <?= ($selected_state == 'visible') ? 'active' : '' ?>" id="btn-visible" onclick="toggleVisibility('visible')" data-toggle="tooltip" data-title="<?= lang('The activity will be visible in the public portal.', 'Die Aktivität wird im öffentlichen Portal sichtbar sein.') ?>">
                                                <?= lang('Visible', 'Sichtbar') ?>
                                            </button>
                                            <?php if ($user_activity) { ?>
                                                <button type="button" class="btn small <?= ($selected_state == 'highlight') ? 'active' : '' ?>" id="btn-highlight" onclick="toggleVisibility('highlight')" data-toggle="tooltip" data-title="<?= lang('The activity will be featured more prominently in your profile and portfolio.', 'Die Aktivität wird prominenter in deinem Profil und Portfolio hervorgehoben.') ?>">
                                                    <i class="ph ph-star" aria-label="<?= lang('Highlight', 'Hervorheben') ?>"></i>
                                                </button>
                                            <?php } ?>
                                        </div>

                                        <script>
                                            let visibilityState = '<?= $selected_state ?>';

                                            function toggleVisibility(newState) {
                                                if (visibilityState === newState) {
                                                    return; // No change
                                                }
                                                $('.btn-group .btn').removeClass('active');
                                                $('#btn-' + newState).addClass('active');

                                                let tasks = [];
                                                if (newState === 'visible' && visibilityState === 'hidden') {
                                                    tasks.push('unhide');
                                                    $('#status-hidden').addClass('hidden');
                                                    $('#status-visible').removeClass('hidden');
                                                } else if (newState === 'hidden' && visibilityState === 'visible') {
                                                    tasks.push('hide');
                                                    $('#status-hidden').removeClass('hidden');
                                                    $('#status-visible').addClass('hidden');
                                                } else if (newState === 'highlight' && visibilityState === 'visible') {
                                                    tasks.push('fav');
                                                    $('#status-highlight').removeClass('hidden');
                                                    $('#status-visible').addClass('hidden');
                                                } else if (newState === 'visible' && visibilityState === 'highlight') {
                                                    tasks.push('unfav');
                                                    $('#status-highlight').addClass('hidden');
                                                    $('#status-visible').removeClass('hidden');
                                                } else if (newState === 'highlight' && visibilityState === 'hidden') {
                                                    tasks.push('unhide');
                                                    tasks.push('fav');
                                                    $('#status-hidden').addClass('hidden');
                                                    $('#status-highlight').removeClass('hidden');
                                                } else if (newState === 'hidden' && visibilityState === 'highlight') {
                                                    tasks.push('unfav');
                                                    tasks.push('hide');
                                                    $('#status-highlight').addClass('hidden');
                                                    $('#status-hidden').removeClass('hidden');
                                                } else {
                                                    console.error('Invalid state transition from ' + visibilityState + ' to ' + newState);
                                                    return;
                                                }
                                                console.log(tasks);
                                                visibilityState = newState;

                                                if (tasks.includes('hide') || tasks.includes('unhide')) {
                                                    $.ajax({
                                                        type: "POST",
                                                        url: ROOTPATH + "/crud/activities/hide",
                                                        data: {
                                                            activity: ACTIVITY_ID
                                                        },
                                                        success: function(response) {
                                                            var hide = $('#btn-hidden').hasClass('active');
                                                            if (hide) {
                                                                toastSuccess(lang('This activity is now hidden in the public portal.', 'Diese Aktivität ist jetzt im öffentlichen Portal versteckt.'));
                                                            } else {
                                                                toastSuccess(lang('This activity is now visible in the public portal.', 'Diese Aktivität ist jetzt im öffentlichen Portal sichtbar.'));
                                                            }
                                                        },
                                                        error: function(response) {
                                                            console.log(response);
                                                        }
                                                    });
                                                }
                                                if (tasks.includes('fav') || tasks.includes('unfav')) {
                                                    $.ajax({
                                                        type: "POST",
                                                        url: ROOTPATH + "/crud/activities/fav",
                                                        data: {
                                                            activity: ACTIVITY_ID
                                                        },
                                                        success: function(response) {
                                                            var highlight = $('#btn-highlight').hasClass('active');
                                                            if (highlight) {
                                                                toastSuccess(lang('This activity is now highlighted in your profile', 'Diese Aktivität ist jetzt in deinem Profil hervorgehoben.'));
                                                            } else {
                                                                toastSuccess(lang('This activity is no longer highlighted in your profile', 'Diese Aktivität ist nicht mehr in deinem Profil hervorgehoben.'));
                                                            }
                                                        },
                                                        error: function(response) {
                                                            console.log(response);
                                                        }
                                                    });
                                                }

                                            }
                                        </script>
                                    </td>
                                </tr>
                            <?php } ?>

                            <?php if ($Settings->hasPermission('activities.exclude')) {
                                $exclude = $doc['exclude_from_reports'] ?? false;
                            ?>
                                <tr>
                                    <td>
                                        <span class="key"><?= lang('Include in reports', 'In Berichten') ?>: </span>
                                        <div id="exclude-toggle" class="btn-group" role="group" aria-label="Exclude toggle">
                                            <button type="button" class="btn small <?= $exclude ? '' : 'active' ?>"
                                                id="btn-include" onclick="toggleExclude(false)"
                                                data-toggle="tooltip" data-title="<?= lang('Will be included in all reports and analytics.', 'wird in allen Berichten und Analysen enthalten sein.') ?>"
                                                style="--blue-color: var(--success-color); --blue-color-20: var(--success-color-20);">
                                                <i class="ph ph-check-circle"></i>
                                                <?= lang('Include', 'Einbeziehen') ?>
                                            </button>
                                            <button type="button" class="btn small <?= $exclude ? 'active' : '' ?>"
                                                id="btn-exclude" onclick="toggleExclude(true)"
                                                data-toggle="tooltip" data-title="<?= lang('Will be excluded from all reports and analytics.', 'Wird von allen Berichten und Analysen ausgeschlossen.') ?>"
                                                style="--blue-color: var(--danger-color); --blue-color-20: var(--danger-color-20);">
                                                <i class="ph ph-prohibit"></i>
                                                <?= lang('Exclude', 'Ausschließen') ?>
                                            </button>
                                        </div>

                                        <script>
                                            function toggleExclude(exclude) {
                                                $('#btn-include').toggleClass('active', !exclude);
                                                $('#btn-exclude').toggleClass('active', exclude);

                                                $.ajax({
                                                    type: "POST",
                                                    url: ROOTPATH + "/crud/activities/exclude-from-reports",
                                                    data: {
                                                        activity: ACTIVITY_ID,
                                                    },
                                                    dataType: "json",
                                                    success: function(response) {
                                                        if (!response.success) {
                                                            toastError(lang('Failed to update the activity. Please try again.', 'Aktualisierung der Aktivität fehlgeschlagen. Bitte versuche es erneut.'));
                                                            return;
                                                        }
                                                        if (exclude) {
                                                            toastSuccess(lang('This activity is now excluded from reports and analytics.', 'Diese Aktivität ist jetzt von Berichten und Analysen ausgeschlossen.'));
                                                        } else {
                                                            toastSuccess(lang('This activity is now included in reports and analytics.', 'Diese Aktivität ist jetzt in Berichten und Analysen enthalten.'));
                                                        }
                                                    },
                                                    error: function(response) {
                                                        console.log(response);
                                                    }
                                                });
                                            }
                                        </script>
                                    </td>
                                </tr>
                            <?php } ?>

                            <?php if (array_key_exists('key', $sections) && !empty($sections['key'])) : ?>
                                <?php foreach ($sections['key'] as $field) : ?>
                                    <tr>
                                        <td>
                                            <span class="key"><?= lang($field['key_en'], $field['key_de']); ?></span>
                                            <span><?= $field['value'] ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>


                    <?php
                    $Format->usecase = "list";
                    foreach (
                        [
                            'bibliography' => lang('Bibliography', 'Bibliographie'),
                            'locations' => lang('Locations', 'Orte'),
                            'events' => lang('Events', 'Veranstaltungen'),
                            'people' => lang('People and Organizations', 'Personen und Organisationen'),
                            'software' => lang('Software', 'Software'),
                            'others' => lang('Others', 'Andere')
                        ] as $section => $section_label
                    ) {
                        if (array_key_exists($section, $sections) && !empty($sections[$section])) { ?>
                            <h4 class="table-title"><?= $section_label ?></h4>
                            <table class="table">
                                <tbody>
                                    <?php foreach ($sections[$section] as $field) {
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="key"><?= lang($field['key_en'], $field['key_de']); ?></span>
                                                <span><?= $field['value']; ?></span>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                    <?php }
                    }
                    ?>

                    <?php if (count($empty_fields) > 0) { ?>

                        <p class="text-muted">
                            <small>
                                <?= lang("The following fields are empty: ", "Die folgenden Felder sind leer: ") ?>
                            </small>
                            <?= implode(", ", array_map(function ($f) use ($Modules) {
                                $names = $Modules->all_modules[$f] ?? [];
                                return lang($names['name_en'] ?? ucfirst($f), $names['name_de'] ?? ucfirst($f));
                            }, $empty_fields)) ?>
                        </p>
                    <?php } ?>
                    </table>


                    <?php if ($Settings->featureEnabled('spectrum') && isset($doc['doi']) && $doc['type'] == 'publication') : ?>
                        <h4 class="table-title">
                            <?= lang('Research Spectrum', 'Forschungs-Spektrum') ?>
                        </h4>
                        <?php
                        if (empty($openalex)) : ?>
                            <p>
                                <?= lang(
                                    'The assignment of topics from OpenAlex is still pending. Please come back later or refresh the page.',
                                    'Die Zuweisung von Themen aus OpenAlex steht noch aus. Bitte versuche es später erneut oder aktualisiere die Seite.'
                                ) ?>
                            </p>
                        <?php
                        elseif (!empty($spectrum)) :
                            include_once BASEPATH . "/php/Spectrum.php";
                            Spectrum::render($spectrum, $count = null, $class = 'mt-0');
                        else :
                            $fetched = $openalex['fetched_at'] ?? null;
                        ?>
                            <p>
                                <?= lang('No topics are assigned to this activity.', 'Zu dieser Aktivität sind keine Themen zugewiesen.') ?>
                            </p>
                            <?php if ($fetched) : ?>
                                <small class="d-block mt-5 text-muted">
                                    <?= lang('Topic data was last updated on', 'Die Themen wurden zuletzt aktualisiert am') ?> <?= date('d.m.Y', strtotime($fetched)) ?>
                                </small>
                            <?php endif; ?>
                            <!-- if fetched is longer ago, show a button to fetch new data -->
                            <?php if (!$fetched || strtotime($fetched) < strtotime('-30 days')) : ?>
                                <button class="btn primary small mt-5" id="openalex-refresh-button" onclick="fetchOpenAlex('<?= $doc['doi'] ?>')">
                                    <i class="ph ph-arrows-clockwise"></i>
                                    <?= lang('Fetch latest topics', 'Neueste Themen abrufen') ?>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                    <?php endif; ?>


                </div>
            </div>
        </section>

        <section id="coauthors" style="display:none" class="box tab-box">
            <div class="content">


                <div class="row row-eq-spacing">
                    <div class="col-md-6 align-self-auto">

                        <p class="mt-0">
                            <b><?= lang('Institutional Cooperation', 'Institutionelle Zusammenarbeit') ?>: </b>
                            <?php
                            switch ($doc['cooperative'] ?? '-') {
                                case 'individual': ?>
                                    <span class="badge" data-toggle="tooltip" data-title="<?= lang('Only one author', 'Nur ein Autor/eine Autorin') ?>">
                                        <?= lang('Individual', 'Einzelarbeit') ?>
                                    </span>
                                <?php
                                    break;
                                case 'departmental': ?>
                                    <span class="badge" data-toggle="tooltip" data-title="<?= lang('Authors from the same department* of this institution', 'Autoren aus der gleichen Abteilung* der Institution') ?>">
                                        <?= lang('Departmental', 'Abteilungsintern') ?>
                                    </span>
                                <?php
                                    break;
                                case 'institutional': ?>
                                    <span class="badge" data-toggle="tooltip" data-title="<?= lang('Authors from different departments* but all from this institution', 'Autoren aus verschiedenen Abteilungen*, aber alle von dieser Institution') ?>">
                                        <?= lang('Institutional', 'Institutionell') ?>
                                    </span>
                                <?php
                                    break;
                                case 'contributing': ?>
                                    <span class="badge" data-toggle="tooltip" data-title="<?= lang('Authors from different institutions with us being middle authors', 'Autoren aus unterschiedlichen Institutionen mit uns als Mittelautoren') ?>">
                                        <?= lang('Cooperative (Contributing)', 'Kooperativ (Beitragend)') ?>
                                    </span>
                                <?php
                                    break;
                                case 'leading': ?>
                                    <span class="badge" data-toggle="tooltip" data-title="<?= lang('Authors from different institutions with us being leading authors', 'Autoren aus unterschiedlichen Institutionen mit uns als führenden Autoren') ?>">
                                        <?= lang('Cooperative (Leading)', 'Kooperativ (Führend)') ?>
                                    </span>
                                <?php
                                    break;
                                default: ?>
                                    <span class="badge" data-toggle="tooltip" data-title="<?= lang('No author affiliated', 'Autor:innen sind nicht affiliiert') ?>">
                                        <?= lang('None', 'Keine') ?>
                                    </span>
                            <?php
                                    break;
                            }
                            ?>
                        </p>
                        <?php
                        $authorModules = ['authors', 'author-table', 'scientist', 'supervisor', 'supervisor-thesis', 'editor'];
                        $authorTypes = [];
                        foreach ($typeFields as $field_id => $props) {
                            if (!in_array($field_id, $authorModules, true)) continue;
                            $role = Document::author_role_from_field($field_id);
                            if ($role === null) continue;
                            $authorTypes[] = $role;
                            $contributors = $doc[$role] ?? [];
                            // --- Configure optional third column (avoid duplicated if/elseif in thead + tbody) ---
                            $thirdCol = null;
                            if ($sws) {
                                $thirdCol = [
                                    'label' => 'SWS',
                                    'value' => fn($a) => 'SWS <b>' . ($a['sws'] ?? 0) . '</b>',
                                ];
                            } elseif ($supervisorThesis) {
                                $thirdCol = [
                                    'label' => lang('Role', 'Rolle'),
                                    'value' => fn($a) => $Format->getSupervisorRole($a['role'] ?? 'other'),
                                ];
                            } elseif ($role === 'authors') {
                                $thirdCol = [
                                    'label' => lang('Position', 'Position'),
                                    'value' => fn($a) => $Format->getPosition($a['position'] ?? ''),
                                ];
                            }
                            $previewIdx = Document::selectContributorPreviewIndices($contributors, 10);
                            $previewSet = array_fill_keys($previewIdx, true);


                            $total = count($contributors);
                            $hidden = max(0, $total - count($previewIdx));
                            $affCount = 0;
                            foreach ($contributors as $a) if (Document::isAffiliated($a)) $affCount++;

                        ?>
                            <div class="contributor-area mb-20">
                                <div class="d-flex align-items-center gap-10 mb-10">
                                    <h3 class="mt-0 mb-0"><?= $Modules->get_name($field_id) ?></h3>
                                    <?php if ($canEdit): ?>
                                        <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>/<?= $role ?>" class="">
                                            <i class="ph ph-edit"></i>
                                            <span class="sr-only"><?= lang("Edit", "Bearbeiten") ?></span>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="contributors-toolbar">
                                    <?php if ($affCount > 0 && $affCount < $total) { ?>
                                        <button type="button" class="btn small btn-only-affiliated" data-active="0">
                                            <?= lang('Show only affiliated', 'Zeige nur Affiliierte') ?> (<?= $affCount ?>)
                                        </button>
                                    <?php } ?>
                                </div>

                                <table class="table simple author-table contributors-list" data-preview-limit="10" data-role="<?= e($role) ?>">
                                    <tbody id="<?= e($role) ?>">
                                        <?php
                                        $lastHidden = false;
                                        foreach ($contributors as $i => $author) :

                                            $affiliated = Document::isAffiliated($author);
                                            $classes = ['author-row'];
                                            if ($affiliated) {
                                                $classes[] = 'is-affiliated';
                                            }
                                            if (!isset($previewSet[$i])) {
                                                $classes[] = 'is-hidden';
                                                if (!$lastHidden) {
                                                    echo '<tr class="show-more-row"><td><a class="btn-show-all" title="' . lang("Show all contributors", "Alle Mitwirkende anzeigen") . '">&#x22ef;</a></td></tr>';
                                                    $lastHidden = true;
                                                }
                                            } else {
                                                $lastHidden = false;
                                                $classes[] = 'is-preview';
                                            }
                                            // --- Name "Last, First" (inline; used once) ---
                                            $name = $author['last'] ?? '';
                                            if (!empty($author['first'])) $name .= ', ' . $author['first'];
                                            $name = trim($name);

                                            $hasUser = !empty($author['user']);

                                            // Unique dropdown id per row (prevents collisions)
                                            $dropdownId = 'claim-dd-' . $role . '-' . $i;
                                        ?>
                                            <tr class="<?= implode(' ', $classes) ?>" data-index="<?= $i ?>">
                                                <td class="text-nowrap">
                                                    <div class="author-name">
                                                        <?php if ($hasUser): ?>
                                                            <a href="<?= ROOTPATH ?>/profile/<?= e($author['user']) ?>">
                                                                <?= e($name) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <?= e($name) ?>
                                                        <?php endif; ?>

                                                        <?php if (!empty($author['orcid'])): ?>
                                                            <a href="https://orcid.org/<?= e($author['orcid']) ?>"
                                                                target="_blank" rel="noopener"
                                                                data-toggle="tooltip"
                                                                data-title="ORCID: <?= e($author['orcid']) ?>">
                                                                <img loading="lazy" decoding="async" width="16" height="16"
                                                                    class="orcid-img" style="width:16px;"
                                                                    src="<?= ROOTPATH ?>/img/orcid.svg" alt="ORCID">
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="author-chips font-size-12 text-muted">

                                                        <?php if ($affiliated): ?>
                                                            <span class="author-chip success"
                                                                data-toggle="tooltip"
                                                                data-title="<?= lang('Author of the institution', 'Autor:in der Einrichtung') ?>">
                                                                <i class="ph ph-handshake"></i>
                                                                <?= lang('Affiliated', 'Affiliiert') ?>
                                                            </span>
                                                        <?php endif; ?>

                                                        <?php if ($hasUser): ?>
                                                            <?php if ($author['approved']) { ?>
                                                                <span class="author-chip neutral"
                                                                    data-toggle="tooltip"
                                                                    data-title="<?= lang('Author approved this activity', 'Autor hat die Aktivität bestätigt') ?>">
                                                                    <?= bool_icon(true) ?>
                                                                    <?= lang('Approved', 'Bestätigt') ?>
                                                                </span>
                                                            <?php } else { ?>
                                                                <span class="author-chip neutral"
                                                                    data-toggle="tooltip"
                                                                    data-title="<?= lang('Author has not yet approved this activity', 'Autor hat die Aktivität noch nicht bestätigt') ?>">
                                                                    <?= bool_icon(false) ?>
                                                                    <?= lang('Pending', 'Ausstehend') ?>
                                                                </span>
                                                            <?php } ?>
                                                        <?php endif; ?>

                                                        <?php if (!empty($author['units'])): ?>
                                                            <div class="author-chip author-units">
                                                                <span class=""
                                                                    data-toggle="tooltip"
                                                                    data-title="<?= lang('Participating units', 'Beteiligte Einheiten') ?>">
                                                                    <i class="ph ph-users-three"></i>
                                                                </span>
                                                                <?php foreach ($author['units'] as $unit):
                                                                    $u = e((string)$unit);
                                                                    $unit = $Groups->getGroup($u);
                                                                    $p = $Groups->getUnitParent($u, 1);
                                                                    // white or black text depending on brightness of background color
                                                                    $bgColor = $p['color']  . 'aa';
                                                                    $brightness = (hexdec(substr($bgColor, 1, 2)) * 0.299 + hexdec(substr($bgColor, 3, 2)) * 0.587 + hexdec(substr($bgColor, 5, 2)) * 0.114);
                                                                    $textColor = ($brightness > 150) ? '#000000' : '#FFFFFF';
                                                                    $title = lang($unit['name'] ?? '', $unit['name_de'] ?? null);
                                                                ?>
                                                                    <a class="author-unit" href="<?= ROOTPATH ?>/groups/view/<?= $u ?>" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;"
                                                                        data-toggle="tooltip"
                                                                        data-title="<?= $title ?>">
                                                                        <?= $u ?>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if (!empty($thirdCol)): ?>
                                                            <div>
                                                                <span class="author-chip neutral" data-toggle="tooltip" data-title="<?= $thirdCol['label'] ?>">
                                                                    <?= $thirdCol['value']($author) ?>
                                                                </span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (!$hasUser && !$user_activity): ?>
                                                        <span class="claim-action">
                                                            <div class="dropdown d-inline-block">
                                                                <button class="btn small" data-toggle="dropdown" type="button"
                                                                    id="<?= $dropdownId ?>" aria-haspopup="true" aria-expanded="false">
                                                                    <?= lang('Claim', 'Beanspruchen') ?>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-right w-300" aria-labelledby="<?= $dropdownId ?>">
                                                                    <div class="content font-size-12 text-danger mb-10" style="white-space: normal;">
                                                                        <?= lang(
                                                                            'You claim that you are this author.<br> This activity will be added to your list and the author name will be added to your list of alternative names.',
                                                                            'Du beanspruchst, dass du diese Person bist.<br> Du fügst diese Aktivität deiner Liste hinzu und den Namen zur Liste deiner alternativen Namen.'
                                                                        ) ?>
                                                                        <form action="<?= ROOTPATH ?>/crud/activities/claim/<?= $id ?>" method="post">
                                                                            <input type="hidden" name="role" value="<?= e($role) ?>">
                                                                            <input type="hidden" name="index" value="<?= (int)$i ?>">
                                                                            <input type="hidden" name="redirect" value="<?= ROOTPATH . "/activities/view/$id" ?>">
                                                                            <button class="btn block small" type="submit">
                                                                                <?= lang('Claim', 'Beanspruchen') ?>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <?php if ($hidden > 0): ?>
                                    <button type="button" class="btn small btn-show-all" data-active="0">
                                        <?= lang('Show all', 'Zeige alle') ?> (<?= $total ?>)
                                    </button>
                                <?php endif; ?>
                            </div>

                        <?php } ?>

                        <!-- btn-only-affiliated
btn-show-all-->
                        <script>
                            $(document).ready(function() {
                                $('.btn-only-affiliated').click(function() {
                                    var active = $(this).attr('data-active') === '1';
                                    if (active) {
                                        showAllAuthors(this);
                                    } else {
                                        showAffiliatedAuthors(this);
                                    }
                                    $(this).attr('data-active', active ? '0' : '1');
                                    $(this).text(active ? '<?= lang('Show only affiliated', 'Zeige nur Affiliierte') ?> (<?= $affCount ?>)' : '<?= lang('Show all', 'Zeige alle') ?> (<?= $total ?>)');
                                });

                                $('.btn-show-all').click(function() {
                                    var active = $(this).attr('data-active') === '1';
                                    if (!active) {
                                        showAllAuthors(this);
                                        $(this).text('<?= lang('Show less', 'Zeige weniger') ?>');
                                    } else {
                                        showPreviewAuthors(this);
                                        $(this).text('<?= lang('Show all', 'Zeige alle') ?> (<?= $total ?>)');
                                    }
                                    $(this).attr('data-active', active ? '0' : '1');

                                });

                                function showAllAuthors(button) {
                                    var area = $(button).closest('.contributor-area');
                                    area.find('.author-row').removeClass('is-hidden');
                                    area.find('.show-more-row').hide();
                                    area.find('.btn-only-affiliated').attr('data-active', '0').text('<?= lang('Show only affiliated', 'Zeige nur Affiliierte') ?> (<?= $affCount ?>)');
                                }

                                function showPreviewAuthors(button) {
                                    var area = $(button).closest('.contributor-area');
                                    area.find('.author-row').addClass('is-hidden');
                                    area.find('.author-row.is-preview').removeClass('is-hidden');
                                    area.find('.show-more-row').show();
                                }

                                function showAffiliatedAuthors(button) {
                                    var area = $(button).closest('.contributor-area');
                                    area.find('.author-row').addClass('is-hidden');
                                    area.find('.author-row.is-affiliated').removeClass('is-hidden');
                                }
                            });
                        </script>

                    </div>
                    <div class="col-md-6 flex-grow-0 d-flex flex-column align-items-center align-self-auto" style="max-width: 40rem">
                        <h3 class="mt-0">
                            <?= lang('Affiliation to units', 'Zuordnung zu Einheiten') ?>
                        </h3>
                        <?php if (count($authorTypes) > 1) { ?>
                            <div class="pills small no-borders mb-20" id="collab-type-filters">
                                <button class="btn active" onclick="showCollaboratorChart('contributors', this)"><?= lang('All', 'Alle') ?></button>
                                <?php if (in_array('authors', $authorTypes)) { ?>
                                    <button class="btn" onclick="showCollaboratorChart('authors', this)"><?= lang('Authors', 'Autoren') ?></button>
                                <?php } ?>
                                <?php if (in_array('supervisors', $authorTypes)) { ?>
                                    <button class="btn" onclick="showCollaboratorChart('supervisors', this)"><?= lang('Supervisors', 'Betreuer:innen') ?></button>
                                <?php } ?>
                                <?php if (in_array('editors', $authorTypes)) { ?>
                                    <button class="btn" onclick="showCollaboratorChart('editors', this)"><?= lang('Editors', 'Herausgeber:innen') ?></button>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <div id="chart-contributors" class="collab-chart" style="max-width: 40rem;">
                            <canvas id="chart-contributors-canvas"></canvas>
                        </div>
                        <div id="chart-authors" class="collab-chart" style="max-width: 40rem;">
                            <canvas id="chart-authors-canvas"></canvas>
                        </div>
                        <div id="chart-editors" class="collab-chart" style="max-width: 40rem;">
                            <canvas id="chart-editors-canvas"></canvas>
                        </div>
                        <div id="chart-supervisors" class="collab-chart" style="max-width: 40rem;">
                            <canvas id="chart-supervisors-canvas"></canvas>
                        </div>

                        <div id="dept-note" class="mt-20">
                            <small class="text-muted">
                                <?= lang('Departments* are determined based on the organizational units of the authors. If an author is affiliated with multiple units, they will be added to more than one department.', 'Die Abteilungen* werden basierend auf den Organisationseinheiten der Autoren bestimmt. Wenn ein Autor mehreren Einheiten zugeordnet ist, wird er zu mehr als einer Abteilung hinzugefügt.') ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <div class="content">

                <div class="row row-eq-spacing">
                    <div class="col-md-6">

                        <h3 class="mt-0">
                            <?= lang('Affiliated positions', 'Affiliierte Positionen') ?>
                        </h3>

                        <?php
                        $positions = [
                            'first' => lang('First author', 'Erstautor:in'),
                            'last' => lang('Last author', 'Letztautor:in'),
                            'first_and_last' => lang('First and last author', 'Erst- und Letztautor:in'),
                            'first_or_last' => lang('First or last author', 'Erst- oder Letztautor:in'),
                            'middle' => lang('Middle author', 'Mittelautor:in'),
                            'single' => lang('One single affiliated author', 'Ein einzelner affiliierter Autor'),
                            'none' => lang('No author affiliated', 'Kein:e Autor:in affiliiert'),
                            'all' => lang('All authors affiliated', 'Alle Autoren affiliiert'),
                            'corresponding' => lang('Corresponding author', 'Korrespondierender Autor:in'),
                            'not_first' => lang('Not first author', 'Nicht Erstautor:in'),
                            'not_last' => lang('Not last author', 'Nicht letzter Autor:in'),
                            'not_middle' => lang('Not middle author', 'Nicht Mittelautor:in'),
                            'not_corresponding' => lang('Not corresponding author', 'Nicht korrespondierender Autor:in'),
                            'not_first_or_last' => lang('Not first or last author', 'Nicht Erst- oder Letztautor:in'),
                            'not_first_and_last' => lang('Not first and last author', 'Nicht Erst- und Letztautor:in'),
                            'unspecified' => lang('Unspecified (no position specified)', 'Unspezifiziert (keine Positionsangabe)'),
                        ];
                        ?>


                        <?php foreach ($doc['affiliated_positions'] ?? [] as $key) { ?>
                            <span class="badge mr-5 mb-5"><?= $positions[$key] ?? $key ?></span>
                        <?php } ?>
                        <br>
                        <small class="text-muted">
                            <?= lang('Automatically calculated', 'Automatisch berechnet') ?>
                        </small>

                    </div>
                    <div class="col-md-6">

                        <h3 class="mt-0">
                            <?= lang('Participating units', 'Beteiligte Einheiten') ?>
                        </h3>
                        <table class="table unit-table w-full no-borders">
                            <tbody>
                                <?php
                                if (!empty($doc['units'] ?? [])) {
                                    $units = $doc['units'];
                                    $hierarchy = $Groups->getPersonHierarchyTree($units);
                                    $tree = $Groups->readableHierarchy($hierarchy);

                                    foreach ($tree as $row) {
                                        $dept = $Groups->getGroup($row['id']);
                                ?>
                                        <tr>
                                            <td class="indent-<?= $row['indent'] ?>">
                                                <a href="<?= ROOTPATH ?>/groups/view/<?= $row['id'] ?>">
                                                    <?= lang($row['name_en'], $row['name_de'] ?? null) ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                } else { ?>
                                    <tr>
                                        <td>
                                            <?= lang('No organisational unit connected', 'Keine Organisationseinheit verknüpft') ?>
                                        </td>
                                    </tr>
                                <?php }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>



        <!-- new section with history -->
        <section id="history" style="display: none;" class="box padded tab-box">
            <h2 class="mt-0">
                <?= lang('History', 'Historie') ?>
            </h2>
            <p>
                <?= lang('History of changes to this activity.', 'Historie der Änderungen an dieser Aktivität.') ?>
            </p>

            <?php
            if (empty($doc['history'] ?? [])) {
                echo lang('No history available.', 'Keine Historie verfügbar.');
            } else {
                // require BASEPATH . "/php/TextDiff/TextDiff.php";
                // $latest = '';
            ?>
                <div class="history-list ">
                    <?php foreach (($doc['history'] ?? []) as $h) {
                        if (!is_array($h)) continue;
                    ?>
                        <div class="">
                            <small class="text-primary"><?= date('d.m.Y', strtotime($h['date'])) ?></small>
                            <h5 class="m-0">
                                <?php
                                echo Settings::getHistoryType($h['type']);
                                echo ' ';
                                if (isset($h['user']) && !empty($h['user'])) {
                                    echo '<a href="' . ROOTPATH . '/profile/' . $h['user'] . '">' . $DB->getNameFromId($h['user']) . '</a>';
                                } else {
                                    echo "System";
                                }
                                ?>
                            </h5>

                            <?php
                            if (isset($h['comment']) && !empty($h['comment'])) { ?>
                                <code><?= $h['comment'] ?></code>
                            <?php
                            }
                            if (isset($h['changes']) && !empty($h['changes'])) {
                                echo '<div class="font-weight-bold mt-10">' .
                                    lang('Changes to the activity:', 'Änderungen an der Aktivität:') .
                                    '</div>';
                                echo '<table class="table simple w-auto small">';
                                foreach ($h['changes'] as $key => $change) {
                                    $before = $change['before'] ?? '<em>empty</em>';
                                    $after = $change['after'] ?? '<em>empty</em>';
                                    if ($before == $after) continue;
                                    if (empty($before)) $before = '<em>empty</em>';
                                    if (empty($after)) $after = '<em>empty</em>';
                                    echo '<tr>
                                <td class="">
                                    <span class="key">' . $Modules->get_name($key) . '</span> 
                                    <span class="del">' . $before . '</span>
                                    <i class="ph ph-arrow-right mx-10"></i>
                                    <span class="ins">' . $after . '</span>
                                </td>
                            </tr>';
                                }
                                echo '</table>';
                            } else  if (isset($h['data']) && !empty($h['data'])) {
                                echo '<div class="font-weight-bold mt-10">' .
                                    lang('Status at this time point:', 'Status zu diesem Zeitpunkt:') .
                                    '</div>';

                                echo '<table class="table simple w-auto small">';
                                foreach ($h['data'] as $key => $datum) {
                                    echo '<tr>
                                <td class="">
                                    <span class="key">' . $Modules->get_name($key) . '</span> 
                                    ' . $datum . ' 
                                </td>
                            </tr>';
                                }
                                echo '</table>';
                            } else if ($h['type'] == 'edited') {
                                echo lang('No changes tracked.', 'Es wurden keine Änderungen verfolgt.');
                            }
                            ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </section>

        <section id="citations" style="display: none;" class="box padded tab-box">

            <?php
            $print = $doc['rendered']['print'];
            $bibtex = $Format->bibtex();
            $ris = $Format->ris();
            ?>

            <h3><?= lang("Citation", "Zitation") ?></h3>
            <div class="connection" id="citation-box">
                <button class="btn primary small float-right" onclick="copyToClipboard('#citation')" data-toggle="tooltip" data-title="<?= lang('Copy to clipboard', 'In die Zwischenablage kopieren') ?>" aria-label="Copy to clipboard">
                    <i class="ph ph-clipboard" aria-hidden="true"></i>
                </button>
                <span id="citation"><?= $print ?></span>
            </div>

            <h3>BibTeX</h3>
            <div class="connection" id="bibtex-box">
                <button class="btn primary small float-right" onclick="copyToClipboard('#bibtex')" data-toggle="tooltip" data-title="<?= lang('Copy to clipboard', 'In die Zwischenablage kopieren') ?>" aria-label="Copy to clipboard">
                    <i class="ph ph-clipboard" aria-hidden="true"></i>
                </button>
                <div class="overflow-x-scroll">
                    <pre id="bibtex"><?= $bibtex ?? '' ?></pre>
                </div>
            </div>

            <h3>RIS</h3>
            <div class="connection" id="ris-box">
                <button class="btn primary small float-right" onclick="copyToClipboard('#ris')" data-toggle="tooltip" data-title="<?= lang('Copy to clipboard', 'In die Zwischenablage kopieren') ?>" aria-label="Copy to clipboard">
                    <i class="ph ph-clipboard" aria-hidden="true"></i>
                </button>
                <div class="overflow-x-scroll">
                    <pre id="ris"><?= $ris ?? '' ?></pre>
                </div>
            </div>
        </section>

        <p class="text-muted font-size-12">
            *<?= lang('We use the term "department" here to refer to the level of organizational units directly below the top-level unit (e.g. faculty or institution). The exact term may vary depending on the organizational structure of your institution.', 'Wir verwenden hier den Begriff "Abteilung" für die Ebene der Organisationseinheiten direkt unterhalb der obersten Einheit (z.B. Fakultät oder Einrichtung). Der genaue Begriff kann je nach Organisationsstruktur deiner Einrichtung variieren.') ?>
        </p>

    </div>


</div>