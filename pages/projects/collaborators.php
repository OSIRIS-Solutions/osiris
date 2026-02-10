<?php
$institute = $Settings->get('affiliation_details');
$institute['role'] = $project['role'] ?? 'partner';
if (!isset($project['collaborators']) || empty($project['collaborators'])) {
    $collaborators = [];
} else {
    $collaborators = $project['collaborators'];
}
?>


<h2>
    <i class="ph-duotone ph-handshake"></i>
    <?= lang('Collaborators', 'Kooperationspartner') ?>
</h2>



<div class="modal" id="add-organization" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">

        <div class="modal-content">
            <a data-dismiss="modal" href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>

            <div class="content">
                <h3>
                    <?= lang('Add new organization', 'Neue Organisation hinzufügen') ?>
                </h3>

                <p class="text-muted">
                    <?= lang('Fill in the details of the new organization you want to add as a collaborator. Please try to search for the organization first to avoid duplicates.', 'Füll die Details der neuen Organisation aus, die du als Kooperationspartner hinzufügen möchtest. Bitte versuche zuerst, die Organisation zu suchen, um Duplikate zu vermeiden.') ?>
                </p>

                <div class="form-group">
                    <label for="name" class="required">
                        <?= lang('Name of the organisation', 'Name der Organisation') ?>
                    </label>
                    <input type="text" class="form-control" id="org-name" required>
                </div>

                <div class="form-group">
                    <label for="type" class="required">
                        <?= lang('Type of organisation', 'Art der Organisation') ?>
                    </label>
                    <select id="org-type" class="form-control" required>
                        <option value="" disabled><?= lang('Select type', 'Art auswählen') ?></option>
                        <option value="education"><?= lang('Education', 'Bildung') ?></option>
                        <option value="funder"><?= lang('Funder', 'Förderer') ?></option>
                        <option value="healthcare"><?= lang('Healthcare', 'Gesundheitswesen') ?></option>
                        <option value="company"><?= lang('Company', 'Unternehmen') ?></option>
                        <option value="archive"><?= lang('Archive', 'Archiv') ?></option>
                        <option value="nonprofit"><?= lang('Non-profit', 'Gemeinnützig') ?></option>
                        <option value="government"><?= lang('Government', 'Regierung') ?></option>
                        <option value="facility"><?= lang('Facility', 'Einrichtung') ?></option>
                        <option value="other"><?= lang('Other', 'Sonstiges') ?></option>
                    </select>
                </div>


                <div class="row row-eq-spacing">

                    <div class="col-sm">
                        <label for="location">
                            <?= lang('Location', 'Standort') ?>
                        </label>
                        <input type="text" class="form-control" id="org-location">
                    </div>

                    <div class="col-sm">
                        <label for="country" class="required">
                            <?= lang('Country', 'Land') ?>
                        </label>
                        <select id="org-country" class="form-control" required>
                            <option value=""><?= lang('Select country', 'Land auswählen') ?></option>
                            <?php foreach ($DB->getCountries(lang('name', 'name_de')) as $key => $value) { ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <fieldset>
                    <legend>
                        <?= lang('Geographical Coordinates', 'Geografische Koordinaten') ?>
                    </legend>
                    <button type="button" class="btn small primary" onclick="getCoordinates('#org-location', '#org-country', '#org-lat', '#org-lng')">
                        <i class="ph ph-map-pin"></i>
                        <?= lang('Get coordinates by location', 'Koordinaten vom Standort ermitteln') ?>
                    </button>
                    <div class="row row-eq-spacing align-items-end">
                        <div class="col-sm">
                            <label for="lat">
                                <?= lang('Latitude', 'Breitengrad') ?>
                            </label>
                            <input type="number" class="form-control" id="org-lat" step="any">
                        </div>
                        <div class="col-sm">
                            <label for="lng">
                                <?= lang('Longitude', 'Längengrad') ?>
                            </label>
                            <input type="number" class="form-control" id="org-lng" step="any">
                        </div>
                    </div>
                    <small class="text-muted">
                        <?= lang('Geographical coordinates are required to correctly display the organisation on a map.', 'Die geografischen Koordinaten werden benötigt, um die Organisation auf einer Karte korrekt darzustellen.') ?>
                    </small>
                </fieldset>
                <br><br>
                <button type="button" class="btn secondary" onclick="addOrganization()"><?= lang('Save', 'Speichern') ?></button>

            </div>
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



<style>
    #add-organization-button {
        margin-left: 10px;
        height: auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }


    #organization-select-button {
        width: 100%;
        text-align: left;
        height: auto;
        line-height: 1.4;
        padding: .5rem 1rem;
        display: flex;
        justify-content: flex-start;
        align-items: center;
    }

    .suggestions {
        margin-top: 10px;
        width: 100%;
        border-collapse: collapse;
        position: absolute;

    }

    .suggestions tr:hover {
        background-color: var(--table-hover-bg);
        cursor: pointer;
    }
