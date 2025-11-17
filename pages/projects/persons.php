<?php

$collection = $collection ?? 'projects';

$persons = $project['persons'] ?? array();
$start = $project['start_date'] ?? '';
$end = $project['end_date'] ?? '';
$all_users = $osiris->persons->find(['username' => ['$ne' => null], 'last' => ['$ne' => null]], ['sort' => ['last' => 1]])->toArray();

include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
?>

<div id="person-blanks" class="hidden">
    <select class="form-control role" required id="person-role">
        <?php
        $role = $con['role'] ?? '';
        $vocab = $Vocabulary->getValues('project-person-role');
        foreach ($vocab as $v) { ?>
            <option value="<?= $v['id'] ?>" <?= $role == $v['id'] ? 'selected' : '' ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
        <?php } ?>
    </select>
</div>


<div class="container">

    <h1>
        <i class="ph-duotone ph-users-three"></i>
        <?= lang('Manage project staff', 'Projektmitglieder verwalten') ?>
    </h1>

    <form action="<?= ROOTPATH ?>/crud/<?= $collection ?>/update-persons/<?= $id ?>" method="post">

        <table class="table">
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
                    <?php if ($collection == 'projects') { ?>
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
                    <?php } ?>
                    <th>
                        <?= lang('Units', 'Einheiten') ?>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="project-list">
                <?php foreach ($persons as $i => $con) { ?>
                    <tr>
                        <td>
                            <input type="hidden" name="persons[<?= $i ?>][user]" id="persons-<?= $i ?>" required readonly value="<?= $con['user'] ?>">
                            <?= $DB->getNameFromId($con['user']) ?>
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
                        <?php if ($collection == 'projects') { ?>
                            <td>
                                <input type="date" name="persons[<?= $i ?>][start]" id="persons-<?= $i ?>-start" class="form-control start" value="<?= $con['start'] ?? $start ?>">
                            </td>
                            <td>
                                <input type="date" name="persons[<?= $i ?>][end]" id="persons-<?= $i ?>-end" class="form-control end" value="<?= $con['end'] ?? $end ?>">
                            </td>
                        <?php } ?>
                        <td class="units">
                            <?php
                            $selected = DB::doc2Arr($con['units'] ?? []);
                            if (!is_array($selected)) $selected = [];
                            $person_units = $osiris->persons->findOne(['username' => $con['user']], ['units' => 1]);
                            $person_units = $person_units['units'] ?? [];
                            if (empty($person_units)) {
                                echo '<small class="text-danger">No units found</small>';
                            } else {
                                // $person_units = array_column(DB::doc2Arr($person_units), 'unit');
                                foreach ($person_units as $unit) {
                                    $unit_id = $unit['unit'];
                                    $in_past = isset($unit['end']) && date('Y-m-d') > $unit['end'];
                                    $group = $Groups->getGroup($unit_id);
                                    $unit['name'] = lang($group['name'] ?? 'Unit not found', $group['name_de'] ?? null);
                            ?>
                                    <div class="custom-checkbox mb-5 <?= $in_past ? 'text-muted' : '' ?>">
                                        <input type="checkbox"
                                            name="persons[<?= $i ?>][units][]"
                                            id="unit-<?= $i ?>-<?= htmlspecialchars($unit_id) ?>"
                                            value="<?= htmlspecialchars($unit_id) ?>"
                                            <?= in_array($unit_id, $selected) ? 'checked' : '' ?>>
                                        <label for="unit-<?= $i ?>-<?= htmlspecialchars($unit_id) ?>">
                                            <span data-toggle="tooltip" data-title="<?= $unit['name'] ?>" class="underline-dashed">
                                                <?= htmlspecialchars($unit_id) ?>
                                            </span>
                                        </label>
                                    </div>
                            <?php }
                            }
                            ?>

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
                        <!-- <label for="person-select">
                            <?= lang('Add a new person', 'Füge eine weitere Person hinzu') ?>
                        </label> -->
                        <div class="input-group w-400 mw-full">
                            <select id="person-select" class="form-control person">
                                <?php
                                foreach ($all_users as $s) { ?>
                                    <option value="<?= $s['username'] ?>" <?= ($con['user'] == $s['username'] ? 'selected' : '') ?>>
                                        <?= "$s[last], $s[first] ($s[username])" ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <div class="input-group-append">
                                <button class="btn" type="button" onclick="addProjectRow()"><i class="ph ph-user-plus"></i> <?= lang('Add person', 'Person hinzufügen') ?></button>
                            </div>
                        </div>
                    </td>
                </tr>
            </tfoot>

        </table>

        <button class="btn primary mt-20">
            <i class="ph ph-check"></i>
            <?= lang('Submit', 'Bestätigen') ?>
        </button>
    </form>
</div>

<script>
    /**
     * 1. create a unique ID and make sure its is not used as a row index yet
     * 2. get the selected user from the #person-select input field
     * 3. query /api/user-units/<user> to get the name and units
     * 4. append a new row with all fields, incl a copy of #person-role
     */

    const start = '<?= $start ?>';
    const end = '<?= $end ?>';

    function addProjectRow() {
        const id = Date.now(); // unique ID based on timestamp
        const personSelect = $('#person-select');
        const username = personSelect.val();
        if (!username) {
            toastError('<?= lang('Please select a person', 'Bitte wähle eine Person aus') ?>');
            return;
        }

        const role = $('#person-role').html();
        $.getJSON(`${ROOTPATH}/api/user-units/${username}`, function(data) {
            data = data.data;
            const units = data.units || [];
            const name = data.name || username;
            const newRow = `
            <tr>
                <td>
                    <input type="hidden" name="persons[${id}][user]" value="${username}" required readonly>
                    ${name}
                </td>
                <td>
                    <select name="persons[${id}][role]" class="form-control role" required>${role}</select>
                </td>
                    <?php if ($collection == 'projects') { ?>
                <td>
                    <input type="date" name="persons[${id}][start]" class="form-control start" value="${start}">
                </td>
                <td>
                    <input type="date" name="persons[${id}][end]" class="form-control end" value="${end}">
                </td>
                    <?php } ?>
                <td class="units">
                    ${units.map(unit => `
                        <div class="custom-checkbox mb-5 ${unit.in_past ? 'text-muted' : ''}">
                            <input type="checkbox" name="persons[${id}][units][]" id="unit-${id}-${unit.unit}" value="${unit.unit}">
                            <label for="unit-${id}-${unit.unit}">
                                <span data-toggle="tooltip" data-title="${unit.name}" class="underline-dashed">
                                    ${unit.unit}
                                </span>
                            </label>
                        </div>
                    `).join('')}
                </td>
                <td>
                    <button class="btn danger" type="button" onclick="removeRow(this)"><i class="ph ph-trash"></i></button>
                </td>
            </tr>
        `;
            $('#project-list').append(newRow);
        }).fail(function() {
            toastError('<?= lang('Failed to fetch user data', 'Fehler beim Abrufen der Nutzerdaten') ?>');
        });
    }

    function removeRow(btn) {
        // make sure that at least one row is left
        $(btn).closest('tr').remove()
    }
</script>