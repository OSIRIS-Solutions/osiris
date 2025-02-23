<?php
$institute = $Settings->get('affiliation_details');
$institute['role'] = $project['role'];
if (!isset($project['collaborators']) || empty($project['collaborators'])) {
    $collaborators = [];
} else {
    $collaborators = $project['collaborators'];
}
?>


<h2>
    <?= lang('Collaborators', 'Kooperationspartner') ?>
</h2>


<div class="modal" id="collaborators-select" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>

            <label for="collaborators-search"><?= lang('Search Collaborators', 'Suche nach Kooperationspartnern') ?></label>
            <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>
            <div class="input-group">
                <input type="text" class="form-control" id="collaborators-search" onchange="getCollaborators(this.value)">
                <div class="input-group-append">
                    <button class="btn" onclick="getCollaborators($('#collaborators-search').val())"><i class="ph ph-magnifying-glass"></i></button>
                </div>
            </div>
            <table class="table simple">
                <tbody id="collaborators-suggest">

                </tbody>
            </table>
        </div>
    </div>
</div>



<div class="modal" id="collaborators-upload" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>

            <div class="content">
                <h3>
                    <?= lang('Import ROR from CSV', 'ROR aus CSV-Datei importieren') ?>
                </h3>
                <p>
                    <?= lang('Upload a CSV file containing ROR to import multiple collaborators at once.', 'Lade eine CSV-Datei mit ROR-IDs hoch, um mehrere Kooperationspartner auf einmal zu importieren.') ?>
                </p>
                <div class="custom-file">
                    <input type="file" id="ror-file">
                    <label for="ror-file"><?= lang('Select file', 'Datei auswählen') ?></label>
                </div>
                <small>
                    <?= lang('The file should contain a column with the header "ROR" and the ROR-IDs in the following rows.', 'Die Datei sollte eine Spalte mit der Überschrift "ROR" und den ROR-IDs in den folgenden Zeilen enthalten.') ?>
                    <?= lang(
                        'The following other column names are supported and will be filled if they exist: "name", "latitude", "longitude", "coordinator" (please enter any value, e.g. 1, for yes and leave blank for no), "country" (ISO 2 letter code), "location".',
                        'Die folgenden anderen Spaltennamen werden unterstützt und werden ausgefüllt, wenn sie vorhanden sind: "name", "latitude", "longitude", "coordinator" (bitte geben Sie für "ja" einen beliebigen Wert ein, z. B. 1 und lassen Sie ihn für "nein" leer), "country" (ISO-Code mit zwei Buchstaben), "location".'
                    ) ?>
                </small>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="collaborators-ror" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>

            <div class="content">
                <h3>
                    <?= lang('Add via ROR-ID', 'Mittels ROR-ID hinzufügen') ?>
                </h3>
                <p>
                    <?= lang('Enter the ROR-ID of the collaborator you want to add.', 'Geben Sie die ROR-ID des Kooperationspartners ein, den Sie hinzufügen möchten.') ?>
                </p>
                <div class="input-group">
                    <input type="text" class="form-control" id="collaborators-ror-id" onchange="addCollaboratorROR(this.value)">
                    <div class="input-group-append">
                        <button class="btn" onclick="addCollaboratorROR($('#collaborators-ror-id').val())"><i class="ph ph-magnifying-glass"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="bg-white px-10 py-5 rounded mb-20">
    <b>
        <?= lang('Add new partner', 'Neuen Partner hinzufügen') ?>
    </b>
    <div class="btn-toolbar">
        <a href="#collaborators-ror" class="btn primary">
            <i class="ph ph-plus"></i>
            <?= lang('Add via ROR-ID', 'Mittels ROR-ID hinzufügen') ?>
        </a>
        <a href="#collaborators-select" class="btn primary">
            <i class="ph ph-search"></i>
            <?= lang('Search by name/location', 'Suche via Name/Ort') ?>
        </a>
        <a href="#" class="btn primary" onclick="addCollabRow()">
            <i class="ph ph-edit"></i>
            <?= lang('Add manually', 'Manuell hinzufügen') ?>
        </a>
        <a href="#collaborators-upload" class="btn primary">
            <i class="ph ph-upload"></i>
            <?= lang('Upload', 'Hochladen') ?>
        </a>
    </div>
</div>