</style>
<div class="box padded">
    <h6 class="mt-0">
        <?= lang('Add partner', 'Partner hinzufügen') ?>
        <a onclick="$('#search-help').toggleClass('hidden')"><i class="ph ph-question"></i></a>
    </h6>
    <p class="hidden" id="search-help">
        <i class="ph ph-info"></i>
        <?= lang('You can search for organizations by their name or ROR ID. OSIRIS will look for partners in the database first. If no matching organization is found, it will search in the ROR database.', 'Du kannst nach Organisationen anhand ihres Namens oder ihrer ROR-ID suchen. OSIRIS wird zuerst in der Datenbank nach Partnern suchen. Wenn keine passende Organisation gefunden wird, wird in der ROR-Datenbank gesucht.') ?>
    </p>
    <div class="position-relative">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>
        </div>
        <div class="d-flex">
            <div class="input-group">
                <input type="text" class="form-control" id="organization-search" onchange="getOrganization(this.value)" placeholder="<?= lang('Search for organization by name or ROR ID...', 'Nach Organisation anhand von Name oder ROR ID suchen...') ?>">
                <div class="input-group-append">
                    <button class="btn" onclick="getOrganization($('#organization-search').val())"><i class="ph ph-magnifying-glass"></i></button>
                </div>
            </div>
            <a href="#add-organization" class="btn" id="add-organization-button" data-toggle="tooltip" data-title="Neue Organisation hinzufügen">
                <i class="ph ph-plus-circle ph-2x"></i>
            </a>
        </div>
        <table class="table simple">
            <tbody id="organization-suggest"></tbody>
        </table>
    </div>
</div>

<form action="<?= ROOTPATH ?>/crud/projects/update-collaborators/<?= $id ?>" method="POST">
    <table class="table">
        <thead>
            <tr>
                <th><?= lang('Name', 'Name') ?></th>
                <th><label class="required" for="lead"><?= lang('Role', 'Rolle') ?></label></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="collaborators">
            <tr id="collab-institute">
                <td>
                    <span data-toggle="tooltip" data-title="<?= lang('This is your institution. You do not need to add it again.', 'Dies ist deine Einrichtung. Du musst es nicht erneut hinzufügen.') ?>"><i class="ph ph-info text-muted"></i></span>
                    <?= $institute['name'] ?? '' ?>
                </td>
                <td>
                    <?= ucfirst($institute['role'] ?? '') ?>
                </td>
                <td>
                    <?= lang('Your institution', 'Deine Einrichtung') ?>*
                </td>
            </tr>
            <?php
            foreach ($collaborators as $i => $con) {
                $org = $osiris->organizations->findOne(['_id' => $con['organization']]);
                if (empty($org)) { ?>
                    <tr>
                        <td colspan="2">
                            <span class="text-danger">
                                <i class="ph ph-warning-circle"></i>
                                <b><?= e($con['name'] ?? $con['organization']) ?></b>
                                <?= lang('Organization not found. It might have been deleted.', 'Organisation nicht gefunden. Sie wurde möglicherweise gelöscht.') ?>
                            </span>
                        </td>
                        <td>
                            <a class="text-danger my-10" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                        </td>
                    </tr>
                <?php
                    continue;
                }
                ?>
                <tr id="collab-<?= $i ?>">
                    <td>
                        <?= $org['name'] ?? '' ?>
                        <br>
                        <small class="text-muted">
                            <?= $org['location'] ?? '' ?>
                        </small>
                        <input type="hidden" name="values[organization][]" value="<?= $org['_id'] ?>">
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
                        <a class="text-danger my-10" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                    </td>
                </tr>
            <?php
            } ?>
        </tbody>
    </table>

    <p class="font-size-12 text-muted">
        * <?= lang('Your institution is automatically added as a collaborator to every project you create. You do not need to add it again and you cannot remove it. You can change the role of your institution in the project settings.', 'Deine Einrichtung wird bei jedem von dir erstellten Projekt automatisch als Kooperationspartner hinzugefügt. Du musst es nicht manuell hinzufügen und kannst es auch nicht entfernen. Die Rolle deiner Einrichtung kannst du in den Projekteinstellungen ändern.') ?>
    </p>

    <button type="submit" class="btn secondary mt-10">
        Save
    </button>
</form>

<!-- <script src="<?= ROOTPATH ?>/js/papaparse.min.js"></script> -->
<script src="<?= ROOTPATH ?>/js/organizations.js?v=<?= OSIRIS_BUILD ?>"></script>
<!-- <script src="<?= ROOTPATH ?>/js/collaborators.js?v=<?= OSIRIS_BUILD ?>"></script> -->

<script>
    // override default createOrganizationTR function to add collaborators
    function createOrganizationTR(org) {
        var id = cleanID(org.id)
        let tr = `<tr id="collab-${id}">`;
        tr += `<td>
            ${org.name} <br><small class="text-muted">${org.location}</small>
            <input type="hidden" name="values[organization][]" value="${org.id}">
            </td>`;
        tr += `<td>
                    <select name="values[role][]" type="text" class="form-control " required>
                        <option value="partner" selected>Partner</option>
                        <option value="coordinator">Coordinator</option>
                        <option value="associated">${lang('Associated', 'Beteiligt')}</option>
                    </select>
                </td>`;
        tr += `<td>
                    <a class="text-danger my-10" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                </td>`;
        tr += `</tr>`;
        SELECTED.append(tr);
    }
</script>