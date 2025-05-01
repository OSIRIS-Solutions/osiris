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
            <?= lang('Journals', 'Journale') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Automatic retrieval of journal metrics', 'Automatischer Download von Journal-Metriken') ?>
            </label>
            <?php
            $journals = $Settings->featureEnabled('no-journal-metrics');
            ?>

            <p>
                <?= lang('Please note: the metrics are obtained from Scimago and are based on Scopus. If you want to obtain other impact factors and quartiles, you can switch off the automatic import. However, you will then have to maintain the data manually.', 'Bitte beachten: die Metriken werden von Scimago bezogen und richten sich nach Scopus. Wenn ihr andere Impact Faktoren und Quartile beziehen wollt, könnt ihr den automatischen Import ausschalten. Dann müsst ihr die Daten aber händisch pflegen.') ?>
            </p>
            <div class="custom-radio">
                <input type="radio" id="no-journal-metrics-false" value="0" name="values[no-journal-metrics]" <?= $journals ? '' : 'checked' ?>>
                <label for="no-journal-metrics-false"><?= lang('Retrieve metrics automatically', 'Metriken automatisch abrufen') ?></label>
            </div>
            <div class="custom-radio">
                <input type="radio" id="no-journal-metrics-true" value="1" name="values[no-journal-metrics]" <?= $journals ? 'checked' : '' ?>>
                <label for="no-journal-metrics-true"><?= lang('Disable automatic retrieval', 'Automatischen Abruf deaktivieren') ?></label>
            </div>

        </div>
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
            <?= lang('Teaching modules', 'Lehrveranstaltungen') ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Show Teaching modules in Sidebar', 'Zeige Lehrveranstaltungen in der Seitennavigation') ?>
            </label>
            <?php
            $teachingModules = $Settings->featureEnabled('teaching-modules', true);
            ?>

            <div class="custom-radio">
                <input type="radio" id="teaching-modules-true" value="1" name="values[teaching-modules]" <?= $teachingModules ? 'checked' : '' ?>>
                <label for="teaching-modules-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="teaching-modules-false" value="0" name="values[teaching-modules]" <?= $teachingModules ? '' : 'checked' ?>>
                <label for="teaching-modules-false"><?= lang('disabled', 'deaktiviert') ?></label>
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
            <?= $Settings->infrastructureLabel() ?>
        </h3>
        <div class="form-group">
            <label for="">
                <?= lang('Infrastructures in OSIRIS', 'Infrastrukturen in OSIRIS') ?>
            </label>
            <?php
            $infrastructures = $Settings->featureEnabled('infrastructures');
            ?>

            <div class="custom-radio">
                <input type="radio" id="infrastructures-true" value="1" name="values[infrastructures]" <?= $infrastructures ? 'checked' : '' ?>>
                <label for="infrastructures-true"><?= lang('enabled', 'aktiviert') ?></label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="infrastructures-false" value="0" name="values[infrastructures]" <?= $infrastructures ? '' : 'checked' ?>>
                <label for="infrastructures-false"><?= lang('disabled', 'deaktiviert') ?></label>
            </div>

        </div>

        <div class="form-group">
            <label for="position">
                <h5><?= lang('Label', 'Bezeichnung') ?></h5>
            </label>

            <?php
            $label = $Settings->get('infrastructures_label');
            ?>

            <div class="row row-eq-spacing my-0">
                <div class="col-md-6">
                    <label for="infrastructures_label" class="d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                    <input name="general[infrastructures_label][en]" id="infrastructures_label" type="text" class="form-control" value="<?= htmlspecialchars($label['en'] ?? 'Infrastructures') ?>">
                </div>
                <div class="col-md-6">
                    <label for="infrastructures_label_de" class="d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                    <input name="general[infrastructures_label][de]" id="infrastructures_label_de" type="text" class="form-control" value="<?= htmlspecialchars($label['de'] ?? 'Infrastrukturen') ?>">
                </div>
            </div>
        </div>

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
                            <?= lang('The selected custom field is used to create new research areas on this basis. Don\'t worry, you can still edit them later.', 'Das ausgewählte Custom Field wird genommen, um auf dieser Grundlage neue Forschungsbereiche anzulegen. Keine Sorge, du kannst sie später noch bearbeiten.') ?>
                        </li>
                        <li>
                            <?= lang('All activities for which the custom field was completed are assigned to the respective research areas.', 'Alle Aktivitäten, bei denen das Custom Field ausgefüllt war, werden den jeweiligen Forschungsbereichen zugeordnet.') ?>
                        </li>
                        <li>
                            <?= lang('The custom field is then deleted, i.e. the field itself, the assignment to forms and the values set for the activities are removed.', 'Das Custom Field wird daraufhin gelöscht, d.h. das Feld selbst, die Zuordnung zu Formularen und die gesetzten Werte bei den Aktivitäten werden entfernt.') ?>
                        </li>
                    </ul>

                    <button class="btn primary">
                        <?= lang('Migrate', 'Migrieren') ?>
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