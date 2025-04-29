<?php
$persons = $project['persons'] ?? array();
if (empty($persons)) {
    $persons = [
        ['user' => '', 'role' => '']
    ];
}
$start = $project['start_date'] ?? '';
$end = $project['end_date'] ?? '';
$all_users = $osiris->persons->find(['username' => ['$ne' => null], 'last' => ['$ne' => null]], ['sort' => ['last' => 1]])->toArray();

include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
?>


<h1>
    <?= lang('Connect persons', 'Personen verknüpfen') ?>
</h1>

<form action="<?= ROOTPATH ?>/crud/projects/update-persons/<?= $id ?>" method="post">

    <table class="table simple">
        <thead>
            <tr>
                <th>
                    <?= lang('Person', 'Person') ?><br>
                    <span class="badge kdsf m-0">
                        KDSF-B-2-15-A
                    </span>
                </th>
                <th>
                    <?= lang('Role', 'Rolle') ?><br>
                    <span class="badge kdsf m-0">
                        KDSF-B-2-15-B
                    </span>
                </th>
                <th>
                    <?= lang('Start', 'Start') ?><br>
                    <span class="badge kdsf m-0">
                        KDSF-B-2-15-C
                    </span>
                </th>
                <th>
                    <?= lang('End', 'Ende') ?><br>
                    <span class="badge kdsf m-0">
                        KDSF-B-2-15-D
                    </span>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody id="project-list">
            <?php foreach ($persons as $i => $con) { ?>
                <tr>
                    <td class="">
                        <select name="persons[<?= $i ?>][user]" class="form-control person" required>
                            <?php
                            foreach ($all_users as $s) { ?>
                                <option value="<?= $s['username'] ?>" <?= ($con['user'] == $s['username'] ? 'selected' : '') ?>>
                                    <?= "$s[last], $s[first] ($s[username])" ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <select name="persons[<?= $i ?>][role]" id="persons-<?= $i ?>-role" class="form-control role" required>
                            <?php
                            $role = $con['role'] ?? '';
                            $vocab = $Vocabulary->getValues('project-person-role');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= $role == $v['id'] ? 'selected' : '' ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <input type="date" name="persons[<?= $i ?>][start]" id="persons-<?= $i ?>-start" class="form-control start" value="<?= $con['start'] ?? $start ?>">
                    </td>
                    <td>
                        <input type="date" name="persons[<?= $i ?>][end]" id="persons-<?= $i ?>-end" class="form-control end" value="<?= $con['end'] ?? $end ?>">
                    </td>
                    <td>
                        <button class="btn danger" type="button" onclick="removeRow(this)"><i class="ph ph-trash"></i></button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr id="last-row">
                <td colspan="6">
                    <button class="btn" type="button" onclick="addProjectRow()"><i class="ph ph-plus"></i> <?= lang('Add row', 'Zeile hinzufügen') ?></button>
                </td>
            </tr>
        </tfoot>

    </table>

    <button class="btn primary mt-20">
        <i class="ph ph-check"></i>
        <?= lang('Submit', 'Bestätigen') ?>
    </button>
</form>

<script>
    const tr = $('#project-list tr').first().html()
    var counter = <?= $i ?? 0 ?>;
    var start = '<?= $start ?>'
    var end = '<?= $end ?>'

    function addProjectRow() {
        counter++;
        console.log(counter);
        const row = $('<tr>' + tr + '</tr>')
        row.find('select,input').each(function() {
            const name = $(this).attr('name').replace(/\d+/, counter)
            $(this).attr('name', name)
        })
        $('#project-list').append(row)
        // empty the values
        row.find('input').val('')
        row.find('select').val('')
        row.find('input.start').val(start)
        row.find('input.end').val(end)
    }

    function removeRow(btn) {
        // make sure that at least one row is left
        // if ($('#infrastructure-list tr').length <= 1) {
        //     alert('<?= lang('At least one person is required', 'Mindestens eine Person ist erforderlich') ?>')
        //     return
        // }
        $(btn).closest('tr').remove()
    }
</script>