<?php
$formaction = ROOTPATH;
if (!empty($form) && isset($form['id'])) {
    $formaction .= "/crud/fields/update/" . $form['id'];
    $btntext = '<i class="ph ph-check"></i> ' . lang("Update", "Aktualisieren");
    $url = ROOTPATH . "/admin/fields/" . $form['id'];
    $title = $name;
} else {
    $formaction .= "/crud/fields/create";
    $btntext = '<i class="ph ph-check"></i> ' . lang("Save", "Speichern");
    $url = ROOTPATH . "/admin/fields";
    $title = lang('New field', 'Neues Feld');
}
$affiliation = (strtolower($Settings->get('affiliation')));
// keep only letters and numbers
$affiliation = preg_replace('/[^a-zA-Z0-9]/', '', $affiliation);
?>
<style>
    tr.ui-sortable-helper {
        background-color: white;
        border: var(--border-width) solid var(--border-color);
    }
</style>

<div class="modal" id="unique" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('ID must be unique', 'Die ID muss einzigartig sein.') ?></h5>

            <p>
                <?= lang('The ID is used internally to save data for this data field in the database. Furthermore, it will be used in templates to display the data. Therefore, it must be unique and may only contain lowercase letters (a-z), numbers (0-9), and hyphens (-). Spaces and special characters are not allowed.', 'Die ID wird intern verwendet, um Daten für dieses Datenfeld in der Datenbank zu speichern. Außerdem wird sie in Vorlagen verwendet, um die Daten anzuzeigen. Daher muss sie einzigartig sein und darf nur Kleinbuchstaben (a-z), Zahlen (0-9) und Bindestriche (-) enthalten. Leerzeichen und Sonderzeichen sind nicht erlaubt.') ?>
            </p>
            <p>
                <?= lang('As the ID must be unique, the following previously used IDs and keywords (new) cannot be used as IDs:', 'Da die ID einzigartig sein muss, können folgende bereits verwendete IDs und Schlüsselwörter (new) nicht als ID verwendet werden:') ?>
            </p>
            <ul class="list" id="used-ids">
                <li class="font-weight-bold">--- <?= lang('OSIRIS Fields', 'OSIRIS-Felder') ?> ---</li>
                <?php
                require_once BASEPATH . '/php/activity_fields.php';
                $Fields = new ActivityFields();
                $field_ids = array_column($Fields->fields, 'id');
                sort($field_ids);
                foreach ($field_ids as $k) {
                    if (str_contains($k, '.')) continue;
                ?>
                    <li><?= $k ?></li>
                <?php } ?>
                <li class="font-weight-bold">--- Custom Fields ---</li>
                <?php foreach ($osiris->adminFields->distinct('id') as $k) { ?>
                    <li><?= $k ?></li>
                <?php } ?>
                <li class="font-weight-bold">--- <?= lang('Keywords', 'Schlüsselwörter') ?> ---</li>
                <li>language</li>
                <li>new</li>
            </ul>
            <div class="text-right mt-20">
                <a href="#/" class="btn secondary" role="button"><?= lang('I understand', 'Ich verstehe') ?></a>
            </div>
        </div>
    </div>
</div>

<h1>
    <i class="ph-duotone ph-textbox"></i>
    <?= $title ?>
</h1>

