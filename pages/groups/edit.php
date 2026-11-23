<?php

/**
 * Page to add new groups
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /groups/new
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$heads = $form['head'] ?? [];
if (is_string($heads)) $heads = [$heads];
else $heads = DB::doc2Arr($heads);

// $user_groups = $USER['units'] ?? [];


$edit_perm = ($Settings->hasPermission('units.add') || $Groups->editPermission($id));

if (!$edit_perm) {
    echo "You have no right to be here.";
    die;
}

$Format = new Document(true);
$form = $form ?? array();

$formaction = ROOTPATH;
$formaction .= "/crud/groups/update/" . $form['_id'];
$btntext = '<i class="ph ph-check"></i> ' . t('action.update');
$url = ROOTPATH . "/groups/edit/" . $form['_id'];
$title = t('common.edit_group') . $id;

$level = $Groups->getLevel($id);

function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return e($val);
    }
    return $val;
}

function sel($index, $value)
{
    return val($index) == $value ? 'selected' : '';
}

?>
<?php
include_once BASEPATH . '/header-editor.php';
?>

<style>
    section {
        margin: 2rem 0;
    }

    .suggestions {
        color: #464646;
        /* position: absolute; */
        margin: 10px auto;
        top: 100%;
        left: 0;
        max-height: 19.2rem;
        overflow: auto;
        bottom: -3px;
        width: 100%;
        box-sizing: border-box;
        min-width: 12rem;
        background-color: white;
        border: var(--border-width) solid #afafaf;
        /* visibility: hidden; */
        /* opacity: 0; */
        z-index: 100;
        -webkit-transition: opacity 0.4s linear;
        transition: opacity 0.4s linear;
    }

    .suggestions a {
        display: block;
        padding: 0.5rem;
        border-bottom: var(--border-width) solid #afafaf;
        color: #464646;
        text-decoration: none;
        width: 100%;
    }

    .suggestions a:hover {
        background-color: #f0f0f0;
    }

    .form-control.large {
        font-weight: bold;
    }
</style>

<script>
    const UNIT = '<?= $id ?>';
</script>
<script src="<?= ROOTPATH ?>/js/groups-editor.js?v=<?= OSIRIS_BUILD ?>"></script>


<h1 class="title">
    <?= $title ?>
</h1>

<?php if ($form['inactive'] ?? false) { ?>
    <div>
        <span class="badge danger">
            <?= t('groups.inactive') ?>
        </span>
    </div>
<?php } ?>


<nav class="pills mt-20 mb-0">
    <a onclick="navigate('general')" id="btn-general" class="btn active">
        <i class="ph ph-gear" aria-hidden="true"></i>
        <?= t('common.general') ?>
    </a>
    <a onclick="navigate('personnel')" id="btn-personnel" class="btn">
        <i class="ph ph-users" aria-hidden="true"></i>
        <?= t('groups.personnel') ?>
    </a>

    <a onclick="navigate('research-interest')" id="btn-research-interest" class="btn">
        <i class="ph ph-flask" aria-hidden="true"></i>
        <?= t('common.research') ?>
    </a>
    <a onclick="navigate('settings')" id="btn-settings" class="btn">
        <i class="ph ph-trash" aria-hidden="true"></i>
        <?= t('action.delete') ?>
    </a>

</nav>

