<?php

include_once BASEPATH . "/php/Document.php";
include_once BASEPATH . "/php/example-document.php";
$Document = new Document();

$example = 'default';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    if (!DB::is_ObjectID($_GET['id'])) {
        echo lang('The ID you entered is not valid. Please use a valid activity ID.');
    } else {
        $new = $osiris->activities->findOne(
            ['_id' => DB::to_ObjectID($_GET['id'])]
        );
        if (empty($new)) {
            echo lang("Sorry, the activity was not found in the database. We will use the default example.");
        } else {
            $form = $new;
            $example = strval($new['_id']);
        }
    }
} elseif (isset($_GET['type']) && !empty($_GET['type'])) {
    // get newest 
    $new = $osiris->activities->findOne(
        ['type' => $_GET['type']],
        ['sort' => ['_id' => -1]]
    );
    if (empty($new)) {
        echo lang("Sorry, the activity was not found in the database. We will use the default example.");
    } else {
        $form = $new;
        $example = strval($new['_id']);
    }
}

$Document->setDocument($form);
?>

<div class="container">

    <h1>
        Template Builder
        <small class="badge danger float-right"><i class="ph ph-warning"></i> BETA</small>
    </h1>


    <form class="row row-eq-spacing">
        <div class="col floating-form">
            <select name="type" id="type-id" class="form-control">
                <option value="">Select a type</option>
                <?php
                $types = $osiris->adminTypes->find([], ['sort' => ['parent' => 1]]);
                foreach ($types as $type) {
                    echo '<option value="' . $type['id'] . '">' . ucfirst($type['parent']) . ' > '. $type['name'] . '</option>';
                }
                ?>
            </select>
            <label for="type-id">
                <?= lang('Select a type to load the template', 'Wähle einen Typen aus, um das Template zu laden.') ?>
            </label>
        </div>
        <div class="col floating-form">
            <input type="text" name="id" placeholder="id" class="form-control" value="<?= $_GET['id'] ?? '' ?>">
            <label for="id">
                <?= lang('Enter activity ID for displaying a specific example', 'Gib eine für ein spezifisches Beispiel eine Aktivitäts-ID an') ?>
            </label>
        </div>
        <div class="col flex-grow-0">
            <button class="btn primary large" type="submit">Load</button>
        </div>
    </form>

    <hr>

    <p>
        <?=lang('In the following, ', 'Im folgenden wird')?>
        <?php if (DB::is_ObjectID($example)) { ?>
            <a href="<?=ROOTPATH?>/activities/view/<?=$example?>"><?=lang('a real example', 'ein echtes Beispiel')?></a>
        <?php } else { ?>
            <?=lang('a dummy dataset', 'ein Dummy-Datensatz')?>
        <?php } ?>
        <?=lang('is used to show the template builder.', 'eingesetzt, um die Auswirkung des Template-Builders zu veranschaulichen.')?>
        
    </p>


    <div class="row row-eq-spacing">
        <div class="col">
            <h3 class="mt-5">Template</h3>

            <style>
                .template-editor {
                    border: 1px solid var(--border-color);
                    box-sizing: border-box;
                    padding: 0;
                    border-radius: var(--border-radius);
                    background-color: white;
                }

                .template-editor .btn-group {
                    background-color: white;
                    margin: 3px;
                }

                .template-editor textarea {
                    border: none;
                    border-top: 1px solid var(--border-color);
                    border-bottom: 1px solid var(--border-color);
                    background-color: var(--primary-color-20);
                    border-radius: 0;
                    font-family: 'Source Code Pro', Courier, monospace;
                    height: 15rem;
                }

                .template-editor .example-area {
                    padding: 1rem;
                }
            </style>

            <div class="template-editor">
                <div class="btn-group">
                    <button class="btn link small" type="button" onclick="addElement('em')"><i class="ph ph-text-italic"></i></button>
                    <button class="btn link small" type="button" onclick="addElement('b')"><i class="ph ph-text-b"></i></button>
                    <button class="btn link small" type="button" onclick="addElement('u')"><i class="ph ph-text-underline"></i></button>

                    <button class="btn link small ml-10" type="button" onclick="addElement('br')"><i class="ph ph-key-return"></i></button>
                </div>
                <textarea name="template" id="template" class="form-control" placeholder="Start creating your template here"><?= $template ?></textarea>
                <div class="example-area">
                    <b><?= lang('Example', 'Beispiel') ?>:</b>
                    <div id="example" style="min-height: 3rem;"></div>
                </div>
            </div>

        </div>
        <div class="col">
            <input type="search" id="search-templates" class="form-control mb-10" placeholder="Search in table ...">
            <div class="h-400 overflow-auto">
                <table class="table small" id="all-templates">

                    <tbody>
                        <?php

                        $fields = $Document->templates;
                        foreach ($Document->custom_fields as $name => $values) {
                            $fields[$name] = [];
                        }
                        foreach ($fields as $name => $fields) { ?>
                            <tr id="t-<?= $name ?>">
                                <td>
                                    <a onclick="addTemplate('<?= $name ?>')">
                                        <b><?= $name ?></b>
                                    </a>
                                    <?php
                                    $fields = array_filter($fields, function ($field) use ($name) {
                                        return $field != $name;
                                    });
                                    if (!empty($fields))
                                        echo ' <small>(' . implode(', ', $fields) . ')</small>';
                                    ?>

                                    <br>
                                    <small>
                                        <b><?= lang('Example', 'Beispiel') ?>:</b>
                                        <span class="example">
                                            <?php
                                            echo $Document->get_field($name, '-');
                                            ?>
                                        </span>
                                    </small>
                                </td>
                            </tr>
                        <?php   } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<script>
    $('#search-templates').on('input', function() {
        let table = $('#all-templates tbody')
        let input = $('#search-templates').val()
        table.find('tr').show()
        if (input == '') return;
        table.find('tr:not(:contains("' + input + '"))').hide()
    })

    function addTemplate(name) {
        let text = $('#template').val()
        // $('#template').val(`${text}{${name}}`)
        insertAtCursor('template', `{${name}}`)
        updateExample()
        // focus on the textarea
        $('#template').focus()
    }

    function addElement(element) {
        let text = $('#template').val()
        let field = document.getElementById('template')

        let startel = '<' + element + '>'
        let endel = '</' + element + '>'
        if (element == 'br') {
            endel = ''
        }

        let start = field.selectionStart
        let end = field.selectionEnd
        let selected = field.value.substring(start, end)
        let before = field.value.substring(0, start)
        let after = field.value.substring(end)

        field.value = before + startel + selected + endel + after
        field.selectionStart = start + startel.length
        field.selectionEnd = end + startel.length
        field.focus()

        updateExample()
    }


    function insertAtCursor(field, value, wrapAround = false) {

        field = document.getElementById(field)
        //IE support
        if (document.selection) {
            field.focus();
            const sel = document.selection.createRange();
            sel.text = value;
        }
        //MOZILLA and others
        else if (field.selectionStart || field.selectionStart == '0') {
            var startPos = field.selectionStart;
            var endPos = field.selectionEnd;
            field.value = field.value.substring(0, startPos) +
                value +
                field.value.substring(endPos, field.value.length);
        } else {
            field.value += value;
        }
    }

    function updateExample() {
        let content = $('#template').val()
        // replace {templates} with the respective examples
        let pattern = /{([^}]*)}/
        console.log(content);
        while (pattern.test(content)) {
            let match = pattern.exec(content)
            let name = match[1]
            let example = $(`#t-${name} .example`).html().trim()
            content = content.replace(`{${name}}`, example)
        }

        console.log(content);
        $('#example').html(content)
    }

    $('#template').on('change', updateExample)

    updateExample();
</script>