<form action="<?= $formaction ?>" method="post" id="group-form">

    <div class="box">

        <div class="content">

            <div class="form-group">
                <label for="id">ID</label>
                <input type="text" class="form-control" name="values[id]" id="id" value="<?= $form['id'] ?? $affiliation . '-' ?>" <?= !empty($form) ? 'disabled' : '' ?> oninput="sanitizeID(this, '#used-ids li')" required>

                <small>
                    <a href="#unique"><i class="ph ph-info"></i>
                        <?= lang('Important! Must be unique.', 'Wichtig! Die ID muss einzigartig sein.') ?>
                    </a>
                </small>
            </div>


            <div class="row row-eq-spacing">
                <div class="col-sm-6">
                    <label for="name" class="required ">Name (en)</label>
                    <input type="text" class="form-control" name="values[name]" required value="<?= $form['name'] ?? '' ?>">
                </div>
                <div class="col-sm-6">
                    <label for="name_de" class="">Name (de)</label>
                    <input type="text" class="form-control" name="values[name_de]" value="<?= $form['name_de'] ?? '' ?>">
                </div>
            </div>

            <div class="row row-eq-spacing">
                <div class="col-sm-6">
                    <label for="format">Format</label>
                    <select class="form-control" name="values[format]" id="format" onchange="updateFields(this.value)">
                        <option value="string" <?= ($form['format'] ?? '') == 'string' ? 'selected' : '' ?>><?=lang('Normal Text', 'Normaler Text')?></option>
                        <option value="text" <?= ($form['format'] ?? '') == 'text' ? 'selected' : '' ?>><?=lang('Long text', 'Langer Text')?></option>
                        <option value="int" <?= ($form['format'] ?? '') == 'int' ? 'selected' : '' ?>><?=lang('Integer', 'Ganzzahl')?></option>
                        <option value="float" <?= ($form['format'] ?? '') == 'float' ? 'selected' : '' ?>><?=lang('Float', 'Gleitkommazahl')?></option>
                        <option value="list" <?= ($form['format'] ?? '') == 'list' ? 'selected' : '' ?>><?=lang('Dropdown (Select from list)', 'Dropdown (Wähle aus einer Liste)')?></option>
                        <option value="date" <?= ($form['format'] ?? '') == 'date' ? 'selected' : '' ?>><?=lang('Date', 'Datum')?></option>
                        <option value="bool" <?= ($form['format'] ?? '') == 'bool' ? 'selected' : '' ?>><?=lang('Boolean (Yes/No)', 'Boolean (Ja/Nein)')?></option>
                        <option value="bool-check" <?= ($form['format'] ?? '') == 'bool-check' ? 'selected' : '' ?>><?=lang('Boolean (as checkbox)', 'Boolean (als Checkbox)')?></option>
                        <option value="url" <?= ($form['format'] ?? '') == 'url' ? 'selected' : '' ?>>URL</option>
                        <option value="str-list" <?= ($form['format'] ?? '') == 'str-list' ? 'selected' : '' ?>><?=lang('Free text list (without predefined values)', 'Freitext-Liste (ohne vordefinierte Werte)')?></option>
                        <!-- <option value="user">User</option> -->
                    </select>
                </div>
                <div class="col-sm-6">
                    <label for="default">Default</label>
                    <input type="text" class="form-control" name="values[default]" id="default" value="<?= $form['default'] ?? '' ?>">
                </div>
            </div>



            <fieldset id="values-field" <?= ($form['format'] ?? null) != 'list' ? 'style="display: none;"' : '' ?>>
                <legend><?= lang('Possible values', 'Mögliche Werte') ?></legend>
                <table class="table simple small">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Value (english)</th>
                            <th>Wert (deutsch)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="possible-values">
                        <?php if (!empty($form['values'] ?? [])) { ?>
                            <?php foreach ($form['values'] as $value) {
                                if ($value instanceof \MongoDB\BSON\Document) {
                                    $value = DB::doc2Arr($value);
                                }
                                // dump type of value
                                if (is_array($value) || is_object($value)) {
                                    $de = $value[1] ?? $value[0];
                                    $en = $value[0];
                                } else {
                                    $en = $value;
                                    $de = $value;
                                }
                            ?>
                                <tr>
                                    <td class="w-50">
                                        <i class="ph ph-dots-six-vertical text-muted handle"></i>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="values[values][]" value="<?= $en ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="values[values_de][]" value="<?= $de ?>">
                                    </td>
                                    <td>
                                        <a onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>

                    </tbody>
                </table>
                <button class="btn" type="button" onclick="addValuesRow()"><i class="ph ph-plus-circle"></i></button>

                <p class="text-muted">
                    Hint: changing the values will likely conflict with language support.
                </p>

                <!-- multiple? -->
                <div class="form-group mt-20">
                    <div class="custom-checkbox">
                        <input type="hidden" name="values[multiple]" value="0">
                        <input type="checkbox" name="values[multiple]" id="multiple" value="1" <?= ($form['multiple'] ?? 0) == 1 ? 'checked' : '' ?>>
                        <label for="multiple"><?= lang('Multiple Select', 'Mehrfachauswahl möglich') ?></label>
                    </div>
                </div>

                <div class="form-group mt-20">
                    <div class="custom-checkbox">
                        <input type="hidden" name="values[others]" value="0">
                        <input type="checkbox" name="values[others]" id="others" value="1" <?= ($form['others'] ?? 0) == 1 ? 'checked' : '' ?>>
                        <label for="others"><?= lang('Allow text input as <em>Others</em>', 'Erlaube Text-Input als <em>Sonstiges</em>') ?></label>
                    </div>
                    <small class="text-muted">
                        <?= lang('Currently not supported in combination with multiple select.', 'Zurzeit noch nicht mit Mehrfachauswahl unterstützt.') ?>
                    </small>
                </div>
            </fieldset>

            <button type="submit" class="btn success" id="submitBtn"><?= $btntext ?></button>

        </div>
    </div>


</form>

<?php if (!empty($form['id'] ?? null)) { ?>
    <h3>
        <?= lang('Entities that use this field', 'Entitäten, die dieses Feld verwenden') ?>
    </h3>

    <?php
    $id = $form['id'] ?? '';
    $activities = $osiris->adminTypes->find([
        '$or' => [
            ['modules' => ['$in' => [$id, $id . '*']]],
            ['fields.id' => $id]
        ]
    ])->toArray();

    $projects = $osiris->adminProjects->find([
        'phases.modules.module' => $id
    ])->toArray();

    $temp = $Settings->get('person-data');
    if (!empty($temp) && in_array($id, DB::doc2Arr($temp))) {
        $persons = true;
    } else {
        $persons = false;
    }

    $temp = $Settings->get('infrastructure-data');
    if (!empty($temp) && in_array($id, DB::doc2Arr($temp))) {
        $infrastructure = true;
    } else {
        $infrastructure = false;
    }
    ?>
    <table class="table">
        <tbody>
            <tr>
                <th class="w-200">
                    <?= lang('Activities', 'Aktivitäten') ?>
                </th>
                <td>
                    <?php if (!empty($activities)) { ?>
                        <?php foreach ($activities as $a) { ?>
                            <a href="<?= ROOTPATH ?>/admin/types/<?= $a['id'] ?>" class="badge primary">
                                <i class="ph ph-folder-open ph-<?= $a['icon'] ?? '' ?>"></i>
                                <?= lang($a['name'] ?? $a['id'], $a['name_de'] ?? null) ?>
                            </a>
                        <?php } ?>
                    <?php } else { ?>
                        <em><?= lang('No activity type uses this field.', 'Keine Aktivitätstyp verwendet dieses Feld.') ?></em>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th class="w-200">
                    <?= lang('Projects', 'Projekte') ?>
                </th>
                <td>
                    <?php if (!empty($projects)) { ?>
                        <?php foreach ($projects as $p) { ?>
                            <a href="<?= ROOTPATH ?>/admin/projects/<?= $p['id'] ?>" class="badge primary">
                                <i class="ph ph-folder-open ph-<?= $p['icon'] ?? '' ?>"></i>
                                <?= lang($p['name'] ?? $p['id'], $p['name_de'] ?? null) ?>
                            </a>
                        <?php } ?>
                    <?php } else { ?>
                        <em><?= lang('No project type uses this field.', 'Kein Projekttyp verwendet dieses Feld.') ?></em>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th class="w-200">
                    <?= lang('Persons', 'Personen') ?>
                </th>
                <td>
                    <?php if ($persons) { ?>
                        <i class="ph ph-check-circle text-success"></i>
                        <?= lang('Persons use this field.', 'Personen verwenden dieses Feld.') ?>
                    <?php } else { ?>
                        <em><?= lang('Persons do not use this field.', 'Personen verwenden dieses Feld nicht.') ?></em>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th class="w-200">
                    <?= lang('Infrastructure', 'Infrastruktur') ?>
                </th>
                <td>
                    <?php if ($infrastructure) { ?>
                        <i class="ph ph-check-circle text-success"></i>
                        <?= lang('Infrastructures use this field.', 'Infrastrukturen verwenden dieses Feld.') ?>
                    <?php } else { ?>
                        <em><?= lang('Infrastructures do not use this field.', 'Infrastrukturen verwenden dieses Feld nicht.') ?></em>
                    <?php } ?>
                </td>
            </tr>
        </tbody>
    </table>


<?php } ?>



<?php include_once BASEPATH . '/header-editor.php'; ?>
<script>
    function addValuesRow() {
        $('#possible-values').append(`
            <tr>
                <td class="w-50">
                    <i class="ph ph-dots-six-vertical text-muted handle"></i>
                </td>
                <td>
                    <input type="text" class="form-control" name="values[values][]">
                </td>
                <td>
                    <input type="text" class="form-control" name="values[values_de][]">
                </td>
                <td>
                    <a onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                </td>
            </tr>
        `);
    }

    function updateFields(name) {
        $('#values-field').hide()
        switch (name) {
            case 'string':
                break;
            case 'text':
                break;
            case 'int':
                break;
            case 'float':
                break;
            case 'list':
                $('#values-field').show()
                if ($('#possible-values').find('tr').length == 0) {
                    addValuesRow()
                }
                break;
            case 'date':
                break;
            case 'bool':
                break;
            default:
                break;
        }
    }

    $(document).ready(function() {
        $('#possible-values').sortable({
            handle: ".handle",
            // change: function( event, ui ) {}
        });
    })
</script>


<?php if (isset($_GET['verbose'])) {
    dump($form);
} ?>