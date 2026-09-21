<?php

/**
 * Admin page for managing features
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/features
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<style>
    .table td.description {
        color: var(--muted-color);
        padding-top: 0;
        padding-left: 2rem;
        padding-right: 2rem;
    }

    .with-description td {
        border-bottom: 0;
    }

    .box>.form-group>label {
        font-weight: bold;
        display: block;
        margin-bottom: 0;
    }

    .box .custom-radio {
        display: inline-block;
        margin-right: 1rem;
    }

    .box small {
        color: var(--muted-color);
        display: block;
    }

    #features-settings-page label.label {
        font-weight: bold;
        display: block;
    }

    #features-settings-page .on-this-page-nav a {
        padding-left: 1rem;
    }

    #features-settings-page .on-this-page-nav a.submenu {
        font-size: 1.2rem;
        padding-top: 0;
        padding-left: 3rem;
    }

    #features-settings-page p.description {
        font-size: 1.2rem;
        color: var(--muted-color-dark);
    }
</style>

<h1>
    <i class="ph-duotone ph-wrench"></i>
    <?= lang('admin.features') ?>
</h1>
<p class="text-muted">
    <?= lang('admin.here_you_can_enable_or_disable_features_of_osiris_some_features_may_require') ?>
</p>

<form action="<?= ROOTPATH ?>/crud/admin/general" method="post" id="role-form">
    <?php
    function renderCheckbox($feature, $default = false)
    {
        global $Settings;
        $enabled = $Settings->featureEnabled($feature, $default);
    ?>
        <div class="custom-radio">
            <input type="radio" id="<?= $feature ?>-true" value="1" name="features[<?= $feature ?>]" <?= $enabled ? 'checked' : '' ?>>
            <label for="<?= $feature ?>-true">
                <?= lang('common.enabled') ?>
            </label>
        </div>
        <div class="custom-radio">
            <input type="radio" id="<?= $feature ?>-false" value="0" name="features[<?= $feature ?>]" <?= $enabled ? '' : 'checked' ?>>
            <label for="<?= $feature ?>-false">
                <?= lang('common.disabled') ?>
            </label>
        </div>
    <?php
    }

    function badgeDeprecated()
    { ?>
        <span class="badge danger" data-toggle="tooltip" data-title="<?= lang('admin.this_feature_is_deprecated_and_is_currently_not_maintained') ?>">
            <i class="ph ph-warning"></i>
            <?= lang('admin.deprecated') ?>
        </span>
    <?php
    }

    function badgeBeta()
    { ?>
        <span class="badge signal" data-toggle="tooltip" data-title="<?= lang('admin.this_is_a_beta_feature_and_may_not_work_as_expected_use_at_your_own_risk') ?>">
            <i class="ph ph-flask"></i>
            <?= lang('admin.beta') ?>
        </span>
    <?php
    }
    ?>

    <div class="row row-eq-spacing mt-0" id="features-settings-page">
        <div class="col-md-9">

            <!-- search -->
            <input type="search" class="form-control" id="feature-search" placeholder="<?= lang('admin.search_features') ?>" onkeyup="searchFeatures()">
            <script>
                function searchFeatures() {
                    const input = document.getElementById('feature-search');
                    const filter = input.value.toLowerCase();
                    const boxes = $('#features-settings-page .box');

                    if (filter === '') {
                        boxes.show();
                        return;
                    }

                    boxes.each(function() {
                        const text = $(this).text().toLowerCase();
                        if (text.includes(filter)) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            </script>

            <!-- Core Features Section -->

            <section id="core-features">
                <h3 class="header">
                    <?= lang('admin.core_features') ?>
                </h3>

                <div class="box padded">
                    <h4 class="title" id="portal">
                        <?= lang('OSIRIS Portfolio') ?>
                    </h4>

                    <p class="description">
                        <?= lang('admin.the_osiris_portfolio_is_a_public_facing_website_that_showcases_the_research') ?>
                    </p>

                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.portfolio_previews_and_api') ?>
                        </label>
                        <?php
                        renderCheckbox('portal');
                        ?>
                    </div>

                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.public_portal_without_login_on_start_page') ?>
                        </label>
                        <?php
                        renderCheckbox('portal-public');
                        ?>
                    </div>

                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('common.show_research_spectrum_in_portfolio') ?>
                        </label>
                        <?php
                        renderCheckbox('portfolio-spectrum');
                        ?>
                        <small class="text-muted">
                            <?= lang('admin.this_feature_requires_the_research_spectrum_to_be_enabled') ?>
                        </small>
                    </div>

                </div>

                <div class="box padded">
                    <h4 class="title" id="projects">
                        <?= lang('admin.projects_and_proposals') ?>
                    </h4>

                    <p class="description">
                        <?= lang('admin.osiris_is_able_to_manage_complete_project_life_cycles_from_proposal_submiss') ?>
                    </p>

                    <div class="form-group">
                        <?php
                        renderCheckbox('projects');
                        ?>
                    </div>

                    <h5>Nagoya Protocol Compliance</h5>
                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.add_nagoya_protocol_compliance_to_proposals') ?>
                        </label>
                        <?php
                        renderCheckbox('nagoya');
                        ?>
                    </div>
                </div>

                <div class="box padded">
                    <h4 class="title" id="teaching-modules">
                        <?= lang('common.teaching_modules') ?>
                    </h4>
                    <p class="description">
                        <?= lang('admin.it_is_possible_to_centrally_manage_teaching_modules_e_g_at_universities_and') ?>
                    </p>
                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.show_teaching_modules_in_sidebar') ?>
                        </label>
                        <?php
                        renderCheckbox('teaching-modules', true);
                        ?>
                    </div>
                </div>

                <div class="box padded">
                    <h4 class="title" id="research-topics">
                        <?= lang('admin.research_topics') ?>
                    </h4>
                    <div class="form-group">
                        <?php
                        renderCheckbox('topics');
                        ?>
                    </div>
                    <div class="form-group">
                        <?php
                        $label = $Settings->get('topics_label');
                        ?>
                        <div class="row row-eq-spacing my-0">
                            <div class="col-md-6">
                                <label for="topics_label" class="d-flex"><?= lang('common.label') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                <input name="general[topics_label][en]" id="topics_label" type="text" class="form-control" value="<?= e($label['en'] ?? 'Research topics') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="topics_label_de" class="d-flex"><?= lang('common.label') ?> (Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                <input name="general[topics_label][de]" id="topics_label_de" type="text" class="form-control" value="<?= e($label['de'] ?? 'Forschungsbereiche') ?>">
                            </div>
                        </div>
                    </div>

                    <?php
                    $n_topics = $osiris->topics->count();
                    $list_fields = $osiris->adminFields->find(['format' => 'list'])->toArray();
                    if ($n_topics == 0 && count($list_fields) > 0) { ?>
                        <div class="mb-20">
                            <a href="#migrate-topics" class="btn">
                                <?= lang('admin.migrate_custom_fields_to_topics') ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>

                <div class="box padded">
                    <h4 class="title" id="infrastructures">
                        <?= lang('admin.infrastructures_in_osiris') ?>
                    </h4>
                    <div class="form-group">
                        <?php
                        renderCheckbox('infrastructures');
                        ?>
                    </div>
                    <div class="form-group">
                        <?php
                        $label = $Settings->get('infrastructures_label');
                        ?>

                        <div class="row row-eq-spacing my-0">
                            <div class="col-md-6">
                                <label for="infrastructures_label" class="d-flex"><?= lang('common.label') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                <input name="general[infrastructures_label][en]" id="infrastructures_label" type="text" class="form-control" value="<?= e($label['en'] ?? 'Infrastructures') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="infrastructures_label_de" class="d-flex"><?= lang('common.label') ?> (Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                <input name="general[infrastructures_label][de]" id="infrastructures_label_de" type="text" class="form-control" value="<?= e($label['de'] ?? 'Infrastrukturen') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box padded">
                    <h4 class="title" id="calendar">
                        <?= lang('admin.calendar_and_events') ?>
                    </h4>
                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.enable_central_event_management') ?>
                        </label>
                        <?php
                        renderCheckbox('events', true);
                        ?>
                    </div>
                    <div class="form-group">
                        <label for="events" class="label">
                            <?= lang('admin.add_deadlines_to_central_event_management') ?>
                        </label>
                        <?php
                        renderCheckbox('deadlines', false);
                        ?>
                    </div>
                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.show_the_calendar_in_sidebar') ?>
                        </label>
                        <?php
                        renderCheckbox('calendar', false);
                        ?>
                    </div>
                </div>

                <div class="box padded">
                    <h4 class="title" id="tags">
                        <?= lang('common.tags') ?>
                    </h4>
                    <p class="description">
                        <?= lang('admin.tags_can_be_used_to_label_and_categorize_activities_projects_and_events_by') ?>
                    </p>
                    <div class="form-group">
                        <?php
                        renderCheckbox('tags');
                        ?>
                    </div>

                    <div class="form-group">
                        <?php
                        $label = $Settings->get('tags_label');
                        ?>
                        <div class="row row-eq-spacing my-0">
                            <div class="col-md-6">
                                <label for="tags_label" class="d-flex"><?= lang('common.label') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                <input name="general[tags_label][en]" id="tags_label" type="text" class="form-control" value="<?= e($label['en'] ?? 'Tags') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="tags_label_de" class="d-flex"><?= lang('common.label') ?> (Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                <input name="general[tags_label][de]" id="tags_label_de" type="text" class="form-control" value="<?= e($label['de'] ?? 'Schlagwörter') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box padded">
                    <h4 class="title" id="trips">
                        <?= lang('admin.research_trips') ?>
                    </h4>
                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.enable_a_module_for_analysing_research_trips') ?>
                        </label>

                        <p class="text-muted">
                            <?= lang('admin.the_add_on_requires_an_activity_type_called_travel_that_has_the_following_d') ?>
                        </p>
                        <?php
                        $trips = $Settings->featureEnabled('trips');

                        $travel_available = $osiris->adminTypes->count(['id' => 'travel']);
                        $modules_available = $osiris->adminTypes->count(['modules' => ['$in' => ['status', 'countries', 'country', 'status*',  'countries*', 'country*']]]);

                        if ($travel_available == 0) { ?>
                            <p>
                                <i class="ph ph-warning text-danger"></i>
                                <?= lang('admin.the_activity_type_travel_is_not_available_please_create_it_first') ?>
                            </p>
                        <?php } else if ($modules_available == 0) { ?>
                            <p>
                                <i class="ph ph-warning text-danger"></i>
                                <?= lang('admin.the_activity_type_travel_does_not_have_the_required_data_fields_please_add') ?>
                            </p>
                        <?php } else { ?>
                            <p>
                                <i class="ph ph-seal-check text-success"></i>
                                <?= lang('admin.the_module_is_available_and_can_be_activated_here') ?>
                            </p>

                            <div class="custom-radio">
                                <input type="radio" id="trips-true" value="1" name="features[trips]" <?= $trips ? 'checked' : '' ?>>
                                <label for="trips-true"><?= lang('common.enabled_features') ?></label>
                            </div>

                            <div class="custom-radio">
                                <input type="radio" id="trips-false" value="0" name="features[trips]" <?= $trips ? '' : 'checked' ?>>
                                <label for="trips-false"><?= lang('common.disabled_features') ?></label>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="box padded">
                    <h4 class="title" id="wordcloud">
                        <?= lang('admin.word_clouds') ?>
                    </h4>
                    <div class="form-group">
                        <?php
                        renderCheckbox('wordcloud');
                        ?>
                    </div>
                </div>
            </section>

            <!-- Reporting & Quality Features Section -->

            <section id="reporting-quality-features">
                <h3 class="header">
                    <?= lang('admin.reporting_quality') ?>
                </h3>
                <div class="box padded">
                    <h4 class="title" id="quarterly-reporting">
                        <?= lang('admin.quarterly_reporting') ?>
                    </h4>
                    <div class="form-group">

                        <p class="description">
                            <?= lang('admin.osiris_reminds_users_every_3_months_to_update_their_activities_and_submit_t') ?>
                            <br>
                            <?= lang('admin.if_you_do_not_wish_to_use_this_function_you_can_deactivate_it_here_reminder') ?>
                        </p>

                        <?php
                        renderCheckbox('quarterly-reporting', true);
                        ?>
                    </div>
                </div>

                <div class="box padded">
                    <h4 class="title" id="quality-workflow">
                        <?= lang('admin.quality_workflows_of_activities') ?>
                    </h4>
                    <div class="form-group">
                        <p class="description">
                            <?= lang('admin.you_can_enable_a_quality_workflow_for_activities_this_means_that_users_can') ?>
                        </p>
                        <?php
                        renderCheckbox('quality-workflow', false);
                        ?>
                    </div>
                </div>


                <div class="box padded">
                    <h4 class="title" id="drafts">
                        <?= lang('common.drafts') ?>
                    </h4>
                    <div class="form-group">
                        <p class="description">
                            <?= lang('admin.you_can_enable_drafts_for_activities_this_means_that_users_can_save_their_a') ?>
                        </p>
                        <?php
                        renderCheckbox('drafts', false);
                        ?>
                    </div>
                </div>

                <div class="box padded">
                    <h4>
                        <?= lang('admin.ida_integration') ?>
                    </h4>
                    <?= badgeDeprecated() ?>
                    <p class="description">
                        <?= lang('admin.ida_is_an_information_system_for_data_collection_and_evaluation_used_by_the') ?>
                    </p>
                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.enable_integration_with_the_ida_tool') ?>
                        </label>

                        <?php
                        renderCheckbox('ida');
                        ?>
                    </div>
                </div>
            </section>


            <!-- Imports & External Features Section -->

            <section id="imports-external-features">
                <h3 class="header">
                    <?= lang('admin.imports_external_features') ?>
                </h3>
                <div class="box padded">
                    <h4 class="title" id="imports">
                        <?= lang('admin.imports') ?>
                    </h4>
                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.allow_user_import_from_google_scholar') ?>
                        </label>
                        <?php
                        renderCheckbox('googlescholar', true);
                        ?>
                    </div>


                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.allow_user_import_from_openalex') ?>
                        </label>
                        <?php
                        renderCheckbox('openalex', true);
                        ?>
                    </div>
                </div>

                <div class="box padded">
                    <h4 class="title" id="altmetrics">
                        <?= lang('admin.altmetrics') ?>
                    </h4>
                    <?= badgeBeta() ?>
                    <p class="description">
                        <?= lang('admin.altmetrics_are_alternative_metrics_that_measure_the_attention_and_impact_of') ?>
                        <br>
                        <?= lang('admin.in_this_first_version_only_public_badges_are_supported') ?>
                    </p>
                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.enable_altmetrics_for_publications') ?>
                        </label>
                        <?php
                        renderCheckbox('altmetrics');
                        ?>
                    </div>
                </div>

                <div class="box padded">
                    <h4 class="title" id="spectrum">
                        <?= lang('common.research_spectrum') ?>
                    </h4>

                    <?= badgeBeta() ?>

                    <p class="description">
                        <?= lang('admin.the_research_spectrum_is_based_on_openalex_topics_and_provides_a_visual_rep') ?>
                    </p>

                    <div class="form-group">
                        <?php
                        renderCheckbox('spectrum');
                        ?>
                    </div>
                </div>
            </section>



            <section id="guest-management-features">
                <h3 class="header">
                    <?= lang('admin.profiles_and_guests') ?>
                </h3>



                <div class="box padded">
                    <h4 class="title" id="home-page">
                        <?= lang('admin.home_page_features') ?>
                    </h4>

                    <div class="form-group mt-10" id="new-publications">
                        <label for="" class="label">
                            <?= lang('admin.show_new_publications_on_the_home_page') ?>
                        </label>
                        <?php
                        renderCheckbox('new-publications', true);
                        ?>
                    </div>

                    <div class="form-group mt-10" id="new-colleagues">
                        <label for="" class="label">
                            <?= lang('admin.show_new_colleagues_on_the_home_page') ?>
                        </label>
                        <?php
                        renderCheckbox('new-colleagues');
                        ?>
                    </div>

                    <div class="form-group mt-10" id="news">
                        <label for="" class="label">
                            <?= lang('admin.show_news_on_the_home_page') ?>
                        </label>
                        <?php
                        renderCheckbox('news', true);
                        ?>
                    </div>

                    <div class="form-group">
                        <?php
                        $news_lang = $Settings->get('news-language', 'both');
                        ?>

                        <label for="news-language">
                            <?= lang('admin.language_of_news') ?>
                        </label>
                        <select name="general[news-language]" id="news-language" class="form-control small">
                            <option value="one" <?= $news_lang == 'en' ? 'selected' : '' ?>><?= lang('admin.only_one_language') ?></option>
                            <option value="both" <?= $news_lang == 'both' ? 'selected' : '' ?>><?= lang('common.both_languages') ?></option>
                        </select>
                    </div>

                </div>

                <div class="box padded">
                    <h4 class="title" id="guest-forms">
                        <?= lang('admin.guest_forms') ?>
                    </h4>

                    <?= badgeBeta() ?>

                    <div class="form-group mt-10">
                        <label for="" class="label">
                            <?= lang('admin.guests_can_be_registered_in_osiris') ?>
                        </label>
                        <?php
                        renderCheckbox('guests');
                        ?>
                    </div>


                    <div class="form-group">
                        <label for="" class="label">
                            <?= lang('admin.external_guest_forms_to_complete_registration') ?>
                        </label>
                        <?php
                        renderCheckbox('guest-forms');
                        ?>

                        <div class="row mt-10">
                            <label for="guest-forms-server" class="w-150 col flex-reset"><?= lang('admin.server_address') ?></label>
                            <input type="text" class="form-control small col" name="general[guest-forms-server]" id="guest-forms-server" value="<?= $Settings->get('guest-forms-server') ?>">
                        </div>
                        <div class="row mt-10">
                            <label for="guest-forms-secret-key" class="w-150 col flex-reset"><?= lang('Secret key') ?></label>
                            <input type="text" class="form-control small col" name="general[guest-forms-secret-key]" id="guest-forms-secret-key" value="<?= $Settings->get('guest-forms-secret-key') ?>">
                        </div>

                    </div>

                </div>
            </section>

            <!-- resource hub -->
            <section id="others-hub">
                <h3 class="header">
                    <?= lang('admin.other_features') ?>
                </h3>

                <div class="box padded">
                    <h4 class="title" id="resource-hub">
                        <?= lang('common.resource_hub') ?>
                    </h4>

                    <div class="form-group mt-10">
                        <label for="" class="label">
                            <?= lang('admin.enable_resource_hub') ?>
                        </label>
                        <?php
                        renderCheckbox('resource-hub');
                        ?>
                    </div>

                    <p class="description">
                        <?= lang('admin.more_configuration_options_for_the_resource_hub_can_be_found_after_enabling') ?>
                    </p>
                </div>
            </section>

            <div class="bottom-buttons">
                <button class="btn success" type="submit">
                    <i class="ph ph-floppy-disk"></i>
                    <?= lang('common.save_changes') ?>
                </button>
            </div>
        </div>


        <div class="col-md-3 d-none d-md-block">
            <nav class="on-this-page-nav">
                <div class="">
                    <div class="title"><?= lang('admin.features') ?></div>

                    <a href="#core-features"><?= lang('admin.core_features') ?></a>
                    <a href="#portal" class="submenu"><?= lang('OSIRIS Portfolio') ?></a>
                    <a href="#projects" class="submenu"><?= lang('admin.projects_and_proposals') ?></a>
                    <a href="#teaching-modules" class="submenu"><?= lang('common.teaching_modules') ?></a>
                    <a href="#research-topics" class="submenu"><?= lang('admin.research_topics') ?></a>
                    <a href="#infrastructures" class="submenu"><?= lang('common.infrastructures') ?></a>
                    <a href="#calendar" class="submenu"><?= lang('admin.calendar_and_events') ?></a>
                    <a href="#tags" class="submenu"><?= lang('common.tags') ?></a>
                    <a href="#trips" class="submenu"><?= lang('admin.research_trips') ?></a>
                    <a href="#wordcloud" class="submenu"><?= lang('admin.word_clouds') ?></a>

                    <a href="#reporting-quality-features"><?= lang('admin.reporting_quality') ?></a>
                    <a href="#quarterly-reporting" class="submenu"><?= lang('admin.quarterly_reporting') ?></a>
                    <a href="#quality-workflow" class="submenu"><?= lang('admin.quality_workflows') ?></a>
                    <a href="#drafts" class="submenu"><?= lang('common.drafts') ?></a>
                    <a href="#ida" class="submenu"><?= lang('admin.ida_integration') ?></a>

                    <a href="#imports-external-features"><?= lang('admin.imports_external_features') ?></a>
                    <a href="#imports" class="submenu"><?= lang('admin.imports') ?></a>
                    <a href="#altmetrics" class="submenu"><?= lang('admin.altmetrics') ?></a>
                    <a href="#spectrum" class="submenu"><?= lang('common.research_spectrum') ?></a>

                    <a href="#guest-management-features"><?= lang('admin.profiles_and_guests') ?></a>
                    <a href="#home-page" class="submenu"><?= lang('admin.home_page') ?></a>
                    <a href="#guest-forms" class="submenu"><?= lang('admin.guest_forms') ?></a>
                </div>

            </nav>

        </div>

    </div>


</form>




<?php if ($n_topics == 0 && count($list_fields) > 0) { ?>

    <div class="modal" id="migrate-topics" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#!">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="modal-title">
                    <?= lang('admin.migrate_custom_fields_to_research_topics') ?>
                </h5>

                <form action="<?= ROOTPATH ?>/migrate/custom-fields-to-topics" method="post">
                    <div class="form-group ">
                        <label for="field"><?= lang('admin.select_a_field_you_want_to_use') ?></label>

                        <select name="field" id="field" class="form-control">
                            <?php foreach ($list_fields as $field) { ?>
                                <option value="<?= $field['id'] ?>"><?= lang($field['name'], $field['name_de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <?= lang('admin.the_following_will_happen_if_you_click_on_migrate') ?>

                    <ul class="list">
                        <li>
                            <?= lang('admin.the_selected_custom_field_is_used_to_create_new_research_areas_on_this_basi') ?>
                        </li>
                        <li>
                            <?= lang('admin.all_activities_for_which_the_custom_field_was_completed_are_assigned_to_the') ?>
                        </li>
                        <li>
                            <?= lang('admin.the_custom_field_is_then_deleted_i_e_the_field_itself_the_assignment_to_for') ?>
                        </li>
                    </ul>

                    <button class="btn primary">
                        <?= lang('admin.migrate') ?>
                    </button>
                </form>

            </div>
        </div>
    </div>

<?php } ?>