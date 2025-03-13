<?php

/**
 * Page for admin dashboard to define activities
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/activities
 *
 * @package OSIRIS
 * @since 1.1.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 * 
 * @deprecated 1.3.0
 */

include_once BASEPATH . "/php/Modules.php";
$Modules = new Modules();

$activities = $Categories->categories;

if (isset($_GET['type']) && isset($_GET['type']['id'])) {
    $type = $_GET['type'];
    $id = $type['id'];
    $activities[$id] =
        [
            "id" => $id,
            "icon" => $type['icon'] ?? 'placeholder',
            "color" => $type['color'] ?? '#000000',
            "name" => $type['name'],
            "name_de" => $type['name_de'] ?? $type['name'],
            "new" => true,
            "children" => [
                $id => [
                    "id" => $id,
                    "icon" => $type['icon'] ?? 'placeholder',
                    "name" => $type['name'],
                    "name_de" => $type['name_de'] ?? $type['name'],
                    "modules" => [
                        "title",
                        "authors",
                        "date"
                    ],
                    "template" => [
                        "print" => "{authors} ({year}) {title}.",
                        "title" => "{title}",
                        "subtitle" => "{authors}, {date}"
                    ],
                    "coins" => 0
                ]
            ]
        ];
}

if (isset($_GET['subtype']) && isset($_GET['subtype']['id'])) {
    $type = $_GET['subtype'];
    $id = $type['id'];
    $activities[$type['type']]['children'][$id] = [
        "id" => $id,
        "icon" => $type['icon'] ?? 'placeholder',
        "name" => $type['name'],
        "name_de" => $type['name_de'] ?? $type['name'],
        "new" => true,
        "modules" => [
            "title",
            "authors",
            "date"
        ],
        "template" => [
            "print" => "{authors} ({year}) {title}.",
            "title" => "{title}",
            "subtitle" => "{authors}, {date}"
        ],
        "coins" => 0

    ];
}
?>
<style>
    form>.box {
        border-left-width: 4px;
    }
</style>

