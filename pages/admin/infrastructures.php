
<?php
$fields = file_get_contents(BASEPATH . '/data/infrastructure-fields.json');
$fields = json_decode($fields, true);

$custom_fields = $osiris->adminFields->find();

$data = $Settings->get('infrastructure-data');
if (!is_null($data)) {
    $data = DB::doc2Arr($data);
} else {
    $data = array_filter($fields, function ($field) {
        return $field['default'] ?? false;
    });
    $data = array_column($data, 'id');
}
?>


<section id="data-fields">
    <h2>
        <?= lang('Data fields for Infrastructures', 'Datenfelder für Infrastrukturen') ?>
    </h2>

    <p>
        <?= lang('Here you can manage the data fields for the infrastructures.', 'Hier kannst du die Datenfelder für die Infrastrukturen verwalten.') ?>
    </p>

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/infrastructures">
        <div class="box primary padded">
            <h3>
                <?= lang('Data fields', 'Datenfelder') ?>
            </h3>

            <table class="table simple w-auto small mb-10">
                <thead>
                    <tr>
                        <th><?= lang('Active', 'Aktiv') ?></th>
                        <th><?= lang('Field name', 'Feldname') ?></th>
                    </tr>
                </thead>
                <tbody id="data-fields">
                    <?php foreach ($fields as $field) { ?>
                        <tr>
                            <td>
                                <!-- checkbox -->
                                <div class="custom-checkbox">
                                    <input type="checkbox" name="general[infrastructure-data][]" id="field-<?= $field['id'] ?>" value="<?= $field['id'] ?>" <?= in_array($field['id'], $data) ? 'checked' : '' ?>>
                                    <label for="field-<?= $field['id'] ?>"></label>
                                </div>
                            </td>
                            <td>
                                <b><?= lang($field['en'], $field['de'] ?? null) ?></b>
                                <?php if (isset($field['description'])) { ?>
                                    <small class="d-block text-muted">
                                        <?= lang($field['description']['en'], $field['description']['de'] ?? null) ?>
                                    </small>
                                <?php } ?>
                            </td>

                        </tr>
                    <?php } ?>
                    <?php if (!empty($custom_fields)) { ?>
                        <tr>
                            <td colspan="2">
                                <h5>
                                    <?= lang('Custom fields', 'Benutzerdefinierte Felder') ?>
                                </h5>
                            </td>
                        </tr>
                        <?php foreach ($custom_fields as $field) { ?>
                            <tr>
                                <td>
                                    <!-- checkbox -->
                                    <div class="custom-checkbox">
                                        <input type="checkbox" name="general[infrastructure-data][]" id="field-<?= $field['id'] ?>" value="<?= $field['id'] ?>" <?= in_array($field['id'], $data) ? 'checked' : '' ?>>
                                        <label for="field-<?= $field['id'] ?>"></label>
                                    </div>
                                </td>
                                <td>
                                    <b><?= htmlspecialchars($field['name']) ?></b>
                                </td>

                            </tr>
                    <?php } ?>
                    
                    <?php } ?>
                </tbody>
            </table>

            <button class="btn signal">
                <i class="ph ph-floppy-disk"></i>
                <?= lang('Save', 'Speichern') ?>
            </button>

        </div>
    </form>
