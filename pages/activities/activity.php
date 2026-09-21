<?php

/**
 * Page to see details on one activity
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /activities/view/<activity_id>
 *
 * @package     OSIRIS
 * @since       1.0 
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};

// User context
$user_units = DB::doc2Arr($USER['units'] ?? []);
if (!empty($user_units)) {
    $user_units = array_column($user_units, 'unit');
}

if ($edit_perm) {
    include_once BASEPATH . '/pages/activities/activity-modals.php';
}
?>

<style>
    .pills {
        position: sticky;
        top: 8rem;
        z-index: 10;
    }
</style>

<style>
    [class^="col-"] .box {
        margin: 0;
        /* height: 100%; */
    }

    .btn-toolbar {
        margin: 0 0 1rem;
        /* background-color: white;
        padding: .5rem;
        border-radius: .5rem; */
    }

    .show-on-hover:hover .invisible {
        visibility: visible !important;
    }

    .badge.block {
        display: block;
        text-align: center;
    }
</style>

<div class="content-container">
    <?php
    if (function_exists('printMsg') && (isset($_GET['msg']) || isset($_GET['error'])) || isset($_SESSION['msg'])) {
        printMsg();
    }
    ?>

    <?php

    if (isset($_GET['msg']) && $_GET['msg'] == 'add-success') { ?>


        <?php if ($Settings->featureEnabled('projects') && !empty($projects)) { ?>
            <div class="alert success mb-20">
                <h3 class="title">
                    <?= lang('activities.projects_connected') ?>
                </h3>
                <?= lang('activities.this_activity_was_automatically_connected_to_projects_based_on_funding_numb') ?>
                <br>
                <a href="#projects" class="btn success">
                    <i class="ph ph-tree-structure"></i>
                    <?= lang('common.projects') ?>
                </a>
            </div>
        <?php } ?>
    <?php } ?>

    <?php include_once BASEPATH . '/header-editor.php'; ?>

    <script>
        const ACTIVITY_ID = '<?= $id ?>';
        const TYPE = '<?= $doc['type'] ?>';
    </script>

    <script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>

    <script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/activity.js?v=<?= OSIRIS_BUILD ?>"></script>


    <div class="btn-toolbar">
        <?php if ($doc['locked'] ?? false) { ?>
            <span class="badge danger cursor-default mr-10 border-danger" data-toggle="tooltip" data-title="<?= lang('common.this_activity_has_been_locked') ?>">
                <i class="ph ph-lock text-danger"></i>
                <?= lang('common.locked') ?>
            </span>
        <?php } ?>

        <?php if ($Settings->hasPermission('activities.lock')) { ?>
            <form action="<?= ROOTPATH ?>/crud/activities/<?= $id ?>/lock" method="post">
                <?php if ($doc['locked'] ?? false) { ?>
                    <button class="btn text-success border-success mr-10" type="submit">
                        <i class="ph ph-lock-open"></i>
                        <?= lang('common.unlock') ?>
                    </button>
                <?php } else { ?>
                    <button class="btn text-danger border-danger mr-10" type="submit">
                        <i class="ph ph-lock"></i>
                        <?= lang('common.lock') ?>
                    </button>
                <?php } ?>
            </form>
        <?php } ?>

        <div class="btn-group">
            <?php if (($edit_perm) && (!$locked || $Settings->hasPermission('activities.edit-locked'))) { ?>
                <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn primary outline">
                    <i class="ph ph-pencil-simple-line"></i>
                    <?= lang('action.edit') ?>
                </a>
            <?php } ?>
            <?php if (!in_array($doc['type'], ['publication'])) { ?>
                <a href="<?= ROOTPATH ?>/activities/copy/<?= $id ?>" class="btn primary outline">
                    <i class="ph ph-copy"></i>
                    <?= lang('common.copy') ?>
                </a>
            <?php } ?>
            <?php if ($Settings->featureEnabled('tags')) { ?>
                <a href="#add-tags" class="btn primary outline">
                    <i class="ph ph-tag"></i>
                    <?= $tagLabel ?>
                </a>
            <?php } ?>

        </div>


        <div class="btn-group">
            <div class=" dropdown with-arrow btn-group ">
                <button class="btn primary outline" data-toggle="dropdown" type="button" id="download-btn" aria-haspopup="true" aria-expanded="false">
                    <i class="ph ph-download"></i> Download
                    <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="download-btn">
                    <div class="content">
                        <button class="btn primary outline" onclick="addToCart(this, '<?= $id ?>')">
                            <i class="<?= (in_array($id, $cart)) ? 'ph ph-duotone ph-basket ph-basket-plus text-success' : 'ph ph-basket ph-basket-plus' ?>"></i>
                            <?= lang('common.collect') ?>
                        </button>
                        <hr>
                        <form action="<?= ROOTPATH ?>/download" method="post">

                            <input type="hidden" name="filter[id]" value="<?= $id ?>">

                            <div class="form-group">

                                <?= lang('common.highlight') ?>

                                <div class="custom-radio ml-10">
                                    <input type="radio" name="highlight" id="highlight-user" value="user" checked="checked">
                                    <label for="highlight-user"><?= lang('common.me') ?></label>
                                </div>

                                <div class="custom-radio ml-10">
                                    <input type="radio" name="highlight" id="highlight-aoi" value="aoi">
                                    <label for="highlight-aoi"><?= $Settings->get('affiliation') ?><?= lang('common.authors') ?></label>
                                </div>

                                <div class="custom-radio ml-10">
                                    <input type="radio" name="highlight" id="highlight-none" value="">
                                    <label for="highlight-none"><?= lang('common.none_download') ?></label>
                                </div>

                            </div>


                            <div class="form-group">

                                <?= lang('common.file_format') ?>

                                <div class="custom-radio ml-10">
                                    <input type="radio" name="format" id="format-word" value="word" checked="checked">
                                    <label for="format-word">Word</label>
                                </div>

                                <div class="custom-radio ml-10">
                                    <input type="radio" name="format" id="format-bibtex" value="bibtex">
                                    <label for="format-bibtex">BibTeX</label>
                                </div>

                            </div>
                            <button class="btn primary outline">Download</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="dropdown">
            <button class="btn" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                <?= lang('common.more_actions') ?> <i class="ph ph-dots-three-outline ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdown-1">
                <!-- <h6 class="header">Header</h6> -->
                <div class="content">
                    <a href="?view=new" class="btn block">
                        <i class="ph ph-lightning m-0"></i>
                        <?= lang('activities.modern_view') ?>
                    </a>
                    <?php if (!in_array($doc['type'], ['publication'])) { ?>
                        <a href="<?= ROOTPATH ?>/activities/copy/<?= $id ?>" class="btn block">
                            <i class="ph ph-copy"></i>
                            <?= lang('common.copy') ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>


        <?php if ($Settings->featureEnabled('portal')) { ?>
            <a class="btn primary outline ml-auto" href="<?= ROOTPATH ?>/preview/activity/<?= $id ?>">
                <i class="ph ph-eye ph-fw"></i>
                <?= lang('common.preview') ?>
            </a>
        <?php } ?>
    </div>

    <!-- HEAD -->
    <div class="my-20 pt-20">

        <ul class="breadcrumb category" style="--highlight-color:<?= $Format->typeArr['color'] ?? '' ?>">
            <li><?= $Format->activity_type() ?></li>
            <li><?= $Format->activity_subtype() ?></li>
        </ul>

        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1> <?= $Format->getTitle('web') ?></h1>
                <p class="lead"><?= $Format->getSubtitle('web') ?></p>
            </div>

            <?php if ($Settings->featureEnabled('altmetrics')) {
                $displayAltmetric = true;
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
                if ($displayAltmetric) {
                    $detailsAttr = '';
                    foreach ($details as $k => $v) {
                        $detailsAttr .= " $k='$v' ";
                    }
            ?>
                    <script type='text/javascript' src='https://embed.altmetric.com/assets/embed.js'></script>
                    <div class='altmetric-embed' <?= $detailsAttr ?>></div>
            <?php }
            } ?>

        </div>
    </div>

    <!-- check for basic things -->
    <?php
    if (!isset($doc['authors']) || empty($doc['authors'])) {
        $doc['authors'] = [];
        if ((!isset($doc['editors']) || empty($doc['editors'])) && (!isset($doc['supervisors']) || empty($doc['supervisors']))) {
    ?>
            <div class="alert danger mb-20">
                <h3 class="title">
                    <?= lang('common.no_authors_or_editors') ?>
                </h3>
                <p>
                    <?= lang('common.this_activity_has_no_authors_or_editors_assigned_please_add_at_least_one_au') ?>
                </p>
            </div>
    <?php
        }
    }
    ?>




    <!-- check for date, at least month and year must be given -->
    <?php
    if (!isset($doc['year']) || empty($doc['year']) || !isset($doc['month']) || empty($doc['month'])) {
        // if no date is given, show an error
    ?>
        <div class="alert danger mb-20">
            <h3 class="title">
                <?= lang('common.no_time_specified') ?>
            </h3>
            <p>
                <?= lang('common.this_activity_has_no_time_specified_please_add_at_least_month_and_year_to_t') ?>
            </p>
        </div>
    <?php
    }
    ?>



    <!-- show research topics -->
    <?php if ($Settings->featureEnabled('topics')) {
        echo $Settings->printTopics($doc['topics'] ?? [], 'mb-20');
    } ?>


    <div class="d-flex">

        <div class="mr-10 badge bg-white">
            <small><?= lang('common.date') ?>: </small>
            <br />
            <span class="badge"><?= $Format->format_date($doc) ?></span>
        </div>

        <div class="mr-10 badge bg-white">
            <small><?= $Settings->get('affiliation') ?>: </small>
            <br />
            <?php

            if ($doc['affiliated'] ?? true) { ?>
                <div class="badge success" data-toggle="tooltip" data-title="<?= lang('common.at_least_on_author_of_this_activity_has_an_affiliation_with_the_institute') ?>">
                    <!-- <i class="ph ph-handshake m-0"></i> -->
                    <?= lang('common.affiliated') ?>
                </div>
            <?php } else { ?>
                <div class="badge danger" data-toggle="tooltip" data-title="<?= lang('common.none_of_the_authors_has_an_affiliation_to_the_institute') ?>">
                    <!-- <i class="ph ph-hand-x m-0"></i> -->
                    <?= lang('common.not_affiliated') ?>
                </div>
            <?php } ?>
        </div>

        <!-- cooperative -->
        <div class="mr-10 badge bg-white">
            <small><?= lang('activities.cooperation') ?>: </small>
            <br />
            <?php
            switch ($doc['cooperative'] ?? '-') {
                case 'individual': ?>
                    <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('common.only_one_author') ?>">
                        <?= lang('common.individual') ?>
                    </span>
                <?php
                    break;
                case 'departmental': ?>
                    <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('activities.authors_from_the_same_department_of_this_institute') ?>">
                        <?= lang('activities.departmental_activity') ?>
                    </span>
                <?php
                    break;
                case 'institutional': ?>
                    <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('activities.authors_from_different_departments_but_all_from_this_institute') ?>">
                        <?= lang('common.institutional') ?>
                    </span>
                <?php
                    break;
                case 'contributing': ?>
                    <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('activities.authors_from_different_institutes_with_us_being_middle_authors') ?>">
                        <?= lang('common.cooperative_contributing') ?>
                    </span>
                <?php
                    break;
                case 'leading': ?>
                    <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('activities.authors_from_different_institutes_with_us_being_leading_authors') ?>">
                        <?= lang('common.cooperative_leading') ?>
                    </span>
                <?php
                    break;
                default: ?>
                    <span class="badge block" data-toggle="tooltip" data-title="<?= lang('common.no_author_affiliated') ?>">
                        <?= lang('common.none') ?>
                    </span>
            <?php
                    break;
            }
            ?>

        </div>

        <?php if ($doc['impact'] ?? false) { ?>
            <div class="mr-10 badge bg-white">
                <small><?= $Settings->impactLabel() ?>: </small>
                <br />
                <span class="badge danger"><?= $doc['impact'] ?></span>
            </div>
        <?php } ?>
        <?php if ($doc['quartile'] ?? false) { ?>
            <div class="mr-10 badge bg-white">
                <small><?= lang('common.quartile') ?>: </small>
                <br />
                <span class="quartile <?= $doc['quartile'] ?>"><?= $doc['quartile'] ?></span>
            </div>
        <?php } ?>

        <?php if (!empty($openalex) && isset($openalex['cited_by_count'])) {
            $fetched_at = isset($openalex['fetched_at']) ? date('d.m.Y', strtotime($openalex['fetched_at'])) : '-';
        ?>
            <div class="mr-10 badge bg-white">
                <small><?= lang('common.citations') ?>: </small>
                <br />
                <span class="badge" data-toggle="tooltip" data-title="<?= lang('common.last_updated') ?>: <?= $fetched_at ?>"><?= $openalex['cited_by_count'] ?></span>
            </div>
        <?php } ?>


        <?php if (!empty($projects)) { ?>
            <div class="mr-10 badge bg-white">
                <small><?= lang('common.projects') ?>: </small>
                <br />
                <a href="#projects" class="badge primary outline">
                    <i class="ph ph-tree-structure"></i>
                    <?= count($projects) ?>
                    <?= lang('common.projects') ?>
                </a>
            </div>
        <?php } ?>

        <?php if ($Settings->featureEnabled('portal')) {
            $doc['hide'] = $doc['hide'] ?? false;
        ?>
            <div class="mr-10 badge bg-white">
                <small><?= lang('common.online_visibility') ?>: </small>
                <br />
                <?php if (!in_array($doc['subtype'], $visible_subtypes)) { ?>
                    <span class="badge warning" data-toggle="tooltip" data-title="<?= lang('activities.this_activity_subtype_is_not_visible_on_the_portal_due_to_general_settings') ?>">
                        <i class="ph ph-eye-slash m-0"></i>
                        <?= lang('common.activity_type_not_visible') ?>
                    </span>
                <?php } else if ($edit_perm) {
                    $highlighted_by = $osiris->persons->count(['highlighted' => strval($id), 'username' => ['$ne' => $_SESSION['username']]]);
                ?>
                    <div class="custom-switch d-inline-block">
                        <input type="checkbox" id="hide" <?= $doc['hide'] ? 'checked' : '' ?> name="values[hide]" onchange="hide()">
                        <label for="hide" id="hide-label">
                            <?= $doc['hide'] ? lang('common.hidden') :  lang('common.visible')  ?>
                        </label>
                    </div>

                    <?php if ($highlighted_by > 0) { ?>
                        <span data-toggle="tooltip" data-title="<?= lang('common.this_activity_is_highlighted_by_highlighted_by_other_person_s', replace: ['highlighted_by' => $highlighted_by]) ?>"><i class="ph ph-star text-signal"></i></span>
                    <?php } ?>

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

                                    $('#hide-label').text(hide ? '<?= lang('common.hidden') ?>' : '<?= lang('common.visible') ?>');
                                    $('#highlight').prop('disabled', hide);
                                    if (hide) {
                                        $('#highlight').prop('checked', false);
                                        $('#highlight-label').text('<?= lang('common.normal') ?>');
                                    }
                                    toastSuccess(<?= json_encode(lang('activities.visibility_status_changed'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)
                                },
                                error: function(response) {
                                    console.log(response);
                                }
                            });
                        }
                    </script>


                <?php } else { ?>
                    <?php if ($doc['hide']) { ?>
                        <span class="badge danger" data-toggle="tooltip" data-title="<?= lang('activities.this_activity_is_hidden_on_the_portal') ?>">
                            <i class="ph ph-eye-slash"></i>
                            <?= lang('common.hidden') ?>
                        </span>
                    <?php } else { ?>
                        <span class="badge success" data-toggle="tooltip" data-title="<?= lang('activities.this_activity_is_visible_on_the_portal') ?>">
                            <i class="ph ph-eye"></i>
                            <?= lang('common.visible') ?>
                        </span>
                    <?php } ?>
                <?php } ?>
            </div>
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
            <div class="mr-10 badge bg-white">
                <small><?= lang('activities.displayed_in_your_profile') ?>: </small>
                <br />
                <div class="custom-switch">
                    <input type="checkbox" id="highlight" <?= ($highlighted) ? 'checked' : '' ?> name="values[highlight]" onchange="fav()" <?= $disabled ? 'disabled' : '' ?>>
                    <label for="highlight" id="highlight-label">
                        <?= $highlighted ? lang('common.highlighted') : lang('common.normal') ?>
                    </label>
                </div>
            </div>
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
                            $('#highlight-label').text(highlight ? '<?= lang('common.highlighted') ?>' : '<?= lang('common.normal') ?>');
                            toastSuccess(<?= json_encode(lang('activities.highlight_status_changed'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)
                        },
                        error: function(response) {
                            console.log(response);
                        }
                    });
                }
            </script>
        <?php } ?>

    </div>

    <!-- TAB AREA -->

    <nav class="pills mt-20 mb-0" id="navigation">
        <a onclick="navigate('general')" id="btn-general" class="btn active">
            <i class="ph ph-info" aria-hidden="true"></i>
            <?= lang('common.general') ?>
        </a>

        <?php if ($guests_involved) { ?>
            <a onclick="navigate('guests')" id="btn-guests" class="btn">
                <i class="ph ph-user-plus" aria-hidden="true"></i>
                <?= lang('common.guests') ?>
                <span class="index"><?= count($guests) ?></span>
            </a>
        <?php } ?>


        <?php if (count($doc['authors']) > 1) { ?>
            <a onclick="navigate('coauthors')" id="btn-coauthors" class="btn">
                <i class="ph ph-users" aria-hidden="true"></i>
                <?= lang('common.coauthors') ?>
                <span class="index"><?= count($doc['authors']) ?></span>
            </a>
        <?php } ?>

        <a onclick="navigate('activities')" id="btn-activities" class="btn">
            <i class="ph ph-plugs" aria-hidden="true"></i>
            <?= lang('common.activities') ?>
            <span class="index"><?= count($connected_activities) ?></span>
        </a>

        <?php if ($Settings->featureEnabled('projects')) { ?>
            <?php
            $count_projects = count($projects);
            if ($count_projects) :
            ?>
                <a onclick="navigate('projects')" id="btn-projects" class="btn">
                    <i class="ph ph-tree-structure" aria-hidden="true"></i>
                    <?= lang('common.projects') ?>
                    <span class="index"><?= $count_projects ?></span>
                </a>

            <?php else : ?>
                <a href="#projects" class="btn">
                    <i class="ph ph-plus-circle"></i>
                    <?= lang('activities.add_projects') ?>
                </a>
            <?php endif; ?>
        <?php } ?>

        <?php if ($Settings->featureEnabled('infrastructures')) { ?>
            <?php
            $count_infrastructures = count($infrastructures);
            if ($count_infrastructures) :
            ?>
                <a onclick="navigate('infrastructures')" id="btn-infrastructures" class="btn">
                    <i class="ph ph-cube-transparent" aria-hidden="true"></i>
                    <?= $Settings->infrastructureLabel() ?>
                    <span class="index"><?= $count_infrastructures ?></span>
                </a>

            <?php else : ?>
                <a href="#infrastructures" class="btn">
                    <i class="ph ph-plus-circle"></i>
                    <?= lang('activities.add_infrastructures') ?>
                </a>
            <?php endif; ?>
        <?php } ?>

        <?php
        if ($upload_possible):
            $count_files = count($files);
        ?>
            <a onclick="navigate('files')" id="btn-files" class="btn">
                <i class="ph ph-files" aria-hidden="true"></i>
                <?= lang('common.files') ?>
                <span class="index"><?= $count_files ?></span>
            </a>
        <?php endif; ?>

        <?php if ($Settings->featureEnabled('spectrum') && isset($doc['openalex'])) { ?>
            <?php
            $count_spectrum = count($spectrum);
            ?>
            <a onclick="navigate('spectrum')" id="btn-spectrum" class="btn">
                <i class="ph ph-lightbulb" aria-hidden="true"></i>
                <?= lang('common.research_spectrum') ?>
                <span class="index"><?= $count_spectrum ?></span>
            </a>
        <?php } ?>


        <?php
        $count_history = count($doc['history'] ?? []);
        if ($count_history) :
        ?>
            <a onclick="navigate('history')" id="btn-history" class="btn">
                <i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>
                <?= lang('common.history') ?>
                <span class="index"><?= $count_history ?></span>
            </a>
        <?php endif; ?>

        <?php if ($Settings->hasPermission('raw-data') || isset($_GET['verbose'])) { ?>
            <a onclick="navigate('raw')" id="btn-raw" class="btn">
                <i class="ph ph-code" aria-hidden="true"></i>
                <?= lang('common.raw_data')  ?>
            </a>
        <?php } ?>

    </nav>



    <section id="raw" style="display:none">

        <h2 class="title">
            <?= lang('common.raw_data') ?>
        </h2>

        <?= lang('common.raw_data_as_they_are_stored_in_the_database') ?>

        <div class="box padded overflow-x-scroll">
            <pre><?= e(json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>

    </section>

    <section id="general">
        <div class="row row-eq-spacing-lg">
            <div class="col-lg-6">

                <div class="btn-toolbar float-sm-right">
                    <?php if (($edit_perm) && (!$locked || $Settings->hasPermission('activities.edit-locked'))) { ?>
                        <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn secondary">
                            <i class="ph ph-pencil-simple-line"></i>
                            <?= lang('action.edit') ?>
                        </a>
                    <?php } ?>


                    <?php if (!in_array($doc['type'], ['publication'])) { ?>
                        <a href="<?= ROOTPATH ?>/activities/copy/<?= $id ?>" class="btn secondary">
                            <i class="ph ph-copy"></i>
                            <?= lang('activities.add_a_copy') ?>
                        </a>
                    <?php } ?>


                    <?php if ($user_activity && $locked && empty($doc['end'] ?? null) && $ongoing) { ?>
                        <!-- End user activity even if activity is locked -->
                        <div class="dropdown">
                            <button class="btn secondary" data-toggle="dropdown" type="button" id="update-end-date" aria-haspopup="true" aria-expanded="false">
                                <i class="ph ph-calendar-check"></i>
                                <?= lang('common.end_activity') ?> <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-center w-200" aria-labelledby="update-end-date">
                                <form action="<?= ROOTPATH . "/crud/activities/update/" . $id ?>" method="POST" class="content">
                                    <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities/view/" . $id ?>">
                                    <div class="form-group">
                                        <label for="date_end"><?= lang('common.activity_ended_at') ?></label>
                                        <input type="date" class="form-control" name="values[end]" id="date_end" value="<?= valueFromDateArray($doc['end'] ?? null) ?>" required>
                                    </div>
                                    <button class="btn btn-block" type="submit"><?= lang('action.save') ?></button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>

                </div>

                <h2 class="mt-0">Details</h2>

                <script>
                    function copyToClipboard() {
                        var text = $('#formatted').text()
                        navigator.clipboard.writeText(text)
                        toastSuccess('Query copied to clipboard.')
                    }
                </script>
                <table class="table" id="detail-table">

                    <tr>
                        <td>
                            <button class="btn small float-right" onclick="copyToClipboard()" data-toggle="tooltip" data-title="<?= lang('common.copy_to_clipboard') ?>">
                                <i class="ph ph-clipboard" aria-label="Copy to clipboard"></i>
                            </button>

                            <span class="key"><?= lang('activities.formatted_entry') ?></span>
                            <div id="formatted"><?= $doc['rendered']['print'] ?></div>
                        </td>
                    </tr>

                    <?php if ($Settings->hasPermission('activities.exclude')) {
                        $exclude = $doc['exclude_from_reports'] ?? false;
                    ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('common.include_in_reports') ?>: </span>
                                <div id="exclude-toggle" class="btn-group" role="group" aria-label="Exclude toggle">
                                    <button type="button" class="btn small <?= $exclude ? '' : 'active' ?>"
                                        id="btn-include" onclick="toggleExclude(false)"
                                        data-toggle="tooltip" data-title="<?= lang('common.will_be_included_in_all_reports_and_analytics') ?>"
                                        style="--blue-color: var(--success-color); --blue-color-20: var(--success-color-20);">
                                        <i class="ph ph-check-circle"></i>
                                        <?= lang('common.include') ?>
                                    </button>
                                    <button type="button" class="btn small <?= $exclude ? 'active' : '' ?>"
                                        id="btn-exclude" onclick="toggleExclude(true)"
                                        data-toggle="tooltip" data-title="<?= lang('common.will_be_excluded_from_all_reports_and_analytics') ?>"
                                        style="--blue-color: var(--danger-color); --blue-color-20: var(--danger-color-20);">
                                        <i class="ph ph-prohibit"></i>
                                        <?= lang('common.exclude') ?>
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
                                                    toastError(<?= json_encode(lang('common.failed_to_update_the_activity_please_try_again'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
                                                    return;
                                                }
                                                if (exclude) {
                                                    toastSuccess(<?= json_encode(lang('common.this_activity_is_now_excluded_from_reports_and_analytics'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
                                                } else {
                                                    toastSuccess(<?= json_encode(lang('common.this_activity_is_now_included_in_reports_and_analytics'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
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

                    <?php
                    $Format->usecase = "list";

                    $emptyModules = [];

                    foreach ($typeModules as $module) {
                        if (str_ends_with($module, '*')) $module = str_replace('*', '', $module);
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
                                    <span class="key"><?= lang('common.teaching_module') ?></span>

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
                                            <?= $Settings->impactLabel() ?>:
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
                                            <?= lang('common.this_event_has_been_deleted') ?>
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
                                                    <b><?= lang('common.location') ?></b>: <?= $conference['location'] ?>
                                                </li>
                                                <li>
                                                    <b><?= lang('common.date') ?></b>: <?= fromToDate($conference['start'], $conference['end']) ?>
                                                </li>
                                                <li>
                                                    <a href="<?= $conference['url'] ?>" target="_blank">
                                                        <i class="ph ph-link"></i>
                                                        <?= lang('common.website') ?>
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

                    <!-- tags -->
                    <?php if ($Settings->featureEnabled('tags') && $edit_perm) : ?>
                        <tr>
                            <td>
                                <?php if ($edit_perm && $Settings->hasPermission('activities.tags')) { ?>
                                    <a href="#add-tags" class="btn small float-right">
                                        <i class="ph ph-edit"></i>
                                        <?= lang('action.edit') ?>
                                    </a>
                                <?php } ?>
                                <span class="key"><?= $tagLabel ?></span>
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
                                        echo lang('common.no_taglabel_assigned_yet', replace: ['tagLabel' => $tagLabel]);
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
                                <span class="key text-danger"><?= lang('common.the_following_fields_are_not_filled_in') ?>:</span>
                                <?php foreach ($emptyModules as $key) { ?>
                                    <span class="badge mr-5 mb-5"><?= $Modules->get_name($key) ?></span>
                                <?php } ?>

                            </td>
                        </tr>
                    <?php } ?>



                    <?php if (($edit_perm) && isset($doc['comment'])) : ?>
                        <tr class="text-muted">
                            <td>
                                <span class="key" style="text-decoration: 1px dotted underline;" data-toggle="tooltip" data-title="<?= lang('common.only_visible_for_authors_and_editors') ?>">
                                    <?= lang('common.comment') ?>:
                                </span>

                                <?= $doc['comment'] ?>
                            </td>
                        </tr>
                    <?php endif; ?>


                </table>


                <div class="alert danger mt-20 py-20">
                    <h2 class="title">
                        <?= lang('action.delete') ?>
                    </h2>
                    <?php

                    // $in_quarter = inCurrentQuarter($doc['year'], $doc['month']);
                    if ($locked && !$Settings->hasPermission('activities.delete-locked')) : ?>
                        <p class="mt-0">
                            <?= lang('activities.this_activity_has_been_locked_because_it_was_already_used_by_reporters_in_a') ?>
                        </p>
                    <?php
                    elseif ($Settings->hasPermission('activities.delete')) :
                    ?>
                        <p class="mt-0">
                            <?= lang('activities.you_have_permission_to_delete_this_activity') ?>
                        </p>
                        <form action="<?= ROOTPATH ?>/crud/activities/delete/<?= $id ?>" method="post" class="d-inline-block ml-auto">
                            <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities" ?>">
                            <button type="submit" class="btn danger">
                                <i class="ph ph-trash"></i>
                                <?= lang('common.delete_activity') ?>
                            </button>
                        </form>
                    <?php elseif (!$user_activity) : ?>

                        <p class="mt-0">
                            <?= lang('activities.this_is_not_your_own_activity_if_for_any_reason_you_want_it_changed_or_dele') ?>
                        </p>

                    <?php elseif ($user_activity && $Settings->hasPermission('activities.delete-own')) : ?>
                        <p class="mt-0">
                            <b>Info:</b>
                            <?= lang('activities.this_is_your_own_activity_and_it_has_not_been_locked_yet_you_can_delete_it') ?>
                        </p>
                        <form action="<?= ROOTPATH ?>/crud/activities/delete/<?= $id ?>" method="post" class="d-inline-block ml-auto">
                            <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities" ?>">
                            <button type="submit" class="btn danger">
                                <i class="ph ph-trash"></i>
                                <?= lang('common.delete_activity') ?>
                            </button>
                            <br>
                            <small class="text-danger">
                                <?= lang('activities.cannot_be_made_undone') ?>
                            </small>
                        </form>
                    <?php endif; ?>

                </div>
            </div>


            <div class="col-lg-6">
                <style>
                    /* --- Author chips / pills --- */
                    .author-chips {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 6px 10px;
                        align-items: center;
                    }

                    .author-chip {
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                        padding: 2px 8px;
                        border-radius: 999px;
                        font-size: 12px;
                        line-height: 1.4;
                        white-space: nowrap;
                        background: var(--gray-100, #f2f2f2);
                        color: var(--gray-800, #333);
                    }

                    /* .author-chip i {
                        font-size: 14px;
                        opacity: 0.8;
                    } */

                    .author-chip.success {
                        background: var(--success-color-20);
                        color: var(--success-color-dark);
                    }

                    .author-chip.neutral {
                        background: #eee;
                        color: #555;
                    }

                    .author-units {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 6px;
                        align-items: center;
                    }

                    .author-unit {
                        padding: 2px 6px;
                        font-size: 10px;
                        border-radius: 100px;
                        background: #f7f7f7;
                        border: 1px solid #e0e0e0;
                        color: #444;
                    }

                    .author-unit:hover {
                        background: #ececec;
                        text-decoration: none;
                    }

                    /* Optional: only show claim on hover (less visual noise) */
                    .author-row .claim-action {
                        opacity: 0;
                        position: absolute;
                        right: 0;
                        top: .75rem;
                        transition: opacity 0.15s ease;
                    }

                    .author-row td {
                        position: relative;
                    }

                    .author-row:hover .claim-action {
                        opacity: 1;
                    }
                </style>

                <?php

                $authorModules = ['authors', 'author-table', 'scientist', 'supervisor', 'supervisor-thesis', 'editor'];

                foreach ($typeFields as $field_id => $props) {

                    if (!in_array($field_id, $authorModules, true)) continue;

                    $role = Document::author_role_from_field($field_id);
                    if ($role === null) continue;

                    $authors = $activity[$role] ?? [];
                    if (empty($authors)) continue;

                    $canEdit = ($edit_perm) && (!$locked || $Settings->hasPermission('activities.edit-locked'));

                    // --- Configure optional third column (avoid duplicated if/elseif in thead + tbody) ---
                    $thirdCol = null;
                    if ($sws) {
                        $thirdCol = [
                            'label' => 'SWS',
                            'value' => fn($a) => ($a['sws'] ?? 0)
                        ];
                    } elseif ($supervisorThesis) {
                        $thirdCol = [
                            'label' => lang('common.role'),
                            'value' => fn($a) => $Format->getSupervisorRole($a['role'] ?? 'other'),
                        ];
                    } elseif ($role === 'authors') {
                        $thirdCol = [
                            'label' => lang('common.position'),
                            'value' => fn($a) => $Format->getPosition($a['position'] ?? ''),
                        ];
                    }
                ?>

                    <div class="d-flex align-items-start justify-content-between gap-10 mb-10">
                        <h2 class="mt-0 mb-0"><?= $Modules->get_name($field_id) ?></h2>

                        <?php if ($canEdit): ?>
                            <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>/<?= $role ?>" class="btn secondary">
                                <i class="ph ph-pencil-simple-line"></i>
                                <?= lang('action.edit') ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <table class="table mb-20">
                        <thead>
                            <tr>
                                <th><?= lang('common.person') ?></th>
                                <!-- <th><?= lang('common.details') ?></th> -->
                                <?php if (!empty($thirdCol)): ?>
                                    <th><?= $thirdCol['label'] ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>

                        <tbody id="<?= e($role) ?>">
                            <?php foreach ($authors as $i => $author):

                                // --- Name "Last, First" (inline; used once) ---
                                $name = $author['last'] ?? '';
                                if (!empty($author['first'])) $name .= ', ' . $author['first'];
                                $name = trim($name);

                                $hasUser = !empty($author['user']);
                                $isAffiliated = (($author['aoi'] ?? 0) == 1);

                                // Unique dropdown id per row (prevents collisions)
                                $dropdownId = 'claim-dd-' . $role . '-' . $i;
                            ?>
                                <tr class="author-row">
                                    <td class="text-nowrap">
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
                                        <br>
                                        <div class="author-chips font-size-12 text-muted">

                                            <?php if ($isAffiliated): ?>
                                                <span class="author-chip success"
                                                    data-toggle="tooltip"
                                                    data-title="<?= lang('common.author_of_the_institution') ?>">
                                                    <i class="ph ph-handshake"></i>
                                                    <?= lang('common.affiliated') ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($hasUser): ?>
                                                <?php if ($author['approved']) { ?>
                                                    <span class="author-chip neutral"
                                                        data-toggle="tooltip"
                                                        data-title="<?= lang('common.author_approved_this_activity') ?>">
                                                        <?= bool_icon(true) ?>
                                                        <?= lang('common.approved') ?>
                                                    </span>
                                                <?php } else { ?>
                                                    <span class="author-chip neutral"
                                                        data-toggle="tooltip"
                                                        data-title="<?= lang('common.author_has_not_yet_approved_this_activity') ?>">
                                                        <?= bool_icon(false) ?>
                                                        <?= lang('common.pending') ?>
                                                    </span>
                                                <?php } ?>


                                            <?php elseif (!$user_activity): ?>
                                                <span class="claim-action">
                                                    <div class="dropdown d-inline-block">
                                                        <button class="btn small" data-toggle="dropdown" type="button"
                                                            id="<?= $dropdownId ?>" aria-haspopup="true" aria-expanded="false">
                                                            <?= lang('action.claim') ?>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right w-300" aria-labelledby="<?= $dropdownId ?>">
                                                            <div class="content font-size-12">
                                                                <div class="d-block text-danger mb-10">
                                                                    <?= lang('common.you_claim_that_you_are_this_author_this_activity_will_be_added_to_your_list') ?>
                                                                </div>
                                                                <form action="<?= ROOTPATH ?>/crud/activities/claim/<?= $id ?>" method="post">
                                                                    <input type="hidden" name="role" value="<?= e($role) ?>">
                                                                    <input type="hidden" name="index" value="<?= (int)$i ?>">
                                                                    <input type="hidden" name="redirect" value="<?= ROOTPATH . "/activities/view/$id" ?>">
                                                                    <button class="btn block small" type="submit">
                                                                        <?= lang('action.claim') ?>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </span>
                                            <?php endif; ?>

                                            <?php if (!empty($author['units'])): ?>
                                                <div class="author-chip author-units">
                                                    <span class=""
                                                        data-toggle="tooltip"
                                                        data-title="<?= lang('common.participating_units') ?>">
                                                        <i class="ph ph-users-three"></i>
                                                    </span>
                                                    <?php foreach ($author['units'] as $unit):
                                                        $u = e((string)$unit);
                                                    ?>
                                                        <a class="author-unit" href="<?= ROOTPATH ?>/groups/view/<?= $u ?>">
                                                            <?= $u ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </td>

                                    <?php if (!empty($thirdCol)): ?>
                                        <td><?= $thirdCol['value']($author) ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php } ?>

                <h3>
                    <?= lang('common.affiliated_positions') ?>
                </h3>

                <?php
                $positions = [
                    'first' => lang('common.first_author'),
                    'last' => lang('common.last_author'),
                    'first_and_last' => lang('common.first_and_last_author'),
                    'first_or_last' => lang('common.first_or_last_author'),
                    'middle' => lang('common.middle_author'),
                    'single' => lang('common.one_single_affiliated_author'),
                    'none' => lang('common.no_author_affiliated_view'),
                    'all' => lang('common.all_authors_affiliated'),
                    'corresponding' => lang('common.corresponding_author'),
                    'not_first' => lang('common.not_first_author'),
                    'not_last' => lang('common.not_last_author'),
                    'not_middle' => lang('common.not_middle_author'),
                    'not_corresponding' => lang('common.not_corresponding_author'),
                    'not_first_or_last' => lang('common.not_first_or_last_author'),
                    'not_first_and_last' => lang('common.not_first_and_last_author'),
                    'unspecified' => lang('common.unspecified_no_position_specified'),
                ];
                ?>


                <?php foreach ($doc['affiliated_positions'] ?? [] as $key) { ?>
                    <span class="badge bg-white mr-5 mb-5"><?= $positions[$key] ?? $key ?></span>
                <?php } ?>
                <br>
                <small class="text-muted">
                    <?= lang('common.automatically_calculated') ?>
                </small>

                <h3>
                    <?= lang('common.participating_units') ?>
                </h3>
                <table class="table unit-table w-full">
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
                                    <?= lang('common.no_organisational_unit_connected') ?>
                                </td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>



    <?php if ($Settings->featureEnabled('projects')) { ?>
        <div class="modal" id="projects" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="title">
                        <?= lang('activities.connect_projects') ?>
                    </h5>
                    <div>
                        <?php
                        include BASEPATH . "/components/connect-projects.php";
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <section id="activities" style="display: none;">
        <h2 class="title">
            <?= lang('common.connected_activities') ?>
        </h2>


        <?php if (!empty($connected_activities)) { ?>
            <table class="table">
                <?php foreach ($connected_activities as $con) {
                    // check if activity is target or source
                    $reverse = ($con['target_id'] == $id);
                    $activity = $osiris->activities->findOne(['_id' => $reverse ? $con['source_id'] : $con['target_id']], ['projection' => [
                        'rendered' => 1,
                    ]]);
                    $conLabel = $Format->getRelationshipLabel($con['relationship'], $reverse);
                ?>
                    <tr>
                        <td>
                            <h5 class="m-0">
                                <?= lang($conLabel['en'], $conLabel['de']) ?>
                            </h5>
                            <div><?= $activity['rendered']['web'] ?? '' ?></div>
                        </td>
                        <?php if ($edit_perm) { ?>
                            <td>
                                <form action="<?= ROOTPATH ?>/crud/activities/disconnect" method="post" class="d-inline-block ml-auto">
                                    <input type="hidden" name="connection_id" value="<?= $con['_id'] ?>">
                                    <input type="hidden" name="redirect" value="<?= ROOTPATH . "/activities/view/" . $id ?>#section-activities">
                                    <button type="submit" class="btn small danger" data-toggle="tooltip" data-title="<?= lang('common.disconnect_activity') ?>">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            </td>
                        <?php } ?>

                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <?= lang('activities.no_connected_activities') ?>
        <?php } ?>

        <?php if ($edit_perm) { ?>

            <div class="box padded">
                <h3 class="title">
                    <?= lang('common.connect_activities') ?>
                </h3>

                <form action="<?= ROOTPATH ?>/crud/activities/connect" method="post">
                    <input type="hidden" name="source_id" value="<?= $id ?>">
                    <input type="hidden" name="redirect" value="<?= ROOTPATH . "/activities/view/" . $id ?>#section-activities">
                    <!-- relationship type -->
                    <div class="form-group">
                        <label for="relationship-type"><?= lang('common.relationship_type') ?></label>
                        <div class="form-group">
                            <div class="input-group">
                                <select name="relationship" id="relationship-type" class="form-control" required>
                                    <?php
                                    $relationships = $Format->getRelationships();
                                    foreach ($relationships as $rel) {
                                        $key = $rel['id'];
                                        $label = lang($rel['label']['en'], $rel['label']['de'] ?? null);
                                        $rev = lang($rel['reverse_label']['en'], $rel['reverse_label']['de'] ?? null);
                                    ?>
                                        <option data-label="<?= $label ?>" data-reverse-label="<?= $rev ?>" value="<?= $key ?>"><?= $label ?></option>
                                    <?php } ?>
                                </select>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <div class="custom-switch">
                                            <input type="checkbox" id="swap-relationship-dir" name="reverse" value="1" onchange="swapRelationshipDirection()">
                                            <label for="swap-relationship-dir">
                                                <?= lang('common.swap_direction') ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- input field with suggesting activities -->
                    <div class="form-group" id="activity-suggest">
                        <label for="activity-suggested"><?= lang('common.select_an_activity_to_connect') ?></label>
                        <input type="text" name="activity-suggested" id="activity-suggested" class="form-control" required placeholder="<?= lang('common.start_typing_to_search_for_activities') ?>">

                        <div class="form-group font-size-12">
                            <div class="custom-radio d-inline-block mr-20">
                                <input type="radio" name="activity-search-limit" id="activity-suggest-author" value="user" checked="checked">
                                <label for="activity-suggest-author"><?= lang('common.only_show_my_activities') ?></label>
                            </div>
                            <div class="custom-radio d-inline-block mr-20">
                                <input type="radio" name="activity-search-limit" id="activity-suggest-unit" value="unit">
                                <label for="activity-suggest-unit"><?= lang('common.show_activities_from_my_unit_s') ?></label>
                            </div>
                            <div class="custom-radio d-inline-block mr-20">
                                <input type="radio" name="activity-search-limit" id="activity-suggest-all" value="all">
                                <label for="activity-suggest-all"><?= lang('common.show_all_activities') ?></label>
                            </div>
                        </div>




                        <div class="suggestions on-focus"></div>
                    </div>
                    <input type="hidden" name="target_id" id="activity-selected" required value="">

                    <button type="submit" class="btn primary">
                        <?= lang('common.connect') ?>
                    </button>
                </form>
            </div>
            <style>
                .suggestions {
                    color: #464646;
                    /* position: absolute; */
                    margin: 10px auto;
                    top: 100%;
                    left: 0;
                    /* height: 19.2rem; */
                    overflow: auto;
                    bottom: -3px;
                    width: 100%;
                    box-sizing: border-box;
                    min-width: 12rem;
                    background-color: white;
                    border: var(--border-width) solid #afafaf;
                    /* visibility: hidden; */
                    /* opacity: 0; */
                    z-index: 100;
                    -webkit-transition: opacity 0.4s linear;
                    transition: opacity 0.4s linear;
                }

                .suggestions a {
                    display: block;
                    padding: 0.5rem;
                    border-bottom: var(--border-width) solid #afafaf;
                    color: #464646;
                    text-decoration: none;
                    width: 100%;
                }

                .suggestions a:hover {
                    background-color: #f0f0f0;
                }
            </style>
            <script>
                $('#activity-suggested').on('input', function() {
                    // prevent enter from submitting form
                    $(this).closest('form').on('keypress', function(event) {
                        if (event.keyCode == 13) {
                            event.preventDefault();
                        }
                    })
                    const val = $(this).val();
                    if (val.length < 3) return;
                    let filter = ''
                    let limit = $('input[name="activity-search-limit"]:checked').val();
                    if (limit == 'user') {
                        filter = '?user=<?= urlencode($_SESSION['username']) ?>';
                    } else if (limit == 'unit') {
                        filter = '?unit=<?= implode(',', $user_units) ?>';
                    } else {
                        filter = '';
                    }
                    $.get('<?= ROOTPATH ?>/api/activities-suggest/' + val + filter, function(data) {
                        $('#activity-suggest .suggestions').empty();
                        console.log(data);
                        data.data.forEach(function(d) {
                            if (d.id.toString() == '<?= $id ?>') return; // prevent selecting itself
                            $('#activity-suggest .suggestions').append(
                                `<a data-id="${d.id.toString()}">${d.details.icon} ${d.details.plain}</a>`
                            )
                        })

                        // $('#activity-suggest .suggest').html(data);
                    })
                })
                $('#activity-suggest .suggestions').on('click', 'a', function() {
                    const activity_id = $(this).data('id');
                    const activity_text = $(this).text();
                    $('#activity-selected').val(activity_id);
                    $('#activity-suggested').val(activity_text);
                    $('#activity-suggest .suggestions').empty();
                })

                function swapRelationshipDirection() {
                    // swap all the labels
                    const select = $('#relationship-type');
                    const isReverse = $('#swap-relationship-dir').is(':checked');
                    select.find('option').each(function() {
                        const label = $(this).data(isReverse ? 'reverse-label' : 'label');
                        $(this).text(label);
                    });
                }
            </script>
        <?php } ?>

    </section>

    <?php if ($Settings->featureEnabled('projects')) { ?>
        <section id="projects" style="display: none;">
            <div class="btn-toolbar float-sm-right">
                <a href="#projects" class="btn secondary mr-5">
                    <i class="ph ph-tree-structure"></i>
                    <?= lang('common.connect') ?>
                </a>
            </div>

            <h2 class="title">
                <?= lang('common.projects') ?>
            </h2>

            <?php if (!empty($projects)) {

                require_once BASEPATH . "/php/Project.php";
                $Project = new Project();

                foreach ($projects as $project) {
                    $Project->setProject($project);
            ?>
                    <?= $Project->widgetSmall(true) ?>
                <?php } ?>

            <?php } else { ?>
                <?= lang('activities.no_projects_connected') ?>
            <?php } ?>

        </section>
    <?php } ?>




    <?php if ($Settings->featureEnabled('infrastructures')) { ?>
        <div class="modal" id="infrastructures" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="title">
                        <?= lang('activities.connect_settings', replace: ['Settings' => $Settings->infrastructureLabel()]) ?>
                    </h5>
                    <div>
                        <?php
                        include BASEPATH . "/components/connect-infrastructures.php";
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <section id="infrastructures" style="display: none;">
            <div class="btn-toolbar float-sm-right">
                <a href="#infrastructures" class="btn secondary mr-5">
                    <i class="ph ph-cube-transparent"></i>
                    <?= lang('common.connect') ?>
                </a>
            </div>

            <h2 class="title">
                <?= $Settings->infrastructureLabel() ?>
            </h2>

            <?php if (!empty($infrastructures)) {
            ?>
                <table class="table">
                    <tbody>
                        <?php foreach ($infrastructures as $infra) {
                            if (empty($infra)) continue;
                        ?>
                            <tr>
                                <td>
                                    <h6 class="m-0">
                                        <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infra['_id'] ?>" class="link">
                                            <?= lang($infra['name'], $infra['name_de'] ?? null) ?>
                                        </a>
                                        <br>
                                    </h6>

                                    <div class="text-muted mb-5">
                                        <?php if (!empty($infra['subtitle'])) { ?>
                                            <?= lang($infra['subtitle'], $infra['subtitle_de'] ?? null) ?>
                                        <?php } else { ?>
                                            <?= get_preview(lang($infra['description'], $infra['description_de'] ?? null), 300) ?>
                                        <?php } ?>
                                    </div>
                                    <div>
                                        <?= fromToYear($infra['start_date'], $infra['end_date'] ?? null, true) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                </table>

            <?php } else { ?>
                <?= lang('common.no_infrastructures_connected') ?>
            <?php } ?>

        </section>
    <?php } ?>


    <?php
    if ($upload_possible):
    ?>
        <section id="files" style="display: none; max-width: 60rem;">
            <h2 class="title"><?= lang('common.files') ?></h2>
            <?php
            // check for legacy files
            $legacy_files = $doc['files'] ?? array();
            if (!empty($legacy_files)) : ?>
                <div class="box padded">
                    <?php foreach ($legacy_files as $legacy_file) :
                        $doctype = $legacy_file['type'] ?? 'file';
                    ?>
                        <div class="">
                            <i class='ph ph-file ph-<?= $icon ?>'></i>
                            <?= $legacy_file['filename'] ?>
                            <div class="d-flex justify-content-between">
                                <a href="<?= $legacy_file['filepath'] ?>" class="btn small primary"><i class="ph ph-download"></i> Download</a>
                                <form action="<?= ROOTPATH ?>/crud/activities/upload-files/<?= $id ?>" method="post" class="d-inline-block">
                                    <input type="hidden" name="delete" value="<?= $legacy_file['filename'] ?>">

                                    <button class="btn small danger" type="submit">
                                        <i class="ph-duotone ph-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <!-- hint to migrate -->
                    <div class="alert signal mt-20">
                        <b>Info:</b>
                        <?= lang('activities.these_are_legacy_files_please_re_upload_them_using_the_new_document_upload') ?>
                    </div>
                </div>
            <?php endif; ?>

            <table class="table">
                <tbody>
                    <?php
                    if (empty($files)) {
                        echo '<tr><td>' . lang('common.no_documents_available') . '</td></tr>';
                    } else {
                        foreach ($files as $file) {
                            $file_url = ROOTPATH . '/uploads/' . $file['_id'] . '.' . $file['extension'];
                    ?>
                            <tr>
                                <td class="font-size-18 text-center text-muted" style="width: 50px;">
                                    <i class='ph ph-file ph-<?= getFileIcon($file['extension'] ?? '') ?>'></i>
                                </td>
                                <td>
                                    <?php if ($edit_perm) : ?>
                                        <div class="float-right">
                                            <div class="dropdown">
                                                <button class="btn link" data-toggle="dropdown" type="button" id="edit-doc-<?= $file['_id'] ?>" aria-haspopup="true" aria-expanded="false">
                                                    <i class="ph ph-edit text-primary"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="edit-doc-<?= $file['_id'] ?>">
                                                    <div class="content">
                                                        <form action="<?= ROOTPATH ?>/data/document/update" method="post">
                                                            <div class="form-group floating-form">
                                                                <select class="form-control" name="name" placeholder="Name" required>
                                                                    <?php
                                                                    $vocab = $Vocabulary->getValues('activity-document-types');
                                                                    foreach ($vocab as $v) { ?>
                                                                        <option value="<?= $v['id'] ?>" <?= ($file['name'] == $v['id'] ? 'selected' : '') ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                                <label for="name" class="required"><?= lang('common.doc_type') ?></label>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="description"><?= lang('common.description') ?></label>
                                                                <textarea class="form-control" name="description" placeholder="<?= lang('common.description') ?>"><?= $file['description'] ?? '' ?></textarea>
                                                            </div>
                                                            <input type="hidden" name="id" value="<?= $file['_id'] ?>">
                                                            <button class="btn btn-block primary" type="submit"><?= lang('common.save_changes') ?></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn link" data-toggle="dropdown" type="button" id="delete-doc-<?= $file['_id'] ?>" aria-haspopup="true" aria-expanded="false">
                                                    <i class="ph ph-trash text-danger"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="delete-doc-<?= $file['_id'] ?>">
                                                    <div class="content">
                                                        <form action="<?= ROOTPATH ?>/data/delete" method="post">
                                                            <span class="text-danger"><?= lang('common.do_you_want_to_delete_this_document') ?></span>
                                                            <input type="hidden" name="id" value="<?= $file['_id'] ?>">
                                                            <button class="btn btn-block danger" type="submit"><?= lang('action.delete') ?></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <h6 class="m-0">
                                        <a href="<?= $file_url ?>" target="_blank" rel="noopener">
                                            <?= $Vocabulary->getValue('activity-document-types', $file['name'] ?? '', lang('common.other')); ?>
                                            <i class="ph ph-download"></i>
                                        </a>
                                    </h6>
                                    <?= $file['description'] ?? '' ?>
                                    <br>
                                    <div class="font-size-12 text-muted d-flex align-items-center justify-content-between">
                                        <div>
                                            <?= $file['filename'] ?> (<?= $file['size'] ?> Bytes)
                                            <br>
                                            <?= lang('common.uploaded_by') ?> <?= $DB->getNameFromId($file['uploaded_by']) ?>
                                            <?= lang('common.on') ?> <?= date('d.m.Y', strtotime($file['uploaded'])) ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>

            <?php if ($edit_perm) { ?>
                <form action="<?= ROOTPATH ?>/data/upload" method="post" enctype="multipart/form-data" class="box padded">
                    <h5 class="title font-size-16">
                        <?= lang('common.upload_document') ?>
                    </h5>
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" id="upload-file" name="file" class="custom-file-input" maxsize="16777216" required>
                            <label for="upload-file" class="custom-file-label"><?= lang('common.choose_a_file') ?></label>
                            <br><small class="text-danger">Max. 16 MB.</small>
                        </div>
                    </div>
                    <input type="hidden" name="values[type]" value="activities">
                    <input type="hidden" name="values[id]" value="<?= $id ?>">
                    <div class="form-group floating-form">
                        <select class="form-control" name="values[name]" placeholder="Name" required>
                            <?php
                            $vocab = $Vocabulary->getValues('activity-document-types');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>"><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="name" class="required"><?= lang('common.doc_type') ?></label>
                    </div>
                    <div class="form-group floating-form">
                        <input type="text" class="form-control" name="values[description]" placeholder="<?= lang('common.description') ?>" value="">
                        <label for="description"><?= lang('common.description') ?></label>
                    </div>
                    <button class="btn primary" type="submit"><?= lang('action.upload') ?></button>
                </form>

                <script>
                    var uploadField = document.getElementById("upload-file");

                    uploadField.onchange = function() {
                        if (this.files[0].size > 16777216) {
                            toastError(<?= json_encode(lang('common.file_is_too_large_max_16mb_is_supported'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
                            this.value = "";
                        };
                    };
                </script>
            <?php } ?>

        </section>

    <?php endif; ?>


    <div class="modal" id="add-tags" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="title">
                    <?= lang('common.connect_taglabel', replace: ['tagLabel' => $tagLabel]) ?>
                </h5>
                <p>
                    <?= lang('common.currently_connected') . $tagLabel ?>:
                    <?php
                    $tags = $doc['tags'] ?? [];
                    if (count($tags)) {
                        echo $Settings->printTags($tags, 'all-activities');
                    } else {
                        echo lang('common.no_taglabel_assigned_yet', replace: ['tagLabel' => $tagLabel]);
                    }
                    ?>
                </p>

                <?php if ($edit_perm && $Settings->hasPermission('activities.tags')) { ?>
                    <form action="<?= ROOTPATH ?>/crud/activities/update-tags/<?= $id ?>" method="post">
                        <?php
                        $Settings->tagChooser($doc['tags'] ?? []);
                        ?>

                        <button type="submit" class="btn success">
                            <i class="ph ph-floppy-disk"></i>
                            <?= lang('action.save') ?>
                        </button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php if ($Settings->featureEnabled('spectrum')) { ?>
        <section id="spectrum" style="display:none">
            <?php if ($edit_perm) { ?>
                <a href="#spectrum-editor" class="btn mt-20">
                    <i class="ph ph-edit"></i>
                    <?= lang('common.edit_spectrum') ?>
                </a>
            <?php } ?>
            <?php
            if (!empty($spectrum)) :
                include_once BASEPATH . "/php/Spectrum.php";
                Spectrum::render($spectrum);
            else :
                $manually = $openalex['manual_at'] ?? null;
                $fetched = $openalex['fetched_at'] ?? null;
            ?>
                <p>
                    <?= lang('common.no_topics_are_assigned_to_this_activity') ?>
                </p>
                <?php if ($manually) : ?>
                    <small class="d-block mt-5 text-muted">
                        <?= lang('common.topic_data_was_last_updated_manually_on') ?> <?= date('d.m.Y', strtotime($manually)) ?>
                    </small>
                <?php
                elseif ($fetched) : ?>
                    <small class="d-block mt-5 text-muted">
                        <?= lang('common.topic_data_was_last_updated_on') ?> <?= date('d.m.Y', strtotime($fetched)) ?>
                    </small>
                <?php endif; ?>
                <!-- if fetched is longer ago, show a button to fetch new data -->
                <?php if (!$fetched || strtotime($fetched) < strtotime('-30 days')) : ?>
                    <button class="btn primary small mt-5" id="openalex-refresh-button" onclick="fetchOpenAlex('<?= $doc['doi'] ?>')">
                        <i class="ph ph-arrows-clockwise"></i>
                        <?= lang('common.fetch_latest_topics') ?>
                    </button>
                <?php endif; ?>
            <?php endif; ?>

        </section>
    <?php } ?>


    <section id="coauthors" style="display:none">
        <h2>
            <i class="ph ph-graph" aria-hidden="true"></i>
            <?= lang('common.coauthors') ?>
        </h2>
        <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>/authors" class="btn secondary">
            <i class="ph ph-pencil-simple-line"></i>
            <?= lang('action.edit') ?>
        </a>
        <div class="row row-eq-spacing">
            <div class="col-md-6 flex-grow-0" style="max-width: 40rem">
                <div id="chart-contributors">
                    <canvas id="chart-contributors-canvas"></canvas>
                </div>
            </div>
            <div class="offset-1"></div>
            <div class="col-md-5">
                <div id="dept-legend"></div>
            </div>
        </div>
    </section>


    <!-- new section with history -->
    <section id="history" style="display: none;">
        <h2 class="title">
            <?= lang('common.history') ?>
        </h2>
        <p>
            <?= lang('common.history_of_changes_to_this_activity') ?>
        </p>

        <?php
        if (empty($doc['history'] ?? [])) {
            echo lang('common.no_history_available');
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
                        if (isset($h['source']) && !empty($h['source'])) {
                            echo '<div><b>' . lang('common.source') . '</b>';
                            echo ' <code>' . $h['source'] . '</code></div>';
                        }
                        if (isset($h['comment']) && !empty($h['comment'])) { ?>
                            <code><?= $h['comment'] ?></code>
                        <?php
                        }
                        if (isset($h['changes']) && !empty($h['changes'])) {
                            echo '<div class="font-weight-bold mt-10">' .
                                lang('common.changes_to_the_activity') .
                                '</div>';
                            echo '<table class="table w-auto small">';
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
                                lang('common.status_at_this_time_point') .
                                '</div>';

                            echo '<table class="table w-auto small">';
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
                            echo lang('common.no_changes_tracked');
                        }
                        ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </section>

    <?php if ($guests_involved) { ?>


        <?php if ($Settings->featureEnabled('guest-forms')) {

            $guest_server = $Settings->get('guest-forms-server');
            $url = $guest_server . "/a/" . $id;
        ?>
            <script src="<?= ROOTPATH ?>/js/papaparse.min.js"></script>
            <!-- modals -->
            <div class="modal" id="add-guests" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                            <span aria-hidden="true">&times;</span>
                        </a>
                        <h5 class="title">
                            <?= lang('activities.add_guests') ?>
                        </h5>
                        <div>
                            <h3>
                                <?= lang('activities.add_guests_to_this_activity') ?>
                            </h3>
                            <p>
                                <?= lang('activities.you_can_add_guests_to_this_activity_by_entering_their_names_and_affiliation') ?>
                            </p>

                            <form action="<?= ROOTPATH ?>/crud/activities/guests" method="post">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <table class="table mb-20">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th><?= lang('activities.last') ?></th>
                                            <th><?= lang('activities.first') ?></th>
                                            <th><?= lang('Email') ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="guest-list">
                                        <?php foreach ($guests as $guest) { ?>
                                            <tr>
                                                <td>
                                                    <input type="text" name="guests[id][]" class="form-control disabled" required value="<?= $guest['id'] ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="guests[last][]" class="form-control" required value="<?= $guest['last'] ?>">
                                                </td>
                                                <td>
                                                    <input type="text" name="guests[first][]" class="form-control" required value="<?= $guest['first'] ?>">
                                                </td>
                                                <td>
                                                    <input type="email" name="guests[email][]" class="form-control" required value="<?= $guest['email'] ?>">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn small link" id="remove-guest" onclick="$(this).closest('tr').remove()">
                                                        <i class="ph-duotone ph-trash text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5">
                                                <button type="button" class="btn small" id="add-guest" onclick="addGuestRow()">
                                                    <i class="ph ph-plus"></i>
                                                    <?= lang('activities.add_guest') ?>
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <button type="submit" class="btn primary">
                                    <i class="ph ph-save"></i>
                                    <?= lang('activities.save_guests') ?>
                                </button>

                            </form>

                            <div class="box">
                                <div class="content">
                                    <h3>
                                        <?= lang('activities.import_guests_from_csv') ?>
                                    </h3>
                                    <p>
                                        <?= lang('activities.you_can_import_a_list_of_guests_from_a_csv_file') ?>
                                    </p>
                                    <div class="custom-file">
                                        <input type="file" id="guest-file">
                                        <label for="guest-file"><?= lang('common.select_file') ?></label>
                                    </div>
                                    <small>
                                        <?= lang('activities.the_file_should_contain_columns_for_last_name_first_name_and_email_address') ?>
                                    </small>
                                </div>

                                <script>
                                    document.getElementById('guest-file').addEventListener('change', function(e) {
                                        var file = e.target.files[0];
                                        if (!file) return;
                                        Papa.parse(file, {
                                            header: true,
                                            complete: function(results) {
                                                console.log(results);
                                                results.data.forEach(function(raw) {
                                                    var row = {};
                                                    // try to find first and last name and email
                                                    ['first', 'first name', 'vorname', 'First name', 'First', 'Vorname', 'FIRST', 'FIRST NAME', 'VORNAME'].forEach(key => {
                                                        if (raw[key]) {
                                                            row.first = raw[key];
                                                            return
                                                        }
                                                    });
                                                    ['last', 'last name', 'nachname', 'Last name', 'Last', 'Nachname', 'LAST', 'LAST NAME', 'NACHNAME'].forEach(key => {
                                                        if (raw[key]) {
                                                            row.last = raw[key];
                                                            return
                                                        }
                                                    });
                                                    ['email', 'mail', 'Email', 'Mail', 'E-Mail'].forEach(key => {
                                                        if (raw[key]) {
                                                            row.email = raw[key];
                                                            return
                                                        }
                                                    });
                                                    if (!row.first && !row.last) {
                                                        ['name', 'Name', 'NAME'].forEach(key => {
                                                            if (raw[key]) {

                                                                // try last, first
                                                                var parts = raw[key].split(', ');
                                                                if (parts.length == 2) {
                                                                    row.last = parts[0];
                                                                    row.first = parts[1];
                                                                    return
                                                                }
                                                                // try first last
                                                                var parts = raw[key].split(' ');
                                                                if (parts.length == 2) {
                                                                    row.first = parts[0];
                                                                    row.last = parts[1];
                                                                    return
                                                                }
                                                            }
                                                        });
                                                    }


                                                    addGuestRow(row);
                                                });
                                            }
                                        });
                                    });
                                </script>

                            </div>

                            <script>
                                function addGuestRow(data = {}) {
                                    var row = document.createElement('tr');
                                    var id = Math.random().toString(36).substring(7);
                                    row.innerHTML = `
                                    <td>
                                        <input type="text" name="guests[id][]" class="form-control disabled" required readonly value="${id}">
                                    </td>
                                    <td>
                                        <input type="text" name="guests[last][]" class="form-control" required value="${data.last ?? ''}">
                                    </td>
                                    <td>
                                        <input type="text" name="guests[first][]" class="form-control" required value="${data.first ?? ''}">
                                    </td>
                                    <td>
                                        <input type="email" name="guests[email][]" class="form-control" required value="${data.email ?? ''}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn small link" id="remove-guest" onclick="$(this).closest('tr').remove()">
                                            <i class="ph-duotone ph-trash text-danger"></i>
                                        </button>
                                    </td>
                                `;
                                    document.getElementById('guest-list').appendChild(row);
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>

        <?php } ?>


        <section id="guests" style="display:none">

            <h2 class="title">
                <?= lang('common.guests') ?>
            </h2>

            <?php if ($Settings->featureEnabled('guest-forms')) {

            ?>
                <a href="#add-guests" class="btn primary">
                    <i class="ph ph-plus" aria-hidden="true"></i>
                    <?= lang('activities.add_guests') ?>
                </a>

            <?php } ?>

            <p>
                <?= lang('activities.there_are_currently_guests_guests_involved_in_this_activity', replace: ['guests' => count($guests)]) ?>
            </p>

            <?php if ($user_activity || $Settings->hasPermission('guests.view')) {
                $new_guests = false;
            ?>

                <table class="table mb-20">
                    <thead>
                        <tr>
                            <th><?= lang('activities.last') ?></th>
                            <th><?= lang('activities.first') ?></th>
                            <th><?= lang('Email') ?></th>
                            <th><?= lang('Status') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guests as $guest) { ?>
                            <tr>
                                <td><?= $guest['last'] ?></td>
                                <td><?= $guest['first'] ?></td>
                                <td><?= $guest['email'] ?></td>
                                <td>
                                    <?php
                                    switch ($guest['status'] ?? 'new') {
                                        case 'pending':
                                            echo '<span class="badge primary">' . lang('common.pending') . '</span>';
                                            break;
                                        case 'approved':
                                            echo '<span class="badge success">' . lang('common.approved') . '</span>';
                                            break;
                                        case 'new':
                                            echo '<span class="badge signal">' . lang('common.new') . '</span>';
                                            $new_guests = true;
                                            break;
                                        default:
                                            echo '<span class="badge danger">' . lang('common.unknown') . '</span>';
                                            break;
                                    }
                                    ?>

                                    <!-- action buttons -->
                                    <!-- send mail -->
                                    <?php if (($guest['status'] ?? 'new') == 'new') { ?>
                                        <form action="<?= ROOTPATH ?>/crud/activities/guest-mail/<?= $id ?>" method="post" class="d-inline-block">
                                            <input type="hidden" name="guest" value="<?= $guest['id'] ?>">
                                            <button type="submit" class="btn small">
                                                <i class="ph ph-envelope" aria-hidden="true"></i>
                                                <?= lang('activities.send_email') ?>
                                            </button>
                                        </form>
                                    <?php } ?>

                                    <!-- show qr code -->
                                    <button type="button" class="btn small" data-toggle="modal" data-target="qr-<?= $guest['id'] ?>">
                                        <i class="ph ph-qr-code" aria-hidden="true"></i>
                                        <?= lang('activities.qr_code') ?>
                                    </button>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- qr modals -->
                <?php foreach ($guests as $guest) {
                    $guest_server = $Settings->get('guest-forms-server');
                    $url = $guest_server . "/a/" . $id . "." . $guest['id'];
                    $options = new QROptions([]);

                    try {
                        $qr = (new QRCode($options))->render($url);
                    } catch (Throwable $e) {
                        $qr = '';
                        exit($e->getMessage());
                    }
                ?>
                    <div class="modal" id="qr-<?= $guest['id'] ?>" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <h5 class="title">
                                    <?= lang('activities.qr_code_for') . $guest['first'] . ' ' . $guest['last'] ?>
                                </h5>
                                <div>
                                    <div style="background-color: white; display: inline-block;">
                                        <img src="<?= $qr ?>" alt="QR code for <?= $guest['first'] . ' ' . $guest['last'] ?>" class="w-200">
                                    </div>
                                    <br>
                                    <b>Link:</b>
                                    <a href="<?= $url ?>" target="_blank"><?= $url ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($new_guests) { ?>
                    <!-- send email to all new guests -->
                    <form action="<?= ROOTPATH ?>/crud/activities/guest-mail/<?= $id ?>" method="post">
                        <button type="submit" class="btn primary">
                            <i class="ph ph-envelope" aria-hidden="true"></i>
                            <?= lang('activities.send_email_to_new_guests') ?>
                        </button>
                    </form>
                <?php } ?>


            <?php } else { ?>
                <p>
                    <?= lang('activities.you_do_not_have_permission_to_view_the_list_of_guests_only_authors_of_the_a') ?>
                </p>
            <?php } ?>




        </section>
    <?php } ?>

</div>