<div class="modal" id="add-type" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title">
                <?= lang('Add category', 'Kategorie hinzufügen') ?>
            </h5>

            <form action="#" method="get">
                <div class="row row-eq-spacing px-0" style="margin: 1rem -1rem;">
                    <div class="col-sm">
                        <label for="id" class="required element-time">ID (<?= lang('Abbr.', 'Abk.') ?>)</label>
                        <input type="text" class="form-control" name="type[id]" required oninput="lowercaseInput(this)">
                    </div>
                    <div class="col-sm">
                        <label for="icon" class="required element-time">Icon</label>
                        <input type="text" class="form-control" name="type[icon]" required>
                    </div>
                    <div class="col-sm">
                        <label for="name_de" class=""><?= lang('Color', 'Farbe') ?></label>
                        <input type="color" class="form-control" name="type[color]">
                    </div>
                </div>

                <div class="row row-eq-spacing  px-0" style="margin: 1rem -1rem;">
                    <div class="col-sm">
                        <label for="name" class="required ">Name (en)</label>
                        <input type="text" class="form-control" name="type[name]" required>
                    </div>
                    <div class="col-sm">
                        <label for="name_de" class="">Name (de)</label>
                        <input type="text" class="form-control" name="type[name_de]">
                    </div>
                </div>
                <button class="btn">Submit</button>
            </form>

            <div class="text-right mt-20">
                <a href="#close-modal" class="btn mr-5" role="button">Close</a>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="add-sub-type" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title">
                <?= lang('Add activity type', 'Aktivitätstyp hinzufügen') ?>
            </h5>

            <form action="#" method="get">
                <div class="row row-eq-spacing">
                    <div class="col-sm">
                        <label for="name" class="required "><?= lang('Category', 'Kategorie') ?></label>
                        <select name="subtype[type]" class="form-control" required>
                            <?php foreach ($Settings->getActivities() as $m => $val) { ?>
                                <option value="<?= $m ?>"><?= $m ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm">
                        <label for="id" class="required">ID</label>
                        <input type="text" class="form-control" name="subtype[id]" required oninput="lowercaseInput(this)">
                    </div>
                </div>
                <div class="row row-eq-spacing">
                    <div class="col-sm-2">
                        <label for="icon" class="required">Icon</label>
                        <input type="text" class="form-control" name="subtype[icon]" required>
                    </div>
                    <div class="col-sm">
                        <label for="name" class="required ">Name (en)</label>
                        <input type="text" class="form-control" name="subtype[name]" required>
                    </div>
                    <div class="col-sm">
                        <label for="name_de" class="">Name (de)</label>
                        <input type="text" class="form-control" name="subtype[name_de]">
                    </div>
                </div>
                <button class="btn">Submit</button>
            </form>
            <div class="text-right mt-20">
                <a href="#close-modal" class="btn mr-5" role="button">Close</a>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-9">
        <?php
        // include BASEPATH . "/components/admin-nav.php";
        ?>

        <form action="#" method="post" id="modules-form" class="">
            <button class="btn success large position-fixed" style="top: calc(100vh - 10rem);right:0;z-index: 30;margin: 3rem">
                <i class="ph ph-floppy-disk"></i>
                Save
            </button>
            <?php foreach ($activities as $t => $type) {
                $color = $type['color'] ?? '';
                $member = $osiris->activities->count(['type' => $t]);
            ?>

                <div class="box type" id="type-<?= $t ?>" style="border-color:<?= $color ?>; <?= isset($type['new']) ? 'opacity:.8;font-style:italic;' : '' ?>">
                    <h2 class="header" style="background-color:<?= $color ?>20">
                        <i class="ph ph-<?= $type['icon'] ?? 'placeholder' ?> text-<?= $t ?> mr-10"></i>
                        <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
                        <a class="btn link px-5 text-secondary ml-auto" onclick="moveElementUp('type-<?= $t ?>')" data-toggle="tooltip" data-title="<?= lang('Move one up.', 'Bewege einen nach oben.') ?>"><i class="ph ph-arrow-line-up"></i></a>
                        <a class="btn link px-5 text-secondary" onclick="moveElementDown('type-<?= $t ?>')" data-toggle="tooltip" data-title="<?= lang('Move one down.', 'Bewege einen nach unten.') ?>"><i class="ph ph-arrow-line-down"></i></a>
                        <?php if ($member == 0) { ?>
                            <a class="btn link px-5 ml-20 text-danger " onclick="deleteElement('type-<?= $t ?>')" data-toggle="tooltip" data-title="<?= lang('Delete element.', 'Lösche Element.') ?>"><i class="ph ph-trash"></i></a>
                        <?php } else { ?>
                            <a class="btn link px-5 ml-20 text-muted " href='<?= ROOTPATH ?>/activities/search#{"$and":[{"type":"<?= $t ?>"}]}' target="_blank" data-toggle="tooltip" data-title="<?= lang("Can\'t delete category: $member activities associated.", "Kann Kategorie nicht löschen: $member Aktivitäten zugeordnet.") ?>"><i class="ph ph-trash"></i></a>
                        <?php } ?>


                    </h2>

                    <div class="content">
                        <input type="hidden" name="activities[<?= $t ?>][id]" value="<?= $t ?>">
                        <input type="hidden" name="add" value="type">

                        <div class="row row-eq-spacing">

                            <div class="col-sm">
                                <label for="icon" class="required">ID</label>
                                <input type="text" class="form-control disabled" name="activities[<?= $t ?>][id]" required value="<?= $type['id'] ?>" readonly>
                            </div>
                            <div class="col-sm">
                                <label for="icon" class="required element-time"><a href="https://phosphoricons.com/" class="link" target="_blank" rel="noopener noreferrer">Icon</a> </label>
                                <input type="text" class="form-control" name="activities[<?= $t ?>][icon]" required value="<?= $type['icon'] ?? 'placeholder' ?>">
                            </div>
                            <div class="col-sm">
                                <label for="name_de" class="">Color</label>
                                <input type="color" class="form-control" name="activities[<?= $t ?>][color]" value="<?= $type['color'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="row row-eq-spacing">
                            <div class="col-sm">
                                <label for="name" class="required ">Name (en)</label>
                                <input type="text" class="form-control" name="activities[<?= $t ?>][name]" required value="<?= $type['name'] ?? '' ?>">
                            </div>
                            <div class="col-sm">
                                <label for="name_de" class="">Name (de)</label>
                                <input type="text" class="form-control" name="activities[<?= $t ?>][name_de]" value="<?= $type['name_de'] ?? '' ?>">
                            </div>
                        </div>


                        <div class="children">
                            <?php
                            foreach ($type['children'] as $subtype) {
                                $st = $subtype['id'];
                                $submember = $osiris->activities->count(['type' => $t, 'subtype' => $st]);
                            ?>
                                <div class="box subtype" id="subtype-<?= $st ?>" style="border-color:<?= $color ?>; <?= isset($subtype['new']) ? 'opacity:.9;font-style:italic;' : '' ?>">
                                    <h4 class="header" style="background-color:<?= $color ?>20">
                                        <i class="ph ph-<?= $subtype['icon'] ?? 'placeholder' ?> text-<?= $t ?> mr-10"></i>
                                        <?= lang($subtype['name'], $subtype['name_de'] ?? $subtype['name']) ?>
                                        <?php if ($subtype['disabled'] ?? false) { ?>
                                            <span class="badge danger ml-20">DISABLED</span>
                                        <?php } ?>

                                        <a class="btn link px-5 text-secondary ml-auto" onclick="moveElementUp('subtype-<?= $st ?>')" data-toggle="tooltip" data-title="<?= lang('Move one up.', 'Bewege einen nach oben.') ?>"><i class="ph ph-arrow-line-up"></i></a>
                                        <a class="btn link px-5 text-secondary" onclick="moveElementDown('subtype-<?= $st ?>')" data-toggle="tooltip" data-title="<?= lang('Move one down.', 'Bewege einen nach unten.') ?>"><i class="ph ph-arrow-line-down"></i></a>
                                        <?php if ($submember == 0) { ?>
                                            <a class="btn link px-5 ml-20 text-danger " onclick="deleteElement('subtype-<?= $st ?>')" data-toggle="tooltip" data-title="<?= lang('Delete element.', 'Lösche Element.') ?>"><i class="ph ph-trash"></i></a>
                                        <?php } else { ?>
                                            <a class="btn link px-5 ml-20 text-muted " href='<?= ROOTPATH ?>/activities/search#{"$and":[{"type":"<?= $t ?>"},{"subtype":"<?= $st ?>"}]}' target="_blank" data-toggle="tooltip" data-title="<?= lang("Can\'t delete type: $submember activities associated.", "Kann Typ nicht löschen: $submember Aktivitäten zugeordnet.") ?>"><i class="ph ph-trash"></i></a>
                                        <?php } ?>
                                    </h4>

                                    <input type="hidden" name="activities[<?= $t ?>][children][<?= $st ?>][id]" value="<?= $st ?>">
                                    <div class="content">

                                        <div class="row row-eq-spacing">

                                            <div class="col-sm-2">
                                                <label for="icon" class="required">ID</label>
                                                <input type="text" class="form-control <?= isset($type['new']) ? '' : 'disabled' ?>" name="activities[<?= $t ?>][children][<?= $st ?>][id]" required value="<?= $subtype['id'] ?>" <?= isset($type['new']) ? '' : 'readonly' ?>>
                                            </div>
                                            <div class="col-sm-2">
                                                <label for="icon" class="required element-time"><a href="https://phosphoricons.com/" class="link" target="_blank" rel="noopener noreferrer">Icon</a> </label>
                                                <input type="text" class="form-control" name="activities[<?= $t ?>][children][<?= $st ?>][icon]" required value="<?= $subtype['icon'] ?? 'placeholder' ?>">
                                            </div>
                                            <div class="col-sm">
                                                <label for="name" class="required ">Name (en)</label>
                                                <input type="text" class="form-control" name="activities[<?= $t ?>][children][<?= $st ?>][name]" required value="<?= $subtype['name'] ?? '' ?>">
                                            </div>
                                            <div class="col-sm">
                                                <label for="name_de" class="">Name (de)</label>
                                                <input type="text" class="form-control" name="activities[<?= $t ?>][children][<?= $st ?>][name_de]" value="<?= $subtype['name_de'] ?? '' ?>">
                                            </div>
                                        </div>


                                        <div class="row row-eq-spacing">
                                            <div class="col-sm">
                                                <label for="description"><?= lang('Description', 'Beschreibung') ?> (en)</label>
                                                <input type="text" class="form-control" name="activities[<?= $t ?>][children][<?= $st ?>][description]" value="<?= $subtype['description'] ?? '' ?>">
                                            </div>
                                            <div class="col-sm">
                                                <label for="description_de" class=""><?= lang('Description', 'Beschreibung') ?> (de)</label>
                                                <input type="text" class="form-control" name="activities[<?= $t ?>][children][<?= $st ?>][description_de]" value="<?= $subtype['description_de'] ?? '' ?>">
                                            </div>
                                        </div>

                                    </div>
                                    <hr>

                                    <div class="content">
                                        <label for="module" class="font-weight-bold">Modules:</label>
                                        <div class="author-widget">
                                            <div class="author-list p-10">
                                                <?php
                                                $module_lst = [];
                                                foreach ($subtype['modules'] ?? array() as $module) {
                                                    $req = '';
                                                    $name = trim($module);
                                                    if (str_ends_with($name, '*') || in_array($name, ['title', 'authors', 'date', 'date-range'])) {
                                                        $name = str_replace('*', '', $name);
                                                        $module = $name . "*";
                                                        $req = 'required';
                                                    }
                                                    $module_lst[] = $name;
                                                ?>
                                                    <div class='author <?= $req ?>' ondblclick="toggleRequired(this)">
                                                        <?= $name ?>
                                                        <input type='hidden' name='activities[<?= $t ?>][children][<?= $st ?>][modules][]' value='<?= $module ?>'>
                                                        <a onclick='$(this).parent().remove()'>&times;</a>
                                                    </div>
                                                <?php } ?>

                                            </div>
                                            <div class=" footer">
                                                <div class="input-group small d-inline-flex w-auto">
                                                    <select class="module-input form-control">
                                                        <option value="" disabled selected><?= lang('Add module ...', 'Füge Module hinzu ...') ?></option>
                                                        <?php
                                                        foreach ($Modules->all_modules as $m => $_) {
                                                            if (in_array($m, $module_lst)) continue;
                                                        ?>
                                                            <option><?= $m ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button class="btn secondary h-full" type="button" onclick="addModule('<?= $t ?>', '<?= $st ?>');">
                                                            <i class="ph ph-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="content">
                                        <label for="format" class="font-weight-bold">Templates:</label>

                                        <div class="input-group mb-10">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text w-100">Print</span>
                                            </div>
                                            <input type="text" class="form-control" name="activities[<?= $t ?>][children][<?= $st ?>][template][print]" value="<?= $subtype['template']['print'] ?? '{title}' ?>">
                                        </div>

                                        <div class="input-group mb-10">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text w-100">Title</span>
                                            </div>
                                            <input type="text" class="form-control" name="activities[<?= $t ?>][children][<?= $st ?>][template][title]" value="<?= $subtype['template']['title'] ?? '{title}' ?>">
                                        </div>

                                        <div class="input-group mb-10">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text w-100">Subtitle</span>
                                            </div>
                                            <input type="text" class="form-control" name="activities[<?= $t ?>][children][<?= $st ?>][template][subtitle]" value="<?= $subtype['template']['subtitle'] ?? '{authors}' ?>">
                                        </div>

                                    </div>


                                    <hr>


                                    <div class="content">
                                        <label for="coins" class="font-weight-bold">Coins:</label>
                                        <input type="text" class="form-control" name="activities[<?= $t ?>][children][<?= $st ?>][coins]" value="<?= $subtype['coins'] ?? '0' ?>">
                                        <span class="text-muted">
                                            <?= lang('Please note that <q>middle</q> authors will receive half the amount.', 'Bitte bemerken Sie, <q>middle</q>-Autoren nur die Hälfte der Coins bekommen.') ?>
                                        </span>
                                    </div>

                                    <hr>


                                    <div class="content">
                                        <div class="custom-checkbox mb-10 danger">
                                            <input type="checkbox" id="disable-<?= $t ?>-<?= $st ?>" value="true" name="activities[<?= $t ?>][children][<?= $st ?>][disabled]" <?= ($subtype['disabled'] ?? false) ? 'checked' : '' ?>>
                                            <label for="disable-<?= $t ?>-<?= $st ?>"><?= lang('Deactivate', 'Deaktivieren') ?></label>
                                        </div>
                                        <span class="text-muted">
                                            <?= lang('Deactivated types are retained for past activities, but no new ones can be added.', 'Deaktivierte Typen bleiben erhalten für vergangene Aktivitäten, es können aber keine neuen hinzugefügt werden.') ?>
                                        </span>
                                    </div>

                                </div>
                            <?php } ?>

                        </div>

                        <a class="btn text-<?= $t ?>" href="#add-sub-type"><i class="ph ph-plus-circle"></i>
                            <?= lang('Add subtype', 'Neuen Typ hinzufügen') ?>
                        </a>


                    </div>

                </div>

            <?php } ?>


            <a class="btn osiris" href="#add-type"><i class="ph ph-plus-circle"></i>
                <?= lang('Add category', 'Neue Kategorie hinzufügen') ?>
            </a>



        </form>
    </div>
    <div class="col-lg-3 d-none d-lg-block">
        <nav class="on-this-page-nav">
            <div class="content">
                <?php foreach ($activities as $t => $type) { ?>
                    <a href="#type-<?= $t ?>" class="pl-10 font-weight-bold" style="color: <?= $type['color'] ?? 'inherit' ?>">
                        <?= lang($type['name'], $type['name_de'] ?? null) ?>
                    </a>
                    <?php foreach ($type['children'] as $subtype) { ?>
                        <a href="#subtype-<?= $subtype['id'] ?>" class="pl-20" style="color: <?= $type['color'] ?? 'inherit' ?>">
                            <?= lang($subtype['name'], $subtype['name_de'] ?? null) ?>
                        </a>
                    <?php } ?>
                <?php } ?>
            </div>
        </nav>
    </div>
</div>


<script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>

<script>
    function lowercaseInput(element) {
        $(element).val(element.value.toLowerCase())
    }

    function addModule(type, subtype) {

        var el = $('#type-' + type).find('#subtype-' + subtype).find('.author-widget')
        var val = el.find('.module-input').val()
        if (val === undefined || val === null) return;
        console.log(val);
        var author = $('<div class="author" ondblclick="toggleRequired(this)">')
            .html(val);
        author.append('<input type="hidden" name="activities[' + type + '][children][' + subtype + '][modules][]" value="' + val + '">')
        author.append('<a onclick="$(this).parent().remove()">&times;</a>')
        author.appendTo(el.find('.author-list'))
    }

    function toggleRequired(el) {
        const element = $(el)
        const input = element.find('input')
        if (element.hasClass('required')) {
            input.val(input.val().replace('*', ''))
        } else {
            input.val(input.val() + '*')
        }
        element.toggleClass('required')
    }

    function deleteElement(selector) {
        const el = $('#' + selector)
        if (el.hasClass('subtype')) {
            // check if there are other children
            const parent = el.closest('.type')
            if (parent.find('.subtype').length <= 1) {
                toastError(lang('Each category needs at least one Type.', 'Jede Kategorie benötigt mindestens einen Typ.'))
                return
            }
        }
        el.remove()
    }

    function moveElementUp(selector) {
        const el = $('#' + selector)
        el.insertBefore(el.prev());
    }

    function moveElementDown(selector) {
        const el = $('#' + selector)
        el.insertAfter(el.next());
    }
    var authordiv = $('.author-list')
    if (authordiv.length > 0) {
        authordiv.sortable({});
    }
</script>

<?php if (isset($_GET['subtype']) && isset($_GET['subtype']['id'])) { ?>
    <script>
        // TODO: scroll to type
    </script>
<?php } ?>