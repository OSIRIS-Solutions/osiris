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
    <?= lang('common.collaborators') ?>
</h2>



<div class="modal" id="add-organization" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">

        <div class="modal-content">
            <a data-dismiss="modal" href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>

            <div class="content">
                <h3>
                    <?= lang('projects.add_new_organization') ?>
                </h3>

                <p class="text-muted">
                    <?= lang('projects.fill_in_the_details_of_the_new_organization_you_want_to_add_as_a_collaborat') ?>
                </p>

                <div class="form-group">
                    <label for="name" class="required">
                        <?= lang('common.name_of_the_organisation') ?>
                    </label>
                    <input type="text" class="form-control" id="org-name" required>
                </div>

                <div class="form-group">
                    <label for="type" class="required">
                        <?= lang('common.type_of_organisation') ?>
                    </label>
                    <select id="org-type" class="form-control" required>
                        <option value="" disabled><?= lang('common.select_type') ?></option>
                        <option value="education"><?= lang('common.education') ?></option>
                        <option value="funder"><?= lang('common.funder') ?></option>
                        <option value="healthcare"><?= lang('common.healthcare') ?></option>
                        <option value="company"><?= lang('common.company') ?></option>
                        <option value="archive"><?= lang('common.archive') ?></option>
                        <option value="nonprofit"><?= lang('common.non_profit') ?></option>
                        <option value="government"><?= lang('common.government') ?></option>
                        <option value="facility"><?= lang('common.facility') ?></option>
                        <option value="other"><?= lang('common.other') ?></option>
                    </select>
                </div>


                <div class="row row-eq-spacing">

                    <div class="col-sm">
                        <label for="location">
                            <?= lang('common.location_edit') ?>
                        </label>
                        <input type="text" class="form-control" id="org-location">
                    </div>

                    <div class="col-sm">
                        <label for="country" class="required">
                            <?= lang('common.country') ?>
                        </label>
                        <select id="org-country" class="form-control" required>
                            <option value=""><?= lang('common.select_country') ?></option>
                            <?php foreach ($DB->getCountries(lang('common.field_name_language')) as $key => $value) { ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <fieldset>
                    <legend>
                        <?= lang('projects.geographical_coordinates') ?>
                    </legend>
                    <button type="button" class="btn small primary" onclick="getCoordinates('#org-location', '#org-country', '#org-lat', '#org-lng')">
                        <i class="ph ph-map-pin"></i>
                        <?= lang('common.get_coordinates_by_location') ?>
                    </button>
                    <div class="row row-eq-spacing align-items-end">
                        <div class="col-sm">
                            <label for="lat">
                                <?= lang('common.latitude') ?>
                            </label>
                            <input type="number" class="form-control" id="org-lat" step="any">
                        </div>
                        <div class="col-sm">
                            <label for="lng">
                                <?= lang('common.longitude') ?>
                            </label>
                            <input type="number" class="form-control" id="org-lng" step="any">
                        </div>
                    </div>
                    <small class="text-muted">
                        <?= lang('common.geographical_coordinates_are_required_to_correctly_display_the_organisation') ?>
                    </small>
                </fieldset>
                <br><br>
                <button type="button" class="btn secondary" onclick="addOrganization()"><?= lang('action.save') ?></button>

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
                    <?= lang('projects.import_ror_from_csv') ?>
                </h3>
                <p>
                    <?= lang('projects.upload_a_csv_file_containing_ror_to_import_multiple_collaborators_at_once') ?>
                </p>
                <div class="custom-file">
                    <input type="file" id="ror-file">
                    <label for="ror-file"><?= lang('common.select_file') ?></label>
                </div>
                <small>
                    <?= lang('projects.the_file_should_contain_a_column_with_the_header_ror_and_the_ror_ids_in_the') ?>
                    <?= lang('projects.the_following_other_column_names_are_supported_and_will_be_filled_if_they_e') ?>
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
        <?= lang('projects.add_partner') ?>
        <a onclick="$('#search-help').toggleClass('hidden')"><i class="ph ph-question"></i></a>
    </h6>
    <p class="hidden" id="search-help">
        <i class="ph ph-info"></i>
        <?= lang('projects.you_can_search_for_organizations_by_their_name_or_ror_id_osiris_will_look_f') ?>
    </p>
    <div class="position-relative">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>
        </div>
        <div class="d-flex">
            <div class="input-group">
                <input type="text" class="form-control" id="organization-search" onchange="getOrganization(this.value)" placeholder="<?= lang('projects.search_for_organization_by_name_or_ror_id') ?>">
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
                <th><?= lang('common.name') ?></th>
                <th><label class="required" for="lead"><?= lang('common.role') ?></label></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="collaborators">
            <tr id="collab-institute">
                <td>
                    <span data-toggle="tooltip" data-title="<?= lang('projects.this_is_your_institution_you_do_not_need_to_add_it_again') ?>"><i class="ph ph-info text-muted"></i></span>
                    <?= $institute['name'] ?? '' ?>
                </td>
                <td>
                    <?= ucfirst($institute['role'] ?? '') ?>
                </td>
                <td>
                    <?= lang('projects.your_institution') ?>*
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
                                <?= lang('projects.organization_not_found_it_might_have_been_deleted') ?>
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
                            <option <?= $t == 'associated' ? 'selected' : '' ?> value="associated"><?= lang('projects.associated') ?></option>
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
        * <?= lang('projects.your_institution_is_automatically_added_as_a_collaborator_to_every_project') ?>
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
                        <option value="associated">${<?= json_encode(lang('projects.associated'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>}</option>
                    </select>
                </td>`;
        tr += `<td>
                    <a class="text-danger my-10" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                </td>`;
        tr += `</tr>`;
        SELECTED.append(tr);
    }
</script>