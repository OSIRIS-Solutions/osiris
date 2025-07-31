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
                                <?php if (!empty($field['kdsf'])) { ?>
                                    <span class="badge kdsf">
                                        <?= $field['kdsf'] ?>
                                    </span>
                                <?php } ?>

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

    <p class="text-muted">
       <?=lang('To add more fields to the <b>annual statistics</b>, you can update', 'Um weitere Felder zu der <b>Jahresstatistik</b> hinzuzufügen, kannst du')?> 
       <a href="<?= ROOTPATH ?>/admin/vocabulary#vocabulary-infrastructure-stats"><?= lang('the vocabulary for infrastructure statistics', 'das Vokabular für Infrastrukturstatistiken bearbeiten') ?></a> 
         <?=lang('and add the fields you want to use there.', 'und dort die Felder hinzufügen, die du verwenden möchtest.')?>
    </p>

           
<!--  <hr>
            <h5>
                <?= lang('Additional statistics', 'Weitere Statistiken') ?>
            </h5>

            <p>
                <?= lang('You can add additional statistics for the infrastructures here. These will be displayed in the infrastructure overview.', 'Hier kannst du weitere Statistiken für die Infrastrukturen hinzufügen. Diese werden in der Infrastrukturübersicht angezeigt.') ?>
            </p>
            <p>
                Die folgenden Felder sind standardmäßig aktiviert:
            </p>

            
            <p>
                Füge hier weitere Felder zur Jahresstatistik hinzu:
            </p>
            <table class="table simple">
                    <thead>
                        <tr>
                            <th></th>
                            <th>
                                <?= lang('ID') ?>
                            </th>
                            <th>
                                <?= lang('Value', "Wert") ?> (EN)
                            </th>
                            <th>
                                <?= lang('Value', "Wert") ?> (DE)
                            </th>
                            <th>
                                <?= lang('Inactive', 'Inaktiv') ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $default = [
                            'internal' => ['en' => 'Number of internal users', 'de' => 'Anzahl interner Nutzer/-innen'],
                            'national' => ['en' => 'Number of national users', 'de' => 'Anzahl nationaler Nutzer/-innen'],
                            'international' => ['en' => 'Number of international users', 'de' => 'Anzahl internationaler Nutzer/-innen'],
                            'hours' => ['en' => 'Number of hours used', 'de' => 'Anzahl der genutzten Stunden'],
                            'accesses' => ['en' => 'Number of accesses', 'de' => 'Anzahl der Nutzungszugriffe']
                        ];
                        foreach ($default as $key => $value) { ?>
                            <tr>
                                <td class="w-50">
                                    <i class="ph ph-dots-six-vertical text-muted handle"></i>
                                </td>
                                <td class="w-50">
                                    <code class="code"><?= $key ?></code>
                                </td>
                                <td>
                                    <?= $value['en'] ?>
                                </td>
                                <td>
                                    <?= $value['de'] ?>
                                </td>
                                <td>-</td>
                            </tr>
                        <?php } 
                        
                        $values = $Settings->get('infrastructure-stats');
                        foreach ($values as $i => $v) {
                            $inactive = ($v['inactive'] ?? false) ? 'checked' : '';
                        ?>
                            <tr>
                                <td class="w-50">
                                    <i class="ph ph-dots-six-vertical text-muted handle"></i>
                                </td>
                                <td class="w-50">
                                    <input type="hidden" name="values[<?= $i ?>][id]" value="<?= $v['id'] ?>">
                                    <code class="code"><?= $v['id'] ?></code>
                                </td>
                                <td>
                                    <input type="text" name="values[<?= $i ?>][en]" value="<?= $v['en'] ?>" class="form-control">
                                </td>
                                <td>
                                    <input type="text" name="values[<?= $i ?>][de]" value="<?= $v['de'] ?>" class="form-control">
                                </td>
                                <td>
                                    <div class="custom-checkbox">
                                        <input type="checkbox" name="values[<?= $i ?>][inactive]" value="1" id="inactive-<?= $vocab['id'] ?>-<?= $i ?>" <?= $inactive ?>>
                                        <label for="inactive-<?= $vocab['id'] ?>-<?= $i ?>">
                                        </label>
                                    </div>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td class="w-50 bg-white"></td>
                            <td colspan="4" class="bg-white">
                                <button type="button" class="btn small primary" onclick="addRow(this)">
                                    <i class="ph ph-plus"></i>
                                    <?= lang('Add Value', 'Wert hinzufügen') ?>
                                </button>
                            </td>
                        </tr>
                    </tfoot>
            </table> -->

            <button class="btn signal">
                <i class="ph ph-floppy-disk"></i>
                <?= lang('Save', 'Speichern') ?>
            </button>

        </div>
    </form>


    <script>
            function addRow(btn) {
                let table = btn.closest('table');
                let tbody = table.querySelector('tbody');
                let tr = document.createElement('tr');

                // generate random id for the checkbox
                let random_id = Math.random().toString(36).substring(7);

                // get the index of the last row, make sure to consider meanwhile deleted rows
                let last_row = tbody.querySelector('tr:last-child');
                let i = last_row ? parseInt(last_row.querySelector('input').name.match(/\[(\d+)\]/)[1]) + 1 : 0;

                tr.innerHTML = `
        <td class="w-50">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
        </td>
        <td>
            <input type="text" name="values[${i}][id]" value="" class="form-control">
        </td>
        <td>
            <input type="text" name="values[${i}][en]" value="" class="form-control">
        </td>
        <td>
            <input type="text" name="values[${i}][de]" value="" class="form-control">
        </td>
        <td>
            <div class="custom-checkbox">
                <input type="checkbox" name="values[${i}][inactive]" value="1" id="inactive-${random_id}">
                <label for="inactive-${random_id}">
                </label>
            </div>
        </td>
    `;
                tbody.appendChild(tr);
            }

            $(document).ready(function() {
                $('tbody').sortable({
                    handle: ".handle",
                });
            });
        </script>

