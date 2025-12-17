<?php

/**
 * Form Builder
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.5.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
include_once BASEPATH . '/header-editor.php';
include_once BASEPATH . '/php/Modules.php';

$Modules = new Modules();
$all = [];
$tags = [];
foreach ($Modules->all_modules as $key => $value) {
    $all[$key] = [
        'name' => $value['name'] ?? $key,
        'name_de' => $value['name_de'] ?? null,
        'label' => $value['label'] ?? $value['name'] ?? $key,
        'label_de' => $value['label_de'] ?? null,
        'width' => $value['width'] ?? '12',
        'fields' => array_keys($value['fields'] ?? []),
        'type' => 'field',
        'tags' => implode(',', $value['tags'] ?? []),
    ];
    foreach ($value['tags'] ?? [] as $tag) {
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
        }
    }
}

$custom_fields = $osiris->adminFields->find()->toArray();
foreach ($custom_fields as $f) {
    $all[$f['id']] = [
        'name' => $f['name'],
        'name_de' => $f['name_de'] ?? null,
        'label' => $f['name'] ?? $key,
        'label_de' => $f['name_de'] ?? null,
        'width' => $f['width'] ?? '12',
        'fields' => [$f['id']],
        'type' => 'custom',
    ];
}

if (isset($_GET['copy']) && !empty($_GET['copy'])) {
    // load fields from another form
    $copy = $osiris->adminTypes->findOne(['id' => $_GET['copy']]);

    $modules = $copy['modules'] ?? [];
    $fields = DB::doc2Arr($copy['fields'] ?? []);
    if (empty($fields)) {
        foreach ($modules as $m) {
            $req = str_ends_with($m, '*');
            $id = $req ? substr($m, 0, -1) : $m;
            $mod = $all[$id] ?? [];
            $fields[] = [
                'id' => $id,
                'type' => $mod['type'] ?? 'field',
                'props' => ['required' => $req]
            ];
        }
    }
} else {
    // use current type
    $copy = false;
    $modules = $type['modules'] ?? [];
    $fields = DB::doc2Arr($type['fields'] ?? []);
    if (empty($fields)) {
        foreach ($modules as $m) {
            $req = str_ends_with($m, '*');
            $id = $req ? substr($m, 0, -1) : $m;
            $mod = $all[$id] ?? [];
            $fields[] = [
                'id' => $id,
                'type' => $mod['type'] ?? 'field',
                'props' => ['required' => $req]
            ];
        }
    }
}

// remove important tag from tags
if (($key = array_search('important', $tags)) !== false) {
    unset($tags[$key]);
}

$tagLabels = [
    'publication' => ['en' => 'Publication', 'de' => 'Publikation'],
    'general' => ['en' => 'General', 'de' => 'Allgemein'],
    'authors' => ['en' => 'Users', 'de' => 'Nutzende'],
    'layout' => ['en' => 'Layout', 'de' => 'Layout'],
    'location' => ['en' => 'Location', 'de' => 'Ort'],
    'event' => ['en' => 'Event', 'de' => 'Ereignis'],
    'date' => ['en' => 'Date', 'de' => 'Datum'],
    // 'important' => ['en' => 'Important', 'de' => 'Wichtig'],
    'teaching' => ['en' => 'Teaching', 'de' => 'Lehre'],
    'organizations' => ['en' => 'Organisations', 'de' => 'Organisationen'],
    'people' => ['en' => 'People', 'de' => 'Personen'],
];

?>

<style>
    /* Leichtes Layout-Tuning auf Basis Bootstrap-Klassen */
    body {
        background: #f7f7fb
    }

    .editor-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #fff;
    }

    .editor-header .btn {
        margin-left: .5rem
    }

    .panel {
        border: 1px solid var(--border-color);
        border-radius: .5rem;
        background: #fff
    }

    .panel .card-header {
        background: #fff;
    }

    .droparea {
        min-height: 320px;
        border: 2px dashed #e5e7eb;
        border-radius: .5rem;
        background: #fff;
    }

    .row.row-eq-spacing.droparea {
        padding: 1rem 1rem 4rem;
        margin: 0;
        position: relative;
    }

    .row.row-eq-spacing.droparea::after {
        content: "<?= lang('Select fields on the left and place them here.', 'Felder links auswählen und hier platzieren.') ?>";
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
        color: var(--muted-color);
        font-size: 1rem;

    }


    .canvas-item,
    #canvas-list .drag-item {
        border: 1px solid var(--border-color);
        border-radius: .5rem;
        padding: .75rem;
        background: #fff;
        margin-bottom: .75rem;
        display: flex;
        gap: .75rem;
        align-items: center;
        width: 100%;
    }

    .canvas-item .handle {
        width: 8px;
        align-self: stretch;
        border-right: 1px dashed var(--border-color)
    }

    .canvas-item .title,
    #canvas-list .drag-item span {
        font-weight: 600;
        font-size: 1.4rem;
    }

    .canvas-item .subtitle {
        font-size: .875rem;
        color: #6b7280;
        margin-top: -0.5rem;
    }

    .canvas-item .actions .btn {
        margin-left: .25rem
    }

    .canvas-item .icon {
        color: var(--primary-color);
        opacity: 0.7;
        font-size: 1.6rem;
        /* height: 2rem; */
    }

    #canvas-list .drag-item::before {
        content: "\E3D6";
        font-family: "Phosphor";
        font-size: 1.6rem;
        color: var(--primary-color);
        /* opacity: 0.3; */
        margin-right: .5rem;
        margin-left: .2rem;
    }

    .is-selected {
        outline: 2px solid var(--primary-color);
    }

    .list-group li {
        cursor: grab;
        border-top: 1px solid var(--border-color) !important;
        margin-top: -1px;
        /* display: flex;
        justify-content: space-between;
        align-items: center; */
    }

    .list-group li .badge {
        font-size: .75rem
    }

    .list-group li.dragging {
        background: #f3f4f6;
        border-top-width: 1px;
    }

    .sticky-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        padding: .75rem
    }

    .pillbar {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        padding: .5rem 0;
    }

    a.tag {
        cursor: pointer;
        font-size: .75rem
    }

    a.tag.active {
        background-color: var(--primary-color-30);
    }

    .sticky-panel {
        position: sticky;
        top: 8rem;
        max-height: calc(100vh - 10rem);
        overflow-y: auto;
    }

    #catalog-search-header {
        position: sticky;
        top: 0rem;
        z-index: 1000;
        background: white;
        margin: 0rem -2rem;
        padding: 2rem 2rem 1rem;
        border-bottom: 1px solid var(--border-color);
    }


    .form-help {
        display: none;
        position: absolute;
        right: 2rem;
        /* in d-flex row sitzt es angenehm rechts */
        top: 100%;
        margin-top: -.75rem;
        max-width: 28rem;
        z-index: 10;
        background: var(--signal-color-very-light);
        border: 1px solid var(--signal-color);
        border-radius: .5rem;
        box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
        padding: .5rem .75rem;
        font-size: 1rem;
        color: #374151;
    }

    .data-module:focus-within .form-help {
        display: block;
    }

    /* kleine Pfeilspitze */
    .form-help::before {
        content: "";
        position: absolute;
        right: .75rem;
        top: -13px;
        border: 6px solid transparent;
        border-bottom-color: var(--signal-color);
    }

    .form-help::after {
        content: "";
        position: absolute;
        right: .75rem;
        top: -11px;
        border: 6px solid transparent;
        border-bottom-color: var(--signal-color-very-light);
    }

    label.has-help::before {
        content: "\E2CE";
        font-family: "Phosphor";
        font-size: .85em;
        margin-right: .4rem;
        color: var(--signal-color);
        cursor: help;
    }

    /* Mobile: Hilfe unter das Feld in den Fluss setzen */
    @media (max-width: 576px) {
        .form-help {
            position: static;
            display: none;
            margin-top: .25rem;
        }

        .data-module:focus-within .form-help {
            display: block;
        }
    }
