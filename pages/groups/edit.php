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
$btntext = '<i class="ph ph-check"></i> ' . lang('action.update');
$url = ROOTPATH . "/groups/edit/" . $form['_id'];
$title = lang('common.edit_group') . $id;

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

<script src="<?= ROOTPATH ?>/js/selectize.min.js"></script>
<link rel="stylesheet" href="<?= ROOTPATH ?>/css/selectize.css">

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
<?php include_once BASEPATH . '/header-editor.php'; ?>
<script src="<?= ROOTPATH ?>/js/groups-editor.js?v=<?= OSIRIS_BUILD ?>"></script>


<h1 class="title">
    <?= $title ?>
</h1>

<?php if ($form['inactive'] ?? false) { ?>
    <div>
        <span class="badge danger">
            <?= lang('groups.inactive') ?>
        </span>
    </div>
<?php } ?>


<nav class="pills mt-20 mb-0">
    <a onclick="navigate('general')" id="btn-general" class="btn active">
        <i class="ph ph-gear" aria-hidden="true"></i>
        <?= lang('common.general') ?>
    </a>
    <a onclick="navigate('personnel')" id="btn-personnel" class="btn">
        <i class="ph ph-users" aria-hidden="true"></i>
        <?= lang('groups.personnel') ?>
    </a>

    <a onclick="navigate('research-interest')" id="btn-research-interest" class="btn">
        <i class="ph ph-flask" aria-hidden="true"></i>
        <?= lang('common.research') ?>
    </a>
    <a onclick="navigate('settings')" id="btn-settings" class="btn">
        <i class="ph ph-trash" aria-hidden="true"></i>
        <?= lang('action.delete') ?>
    </a>

</nav>

