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

?>
<style>
    tr.ui-sortable-helper {
        background-color: white;
        border: 1px solid var(--border-color);
    }
</style>

<form action="<?= $formaction ?>" method="post" id="group-form">

    <div class="box">
        <h4 class="header">
            <?= $title ?>
        </h4>

        <div class="content">

            <div class="form-group">
                <label for="id">ID</label>
                <input type="text" class="form-control" name="values[id]" id="id" value="<?= $form['id'] ?? '' ?>" <?= !empty($form) ? 'disabled' : '' ?>>
                <small class="form-text">
                    <?= lang('Important! The ID will be used in the module list and in templates. Choose sth precise, unique and without spaces.', 'Wichtig! Die ID wird in der Modulliste gezeigt, wähle also etwas genaues, einzigartiges und nutze kein Leerzeichen!') ?>
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
                        <option value="string" <?= ($form['format'] ?? '') == 'string' ? 'selected' : '' ?>>Text</option>
                        <option value="text" <?= ($form['format'] ?? '') == 'text' ? 'selected' : '' ?>>Long text</option>
                        <option value="int" <?= ($form['format'] ?? '') == 'int' ? 'selected' : '' ?>>Integer</option>
                        <option value="float" <?= ($form['format'] ?? '') == 'float' ? 'selected' : '' ?>>Float</option>
                        <option value="list" <?= ($form['format'] ?? '') == 'list' ? 'selected' : '' ?>>List</option>
                        <option value="date" <?= ($form['format'] ?? '') == 'date' ? 'selected' : '' ?>>Date</option>
                        <option value="bool" <?= ($form['format'] ?? '') == 'bool' ? 'selected' : '' ?>>Boolean (Ja/Nein)</option>
                        <option value="bool-check" <?= ($form['format'] ?? '') == 'bool-check' ? 'selected' : '' ?>>Boolean (Checkbox)</option>
                        <option value="url" <?= ($form['format'] ?? '') == 'url' ? 'selected' : '' ?>>URL</option>
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

                <!-- multiple? -->
                <div class="form-group mt-20">
                    <label for="multiple"><?=lang('Multiple Select?', 'Mehrfachauswahl möglich?')?></label>
                    <select class="form-control" name="values[multiple]" id="multiple">
                        <option value="0" <?= ($form['multiple'] ?? 0) == 0 ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= ($form['multiple'] ?? 0) == 1 ? 'selected' : '' ?>>Yes</option>
                    </select>
                </div>
            </fieldset>


            <button type="submit" class="btn success" id="submitBtn"><?= $btntext ?></button>

        </div>
    </div>


</form>


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