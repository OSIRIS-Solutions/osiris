<style>
    ul.authors {
        list-style: none;
        padding: 0;
        font-size: 1.6rem;
    }

    ul.authors>li {
        display: inline-block;
        margin-right: .5rem;
        margin-bottom: .2rem;
    }

    ul.authors>li::after {
        content: ",";
    }

    ul.authors>li:last-child::after {
        content: "";
    }

    ul.authors>li.more-authors {
        font-style: italic;
    }


    .cards {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .card {
        width: 100%;
        margin: 0.5rem 0;
        border: var(--border-width) solid var(--border-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        background: var(--box-bg-color);
        /* display: flex;
        flex-direction: column;
        align-items: center; */
        padding: 1rem 1.4rem;
    }

    .card h5 a {
        color: var(--link-color) !important;
    }

    .card div {
        border: 0;
        box-shadow: none;
        /* width: 100%; */
        /* height: 100%; */
        display: block;
    }

    .card small,
    .card p {
        display: block;
        margin: 0;
    }

    /* two columns on larger screens */
    @media (min-width: 768px) {
        .card {
            width: calc(50% - 0.5rem);
        }
    }


    .identifier {
        /* display: inline-flex;
        align-items: center;
        gap: .5rem;
        margin-right: 1rem; */
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        margin-right: 1rem;
        border: 1px solid var(--primary-color);
        border-radius: 5px;
        padding: 0 0.5rem 0 0;
        background: white;
    }

    .identifier .label {
        /* background: var(--primary-color);
        color: white;
        padding: 0.1rem 0.4rem;
        border-radius: 3px;
        font-size: 1.2rem;
        text-transform: uppercase; */

        background: var(--primary-color);
        color: white;
        padding: 0.3rem 0.4rem;
        border-radius: 4px;
        border-bottom-right-radius: 0;
        font-size: 1.2rem;
        text-transform: uppercase;
        border-top-right-radius: 0;
    }
</style>

<?php

// check if this is an ongoing activity type
$ongoing = false;
$sws = false;
$supervisorThesis = false;

$typeArr = $Format->typeArr;
$upload_possible = $typeArr['upload'] ?? true;
$subtypeArr = $Format->subtypeArr;
$typeModules = DB::doc2Arr($subtypeArr['modules'] ?? array());
$typeFields = $Modules->getFields();
$fields = array_keys($typeFields);

foreach ($fields as $m) {
    // if (str_ends_with($m, '*')) $m = str_replace('*', '', $m);
    if ($m == 'date-range-ongoing') $ongoing = true;
    if ($m == 'supervisor') $sws = true;
    if ($m == 'supervisor-thesis') $supervisorThesis = true;
}

$projects = [];
if (isset($activity['projects']) && count($activity['projects']) > 0) {
    $projects = $osiris->projects->find(
        ['_id' => ['$in' => $activity['projects']]],
        ['projection' => ['_id' => 1, 'acronym' => 1, 'name' => 1, 'start' => 1, 'end' => 1, 'title' => 1, 'funder' => 1]]
    )->toArray();
}

$guests_involved = boolval($subtypeArr['guests'] ?? false);
$guests = $doc['guests'] ?? [];
// if ($guests_involved)
//     $guests = $osiris->guests->find(['activity' => $id])->toArray();

$edit_perm = ($user_activity || $Settings->hasPermission('activities.edit'));
$tagName = '';
if ($Settings->featureEnabled('tags')) {
    $tagName = $Settings->tagLabel();
}

$connected_activities = $osiris->activitiesConnections->find(
    ['$or' => [['source_id' => $id], ['target_id' => $id]]]
)->toArray();

// Nimm deinen bestehenden User-Kontext
$user_units = DB::doc2Arr($USER['units'] ?? []);
if (!empty($user_units)) {
    $user_units = array_column($user_units, 'unit');
}

$warnings = [];
if ((!isset($doc['editors']) || empty($doc['editors'])) && (!isset($doc['supervisors']) || empty($doc['supervisors']))) {
    $warnings[] = 'no_persons';
}
if (!isset($doc['year']) || empty($doc['year']) || !isset($doc['month']) || empty($doc['month'])) {
    $warnings[] = 'no_date';
}

$documents = $osiris->uploads->find(['type' => 'activities', 'id' => strval($id)])->toArray();

if ($Settings->featureEnabled('quality-workflow', false) && ($user_activity || $Settings->hasPermission('workflows.view'))) {
    include_once BASEPATH . '/pages/activities/activity-workflow.php';
}

$visible_subtypes = $Settings->getActivitiesPortfolio(true);


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


$print = $doc['rendered']['print'];
$bibtex = $Format->bibtex();
$ris = $Format->ris();


$sections = [];
foreach ($fields as $field) {
    if (!array_key_exists($field, $typeFields)) {
        $section = 'others';
    } else {
        $section = $Modules->all_modules[$field]['section'] ?? '';
    }
    if (empty($section)) continue; // if no section is defined, do not show the field
    $sections[$section][] = $field;
}
?>

<script>
    const ACTIVITY_ID = '<?= $id ?>';
    const TYPE = '<?= $doc['type'] ?>';
</script>

<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
<script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
<script src="<?= ROOTPATH ?>/js/activity.js?v=<?= OSIRIS_BUILD ?>"></script>


<div class="content-container">
    <div class="container-lg">


        <div class="btn-toolbar mb-20">
            <?php if (($edit_perm) && (!$locked || $Settings->hasPermission('activities.edit-locked'))) { ?>
                <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn primary filled">
                    <i class="ph ph-pencil-simple-line mr-5"></i>
                    <?= lang('Edit', 'Bearbeiten') ?>
                </a>
            <?php } ?>
            <?php if ($Settings->featureEnabled('portal')) { ?>
                <a class="btn primary outline" href="<?= ROOTPATH ?>/preview/activity/<?= $id ?>">
                    <i class="ph ph-eye mr-5"></i>
                    <?= lang('Preview', 'Vorschau') ?>
                </a>
            <?php } ?>

            <?php if ($user_activity && $locked && empty($doc['end'] ?? null) && $ongoing) { ?>
                <div class="dropdown">
                    <button class="btn primary outline" data-toggle="dropdown" type="button" id="update-end-date" aria-haspopup="true" aria-expanded="false">
                        <i class="ph ph-calendar-check"></i>
                        <?= lang('End activity', 'Beenden') ?> <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-center w-200" aria-labelledby="update-end-date">
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

            <button class="btn">
                <?= lang('More Actions', 'Weitere Aktionen') ?>
                <i class="ph ph-caret-down ml-5"></i>
            </button>

        </div>

        <h1 class="title"><?= $doc['title']; ?></h1>

        <div class="row row-eq-spacing my-0">
            <div class="col-md-8">
                <?php if (!empty($doc['authors'])): ?>
                    <ul class="authors">
                        <?php foreach ($doc['authors'] as $i => $author): ?>
                            <li style="<?= $i > 9 ? 'display:none;' : '' ?>">
                                <?php if (!empty($author['user'])): ?>
                                    <a href="<?= ROOTPATH ?>/profile/<?= $author['user'] ?>">
                                        <?= $author['first'] ?> <?= $author['last'] ?>
                                    </a>
                                <?php else: ?>
                                    <?= $author['first'] ?> <?= $author['last'] ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        <?php if (count($doc['authors']) > 10): ?>
                            <li class="more-authors">
                                <a href="#" onclick="$(this).closest('ul').find('li').show(); $(this).parent().remove();">
                                    <?= lang("and " . (count($doc['authors']) - 10) . " more", "und " . (count($doc['authors']) - 10) . " weitere"); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($departments)): ?>
                    <h3 class="title"><?= lang("Units", "Einheiten") ?></h3>
                    <p>
                        <?php foreach ($departments as $deptId => $d): ?>
                            <a href="<?= ROOTPATH ?>/group/<?= $deptId; ?>" class="badge primary mr-5 mb-5">
                                <?= lang($d['en'], $d['de'] ?? null); ?>
                            </a>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($doc['abstract'])): ?>
                    <h3 class="title"><?= lang("Abstract", "Zusammenfassung"); ?></h3>
                    <div><?= $doc['abstract'] ?></div>
                <?php endif; ?>


                <div class="font-size-16 mt-10">
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


                <?php if (!empty($connected_activities)) { ?>
                    <h3 class="title"><?= lang("Related Activities", "Verknüpfte Aktivitäten"); ?></h3>
                    <table class="table">
                        <tbody>
                            <?php foreach ($connected_activities as $conn) { ?>
                                <tr>
                                    <td>
                                        <div class="font-size-16 mb-10">
                                            <b><?= lang('This', 'Dies') ?> <?= lang($conn['relationship']['en'], $conn['relationship']['de'] ?? null); ?></b><br />
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="w-50">
                                                <!-- <i class="ph ph-arrow-elbow-down-right align-baseline"></i> -->
                                                <?= $conn['icon']; ?>
                                            </div>
                                            <div class="w-full">
                                                <?= $conn['html']; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>


                <?php if (!empty($infrastructures)): ?>
                    <h3 class="title">
                        <?= lang("Associated Infrastructures", "Assoziierte Infrastrukturen"); ?>
                    </h3>
                    <div class="cards">
                        <?php foreach ($infrastructures as $infrastructure): ?>
                            <div class="card">
                                <div>
                                    <h5 class="my-0">
                                        <a href="<?= ROOTPATH ?>/infrastructure/<?= $infrastructure['id']; ?>"> <?= $infrastructure['name']; ?> </a>
                                    </h5>
                                    <small class="text-muted"><?= $infrastructure['subtitle'] ?? '' ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($projects)): ?>
                    <h3 class="title"><?= lang("Associated Projects", "Assoziierte Projekte"); ?></h3>
                    <div class="cards">
                        <?php foreach ($projects as $project): ?>
                            <div class="card">
                                <div>
                                    <h5 class="my-0">
                                        <a href="<?= ROOTPATH ?>/project/<?= $project['_id']; ?>"> <?= $project['name']; ?> </a>
                                    </h5>
                                    <small class="text-muted"><?= $project['title'] ?? '' ?></small>
                                    <hr />
                                    <b> <?= $project['funding_organization'] ?? $project['funder'] ?? $project['scholarship'] ?? "" ?> </b> &nbsp;
                                    <p><?= fromToDate($project['start'], $project['end']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>


                <h3><?= lang("Cite this activity", "Zitiere diese Aktivität"); ?></h3>
                <nav class="pills">
                    <a class="btn active" onclick="nav('citation')">Citation</a>
                    <?php if (!empty($bibtex)): ?>
                        <a class="btn" onclick="nav('bibtex')">BibTeX</a>
                    <?php endif; ?>
                    <?php if (!empty($ris)): ?>
                        <a class="btn" onclick="nav('ris')">RIS</a>
                    <?php endif; ?>
                </nav>

                <div id="tabs">
                    <div class="box padded" id="citation-box">
                        <span><?= $print ?></span>
                    </div>
                    <div class="box padded" id="bibtex-box" style="display: none;">
                        <pre><?= $bibtex ?? '' ?></pre>
                    </div>
                    <div class="box padded" id="ris-box" style="display: none;">
                        <pre><?= $ris ?? '' ?></pre>
                    </div>

                </div>
            </div>



            <div class="col-md-4">

                <h3 class="title"><?= lang("Information", "Informationen"); ?></h3>
                <table class="table" id="info-table">
                    <tbody>
                        <!-- topics -->
                        <?php if ($Settings->featureEnabled('topics')) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= $Settings->topicLabel() ?></span>
                                    <?= $Settings->printTopics($doc['topics'] ?? []) ?>
                                </td>
                            </tr>
                        <?php } ?>


                        <tr>
                            <td>
                                <span class="key"><?= lang('Date', 'Datum') ?>: </span>
                                <?= $Format->format_date($doc) ?>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <span class="key"><?= $Settings->get('affiliation') ?>: </span>
                                <?php

                                if ($doc['affiliated'] ?? true) { ?>
                                    <div class="badge success" data-toggle="tooltip" data-title="<?= lang('At least on author of this activity has an affiliation with the institute.', 'Mindestens ein Autor dieser Aktivität ist mit dem Institut affiliiert.') ?>">
                                        <!-- <i class="ph ph-handshake m-0"></i> -->
                                        <?= lang('Affiliated', 'Affiliiert') ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="badge danger" data-toggle="tooltip" data-title="<?= lang('None of the authors has an affiliation to the Institute.', 'Keiner der Autoren ist mit dem Institut affiliiert.') ?>">
                                        <!-- <i class="ph ph-hand-x m-0"></i> -->
                                        <?= lang('Not affiliated', 'Nicht affiliiert') ?>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>

                        <!-- cooperative -->
                        <tr>
                            <td>
                                <span class="key"><?= lang('Cooperation', 'Zusammenarbeit') ?>: </span>
                                <?php
                                switch ($doc['cooperative'] ?? '-') {
                                    case 'individual': ?>
                                        <span class="badge block" data-toggle="tooltip" data-title="<?= lang('Only one author', 'Nur ein Autor/eine Autorin') ?>">
                                            <?= lang('Individual', 'Einzelarbeit') ?>
                                        </span>
                                    <?php
                                        break;
                                    case 'departmental': ?>
                                        <span class="badge block" data-toggle="tooltip" data-title="<?= lang('Authors from the same department of this institute', 'Autoren aus der gleichen Abteilung des Instituts') ?>">
                                            <?= lang('Departmental', 'Abteilungsübergreifend') ?>
                                        </span>
                                    <?php
                                        break;
                                    case 'institutional': ?>
                                        <span class="badge block" data-toggle="tooltip" data-title="<?= lang('Authors from different departments but all from this institute', 'Autoren aus verschiedenen Abteilungen, aber alle vom Institut') ?>">
                                            <?= lang('Institutional', 'Institutionell') ?>
                                        </span>
                                    <?php
                                        break;
                                    case 'contributing': ?>
                                        <span class="badge block" data-toggle="tooltip" data-title="<?= lang('Authors from different institutes with us being middle authors', 'Autoren aus unterschiedlichen Instituten mit uns als Mittelautoren') ?>">
                                            <?= lang('Cooperative (Contributing)', 'Kooperativ (Beitragend)') ?>
                                        </span>
                                    <?php
                                        break;
                                    case 'leading': ?>
                                        <span class="badge block" data-toggle="tooltip" data-title="<?= lang('Authors from different institutes with us being leading authors', 'Autoren aus unterschiedlichen Instituten mit uns als führenden Autoren') ?>">
                                            <?= lang('Cooperative (Leading)', 'Kooperativ (Führend)') ?>
                                        </span>
                                    <?php
                                        break;
                                    default: ?>
                                        <span class="badge block" data-toggle="tooltip" data-title="<?= lang('No author affiliated', 'Autor:innen sind nicht affiliiert') ?>">
                                            <?= lang('None', 'Keine') ?>
                                        </span>
                                <?php
                                        break;
                                }
                                ?>

                            </td>
                        </tr>

                        <?php if ($doc['impact'] ?? false) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Impact', 'Impact') ?>: </span>
                                    <span class="badge"><?= $doc['impact'] ?></span>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($doc['quartile'] ?? false) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Quartile', 'Quartil') ?>: </span>
                                    <span class="quartile <?= $doc['quartile'] ?>"><?= $doc['quartile'] ?></span>
                                </td>
                            </tr>
                        <?php } ?>

                        <!-- <?php if (!empty($projects)) { ?>
            <tr>
                <td>
                <span class="key"><?= lang('Projects', 'Projekte') ?>: </span>
                <?= count($projects) ?>
                </td>
            </tr>
        <?php } ?> -->

                        <?php if ($Settings->featureEnabled('portal')) {
                            $doc['hide'] = $doc['hide'] ?? false;
                            $visible_subtypes = $Settings->getActivitiesPortfolio(true);
                        ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Online Visibility', 'Online-Sichtbarkeit') ?>: </span>
                                    <?php if (!in_array($doc['subtype'], $visible_subtypes)) { ?>
                                        <span class="badge warning" data-toggle="tooltip" data-title="<?= lang('This activity subtype is not visible on the portal due to general settings of your institute.', 'Dieser Aktivitätstyp ist aufgrund genereller Instituts-Einstellungen im Portal nicht sichtbar.') ?>">
                                            <i class="ph ph-eye-slash m-0"></i>
                                            <?= lang('Activity type not visible', 'Aktivitätstyp nicht sichtbar') ?>
                                        </span>
                                    <?php } else if ($edit_perm) { ?>
                                        <div class="custom-switch">
                                            <input type="checkbox" id="hide" <?= $doc['hide'] ? 'checked' : '' ?> name="values[hide]" onchange="hide()">
                                            <label for="hide" id="hide-label">
                                                <?= $doc['hide'] ? lang('Hidden', 'Versteckt') :  lang('Visible', 'Sichtbar')  ?>
                                            </label>
                                        </div>

                                        <script>
                                            function hide() {
                                                $.ajax({
                                                    type: "POST",
                                                    url: ROOTPATH + "/crud/activities/hide",
                                                    data: {
                                                        activity: ACTIVITY_ID
                                                    },
                                                    success: function(response) {
                                                        var hide = $('#hide').prop('checked');

                                                        $('#hide-label').text(hide ? '<?= lang('Hidden', 'Versteckt') ?>' : '<?= lang('Visible', 'Sichtbar') ?>');
                                                        $('#highlight').prop('disabled', hide);
                                                        if (hide) {
                                                            $('#highlight').prop('checked', false);
                                                            $('#highlight-label').text('<?= lang('Normal', 'Normal') ?>');
                                                        }
                                                        toastSuccess(lang('Visibility status changed', 'Sichtbarkeitsstatus geändert'))
                                                    },
                                                    error: function(response) {
                                                        console.log(response);
                                                    }
                                                });
                                            }
                                        </script>


                                    <?php } else { ?>
                                        <?php if ($doc['hide']) { ?>
                                            <span class="badge danger" data-toggle="tooltip" data-title="<?= lang('This activity is hidden on the portal.', 'Diese Aktivität ist auf dem Portal versteckt.') ?>">
                                                <i class="ph ph-eye-slash"></i>
                                                <?= lang('Hidden', 'Versteckt') ?>
                                            </span>
                                        <?php } else { ?>
                                            <span class="badge success" data-toggle="tooltip" data-title="<?= lang('This activity is visible on the portal.', 'Diese Aktivität ist auf dem Portal sichtbar.') ?>">
                                                <i class="ph ph-eye"></i>
                                                <?= lang('Visible', 'Sichtbar') ?>
                                            </span>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ($DB->isUserActivity($doc, $_SESSION['username'], false)) {
                            $disabled = $doc['hide'] ?? false;
                            if ($disabled) {
                                $highlighted = false;
                            } else {
                                $highlights = DB::doc2Arr($USER['highlighted'] ?? []);
                                $highlighted = in_array($id, $highlights);
                            }
                        ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Displayed in your profile', 'Darstellung in deinem Profil') ?>: </span>
                                    <div class="custom-switch">
                                        <input type="checkbox" id="highlight" <?= ($highlighted) ? 'checked' : '' ?> name="values[highlight]" onchange="fav()" <?= $disabled ? 'disabled' : '' ?>>
                                        <label for="highlight" id="highlight-label">
                                            <?= $highlighted ? lang('Highlighted', 'Hervorgehoben') : lang('Normal', 'Normal') ?>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <script>
                                function fav() {
                                    $.ajax({
                                        type: "POST",
                                        url: ROOTPATH + "/crud/activities/fav",
                                        data: {
                                            activity: ACTIVITY_ID
                                        },
                                        dataType: "json",
                                        success: function(response) {
                                            var highlight = $('#highlight').prop('checked');
                                            $('#highlight-label').text(highlight ? '<?= lang('Highlighted', 'Hervorgehoben') ?>' : '<?= lang('Normal', 'Normal') ?>');
                                            toastSuccess(lang('Highlight status changed', 'Hervorhebungsstatus geändert'))
                                        },
                                        error: function(response) {
                                            console.log(response);
                                        }
                                    });
                                }
                            </script>
                        <?php } ?>
                    </tbody>
                </table>

                <?php
                $hidden_fields = ['authors', "editors", "supervisors", "semester-select", 'abstract', 'depts', 'projects', 'title'];
                $empty_fields = [];
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
                    if (array_key_exists($section, $sections)) { ?>
                        <h4 class="table-title"><?= $section_label ?></h4>
                        <table class="table">
                            <tbody>
                                <?php foreach ($sections[$section] as $field_id) {
                                    if (in_array($field_id, $hidden_fields)) continue;
                                    if ($field_id == 'teaching-course' && isset($doc['module_id'])) :
                                        $module = $DB->getConnected('teaching', $doc['module_id']);
                                        $field = [
                                            'key_en' => 'Teaching Module',
                                            'key_de' => 'Lehrveranstaltung',
                                            'value' => $module['module']
                                        ];
                                    elseif ($field_id == 'journal' && isset($doc['journal_id'])) :
                                        $journal = $DB->getConnected('journal', $doc['journal_id']);
                                        $field = [
                                            'key_en' => 'Journal',
                                            'key_de' => 'Journal',
                                            'value' => $journal['journal']
                                        ];
                                    elseif ($Format->get_field($field_id) != '-') :
                                        $names = $Modules->all_modules[$field_id] ?? [];
                                        $field = [
                                            'key_en' => $names['name'] ?? ucfirst($field_id),
                                            'key_de' => $names['name_de'] ?? ucfirst($field_id),
                                            'value' => $Format->get_field($field_id)
                                        ];
                                    endif;

                                    if (empty($field['value']) || $field['value'] == '-') {
                                        $empty_fields[] = $field_id;
                                        continue;
                                    }
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

            </div>
        </div>

        <p id="disclaimer" class="text-muted">
            <?= lang(
                "The content on this page is maintained by the authors.",
                "Die Inhalte auf dieser Seite werden von den Autor:innen selbst gepflegt."
            ); ?>
        </p>
        <script>
            function nav(id) {
                document.querySelectorAll('.pills .btn').forEach(btn => btn.classList.remove('active'));
                document.getElementById(id + '-box').style.display = 'block';
                document.querySelector('.pills .btn[onclick="nav(\'' + id + '\')"]').classList.add('active');
                ['citation', 'bibtex', 'ris'].forEach(box => {
                    if (box !== id) {
                        document.getElementById(box + '-box').style.display = 'none';
                    }
                });
            }
        </script>
    </div>
</div>






<!-- 

<div class="content-container">

    <article class="">
        <div class="mb-20">

            <div class="header d-flex align-items-center justify-content-between flex-wrap">
                <ul class="breadcrumb category" style="--highlight-color:<?= $Format->typeArr['color'] ?? '' ?>">
                    <li><?= $Format->activity_type() ?></li>
                    <li><?= $Format->activity_subtype() ?></li>
                </ul>

                <?php if ($doc['locked'] ?? false) { ?>
                    <span class="btn danger cursor-default" data-toggle="tooltip" data-title="<?= lang('This activity has been locked.', 'Diese Aktivität wurde gesperrt.') ?>">
                        <i class="ph ph-lock text-danger"></i>
                        <?= lang('Locked', 'Gesperrt') ?>
                    </span>
                <?php } ?>
            </div>

            <h1>
                <?= $Format->getTitle('web') ?>
            </h1>

            <p class="lead"><?= $Format->getSubtitle('web') ?></p>


            <?php if ($Settings->featureEnabled('topics')) {
                // echo $Settings->printTopics($doc['topics'] ?? [], 'mb-20');
            } ?>
            <div class="btn-toolbar mt-20">
                <?php if (($edit_perm) && (!$locked || $Settings->hasPermission('activities.edit-locked'))) { ?>
                    <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn primary filled">
                        <i class="ph ph-pencil-simple-line mr-5"></i>
                        <?= lang('Edit', 'Bearbeiten') ?>
                    </a>
                <?php } ?>
                <?php if ($Settings->featureEnabled('portal')) { ?>
                    <a class="btn primary outline" href="<?= ROOTPATH ?>/preview/activity/<?= $id ?>">
                        <i class="ph ph-eye mr-5"></i>
                        <?= lang('Preview', 'Vorschau') ?>
                    </a>
                <?php } ?>

                <?php if ($user_activity && $locked && empty($doc['end'] ?? null) && $ongoing) { ?>
                    <div class="dropdown">
                        <button class="btn primary outline" data-toggle="dropdown" type="button" id="update-end-date" aria-haspopup="true" aria-expanded="false">
                            <i class="ph ph-calendar-check"></i>
                            <?= lang('End activity', 'Beenden') ?> <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-center w-200" aria-labelledby="update-end-date">
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

                <button class="btn">
                    <?= lang('More Actions', 'Weitere Aktionen') ?>
                    <i class="ph ph-caret-down ml-5"></i>
                </button>

            </div>
        </div>
    </article>



    <nav class="tabs">
        <a onclick="navigate('general')" id="btn-general" class="btn active">
            <i class="ph ph-info" aria-hidden="true"></i>
            <?= lang('General', 'Allgemein') ?>
        </a>

        <?php if ($guests_involved) { ?>
            <a onclick="navigate('guests')" id="btn-guests" class="btn">
                <i class="ph ph-user-plus" aria-hidden="true"></i>
                <?= lang('Guests', 'Gäste') ?>
                <span class="index"><?= count($guests) ?></span>
            </a>
        <?php } ?>


        <?php if (count($doc['authors']) > 1) { ?>
            <a onclick="navigate('coauthors')" id="btn-coauthors" class="btn">
                <i class="ph ph-users" aria-hidden="true"></i>
                <?= lang('Authors', 'Autoren') ?>
                <span class="index"><?= count($doc['authors']) ?></span>
            </a>
        <?php } ?>

        <?php
        if ($upload_possible):
            $count_files = count($documents);
        ?>
            <a onclick="navigate('files')" id="btn-files" class="btn">
                <i class="ph ph-files" aria-hidden="true"></i>
                <?= lang('Files', 'Dateien') ?>
                <span class="index"><?= $count_files ?></span>
            </a>
        <?php endif; ?>

        <?php if ($Settings->featureEnabled('concepts')) { ?>
            <?php
            $count_concepts = count($doc['concepts'] ?? []);
            if ($count_concepts) :
            ?>
                <a onclick="navigate('concepts')" id="btn-concepts" class="btn">
                    <i class="ph ph-lightbulb" aria-hidden="true"></i>
                    <?= lang('Concepts', 'Konzepte') ?>
                    <span class="index"><?= $count_concepts ?></span>
                </a>
            <?php endif; ?>
        <?php } ?>


        <?php
        $count_history = count($doc['history'] ?? []);
        if ($count_history) :
        ?>
            <a onclick="navigate('history')" id="btn-history" class="btn">
                <i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>
                <?= lang('History', 'Historie') ?>
                <span class="index"><?= $count_history ?></span>
            </a>
        <?php endif; ?>

        <?php if ($Settings->hasPermission('raw-data') || isset($_GET['verbose'])) { ?>
            <a onclick="navigate('raw')" id="btn-raw" class="btn">
                <i class="ph ph-code" aria-hidden="true"></i>
                <?= lang('Raw data', 'Rohdaten')  ?>
            </a>
        <?php } ?>
    </nav>

    <section id="raw" style="display:none" class="box mt-0">
        <div class="content">

            <h2 class="title">
                <?= lang('Raw data', 'Rohdaten') ?>
            </h2>

            <?= lang('Raw data as they are stored in the database.', 'Die Rohdaten, wie sie in der Datenbank gespeichert werden.') ?>

        </div>

        <div class="overflow-x-scroll bg-light p-20 border-top">
            <pre><?= htmlspecialchars(json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>

    </section>



    <section id="general">
        <div class="box mt-0">
            <div class="content">

                <div class="">

                    <button class="btn small float-right" onclick="copyToClipboard()" data-toggle="tooltip" data-title="<?= lang('Copy to clipboard', 'In die Zwischenablage kopieren') ?>">
                        <i class="ph ph-clipboard" aria-label="Copy to clipboard"></i>
                    </button>

                    <span class="key"><?= lang('Formatted entry', 'Formatierter Eintrag') ?></span>
                    <div id="formatted"><?= $doc['rendered']['print'] ?></div>

                </div>
            </div>



            <table class="table simple" id="detail-table">

                <tr>
                    <td> </td>
                </tr>
                <?php
                $Format->usecase = "list";

                $sections = [];
                foreach ($fields as $field) {
                    if (!array_key_exists($field, $typeFields)) {
                        $section = 'others';
                    } else {
                        $section = $Modules->all_modules[$field]['section'] ?? '';
                    }
                    if (empty($section)) continue; // if no section is defined, do not show the field
                    $sections[$section][] = $field;
                }
                // dump($sections);

                $emptyModules = [];

                foreach ($fields as $module) {
                    if (in_array($module, ["semester-select", "event-select", "projects"])) continue;
                ?>
                    <?php if ($module == 'teaching-course' && isset($doc['module_id'])) :
                        $module = $DB->getConnected('teaching', $doc['module_id']);
                        if (empty($module)) {
                            $emptyModules[] = 'teaching-course';
                            continue;
                        }
                    ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Teaching module', 'Lehrveranstaltung') ?></span>

                                <a class="module " href="<?= ROOTPATH ?>/teaching#<?= $doc['module_id'] ?>">
                                    <h5 class="m-0"><span class="highlight-text"><?= $module['module'] ?></span> <?= $module['title'] ?></h5>
                                    <span class="text-muted-"><?= $module['affiliation'] ?></span>
                                </a>
                            </td>
                        </tr>

                    <?php elseif ($module == 'journal' && isset($doc['journal_id'])) :
                        $journal = $DB->getConnected('journal', $doc['journal_id']);
                        if (empty($journal)) {
                            $emptyModules[] = 'journal';
                            continue;
                        }
                    ?>

                        <tr>
                            <td>
                                <span class="key"><?= $Settings->journalLabel() ?></span>

                                <a class="module " href="<?= ROOTPATH ?>/journal/view/<?= $doc['journal_id'] ?>">
                                    <h6 class="m-0"><?= $journal['journal'] ?></h6>
                                    <span class="float-right text-muted-"><?= $journal['publisher'] ?></span>
                                    <span class="text-muted-">
                                        ISSN: <?= print_list($journal['issn']) ?>
                                        <br>
                                        Impact:
                                        <?= $doc['impact'] ?? 'unknown' ?>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php elseif ($module == 'conference' && isset($doc['conference_id'])) :
                        $conference = $DB->getConnected('conference', $doc['conference_id']);
                    ?>

                        <tr>
                            <td>
                                <span class="key">Event</span>
                                <?php if (empty($conference)) { ?>
                                    <div><?= $doc['conference'] ?? '' ?></div>
                                    <span class="text-danger">
                                        <?= lang('This event has been deleted.', 'Diese Veranstaltung wurde gelöscht.') ?>
                                    </span>
                                <?php } else { ?>

                                    <div class="module ">
                                        <h6 class="m-0">
                                            <a href="<?= ROOTPATH ?>/conferences/view/<?= $doc['conference_id'] ?>">
                                                <?= $conference['title'] ?>
                                            </a>
                                        </h6>
                                        <div class="text-muted mb-10"><?= $conference['title_full'] ?></div>
                                        <ul class="horizontal mb-0">
                                            <li>
                                                <b><?= lang('Location', 'Ort') ?></b>: <?= $conference['location'] ?>
                                            </li>
                                            <li>
                                                <b><?= lang('Date', 'Datum') ?></b>: <?= fromToDate($conference['start'], $conference['end']) ?>
                                            </li>
                                            <li>
                                                <a href="<?= $conference['url'] ?>" target="_blank">
                                                    <i class="ph ph-link"></i>
                                                    <?= lang('Website', 'Website') ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php else :
                        $val = $Format->get_field($module);
                        if (empty($val) || $val == '-') {
                            $emptyModules[] = $module;
                            continue;
                        }
                    ?>

                        <tr>
                            <td>
                                <span class="key"><?= $Modules->get_name($module) ?></span>
                                <?= $Format->get_field($module) ?>
                            </td>
                        </tr>

                    <?php endif; ?>

                <?php } ?>

                <?php if ($Settings->featureEnabled('tags') && $edit_perm) : ?>
                    <tr>
                        <td>
                            <?php if ($edit_perm && $Settings->hasPermission('activities.tags')) { ?>
                                <a href="#add-tags" class="btn small float-right">
                                    <i class="ph ph-edit"></i>
                                    <?= lang('Edit', 'Bearbeiten') ?>
                                </a>
                            <?php } ?>
                            <span class="key"><?= $tagName ?></span>
                            <p id="tag-list" class="mt-5">
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
                                } else {
                                    echo lang('No ' . $tagName . ' assigned yet.', 'Noch keine ' . $tagName . ' vergeben.');
                                }
                                ?>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php
                // check for empty modules and show a short info
                if (count($emptyModules)) {
                    $emptyModules = array_unique($emptyModules);
                ?>
                    <tr>
                        <td>
                            <span class="key text-danger"><?= lang('The following fields are not filled in', 'Die folgenden Felder sind nicht ausgefüllt') ?>:</span>
                            <?php foreach ($emptyModules as $key) { ?>
                                <span class="badge mr-5 mb-5"><?= $Modules->get_name($key) ?></span>
                            <?php } ?>

                        </td>
                    </tr>
                <?php } ?>



                <?php if (($edit_perm) && isset($doc['comment'])) : ?>
                    <tr class="text-muted">
                        <td>
                            <span class="key" style="text-decoration: 1px dotted underline;" data-toggle="tooltip" data-title="<?= lang('Only visible for authors and editors.', 'Nur sichtbar für Autoren und Editor-MA.') ?>">
                                <?= lang('Comment', 'Kommentar') ?>:
                            </span>

                            <?= $doc['comment'] ?>
                        </td>
                    </tr>
                <?php endif; ?>


            </table>

        </div>
    </section>

</div>




<script>
    function copyToClipboard() {
        var text = $('#formatted').text()
        navigator.clipboard.writeText(text)
        toastSuccess('Query copied to clipboard.')
    }
</script> -->