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
    <?= lang('common.settings') ?>
</h1>

<!-- search -->
<div id="search">
    <input type="text" class="form-control" id="search-input" placeholder="<?= lang('admin.search_settings') ?>">
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
                <b><?= lang('admin.general_settings') ?></b>
                <p><?= lang('admin.general_setting_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/features">
                <i class="ph-duotone ph-wrench"></i>
                <b><?= lang('admin.features') ?></b>
                <p><?= lang('admin.features_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/announcements">
                <i class="ph-duotone ph-megaphone"></i>
                <b><?= lang('admin.announcements') ?></b>
                <p><?= lang('admin.announcements_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/mail">
                <i class="ph-duotone ph-envelope"></i>
                <b><?= lang('admin.email_settings') ?></b>
                <p><?= lang('admin.email_settings_description') ?></p>
            </a>
            <?php if ($Settings->featureEnabled('portal')) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/portfolio">
                    <i class="ph-duotone ph-globe"></i>
                    <b><?= lang('admin.portfolio') ?></b>
                    <p><?= lang('admin.portfolio_description') ?></p>
                </a>
            <?php } ?>
        </div>
    <?php endif; ?>
    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="design-settings">
            <h2><i class="ph-duotone ph-palette"></i> <?= lang('admin.design_and_branding') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/logo">
                <i class="ph-duotone ph-image"></i>
                <b><?= lang('admin.logo') ?></b>
                <p><?= lang('admin.logo_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/institute">
                <i class="ph-duotone ph-building" aria-hidden="true"></i>
                <b><?= lang('admin.institution') ?></b>
                <p><?= lang('admin.institution_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/design">
                <i class="ph-duotone ph-palette"></i>
                <b><?= lang('admin.design') ?></b>
                <p><?= lang('admin.design_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/footer">
                <i class="ph-duotone ph-scales"></i>
                <b><?= lang('admin.footer') ?></b>
                <p><?= lang('admin.footer_description') ?></p>
            </a>
        </div>
    <?php endif; ?>
    <?php if ($userSyncPerm) : ?>
        <div class="col-md-6 col-lg-4" id="user-settings">
            <h2><i class="ph-duotone ph-users"></i> <?= lang('admin.users_and_roles') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/persons">
                <i class="ph-duotone ph-user" aria-hidden="true"></i>
                <b><?= lang('admin.person_data') ?></b>
                <p><?= lang('admin.person_data_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/roles">
                <i class="ph-duotone ph-shield"></i>
                <b><?= lang('admin.roles_permissions') ?></b>
                <p><?= lang('admin.roles_permissions_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/roles/distribute">
                <i class="ph-duotone ph-shield-check"></i>
                <b><?= lang('admin.distribute_roles') ?></b>
                <p><?= lang('admin.distribute_roles_description') ?></p>
            </a>
            <?php
            switch (strtoupper(USER_MANAGEMENT)) {
                case 'AUTH':
            ?>
                    <a class="card" href="<?= ROOTPATH ?>/admin/authentication">
                        <i class="ph-duotone ph-users"></i>
                        <b><?= lang('admin.manage_authentication') ?></b>
                        <p><?= lang('admin.manage_authentication_description') ?></p>
                    </a>

                <?php
                    break;
                case 'LDAP':
                ?>
                    <a class="card" href="<?= ROOTPATH ?>/admin/ldap-users">
                        <i class="ph-duotone ph-arrows-clockwise"></i>
                        <b><?= lang('admin.synchronize_users') ?></b>
                        <p><?= lang('admin.synchronize_users_description') ?></p>
                    </a>
                    <a class="card" href="<?= ROOTPATH ?>/admin/ldap-attributes">
                        <i class="ph-duotone ph-user-switch"></i>
                        <b><?= lang('admin.attribute_synchronization') ?></b>
                        <p><?= lang('admin.attribute_synchronization_description') ?></p>
                    </a>
                    <a class="card" href="<?= ROOTPATH ?>/admin/guest-account">
                        <i class="ph-duotone ph-user-circle"></i>
                        <b><?= lang('admin.guest_accounts') ?></b>
                        <p><?= lang('admin.guest_accounts_description') ?></p>
                    </a>
            <?php
                    break;
                default:
                    break;
            }
            ?>
            <a class="card" href="<?= ROOTPATH ?>/admin/users">
                <i class="ph-duotone ph-users"></i>
                <b><?= lang('admin.add_users') ?></b>
                <p><?= lang('admin.add_users_description') ?></p>
            </a>
        </div>
    <?php endif; ?>
    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="content-settings">
            <h2><i class="ph-duotone ph-treasure-chest"></i> <?= lang('admin.data_model_and_content') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/categories">
                <i class="ph-duotone ph-bookmarks" aria-hidden="true"></i>
                <b><?= lang('common.activities') ?></b>
                <p><?= lang('admin.activities_description') ?></p>
            </a>

            <?php if ($Settings->featureEnabled('projects')) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/projects">
                    <i class="ph-duotone ph-tree-structure" aria-hidden="true"></i>
                    <b><?= lang('common.projects') ?></b>
                    <p><?= lang('admin.projects_description') ?></p>
                </a>
            <?php } ?>
            <?php if ($Settings->featureEnabled('infrastructures')) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/infrastructures">
                    <i class="ph-duotone ph-cube-transparent" aria-hidden="true"></i>
                    <b><?= lang('common.infrastructures') ?></b>
                    <p><?= lang('admin.infrastructures_description') ?></p>
                </a>
            <?php } ?>

            <a class="card" href="<?= ROOTPATH ?>/admin/journals">
                <i class="ph-duotone ph-stack" aria-hidden="true"></i>
                <b><?= lang('common.journals') ?></b>
                <p><?= lang('admin.journals_description') ?></p>
            </a>
        </div><?php endif; ?>

    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="custom-data-settings">
            <h2><i class="ph-duotone ph-database"></i> <?= lang('admin.custom_data') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/fields">
                <i class="ph-duotone ph-textbox" aria-hidden="true"></i>
                <b><?= lang('common.custom_fields') ?></b>
                <p><?= lang('admin.custom_fields_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/vocabulary">
                <i class="ph-duotone ph-book-bookmark" aria-hidden="true"></i>
                <b><?= lang('admin.vocabularies') ?></b>
                <p><?= lang('admin.vocabularies_description') ?></p>
            </a>
            <?php if ($Settings->featureEnabled('tags')) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/tags">
                    <i class="ph-duotone ph-tag" aria-hidden="true"></i>
                    <b><?= lang('common.tags') ?></b>
                    <p><?= lang('admin.tags_description') ?></p>
                </a>
            <?php } ?>
            <a class="card" href="<?= ROOTPATH ?>/admin/countries">
                <i class="ph-duotone ph-globe-hemisphere-west"></i>
                <b><?= lang('admin.country_settings') ?></b>
                <p><?= lang('admin.country_settings_description') ?></p>
            </a>

        </div>
    <?php endif; ?>
    <?php if ($adminPerm || $reportPerm) : ?>
        <div class="col-md-6 col-lg-4" id="reporting-settings">
            <h2><i class="ph-duotone ph-chart-bar"></i> <?= lang('admin.reports_and_tools') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/reports">
                <i class="ph-duotone ph-clipboard"></i>
                <b><?= lang('admin.report_templates') ?></b>
                <p><?= lang('admin.report_templates_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/export-design">
                <i class="ph-duotone ph-file-doc"></i>
                <b><?= lang('admin.export_design') ?></b>
                <p><?= lang('admin.export_design_description') ?></p>
            </a>
            <?php if ($Settings->featureEnabled('quality-workflow') && $adminPerm) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/workflows">
                    <i class="ph-duotone ph-seal-check" aria-hidden="true"></i>
                    <b><?= lang('admin.quality_workflows') ?></b>
                    <p><?= lang('admin.quality_workflows_description') ?></p>
                </a>
            <?php } ?>
            <a class="card" href="<?= ROOTPATH ?>/admin/module-helper">
                <i class="ph-duotone ph-textbox" aria-hidden="true"></i>
                <b><?= lang('admin.field_overview') ?></b>
                <p><?= lang('admin.field_overview_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/templates">
                <i class="ph-duotone ph-text-aa" aria-hidden="true"></i>
                <b><?= lang('admin.template_builder') ?></b>
                <p><?= lang('admin.template_builder_description') ?></p>
            </a>
        </div>
    <?php endif; ?>

    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="integrations-settings">
            <h2><i class="ph-duotone ph-link"></i> <?= lang('admin.integrations') ?></h2>

            <a class="card" href="<?= ROOTPATH ?>/admin/orcid">
                <i class="ph-duotone ph-student" aria-hidden="true"></i>
                <b><?= lang('common.orcid') ?></b>
                <p><?= lang('admin.orcid_setting_description') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/doi-mappings">
                <i class="ph-duotone ph-link"></i>
                <b><?= lang('admin.doi_mappings') ?></b>
                <p><?= lang('admin.doi_mappings_description') ?></p>
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
</style>