<form action="<?= $formaction ?>" method="post" id="group-form">
    <input type="hidden" class="hidden" name="redirect" value="<?= $url ?>">


    <section id="general">

        <h3 class=""><?= lang('groups.name_and_description') ?></h3>
        <div class="row row-eq-spacing mb-0">
            <div class="col-md-6">
                <fieldset>
                    <legend class="d-flex"><?= lang('common.english') ?> <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></legend>
                    <div class="form-group">
                        <label for="name" class="required">
                            <?= lang('forms.full_name') ?> (EN)
                        </label>
                        <input type="text" class="form-control large" name="values[name]" id="name" required value="<?= val('name') ?>">
                    </div>

                    <div class="form-group">
                        <label for="description"><?= lang('common.description') ?> (EN)</label>

                        <div id="description-quill"><?= $form['description'] ?? '' ?></div>
                        <textarea name="values[description]" id="description" class="d-none" readonly><?= $form['description'] ?? '' ?></textarea>
                        <script>
                            quillEditor('description');
                        </script>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-6">
                <fieldset>
                    <legend class="d-flex"><?= lang('common.german') ?> <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></legend>
                    <div class="form-group">
                        <label for="name_de" class="required">
                            <?= lang('forms.full_name') ?> (DE)
                        </label>
                        <input type="text" class="form-control large" name="values[name_de]" id="name_de" required value="<?= val('name_de') ?>">
                    </div>
                    <div class="form-group">
                        <label for="description_de"><?= lang('common.description') ?> (DE)</label>

                        <div id="description_de-quill"><?= $form['description_de'] ?? '' ?></div>
                        <textarea name="values[description_de]" id="description_de" class="d-none" readonly><?= $form['description_de'] ?? '' ?></textarea>
                        <script>
                            quillEditor('description_de');
                        </script>
                    </div>
                </fieldset>
            </div>
        </div>


        <h3 class="mt-0"><?= lang('common.general') ?></h3>
        <fieldset>
            <?php if ($Settings->featureEnabled('portal') && $level != 0) { ?>
                <h5 class="mt-0">
                    <?= lang('common.visibility_on_website') ?>
                </h5>

                <div class="form-group">
                    <input type="hidden" name="values[hide]" value="0">
                    <div class="custom-switch">
                        <input type="checkbox" id="hide-check" <?= val('hide') ? 'checked' : '' ?> name="values[hide]" value="1">
                        <label for="hide-check">
                            <?= lang('groups.hide_group_from_public_view_edit') ?>
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
                        <?= lang('groups.mark_group_as_inactive') ?>
                    </label>
                </div>
            </div>

            <div class="row row-eq-spacing mt-0">
                <div class="col-md-2">
                    <label for="id" class="required">
                        <?= lang('common.acronym') ?>
                    </label>
                    <input type="text" class="form-control" name="values[id]" id="id" required value="<?= val('id') ?>" maxlength="9">
                </div>

                <div class="col-sm-5">
                    <label for="parent">
                        <?= lang('common.parent_group') ?>
                    </label>
                    <select class="form-control" name="values[parent]" id="parent" onchange="deptSelect(this.value)">
                        <option value="" data-level="99"><?= lang('common.attention_no_parent_group_chosen') ?></option>
                        <?php foreach ($Groups->groups as $d => $dept) { ?>
                            <option value="<?= $d ?>" <?= sel('parent', $d) ?> data-level="<?= $dept['level'] ?? $Groups->getLevel($d) ?>">
                                <?= $dept['name'] != $d ? "$d: " : '' ?><?= $dept['name'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>


                <div class="col-sm-5">
                    <label for="unit" class="required">
                        <?= lang('common.type_of_group') ?>
                    </label>
                    <input type="text" class="form-control" name="values[unit]" id="unit" required value="<?= val('unit') ?>" placeholder="<?= lang('common.double_click_to_see_suggestions') ?>" list="unit-list">
                </div>

            </div>
            <div class="form-group" id="color-row" <?= $level != 1 ? 'style="display:none;"' : '' ?>>
                <label for="color" class=""><?= lang('common.color') ?></label>
                <input type="color" class="form-control w-50" name="values[color]" required value="<?= val('color') ?>">
                <span><?= lang('common.note_that_only_level_1_groups_can_have_a_color') ?></span>
            </div>

            <!-- synonyms -->
            <div class="form-group">
                <label for="synonyms">
                    <?= lang('common.synonyms') ?>
                </label>
                <?php
                $synonyms = DB::doc2Arr($form['synonyms'] ?? []);
                ?>

                <input type="text" class="form-control" name="values[synonyms]" id="synonyms" value="<?= e(is_array($synonyms) ? implode('; ', $synonyms) : $synonyms) ?>">
                <small class="text-muted"><?= lang('groups.separate_multiple_synonyms_with_semi_colons') ?></small>
            </div>

            <!-- cost center -->
            <div class="form-group">
                <label for="costcenter">
                    <?= lang('groups.cost_center') ?>
                </label>
                <input type="text" class="form-control" name="values[costcenter]" id="costcenter" value="<?= val('costcenter') ?>">
            </div>


            <?php if ($Settings->featureEnabled('topics')) { ?>
                <!-- if topics are registered, you can choose them here -->
                <?php $Settings->topicChooser($form['topics'] ?? []) ?>
            <?php } ?>

        </fieldset>

        <button class="btn secondary" type="submit" id="submit-btn">
            <i class="ph ph-check"></i> <?= lang('action.save') ?>
        </button>

    </section>


    <section id="research-interest" style="display:none;">

        <h3><?= lang('common.research_interests') ?></h3>

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
                            <h4 class="m-0"><q><?= e($con['title'] ?? lang('common.research_interest')) ?></q></h4>
                            <div class="btn-group ml-auto">
                                <button class="btn" type="button" onclick="moveResearchrow(this, 'up')"><i class="ph ph-arrow-up"></i></button>
                                <button class="btn" type="button" onclick="moveResearchrow(this, 'down')"><i class="ph ph-arrow-down"></i></button>
                                <button class="btn danger" type="button" onclick="$(this).closest('.box').remove()"><i class="ph ph-trash"></i></button>
                            </div>
                        </div>

                        <div class="content">
                            <div class="row row-eq-spacing">
                                <div class="col-md-6">
                                    <h5 class="mt-0 ">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                                    <div class="form-group floating-form">
                                        <input name="values[research][<?= $i ?>][title]" type="text" class="form-control large" value="<?= e($con['title'] ?? '') ?>" placeholder="Title" required>
                                        <label for="values[research][<?= $i ?>][title]" class="required"><?= lang('common.title') ?></label>
                                    </div>
                                    <div class="form-group floating-form">
                                        <input name="values[research][<?= $i ?>][subtitle]" type="text" class="form-control" value="<?= e($con['subtitle'] ?? '') ?>" placeholder="Subtitle">
                                        <label for="values[research][<?= $i ?>][subtitle]"><?= lang('common.subtitle') ?></label>
                                    </div>
                                    <div class="form-group mb-0">
                                        <div id="info-<?= $i ?>-quill"><?= $con['info'] ?? '' ?></div>
                                        <textarea name="values[research][<?= $i ?>][info]" id="info-<?= $i ?>" class="d-none" readonly><?= $con['info'] ?? '' ?></textarea>
                                        <script>
                                            quillEditor('info-<?= $i ?>');
                                        </script>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <h5 class="mt-0 ">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                                    <div class="form-group floating-form">
                                        <input name="values[research][<?= $i ?>][title_de]" type="text" class="form-control large" value="<?= e($con['title_de'] ?? '') ?>" placeholder="Title">
                                        <label for="values[research][<?= $i ?>][title_de]"><?= lang('common.title') ?></label>
                                    </div>
                                    <div class="form-group floating-form">
                                        <input name="values[research][<?= $i ?>][subtitle_de]" type="text" class="form-control" value="<?= e($con['subtitle_de'] ?? '') ?>" placeholder="Subtitle">
                                        <label for="values[research][<?= $i ?>][subtitle_de]"><?= lang('common.subtitle') ?></label>
                                    </div>
                                    <div class="form-group mb-0">
                                        <div id="info_de-<?= $i ?>-quill"><?= $con['info_de'] ?? '' ?></div>
                                        <textarea name="values[research][<?= $i ?>][info_de]" id="info_de-<?= $i ?>" class="d-none" readonly><?= $con['info_de'] ?? '' ?></textarea>
                                        <script>
                                            quillEditor('info_de-<?= $i ?>');
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div id="activities-<?= $i ?>" class="content">
                            <h5><?= lang('common.connected_activities') ?></h5>

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
                                <input type="text" class="form-control" placeholder="Search for Activity" onkeypress="if(event.key === 'Enter') { searchActivities('<?= $i ?>'); event.preventDefault(); }">
                                <div class="input-group-append">
                                    <button class="btn secondary" type="button" onclick="searchActivities('<?= $i ?>')"><?= lang('action.search') ?></button>
                                </div>
                            </div>

                            <div class="suggestions" style="display:none;"></div>

                        </div>
                    </div>
            <?php }
            } ?>

        </div>
        <button class="btn" type="button" onclick="addResearchrow(event, '#research-list')">
            <i class="ph ph-plus text-success"></i> <?= lang('groups.add_research_interest') ?>
        </button>
        <br>

        <h3>
            <?= lang('groups.research_field_classification') ?>
        </h3>
        <?php
        include_once BASEPATH . "/components/kdsf-ffk-select.php";
        ?>

        <button class="btn secondary" type="submit" id="submit-btn">
            <i class="ph ph-check"></i> <?= lang('action.save') ?>
        </button>


    </section>


    <section id="personnel" style="display:none;">

        <h3><?= lang('common.staff') ?></h3>
        <h5>
            <?= lang('common.head_s') ?>
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
                            $name .= ' <small class="text-danger">(' . lang('groups.inactive') . ')</small>';
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
                            <option value="" disabled selected><?= lang('common.add_head') ?></option>
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
            <i class="ph ph-check"></i> <?= lang('action.save') ?>
        </button>

    </section>

</form>

<section id="settings" style="display:none;">

    <div class="alert danger mt-20">
        <form action="<?= ROOTPATH ?>/crud/groups/delete/<?= $group['_id'] ?>" method="post">
            <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/groups">
            <button class="btn danger"><i class="ph ph-trash"></i> <?= lang('action.delete') ?></button>
            <span class="ml-20"><?= lang('common.warning_cannot_be_undone') ?></span>
        </form>
    </div>

</section>

<section id="personnel-2" style="display:none;">
    <h5>
        <?= lang('groups.directly_associated_persons') ?>
    </h5>
    <p>
        <?= lang('groups.these_persons_are_directly_associated_with_this_group_persons_who_belong_to') ?>
    </p>

    <a class="btn primary" href="#add-person-modal">
        <i class="ph ph-user-plus ph-fw"></i>
        <?= lang('common.add_person') ?>
    </a>

    <table class="table mt-20">
        <thead>
            <tr>
                <th><?= lang('common.name') ?></th>
                <th><?= lang('common.position') ?></th>
                <th><?= lang('groups.since') ?></th>
                <th><?= lang('common.actions') ?></th>
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
                            <i class="ph ph-crown-simple text-secondary"></i>
                        <?php } elseif ($unit['editor'] ?? false) { ?>
                            <i class="ph ph-clipboard-text text-primary"></i>
                        <?php } ?>

                        <?= $p['position'] ?? '' ?>
                    </td>
                    <td>
                        <?php if ($unit['start'] ?? false) { ?>
                            <?= date('d.m.Y', strtotime($unit['start'])) ?>
                        <?php } else { ?>
                            <em class="text-muted"><?= lang('groups.undefined') ?></em>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="<?= ROOTPATH ?>/profile/<?= $p['username'] ?>" class="btn small">
                            <i class="ph ph-eye"></i> <?= lang('groups.view') ?>
                        </a>
                        <form action="<?= ROOTPATH ?>/crud/groups/removeperson/<?= $id ?>" method="post" class="d-inline">
                            <input type="hidden" name="username" value="<?= $p['username'] ?>">
                            <button class="btn danger small"><i class="ph ph-trash"></i> <?= lang('action.remove') ?></button>
                        </form>
                        <!-- delegate editing rights -->
                        <form action="<?= ROOTPATH ?>/crud/groups/editorperson/<?= $id ?>" method="post" class="d-inline">
                            <input type="hidden" name="username" value="<?= $p['username'] ?>">
                            <?php if ($has_editor_rights) { ?>

                                <button class="btn muted small" disabled><i class="ph ph-shield-check"></i> <?= lang('groups.has_admin_rights') ?></button>
                            <?php } elseif ($unit['editor'] ?? false) { ?>
                                <input type="hidden" name="action" value="remove">
                                <button class="btn secondary small"><i class="ph ph-minus"></i> <?= lang('groups.editor_rights') ?></button>
                            <?php } else { ?>
                                <input type="hidden" name="action" value="add">
                                <button class="btn primary small"><i class="ph ph-plus"></i> <?= lang('groups.editor_rights') ?>*</button>
                            <?php } ?>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <small class="text-muted">
        * <?= lang('groups.persons_with_editor_rights_can_edit_the_group_details_and_add_remove_other') ?>
    </small>

</section>




<datalist id="unit-list">
    <?php
    $units = $osiris->groups->distinct('unit');
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
            <h2><?= lang('common.add_person') ?></h2>
            <form action="<?= ROOTPATH ?>/crud/groups/addperson/<?= $id ?>" method="post">

                <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/groups/edit/<?= $id ?>#section-personnel">

                <div class="form-group">
                    <label for="person-username"><?= lang('common.person') ?></label>
                    <!-- select for distinct user names from DB -->
                    <select name="username" id="person-username" class="form-control" required>
                        <option value="" disabled selected><?= lang('groups.select_person') ?></option>
                        <?php foreach ($osiris->persons->find(['is_active' => ['$ne' => false], 'units.unit' => ['$ne' => $id]], ['sort' => ['last' => 1]]) as $person) { ?>
                            <option value="<?= $person['username'] ?>"><?= $person['last'] . ', ' . $person['first'] ?></option>
                        <?php } ?>
                    </select>
                    <script>
                        $("#person-username").selectize();
                    </script>
                </div>

                <div class="form-group">
                    <label for="start"><?= lang('common.start_date') ?></label>
                    <input type="date" name="start" id="person-start" class="form-control">
                </div>

                <div class="form-group">
                    <label for="scientific"><?= lang('common.scientific') ?></label>
                    <select class="form-control" id="scientific" name="scientific">
                        <option value="1"><?= lang('common.yes') ?></option>
                        <option value="0"><?= lang('common.no') ?></option>
                    </select>
                </div>

                <div id="person-affiliated" style="display: none;">
                    <p>
                        <?= lang('groups.this_person_is_currently_affiliated_with_the_following_units_without_end_da') ?>
                    </p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= lang('common.unit') ?></th>
                                <th><?= lang('groups.since') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <p>
                        <?= lang('groups.should_the_existing_unit_be_retained_or_terminated') ?>
                    </p>

                    <div class="form-group">
                        <div class="custom-radio">
                            <input type="radio" name="change-or-add" id="person-add" value="add" checked="checked">
                            <label for="person-add">
                                <b><?= lang('action.add') ?>:</b>
                                <?= lang('groups.add_this_unit_as_additional_unit_and_keep_other_units_unchanged') ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-radio">
                            <input type="radio" name="change-or-add" id="person-change" value="change">
                            <label for="person-change">
                                <b><?= lang('groups.change') ?>:</b>
                                <?= lang('groups.terminate_existing_units_and_add_this_unit_as_new') ?>
                            </label>
                            <br>
                            <small class="text-danger" id="person-change-warning">
                                <?= lang('groups.only_possible_when_a_starting_date_is_set') ?>
                            </small>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn"><?= lang('action.add') ?></button>
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