</style>

<div class="modal" id="preview" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-white">
            <button type="button" data-dismiss="modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div id="field-preview"></div>
            <div class="text-right mt-20">
                <button class="btn mr-5" type="button" data-dismiss="modal"><?= lang('Close', 'Schließen') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="form-preview" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-white">
            <button type="button" data-dismiss="modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h2 class="title">
                <?= lang('Form preview', 'Vorschau des Formulars') ?>: <?= lang($type['name'] ?? $type['id'], $type['name_de'] ?? null) ?>
            </h2>
            <div id="data-modules" class="row row-eq-spacing">
            </div>
            <div class="text-right mt-20">
                <button class="btn mr-5" type="button" data-dismiss="modal"><?= lang('Close', 'Schließen') ?></button>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="load-form" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-white">
            <button type="button" data-dismiss="modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h2 class="title">
                <?= lang('Load form', 'Formular laden') ?>
            </h2>

            <p class="text-muted">
                <?= lang('Select a form to load its fields into the builder.', 'Wähle ein Formular, um dessen Felder in den Builder zu laden.') ?>
            </p>

            <form action="#" method="GET">
                <div class="form-group">
                    <label for="load-form-select"><?= lang('Select form', 'Formular auswählen') ?></label>
                    <select id="load-form-select" class="form-control" name="copy" required>
                        <option value="" disabled selected><?= lang('Select a form', 'Ein Formular auswählen') ?></option>
                        <?php foreach ($osiris->adminTypes->find() as $at): ?>
                            <option value="<?= $at['id'] ?>"><?= lang($at['name'], $at['name_de'] ?? null) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <p class="text-signal">
                    <i class="ph ph-info"></i>
                    <?= lang('This will load the select form into the builder, however, as long as you do not save your form will not be overwritten.', 'Dies lädt das ausgewählte Formular in den Builder, aber solange du nicht speicherst, wird dein Formular nicht überschrieben.') ?>
                </p>

                <button class="btn signal">
                    <i class="ph ph-download-simple"></i>
                    <?= lang('Load form', 'Formular laden') ?>
                </button>
            </form>
            <div class="text-right mt-20">
                <button class="btn mr-5" type="button" data-dismiss="modal"><?= lang('Close', 'Schließen') ?></button>
            </div>
        </div>
    </div>
</div>


<div class="btn-toolbar float-right">
    <a class="btn" href="#load-form">
        <i class="ph ph-download-simple"></i>
        <?= lang('Load form', 'Formular laden') ?>
    </a>
    <a class="btn" href="<?= ROOTPATH ?>/admin/types/<?= $st ?>">
        <i class="ph ph-x"></i>
        <?= lang('Cancel', 'Abbrechen') ?>
    </a>
</div>

<a href="<?= ROOTPATH ?>/admin/types/<?= $st ?>">
    <i class="ph ph-arrow-left"></i>
    <?= lang('Back to activity type', 'Zurück zum Aktivitätstyp') ?>
</a>

<h1 class="m-0">
    <?= lang('Form Builder', 'Formular-Builder') ?>
</h1>

<ul class="breadcrumb category" style="--highlight-color:<?= $parent['color'] ?? '' ?>">
    <li>
        <a href="<?= ROOTPATH ?>/admin/categories/<?= $t ?>" class="colorless">
            <?= lang($parent['name'], $parent['name_de'] ?? null) ?>
        </a>
    </li>
    <li>
        <a href="<?= ROOTPATH ?>/admin/types/<?= $st ?>" class="colorless">
            <?= lang($type['name'], $type['name_de'] ?? null) ?>
        </a>
    </li>
</ul>

<?php if ($copy) { ?>
    <div class="alert info">
        <?= lang('You have loaded the fields from the following activity into your working space', 'Du hast die Felder von folgender Aktivität in den Arbeitsbereich geladen') ?>: <strong><?= lang($copy['name'], $copy['name_de'] ?? null) ?></strong>.
        <br>
        <?= lang('Once you save the form, it will overwrite the fields of this activity.', 'Sobald du das Formular speicherst, werden die Felder dieser Aktivität überschrieben.') ?>
    </div>
<?php } ?>


