<?php

/**
 * Edit details of a teaching module
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.8.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$form = $GLOBALS['form'] ?? [];
function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return e($val);
    }
    if ($val instanceof MongoDB\Model\BSONArray) {
        return implode(',', DB::doc2Arr($val));
    }
    return $val;
}

function sel($index, $value)
{
    return strtolower(val($index)) == strtolower($value) ? 'selected' : '';
}


if (empty($form) || !isset($form['_id'])) {
    $formaction = ROOTPATH . "/crud/teaching/create";
    $url = ROOTPATH . "/teaching/view/*";
} else {
    $formaction = ROOTPATH . "/crud/teaching/update/" . $form['_id'];
    $url = ROOTPATH . "/teaching/view/" . $form['_id'];
}
include_once BASEPATH . "/header-editor.php";
?>

<div class="container" style="max-width: 80rem;">
    <?php if (empty($form) || !isset($form['_id'])) { ?>

        <h1 class="title">
            <i class="ph-duotone ph-chalkboard" aria-hidden="true"></i>
            <?= lang('New Teaching Module', 'Neue Lehrveranstaltung') ?>
        </h1>

    <?php } else { ?>
        <h1 class="title">
            <i class="ph-duotone ph-chalkboard" aria-hidden="true"></i>
            <?= lang('Edit Teaching Module', 'Lehrveranstaltung bearbeiten') ?>
        </h1>
    <?php } ?>
    <form action="<?= $formaction ?>" method="post" class="form">
        <input type="hidden" name="redirect" value="<?= $url ?>">

        <div class="form-group">
            <label for="module" class="required element-other"><?= lang('Module number', 'Modulnummer') ?></label>
            <input type="text" class="form-control" name="values[module]" id="module" required value="<?= val('module') ?>" placeholder="MB05">
        </div>

        <div class="form-group lang-<?= lang('en', 'de') ?>">
            <label for="title" class="required element-title">
                <?= lang('Name of the module', 'Name des Moduls') ?>
            </label>

            <div class="form-group title-editor" id="title-editor"><?= $form['title'] ?? '' ?></div>
            <input type="text" class="form-control hidden" name="values[title]" id="title" required value="<?= val('title') ?>">
        </div>

        <script>
            initQuill(document.getElementById('title-editor'));
        </script>

        <div class="form-group">
            <label class="required element-author" for="username">
                <?= lang('Contact person in the institution', 'Ansprechperson in der Einrichtung') ?>
            </label>
            <select class="form-control" id="username" name="values[contact_person]" required autocomplete="off">
                <?php
                $userlist = $osiris->persons->find([], ['sort' => ['is_active' => -1, 'last' => 1]]);
                $selected = val('contact_person', $_SESSION['username']);
                foreach ($userlist as $j) { ?>
                    <option value="<?= $j['username'] ?>" <?= $j['username'] == $selected ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                <?php } ?>
            </select>
        </div>

        <?php
        $org_id = val('organization', null);
        $rand_id = rand(1000, 9999);
        ?>
        <div class="form-group">
            <label for="organization" class="required">
                <?= lang('Teaching venue / University', 'Lehrort / Hochschule') ?>
            </label>
            <a id="organization" class="module" href="#organization-modal-organization">
                <i class="ph ph-edit float-right"></i>
                <input hidden readonly name="values[organization]" value="<?= $org_id ?>" id="org-organization-organization" required />
                <span class="text-danger mr-10 float-right" data-toggle="tooltip" data-title="<?= lang('Remove connected organization', 'Verknüpfte Organisation entfernen') ?>">
                    <i class="ph ph-trash" onclick="$('#org-organization-organization').val(''); $('#org-organization-value').html('<?= lang('No organization connected', 'Keine Organisation verknüpft') ?>'); return false;"></i>
                </span>

                <div id="org-organization-value">
                    <?php if (empty($org_id) || !DB::is_ObjectID($org_id)) { ?>

                        <?= lang('No organization selected', 'Keine Organisation ausgewählt') ?>
                        <?php if (!empty($org_id)) { ?>
                            <br><small class="text-muted"><?= $org_id ?></small>
                        <?php } ?>
                        <?php } else {
                        $org_id = DB::to_ObjectID($org_id);
                        $collab = $osiris->organizations->findOne(['_id' => $org_id]);
                        if (!empty($collab)) { ?>
                            <b><?= $collab['name'] ?></b>
                            <br><small class="text-muted"><?= $collab['location'] ?></small>
                        <?php } else { ?>
                            <?= lang('No organization selected', 'Keine Organisation ausgewählt') ?>
                            <br><small class="text-muted"><?= $org_id ?></small>
                    <?php }
                    } ?>
                </div>
            </a>
            <?php

            $orgs = $osiris->teaching->aggregate([
                ['$group' => [
                    '_id' => '$organization',
                    'count' => ['$sum' => 1]
                ]],
                ['$sort' => ['count' => -1]],
                ['$limit' => 5]
            ])->toArray();
            if (!empty($orgs)) { ?>
                <div class="suggestions">
                    <?= lang('Suggestions:', 'Vorschläge:') ?>
                    <?php
                    // suggest oftenly used organisations
                    foreach ($orgs as $o) {
                        if (!DB::is_ObjectID($o['_id'])) continue;
                        $org = $osiris->organizations->findOne(['_id' => DB::to_ObjectID($o['_id'])], ['projection' => ['_id' => 0, 'name' => 1, 'location' => 1, 'id' => ['$toString' => '$_id']]]);
                        if ($org) {
                    ?>
                            <a class="badge primary" onclick='selectOrg("<?= $org["id"] ?>", "<?= e($org["name"]) ?>", "<?= e($org["location"]) ?>", "organization"); return false;'>
                                <?= e($org['name']) ?>
                            </a>
                    <?php
                        }
                    }
                    ?>
                </div>
            <?php } ?>

        </div>


        <button type="submit" class="btn secondary" id="submit"><?= lang('Save', 'Speichern') ?></button>
    </form>


    <div class="modal" id="organization-modal-organization" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a href="#close-modal" class="close" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
                <label for="org-organization-search"><?= lang('Search organization', 'Suche nach Organisation') ?></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="org-organization-search" onkeydown="selectOrgEvent(event, 'organization')" placeholder="<?= lang('Search for an organization', 'Suche nach einer Organisation') ?>" autocomplete="off">
                    <div class="input-group-append">
                        <button class="btn" type="button" onclick="selectOrgEvent(null, 'organization')"><i class="ph ph-magnifying-glass"></i></button>
                    </div>
                </div>
                <p id="org-organization-search-comment"></p>
                <table class="table simple">
                    <tbody id="org-organization-suggest">
                    </tbody>
                </table>
                <small class="text-muted">Search powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>

                <p>
                    <?php if ($Settings->hasPermission('organizations.edit')) { ?>
                        <?= lang('Organisation not found? You can ', 'Organisation nicht gefunden? Du kannst sie') ?>
                        <a target="_blank" href="<?= ROOTPATH ?>/organizations/new" target="_blank"><?= lang('add it manually', 'manuell anlegen') ?></a>.
                    <?php } else { ?>
                        <?= lang('Organisation not found? Please contact', 'Organisation nicht gefunden? Bitte kontaktiere') ?>
                        <a target="_blank" href="<?= ROOTPATH ?>/user/browse?permission=organizations.edit">
                            <?= lang('someone who can add it manually', 'jemanden, der sie manuell anlegen kann') ?>
                        </a>
                    <?php } ?>
                </p>
            </div>
        </div>
    </div>


</div>

<script src="<?= ROOTPATH ?>/js/organizations.js?v=<?= OSIRIS_BUILD ?>"></script>
<script>
    function selectOrgEvent(event = null, type = 'organization') {
        console.log(type);
        if (event === null || event.key === 'Enter') {
            if (event) event.preventDefault();

            SUGGEST = $('#org-' + type + '-suggest')
            INPUT = $('#org-' + type + '-search')
            SELECTED = $('#org-' + type + '-value')
            COMMENT = $('#org-' + type + '-search-comment')
            // console.log(SUGGEST);
            window.createOrganizationTR = function(org) {
                // overwrite organisation function
                let id = cleanID(org.id)
                $('#org-' + type + '-value').html(
                    `<b>${org.name}</b> <br><small class="text-muted">${org.location}</small>`
                );
                $('#org-' + type + '-organization').val(id);
                location.href = '#' + type;
            }

            getOrganization(INPUT.val());
            return false;
        }
    }

    function selectOrg(id, name, location, type = 'organization') {
        console.log(id);
        console.log(name);
        $('#org-' + type + '-value').html(
            `<b>${name}</b> <br><small class="text-muted">${location}</small>`
        );
        $('#org-' + type + '-organization').val(id);
        location.href = '#' + type;
    }
</script>