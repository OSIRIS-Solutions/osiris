
<?php include_once BASEPATH . '/header-editor.php'; ?>
<script src="<?= ROOTPATH ?>/js/script.js"></script>


<!-- <style>
    form .title {
        margin-bottom: 1rem; 
        /* padding-top: .5rem; */
        border-bottom: 1px solid var(--border-color);
    }
</style> -->

<div class="container">

    <h1>
        <i class="ph ph-user-circle-plus text-osiris"></i>
        <?= lang('Guest Forms', 'Gast anmelden') ?>
    </h1>
    <form action="<?= ROOTPATH ?>/guests/save" method="post" class="">
        <p class="text-muted">ID: <?= $id ?></p>

        <input type="hidden" name="values[id]" value="<?= $id ?>">
        <div class="box danger">
            <div class="content">

                <h5 class="title">
                    <?= lang('Details of the stay', 'Details zum Aufenthalt') ?>
                    <b class="text-danger float-right"><?= lang('provided by the supervisor', 'vom Betreuer auszufüllen') ?></b>
                </h5>

                <div class="form-group" data-module="date-range">
                    <label class="required" for="date_start">
                        <?= lang('Time frame of the stay', 'Dauer des Aufenthalts') ?>
                    </label>
                    <div class="input-group" id="date-range-picker">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><?= lang('from', 'von') ?></span>
                        </div>
                        <input type="date" class="form-control" name="values[start]" id="date_start" value="<?= valueFromDateArray($form['start'] ?? null) ?>" required>

                        <div class="input-group-prepend">
                            <span class="input-group-text"><?= lang('to', 'bis') ?></span>
                        </div>
                        <input type="date" class="form-control" name="values[end]" id="date_end" value="<?= valueFromDateArray($form['end'] ?? null) ?>" required>
                    </div>

                </div>

                <div class="form-group">
                    <label class="required" for="username">
                        <?= lang('Responsible Scientist at the ' . $Settings->get('affiliation'), 'Verantwortliche/r Wissenschaftler/in von ' . $Settings->get('affiliation')) ?>
                    </label>
                    <select class="form-control" id="username" name="values[user]" autocomplete="off" required>
                        <?php
                        foreach ($osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ['is_active' => -1, 'last' => 1]]) as $j) { ?>
                            <option value="<?= $j['username'] ?>" <?= $j['username'] == ($form['supervisor']['user'] ?? $_SESSION['username']) ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group" data-module="title">
                    <div class="lang-<?= lang('en', 'de') ?>">
                        <label for="title" class="required">
                            <?= lang('Title / Topic / Description', 'Titel / Thema / Beschreibung') ?>
                        </label>

                        <div class="form-group title-editor" id="title-editor"><?= $form['title'] ?? '' ?></div>
                        <input type="text" class="form-control hidden" name="values[title]" id="title" value="<?= $form['title'] ?? '' ?>" required>
                    </div>
                </div>
                <script>
                    initQuill(document.getElementById('title-editor'));
                </script>

                <div class="form-row row-eq-spacing">

                    <div class="col-sm">
                        <label for="category-guest" class="required"><?= lang('Purpose of stay', 'Zweck des Aufenthalts') ?>:</label>
                        <select name="values[category]" id="category-guest" class="form-control" required>
                            <option value="">-- bitte ausfüllen --</option>
                            <option value="guest scientist" <?= ($form['category'] ?? '') == 'guest scientist' ? 'selected' : '' ?>>Gastwissenschaftler:in</option>
                            <option value="lecture internship" <?= ($form['category'] ?? '') == 'lecture internship' ? 'selected' : '' ?>>Pflichtpraktikum im Rahmen des Studium</option>
                            <option value="student internship" <?= ($form['category'] ?? '') == 'student internship' ? 'selected' : '' ?>>Schülerpraktikum</option>
                            <option value="doctoral thesis" <?= ($form['category'] ?? '') == 'doctoral thesis' ? 'selected' : '' ?>><?= lang('Doctoral Thesis', 'Doktorarbeit') ?></option>
                            <option value="master thesis" <?= ($form['category'] ?? '') == 'master thesis' ? 'selected' : '' ?>><?= lang('Master Thesis', 'Master-Arbeit') ?></option>
                            <option value="bachelor thesis" <?= ($form['category'] ?? '') == 'bachelor thesis' ? 'selected' : '' ?>><?= lang('Bachelor Thesis', 'Bachelor-Arbeit') ?></option>
                            <option value="other" <?= ($form['category'] ?? '') == 'other' ? 'selected' : '' ?>>Sonstiges</option>
                        </select>
                    </div>
                    <div class="col-sm">
                        <label for="guest-payment" class="required"><?= lang('The visit is financed by', 'Die Finanzierung erfolgt') ?>:</label>
                        <select name="values[payment]" id="guest-payment" class="form-control" required>
                            <option value="">-- bitte ausfüllen --</option>
                            <option value="auf eigene Kosten" <?= ($form['payment'] ?? '') == 'auf eigene Kosten' ? 'selected' : '' ?>><?= lang('myself / my institute', 'auf eigene Kosten') ?></option>
                            <option value="DSMZ" <?= ($form['payment'] ?? '') == 'DSMZ' ? 'selected' : '' ?>><?= lang('DSMZ', 'durch die DSMZ') ?></option>
                            <option value="Alexander von Humboldt-Stiftung" <?= ($form['payment'] ?? '') == 'Alexander von Humboldt-Stiftung' ? 'selected' : '' ?>><?= lang('Alexander von Humboldt-Foundation', 'über die Alexander von Humboldt-Stiftung') ?></option>
                            <option value="DAAD" <?= ($form['payment'] ?? '') == 'DAAD' ? 'selected' : '' ?>><?= lang('DAAD', 'über den DAAD') ?></option>
                            <option value="sonstiges" <?= ($form['payment'] ?? '') == 'sonstiges' ? 'selected' : '' ?>><?= lang('Others (please comment)', 'Weiteres (bitte begründen)') ?></option>
                        </select>
                    </div>

                    <!-- if sonstiges is selected -->
                    <div class="col-sm" id="payment-comment" style="display: <?= ($form['payment'] ?? '') == 'sonstiges' ? 'block' : 'none' ?>">
                        <label for="payment-comment"><?= lang('Comment', 'Begründung') ?>:</label>
                        <input type="text" class="form-control" name="values[payment_comment]" id="payment-comment" value="<?= $form['payment_comment'] ?? '' ?>">
                    </div>
                    <script>
                        $('#guest-payment').change(function() {
                            if ($(this).val() == 'sonstiges') {
                                $('#payment-comment').show();
                            } else {
                                $('#payment-comment').hide();
                            }
                        });
                    </script>
                </div>

            </div>
        </div>



        <div class="box muted">
            <div class="content">

                <h5 class="title">
                    <?= lang('Guest information', 'Angaben zum Gast') ?>
                    <span class="text-muted float-right">optional</span>
                </h5>

                <div class="form-row row-eq-spacing" data-module="person">
                    <div class="col-sm-2">
                        <label for="academic-title"><?= lang('Title', 'Titel') ?>
                        </label>
                        <input type="text" class="form-control" name="values[guest][academic_title]" id="academic-title" value="<?= $form['guest']['academic_title'] ?? '' ?>">
                    </div>
                    <div class="col-sm-5">
                        <label for="first-name" class="element-other">
                            <?= lang('First name', 'Vorname') ?>
                        </label>
                        <input type="text" class="form-control" name="values[guest][first]" id="first-name" value="<?= $form['guest']['first'] ?? '' ?>">
                    </div>
                    <div class="col-sm-5">
                        <label for="last-name" class="element-other">
                            <?= lang('Last name', 'Nachname') ?>
                        </label>
                        <input type="text" class="form-control" name="values[guest][last]" id="last-name" value="<?= $form['guest']['last'] ?? '' ?>">
                    </div>
                </div>
                <div class="row" data-module="person">
                    <div class="col-sm-6">
                        <label for="guest-birthday" class="element-other"><?= lang('Date of Birth', 'Geburtstag') ?></label>
                        <input type="date" class="form-control" name="values[guest][birthday]" id="guest-birthday" value="<?= $form['guest']['birthday'] ?? '' ?>">
                    </div>
                </div>

            </div>
            <hr>
            <div class="content">

                <h5 class="title">
                    <?= lang('Contact', 'Kontaktinformationen') ?>
                    <!-- <span class="text-muted float-right">optional</span> -->
                </h5>

                <div class="form-group">
                    <label for="guest-phone" class="element-other"><?= lang('Telephone', 'Telefon') ?></label>
                    <input type="text" class="form-control" name="values[guest][phone]" id="guest-phone" value="<?= $form['guest']['phone'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="guest-mail" class="element-other"><?= lang('E-Mail', 'E-Mail') ?></label>
                    <input type="text" class="form-control" name="values[guest][mail]" id="guest-mail" value="<?= $form['guest']['mail'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="guest-accomodation" class="element-other"><?= lang('Accomodation during stay', 'Unterkunftsadresse während des Aufenthalts') ?></label>
                    <input type="text" class="form-control" name="values[guest][accomodation]" id="guest-accomodation" value="<?= $form['guest']['accomodation'] ?? '' ?>">
                </div>

            </div>
            <hr>
            <div class="content">

                <h5 class="title">
                    <?= lang('Company / University', 'Firma / Universität / Schule') ?>
                    <!-- <span class="text-muted float-right">optional</span> -->
                </h5>

                <div class="form-group">
                    <label for="guest-affiliation" class="element-other"><?= lang('Name', 'Name') ?></label>
                    <input type="text" class="form-control" name="values[affiliation][name]" id="guest-affiliation" value="<?= $form['affiliation']['name'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="guest-address" class="element-other"><?= lang('Address', 'Anschrift') ?></label>
                    <input type="text" class="form-control" name="values[affiliation][address]" id="guest-address" value="<?= $form['affiliation']['address'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="guest-country" class="element-other"><?= lang('Country', 'Land') ?></label>
                    <input type="text" class="form-control" name="values[affiliation][country]" id="guest-country" value="<?= $form['affiliation']['country'] ?? '' ?>">
                </div>
            </div>

        </div>
        <button type="submit" class="btn secondary">
            <i class="ph ph-user-plus"></i>
            <?php if (empty($form)) {
                echo lang('Save guest', 'Gast anlegen');
            } else {
                echo lang('Save guest', 'Gast speichern');
            } ?>
        </button>

    </form>

</div>