<form id="activity-form" action="<?= ROOTPATH ?>/crud/admin/activity-fields" method="post">
    <input type="hidden" name="activityType" value="<?= $type['id'] ?>">
    <input type="hidden" name="schema" id="schema">

    <div class="row row-eq-spacing">
        <!-- KATALOG (links) -->
        <div class="col-4" id="catalog-panel">
            <div class="panel card catalog sticky-panel pt-0">
                <div class="card-header" id="catalog-search-header">
                    <input id="catalog-search" type="search" class="form-control" placeholder="Felder durchsuchen …">

                    <!-- Layout-Sektion -->
                    <div class="pillbar">
                        <a class="badge tag" data-tag="all"><?= lang('All', 'Alle') ?></a>
                        <!-- <a class="badge tag" data-tag="layout">Layout</a> -->
                        <?php foreach ($tags as $tag): ?>
                            <a class="badge tag" data-tag="<?= $tag ?>"><?= lang($tagLabels[$tag]['en'] ?? ucfirst($tag), $tagLabels[$tag]['de'] ?? null) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- -->
                <div class="card-body py-10">
                    <div class="font-size-12 text-muted">Layout</div>
                    <ul id="catalog-layout" class="list-group mb-10">
                        <li class="drag-item"
                            data-tag="layout"
                            data-type="layout-heading" data-label="Überschrift">
                            <span>Überschrift</span>
                            <!-- <span class="badge bg-light ">H2–H4</span> -->
                        </li>
                        <li class="drag-item"
                            data-tag="layout"
                            data-type="layout-hr" data-label="Trennlinie">
                            <span>Trennlinie</span>
                        </li>
                        <li class="drag-item"
                            data-tag="layout"
                            data-type="layout-paragraph" data-label="Absatz">
                            <span>Absatz</span>
                        </li>
                    </ul>

                    <div class="font-size-12 text-muted">Custom Fields</div>
                    <ul id="catalog-custom" class="list-group mb-10">
                        <?php foreach ($custom_fields as $field) { ?>
                            <li class="drag-item"
                                data-type="custom"
                                data-id="<?= htmlspecialchars($field['id']) ?>"
                                data-label="<?= htmlspecialchars($field['name']) ?>"
                                data-label-de="<?= htmlspecialchars($field['name_de'] ?? $field['name']) ?>">
                                <div class="d-flex justify-content-between">
                                    <span><?= lang($field['name'], $field['name_de'] ?? null) ?></span>
                                    <a class="preview" href="#preview"><i class="ph ph-question"></i></a>
                                </div>
                                <code class="badge"><?= $field['id'] ?></code>
                            </li>
                        <?php } ?>

                    </ul>

                    <div class="font-size-12 text-muted mt-10"><?= lang('Data fields', 'Datenfelder') ?></div>
                    <ul id="catalog-list" class="list-group mb-10">
                        <?php foreach ($Modules->all_modules as $id => $def):
                            // skip elements that are in the form already
                            if (in_array($id, array_column($fields, 'id'))) {
                                continue; // bereits im Formular enthalten
                            }
                        ?>
                            <li class="drag-item"
                                data-type="field"
                                data-id="<?= htmlspecialchars($id) ?>"
                                data-label="<?= $def['label'] ?? $def['name'] ?>"
                                data-label-de="<?= $def['label_de'] ?? null ?>"
                                data-tags="<?= htmlspecialchars(implode(',', $def['tags'] ?? [])) ?>">
                                <div class="d-flex justify-content-between">
                                    <span><?= lang($def['name'] ?? $id, $def['name_de'] ?? null) ?></span>
                                    <a class="preview" href="#preview"><i class="ph ph-question"></i></a>
                                </div>
                                <code class="badge"><?= $id ?></code>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- PROPERTIES -->
        <div class="col-4 d-none" id="properties-panel">
            <div class="panel card sticky-panel">

                <button type="button" class="close" role="button" aria-label="Close" id="close-properties-btn">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="title">Eigenschaften</div>
                <div class="card-body">

                    <div class="action mb-20">
                        <button class="btn small danger" type="button" id="deleteSelection">
                            <i class="ph ph-trash"></i> <?= lang('Delete element', 'Element löschen') ?>
                        </button>
                    </div>


                    <div class="mb-10" id="selected-element">
                        <label class="form-label"><?= lang('Selected field', 'Ausgewähltes Feld') ?></label>
                        <input type="text" class="form-control text-monospace" value="—" disabled id="prop-id">
                    </div>

                    <div id="props-field" style="display:none;">
                        <b>
                            <?= lang('Overwrite the default label', 'Standard-Label überschreiben') ?>
                            <small data-toggle="tooltip" data-title="<?= lang('You can overwrite the default in the placeholder text with your own labels.', 'Der Default-Wert im Platzhaltertext kann mit deinem eigenen Wert überschrieben werden.') ?>"><i class="ph ph-info"></i></small>
                        </b>
                        <div class="input-group mb-10">
                            <div class="input-group-prepend">
                                <span class="input-group-text">EN</span>
                            </div>
                            <input type="text" class="form-control" placeholder="z. B. „Journal“" id="prop-label-en">
                        </div>
                        <div class="input-group mb-10">
                            <div class="input-group-prepend">
                                <span class="input-group-text">DE</span>
                            </div>
                            <input type="text" class="form-control" placeholder="z. B. Zeitschrift" id="prop-label-de">
                        </div>

                        <div class="mb-10">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="prop-required">
                                <label class="form-check-label" for="prop-required">Pflichtfeld</label>
                            </div>
                            <!-- <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="prop-portfolio">
                                <label class="form-check-label" for="prop-portfolio">Im Portfolio ausliefern</label>
                            </div> -->
                        </div>

                        <div class="mb-10">
                            <b>
                                <?= lang('Mutual Exclusivity (XOR)', 'Gegenseitige Exklusivität (XOR)') ?>
                                <small data-toggle="tooltip" data-title="<?= lang('Fields with the same XOR group name are mutually exclusive - only one can be filled. Leave empty if not needed.', 'Felder mit dem gleichen XOR-Gruppennamen schließen sich gegenseitig aus - nur eines kann ausgefüllt werden. Leer lassen, falls nicht benötigt.') ?>">
                                    <i class="ph ph-info"></i>
                                </small>
                            </b>
                            <input type="text" class="form-control" placeholder="<?= lang('e.g., "event_selection"', 'z. B. „event_selection"') ?>" id="prop-xor-group">
                            <small class="text-muted">
                                <?= lang('Give the same group name to fields that should be mutually exclusive.', 'Vergib den gleichen Gruppennamen für Felder, die sich gegenseitig ausschließen sollen.') ?>
                            </small>
                        </div>

                        <div class="mb-10">
                            <label class="form-label"><?= lang('Help text (en)', 'Hilfetext (en)') ?></label>
                            <textarea class="form-control" rows="3" placeholder="Short help text …" id="prop-help"></textarea>

                            <label class="form-label"><?= lang('Help text (de)', 'Hilfetext (de)') ?></label>
                            <textarea class="form-control" rows="3" placeholder="Kurzer Hilfetext …" id="prop-help-de"></textarea>
                        </div>



                        <div class="mb-10">
                            <label class="form-label"><?= lang('Width', 'Breite') ?></label>
                            <select class="form-control w-auto d-inline" id="prop-width">
                                <option value="" selected>Default</option>
                                <option value="12">Vollbreite</option>
                                <option value="9">3/4</option>
                                <option value="8">2/3</option>
                                <option value="6">1/2</option>
                                <option value="4">1/3</option>
                                <option value="3">1/4</option>
                            </select>
                            <small>
                                Default: <span id="default-width"></span>
                            </small>
                        </div>

                    </div>


                    <div id="props-heading" style="display:none;">

                        <div class="input-group mb-10">
                            <div class="input-group-prepend">
                                <span class="input-group-text">EN</span>
                            </div>
                            <input type="text" class="form-control" placeholder="z. B. „Journal“" id="prop-heading">
                        </div>
                        <div class="input-group mb-10">
                            <div class="input-group-prepend">
                                <span class="input-group-text">DE</span>
                            </div>
                            <input type="text" class="form-control" placeholder="z. B. Zeitschrift" id="prop-heading-de">
                        </div>
                    </div>

                    <div id="props-paragraph" style="display:none;">
                        <div class="mb-10">
                            <label class="form-label">EN</label>
                            <textarea class="form-control" rows="4" placeholder="Text für Absatz …" id="prop-paragraph"></textarea>
                        </div>
                        <div class="mb-10">
                            <label class="form-label">DE</label>
                            <textarea class="form-control" rows="4" placeholder="Text für Absatz …" id="prop-paragraph-de"></textarea>
                        </div>
                    </div>

                    <!-- <button class="btn" type="button" id="schemaPrint">
                        Echo Schema
                    </button> -->
                </div>
            </div>
        </div>

        <!-- CANVAS (rechts) -->
        <div class="col-8">
            <div class="panel card">
                <div class="card-header d-flex align-items-center">
                    <div class="title">Dieses Formular</div>
                    <div class="ml-auto">
                        <span class="badge" id="field-count">Felder: 0</span>
                    </div>
                </div>
                <div class="card-body">
                    <ul id="canvas-list" class="row row-eq-spacing droparea">
                        <?php if (!empty($fields)): ?>
                            <?php foreach ($fields as $it):
                                $field_type = $it['type'] ?? 'field';
                                $props = $it['props'] ?? [];
                                if ($field_type === 'field' || $field_type === 'custom'):
                                    $module = $all[$it['id']] ?? [];
                            ?>
                                    <li class="canvas-item col-sm-<?= $props['width'] ?? $module['width'] ?? 12 ?>"
                                        data-type="field"
                                        data-id="<?= htmlspecialchars($it['id']) ?>"
                                        data-props='<?= json_encode($props ?? []) ?>'>
                                        <div class="handle"></div>
                                        <div class="icon">
                                            <?php if ($field_type === 'field') { ?>
                                                <i class="ph ph-database"></i>
                                            <?php } else { ?>
                                                <i class="ph ph-textbox"></i>
                                            <?php } ?>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="title"><?= htmlspecialchars($all[$it['id']]['name_de'] ?? $all[$it['id']]['name'] ?? $it['id']) ?></div>
                                            <div class="subtitle">
                                                <code class="badge"><?= $it['id'] ?></code>
                                                <?php if (!empty($props['required'])): ?>
                                                    <span class="badge danger"><i class="ph ph-asterisk m-0"></i></span>
                                                <?php endif; ?>
                                                <?php if (!empty($props['label'])): ?>
                                                    <span class="badge primary"><i class="ph ph-tag m-0"></i></span>
                                                <?php endif; ?>
                                                <?php if (!empty($props['help'])): ?>
                                                    <span class="badge primary"><i class="ph ph-question m-0"></i></span>
                                                <?php endif; ?>
                                                <?php if (!empty($props['width'])): ?>
                                                    <span class="badge primary"><i class="ph ph-ruler m-0"></i></span>
                                                <?php endif; ?>
                                                <?php if (!empty($props['portfolio'])): ?>
                                                    <span class="badge primary"><i class="ph ph-globe m-0"></i></span>
                                                <?php endif; ?>
                                                <?php if (!empty($props['xor_group'])): ?>
                                                    <span class="badge warning" data-toggle="tooltip" data-title="XOR Group: <?= htmlspecialchars($props['xor_group']) ?>">
                                                        <i class="ph ph-arrows-left-right m-0"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <!-- <div class="actions ms-auto">
                                                <button type="button" class="btn link small text-danger js-del"><i class="ph ph-trash"></i></button>
                                            </div> -->
                                    </li>
                                <?php elseif ($field_type === 'heading'): ?>
                                    <li class="canvas-item" data-type="layout-heading" data-props='<?= json_encode($it['props'] ?? []) ?>'>
                                        <div class="handle"></div>
                                        <div class="icon">
                                            <i class="ph ph-text-h"></i>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="title"><?= lang('Header', 'Überschrift') ?></div>
                                            <div class="subtitle"><?= strtoupper(htmlspecialchars($it['props']['text'] ?? 'Platzhaltertext')) ?></div>
                                        </div>
                                        <!-- <div class="actions ms-auto">
                                                <button type="button" class="btn small text-danger js-del"><i class="ph ph-trash"></i></button>
                                            </div> -->
                                    </li>
                                <?php elseif ($field_type === 'paragraph'): ?>
                                    <li class="canvas-item" data-type="layout-paragraph" data-props='<?= json_encode($it['props'] ?? []) ?>'>
                                        <div class="handle"></div>
                                        <div class="icon">
                                            <i class="ph ph-paragraph"></i>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="title">Absatz</div>
                                            <div class="subtitle"><?= htmlspecialchars(($it['props']['text'] ?? 'Platzhaltertext')) ?></div>
                                        </div>
                                        <!-- <div class="actions ms-auto">
                                                <button type="button" class="btn small text-danger js-del"><i class="ph ph-trash"></i></button>
                                            </div> -->
                                    </li>
                                <?php elseif ($field_type === 'hr'): ?>
                                    <li class="canvas-item" data-type="layout-hr">
                                        <div class="handle"></div>
                                        <div class="icon">
                                            <i class="ph ph-minus"></i>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="title">Trennlinie</div>
                                        </div>
                                        <!-- <div class="actions ms-auto">
                                                <button type="button" class="btn small text-danger js-del"><i class="ph ph-trash"></i></button>
                                            </div> -->
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- leerer Canvas -->
                        <?php endif; ?>
                    </ul>
                    <!-- <?php if (empty($fields)): ?>
                        <div class="droparea">
                            <div class="text-center text-muted small py-4">Felder links auswählen und hier platzieren.</div>
                        </div>
                    <?php endif; ?> -->
                </div>
                <div class="sticky-footer text-end">
                    <button class="btn primary" type="submit" id="saveBtn">
                        <i class="ph ph-floppy-disk"></i>
                        <?= lang('Save', 'Speichern') ?>
                    </button>
                    <button class="btn" type="button" id="preview-button">
                        <i class="ph ph-eye"></i>
                        <?= lang('Preview', 'Vorschau') ?>
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>
<!-- sortable -->
<script src="<?= ROOTPATH ?>/js/Sortable.min.js"></script>
<script src="<?= ROOTPATH ?>/js/jquery-sortable.min.js"></script>
<script>
    const ALL = <?= json_encode($all) ?>;
    console.log(ALL);

    $(function() {

        // --- 2) Canvas als Sortier-/Drop-Ziel ---
        $('#catalog-layout').sortable({
            group: {
                name: 'fields',
                pull: 'clone',
                put: false
            },
            sort: false,
            animation: 150
        });

        $('#catalog-list,#catalog-custom').sortable({
            group: {
                name: 'fields',
                pull: true,
                put: false
            },
            sort: false,
            animation: 150
        });

        // Canvas: Ziel (empfangen + sortieren)
        const canvas = $('#canvas-list').sortable({
            group: {
                name: 'fields',
                pull: false,
                put: true
            },
            animation: 150,
            onAdd: function(evt) {
                // Wird ausgelöst, wenn ein Katalog-Item hineingezogen wird
                console.log(evt.item);
                const $src = $(evt.item);
                const t = $src.data('type'); // "field" | "layout-heading" | "layout-hr" | "layout-paragraph"
                const id = $src.data('id') || null; // nur bei "field"
                const label = $src.find('span').text() || $src.data('label') || '—'; // Fallback auf data-label

                // Ersetze den rohen <li> aus dem Katalog durch ein Canvas-Item-Template
                const $item = buildCanvasItem(t, id, label);
                $src.replaceWith($item);
            }
        });

        // preview
        $('#catalog-list, #catalog-custom').on('click', '.preview', function(e) {
            e.preventDefault();
            var $item = $(this).closest('.drag-item');
            var type = $item.data('type');
            var id = $item.data('id') || null;
            $('#field-preview').empty();
            $.ajax({
                url: ROOTPATH + '/get-module/' + id,
                data: {
                    description: true
                },
                success: function(data) {
                    $('#field-preview').html(data);
                    $('#preview').addClass('show');
                },
                error: function() {
                    $('#field-preview').html('<p class="text-danger">Fehler beim Laden der Vorschau.</p>');
                    $('#preview').addClass('show');
                }
            });
        });

        $('#preview-button').click(function(e) {
            e.preventDefault();
            var schema = readSchemaFromDOM();
            $('#data-modules').empty();
            $.ajax({
                url: ROOTPATH + '/get-form-preview',
                data: {
                    schema: JSON.stringify(schema)
                },
                success: function(data) {
                    $('#data-modules').html(data);
                    $('#form-preview').addClass('show');
                },
                error: function() {
                    console.log(data);
                    $('#data-modules').html('<p class="text-danger">Fehler beim Laden der Vorschau.</p>');
                    $('#form-preview').addClass('show');
                }
            });
        });

        function clearSelection() {
            // reset properties pane
            $('#activity-form input[type="text"]').val(''); // Reset input
            $('#activity-form textarea').val(''); // Reset textarea
            $('#activity-form select').val(''); // Reset selects
            $('#activity-form input[type="checkbox"]').prop('checked', false); // Reset checkboxes

            // hide all properties panes
            $('#props-field, #props-heading, #props-paragraph').hide();
            $('#canvas-list .canvas-item').removeClass('is-selected');
        }

        // close properties panel
        $('#close-properties-btn').on('click', function(e) {
            // Deselect
            $('.is-selected').removeClass('is-selected');
            $('#properties-panel').addClass('d-none');
            $('#catalog-panel').removeClass('d-none');
            clearSelection();
        });

        // --- 3) Auswahl (Markierung) ---
        $('#canvas-list').on('click', '.canvas-item', function(e) {
            if ($(e.target).closest('.actions').length) return;

            // check if already selected
            if ($(this).hasClass('is-selected')) {
                // Deselect
                $('#properties-panel').addClass('d-none');
                $('#catalog-panel').removeClass('d-none');
                clearSelection();
                return;
            }
            $('#properties-panel').removeClass('d-none');
            $('#catalog-panel').addClass('d-none');
            // Select this item
            clearSelection(); // Deselect all first
            $(this).addClass('is-selected');

            // update properties pane
            var type = $(this).data('type');
            var id = $(this).data('id') || '';
            var title = $(this).find('.title').text() || '—';
            $('#selected-element').hide()

            if (type === 'field' || type === 'custom') {
                const defaults = ALL[id] || {};
                // 
                console.log(defaults);
                $('#selected-element').show();
                $('#prop-id').val(id);
                $('#props-field').show();

                $('#prop-label-en').attr('placeholder', defaults.label || title);
                $('#prop-label-de').attr('placeholder', defaults.label_de || title);

                if (defaults.width === 0) {
                    // it is not possible to select a width for this module
                    $('#prop-width').val(''); // Reset width
                    $('#prop-width').prop('disabled', true);
                    $('#default-width').text('Disabled'); // No default width
                } else {
                    $('#prop-width').val(defaults.width || '12'); // Set default width
                    $('#prop-width').prop('disabled', false);
                    $('#default-width').text(translateWidth(defaults.width || '12'));
                }
                var props = $(this).data('props') || {};
                console.log('Feld-Eigenschaften:', props);
                $('#prop-help').val(props.help || '');
                $('#prop-help-de').val(props.help_de || props.help || '');
                $('#prop-width').val(props.width || '');
                $('#prop-required').prop('checked', props.required || false);
                $('#prop-label-en').val(props.label || '');
                $('#prop-label-de').val(props.label_de || '');
                $('#prop-xor-group').val(props.xor_group || '');
                // Weitere Logik für Felder…
            } else if (type === 'layout-heading') {
                $('#props-heading').show();
                // Überschrift-Einstellungen laden
                var props = $(this).data('props') || {};
                $('#prop-heading').val(props.text || '');
                $('#prop-heading-de').val(props.text_de || '');
            } else if (type === 'layout-paragraph') {
                $('#props-paragraph').show();
                // Absatz-Einstellungen laden
                var props = $(this).data('props') || {};
                $('#prop-paragraph').val(props.text || '');
                $('#prop-paragraph-de').val(props.text_de || props.text || '');
            }
        });

        // on change of properties: update the selected item
        $('#activity-form').on('change', '#prop-label-en, #prop-label-de, #prop-help, #prop-help-de, #prop-width, #prop-required, #prop-portfolio, #prop-xor-group, #prop-paragraph, #prop-paragraph-de, #prop-heading, #prop-heading-de', function() {
            var $selected = $('#canvas-list .canvas-item.is-selected');
            if ($selected.length === 0) return; // Nichts ausgewählt

            // Update data attributes
            var type = $selected.data('type');
            if (type === 'field' || type === 'custom') {
                // reset subtitle
                $subtitle = $selected.find('.subtitle')
                $subtitle.html('<code class="badge">' + $selected.data('id') + '</code>');

                let defaults = ALL[$selected.data('id')] || {};
                let props = {}
                if ($('#prop-required').is(':checked')) {
                    props.required = true;
                    $subtitle.append(' <span class="badge danger"><i class="ph ph-asterisk m-0"></i></span>');
                } else {
                    props.required = false;
                }
                if ($('#prop-label-en').val()) {
                    props.label = $('#prop-label-en').val();
                    $subtitle.append(' <span class="badge primary"><i class="ph ph-tag m-0"></i></span>');
                }
                if ($('#prop-label-de').val()) {
                    props.label_de = $('#prop-label-de').val();
                }
                if ($('#prop-help').val()) {
                    props.help = $('#prop-help').val();
                    $subtitle.append(' <span class="badge primary"><i class="ph ph-question m-0"></i></span>');
                }
                if ($('#prop-help-de').val()) {
                    props.help_de = $('#prop-help-de').val();
                }
                if ($('#prop-width').val()) {
                    props.width = $('#prop-width').val();
                    $width = $('#prop-width').val();
                    $selected.removeClass('col-sm-1 col-sm-2 col-sm-3 col-sm-4 col-sm-6 col-sm-8 col-sm-9 col-sm-12');
                    $selected.addClass('col-sm-' + $width);
                    $subtitle.append(` <span class="badge primary"><i class="ph ph-ruler m-0"></i></span>`);
                } else {
                    $selected.removeClass('col-sm-1 col-sm-2 col-sm-3 col-sm-4 col-sm-6 col-sm-8 col-sm-9 col-sm-12');
                    $selected.addClass('col-sm-' + defaults.width ?? '12');
                }
                if ($('#prop-portfolio').is(':checked')) {
                    props.portfolio = true;
                    $subtitle.append(' <span class="badge primary"><i class="ph ph-globe m-0"></i></span>');
                } else {
                    props.portfolio = false;
                }
                if ($('#prop-xor-group').val()) {
                    props.xor_group = $('#prop-xor-group').val();
                    $subtitle.append(' <span class="badge warning"><i class="ph ph-arrows-left-right m-0"></i></span>');
                }
                $selected.data('props', props);
                // $selected.find('.title').text($('#prop-label-en').val() || '—');

            } else if (type === 'layout-heading') {
                var props = {
                    text: $('#prop-heading').val() || 'Überschrift',
                    text_de: $('#prop-heading-de').val() || 'Überschrift'
                };
                $selected.data('props', props);
                $selected.find('.title').text(props.text);
            } else if (type === 'layout-paragraph') {
                var props = {
                    text: $('#prop-paragraph').val() || '',
                    text_de: $('#prop-paragraph-de').val() || ''
                };
                $selected.data('props', props);
                $selected.find('.subtitle').text(props.text);
            }
        });

        $('#schemaPrint').on('click', function() {
            var schema = readSchemaFromDOM();
            console.log('Aktuelles Schema:', schema);
        });

        function deleteItem(item) {
            var type = item.data('type');
            if (item.hasClass('is-selected')) {
                $('#properties-panel').addClass('d-none');
                $('#catalog-panel').removeClass('d-none');
                clearSelection();
            }

            let label = item.find('.title').text() || '—';

            if (type == 'field') {
                let module = ALL[item.data('id')] || {};
                $('#catalog-list').prepend(
                    $('<li class="drag-item">')
                    .data('type', 'field')
                    .data('id', item.data('id'))
                    .data('tags', module.tags ?? '')
                    .append(`
                        <div class="d-flex justify-content-between">
                            <span>${label}</span>
                            <a class="preview" href="#preview"><i class="ph ph-question"></i></a>
                        </div>
                        <code class="badge">${item.data('id')}</code>
                    `)
                );
            } else if (type == 'custom') {
                $('#catalog-custom').prepend(
                    $('<li class="drag-item">')
                    .data('type', 'custom')
                    .data('id', item.data('id'))
                    .append(`
                        <div class="d-flex justify-content-between">
                            <span>${label}</span>
                            <a class="preview" href="#preview"><i class="ph ph-question"></i></a>
                        </div>
                        <code class="badge">${item.data('id')}</code>
                    `)
                );
            } else if (type == 'layout-heading' || type == 'layout-hr' || type == 'layout-paragraph') {
                // do nothing, these are layout items
            }
            // clear selection if deleting the selected item

            // remove from canvas
            item.remove();
            updateFieldCount();
        }

        $('#deleteSelection').on('click', function() {
            // Lösche das aktuell ausgewählte Element
            var $item = $('#canvas-list .canvas-item.is-selected');
            if ($item.length === 0) return; // Nichts ausgewählt
            deleteItem($item);
        });

        // --- 4) Aktionen im Canvas ---
        $('#canvas-list').on('click', '.js-del', function() {

            // if it is a field, add it back to the catalog
            var $item = $(this).closest('.canvas-item');
            deleteItem($item);
        });

        // --- 5) Suche im Katalog (einfacher Client-Filter) ---
        $('#catalog-search').on('input', function() {
            var q = $(this).val().toLowerCase();
            $('.catalog .list-group li').each(function() {
                var s = $(this).text().toLowerCase();
                $(this).toggle(s.indexOf(q) !== -1);
            });
        });

        function searchByTag(tag) {
            // Filter the catalog by tag
            $('.catalog .list-group li').each(function() {
                var tags = $(this).data('tags') ? $(this).data('tags').split(',') : [];
                $(this).toggle(tags.includes(tag));
            });
        }
        // Tag-Filter in der Katalog-Sektion
        $('.pillbar .tag').on('click', function() {
            // highlight the selected tag
            var tag = $(this).data('tag');

            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                tag = 'all'; // Reset to show all
            }
            $('.pillbar .tag').removeClass('active');
            if (tag === 'all') {
                // Zeige alle Elemente
                $('.catalog .list-group li').show();
            } else {
                $(this).addClass('active');
                searchByTag(tag);
            }
        });

        // --- 6) Serialisierung beim Speichern ---
        $('#activity-form').on('submit', function() {
            var schema = readSchemaFromDOM();
            $('#schema').val(JSON.stringify(schema));
        });

        // --- 7) Validierung vor dem Speichern ---
        $('#saveBtn').on('click', function(e) {
            e.preventDefault();
            var schema = readSchemaFromDOM();
            console.log(schema);
            // check if schema is empty
            if (schema.items.length === 0) {
                toastError('Das Formular enthält keine Felder. Bitte füge mindestens ein Feld hinzu.');
                return;
            }

            // check if there are duplicate IDs
            var ids = schema.items.map(item => item.id);
            ids = ids.filter(id => id); // remove empty IDs
            var duplicates = ids.filter((id, index) => ids.indexOf(id) !== index);
            if (duplicates.length > 0) {
                toastError('Das Formular enthält doppelte IDs: ' + duplicates.join(', '));
                return;
            }

            // check if an author field is present
            let authorFields = ["authors", "author-table", "scientist", "supervisor", "supervisor-thesis", "editor"];
            let hasAuthorField = schema.items.some(item => item.type === 'field' && authorFields.includes(item.id));
            if (!hasAuthorField) {
                toastError('Das Formular muss mindestens ein Personen-Feld enthalten.');
                // filter by authors tag
                $('.pillbar .tag').removeClass('active');
                searchByTag('authors');
                $('.pillbar .tag[data-tag="authors"]').addClass('active');
                return;
            }

            // check if a date field is present
            let dateFields = ["date", "date-range", "date-range-ongoing"];
            let hasDateField = schema.items.some(item => item.type === 'field' && dateFields.includes(item.id));
            if (!hasDateField) {
                toastError('Das Formular muss mindestens ein Datumsfeld enthalten.');
                // filter by date tag
                $('.pillbar .tag').removeClass('active');
                searchByTag('date');
                $('.pillbar .tag[data-tag="date"]').addClass('active');
                return;
            }
            // Alles ok, Formular absenden
            $('#activity-form').submit();
        });

        function translateWidth(width) {
            // Übersetzt die Breite in einen lesbaren Text
            console.log(width);
            switch (width) {
                case '12':
                case 12:
                    return 'Vollbreite';
                case '9':
                case 9:
                    return '3/4';
                case '8':
                case 8:
                    return '2/3';
                case '6':
                case 6:
                    return '1/2';
                case '4':
                case 4:
                    return '1/3';
                case '3':
                case 3:
                    return '1/4';
                default:
                    return width;
            }
        }

        // ---- Helpers ----
        function buildCanvasItem(type, id, label) {
            // Erzeugt ein Canvas-Item basierend auf Typ und ID
            let defaults = ALL[id] || {};
            let width = defaults.width || '12'; // Default width is full width
            if (type === 'field') {
                return $('<li class="canvas-item col-sm-' + width + '" data-type="field">')
                    .attr('data-id', id)
                    // .attr('data-label', props.label || label)
                    // .attr('data-label-de', props.label_de || label)
                    .append('<div class="handle"></div>')
                    .append('<div class="icon"><i class="ph ph-database"></i></div>')
                    .append('<div class="flex-fill"><div class="title">' + (label || '—') + '</div><div class="subtitle"><code class="badge">' + id + '</code></div></div>')
                // .append('<div class="actions ms-auto"><button type="button" class="btn small text-danger js-del"><i class="ph ph-trash"></i></button></div>')
            }
            if (type === 'custom') {
                return $('<li class="canvas-item col-sm-' + width + '" data-type="custom">')
                    .attr('data-id', id)
                    // .attr('data-label', props.label || label)
                    // .attr('data-label-de', props.label_de || label)
                    .append('<div class="handle"></div>')
                    .append('<div class="icon"><i class="ph ph-textbox"></i></div>')
                    .append('<div class="flex-fill"><div class="title">' + (label || '—') + '</div><div class="subtitle"><code class="badge">' + id + '</code></div></div>')
                // .append('<div class="actions ms-auto"><button type="button" class="btn small text-danger js-del"><i class="ph ph-trash"></i></button></div>')
            }
            if (type === 'layout-heading') {
                return $('<li class="canvas-item col-sm-12" data-type="layout-heading">')
                    .append('<div class="handle"></div>')
                    .append('<div class="icon"><i class="ph ph-text-h"></i></div>')
                    .append('<div class="flex-fill"><div class="title">Überschrift</div><div class="subtitle">Heading</div></div>')
                // .append('<div class="actions ms-auto"><button type="button" class="btn small text-danger js-del"><i class="ph ph-trash"></i></button></div>')
            }
            if (type === 'layout-hr') {
                return $('<li class="canvas-item col-sm-12" data-type="layout-hr">')
                    .append('<div class="handle"></div>')
                    .append('<div class="icon"><i class="ph ph-minus"></i></div>')
                    .append('<div class="flex-fill"><div class="title">Trennlinie</div></div>')
                // .append('<div class="actions ms-auto"><button type="button" class="btn small text-danger js-del"><i class="ph ph-trash"></i></button></div>')
            }
            if (type === 'layout-paragraph') {
                return $('<li class="canvas-item col-sm-12" data-type="layout-paragraph">')
                    .append('<div class="handle"></div>')
                    .append('<div class="icon"><i class="ph ph-paragraph"></i></div>')
                    .append('<div class="flex-fill"><div class="title">Absatz</div><div class="subtitle">Platzhaltertext</div></div>')
                // .append('<div class="actions ms-auto"><button type="button" class="btn small text-danger js-del"><i class="ph ph-trash"></i></button></div>')
            }
            return $('<li class="canvas-item col-sm-12" data-type="unknown"><div class="handle"></div><div>Unbekannt</div></li>');
        }

        function readSchemaFromDOM() {
            var items = [];
            $('#canvas-list .canvas-item').each(function() {
                var $it = $(this);
                var t = $it.data('type');
                if (t === 'field') {
                    items.push({
                        type: 'field',
                        id: $it.data('id'),
                        props: $it.data('props') || {}
                    });
                } else if (t === 'custom') {
                    items.push({
                        type: 'custom',
                        id: $it.data('id'),
                        props: $it.data('props') || {}
                    });
                } else if (t === 'layout-heading') {
                    // Props später über Properties-Pane setzen; hier Defaults
                    var props = $it.data('props') || {
                        text: 'Überschrift',
                        level: 'h2'
                    };
                    items.push({
                        type: 'heading',
                        props: props
                    });
                } else if (t === 'layout-hr') {
                    items.push({
                        type: 'hr',
                        props: {}
                    });
                } else if (t === 'layout-paragraph') {
                    var p = $it.data('props') || {
                        text: 'Platzhaltertext'
                    };
                    items.push({
                        type: 'paragraph',
                        props: p
                    });
                }
            });
            return {
                activityType: $('input[name="activityType"]').val(),
                items: items
            };
        }

        function updateFieldCount() {
            var count = $('#canvas-list .canvas-item').length;
            $('#field-count').text('Felder: ' + count);
        }

        function highlightXorGroups() {
            var xorGroups = {};
            // Reset all XOR styling first
            $('#canvas-list .canvas-item').css('border-left', '');

            // Group fields by xor_group
            $('#canvas-list .canvas-item').each(function() {
                var props = $(this).data('props') || {};
                if (props.xor_group) {
                    if (!xorGroups[props.xor_group]) {
                        xorGroups[props.xor_group] = [];
                    }
                    xorGroups[props.xor_group].push($(this));
                }
            });

            // Apply visual styling to groups
            var colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
            Object.keys(xorGroups).forEach(function(group, index) {
                var color = colors[index % colors.length];
                xorGroups[group].forEach(function($item) {
                    $item.css('border-left', '3px solid ' + color);
                });
            });
        }

        // Update XOR highlighting when canvas changes
        $('#activity-form').on('change', '#prop-xor-group', function() {
            highlightXorGroups();
        });

        // Also update on sort
        $('#canvas-list').on('sortupdate', function() {
            highlightXorGroups();
        });

        // init count (falls serverseitig Items vorhanden)
        updateFieldCount();
        highlightXorGroups();
    });
</script>