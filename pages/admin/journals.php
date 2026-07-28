<style>
    .description {
        font-size: small;
        color: var(--muted-color);
    }
</style>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-stack"></i>
        <?= lang('Journal Settings', 'Journal Einstellungen') ?>
    </h1>

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">

        <?php
        $label = $Settings->get('journals_label');
        ?>
        <div class="box padded">
            <h2 class="title">
                <?= lang('Label for journals', 'Bezeichnung für Journale') ?>
            </h2>
            <div class="row row-eq-spacing">
                <div class="col-md-6">
                    <label for="journals_label" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                    <input name="general[journals_label][en]" id="journals_label" type="text" class="form-control" value="<?= e($label['en'] ?? 'Journals') ?>">
                </div>
                <div class="col-md-6">
                    <label for="journals_label_de" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                    <input name="general[journals_label][de]" id="journals_label_de" type="text" class="form-control" value="<?= e($label['de'] ?? 'Journale') ?>">
                </div>
            </div>
        </div>

        <div class="box padded">

            <h2 class="title">
                <?= lang('Journal Metrics', 'Journal-Metriken') ?>
            </h2>
            <h3 class="font-size-14">
                <?= lang('Disable automatic retrieval of journal metrics', 'Verhindere den automatischen Download von Journal-Metriken') ?>
            </h3>
            <?php
            $enabled = $Settings->featureEnabled('no-journal-metrics', false);
            ?>
            <div class="custom-radio d-inline-block mr-20">
                <input type="radio" id="no-journal-metrics-true" value="1" name="features[no-journal-metrics]" <?= $enabled ? 'checked' : '' ?>>
                <label for="no-journal-metrics-true">
                    <?= lang('Yes', 'Ja') ?>
                </label>
            </div>
            <div class="custom-radio d-inline-block">
                <input type="radio" id="no-journal-metrics-false" value="0" name="features[no-journal-metrics]" <?= $enabled ? '' : 'checked' ?>>
                <label for="no-journal-metrics-false">
                    <?= lang('No', 'Nein') ?>
                </label>
            </div>
            <p class="description">
                <?= lang('Please note: the metrics are obtained from Scimago and are based on Scopus. If you want to obtain other impact factors and quartiles, you can switch off the automatic import. However, you will then have to maintain the data manually.', 'Bitte beachten: die Metriken werden von Scimago bezogen und richten sich nach Scopus. Wenn ihr andere Impact Faktoren und Quartile beziehen wollt, könnt ihr den automatischen Import ausschalten. Dann müsst ihr die Daten aber händisch pflegen.') ?>
            </p>

            <h3 class="font-size-14">
                <?=lang('Name of the main metrics field', 'Name des Hauptmetriken-Feldes') ?>
            </h3>

            
        <?php
        $impact_label = $Settings->get('impact_label');
        ?>
            <div class="row row-eq-spacing">
                <div class="col-md-6">
                    <label for="impact_label" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                    <input name="general[impact_label][en]" id="impact_label" type="text" class="form-control" value="<?= e($impact_label['en'] ?? 'Cite factor') ?>">
                </div>
                <div class="col-md-6">
                    <label for="impact_label_de" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                    <input name="general[impact_label][de]" id="impact_label_de" type="text" class="form-control" value="<?= e($impact_label['de'] ?? 'Cite Factor') ?>">
                </div>
            </div>


        </div>


        <?php
        $custom_fields = $osiris->adminFields->find();

        $data = $Settings->get('journal-data');
        $data = DB::doc2Arr($data);
        ?>



        <div class="box padded">
            <h2 class="title" id="datafields">
                <i class="ph-duotone ph-database" aria-hidden="true"></i>
                <?= lang('Data fields', 'Datenfelder') ?>
            </h2>

            <p class="text-muted">
                <?= lang('You can add custom fields to journals.', 'Du kannst benutzerdefinierte Felder zu Journalen hinzufügen.') ?>
            </p>

            <table class="table simple small mb-10">
                <thead>
                    <tr>
                        <th></th>
                        <th><?= lang('Active', 'Aktiv') ?></th>
                        <th><?= lang('Field name', 'Feldname') ?></th>
                    </tr>
                </thead>
                <tbody id="data-fields">
                    <?php if (!empty($custom_fields)) {
                        // sort the custom fields by order in $data
                        $custom_fields = DB::doc2Arr($custom_fields);
                        usort($custom_fields, function ($a, $b) use ($data) {
                            $a_index = array_search($a['id'], $data);
                            $b_index = array_search($b['id'], $data);
                            if ($a_index === false) $a_index = PHP_INT_MAX;
                            if ($b_index === false) $b_index = PHP_INT_MAX;
                            return $a_index - $b_index;
                        });
                    ?>
                        <?php foreach ($custom_fields as $field) { ?>
                            <tr>
                                <td class="w-50">
                                    <i class="ph ph-dots-six-vertical text-muted handle cursor-pointer"></i>
                                </td>
                                <td class="w-50">
                                    <!-- checkbox -->
                                    <div class="custom-checkbox">
                                        <input type="checkbox" name="general[journal-data][]" id="field-<?= $field['id'] ?>" value="<?= $field['id'] ?>" <?= in_array($field['id'], $data) ? 'checked' : '' ?>>
                                        <label for="field-<?= $field['id'] ?>"></label>
                                    </div>
                                </td>
                                <td>
                                    <b><?= e(lang($field['name'], $field['name_de'] ?? null)) ?></b>
                                    <code class="code mx-10"><?= e($field['format']) ?></code>
                                    <a href="<?= ROOTPATH ?>/admin/fields/<?= $field['id'] ?>">
                                        <i class="ph ph-pencil" title="<?= lang('edit', 'bearbeiten') ?>"></i>
                                    </a>
                                </td>

                            </tr>
                        <?php } ?>

                    <?php } else { ?>
                        <tr>
                            <td colspan="2">
                                <p class="description">
                                    <?= lang('No custom fields found.', 'Keine benutzerdefinierten Felder gefunden.') ?>
                                </p>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>


        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>

    </form>
</div>

<?php include_once BASEPATH . '/header-editor.php'; ?>
<script>
    $(document).ready(function() {
        $('#data-fields').sortable({
            handle: ".handle",
            // change: function( event, ui ) {}
        });
    })
</script>