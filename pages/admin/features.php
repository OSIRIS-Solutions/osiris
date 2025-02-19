<?php

/**
 * Page for admin dashboard for features settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/features
 *
 * @package OSIRIS
 * @since 1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<h1>
    <?= lang('Features', 'Funktionen') ?>
</h1>

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
</style>

<form action="<?= ROOTPATH ?>/crud/admin/features" method="post" id="role-form">

    <div class="box px-20">
        <h3>
            <?= lang('General settings', 'Allgemeine Einstellungen') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Coins', 'Coins') ?>
            </label>
            <?php
            $coins = $Settings->featureEnabled('coins');
            ?>
            <div class=" custom-radio">
                <input type="radio" id="coins-true" value="1" name="values[coins]" <?= $coins ? 'checked' : '' ?>>
                <label for="coins-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="coins-false" value="0" name="values[coins]" <?= $coins ? '' : 'checked' ?>>
                <label for="coins-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

            <small> Coins werden nirgendwo gespeichert, sondern on-demand berechnet. Wenn ihr Coins global ausschaltet, werden sie also gar nicht erst berechnet und nirgendwo gezeigt.</small>

        </div>

        <div class="form-group">
            <label for="">
                <?= lang('Achievements', 'Errungenschaften') ?>
            </label>
            <?php
            $achievements = $Settings->featureEnabled('achievements');
            ?>

            <div class="custom-radio">
                <input type="radio" id="achievements-true" value="1" name="values[achievements]" <?= $achievements ? 'checked' : '' ?>>
                <label for="achievements-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="achievements-false" value="0" name="values[achievements]" <?= $achievements ? '' : 'checked' ?>>
                <label for="achievements-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

        </div>


        <div class="form-group">
            <label for="">
                <?= lang('User profile metrics', 'Metriken im Nutzerprofil') ?>
            </label>
            <?php
            $user_metrics = $Settings->featureEnabled('user-metrics');
            ?>

            <div class=" custom-radio">
                <input type="radio" id="user-metrics-true" value="1" name="values[user-metrics]" <?= $user_metrics ? 'checked' : '' ?>>
                <label for="user-metrics-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="user-metrics-false" value="0" name="values[user-metrics]" <?= $user_metrics ? '' : 'checked' ?>>
                <label for="user-metrics-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

            <small> Wenn diese Funktion ausgeschaltet wird, sind Nutzermetriken (Graphen) nur noch auf der eigenen Profilseite sichtbar.</small>

        </div>

        <div class="form-group">
            <label for="">
                <?= lang('Profile images', 'Profilbilder der Nutzenden') ?>
            </label>
            <?php
            $db_pictures = $Settings->featureEnabled('db_pictures');
            ?>
            <div class=" custom-radio">
                <input type="radio" id="db_pictures-true" value="1" name="values[db_pictures]" <?= $db_pictures ? 'checked' : '' ?>>
                <label for="db_pictures-true"><?= lang('Save in database', 'In Datenbank speichern') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="db_pictures-false" value="0" name="values[db_pictures]" <?= $db_pictures ? '' : 'checked' ?>>
                <label for="db_pictures-false"><?= lang('Save in file system', 'Im Dateisystem speichern') ?></label>
            </div>

            <small>
                <?= lang(
                    'Saving the profile pictures in the database is recommended if the pictures are maintained exclusively via OSIRIS. If the images are saved in the file system, they can be uploaded more easily (into the folder <code>/img/users</code>) and, for example, updated automatically. However, they must then have the user name as the name and be in JPEG format!',
                    'Die Profilbilder in der Datenbank zu speichern wird empfohlen, wenn die Bilder ausschließlich über OSIRIS gepflegt werden. Wenn die Bilder im Dateisystem gespeichert werden, kann man sie leichter anders hochladen (in den Ordner <code>/img/users</code>) und z.B. automatisch aktualisieren. Sie müssen dann aber den Username als Namen haben und im JPEG-Format sein!'
                ) ?>
            </small>
        </div>


        <?php if (strtoupper(USER_MANAGEMENT) !== 'AUTH') { ?>
            <div class="form-group">
                <label for="">
                    <?= lang('LDAP user synchronization', 'LDAP-Nutzersynchronisierung') ?>
                </label>
                <?php
                $sync = $Settings->featureEnabled('ldap-sync');
                ?>

                <div class="form-">
                    <label for="ldap-sync-blacklist"><?= lang('Username Blacklist (separated by comma)', 'Username-Blacklist (Komma-getrennt)') ?></label>
                    <textarea class="form-control small" name="general[ldap-sync-blacklist]" id="ldap-sync-blacklist"><?= $Settings->get('ldap-sync-blacklist') ?></textarea>
                </div>
                <div class="form-">
                    <label for="ldap-sync-whitelist"><?= lang('Username whitelist (separated by comma)', 'Username-Whitelist (Komma-getrennt)') ?></label>
                    <textarea class="form-control small" name="general[ldap-sync-whitelist]" id="ldap-sync-whitelist"><?= $Settings->get('ldap-sync-whitelist') ?></textarea>
                </div>

            </div>
        <?php } ?>
    </div>


    <div class="box px-20">
        <h3>
            <?= lang('Guests', 'Gäste') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Guests can be registered in OSIRIS', 'Gäste können in OSIRIS angemeldet werden') ?>
            </label>
            <?php
            $guests = $Settings->featureEnabled('guests');
            ?>

            <div class="custom-radio">
                <input type="radio" id="guests-true" value="1" name="values[guests]" <?= $guests ? 'checked' : '' ?>>
                <label for="guests-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="guests-false" value="0" name="values[guests]" <?= $guests ? '' : 'checked' ?>>
                <label for="guests-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

        </div>


        <div class="form-group">
            <label for="">
                <?= lang('External guest forms to complete registration', 'Externe Gästeformulare, um die Registration abzuschließen') ?>
            </label>
            <?php
            $guests = $Settings->featureEnabled('guest-forms');
            ?>

            <div class="custom-radio">
                <input type="radio" id="guest-forms-true" value="1" name="values[guest-forms]" <?= $guests ? 'checked' : '' ?>>
                <label for="guest-forms-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>
            <div class="custom-radio">
                <input type="radio" id="guest-forms-false" value="0" name="values[guest-forms]" <?= $guests ? '' : 'checked' ?>>
                <label for="guest-forms-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>
            <div class="row mt-10">
                <label for="guest-forms-server" class="w-150 col flex-reset"><?= lang('Server address', 'Server-Adresse') ?></label>
                <input type="text" class="form-control small col" name="general[guest-forms-server]" id="guest-forms-server" value="<?= $Settings->get('guest-forms-server') ?>">
            </div>
            <div class="row mt-10">
                <label for="guest-forms-secret-key" class="w-150 col flex-reset"><?= lang('Secret key') ?></label>
                <input type="text" class="form-control small col" name="general[guest-forms-secret-key]" id="guest-forms-secret-key" value="<?= $Settings->get('guest-forms-secret-key') ?>">
            </div>

        </div>


        <div class="form-group">
            <label for="">
                <?= lang('Send emails for guests', 'Sende Emails wegen Gästen') ?>
            </label>
            <?php
            $guest_mails = $Settings->featureEnabled('guest-mails');
            ?>

            <div class="custom-radio">
                <input type="radio" id="guest-mails-true" value="1" name="values[guest-mails]" <?= $guest_mails ? 'checked' : '' ?>>
                <label for="guest-mails-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="guest-mails-false" value="0" name="values[guest-mails]" <?= $guest_mails ? '' : 'checked' ?>>
                <label for="guest-mails-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

            <small>
                <?= lang(
                    'Please note that this feature will only work if mail support is enabled and mail account is properly configured.',
                    'Bitte beachte, dass diese Funktion nur funktioniert, wenn die E-Mail-Unterstützung aktiviert ist und das E-Mail-Konto richtig konfiguriert ist.'
                ) ?>
            </small>

            <!-- <h6 class="mb-0"><?= lang('Configure email addresses', 'Email-Adressen konfigurieren') ?></h6>
            <small class="text-muted">
                <?= lang('Enter multiple addresses separated by comma.', 'Du kannst mehrere Adressen durch Komma getrennt angeben.') ?>
            </small>
            <div class="row row-eq-spacing">

                <?php foreach (
                    [
                        'register' => lang('When a guest is registered by the supervisor', 'Bei Anmeldung eines Gastes durch den Betreuer'),
                        'completed' => lang('When the guest has completed the online registration', 'Wenn der Gast die Online-Registrierung abgeschlossen hat'),
                        'expiration' => lang('If the guest\'s stay was longer than 7 days and the time is about to expire', 'Wenn die Laufzeit des Gastes länger als 7 Tage war und die Zeit bald abläuft'),
                        'adjustment' => lang('If the guest is canceled or the period is adjusted', 'Wenn der Gast abgesagt oder der Zeitraum angepasst wird'),
                    ] as $key => $name
                ) { ?>
                    <div class="col-md-6">
                        <label for="guest-mails-<?= $key ?>"><?= $name ?></label>
                        <input type="text" class="form-control small" name="general[guest-mails-<?= $key ?>]" id="guest-mails-<?= $key ?>" value="<?= $Settings->get('guest-mails-' . $key) ?>">
                        <small><?= lang('en', 'Nur in Verbindung mit Gästeformularen') ?></small>
                        <div>
                            <?php
                            $sp = $Settings->get('guest-mails-' . $key . '-supervisor');
                            ?>
                            
                            <?= lang('Include supervisor', 'Betreuende Person einschließen') ?>:
                            <input type="radio" name="general[guest-mails-<?= $key ?>-supervisor]" value="true" id="guest-mails-<?= $key ?>-supervisor-1" <?= $sp ? 'checked' : '' ?>>
                            <label for="guest-mails-<?= $key ?>-supervisor-1"><?= lang('Yes', 'Ja') ?></label>

                            <input type="radio" name="general[guest-mails-<?= $key ?>-supervisor]" value="false" id="guest-mails-<?= $key ?>-supervisor-0" <?= !$sp ? 'checked' : '' ?>>
                            <label for="guest-mails-<?= $key ?>-supervisor-0"><?= lang('No', 'Nein') ?></label>

                        </div>
                    </div>
                <?php } ?>

            </div> -->

        </div>
    </div>


    <div class="box px-20">
        <h3>
            <?= lang('Reporting', 'Berichterstattung') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('IDA Integration') ?>
            </label>
            <?php
            $ida = $Settings->featureEnabled('ida');
            ?>

            <div class="custom-radio">
                <input type="radio" id="ida-true" value="1" name="values[ida]" <?= $ida ? 'checked' : '' ?>>
                <label for="ida-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="ida-false" value="0" name="values[ida]" <?= $ida ? '' : 'checked' ?>>
                <label for="ida-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

        </div>

    </div>


    <div class="box px-20">
        <h3>
            <?= lang('Projects', 'Projekte') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Projects in OSIRIS', 'Projekte in OSIRIS') ?>
            </label>
            <?php
            $projects = $Settings->featureEnabled('projects');
            ?>

            <div class="custom-radio">
                <input type="radio" id="projects-true" value="1" name="values[projects]" <?= $projects ? 'checked' : '' ?>>
                <label for="projects-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="projects-false" value="0" name="values[projects]" <?= $projects ? '' : 'checked' ?>>
                <label for="projects-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

        </div>

    </div>


    <div class="box px-20">
        <h3>
            <?= lang('Research Topics', 'Forschungsbereiche') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Research Topics in OSIRIS', 'Forschungsbereiche in OSIRIS') ?>
            </label>
            <?php
            $topics = $Settings->featureEnabled('topics');
            ?>

            <div class="custom-radio">
                <input type="radio" id="topics-true" value="1" name="values[topics]" <?= $topics ? 'checked' : '' ?>>
                <label for="topics-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="topics-false" value="0" name="values[topics]" <?= $topics ? '' : 'checked' ?>>
                <label for="topics-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

        </div>

        
        <div class="form-group">
                <label for="position">
                    <h5><?= lang('Label', 'Bezeichnung') ?></h5>
                </label>

                <?php
                    $label = $Settings->get('topics_label');
                ?>
                

                <div class="row row-eq-spacing my-0">
                    <div class="col-md-6">
                        <label for="topics_label" class="d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                        <input name="general[topics_label][en]" id="topics_label" type="text" class="form-control" value="<?= htmlspecialchars($label['en'] ?? 'Research topics') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="topics_label_de" class="d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                        <input name="general[topics_label][de]" id="topics_label_de" type="text" class="form-control" value="<?= htmlspecialchars($label['de'] ?? 'Forschungsbereiche') ?>">
                    </div>
                </div>
            </div>

        <?php
        $n_topics = $osiris->topics->count();
        $list_fields = $osiris->adminFields->find(['format' => 'list'])->toArray();
        if ($n_topics == 0 && count($list_fields) > 0) { ?>
            <div class="mb-20">
                <a href="#migrate-topics" class="btn">
                    <?= lang('Migrate custom fields to topics', 'Custom Fields in Bereiche migrieren') ?>
                </a>
            </div>
        <?php } ?>

    </div>





    <div class="box px-20">
        <h3>
            <?= lang('Concepts', 'Konzepte') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Show concepts', 'Zeige Konzepte') ?>
            </label>
            <?php
            $concepts = $Settings->featureEnabled('concepts');
            ?>

            <div class="custom-radio">
                <input type="radio" id="concepts-true" value="1" name="values[concepts]" <?= $concepts ? 'checked' : '' ?>>
                <label for="concepts-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="concepts-false" value="0" name="values[concepts]" <?= $concepts ? '' : 'checked' ?>>
                <label for="concepts-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

        </div>
    </div>


    <div class="box px-20">
        <h3>
            <?= lang('Word cloud') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Show word clouds in user profiles', 'Zeige Word Clouds in Nutzerprofilen') ?>
            </label>
            <?php
            $wordcloud = $Settings->featureEnabled('wordcloud');
            ?>

            <div class="custom-radio">
                <input type="radio" id="wordcloud-true" value="1" name="values[wordcloud]" <?= $wordcloud ? 'checked' : '' ?>>
                <label for="wordcloud-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="wordcloud-false" value="0" name="values[wordcloud]" <?= $wordcloud ? '' : 'checked' ?>>
                <label for="wordcloud-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

        </div>


    </div>


    <div class="box px-20">
        <h3>
            <?= lang('OSIRIS Portfolio') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Portal previews and API', 'Portal-Vorschau und API') ?>
            </label>
            <?php
            $portal = $Settings->featureEnabled('portal');
            ?>

            <div class="custom-radio">
                <input type="radio" id="portal-true" value="1" name="values[portal]" <?= $portal ? 'checked' : '' ?>>
                <label for="portal-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="portal-false" value="0" name="values[portal]" <?= $portal ? '' : 'checked' ?>>
                <label for="portal-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

        </div>
    </div>


    <div class="box px-20">
        <h3>
            <?= lang('Nagoya Protocol Compliance') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Add Nagoya Protocol Compliance to projects', 'Füge Nagoya-Protokoll Compliance zu Projekten hinzu') ?>
            </label>
            <?php
            $nagoya = $Settings->featureEnabled('nagoya');
            ?>

            <div class="custom-radio">
                <input type="radio" id="nagoya-true" value="1" name="values[nagoya]" <?= $nagoya ? 'checked' : '' ?>>
                <label for="nagoya-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="nagoya-false" value="0" name="values[nagoya]" <?= $nagoya ? '' : 'checked' ?>>
                <label for="nagoya-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

        </div>
    </div>

    <button class="btn success">
        <i class="ph ph-floppy-disk"></i>
        Save
    </button>


</form>




<?php if ($n_topics == 0 && count($list_fields) > 0) { ?>

    <div class="modal" id="migrate-topics" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="modal-title">
                    <?= lang('Migrate custom fields to research topics', 'Benutzerdefinierte Felder in Forschungsbereiche migrieren') ?>
                </h5>

                <form action="<?= ROOTPATH ?>/migrate/custom-fields-to-topics" method="post">
                    <div class="form-group ">
                        <label for="field"><?= lang('Select a field you want to use', 'Wähle ein Custom Field, dass du migrieren willst') ?></label>

                        <select name="field" id="field" class="form-control">
                            <?php foreach ($list_fields as $field) { ?>
                                <option value="<?= $field['id'] ?>"><?= lang($field['name'], $field['name_de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <?= lang('The following will happen if you click on migrate:', 'Wenn du auf migrieren klickst, wird das Folgende passieren:') ?>

                    <ul class="list">
                        <li>
                            <?=lang('The selected custom field is used to create new research areas on this basis. Don\'t worry, you can still edit them later.', 'Das ausgewählte Custom Field wird genommen, um auf dieser Grundlage neue Forschungsbereiche anzulegen. Keine Sorge, du kannst sie später noch bearbeiten.')?>
                        </li>
                        <li>
                            <?=lang('All activities for which the custom field was completed are assigned to the respective research areas.', 'Alle Aktivitäten, bei denen das Custom Field ausgefüllt war, werden den jeweiligen Forschungsbereichen zugeordnet.')?>
                        </li>
                        <li>
                            <?=lang('The custom field is then deleted, i.e. the field itself, the assignment to forms and the values set for the activities are removed.', 'Das Custom Field wird daraufhin gelöscht, d.h. das Feld selbst, die Zuordnung zu Formularen und die gesetzten Werte bei den Aktivitäten werden entfernt.')?>
                        </li>
                    </ul>

                    <button class="btn primary">
                        <?=lang('Migrate', 'Migrieren')?>
                    </button>
                </form>

            </div>
        </div>
    </div>

<?php } ?>

<?php if (strtoupper(USER_MANAGEMENT) !== 'AUTH') { ?>
    <br>
    <a href="<?= ROOTPATH ?>/synchronize-users" class="btn">Synchronize LDAP Users</a>
<?php } ?>

<style>
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
</style>