<form action="<?= $formaction ?>" method="post" id="group-form">
    <input type="hidden" class="hidden" name="redirect" value="<?= $url ?>">


    <section id="general">

        <h3 class=""><?= t('groups.name_and_description') ?></h3>
        <fieldset>
            <?php
            localizedField($form, 'name', t('forms.full_name'), [
                'required' => true,
                'class' => 'large',
                'group' => 'general'
            ]);
            localizedField($form, 'description', t('common.description'), ['type' => 'richtext', 'group' => 'general']);
            ?>
        </fieldset>


        <h3 class="mt-0"><?= t('common.general') ?></h3>
        <fieldset>
            <?php if ($Settings->featureEnabled('portal') && $level != 0) { ?>
                <h5 class="mt-0">
                    <?= t('common.visibility_on_website') ?>
                </h5>

                <div class="form-group">
                    <input type="hidden" name="values[hide]" value="0">
                    <div class="custom-switch">
                        <input type="checkbox" id="hide-check" <?= val('hide') ? 'checked' : '' ?> name="values[hide]" value="1">
                        <label for="hide-check">
                            <?= t('groups.hide_group_from_public_view_edit') ?>
                        </label>
                    </div>
                </div>
            <?php } ?>


            <!-- inactive -->
            <div class="form-group">
                <input type="hidden" name="values[inactive]" value="0">
                <div class="custom-switch">
                    <input type="checkbox" id="inactive-check" <?= val('inactive') ? 'checked' : '' ?> name="values[inactive]" value="1">
                    <label for="inactive-check">
                        <?= t('groups.mark_group_as_inactive') ?>
                    </label>
                </div>
            </div>

            <div class="row row-eq-spacing mt-0">
                <div class="col-md-2">
                    <label for="id" class="required">
                        <?= t('common.acronym') ?>
                    </label>
                    <input type="text" class="form-control" name="values[id]" id="id" required value="<?= val('id') ?>" maxlength="9">
                </div>

                <div class="col-sm-5">
                    <label for="parent">
                        <?= t('common.parent_group') ?>
                    </label>
                    <select class="form-control" name="values[parent]" id="parent" onchange="deptSelect(this.value)">
                        <option value="" data-level="99"><?= t('common.attention_no_parent_group_chosen') ?></option>
                        <?php foreach ($Groups->groups as $d => $dept) { ?>
                            <option value="<?= $d ?>" <?= sel('parent', $d) ?> data-level="<?= $dept['level'] ?? $Groups->getLevel($d) ?>">
                                <?php $departmentName = localized($dept['name']); ?>
                                <?= $departmentName != $d ? "$d: " : '' ?><?= e($departmentName) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>


                <div class="col-sm-5">
                    <label for="unit" class="required">
                        <?= t('common.type_of_group') ?>
                    </label>
                    <input type="text" class="form-control" name="values[unit]" id="unit" required value="<?= val('unit') ?>" placeholder="<?= t('common.double_click_to_see_suggestions') ?>" list="unit-list">
                </div>
            </div>
            <div class="form-group" id="color-row" <?= $level != 1 ? 'style="display:none;"' : '' ?>>
                <label for="color" class=""><?= t('common.color') ?></label>
                <input type="color" class="form-control w-100" name="values[color]" required value="<?= val('color') ?>">
                <span><?= t('common.note_that_only_level_1_groups_can_have_a_color') ?></span>
            </div>

            <!-- synonyms -->
            <div class="form-group">
                <label for="synonyms">
                    <?= t('common.synonyms') ?>
                </label>
                <?php
                $synonyms = DB::doc2Arr($form['synonyms'] ?? []);
                ?>

                <input type="text" class="form-control" name="values[synonyms]" id="synonyms" value="<?= e(is_array($synonyms) ? implode('; ', $synonyms) : $synonyms) ?>">
                <small class="text-muted"><?= t('groups.separate_multiple_synonyms_with_semi_colons') ?></small>
            </div>

            <!-- cost center -->
            <div class="form-group">
                <label for="costcenter">
                    <?= t('groups.cost_center') ?>
                </label>
                <input type="text" class="form-control" name="values[costcenter]" id="costcenter" value="<?= val('costcenter') ?>">
            </div>


            <?php if ($Settings->featureEnabled('topics')) { ?>
                <!-- if topics are registered, you can choose them here -->
                <?php $Settings->topicChooser($form['topics'] ?? []) ?>
            <?php } ?>

        </fieldset>

        <button class="btn secondary" type="submit" id="submit-btn">
            <i class="ph ph-check"></i> <?= t('action.save') ?>
        </button>

    </section>


    <section id="research-interest" style="display:none;">

        <h3><?= t('common.research_interests') ?></h3>

        <!-- ensure empty list gets still submitted -->
        <input type="hidden" name="values[research]" value="">
        <div id="research-list">
            <script>
                function moveResearchrow(btn, direction) {
                    const row = btn.closest('.box');
                    if (direction === 'up' && row.previousElementSibling) {
                        row.parentNode.insertBefore(row, row.previousElementSibling);
                    } else if (direction === 'down' && row.nextElementSibling) {
                        row.parentNode.insertBefore(row.nextElementSibling, row);
                    }
                }
            </script>
            <?php
            if (isset($form['research']) && !empty($form['research'])) {

                foreach ($form['research'] as $i => $con) { ?>

                    <div class="box">
                        <div class="header">
                            <h4 class="m-0"><q><?= e(localized($con['title'] ?? t('common.research_interest'))) ?></q></h4>
                            <div class="btn-group ml-auto">
                                <button class="btn" type="button" onclick="moveResearchrow(this, 'up')"><i class="ph ph-arrow-up"></i></button>
                                <button class="btn" type="button" onclick="moveResearchrow(this, 'down')"><i class="ph ph-arrow-down"></i></button>
                                <button class="btn danger" type="button" onclick="$(this).closest('.box').remove()"><i class="ph ph-trash"></i></button>
                            </div>
                        </div>

                        <div class="content">

                            <?php
                            $languageGroup = "research.$i";
                            localizedField($form, "research.$i.title", t('common.title'), [
                                'required' => true,
                                'class' => 'large',
                                'group' => $languageGroup,
                            ]);
                            localizedField($form, "research.$i.subtitle", t('common.subtitle'), [
                                'group' => $languageGroup,
                            ]);
                            localizedField($form, "research.$i.info", t('common.description'), [
                                'type' => 'richtext',
                                'group' => $languageGroup,
                            ]);
                            ?>
                        </div>
                        <hr>
                        <div id="activities-<?= $i ?>" class="content">
                            <h5><?= t('common.connected_activities') ?></h5>

                            <table class="table simple small">
                                <tbody class="activity-list">

                                    <?php foreach ($con['activities'] ?? [] as $res) {
                                        $doc = $DB->getActivity($res);
                                    ?>
                                        <tr>
                                            <td><i class="ph ph-dots-six-vertical handle"></i></td>
                                            <td>
                                                <?= $doc['rendered']['icon'] ?>
                                                <?= $doc['rendered']['plain'] ?>
                                                <input type="hidden" name="values[research][<?= $i ?>][activities][]" value="<?= $res ?>">
                                            </td>
                                            <td>
                                                <button class="btn link text-danger small" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>

                            </table>

                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="<?= t('action.search') ?>" onkeypress="if(event.key === 'Enter') { searchActivities('<?= $i ?>'); event.preventDefault(); }">
                                <div class="input-group-append">
                                    <button class="btn secondary" type="button" onclick="searchActivities('<?= $i ?>')"><?= t('action.search') ?></button>
                                </div>
                            </div>

                            <div class="suggestions" style="display:none;"></div>

                        </div>
                    </div>
            <?php }
            } ?>

        </div>

        <?php
        $templateIndex = '__INDEX__';
        $templateForm = ['research' => [$templateIndex => []]];
        $templateLanguageGroup = "research.$templateIndex";
        ?>
        <template id="research-row-template">
            <div class="box">
                <div class="header">
                    <h4 class="m-0"><q><?= t('common.research_interest') ?></q></h4>
                    <div class="btn-group ml-auto">
                        <button class="btn" type="button" onclick="moveResearchrow(this, 'up')"><i class="ph ph-arrow-up"></i></button>
                        <button class="btn" type="button" onclick="moveResearchrow(this, 'down')"><i class="ph ph-arrow-down"></i></button>
                        <button class="btn danger" type="button" onclick="$(this).closest('.box').remove()"><i class="ph ph-trash"></i></button>
                    </div>
                </div>

                <div class="content">
                    <?php
                    localizedField($templateForm, "research.$templateIndex.title", t('common.title'), [
                        'required' => true,
                        'class' => 'large',
                        'group' => $templateLanguageGroup,
                    ]);
                    localizedField($templateForm, "research.$templateIndex.subtitle", t('common.subtitle'), [
                        'group' => $templateLanguageGroup,
                    ]);
                    localizedField($templateForm, "research.$templateIndex.info", t('common.description'), [
                        'type' => 'richtext',
                        'group' => $templateLanguageGroup,
                    ]);
                    ?>
                </div>

                <hr>

                <div id="activities-<?= $templateIndex ?>" class="content">
                    <h5><?= t('common.connected_activities') ?></h5>

                    <table class="table simple small">
                        <tbody class="activity-list"></tbody>
                    </table>

                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="<?= t('action.search') ?>" onkeypress="if(event.key === 'Enter') { searchActivities('<?= $templateIndex ?>'); event.preventDefault(); }">
                        <div class="input-group-append">
                            <button class="btn secondary" type="button" onclick="searchActivities('<?= $templateIndex ?>')"><?= t('action.search') ?></button>
                        </div>
                    </div>

                    <div class="suggestions" style="display:none;"></div>
                </div>
            </div>
        </template>

        <button class="btn" type="button" onclick="addResearchrow(event)">
            <i class="ph ph-plus text-success"></i> <?= t('groups.add_research_interest') ?>
        </button>
        <br>

        <h3>
            <?= t('groups.research_field_classification') ?>
        </h3>
        <?php
        include_once BASEPATH . "/components/kdsf-ffk-select.php";
        ?>

        <button class="btn secondary" type="submit" id="submit-btn">
            <i class="ph ph-check"></i> <?= t('action.save') ?>
        </button>


    </section>


    <section id="personnel" style="display:none;">

        <h3><?= t('common.staff') ?></h3>
        <h5>
            <?= t('common.head_s') ?>
        </h5>
        <div class="form-group">
            <!-- save empty -->
            <input type="hidden" name="values[head]" value="">
            <div class="author-widget">
                <div class="author-list p-10">
                    <?php
                    foreach ($heads as $h) {
                        $person = $osiris->persons->findOne(['username' => $h]);
                        if (empty($person)) continue;
                        $name = $person['last'] . ', ' . $person['first'];
                        $active = $person['is_active'] ?? true;
                        if (!$active) {
                            $name .= ' <small class="text-danger">(' . t('groups.inactive') . ')</small>';
                        }
                    ?>
                        <div class='author'>
                            <?= $name ?>
                            <input type='hidden' name='values[head][]' value='<?= $h ?>'>
                            <a onclick='$(this).parent().remove()'>&times;</a>
                        </div>
                    <?php } ?>

                </div>
                <div class="footer">
                    <div class="input-group d-inline-flex w-auto">
                        <select class="head-input form-control" id="head-select">
                            <option value="" disabled selected><?= t('common.add_head') ?></option>
                            <?php
                            $userlist = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ['is_active' => -1, 'last' => 1]]);
                            foreach ($userlist as $j) {
                                if (in_array($j['username'], $heads) || empty($j['last'])) continue;
                            ?>
                                <option value="<?= $j['username'] ?>"><?= $j['last'] ?>, <?= $j['first'] ?> <?= ($j['is_active'] ?? true) ? '' : '(inaktiv)' ?></option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <button class="btn secondary h-full" type="button" onclick="addHead();">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <script>
                    $("#head-select").selectize();
                </script>
            </div>
        </div>
        <button class="btn secondary" type="submit" id="submit-btn">
            <i class="ph ph-check"></i> <?= t('action.save') ?>
        </button>

    </section>

</form>

<section id="settings" style="display:none;">

    <div class="alert danger mt-20">
        <form action="<?= ROOTPATH ?>/crud/groups/delete/<?= $group['_id'] ?>" method="post">
            <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/groups">
            <button class="btn danger"><i class="ph ph-trash"></i> <?= t('action.delete') ?></button>
            <span class="ml-20"><?= t('common.warning_cannot_be_undone') ?></span>
        </form>
    </div>

</section>

<section id="personnel-2" style="display:none;">
    <h5>
        <?= t('groups.directly_associated_persons') ?>
    </h5>
    <p>
        <?= t('groups.these_persons_are_directly_associated_with_this_group_persons_who_belong_to') ?>
    </p>

    <a class="btn primary" href="#add-person-modal">
        <i class="ph ph-user-plus ph-fw"></i>
        <?= t('common.add_person') ?>
    </a>

    <table class="table mt-20">
        <thead>
            <tr>
                <th><?= t('common.name') ?></th>
                <th><?= t('common.position') ?></th>
                <th><?= t('groups.since') ?></th>
                <th><?= t('common.actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php

            // get all roles that can edit units
            $roles = $osiris->adminRights->find(['right' => 'units.add', 'value' => true], ['projection' => ['role' => 1]])->toArray();
            $roles = array_column($roles, 'role');

            $persons = $Groups->getAllPersons($id);
            foreach ($persons as $p) {
                $is_head = in_array($p['username'], $heads);
                $unit = [];
                if (!empty($p['units'] ?? null)) {
                    // look for the units.unit id
                    $units = array_filter(DB::doc2Arr($p['units']), function ($u) use ($id) {
                        return $u['unit'] == $id;
                    });
                    if (!empty($units))
                        $unit = array_values($units)[0];
                }
                // check if person has editor rights
                $has_editor_rights = false;
                if (!empty($p['roles'] ?? null)) {
                    foreach ($p['roles'] as $r) {
                        if (in_array($r, $roles)) {
                            $has_editor_rights = true;
                            break;
                        }
                    }
                }

            ?>
                <tr>
                    <td><?= $p['last'] ?>, <?= $p['first'] ?></td>
                    <td>
                        <?php if ($is_head) { ?>
                            <i class="ph ph-flag-banner text-secondary"></i>
                        <?php } elseif ($unit['editor'] ?? false) { ?>
                            <i class="ph ph-clipboard-text text-primary"></i>
                        <?php } ?>

                        <?= e(localized($p['position'] ?? '')) ?>
                    </td>
                    <td>
                        <?php if ($unit['start'] ?? false) { ?>
                            <?= date('d.m.Y', strtotime($unit['start'])) ?>
                        <?php } else { ?>
                            <em class="text-muted"><?= t('groups.undefined') ?></em>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="<?= ROOTPATH ?>/profile/<?= $p['username'] ?>" class="btn small">
                            <i class="ph ph-eye"></i> <?= t('groups.view') ?>
                        </a>
                        <form action="<?= ROOTPATH ?>/crud/groups/removeperson/<?= $id ?>" method="post" class="d-inline">
                            <input type="hidden" name="username" value="<?= $p['username'] ?>">
                            <button class="btn danger small"><i class="ph ph-trash"></i> <?= t('action.remove') ?></button>
                        </form>
                        <!-- delegate editing rights -->
                        <form action="<?= ROOTPATH ?>/crud/groups/editorperson/<?= $id ?>" method="post" class="d-inline">
                            <input type="hidden" name="username" value="<?= $p['username'] ?>">
                            <?php if ($has_editor_rights) { ?>

                                <button class="btn muted small" disabled><i class="ph ph-shield-check"></i> <?= t('groups.has_admin_rights') ?></button>
                            <?php } elseif ($unit['editor'] ?? false) { ?>
                                <input type="hidden" name="action" value="remove">
                                <button class="btn secondary small"><i class="ph ph-minus"></i> <?= t('groups.editor_rights') ?></button>
                            <?php } else { ?>
                                <input type="hidden" name="action" value="add">
                                <button class="btn primary small"><i class="ph ph-plus"></i> <?= t('groups.editor_rights') ?>*</button>
                            <?php } ?>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <small class="text-muted">
        * <?= t('groups.persons_with_editor_rights_can_edit_the_group_details_and_add_remove_other') ?>
    </small>

</section>




<datalist id="unit-list">
    <?php
    $units = $osiris->groups->distinct('unit');
    $standard_keys = $Groups->getUnitKeys();
    $standard_keys = array_map('ucfirst', $standard_keys);
    $units = array_merge($units, $standard_keys);
    $units = array_unique($units);
    foreach ($units as $u) { ?>
        <option><?= $u ?></option>
    <?php } ?>
</datalist>

<script>
    var i = <?= $i ?? 0 ?>;
    var CURRENTYEAR = <?= CURRENTYEAR ?>;
    // toggleVisibility();
</script>



<!-- modal to add person -->
<div id="add-person-modal" class="modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><?= t('common.add_person') ?></h2>
            <form action="<?= ROOTPATH ?>/crud/groups/addperson/<?= $id ?>" method="post">

                <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/groups/edit/<?= $id ?>#section-personnel">

                <div class="form-group">
                    <label for="person-username"><?= t('common.person') ?></label>
                    <!-- select for distinct user names from DB -->
                    <select name="username" id="person-username" class="form-control" required>
                        <option value="" disabled selected><?= t('groups.select_person') ?></option>
                        <?php foreach ($osiris->persons->find(['is_active' => ['$ne' => false], 'units.unit' => ['$ne' => $id]], ['sort' => ['last' => 1]]) as $person) { ?>
                            <option value="<?= $person['username'] ?>"><?= $person['last'] . ', ' . $person['first'] ?></option>
                        <?php } ?>
                    </select>
                    <script>
                        $("#person-username").selectize();
                    </script>
                </div>

                <div class="form-group">
                    <label for="start"><?= t('common.start_date') ?></label>
                    <input type="date" name="start" id="person-start" class="form-control">
                </div>

                <div class="form-group">
                    <label for="scientific"><?= t('common.scientific') ?></label>
                    <select class="form-control" id="scientific" name="scientific">
                        <option value="1"><?= t('common.yes') ?></option>
                        <option value="0"><?= t('common.no') ?></option>
                    </select>
                </div>

                <div id="person-affiliated" style="display: none;">
                    <p>
                        <?= t('groups.this_person_is_currently_affiliated_with_the_following_units_without_end_da') ?>
                    </p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= t('common.unit') ?></th>
                                <th><?= t('groups.since') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <p>
                        <?= t('groups.should_the_existing_unit_be_retained_or_terminated') ?>
                    </p>

                    <div class="form-group">
                        <div class="custom-radio">
                            <input type="radio" name="change-or-add" id="person-add" value="add" checked="checked">
                            <label for="person-add">
                                <b><?= t('action.add') ?>:</b>
                                <?= t('groups.add_this_unit_as_additional_unit_and_keep_other_units_unchanged') ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-radio">
                            <input type="radio" name="change-or-add" id="person-change" value="change">
                            <label for="person-change">
                                <b><?= t('groups.change') ?>:</b>
                                <?= t('groups.terminate_existing_units_and_add_this_unit_as_new') ?>
                            </label>
                            <br>
                            <small class="text-danger" id="person-change-warning">
                                <?= t('groups.only_possible_when_a_starting_date_is_set') ?>
                            </small>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn"><?= t('action.add') ?></button>
            </form>

            <script>
                $('#person-username').on('change', function() {
                    var username = $(this).val();
                    if (username) {
                        $.get('<?= ROOTPATH ?>/api/users/' + username + '?columns[]=units', function(data) {
                            console.log(data);
                            var units = data.data.units;
                            if (!units) {
                                $('#person-affiliated').hide();
                                return;
                            } else {
                                var filtered_units = units.filter(function(unit) {
                                    //only show units that are not in the past
                                    return unit.end == null;
                                });
                                if (filtered_units.length == 0) {
                                    $('#person-affiliated').hide();
                                    return;
                                }
                                $('#person-affiliated').show();
                                var tbody = $('#person-affiliated tbody');
                                tbody.empty();
                                filtered_units.forEach(function(unit) {
                                    var tr = $('<tr>');
                                    tr.append($('<td>').html(
                                        `<a href="<?= ROOTPATH ?>/groups/view/${unit.unit}" target='_blank'>${unit.unit}</a>`
                                    ));
                                    tr.append($('<td>').html(
                                        unit.start ? new Date(unit.start).toLocaleDateString() : '<em>undefined</em>'
                                    ));
                                    tbody.append(tr);
                                });
                            }

                        });
                    }
                });

                $('input[name="change-or-add"]').on('change', function() {
                    if ($(this).val() == 'change') {
                        $('#person-start').prop('required', true);
                    } else {
                        $('#person-start').prop('required', false);
                    }
                });
            </script>
        </div>
    </div>
</div>




<script>
    $(document).ready(function() {
        $('.activity-list').sortable({
            handle: ".handle",
            // change: function( event, ui ) {}
        });
    })
</script>
