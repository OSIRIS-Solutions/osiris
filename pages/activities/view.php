    <style>
        .abstract-section img {
            max-width: 100%;
            height: auto;
            max-height: 400px;
        }
    </style>

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
    $abstract_fields = [];
    $hidden_fields = ['authors', "editors", "supervisors", "semester-select", 'depts', 'projects', 'title', 'event-select'];
    $empty_fields = [];
    $sections = [];
    $Format->usecase = 'list';
    foreach ($fields as $field_id) {
        if (in_array($field_id, ['abstract', 'description'])) {
            $abstract_fields[] = $field_id;
            continue;
        }
        if (in_array($field_id, $hidden_fields)) continue;
        if (!array_key_exists($field_id, $Modules->all_modules)) {
            $section = 'others';
            $custom_field = $osiris->adminFields->findOne(['id' => $field_id]);
            if ($custom_field) {
                if (in_array($custom_field['format'], ['text-format', 'text'])) {
                    $abstract_fields[] = $field_id;
                    continue;
                }
            }
        } else {
            $section = $Modules->all_modules[$field_id]['section'] ?? 'others';
        }
        if (empty($section)) $section = 'others';
        if (in_array($field_id, $hidden_fields)) continue;

        $name = $Modules->get_name($field_id);
        $field = [
            'label' => $name,
            'value' => null,
        ];
        if ($field_id == 'teaching-course' && isset($doc['module_id'])) :
            $module = $DB->getConnected('teaching', $doc['module_id']);
            if (isset($module['organization'])) {
                $org_id = DB::to_ObjectID($module['organization']);
                $org = $osiris->organizations->findOne(['_id' => $org_id]);
                $affiliation = $org['name'] ?? '';
            } else {
                $affiliation = $module['affiliation'] ?? null;
            }
            $field['value'] = '<a class="font-weight-bold" href="' . ROOTPATH . '/teaching/view/' . ($module['_id'] ?? '#') . '">' . ($module['module'] ?? '-') . '</a>: ' . $module['title'] ?? '';
            $field['value'] .= '<br><small>' . $affiliation . '</small>';
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
            $empty_fields[] = $name;
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
                        <?= lang('action.edit') ?>
                    </a>
                <?php } ?>
                <?php if ($user_activity && $locked && empty($doc['end'] ?? null) && $ongoing) { ?>
                    <div class="dropdown">
                        <button class="btn secondary outline" data-toggle="dropdown" type="button" id="update-end-date" aria-haspopup="true" aria-expanded="false">
                            <i class="ph ph-calendar-check"></i>
                            <?= lang('common.end_activity') ?> <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu w-200" aria-labelledby="update-end-date">
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


                <?php if ($Settings->featureEnabled('portal')) { ?>
                    <a class="btn secondary outline" href="<?= ROOTPATH ?>/preview/activity/<?= $id ?>">
                        <i class="ph ph-eye mr-5"></i>
                        <?= lang('common.preview') ?>
                    </a>
                <?php } ?>


                <div class="dropdown">
                    <button class="btn" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                        <i class="ph ph-download mr-5"></i>
                        <?= lang('common.download') ?>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdown-1">
                        <div class="content">
                            <button class="btn block primary" onclick="addToCart(this, '<?= $id ?>')">
                                <i class="<?= (in_array($id, $cart)) ? 'ph ph-duotone ph-basket ph-basket-plus text-success' : 'ph ph-basket ph-basket-plus' ?>"></i>
                                <?= lang('common.collect') ?>
                            </button>
                        </div>
                        <div class="divider"></div>
                        <form action="<?= ROOTPATH ?>/download" method="post" class="content">
                            <input type="hidden" name="filter[id]" value="<?= $id ?>">
                            <div class="form-group">
                                <b><?= lang('activities.download_as') ?></b>
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
                                <b><?= lang('activities.highlight') ?></b>
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

                            <button class="btn block primary">
                                <i class="ph ph-download mr-5"></i>
                                <?= lang('common.download') ?>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="btn" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only"><?= lang('common.more_actions') ?></span><i class="ph ph-dots-three" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdown-1">
                        <div class="content">
                            <a href="?view=old" class="btn block">
                                <i class="ph ph-lightning-slash m-0"></i>
                                <?= lang('activities.classic_view') ?>
                            </a>
                            <?php if (!in_array($doc['type'], ['publication'])) { ?>
                                <hr>
                                <a href="<?= ROOTPATH ?>/activities/copy/<?= $id ?>" class="btn block">
                                    <i class="ph ph-copy"></i>
                                    <?= lang('common.copy') ?>
                                </a>
                            <?php } ?>

                            <?php if ($Settings->hasPermission('activities.lock')) { ?>
                                <hr>
                                <form action="<?= ROOTPATH ?>/crud/activities/<?= $id ?>/lock" method="post">
                                    <?php if ($doc['locked'] ?? false) { ?>
                                        <button class="btn success block" type="submit">
                                            <i class="ph ph-lock-open"></i>
                                            <?= lang('common.unlock') ?>
                                        </button>
                                    <?php } else { ?>
                                        <button class="btn danger block" type="submit">
                                            <i class="ph ph-lock"></i>
                                            <?= lang('common.lock') ?>
                                        </button>
                                    <?php } ?>
                                </form>
                            <?php } ?>

                            <?php if ($canDelete) { ?>
                                <hr>
                                <form action="<?= ROOTPATH ?>/crud/activities/delete/<?= $id ?>" method="post" onsubmit="return confirm('<?= lang('activities.are_you_sure_you_want_to_delete_this_activity') ?>')">
                                    <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities" ?>">
                                    <button class="btn danger block" type="submit">
                                        <i class="ph ph-trash"></i>
                                        <?= lang('common.delete_activity') ?>
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
                        <?= lang('common.overview') ?>
                    </a>

                    <a onclick="navigate('citations')" id="btn-citations" class="btn">
                        <?= lang('activities.citation') ?>
                    </a>

                    <?php if ($count_authors > 0) { ?>
                        <a onclick="navigate('coauthors')" id="btn-coauthors" class="btn">
                            <?= lang('activities.contributors') ?>
                            <span class="index"><?= $count_authors ?></span>
                        </a>
                    <?php } ?>

                    <?php if ($guests_involved) { ?>
                        <a onclick="navigate('guests')" id="btn-guests" class="btn">
                            <?= lang('common.guests') ?>
                            <span class="index"><?= count($guests) ?></span>
                        </a>
                    <?php } ?>

                    <?php
                    if (!empty($doc['history'])) :
                    ?>
                        <a onclick="navigate('history')" id="btn-history" class="btn">
                            <?= lang('common.history') ?>
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
                        <div class="badge success" data-toggle="tooltip" data-title="<?= lang('common.at_least_on_author_of_this_activity_has_an_affiliation_with_the_institute') ?>">
                            <i class="ph-duotone ph-push-pin m-0"></i>
                            <?= lang('common.affiliated') ?>
                        </div>
                    <?php } else { ?>
                        <div class="badge danger" data-toggle="tooltip" data-title="<?= lang('common.none_of_the_authors_has_an_affiliation_to_the_institute') ?>">
                            <i class="ph-duotone ph-push-pin-slash m-0"></i>
                            <?= lang('common.not_affiliated') ?>
                        </div>
                    <?php } ?>
                    <?php if ($doc['locked'] ?? false) { ?>
                        <span id="status-locked" class="badge danger" data-toggle="tooltip" data-title="<?= lang('common.this_activity_has_been_locked') ?>">
                            <i class="ph-duotone ph-lock"></i>
                            <?= lang('common.locked') ?>
                        </span>
                    <?php } ?>

                    <span id="status-not-visible" class="badge <?= $visible_badge !== 'status-not-visible' ? 'hidden' : '' ?>" data-toggle="tooltip" data-title="<?= lang('activities.this_activity_subtype_is_not_visible_in_the_portfolio_due_to_general_settin') ?>">
                        <i class="ph-duotone ph-eye-slash m-0"></i>
                        <?= lang('common.activity_type_not_visible') ?>
                    </span>

                    <span id="status-hidden" class="badge danger <?= $visible_badge !== 'status-hidden' ? 'hidden' : '' ?>" data-toggle="tooltip" data-title="<?= lang('activities.this_activity_is_hidden_in_the_portfolio') ?>">
                        <i class="ph-duotone ph-eye-slash"></i>
                        <?= lang('common.hidden') ?>
                    </span>

                    <span id="status-visible" class="badge success <?= $visible_badge !== 'status-visible' ? 'hidden' : '' ?>" data-toggle="tooltip" data-title="<?= lang('activities.this_activity_is_visible_in_the_portfolio') ?>">
                        <i class="ph-duotone ph-eye"></i>
                        <?= lang('common.visible') ?>
                    </span>

                    <span id="status-highlight" class="badge signal <?= $visible_badge !== 'status-highlight' ? 'hidden' : '' ?>" data-toggle="tooltip" data-title="<?= lang('activities.this_activity_is_highlighted_in_the_portfolio') ?>">
                        <i class="ph-duotone ph-star"></i>
                        <?= lang('common.highlighted') ?>
                    </span>

                    <span id="status-ongoing" class="badge blue <?= !$ongoing ? 'hidden' : '' ?>" data-toggle="tooltip" data-title="<?= lang('activities.this_activity_is_currently_ongoing') ?>">
                        <i class="ph-duotone ph-infinity"></i>
                        <?= lang('projects.ongoing') ?>
                    </span>
                </div>
            </div>

            <section id="raw" style="display:none" class="box padded tab-box">

                <h2 class="title">
                    <?= lang('common.raw_data') ?>
                </h2>

                <?= lang('common.raw_data_as_they_are_stored_in_the_database') ?>

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
                                        <?= lang('activities.contributors') ?>
                                        <span class="data-index"><?= $count_authors ?></span>
                                        <a onclick="navigate('coauthors')">
                                            <i class="ph ph-arrow-square-right ml-5" title="<?= lang('activities.view_all_contributors') ?>"></i>
                                        </a>
                                    </h3>
                                <?php else: ?>
                                    <div class="message danger mb-20">
                                        <h4 class="title">
                                            <?= lang('common.no_authors_or_editors') ?>
                                        </h4>
                                        <?= lang('common.this_activity_has_no_authors_or_editors_assigned_please_add_at_least_one_au') ?>
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
                                                        <?= lang('activities.and_doc_more', replace: ['doc' => (count($doc[$role])-10)]); ?>
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
                            if (!empty($abstract_fields) || ($displayAltmetric)): ?>
                                <hr>
                                <div class="content abstract-section">
                                    <!-- floating container for altmetric badge -->
                                    <?php if ($displayAltmetric) { ?>
                                        <?php if (empty($abstract_fields)) { ?>
                                            <h3 class="section-title">
                                                <?= lang('activities.altmetric_attention_score') ?>
                                            </h3>
                                        <?php } else { ?>
                                            <style>
                                                #altmetric-container {
                                                    float: right;
                                                }
                                            </style>
                                        <?php } ?>
                                        <style>
                                            .abstract-section {
                                                min-height: 12rem;
                                            }
                                        </style>
                                        <div id="altmetric-container" class="ml-20">
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
                                    <?php foreach ($abstract_fields as $field_id) {
                                        $field_name = $Modules->get_name($field_id);
                                        echo "<h3 class='section-title'>$field_name</h3>";
                                        if (!empty($doc[$field_id])) {
                                            echo '<div class="text-justify">' . $doc[$field_id] . '</div>';
                                        } else {
                                            echo '<p>' . lang('activities.no_field_name_available', replace: ['field_name' => strtolower($field_name)]) . '</p>';
                                        }
                                    } ?>

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
                                                <span class="sr-only"><?= lang('action.edit') ?></span>
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
                                            <p class="text-muted"><?= lang('common.no_taglabel_assigned_yet', replace: ['tagLabel' => $tagLabel]); ?></p>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php endif; ?>


                            <?php if ($upload_possible): ?>
                                <hr>
                                <div class="content">
                                    <h3 class="section-title">
                                        <?= lang('common.files'); ?>
                                        <span class="data-index"><?= count($files) ?></span>
                                        <?php if ($canEdit): ?>
                                            <a href="#edit-files" class="ml-10">
                                                <i class="ph ph-edit"></i>
                                                <span class="sr-only"><?= lang('action.edit') ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </h3>

                                    <?php if (empty($files)): ?>
                                        <p class="text-muted"><?= lang('activities.no_files_uploaded_yet') ?></p>
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
                                                        <small class="badge muted"><?= $Vocabulary->getValue('activity-document-types', $file['name'] ?? '', lang('common.other')); ?></small>
                                                        <p>
                                                            <?= $file['description'] ?? '' ?>
                                                        </p>

                                                        <ul class="horizontal">
                                                            <li><?= $file_size ?></li>
                                                            <li><?= lang('common.uploaded_by') ?> <?= $DB->getNameFromId($file['uploaded_by']) ?></li>
                                                            <li><?= lang('common.on') ?> <?= date('d.m.Y', strtotime($file['uploaded'])) ?></li>
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
                                $connections['news'] = count($connected_news ?? []);
                                $count_connections = array_sum($connections);
                                ?>
                                <h3 class="section-title">
                                    <?= lang('activities.connections') ?>
                                    <span class="data-index"><?= $count_connections ?></span>
                                    <?php if ($edit_perm) { ?>
                                        <a href="<?= ROOTPATH ?>/activities/edit-connections/<?= $id ?>" class="ml-10">
                                            <i class="ph ph-edit"></i>
                                            <span class="sr-only"><?= lang('action.edit') ?></span>
                                        </a>
                                    <?php } ?>
                                </h3>

                                <?php if ($count_connections === 0) { ?>
                                    <div class="text-muted">
                                        <?= lang('activities.this_activity_has_no_connections_to_other_entities_yet') ?>
                                        <?php if ($edit_perm) { ?>
                                            <?= lang('activities.you_can_connect') ?>:
                                            <ul class="horizontal mb-10">
                                                <?php if (isset($connections['projects'])) { ?>
                                                    <li><?= lang('common.projects') ?></li>
                                                <?php } ?>
                                                <?php if (isset($connections['infrastructures'])) { ?>
                                                    <li><?= lang('common.infrastructures') ?></li>
                                                <?php } ?>
                                                <li><?= lang('common.other_activities') ?></li>
                                            </ul>
                                            <a href="<?= ROOTPATH ?>/activities/edit-connections/<?= $activity['_id']; ?>" class="btn small">
                                                <i class="ph ph-edit"></i>
                                                <?= lang('activities.connect_now') ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                <?php } else { ?>
                                    <p>
                                        <?php if (isset($connections['projects'])) { ?>
                                            <span class="badge project-badge"><i class="ph ph-tree-structure"></i> <?= lang('common.projects') ?> <b><?= $connections['projects'] ?></b></span>
                                        <?php } ?>
                                        <?php if (isset($connections['infrastructures'])) { ?>
                                            <span class="badge infrastructure-badge"><i class="ph ph-cube-transparent"></i> <?= lang('common.infrastructures') ?> <b><?= $connections['infrastructures'] ?></b></span>
                                        <?php } ?>
                                        <span class="badge activity-badge"><i class="ph ph-folder"></i> <?= lang('common.activities') ?> <b><?= $connections['activities'] ?></b></span>
                                        <?php if (isset($connections['news'])) { ?>
                                            <span class="badge news-badge"><i class="ph ph-newspaper"></i> <?= lang('common.news') ?> <b><?= $connections['news'] ?></b></span>
                                        <?php } ?>
                                    </p>
                                <?php } ?>


                                <div class="connections">
                                    <?php if (!empty($projects)): ?>
                                        <?php foreach ($projects as $project): ?>
                                            <div class="connection">
                                                <span class="badge project-badge"><i class="ph ph-tree-structure"></i> <?= lang('common.project') ?></span>
                                                <h5>
                                                    <a href="<?= ROOTPATH ?>/projects/view/<?= $project['_id']; ?>"> <?= $project['name']; ?> </a>
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
                                                <span class="badge infrastructure-badge"><i class="ph ph-cube-transparent"></i> <?= lang('common.infrastructure') ?></span>
                                                <h5>
                                                    <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infrastructure['_id']; ?>"> <?= $infrastructure['name']; ?> </a>
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
                                                <span class="badge activity-badge"><?= $activity['rendered']['icon'] ?> <?= lang('common.activity') ?></span>
                                                <div><?= lang($conLabel['en'], $conLabel['de']) ?></div>
                                                <?= $activity['rendered']['web'] ?? '' ?>
                                            </div>
                                        <?php } ?>
                                    <?php endif; ?>

                                    <?php if (!empty($connected_news)) : ?>
                                        <?php foreach ($connected_news as $news) { ?>
                                            <div class="connection">
                                                <span class="badge news-badge"><i class="ph ph-newspaper"></i> <?= lang('common.news') ?></span>
                                                <h5>
                                                    <a href="<?= ROOTPATH ?>/news/view/<?= $news['_id']; ?>"> <?= $news['title']; ?> </a>
                                                </h5>
                                                <ul class="horizontal">
                                                    <li><?= date('d.m.Y', strtotime($news['published'] ?? $news['created'])) ?></li>
                                                </ul>
                                                <p><?= $news['teaser'] ?? '' ?></p>
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
                                            <?= $Settings->printTopics($doc['topics'] ?? []) ?: '-' ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <tr>
                                    <td>
                                        <span class="key"><?= lang('common.date') ?>: </span>
                                        <?php if (!isset($doc['year']) || empty($doc['year']) || !isset($doc['month']) || empty($doc['month'])) { ?>
                                            <div class="message danger">
                                                <h3 class="title">
                                                    <?= lang('common.no_time_specified') ?>
                                                </h3>
                                                <?= lang('common.this_activity_has_no_time_specified_please_add_at_least_month_and_year_to_t') ?>
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
                                                        <span class="key"><?= $Settings->impactLabel() ?>: </span>
                                                        <span class="badge"><?= $doc['impact'] ?></span>
                                                    </div>
                                                <?php } ?>

                                                <?php if ($doc['quartile'] ?? false) { ?>
                                                    <div>
                                                        <span class="key"><?= lang('common.quartile') ?>: </span>
                                                        <span class="quartile <?= $doc['quartile'] ?>"><?= $doc['quartile'] ?></span>
                                                    </div>
                                                <?php } ?>

                                                <?php if (!empty($openalex) && isset($openalex['cited_by_count'])) {
                                                    $fetched_at = isset($openalex['fetched_at']) ? date('d.m.Y', strtotime($openalex['fetched_at'])) : '-';
                                                ?>
                                                    <div>
                                                        <span class="key"><?= lang('common.citations') ?>: </span>
                                                        <span class="badge" data-toggle="tooltip" data-title="<?= lang('common.last_updated') ?>: <?= $fetched_at ?>"><?= $openalex['cited_by_count'] ?></span>
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
                                    $highlighted_by = $osiris->persons->count(['highlighted' => strval($id), 'username' => ['$ne' => $_SESSION['username']]]);
                                ?>
                                    <tr>
                                        <td>
                                            <span class="key"><?= lang('common.online_visibility') ?>: </span>
                                            <div id="visibility-toggle" class="btn-group" role="group" aria-label="Visibility toggle">
                                                <button type="button" class="btn small <?= ($selected_state == 'hidden') ? 'active' : '' ?>" id="btn-hidden" onclick="toggleVisibility('hidden')" data-toggle="tooltip" data-title="<?= lang('activities.the_activity_will_be_hidden_from_the_public_portal') ?>">
                                                    <?= lang('common.hidden') ?>
                                                </button>
                                                <button type="button" class="btn small <?= ($selected_state == 'visible') ? 'active' : '' ?>" id="btn-visible" onclick="toggleVisibility('visible')" data-toggle="tooltip" data-title="<?= lang('activities.the_activity_will_be_visible_in_the_public_portal') ?>">
                                                    <?= lang('common.visible') ?>
                                                </button>
                                                <?php if ($user_activity) { ?>
                                                    <button type="button" class="btn small <?= ($selected_state == 'highlight') ? 'active' : '' ?>" id="btn-highlight" onclick="toggleVisibility('highlight')" data-toggle="tooltip" data-title="<?= lang('activities.the_activity_will_be_featured_more_prominently_in_your_profile_and_portfoli') ?>">
                                                        <i class="ph ph-star" aria-label="<?= lang('activities.highlight') ?>"></i>
                                                    </button>
                                                <?php } ?>
                                            </div>
                                            <?php if ($highlighted_by > 0) { ?>
                                                <span data-toggle="tooltip" data-title="<?= lang('common.this_activity_is_highlighted_by_highlighted_by_other_person_s', replace: ['highlighted_by' => $highlighted_by]) ?>"><i class="ph ph-star text-signal"></i></span>
                                            <?php } ?>


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
                                                                    toastSuccess(<?= json_encode(lang('activities.this_activity_is_now_hidden_in_the_public_portal'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
                                                                } else {
                                                                    toastSuccess(<?= json_encode(lang('activities.this_activity_is_now_visible_in_the_public_portal'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
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
                                                                    toastSuccess(<?= json_encode(lang('activities.this_activity_is_now_highlighted_in_your_profile'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
                                                                } else {
                                                                    toastSuccess(<?= json_encode(lang('activities.this_activity_is_no_longer_highlighted_in_your_profile'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
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

                                <?php if (array_key_exists('key', $sections) && !empty($sections['key'])) : ?>
                                    <?php foreach ($sections['key'] as $field) : ?>
                                        <tr>
                                            <td>
                                                <span class="key"><?= $field['label'] ?></span>
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
                                'bibliography' => lang('activities.bibliography'),
                                'locations' => lang('activities.locations'),
                                'events' => lang('common.events'),
                                'people' => lang('activities.people_and_organizations'),
                                'software' => lang('activities.software'),
                                'others' => lang('activities.other_data')
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
                                                    <span class="key"><?= $field['label'] ?></span>
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
                                    <?= lang('activities.the_following_fields_are_empty') ?>
                                </small>
                                <?= implode(", ", $empty_fields) ?>
                            </p>
                        <?php } ?>
                        </table>


                        <?php if ($Settings->featureEnabled('spectrum') && isset($doc['doi']) && $doc['type'] == 'publication') : ?>
                            <h4 class="table-title">
                                <?= lang('common.research_spectrum') ?>
                                <?php if ($edit_perm) { ?>
                                    <a href="#spectrum-editor" class="ml-10" title="<?= lang('common.edit_spectrum') ?>">
                                        <i class="ph ph-edit"></i>
                                    </a>
                                <?php } ?>
                            </h4>
                            <?php
                            if (empty($openalex)) : ?>
                                <p>
                                    <?= lang('activities.the_assignment_of_topics_from_openalex_is_still_pending_please_come_back_la') ?>
                                </p>
                            <?php
                            elseif (!empty($spectrum)) :
                                include_once BASEPATH . "/php/Spectrum.php";
                                Spectrum::render($spectrum, $count = null, $class = 'mt-0');
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

                        <?php endif; ?>


                    </div>
                </div>
            </section>

            <section id="coauthors" style="display:none" class="box tab-box">
                <div class="content">


                    <div class="row row-eq-spacing">
                        <div class="col-md-6 align-self-auto">

                            <p class="mt-0">
                                <b><?= lang('activities.institutional_cooperation') ?>: </b>
                                <?php
                                switch ($doc['cooperative'] ?? '-') {
                                    case 'individual': ?>
                                        <span class="badge" data-toggle="tooltip" data-title="<?= lang('common.only_one_author') ?>">
                                            <?= lang('common.individual') ?>
                                        </span>
                                    <?php
                                        break;
                                    case 'departmental': ?>
                                        <span class="badge" data-toggle="tooltip" data-title="<?= lang('activities.authors_from_the_same_department_of_this_institution') ?>">
                                            <?= lang('activities.departmental') ?>
                                        </span>
                                    <?php
                                        break;
                                    case 'institutional': ?>
                                        <span class="badge" data-toggle="tooltip" data-title="<?= lang('activities.authors_from_different_departments_but_all_from_this_institution') ?>">
                                            <?= lang('common.institutional') ?>
                                        </span>
                                    <?php
                                        break;
                                    case 'contributing': ?>
                                        <span class="badge" data-toggle="tooltip" data-title="<?= lang('activities.authors_from_different_institutions_with_us_being_middle_authors') ?>">
                                            <?= lang('common.cooperative_contributing') ?>
                                        </span>
                                    <?php
                                        break;
                                    case 'leading': ?>
                                        <span class="badge" data-toggle="tooltip" data-title="<?= lang('activities.authors_from_different_institutions_with_us_being_leading_authors') ?>">
                                            <?= lang('common.cooperative_leading') ?>
                                        </span>
                                    <?php
                                        break;
                                    default: ?>
                                        <span class="badge" data-toggle="tooltip" data-title="<?= lang('common.no_author_affiliated') ?>">
                                            <?= lang('common.none') ?>
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
                                        'label' => lang('common.role'),
                                        'value' => fn($a) => $Format->getSupervisorRole($a['role'] ?? 'other'),
                                    ];
                                } elseif ($role === 'authors') {
                                    $thirdCol = [
                                        'label' => lang('common.position'),
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
                                            <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>/<?= $role ?>" class="btn primary small">
                                                <i class="ph ph-edit"></i>
                                                <?= lang('action.edit') ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <div class="contributors-toolbar">
                                        <?php if ($affCount > 0 && $affCount < $total) { ?>
                                            <button type="button" class="btn small btn-only-affiliated" data-active="0">
                                                <?= lang('activities.show_only_affiliated') ?> (<?= $affCount ?>)
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
                                                        echo '<tr class="show-more-row"><td><a class="btn-show-all" title="' . lang('activities.show_all_contributors') . '">&#x22ef;</a></td></tr>';
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
                                                                    data-title="<?= lang('common.author_of_the_institution') ?>">
                                                                    <i class="ph ph-handshake"></i>
                                                                    <?= lang('common.affiliated') ?>
                                                                </span>
                                                            <?php endif; ?>

                                                            <?php if ($hasUser): ?>
                                                                <?php if ($author['approved'] ?? false) { ?>
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
                                                                        <?= lang('action.claim') ?>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right w-300" aria-labelledby="<?= $dropdownId ?>">
                                                                        <div class="content font-size-12 text-danger mb-10" style="white-space: normal;">
                                                                            <?= lang('common.you_claim_that_you_are_this_author_this_activity_will_be_added_to_your_list') ?>
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
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <?php if ($hidden > 0): ?>
                                        <button type="button" class="btn small btn-show-all" data-active="0">
                                            <?= lang('activities.show_all') ?> (<?= $total ?>)
                                        </button>
                                    <?php endif; ?>
                                </div>

                            <?php } ?>

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
                                        $(this).text(active ? '<?= lang('activities.show_only_affiliated') ?> (<?= $affCount ?>)' : '<?= lang('activities.show_all') ?> (<?= $total ?>)');
                                    });

                                    $('.btn-show-all').click(function() {
                                        var active = $(this).attr('data-active') === '1';
                                        if (!active) {
                                            showAllAuthors(this);
                                            $(this).text('<?= lang('activities.show_less') ?>');
                                        } else {
                                            showPreviewAuthors(this);
                                            $(this).text('<?= lang('activities.show_all') ?> (<?= $total ?>)');
                                        }
                                        $(this).attr('data-active', active ? '0' : '1');

                                    });

                                    function showAllAuthors(button) {
                                        var area = $(button).closest('.contributor-area');
                                        area.find('.author-row').removeClass('is-hidden');
                                        area.find('.show-more-row').hide();
                                        area.find('.btn-only-affiliated').attr('data-active', '0').text('<?= lang('activities.show_only_affiliated') ?> (<?= $affCount ?>)');
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
                                <?= lang('activities.affiliation_to_units') ?>
                            </h3>
                            <?php if (count($authorTypes) > 1) { ?>
                                <div class="pills small no-borders mb-20" id="collab-type-filters">
                                    <button class="btn active" onclick="showCollaboratorChart('contributors', this)"><?= lang('common.all') ?></button>
                                    <?php if (in_array('authors', $authorTypes)) { ?>
                                        <button class="btn" onclick="showCollaboratorChart('authors', this)"><?= lang('common.authors_all_activities') ?></button>
                                    <?php } ?>
                                    <?php if (in_array('supervisors', $authorTypes)) { ?>
                                        <button class="btn" onclick="showCollaboratorChart('supervisors', this)"><?= lang('activities.supervisors') ?></button>
                                    <?php } ?>
                                    <?php if (in_array('editors', $authorTypes)) { ?>
                                        <button class="btn" onclick="showCollaboratorChart('editors', this)"><?= lang('activities.editors') ?></button>
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
                                    <?= lang('activities.departments_are_determined_based_on_the_organizational_units_of_the_authors') ?>
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
                                <span class="badge mr-5 mb-5"><?= $positions[$key] ?? $key ?></span>
                            <?php } ?>
                            <br>
                            <small class="text-muted">
                                <?= lang('common.automatically_calculated') ?>
                            </small>

                        </div>
                        <div class="col-md-6">

                            <h3 class="mt-0">
                                <?= lang('common.participating_units') ?>
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
                                                <?= lang('common.no_organisational_unit_connected') ?>
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
                                        lang('common.status_at_this_time_point') .
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
                                    echo lang('common.no_changes_tracked');
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

                <h3><?= lang('activities.citation_view') ?></h3>
                <div class="connection" id="citation-box">
                    <button class="btn primary small float-right" onclick="copyToClipboard('#citation')" data-toggle="tooltip" data-title="<?= lang('common.copy_to_clipboard') ?>" aria-label="Copy to clipboard">
                        <i class="ph ph-clipboard" aria-hidden="true"></i>
                    </button>
                    <span id="citation"><?= $print ?></span>
                </div>

                <h3>BibTeX</h3>
                <div class="connection" id="bibtex-box">
                    <button class="btn primary small float-right" onclick="copyToClipboard('#bibtex')" data-toggle="tooltip" data-title="<?= lang('common.copy_to_clipboard') ?>" aria-label="Copy to clipboard">
                        <i class="ph ph-clipboard" aria-hidden="true"></i>
                    </button>
                    <div class="overflow-x-scroll">
                        <pre id="bibtex"><?= $bibtex ?? '' ?></pre>
                    </div>
                </div>

                <h3>RIS</h3>
                <div class="connection" id="ris-box">
                    <button class="btn primary small float-right" onclick="copyToClipboard('#ris')" data-toggle="tooltip" data-title="<?= lang('common.copy_to_clipboard') ?>" aria-label="Copy to clipboard">
                        <i class="ph ph-clipboard" aria-hidden="true"></i>
                    </button>
                    <div class="overflow-x-scroll">
                        <pre id="ris"><?= $ris ?? '' ?></pre>
                    </div>
                </div>
            </section>

            <p class="text-muted font-size-12">
                *<?= lang('activities.we_use_the_term_department_here_to_refer_to_the_level_of_organizational_uni') ?>
            </p>

        </div>


    </div>