<?php

/**
 * Page to add or edit category of activity types
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/types/new
 * @link        /admin/types/<type_id>
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

include_once BASEPATH . '/php/Modules.php';
$Modules = new Modules();

$color = $color ?? '#000000';
$formaction = ROOTPATH;
if (!empty($form) && isset($form['_id'])) {
    $id = $form['id'];
    $formaction .= "/crud/types/update/" . $form['_id'];
    $btntext = '<i class="ph ph-check"></i> ' . lang("Update", "Aktualisieren");
    $url = ROOTPATH . "/admin/types/" . $form['id'];
    $title = $name;
    $new = false;

    $member = $osiris->activities->count(['subtype' => $id]);
} else {
    $new = true;
    $formaction .= "/crud/types/create";
    $btntext = '<i class="ph ph-check"></i> ' . lang("Save", "Speichern");
    $url = ROOTPATH . "/admin/types/*";
    $title = lang('New category', 'Neue Kategorie');
    $member = 0;

    // check if type is the first in the category
    if (isset($_GET['parent'])) {
        $p = $type['parent'];
        $first = $osiris->adminTypes->count(['parent' => $p]) == 0;

        if ($first) {
            $parent = $osiris->adminCategories->findOne(['id' => $p]);
            $type['icon'] = $parent['icon'];
            $type['name'] = $parent['name'];
            $type['name_de'] = $parent['name_de'];
            $type['id'] = $parent['id'];
        }
    }
}

?>

<style>
    #data-fields .badge {
        border: var(--border-width) solid var(--text-color);
        margin: .25rem;
    }

    #data-fields .badge i {
        color: var(--primary-color);
        margin: 0;
    }
</style>

<div class="modal" id="unique" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('ID must be unique', 'Die ID muss einzigartig sein.') ?></h5>
            <p>
                <?= lang('Each category and each activity type must have a unique ID with which it is linked to an activity.', 'Jede Kategorie und jeder Aktivitätstyp muss eine einzigartige ID haben, mit der er zu einer Aktivität verknüpft wird.') ?>
            </p>
            <p>
                <?= lang('As the ID must be unique, the following previously used IDs and keywords (new) cannot be used as IDs:', 'Da die ID einzigartig sein muss, können folgende bereits verwendete IDs und Schlüsselwörter (new) nicht als ID verwendet werden:') ?>
            </p>
            <ul class="list" id="IDLIST">
                <?php foreach ($osiris->adminTypes->distinct('id') as $k) { ?>
                    <li><?= $k ?></li>
                <?php } ?>
                <li>new</li>
            </ul>
            <div class="text-right mt-20">
                <a href="#/" class="btn secondary" role="button"><?= lang('I understand', 'Ich verstehe') ?></a>
            </div>
        </div>
    </div>
</div>



<form action="<?= $formaction ?>" method="post" id="group-form">
    <input type="hidden" class="hidden" name="redirect" value="<?= $url ?>">

    <div class="box subtype" style="border-color:<?= $color ?>;">
        <h4 class="header" style="background-color:<?= $color ?>20; color:<?= $color ?>">
            <?php if (!isset($type['new'])) { ?>
                <i class="ph ph-<?= $type['icon'] ?? 'folder-open' ?> mr-10"></i>
                <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
                <?php if ($type['disabled'] ?? false) { ?>
                    <span class="badge danger ml-20">DISABLED</span>
                <?php } ?>

            <?php } else { ?>
                <?= lang('New type of activity', 'Neuer Typ von Aktivität') ?>
            <?php } ?>
        </h4>


        <div class="content">

            <?php if (isset($type['parent'])) { ?>
                <input type="hidden" name="original_parent" value="<?= $type['parent'] ?>">
            <?php } ?>

            <label for="parent" class="required"><?= lang('Category', 'Übergeordnete Kategorie') ?></label>
            <select name="values[parent]" id="parent" class="form-control" required>
                <?php foreach ($osiris->adminCategories->find() as $cat) { ?>
                    <option value="<?= $cat['id'] ?>" <?= $type['parent'] == $cat['id'] ? 'selected' : '' ?>><?= lang($cat['name'], $cat['name_de']) ?></option>
                <?php } ?>
            </select>
        </div>
        <hr>
        <div class="content">

            <div class="row row-eq-spacing">

                <?php if (isset($type['id'])) { ?>
                    <input type="hidden" name="original_id" value="<?= $type['id'] ?>">
                <?php } ?>

                <div class="col-sm-2">
                    <label for="id" class="required">ID</label>
                    <input type="text" class="form-control" name="values[id]" required value="<?= e($type['id']) ?>" data-value="<?= e($type['id']) ?>" oninput="sanitizeID(this)">
                    <small><a href="#unique"><i class="ph ph-info"></i> <?= lang('Must be unqiue', 'Muss einzigartig sein') ?></a></small>
                </div>
                <div class="col-sm-2">
                    <label for="icon" class="required element-time"><a href="https://phosphoricons.com/" class="link" target="_blank" rel="noopener noreferrer">Icon</a> </label>

                    <div class="input-group">
                        <input type="text" class="form-control" name="values[icon]" required value="<?= e($type['icon'] ?? 'folder-open') ?>" onchange="iconTest(this.value)">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="ph ph-<?= e($type['icon'] ?? 'folder-open') ?>" id="test-icon"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm">
                    <label for="name" class="required ">Name (en)</label>
                    <input type="text" class="form-control" name="values[name]" required value="<?= e($type['name'] ?? '') ?>">
                </div>
                <div class="col-sm">
                    <label for="name_de" class="">Name (de)</label>
                    <input type="text" class="form-control" name="values[name_de]" value="<?= e($type['name_de'] ?? '') ?>">
                </div>
            </div>

            <div class="row row-eq-spacing">
                <div class="col-sm">
                    <label for="description"><?= lang('Description', 'Beschreibung') ?> (en)</label>
                    <textarea class="form-control" name="values[description]"><?= e($type['description'] ?? '') ?></textarea>
                </div>
                <div class="col-sm">
                    <label for="description_de" class=""><?= lang('Description', 'Beschreibung') ?> (de)</label>
                    <textarea class="form-control" name="values[description_de]"><?= e($type['description_de'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- <div class="mt-20">
                <input type="hidden" name="values[guests]" value="">
                <div class="custom-checkbox">
                    <input type="checkbox" id="guest-question" value="1" name="values[guests]" <?= ($type['guests'] ?? false) ? 'checked' : '' ?>>
                    <label for="guest-question">
                        <?= lang('Guests should be registered for this activity', 'Gäste sollen zu dieser Aktivität angemeldet werden können?') ?>
                    </label>
                </div>
            </div> -->
            <?php if ($Settings->featureEnabled('portal')) {
                $portfolio = $type['portfolio'] ?? false;
                if (!isset($type['portfolio']) && $type['parent'] == 'publication') {
                    $portfolio = true;
                }
            ?>
                <div class="mt-20">
                    <input type="hidden" name="values[portfolio]" value="false">
                    <div class="custom-checkbox">
                        <input type="checkbox" id="portfolio-question" value="true" name="values[portfolio]" <?= $portfolio ? 'checked' : '' ?>>
                        <label for="portfolio-question">
                            <?= lang('This type of activity should be visible in OSIRIS Portfolio.', 'Diese Art von Aktivität sollte in OSIRIS Portfolio sichtbar sein.') ?>
                        </label>
                    </div>
                </div>
            <?php } ?>
            <?php if ($Settings->featureEnabled('topics')) { ?>
                <div class="mt-20">
                    <input type="hidden" name="values[topics-required]" value="">
                    <div class="custom-checkbox">
                        <input type="checkbox" id="topics-question" value="1" name="values[topics-required]" <?= ($type['topics-required'] ?? false) ? 'checked' : '' ?>>
                        <label for="topics-question">
                            <?= lang('Research Topics are a required field for this activity', 'Forschungsbereiche sind für diese Aktivität ein Pflichtfeld') ?>
                        </label>
                    </div>
                </div>
            <?php } ?>

        </div>
        <hr>

        <div class="content">
            <label for="module" class="font-weight-bold"><?= lang('Data fields', 'Datenfelder') ?>:</label>

            <?php if ($new) { ?>
                <div class="text-signal">
                    <?= lang('Data fields can only be edited after saving the type.', 'Datenfelder können erst nach dem erstmaligen Speichern des Typs bearbeitet werden.') ?>
                </div>
            <?php } else { ?>
                <a href="<?= ROOTPATH ?>/admin/types/<?= $st ?>/fields">
                    <i class="ph ph-edit"></i>
                    <?= lang('Edit', 'Bearbeiten') ?>
                </a>
            <?php } ?>

            <a href="<?= ROOTPATH ?>/admin/module-helper?type=<?= $st ?>" target="_blank" rel="noopener noreferrer" class="ml-10 float-right">
                <?= lang('Field overview', 'Datenfelder-Übersicht') ?> <i class="ph ph-arrow-square-out ml-5"></i>
            </a>

            <div id="data-fields">
                <?php
                $available = [];
                $all_fields = $Modules->all_modules;
                include_once BASEPATH . '/php/Document.php';
                $Format = new Document();
                $templates = $Format->templates;
                $fields_from_templates = [];
                foreach ($templates as $template => $template_fields) {
                    foreach ($template_fields as $f) {
                        if (isset($fields_from_templates[$f])) {
                            $fields_from_templates[$f][] = $template;
                        } else {
                            $fields_from_templates[$f] = [$template];
                        }
                    }
                }
                if (isset($type['fields'])) {
                    foreach ($type['fields'] as $field) {
                        $field_type = $field['type'] ?? 'field';
                        $props = $field['props'] ?? array();
                        $icon = '';
                        $name = '';
                        $tooltip = '';
                        switch ($field_type) {
                            case 'field':
                                $f = $Modules->all_modules[$field['id']] ?? array();
                                $name = lang($f['name'] ?? $field['id'], $f['name_de'] ?? null);
                                $icon = 'ph-database';

                                $tem = $fields_from_templates[$field['id']] ?? array();
                                if (!empty($tem)) {
                                    $available = array_merge($available, $tem);
                                }
                                break;
                            case 'custom':
                                $f = $osiris->adminFields->findOne(['id' => $field['id']]);
                                $name = lang($f['name'] ?? $field['id'], $f['name_de'] ?? null);
                                $icon = 'ph-textbox';
                                break;
                            case 'paragraph':
                                $tooltip = lang($props['text'] ?? 'Paragraph', $props['text_de'] ?? 'Absatz');
                                $icon = 'ph-paragraph';
                                break;
                            case 'hr':
                                $tooltip = lang('Divider', 'Trennlinie');
                                $icon = 'ph-minus';
                                break;
                            case 'heading':
                                $tooltip = lang($props['text'] ?? 'Heading', $props['text_de'] ?? 'Überschrift');
                                $icon = 'ph-text-h';
                                break;
                            default:
                                $name = '';
                                $icon = 'ph-folder-open';
                        }
                        if ($tooltip) {
                            $tooltip = get_preview($tooltip, 30);
                            $tooltip = "data-toggle='tooltip' data-title='$tooltip'";
                        }
                        echo "<span class='badge' $tooltip><i class='ph $icon'></i> $name</span>";
                    }
                } else {
                    foreach ($type['modules'] ?? array() as $module) {
                        $name = trim($module);
                        if (str_ends_with($name, '*') || in_array($name, ['title', 'authors', 'date', 'date-range'])) {
                            $name = str_replace('*', '', $name);
                        }
                        $tem = $fields_from_templates[$name] ?? array();
                        if (!empty($tem)) {
                            $available = array_merge($available, $tem);
                        }

                        $mod = $all_fields[$name] ?? array();
                        if (!empty($mod)) {
                            echo "<span class='badge'><i class='ph ph-database'></i> " . lang($mod['name'], $mod['name_de'] ?? null) . "</span>";
                        } else {
                            echo "<span class='badge'><i class='ph ph-textbox'></i> " . lang($name) . "</span>";
                        }
                    }
                }

                ?>

            </div>

        </div>

        <hr>

        <div class="content">
            <label for="format" class="font-weight-bold">Templates:</label>

            <div class="input-group mb-10">
                <div class="input-group-prepend">
                    <span class="input-group-text w-100">Print</span>
                </div>
                <input type="text" class="form-control" name="values[template][print]" value="<?= e($type['template']['print'] ?? '{title}') ?>">
            </div>

            <div class="input-group mb-10">
                <div class="input-group-prepend">
                    <span class="input-group-text w-100">Web Title</span>
                </div>
                <input type="text" class="form-control" name="values[template][title]" value="<?= e($type['template']['title'] ?? '{title}') ?>">
            </div>

            <div class="input-group mb-10">
                <div class="input-group-prepend">
                    <span class="input-group-text w-100">Web Subtitle</span>
                </div>
                <input type="text" class="form-control" name="values[template][subtitle]" value="<?= e($type['template']['subtitle'] ?? '{authors}') ?>">
            </div>

            <?= lang('How to use templates:', 'Wie man Templates verwendet:') ?>

            <a href="<?= ROOTPATH ?>/admin/templates?type=<?= $st ?>" target="_blank" rel="noopener noreferrer" class="ml-10 link">
                <?= lang('Template builder', 'Template-Baukasten') ?>
            </a>

            <a href="https://wiki.osiris-app.de/admins/content/templates/" target="_blank" rel="noopener noreferrer" class="ml-10 link">
                <?= lang('Documentation', 'Dokumentation') ?>
            </a>
            <style>
                .cheat-sheet {
                    display: none;
                    font-size: 0.9em;
                    background: var(--body-color);
                    padding: 0.5rem 1rem;
                    border-radius: var(--border-radius);
                }
            </style>

            <a onclick="$(this).next().slideToggle();" class="ml-10"><?= lang('Show cheat sheet', 'Zeige die Cheat-Sheet') ?></a>
            <div class="cheat-sheet">
                <strong><?= lang('Available fields:', 'Verfügbare Felder:') ?></strong>
                <ul class="list">
                    <?php
                    $available = array_unique($available);
                    sort($available);
                    foreach ($available as $a) { ?>
                        <li><code>{<?= e($a) ?>}</code></li>
                    <?php } ?>
                </ul>
                <p>
                    <?= lang('Please note that this list is not exhaustive, as some fields (e.g. authors) can be displayed with many different templates.', 'Bitte beachten Sie, dass diese Liste nicht vollständig ist, da einige Felder (z.B. Autoren) mit vielen verschiedenen Templates angezeigt werden können.') ?>
                </p>
            </div>
        </div>


        <hr>


        <div class="content">
            <label for="coins" class="font-weight-bold">Coins:</label>
            <input type="text" class="form-control" name="values[coins]" value="<?= $type['coins'] ?? '0' ?>">
            <span class="text-muted">
                <?= lang('Please note that <q>middle</q> authors will receive half the amount.', 'Bitte beachten Sie, dass <q>middle</q>-Autoren nur die Hälfte der Coins bekommen.') ?>
            </span>
        </div>

        <hr>


        <div class="content">
            <div class="custom-checkbox mb-10 danger">
                <input type="checkbox" id="disable-<?= $t ?>-<?= $st ?>" value="true" name="values[disabled]" <?= ($type['disabled'] ?? false) ? 'checked' : '' ?>>
                <label for="disable-<?= $t ?>-<?= $st ?>"><?= lang('Deactivate', 'Deaktivieren') ?></label>
            </div>
            <span class="text-muted">
                <?= lang('Deactivated types are retained for past activities, but no new ones can be added.', 'Deaktivierte Typen bleiben erhalten für vergangene Aktivitäten, es können aber keine neuen hinzugefügt werden.') ?>
            </span>
        </div>

    </div>
    <button class="btn success" id="submitBtn"><?= $btntext ?></button>
</form>


<?php if (!$new) { ?>


    <?php if ($member == 0) { ?>
        <div class="alert danger mt-20">
            <form action="<?= ROOTPATH ?>/crud/types/delete/<?= $id ?>" method="post">
                <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/categories/<?= $type['parent'] ?>">
                <button class="btn danger"><i class="ph ph-trash"></i> <?= lang('Delete', 'Löschen') ?></button>
                <span class="ml-20"><?= lang('Warning! Cannot be undone.', 'Warnung, kann nicht rückgängig gemacht werden!') ?></span>
            </form>
        </div>
    <?php } else { ?>

        <div class="alert danger mt-20">
            <?= lang("Can't delete type: $member activities associated.", "Kann Typ nicht löschen: $member Aktivitäten zugeordnet.") ?><br>
            <a href='<?= ROOTPATH ?>/activities/search#{"$and":[{"subtype":"<?= $id ?>"}]}' target="_blank" class="text-danger">
                <i class="ph ph-search"></i>
                <?= lang('View activities', 'Aktivitäten zeigen') ?>
            </a>

        </div>
    <?php } ?>


<?php } ?>

<br>
<!-- rerender only this type -->
<a href='<?= ROOTPATH ?>/rerender?subtype=<?= $id ?>' target="_blank" class="text-primary">
    <i class="ph ph-arrow-clockwise"></i>
    <?= lang('Rerender all activities of this type', 'Alle Aktivitäten dieses Typs neu rendern') ?>
</a>

<?php include_once BASEPATH . '/header-editor.php'; ?>
<script src="<?= ROOTPATH ?>/js/admin-categories.js"></script>