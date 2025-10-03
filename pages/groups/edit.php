<?php

/**
 * Page to add new groups
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /groups/new
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
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
$btntext = '<i class="ph ph-check"></i> ' . lang("Update", "Aktualisieren");
$url = ROOTPATH . "/groups/edit/" . $form['_id'];
$title = lang('Edit group: ', 'Gruppe bearbeiten: ') . $id;

$level = $Groups->getLevel($id);

function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return htmlspecialchars($val);
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
<script src="<?= ROOTPATH ?>/js/groups-editor.js?v=<?= CSS_JS_VERSION ?>"></script>


<h1 class="title">
    <?= $title ?>
</h1>

<?php if ($form['inactive'] ?? false) { ?>
    <div>
        <span class="badge danger">
            <?= lang('inactive', 'inaktiv') ?>
        </span>
    </div>
<?php } ?>


<nav class="pills mt-20 mb-0">
    <a onclick="navigate('general')" id="btn-general" class="btn active">
        <i class="ph ph-gear" aria-hidden="true"></i>
        <?= lang('General', 'Allgemein') ?>
    </a>
    <a onclick="navigate('personnel')" id="btn-personnel" class="btn">
        <i class="ph ph-users" aria-hidden="true"></i>
        <?= lang('Personnel', 'Personal') ?>
    </a>

    <a onclick="navigate('research-interest')" id="btn-research-interest" class="btn">
        <i class="ph ph-flask" aria-hidden="true"></i>
        <?= lang('Research', 'Forschung') ?>
    </a>
    <a onclick="navigate('settings')" id="btn-settings" class="btn">
        <i class="ph ph-trash" aria-hidden="true"></i>
        <?= lang('Delete', 'Löschen') ?>
    </a>

</nav>

<form action="<?= $formaction ?>" method="post" id="group-form">
    <input type="hidden" class="hidden" name="redirect" value="<?= $url ?>">


    <section id="general">

        <h3 class=""><?= lang('Name and description', 'Name und Beschreibung') ?></h3>
        <div class="row row-eq-spacing mb-0">
            <div class="col-md-6">
                <fieldset>
                    <legend class="d-flex"><?= lang('English', 'Englisch') ?> <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></legend>
                    <div class="form-group">
                        <label for="name" class="required">
                            <?= lang('Full Name', 'Voller Name') ?> (EN)
                        </label>
                        <input type="text" class="form-control large" name="values[name]" id="name" required value="<?= val('name') ?>">
                    </div>

                    <div class="form-group">
                        <label for="description"><?= lang('Description', 'Beschreibung') ?> (EN)</label>

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
                    <legend class="d-flex"><?= lang('German', 'Deutsch') ?> <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></legend>
                    <div class="form-group">
                        <label for="name_de" class="required">
                            <?= lang('Full Name', 'Voller Name') ?> (DE)
                        </label>
                        <input type="text" class="form-control large" name="values[name_de]" id="name_de" required value="<?= val('name_de') ?>">
                    </div>
                    <div class="form-group">
                        <label for="description_de"><?= lang('Description', 'Beschreibung') ?> (DE)</label>

                        <div id="description_de-quill"><?= $form['description_de'] ?? '' ?></div>
                        <textarea name="values[description_de]" id="description_de" class="d-none" readonly><?= $form['description_de'] ?? '' ?></textarea>
                        <script>
                            quillEditor('description_de');
                        </script>
                    </div>
                </fieldset>
            </div>
        </div>


        <h3 class="mt-0"><?= lang('General', 'Allgemein') ?></h3>
        <fieldset>
            <?php if ($Settings->featureEnabled('portal') && $level != 0) { ?>
                <h5 class="mt-0">
                    <?= lang('Visibility on Website', 'Darstellung auf der Webseite') ?>
                </h5>

                <div class="form-group">
                    <input type="hidden" name="values[hide]" value="0">
                    <div class="custom-switch">
                        <input type="checkbox" id="hide-check" <?= val('hide') ? 'checked' : '' ?> name="values[hide]" value="1">
                        <label for="hide-check">
                            <?= lang('Hide group from public view', 'Gruppe <b>nicht</b> öffentlich anzeigen') ?>
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
                        <?= lang('Mark group as inactive', 'Gruppe als inaktiv markieren') ?>
                    </label>
                </div>
            </div>

            <div class="row row-eq-spacing mt-0">
                <div class="col-md-2">
                    <label for="id" class="required">
                        <?= lang('Acronym', 'Abkürzung') ?>
                    </label>
                    <input type="text" class="form-control" name="values[id]" id="id" required value="<?= val('id') ?>" maxlength="8">
                </div>

                <div class="col-sm-5">
                    <label for="parent">
                        <?= lang('Parent group', 'Übergeordnete Gruppe') ?>
                    </label>
                    <select class="form-control" name="values[parent]" id="parent" onchange="deptSelect(this.value)">
                        <option value="" data-level="99"><?= lang('!!!Attention: No parent group chosen', '!!! Achtung: Keine übergeordnete Gruppe gewählt') ?></option>
                        <?php foreach ($Groups->groups as $d => $dept) { ?>
                            <option value="<?= $d ?>" <?= sel('parent', $d) ?> data-level="<?= $dept['level'] ?? $Groups->getLevel($d) ?>">
                                <?= $dept['name'] != $d ? "$d: " : '' ?><?= $dept['name'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>


                <div class="col-sm-5">
                    <label for="unit" class="required">
                        <?= lang('Type of group', 'Art der Gruppe') ?>
                    </label>
                    <input type="text" class="form-control" name="values[unit]" id="unit" required value="<?= val('unit') ?>" placeholder="<?= lang('Double click to see suggestions', 'Doppelklick für Vorschläge') ?>" list="unit-list">
                </div>

            </div>
            <div class="form-group" id="color-row" <?= $level != 1 ? 'style="display:none;"' : '' ?>>
                <label for="color" class=""><?= lang('Color', 'Farbe') ?></label>
                <input type="color" class="form-control w-50" name="values[color]" required value="<?= val('color') ?>">
                <span><?= lang('Note that only level 1 groups can have a color.', 'Bitte beachte, dass nur Level 1-Gruppen eine eigene Farbe haben können.') ?></span>
            </div>

            <!-- cost center -->
            <div class="form-group">
                <label for="costcenter">
                    <?= lang('Cost Center', 'Kostenstelle') ?>
                </label>
                <input type="text" class="form-control" name="values[costcenter]" id="costcenter" value="<?= val('costcenter') ?>">
            </div>


            <?php if ($Settings->featureEnabled('topics')) { ?>
                <!-- if topics are registered, you can choose them here -->
                <?php $Settings->topicChooser($form['topics'] ?? []) ?>
            <?php } ?>

        </fieldset>

        <button class="btn secondary" type="submit" id="submit-btn">
            <i class="ph ph-check"></i> <?= lang("Save", "Speichern") ?>
        </button>

    </section>


    <section id="research-interest" style="display:none;">

        <h3><?= lang('Research interest', 'Forschungsinteressen') ?></h3>

        <!-- ensure empty list gets still submitted -->
        <input type="hidden" name="values[research]" value="">
        <div id="research-list">
            <?php
            if (isset($form['research']) && !empty($form['research'])) {

                foreach ($form['research'] as $i => $con) { ?>

                    <div class="box padded">

                        <div class="row row-eq-spacing my-0">
                            <div class="col-md-6">
                                <h5 class="mt-0 ">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                                <div class="form-group floating-form">
                                    <input name="values[research][<?= $i ?>][title]" type="text" class="form-control large" value="<?= htmlspecialchars($con['title'] ?? '') ?>" placeholder="Title" required>
                                    <label for="values[research][<?= $i ?>][title]" class="required"><?= lang('Title', 'Titel') ?></label>
                                </div>
                                <div class="form-group floating-form">
                                    <input name="values[research][<?= $i ?>][subtitle]" type="text" class="form-control" value="<?= htmlspecialchars($con['subtitle'] ?? '') ?>" placeholder="Subtitle">
                                    <label for="values[research][<?= $i ?>][subtitle]"><?= lang('Subtitle', 'Untertitel') ?></label>
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
                                    <input name="values[research][<?= $i ?>][title_de]" type="text" class="form-control large" value="<?= htmlspecialchars($con['title_de'] ?? '') ?>" placeholder="Title">
                                    <label for="values[research][<?= $i ?>][title_de]"><?= lang('Title', 'Titel') ?></label>
                                </div>
                                <div class="form-group floating-form">
                                    <input name="values[research][<?= $i ?>][subtitle_de]" type="text" class="form-control" value="<?= htmlspecialchars($con['subtitle_de'] ?? '') ?>" placeholder="Subtitle">
                                    <label for="values[research][<?= $i ?>][subtitle_de]"><?= lang('Subtitle', 'Untertitel') ?></label>
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

                        <div id="activities-<?= $i ?>">
                            <h5><?= lang('Connected activities', 'Verknüpfte Aktivitäten') ?></h5>

                            <ul>
                                <?php foreach ($con['activities'] ?? [] as $res) {
                                    $doc = $DB->getActivity($res);
                                ?>
                                    <li>
                                        <?= $doc['rendered']['icon'] ?>
                                        <?= $doc['rendered']['plain'] ?>
                                        <input type="hidden" name="values[research][<?= $i ?>][activities][]" value="<?= $res ?>">
                                        <button class="btn link text-danger small" type="button" onclick="$(this).closest('li').remove()"><i class="ph ph-trash"></i></button>
                                    </li>
                                <?php } ?>

                            </ul>

                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search for Activity">
                                <div class="input-group-append">
                                    <button class="btn secondary" type="button" onclick="searchActivities('<?= $i ?>')"><?= lang('Search', 'Suchen') ?></button>
                                </div>
                            </div>

                            <div class="suggestions" style="display:none;"></div>

                        </div>

                        <button class="btn danger small my-10" type="button" onclick="$(this).closest('.box').remove()"><i class="ph ph-trash"></i> <?= lang('Delete', 'Löschen') ?></button>
                    </div>
            <?php }
            } ?>

        </div>
        <button class="btn" type="button" onclick="addResearchrow(event, '#research-list')"><i class="ph ph-plus text-success"></i> <?= lang('Add entry', 'Eintrag hinzufügen') ?></button>
        <br>
        <br>
        <button class="btn secondary" type="submit" id="submit-btn">
            <i class="ph ph-check"></i> <?= lang("Save", "Speichern") ?>
        </button>

    </section>


    <section id="personnel" style="display:none;">

        <h3><?= lang('Staff', 'Personal') ?></h3>
        <h5>
            <?= lang('Head(s)', 'Leitende Person(en)') ?>
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
                            $name .= ' <small class="text-danger">(' . lang('inactive', 'inaktiv') . ')</small>';
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
                            <option value="" disabled selected><?= lang('Add head ...', 'Füge leitende Person hinzu ...') ?></option>
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
            <i class="ph ph-check"></i> <?= lang("Save", "Speichern") ?>
        </button>

    </section>

</form>

<section id="settings" style="display:none;">

    <div class="alert danger mt-20">
        <form action="<?= ROOTPATH ?>/crud/groups/delete/<?= $group['_id'] ?>" method="post">
            <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/groups">
            <button class="btn danger"><i class="ph ph-trash"></i> <?= lang('Delete', 'Löschen') ?></button>
            <span class="ml-20"><?= lang('Warning! Cannot be undone.', 'Warnung, kann nicht rückgängig gemacht werden!') ?></span>
        </form>
    </div>

</section>

<section id="personnel-2" style="display:none;">
    <h5>
        <?= lang('Directly associated persons', 'Direkt zugeordnete Personen') ?>
    </h5>
    <p>
        <?= lang('These persons are directly associated with this group. Persons who belong to a sub-unit are shown in the overview, but are not visible here.', 'Diese Personen sind direkt mit dieser Gruppe verbunden. Personen, die einer Untereinheit angehören, werden in der Übersicht gezeigt, sind hier aber nicht zu sehen.') ?>
    </p>

    <a class="btn primary" href="#add-person-modal">
        <i class="ph ph-user-plus ph-fw"></i>
        <?= lang('Add person', 'Person hinzufügen') ?>
    </a>

    <table class="table mt-20">
        <thead>
            <tr>
                <th><?= lang('Name', 'Name') ?></th>
                <th><?= lang('Position', 'Position') ?></th>
                <th><?= lang('Since', 'seit') ?></th>
                <th><?= lang('Actions', 'Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
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
                            <em class="text-muted"><?= lang('undefined', 'undefiniert') ?></em>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="<?= ROOTPATH ?>/profile/<?= $p['username'] ?>" class="btn small">
                            <i class="ph ph-eye"></i> <?= lang('View', 'Ansehen') ?>
                        </a>
                        <form action="<?= ROOTPATH ?>/crud/groups/removeperson/<?= $id ?>" method="post" class="d-inline">
                            <input type="hidden" name="username" value="<?= $p['username'] ?>">
                            <button class="btn danger small"><i class="ph ph-trash"></i> <?= lang('Remove', 'Entfernen') ?></button>
                        </form>
                        <!-- delegate editing rights -->
                        <form action="<?= ROOTPATH ?>/crud/groups/editorperson/<?= $id ?>" method="post" class="d-inline">
                            <input type="hidden" name="username" value="<?= $p['username'] ?>">
                            <?php if ($unit['editor'] ?? false) { ?>
                                <input type="hidden" name="action" value="remove">
                                <button class="btn secondary small"><i class="ph ph-minus"></i> <?= lang('Editor rights', 'Editor-Rechte') ?></button>
                            <?php } else { ?>
                                <input type="hidden" name="action" value="add">
                                <button class="btn primary small"><i class="ph ph-plus"></i> <?= lang('Editor rights', 'Editor-Rechte') ?>*</button>
                            <?php } ?>
                        </form>

                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <small class="text-muted">
        * <?= lang('Persons with editor rights can edit the group details, and add/remove other persons.', 'Personen mit Editor-Rechten können die Gruppendetails bearbeiten und andere Personen hinzufügen/entfernen.') ?>
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
    var i = <?= $i ?? 0 ?>
    var CURRENTYEAR = <?= CURRENTYEAR ?>;
    // toggleVisibility();
</script>



<!-- modal to add person -->
<div id="add-person-modal" class="modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><?= lang('Add person', 'Person hinzufügen') ?></h2>
            <form action="<?= ROOTPATH ?>/crud/groups/addperson/<?= $id ?>" method="post">

                <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/groups/edit/<?= $id ?>#section-personnel">

                <div class="form-group">
                    <label for="person-username"><?= lang('Person', 'Person') ?></label>
                    <!-- select for distinct user names from DB -->
                    <select name="username" id="person-username" class="form-control" required>
                        <option value="" disabled selected><?= lang('Select person', 'Person auswählen') ?></option>
                        <?php foreach ($osiris->persons->find(['is_active' => ['$ne' => false], 'units.unit' => ['$ne' => $id]], ['sort' => ['last' => 1]]) as $person) { ?>
                            <option value="<?= $person['username'] ?>"><?= $person['last'] . ', ' . $person['first'] ?></option>
                        <?php } ?>
                    </select>
                    <script>
                        $("#person-username").selectize();
                    </script>
                </div>

                <div class="form-group">
                    <label for="start"><?= lang('Start date', 'Anfangsdatum') ?></label>
                    <input type="date" name="start" id="person-start" class="form-control">
                </div>

                <div class="form-group">
                    <label for="scientific"><?= lang('Scientific', 'Wissenschaftlich') ?></label>
                    <select class="form-control" id="scientific" name="scientific">
                        <option value="1"><?= lang('yes', 'ja') ?></option>
                        <option value="0"><?= lang('no', 'nein') ?></option>
                    </select>
                </div>

                <div id="person-affiliated" style="display: none;">
                    <p>
                        <?= lang('This person is currently affiliated with the following units without end date:', 'Diese Person ist zurzeit zu folgenden Einheiten zugehörig (bei denen kein Enddatum angegeben ist):') ?>
                    </p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= lang('Unit', 'Einheit') ?></th>
                                <th><?= lang('Since', 'seit') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <p>
                        <?= lang('Should the existing unit be retained or terminated?', 'Soll die vorhandene Einheit beibehalten oder beendet werden?') ?>
                    </p>

                    <div class="form-group">
                        <div class="custom-radio">
                            <input type="radio" name="change-or-add" id="person-add" value="add" checked="checked">
                            <label for="person-add">
                                <b><?= lang('Add', 'Hinzufügen') ?>:</b>
                                <?= lang('Add this unit as additional unit and keep other units unchanged.', 'Füge diese Einheit der Person zusätzlich hinzu und behalte die vorhandenen Einheiten ungeändert.') ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-radio">
                            <input type="radio" name="change-or-add" id="person-change" value="change">
                            <label for="person-change">
                                <b><?= lang('Change', 'Wechseln') ?>:</b>
                                <?= lang('Terminate existing units and add this unit as new.', 'Beende bestehende Einheiten und füge diese Einheit als neue hinzu.') ?>
                            </label>
                            <br>
                            <small class="text-danger" id="person-change-warning">
                                <?= lang('Only possible when a starting date is set.', 'Nur möglich, wenn ein Anfangsdatum ausgewählt ist.') ?>
                            </small>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn"><?= lang('Add', 'Hinzufügen') ?></button>
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