<form action="<?= ROOTPATH ?>/crud/projects/update-collaborators/<?= $id ?>" method="POST">
    <table class="table">
        <thead>
            <tr>
                <th><label class="required" for="name"><?= lang('Name', 'Name') ?></label></th>
                <th><label class="required" for="lead"><?= lang('Role', 'Rolle') ?></label></th>
                <th>
                    <label class="required" for="type"><?= lang('Type', 'Typ') ?></label>
                    <a href="https://ror.readme.io/docs/ror-data-structure#types" target="_blank" rel="noopener noreferrer"><i class="ph ph-arrow-square-out"></i></a>
                </th>
                <!-- <th><label for="ror"><?= lang('ROR-ID') ?></label></th> -->
                <th><label for="location"><?= lang('Location', 'Ort') ?></label></th>
                <th><label class="required" for="country"><?= lang('Country', 'Land') ?></label></th>
                <th><label for="lat"><?= lang('Latitute') ?></label></th>
                <th><label for="lng"><?= lang('Longitude') ?></label></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="collaborators">
            <tr id="collab-<?= $i ?>">
                <td>
                    <span data-toggle="tooltip" data-title="<?= lang('This is your institute.', 'Dies ist dein Institut.') ?>"><i class="ph ph-info text-muted"></i></span>
                    <?= $institute['name'] ?? '' ?>
                </td>
                <td>
                    <?= ucfirst($institute['role'] ?? '') ?>
                </td>
                <td>
                    <?= $institute['type'] ?? '' ?>
                </td>
                <td class="hidden">
                    <?= $institute['ror'] ?? '' ?>
                </td>
                <td>
                    <?= $institute['location'] ?? '' ?>
                </td>
                <td>
                    <?= $institute['country'] ?? '' ?>
                </td>
                <td>
                    <?= $institute['lat'] ?? '' ?>
                </td>
                <td>
                    <?= $institute['lng'] ?? '' ?>
                </td>
                <td>
                </td>
            </tr>
            <?php
            foreach ($collaborators as $i => $con) {
            ?>
                <tr id="collab-<?= $i ?>">
                    <td>
                        <input name="values[name][]" type="text" class="form-control " value="<?= $con['name'] ?? '' ?>" required>
                    </td>
                    <td>
                        <?php $t = $con['role'] ?? ''; ?>
                        <select name="values[role][]" type="text" class="form-control " required>
                            <option <?= $t == 'partner' ? 'selected' : '' ?> value="partner">Partner</option>
                            <option <?= $t == 'coordinator' ? 'selected' : '' ?> value="coordinator">Coordinator</option>
                            <option <?= $t == 'associated' ? 'selected' : '' ?> value="associated"><?= lang('Associated', 'Beteiligt') ?></option>
                        </select>
                    </td>
                    <td>
                        <?php $t = $con['type'] ?? ''; ?>
                        <select name="values[type][]" type="text" class="form-control " required>
                            <option value="Education" <?= $t == 'Education' ? 'selected' : '' ?>>Education</option>
                            <option value="Healthcare" <?= $t == 'Healthcare' ? 'selected' : '' ?>>Healthcare</option>
                            <option value="Company" <?= $t == 'Company' ? 'selected' : '' ?>>Company</option>
                            <option value="Archive" <?= $t == 'Archive' ? 'selected' : '' ?>>Archive</option>
                            <option value="Nonprofit" <?= $t == 'Nonprofit' ? 'selected' : '' ?>>Nonprofit</option>
                            <option value="Government" <?= $t == 'Government' ? 'selected' : '' ?>>Government</option>
                            <option value="Facility" <?= $t == 'Facility' ? 'selected' : '' ?>>Facility</option>
                            <option value="Other" <?= $t == 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </td>
                    <td class="hidden">
                        <input name="values[ror][]" type="text" class="form-control " value="<?= $con['ror'] ?? '' ?>">
                    </td>
                    <td>
                        <input name="values[location][]" type="text" class="form-control " value="<?= $con['location'] ?? '' ?>">
                    </td>
                    <td>
                        <input name="values[country][]" type="text" maxlength="2" class="form-control w-50" value="<?= $con['country'] ?? '' ?>" required>
                    </td>
                    <td>
                        <input name="values[lat][]" type="text" class="form-control w-100" value="<?= $con['lat'] ?? '' ?>">
                    </td>
                    <td>
                        <input name="values[lng][]" type="text" class="form-control w-100" value="<?= $con['lng'] ?? '' ?>">
                    </td>
                    <td>
                        <a class="text-danger my-10" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                    </td>
                </tr>
            <?php
            } ?>
        </tbody>
    </table>

    <button type="submit" class="btn secondary mt-10">
        Save
    </button>
</form>

<script src="<?= ROOTPATH ?>/js/papaparse.min.js"></script>
<script src="<?= ROOTPATH ?>/js/collaborators.js?v=<?=CSS_JS_VERSION?>"></script>