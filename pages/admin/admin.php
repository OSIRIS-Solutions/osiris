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
    <?= lang('Settings', 'Einstellungen') ?>
</h1>

<!-- search -->
<div id="search">
    <input type="text" class="form-control" id="search-input" placeholder="<?= lang('Search settings...', 'Einstellungen durchsuchen...') ?>">
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
                <b><?= lang('General Settings', 'Allgemeine Einstellungen') ?></b>
                <p><?= lang('General setting for OSIRIS', 'Allgemeine Einstellungen für OSIRIS') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/features">
                <i class="ph-duotone ph-wrench"></i>
                <b><?= lang('Features', 'Funktionen') ?></b>
                <p><?= lang('Enable, disable or configure features', 'Funktionen aktivieren, deaktivieren oder konfigurieren') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/announcements">
                <i class="ph-duotone ph-megaphone"></i>
                <b><?= lang('Announcements', 'Ankündigungen') ?></b>
                <p><?= lang('Settings for managing announcements', 'Einstellungen zur Verwaltung von Ankündigungen') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/mail">
                <i class="ph-duotone ph-envelope"></i>
                <b><?= lang('Email Settings', 'E-Mail-Einstellungen') ?></b>
                <p><?= lang('Settings for managing email configurations', 'Einstellungen zur Verwaltung von E-Mail-Konfigurationen') ?></p>
            </a>
            <?php if ($Settings->featureEnabled('portal')) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/portfolio">
                    <i class="ph-duotone ph-globe"></i>
                    <b><?= lang('Portfolio', 'Portfolio') ?></b>
                    <p><?= lang('Settings for the public portfolio', 'Einstellungen für das öffentliche Portfolio') ?></p>
                </a>
            <?php } ?>
        </div>
    <?php endif; ?>
    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="design-settings">
            <h2><i class="ph-duotone ph-palette"></i> <?= lang('Design & Branding', 'Darstellung & Branding') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/logo">
                <i class="ph-duotone ph-image"></i>
                <b><?= lang('Logo', 'Logo') ?></b>
                <p><?= lang('Upload the logo to be displayed in the header', 'Lade das Logo hoch, das im Header angezeigt werden soll') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/institute">
                <i class="ph-duotone ph-building" aria-hidden="true"></i>
                <b><?= lang('Institution', 'Einrichtung') ?></b>
                <p><?= lang('Manage the name and contact details of your institution', 'Verwalte den Namen und die Kontaktdaten deiner Einrichtung') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/design">
                <i class="ph-duotone ph-palette"></i>
                <b><?= lang('Design', 'Design') ?></b>
                <p><?= lang('Manage the corporate design of your OSIRIS installation', 'Verwalte das Corporate Design deiner OSIRIS-Installation') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/footer">
                <i class="ph-duotone ph-scales"></i>
                <b><?= lang('Footer', 'Footer') ?></b>
                <p><?= lang('Adjust the imprint and privacy policy and add links to the footer', 'Passe das Impressum und die Datenschutzerklärung an und füge Links zum Footer hinzu') ?></p>
            </a>
        </div>
    <?php endif; ?>
    <?php if ($userSyncPerm) : ?>
        <div class="col-md-6 col-lg-4" id="user-settings">
            <h2><i class="ph-duotone ph-users"></i> <?= lang('Users & Roles', 'Benutzer & Rollen') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/persons">
                <i class="ph-duotone ph-user" aria-hidden="true"></i>
                <b><?= lang('Person data', 'Personendaten') ?></b>
                <p><?= lang('Manage data fields of people', 'Verwalte Datenfelder von Personen') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/roles">
                <i class="ph-duotone ph-shield"></i>
                <b><?= lang('Roles & Permissions', 'Rollen & Rechte') ?></b>
                <p><?= lang('Settings for managing roles and permissions', 'Einstellungen zur Verwaltung von Rollen und Rechten') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/roles/distribute">
                <i class="ph-duotone ph-shield-check"></i>
                <b><?= lang('Distribute Roles', 'Rollen verteilen') ?></b>
                <p><?= lang('Easily distribute roles to users', 'Verteile Rollen einfach an Nutzer:innen') ?></p>
            </a>
            <?php
            switch (strtoupper(USER_MANAGEMENT)) {
                case 'AUTH':
            ?>
                    <a class="card" href="<?= ROOTPATH ?>/admin/authentication">
                        <i class="ph-duotone ph-users"></i>
                        <b><?= lang('Manage authentication', 'Authentifizierung verwalten') ?></b>
                        <p><?= lang('Set up how users can log in to OSIRIS', 'Lege fest, wie sich Nutzer:innen bei OSIRIS anmelden können') ?></p>
                    </a>

                <?php
                    break;
                case 'LDAP':
                ?>
                    <a class="card" href="<?= ROOTPATH ?>/admin/ldap-users">
                        <i class="ph-duotone ph-arrows-clockwise"></i>
                        <b><?= lang('Synchronize users', 'Nutzer:innen synchronisieren') ?></b>
                        <p><?= lang('Synchronize new users from your LDAP directory to OSIRIS.', 'Synchronisiere neue Nutzer:innen aus deinem LDAP-Verzeichnis mit OSIRIS.') ?></p>
                    </a>
                    <a class="card" href="<?= ROOTPATH ?>/admin/ldap-attributes">
                        <i class="ph-duotone ph-user-switch"></i>
                        <b><?= lang('Attribute synchronization', 'Attribut-Synchronisation') ?></b>
                        <p><?= lang('You can synchronize user attributes from your LDAP directory to OSIRIS.', 'Du kannst Nutzerattribute aus deinem LDAP-Verzeichnis mit OSIRIS synchronisieren.') ?></p>
                    </a>
                    <a class="card" href="<?= ROOTPATH ?>/admin/guest-account">
                        <i class="ph-duotone ph-user-circle"></i>
                        <b><?= lang('Guest Accounts', 'Gast-Accounts') ?></b>
                        <p><?= lang('Settings for managing guest accounts', 'Einstellungen zur Verwaltung von Gast-Accounts') ?></p>
                    </a>
            <?php
                    break;
                default:
                    break;
            }
            ?>
            <a class="card" href="<?= ROOTPATH ?>/admin/users">
                <i class="ph-duotone ph-users"></i>
                <b><?= lang('Add Users', 'Benutzer hinzufügen') ?></b>
                <p><?= lang('Add new users to the system', 'Füge neue Benutzer zum System hinzu') ?></p>
            </a>
        </div>
    <?php endif; ?>
    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="content-settings">
            <h2><i class="ph-duotone ph-treasure-chest"></i> <?= lang('Data Model & Content', 'Datenmodell & Inhalte') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/categories">
                <i class="ph-duotone ph-bookmarks" aria-hidden="true"></i>
                <b><?= lang('Activities', 'Aktivitäten') ?></b>
                <p><?= lang('Manage activity types and categories', 'Verwalte Aktivitätstypen und Kategorien') ?></p>
            </a>

            <a class="card" href="<?= ROOTPATH ?>/admin/doi-mappings">
                <i class="ph-duotone ph-link"></i>
                <b><?= lang('DOI Mappings', 'DOI Zuordnungen') ?></b>
                <p><?= lang('Manage type mappings for imported activities', 'Verwalte Typ-Zuordnungen für importierte Aktivitäten') ?></p>
            </a>
            <?php if ($Settings->featureEnabled('projects')) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/projects">
                    <i class="ph-duotone ph-tree-structure" aria-hidden="true"></i>
                    <b><?= lang('Projects', 'Projekte') ?></b>
                    <p><?= lang('Manage projects and proposals', 'Verwalte Projekte und Anträge') ?></p>
                </a>
            <?php } ?>
            <?php if ($Settings->featureEnabled('infrastructures')) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/infrastructures">
                    <i class="ph-duotone ph-cube-transparent" aria-hidden="true"></i>
                    <b><?= lang('Infrastructures', 'Infrastrukturen') ?></b>
                    <p><?= lang('Manage data of infrastructures', 'Verwalte Daten von Infrastrukturen') ?></p>
                </a>
            <?php } ?>
        </div><?php endif; ?>
    <?php if ($adminPerm) : ?>
        <div class="col-md-6 col-lg-4" id="custom-data-settings">
            <h2><i class="ph-duotone ph-database"></i> <?= lang('Custom data', 'Benutzerdefinierte Daten') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/fields">
                <i class="ph-duotone ph-textbox" aria-hidden="true"></i>
                <b><?= lang('Custom fields', 'Benutzerdefinierte Felder') ?></b>
                <p><?= lang('Create your own data fields for activities and projects', 'Erstelle deine eigenen Datenfelder für Aktivitäten und Projekte') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/vocabulary">
                <i class="ph-duotone ph-book-bookmark" aria-hidden="true"></i>
                <b><?= lang('Vocabularies', 'Vokabular') ?></b>
                <p><?= lang('Modify existing vocabularies for activities and projects', 'Bearbeite existierendes Vokabular für Aktivitäten und Projekte') ?></p>
            </a>
            <?php if ($Settings->featureEnabled('tags')) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/tags">
                    <i class="ph-duotone ph-tag" aria-hidden="true"></i>
                    <b><?= lang('Tags', 'Schlagwörter') ?></b>
                    <p><?= lang('Manage tags for activities and projects', 'Verwalte Tags für Aktivitäten und Projekte') ?></p>
                </a>
            <?php } ?>
            <a class="card" href="<?= ROOTPATH ?>/admin/countries">
                <i class="ph-duotone ph-globe-hemisphere-west"></i>
                <b><?= lang('Country Settings', 'Ländereinstellungen') ?></b>
                <p><?= lang('Update the list of countries', 'Aktualisiere die Liste der Länder') ?></p>
            </a>
        </div>
    <?php endif; ?>
    <?php if ($adminPerm || $reportPerm) : ?>
        <div class="col-md-6 col-lg-4" id="reporting-settings">
            <h2><i class="ph-duotone ph-chart-bar"></i> <?= lang('Reports & Tools', 'Berichte & Werkzeuge') ?></h2>
            <a class="card" href="<?= ROOTPATH ?>/admin/reports">
                <i class="ph-duotone ph-clipboard"></i>
                <b><?= lang('Report Templates', 'Berichtsvorlagen') ?></b>
                <p><?= lang('Settings for managing report templates', 'Einstellungen zur Verwaltung von Berichtsvorlagen') ?></p>
            </a>
            <?php if ($Settings->featureEnabled('quality-workflow') && $adminPerm) { ?>
                <a class="card" href="<?= ROOTPATH ?>/admin/workflows">
                    <i class="ph-duotone ph-seal-check" aria-hidden="true"></i>
                    <b><?= lang('Quality workflows', 'Qualitäts-Workflows') ?></b>
                    <p><?= lang('Manage workflows to quality-check your activities', 'Verwalte Workflows, um Aktivitäten zu prüfen') ?></p>
                </a>
            <?php } ?>
            <a class="card" href="<?= ROOTPATH ?>/admin/module-helper">
                <i class="ph-duotone ph-textbox" aria-hidden="true"></i>
                <b><?= lang('Field overview', 'Datenfelder-Übersicht') ?></b>
                <p><?= lang('Overview of all data fields and their usage', 'Übersicht über alle Datenfelder und deren Verwendung') ?></p>
            </a>
            <a class="card" href="<?= ROOTPATH ?>/admin/templates">
                <i class="ph-duotone ph-text-aa" aria-hidden="true"></i>
                <b><?= lang('Template builder', 'Template-Baukasten') ?></b>
                <p><?= lang('Create templates for exports and reports', 'Erstelle Vorlagen für Exporte und Berichte') ?></p>
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