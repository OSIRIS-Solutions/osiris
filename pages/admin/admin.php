<?php

/**
 * Administration dashboard with links to all settings pages
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin
 *
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <i class="ph-duotone ph-sliders"></i>
    <?= t('common.settings') ?>
</h1>

<!-- search -->
<div id="search">
    <input type="text" class="form-control" id="search-input" placeholder="<?= t('admin.search_settings') ?>">
</div>

<script>
    $('#search-input').on('input', function() {
        const query = $(this).val().toLowerCase();
        if (query.length === 0) {
            $('.card').show();
            return;
        }
        $('.card').each(function() {
            const title = $(this).find('b').text().toLowerCase();
            const description = $(this).find('p').text().toLowerCase();
            if (title.includes(query) || description.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
</script>

<div class="row row-eq-spacing">

    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="system-settings">
            <h2><i class="ph-duotone ph-faders"></i> System</h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/general">
                <i class="ph-duotone ph-gear"></i>
                <b><?= t('admin.general_settings') ?></b>
                <p><?= t('admin.general_setting_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/features">
                <i class="ph-duotone ph-wrench"></i>
                <b><?= t('admin.features') ?></b>
                <p><?= t('admin.features_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/languages">
                <i class="ph-duotone ph-translate"></i>
                <b><?= t('admin.languages') ?></b>
                <p><?= t('admin.language_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/api-clients">
                <i class="ph-duotone ph-key"></i>
                <b><?= t('common.api_clients') ?></b>
                <p><?= t('admin.manage_api_clients_and_their_access') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/announcements">
                <i class="ph-duotone ph-megaphone"></i>
                <b><?= t('admin.announcements') ?></b>
                <p><?= t('admin.announcements_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/mail">
                <i class="ph-duotone ph-envelope"></i>
                <b><?= t('admin.email_settings') ?></b>
                <p><?= t('admin.email_settings_description') ?></p>
            </a>
            <?php if ($Settings->featureEnabled('portal')) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/portfolio">
                    <i class="ph-duotone ph-globe"></i>
                    <b><?= t('admin.portfolio') ?></b>
                    <p><?= t('admin.portfolio_description') ?></p>
                </a>
            <?php } ?>
        </div>
    <?php endif; ?>
    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="design-settings">
            <h2><i class="ph-duotone ph-palette"></i> <?= t('admin.design_and_branding') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/logo">
                <i class="ph-duotone ph-image"></i>
                <b><?= t('admin.logo') ?></b>
                <p><?= t('admin.logo_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/institute">
                <i class="ph-duotone ph-building" aria-hidden="true"></i>
                <b><?= t('admin.institution') ?></b>
                <p><?= t('admin.institution_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/design">
                <i class="ph-duotone ph-palette"></i>
                <b><?= t('admin.design') ?></b>
                <p><?= t('admin.design_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/footer">
                <i class="ph-duotone ph-scales"></i>
                <b><?= t('admin.footer') ?></b>
                <p><?= t('admin.footer_description') ?></p>
            </a>
        </div>
    <?php endif; ?>
    <?php if ($userSyncPerm) : ?>
        <div class="col-md-6 col-lg-4" id="user-settings">
            <h2><i class="ph-duotone ph-users"></i> <?= t('admin.users_and_roles') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/persons">
                <i class="ph-duotone ph-user" aria-hidden="true"></i>
                <b><?= t('admin.person_data') ?></b>
                <p><?= t('admin.person_data_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/roles">
                <i class="ph-duotone ph-shield"></i>
                <b><?= t('admin.roles_permissions') ?></b>
                <p><?= t('admin.roles_permissions_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/roles/distribute">
                <i class="ph-duotone ph-shield-check"></i>
                <b><?= t('admin.distribute_roles') ?></b>
                <p><?= t('admin.distribute_roles_description') ?></p>
            </a>
            <?php
            switch (strtoupper(USER_MANAGEMENT)) {
                case 'AUTH':
                case 'OAUTH':
            ?>
                    <a class="card" href="<?= ROOTPATH ?>/admin/authentication">
                        <i class="ph-duotone ph-users"></i>
                        <b><?= t('admin.manage_authentication') ?></b>
                        <p><?= t('admin.manage_authentication_description') ?></p>
                    </a>

                <?php
                    break;
                case 'LDAP':
                ?>
                    <a class="card" href="<?= ROOTPATH ?>/admin/ldap-users">
                        <i class="ph-duotone ph-arrows-clockwise"></i>
                        <b><?= t('admin.synchronize_users') ?></b>
                        <p><?= t('admin.synchronize_users_description') ?></p>
                    </a>
                    <a class="card" href="<?= ROOTPATH ?>/admin/ldap-attributes">
                        <i class="ph-duotone ph-user-switch"></i>
                        <b><?= t('admin.attribute_synchronization') ?></b>
                        <p><?= t('admin.attribute_synchronization_description') ?></p>
                    </a>
                    <a class="card" href="<?= ROOTPATH ?>/admin/guest-account">
                        <i class="ph-duotone ph-user-circle"></i>
                        <b><?= t('admin.guest_accounts') ?></b>
                        <p><?= t('admin.guest_accounts_description') ?></p>
                    </a>
            <?php
                    break;
                default:
                    break;
            }
            ?>
            <a class="card" href="<?= ROOTPATH ?>/admin/users">
                <i class="ph-duotone ph-users"></i>
                <b><?= t('admin.add_users') ?></b>
                <p><?= t('admin.add_users_description') ?></p>
            </a>
        </div>
    <?php endif; ?>
    <?php if ($adminPerm || $newsOutletsPerm) : ?>
        <div class="col-md-6 col-lg-4" id="content-settings">
            <h2><i class="ph-duotone ph-treasure-chest"></i> <?= t('admin.data_model_and_content') ?></h2>
            <?php if ($adminPerm) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/categories">
                    <i class="ph-duotone ph-bookmarks" aria-hidden="true"></i>
                    <b><?= t('common.activities') ?></b>
                    <p><?= t('admin.activities_description') ?></p>
                </a>

                <?php if ($Settings->featureEnabled('projects')) { ?>
                    <a class="card" href="<?= ROOTPATH ?>/admin/projects">
                        <i class="ph-duotone ph-tree-structure" aria-hidden="true"></i>
                        <b><?= t('common.projects') ?></b>
                        <p><?= t('admin.projects_description') ?></p>
                    </a>
                <?php } ?>

                <a class="card" href="<?= ROOTPATH ?>/admin/journals">
                    <i class="ph-duotone ph-stack" aria-hidden="true"></i>
                    <b><?= t('common.journals') ?></b>
                    <p><?= t('admin.journals_description') ?></p>
                </a>

                <a class="card" href="<?= ROOTPATH ?>/admin/resource-hub">
                    <i class="ph-duotone ph-<?= e($Settings->resourceHubIcon()) ?>"></i>
                    <b><?= t('common.resource_hub') ?></b>
                    <p><?= t('admin.settings_for_the_resource_hub') ?></p>
                </a>

                <a class="card" href="<?= ROOTPATH ?>/admin/data-model">
                    <i class="ph-duotone ph-gear-six" aria-hidden="true"></i>
                    <b><?= t('common.data_model') ?></b>
                    <p><?= t('admin.manage_other_data_model_settings') ?></p>
                </a>

            <?php } ?>

            <?php if ($newsOutletsPerm) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/news">
                    <i class="ph-duotone ph-newspaper" aria-hidden="true"></i>
                    <b><?= t('admin.news_outlets') ?></b>
                    <p><?= t('admin.manage_editorial_outlets_and_their_portfolio_output') ?></p>
                </a>
            <?php } ?>
        </div><?php endif; ?>

    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="custom-data-settings">
            <h2><i class="ph-duotone ph-database"></i> <?= t('admin.custom_data') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/fields">
                <i class="ph-duotone ph-textbox" aria-hidden="true"></i>
                <b><?= t('common.custom_fields') ?></b>
                <p><?= t('admin.custom_fields_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/vocabulary">
                <i class="ph-duotone ph-book-bookmark" aria-hidden="true"></i>
                <b><?= t('admin.vocabularies') ?></b>
                <p><?= t('admin.vocabularies_description') ?></p>
            </a>
            <?php if ($Settings->featureEnabled('tags')) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/tags">
                    <i class="ph-duotone ph-tag" aria-hidden="true"></i>
                    <b><?= t('common.tags') ?></b>
                    <p><?= t('admin.tags_description') ?></p>
                </a>
            <?php } ?>
            <a class="card" href="<?= ROOTPATH ?>/admin/countries">
                <i class="ph-duotone ph-globe-hemisphere-west"></i>
                <b><?= t('admin.country_settings') ?></b>
                <p><?= t('admin.country_settings_description') ?></p>
            </a>

        </div>
    <?php endif; ?>
    <?php if ($adminPerm || $reportPerm) : ?>
        <div class="col-md-6 col-lg-4" id="reporting-settings">
            <h2><i class="ph-duotone ph-chart-bar"></i> <?= t('admin.reports_and_tools') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/reports">
                <i class="ph-duotone ph-clipboard"></i>
                <b><?= t('admin.report_templates') ?></b>
                <p><?= t('admin.report_templates_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/export-design">
                <i class="ph-duotone ph-file-doc"></i>
                <b><?= t('admin.export_design') ?></b>
                <p><?= t('admin.export_design_description') ?></p>
            </a>
            <?php if ($Settings->featureEnabled('quality-workflow') && $adminPerm) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/workflows">
                    <i class="ph-duotone ph-seal-check" aria-hidden="true"></i>
                    <b><?= t('admin.quality_workflows') ?></b>
                    <p><?= t('admin.quality_workflows_description') ?></p>
                </a>
            <?php } ?>
            <a class="card" href="<?= ROOTPATH ?>/admin/module-helper">
                <i class="ph-duotone ph-textbox" aria-hidden="true"></i>
                <b><?= t('admin.field_overview') ?></b>
                <p><?= t('admin.field_overview_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/templates">
                <i class="ph-duotone ph-text-aa" aria-hidden="true"></i>
                <b><?= t('admin.template_builder') ?></b>
                <p><?= t('admin.template_builder_description') ?></p>
            </a>
        </div>
    <?php endif; ?>

    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="integrations-settings">
            <h2><i class="ph-duotone ph-link"></i> <?= t('admin.integrations') ?></h2>

            <a class="card" href="<?= ROOTPATH ?>/admin/orcid">
                <i class="ph-duotone ph-student" aria-hidden="true"></i>
                <b><?= t('common.orcid') ?></b>
                <p><?= t('admin.orcid_setting_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/doi-mappings">
                <i class="ph-duotone ph-link"></i>
                <b><?= t('admin.doi_mappings') ?></b>
                <p><?= t('admin.doi_mappings_description') ?></p>
            </a>

        </div>
    <?php endif; ?>


    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="database-settings">
            <h2><i class="ph-duotone ph-database"></i> <?= t('admin.database') ?></h2>

            <a class="card" href="<?= ROOTPATH ?>/rerender">
                <i class="ph-duotone ph-arrows-clockwise" aria-hidden="true"></i>
                <b><?= t('admin.re_render') ?></b>
                <p><?= t('admin.re_render_all_activities_may_take_some_time') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/rerender-units">
                <i class="ph-duotone ph-arrows-clockwise" aria-hidden="true"></i>
                <b><?= t('admin.re_render_units') ?></b>
                <p><?= t('admin.re_render_all_units') ?></p>
            </a>

        </div>
    <?php endif; ?>


    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="info-settings">
            <h2><i class="ph-duotone ph-info"></i> <?= t('admin.information') ?></h2>

            <a class="card" href="<?= ROOTPATH ?>/admin/osiris-info">
                <i class="ph-duotone ph-info" aria-hidden="true"></i>
                <b><?= t('common.osiris_info') ?></b>
                <p><?= t('admin.view_osiris_configuration_information') ?></p>
            </a>

            <a class="card" href="<?= ROOTPATH ?>/admin/phpinfo">
                <i class="ph-duotone ph-info" aria-hidden="true"></i>
                <b><?= t('admin.php_info') ?></b>
                <p><?= t('admin.view_php_configuration_information') ?></p>
            </a>

            <a class="card" href="<?= ROOTPATH ?>/admin/logs">
                <i class="ph-duotone ph-file-text" aria-hidden="true"></i>
                <b><?= t('admin.import_logs') ?></b>
                <p><?= t('admin.view_import_logs') ?></p>
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    #system-settings,
    #system-settings a.card {
        --primary-color: #1E5FAF;
        --primary-color-light: #1E5FAF33;
        --primary-color-very-light: #1E5FAF1A;
    }

    #design-settings,
    #design-settings a.card {
        --primary-color: #5B4DB2;
        --primary-color-light: #5B4DB233;
        --primary-color-very-light: #5B4DB21A;
    }

    #user-settings,
    #user-settings a.card {
        --primary-color: #16616b;
        --primary-color-light: #16616b33;
        --primary-color-very-light: #16616b1A;
    }

    #content-settings,
    #content-settings a.card {
        --primary-color: #C75B12;
        --primary-color-light: #C75B1233;
        --primary-color-very-light: #C75B121A;
    }

    #custom-data-settings,
    #custom-data-settings a.card {
        --primary-color: #475569;
        --primary-color-light: #47556933;
        --primary-color-very-light: #4755691A;
    }

    #reporting-settings,
    #reporting-settings a.card {
        --primary-color: #2F855A;
        --primary-color-light: #2F855A33;
        --primary-color-very-light: #2F855A1A;
    }

    #integrations-settings,
    #integrations-settings a.card {
        --primary-color: #D53F8C;
        --primary-color-light: #D53F8C33;
        --primary-color-very-light: #D53F8C1A;
    }

    #info-settings,
    #info-settings a.card {
        --primary-color: #D69E2E;
        --primary-color-light: #D69E2E33;
        --primary-color-very-light: #D69E2E1A;
    }
</style>