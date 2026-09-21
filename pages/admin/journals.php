<style>
    .description {
        font-size: small;
        color: var(--muted-color);
    }
</style>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-stack"></i>
        <?= lang('admin.journal_settings') ?>
    </h1>

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">

        <?php
        $label = $Settings->get('journals_label');
        ?>
        <div class="box padded">
            <h2 class="title">
                <?= lang('admin.label_for_journals') ?>
            </h2>
            <div class="row row-eq-spacing">
                <div class="col-md-6 mt-10 mt-md-0">
                    <label for="journals_label" class="d-flex"><?= lang('common.label') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                    <input name="general[journals_label][en]" id="journals_label" type="text" class="form-control" value="<?= e($label['en'] ?? 'Journals') ?>">
                </div>
                <div class="col-md-6 mt-10 mt-md-0">
                    <label for="journals_label_de" class="d-flex"><?= lang('common.label') ?> (Deutsch) <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                    <input name="general[journals_label][de]" id="journals_label_de" type="text" class="form-control" value="<?= e($label['de'] ?? 'Journale') ?>">
                </div>
            </div>
        </div>

        <div class="box padded">

            <h2 class="title">
                <?= lang('admin.journal_metrics') ?>
            </h2>
            <h3 class="font-size-14">
                <?= lang('admin.disable_automatic_retrieval_of_journal_metrics') ?>
            </h3>
            <?php
            $enabled = $Settings->featureEnabled('no-journal-metrics', false);
            ?>
            <div class="custom-radio d-inline-block mr-20">
                <input type="radio" id="no-journal-metrics-true" value="1" name="features[no-journal-metrics]" <?= $enabled ? 'checked' : '' ?>>
                <label for="no-journal-metrics-true">
                    <?= lang('common.yes') ?>
                </label>
            </div>
            <div class="custom-radio d-inline-block">
                <input type="radio" id="no-journal-metrics-false" value="0" name="features[no-journal-metrics]" <?= $enabled ? '' : 'checked' ?>>
                <label for="no-journal-metrics-false">
                    <?= lang('common.no') ?>
                </label>
            </div>
            <p class="description">
                <?= lang('admin.please_note_the_metrics_are_obtained_from_scimago_and_are_based_on_scopus_i') ?>
            </p>

            <h3 class="font-size-14">
                <?=lang('admin.name_of_the_main_metrics_field') ?>
            </h3>

            
        <?php
        $impact_label = $Settings->get('impact_label');
        ?>
            <div class="row row-eq-spacing">
                <div class="col-md-6 mt-10 mt-md-0">
                    <label for="impact_label" class="d-flex"><?= lang('common.label') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                    <input name="general[impact_label][en]" id="impact_label" type="text" class="form-control" value="<?= e($impact_label['en'] ?? 'Cite factor') ?>">
                </div>
                <div class="col-md-6 mt-10 mt-md-0">
                    <label for="impact_label_de" class="d-flex"><?= lang('common.label') ?> (Deutsch) <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
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
                <?= lang('common.data_fields') ?>
            </h2>

            <p class="text-muted">
                <?= lang('admin.you_can_add_custom_fields_to_journals') ?>
            </p>

            <table class="table simple small mb-10">
                <thead>
                    <tr>
                        <th></th>
                        <th><?= lang('common.active') ?></th>
                        <th><?= lang('common.field_name') ?></th>
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
                                        <i class="ph ph-pencil" title="<?= lang('action.edit') ?>"></i>
                                    </a>
                                </td>

                            </tr>
                        <?php } ?>

                    <?php } else { ?>
                        <tr>
                            <td colspan="2">
                                <p class="description">
                                    <?= lang('admin.no_custom_fields_found') ?>
                                </p>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>


        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('action.save') ?>
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