<?php

/**
 * News add page
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @link        /news/add
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
$news_lang = $Settings->get('news-language', 'one');

include_once BASEPATH . "/header-editor.php";
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$form_action = '/crud/news/create';
if (isset($news) && isset($news['_id'])) {
    $form_action = '/crud/news/update/' . e($news['_id']);
}
?>
<style>
    .bottom-buttons {
        z-index: 90;
    }

    .collapse-panel {
        margin-bottom: 2rem;
    }

    .collapse-content {
        background-color: white;
    }

    .collapse-header {
        cursor: pointer;
        font-size: 1.8rem;
        font-weight: bold;
    }

    .collapse-header,
    .collapse-panel[open] .collapse-header {
        color: var(--primary-color);
        border-color: var(--primary-color);
        background-color: var(--primary-color-20);
    }

    .collapse-header:hover,
    .collapse-panel[open] .collapse-header:hover {
        background-color: var(--primary-color-30);
    }

    .collapse-panel.project-panel {
        --primary-color: #1c7d72;
        --primary-color-dark: #157065;
        --primary-color-20: #1c7d7233;
        --primary-color-30: #1c7d723d;
    }

    .collapse-panel.infrastructure-panel {
        --primary-color: #d48646;
        --primary-color-dark: #b36a36;
        --primary-color-20: #d4864633;
        --primary-color-30: #d486463d;
    }

    .collapse-panel.activity-panel {
        --primary-color: #9f4371;
        --primary-color-dark: #7a345a;
        --primary-color-20: #9f437133;
        --primary-color-30: #9f43713d;
    }


    .suggestions {
        color: #464646;
        /* position: absolute; */
        margin: 10px auto;
        top: 100%;
        left: 0;
        height: 19.2rem;
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
        border-radius: var(--border-radius);
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

    /* .suggestions {
        color: #464646;
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
    } */
</style>

<h1>
    <i class="ph-duotone ph-megaphone"></i>
    <?= isset($news) && isset($news['_id']) ? lang('Edit news item', 'Nachricht bearbeiten') : lang('Create news item', 'Nachricht erstellen') ?>
</h1>

<form action="<?= ROOTPATH ?><?= $form_action ?>" method="post" enctype="multipart/form-data">
    <?php if ($news_lang == 'one') { ?>
        <div class="box padded">
            <h2 class="title"><?= lang('News content', 'Nachrichteninhalt') ?></h2 class="title">
            <div class="form-group">
                <label for="news-title" class="required"><?= lang('Title', 'Titel') ?></label>
                <input type="text" name="news[title]" id="news-title" class="form-control large" value="<?= $news['title'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="news-teaser"><?= lang('Teaser', 'Teaser') ?></label>
                <textarea name="news[teaser]" id="news-teaser" class="form-control" rows="3"><?= $news['teaser'] ?? '' ?></textarea>
                <small class="text-muted"><?= lang('Optional short summary that will be shown in the news overview.', 'Optionale kurze Zusammenfassung, die in der Nachrichtenübersicht angezeigt wird.') ?></small>
            </div>
            <div class="form-group mb-0">
                <label for="content-editor"><?= lang('Content', 'Inhalt') ?></label>
                <div id="content-editor-quill"><?= $news['content'] ?? '' ?></div>
                <textarea name="news[content]" id="content-editor" class="d-none" readonly><?= $news['content'] ?? '' ?></textarea>
                <script>
                    quillEditor('content-editor');
                </script>
            </div>
        </div>
    <?php } else { ?>
        <div class="row row-eq-spacing">
            <div class="col-md-6">
                <div class="box padded h-full">
                    <h2 class="title d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h2 class="title">

                    <div class="form-group">
                        <label for="news-title" class="required"><?= lang('Title', 'Titel') ?></label>
                        <input type="text" name="news[title]" id="news-title" class="form-control large" value="<?= $news['title'] ?? '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="news-teaser"><?= lang('Teaser', 'Teaser') ?></label>
                        <textarea name="news[teaser]" id="news-teaser" class="form-control" rows="3"><?= $news['teaser'] ?? '' ?></textarea>
                        <small class="text-muted"><?= lang('Optional short summary that will be shown in the news overview.', 'Optionale kurze Zusammenfassung, die in der Nachrichtenübersicht angezeigt wird.') ?></small>
                    </div>

                    <div class="form-group mb-0">
                        <label for="content-editor"><?= lang('Content', 'Inhalt') ?></label>
                        <div id="content-editor-quill"><?= $news['content'] ?? '' ?></div>
                        <textarea name="news[content]" id="content-editor" class="d-none" readonly><?= $news['content'] ?? '' ?></textarea>
                        <script>
                            quillEditor('content-editor');
                        </script>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box padded h-full">
                    <h2 class="title d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h2 class="title">

                    <div class="form-group">
                        <label for="news-title-de"><?= lang('Title', 'Titel') ?></label>
                        <input type="text" name="news[title_de]" id="news-title-de" class="form-control large" value="<?= $news['title_de'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="news-teaser-de"><?= lang('Teaser', 'Teaser') ?></label>
                        <textarea name="news[teaser_de]" id="news-teaser-de" class="form-control" rows="3"><?= $news['teaser_de'] ?? '' ?></textarea>
                        <small class="text-muted"><?= lang('Optional short summary that will be shown in the news overview.', 'Optionale kurze Zusammenfassung, die in der Nachrichtenübersicht angezeigt wird.') ?></small>
                    </div>

                    <div class="form-group mb-0">
                        <label for="content_de-editor"><?= lang('Content', 'Inhalt') ?></label>
                        <div id="content_de-editor-quill"><?= $news['content_de'] ?? '' ?></div>
                        <textarea name="news[content_de]" id="content_de-editor" class="d-none" readonly><?= $news['content_de'] ?? '' ?></textarea>
                        <script>
                            quillEditor('content_de-editor');
                        </script>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <!-- add activities -->

    <div class="box padded" id="activities">
        <h2 class="title"><?= lang('Connected people and research', 'Verknüpfte Personen und Forschung') ?></h2 class="title">


        <input type="hidden" name="news[persons]" value="">
        <details class="collapse-panel person-panel" open>
            <summary class="collapse-header">
                <?= lang('People', 'Personen') ?>
            </summary>
            <div class="collapse-content">
                <ul id="person-list">
                    <?php foreach ($news['persons'] ?? [] as $res) {
                        $person = $osiris->persons->findOne(['_id' => $DB->to_ObjectID($res)]);
                    ?>
                        <li>
                            <?= $person['displayname'] ?>
                            <input type="hidden" name="news[persons][]" value="<?= $res ?>">
                            <button class="btn link text-danger small" type="button" onclick="$(this).closest('li').remove()"><i class="ph ph-trash"></i></button>
                        </li>
                    <?php } ?>

                </ul>

                <div class="input-group">
                    <select id="person-select" class="form-control" placeholder="<?= lang('Please select a person', 'Bitte wähle eine Person aus') ?>">
                        <option value=""><?= lang('Please select a person', 'Bitte wähle eine Person aus') ?></option>
                        <?php
                        $persons = $osiris->persons->find([], ['sort' => ['displayname' => 1], 'projection' => ['_id' => 1, 'displayname' => 1]])->toArray();
                        foreach ($persons as $s) { ?>
                            <option value="<?= $s['_id'] ?>"><?= $s['displayname'] ?></option>
                        <?php } ?>
                    </select>
                    <div class="input-group-append">
                        <button class="btn secondary" type="button" onclick="addPerson()"><?= lang('Add', 'Hinzufügen') ?></button>
                    </div>
                </div>
            </div>
        </details>

        <script>
            let personSelect = $("#person-select").selectize();

            function addPerson() {
                const personId = $('#person-select').val();
                const personName = $('#person-select option:selected').text();
                if (!personId) {
                    alert('<?= lang('Please select a person', 'Bitte wähle eine Person aus') ?>');
                    return;
                }
                // check if person already exists
                if ($('#person-list').find(`input[name="news[persons][]"][value="${personId}"]`).length > 0) {
                    toastError('<?= lang('This person is already connected', 'Diese Person ist bereits verbunden') ?>');
                    return;
                }
                const row = $('<li>');
                row.append(`${personName}<input type="hidden" name="news[persons][]" value="${personId}">`);
                row.append(`<button class="btn link text-danger small" type="button" onclick="$(this).closest('li').remove()"><i class="ph ph-trash"></i></button>`);
                $('#person-list').append(row);
                // reset select
                personSelect[0].selectize.clear();
            }
        </script>

        <input type="hidden" name="news[activities]" value="">
        <details class="collapse-panel activity-panel" open id="activity-panel">
            <summary class="collapse-header">
                <?= lang('Activities', 'Aktivitäten') ?>
            </summary>
            <div class="collapse-content">
                <ul id="activity-list">
                    <?php foreach ($news['activities'] ?? [] as $res) {
                        $doc = $DB->getActivity($res);
                    ?>
                        <li>
                            <?= $doc['rendered']['icon'] ?>
                            <?= $doc['rendered']['plain'] ?>
                            <input type="hidden" name="news[activities][]" value="<?= $res ?>">
                            <button class="btn link text-danger small" type="button" onclick="$(this).closest('li').remove()"><i class="ph ph-trash"></i></button>
                        </li>
                    <?php } ?>

                </ul>

                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search for Activity" onkeypress="if(event.keyCode==13){searchActivities();return false;}">
                    <div class="input-group-append">
                        <button class="btn secondary" type="button" onclick="searchActivities()"><?= lang('Search', 'Suchen') ?></button>
                    </div>
                </div>

                <div class="suggestions" style="display:none;"></div>
            </div>
        </details>


        <?php if ($Settings->featureEnabled('projects')) {
            $mongo_ids = DB::to_ObjectIDs($news['projects'] ?? []);
            $projects = $osiris->projects->find(['_id' => ['$in' => $mongo_ids]], [
                'projection' => ['_id' => 1, 'name' => 1, 'acronym' => 1, 'title' => 1, 'title_de' => 1, 'internal_number' => 1],
                'sort' => ['name' => 1]
            ])->toArray();
        ?>
            <details class="collapse-panel project-panel" open>
                <summary class="collapse-header">
                    <?= lang('Projects', 'Projekte') ?>
                </summary>
                <div class="collapse-content">
                    <?php
                    $full_permission = $Settings->hasPermission('projects.edit') || $Settings->hasPermission('projects.connect');
                    $filter = [];
                    if (!$full_permission) {
                        // make sure to include currently selected projects
                        $filter = ['$or' => [['persons.user' => $_SESSION['username']], ['_id' => ['$in' => $news['projects'] ?? []]]]];
                    }
                    $project_list = $osiris->projects->find($filter, [
                        'projection' => ['_id' => 1, 'name' => 1, 'acronym' => 1, 'title' => 1, 'title_de' => 1, 'internal_number' => 1],
                        'sort' => ['name' => 1]
                    ])->toArray();
                    ?>
                    <div class="d-flex gap-10 mb-20">
                        <select id="project-select" class="form-control" placeholder="<?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?>">
                            <option value=""><?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?></option>
                            <?php
                            foreach ($project_list as $s) { ?>
                                <option value="<?= $s['_id'] ?>"><?= isset($s['acronym']) ? $s['acronym'] . ' – ' : '' ?><?= $s['name'] ?> <?= lang($s['title'], $s['title_de'] ?? null) ?> <?= isset($s['internal_number']) ? ('(ID ' . $s['internal_number'] . ')') : '' ?></option>
                            <?php } ?>
                        </select>
                        <button class="btn primary" type="button" onclick="addProjectRow()"><i class="ph ph-plus-circle"></i> <?= lang('Add', 'Hinzufügen') ?></button>
                    </div>
                    <!-- make sure that empty projects are also submitted -->
                    <input type="hidden" name="news[projects]" value="">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= lang('Connected projects', 'Verknüpfte Projekte') ?>:</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="project-list"><?php
                                                    foreach ($projects as $p) {
                                                        if (!isset($p['_id'])) {
                                                            $p['_id'] = $p;
                                                        }
                                                    ?>
                                <tr id="project-<?= $p['_id'] ?>">
                                    <td class="w-full">
                                        <input type="hidden" name="news[projects][]" value="<?= $p['_id'] ?>">
                                        <b><?= isset($p['acronym']) ? $p['acronym'] . ' – ' : '' ?><?= $p['name'] ?></b>
                                        <br>
                                        <span class="text-muted">
                                            <?= $p['title'] ?? '' ?>
                                        </span>
                                    </td>
                                    <td class="w-50">
                                        <button class="btn link text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>



                    <?php if ($full_permission) { ?>
                        <p class="text-muted font-size-12 mb-0">
                            <i class="ph ph-info"></i>
                            <?= lang('Note: only projects are shown here. You cannot connect proposals.', 'Bemerkung: nur Projekte werden hier gezeigt. Du kannst keine Anträge verknüpfen.') ?>
                        </p>
                    <?php } else { ?>
                        <p class="text-muted font-size-12 mb-0">
                            <i class="ph ph-info"></i>
                            <?= lang('Note: only <b>your own</b> projects are shown here. You cannot connect proposals.', 'Bemerkung: nur <b>deine eigenen</b> Projekte werden hier gezeigt. Du kannst keine Anträge verknüpfen.') ?>
                        </p>
                    <?php } ?>

                    <script>
                        let projectSelect = $("#project-select").selectize();

                        function addProjectRow() {
                            const row = $('<tr>')
                            const projectId = $('#project-select').val();
                            const projectName = $('#project-select option:selected').text();
                            if (!projectId) {
                                alert('<?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?>');
                                return;
                            }
                            // check if project already exists
                            if ($('#project-list').find(`#project-${projectId}`).length > 0) {
                                toastError('<?= lang('This project is already connected', 'Dieses Projekt ist bereits verbunden') ?>');
                                return;
                            }
                            row.append(`<td class="w-full">
                                <input type="hidden" name="news[projects][]" value="${projectId}">
                                <b>${projectName}</b>
                                </td>
                                `);
                            row.append(`<td class="w-50">
                                <button class="btn link text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                </td>`);
                            row.attr('id', `project-${projectId}`);
                            $('#project-list').append(row)
                            // reset select
                            projectSelect[0].selectize.clear();
                        }
                    </script>
                </div>
            </details>
        <?php } ?>


        <?php if ($Settings->featureEnabled('events')) {
            $events = $osiris->conferences->find(['_id' => ['$in' => $news['events'] ?? []]], [
                'projection' => ['_id' => 1, 'name' => 1, 'acronym' => 1, 'title' => 1, 'title_de' => 1, 'internal_number' => 1],
                'sort' => ['name' => 1]
            ])->toArray();
        ?>
            <details class="collapse-panel event-panel" open>
                <summary class="collapse-header">
                    <?= lang('Events', 'Veranstaltungen') ?>
                </summary>
                <div class="collapse-content">
                    <?php
                    $filter = [];
                    $event_list = $osiris->conferences->find($filter, [
                        'projection' => ['title' => 1, 'start' => 1, 'end' => 1, 'location' => 1, 'country' => 1],
                        'sort' => ['name' => 1]
                    ])->toArray();
                    ?>
                    <div class="d-flex gap-10 mb-20">
                        <select id="event-select" class="form-control" placeholder="<?= lang('Please select an event', 'Bitte wähle eine Veranstaltung aus') ?>">
                            <option value=""><?= lang('Please select an event', 'Bitte wähle eine Veranstaltung aus') ?></option>
                            <?php
                            foreach ($event_list as $ev) { ?>
                                <option value="<?= $ev['_id'] ?>">
                                    <?= e($ev['title']) ?> |
                                    <?= date('d.m.Y', strtotime($ev['start'])) ?> - <?= date('d.m.Y', strtotime($ev['end'])) ?>
                                    <?php if (!empty($ev['location'])) { ?>
                                        | <?= e($ev['location']) ?>
                                    <?php } ?>
                                </option>
                            <?php } ?>
                        </select>
                        <button class="btn primary" type="button" onclick="addEventRow()"><i class="ph ph-plus-circle"></i> <?= lang('Add', 'Hinzufügen') ?></button>
                    </div>
                    <!-- make sure that empty events are also submitted -->
                    <input type="hidden" name="news[events]" value="">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= lang('Connected events', 'Verknüpfte Veranstaltungen') ?>:</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="event-list"><?php
                                                foreach ($events as $e) {
                                                    if (!isset($e['_id'])) {
                                                        $e['_id'] = $e;
                                                    }
                                                ?>
                                <tr id="event-<?= $e['_id'] ?>">
                                    <td class="w-full">
                                        <input type="hidden" name="news[events][]" value="<?= $e['_id'] ?>">
                                        <b><?= isset($e['acronym']) ? $e['acronym'] . ' – ' : '' ?><?= $e['name'] ?></b>
                                        <br>
                                        <span class="text-muted">
                                            <?= $e['title'] ?? '' ?>
                                        </span>
                                    </td>
                                    <td class="w-50">
                                        <button class="btn link text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <script>
                        let eventSelect = $("#event-select").selectize();

                        function addEventRow() {
                            const row = $('<tr>')
                            const eventId = $('#event-select').val();
                            const eventName = $('#event-select option:selected').text();
                            if (!eventId) {
                                alert('<?= lang('Please select an event', 'Bitte wähle eine Veranstaltung aus') ?>');
                                return;
                            }
                            // check if event already exists
                            if ($('#event-list').find(`#event-${eventId}`).length > 0) {
                                toastError('<?= lang('This event is already connected', 'Diese Veranstaltung ist bereits verbunden') ?>');
                                return;
                            }
                            row.append(`<td class="w-full">
                                <input type="hidden" name="news[events][]" value="${eventId}">
                                <b>${eventName}</b>
                                </td>
                                `);
                            row.append(`<td class="w-50">
                                <button class="btn link text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                </td>`);
                            row.attr('id', `event-${eventId}`);
                            $('#event-list').append(row)
                            // reset select
                            eventSelect[0].selectize.clear();
                        }
                    </script>
                </div>
            </details>
        <?php } ?>


        <?php if ($Settings->featureEnabled('infrastructures')) {
            $infrastructures = $osiris->infrastructures->find(['id' => ['$in' => $news['infrastructures'] ?? []]], [
                'projection' => ['_id' => 1, 'id' => 1, 'name' => 1, 'subtitle' => 1],
                'sort' => ['end_date' => -1, 'start_date' => 1]
            ])->toArray();
        ?>
            <details class="collapse-panel infrastructure-panel" open>
                <summary class="collapse-header">
                    <?= $Settings->infrastructureLabel() ?>
                </summary>
                <div class="collapse-content">
                    <?php
                    $filter = [];
                    $all_infrastructures = $osiris->infrastructures->find(
                        $filter,
                        ['sort' => ['end_date' => -1, 'start_date' => 1], 'projection' => ['id' => 1, 'name' => 1]]
                    )->toArray();
                    ?>
                    <div class="d-flex gap-10 mb-20">
                        <select id="infrastructure-select" class="form-control" placeholder="<?= lang('Please select an infrastructure', 'Bitte wähle eine Infrastruktur aus') ?>">
                            <option value=""><?= lang('Please select an infrastructure', 'Bitte wähle eine Infrastruktur aus') ?></option>
                            <?php
                            foreach ($all_infrastructures as $s) { ?>
                                <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                            <?php } ?>
                        </select>
                        <button class="btn primary" type="button" onclick="addInfrastructureRow()"><i class="ph ph-plus-circle"></i> <?= lang('Add', 'Hinzufügen') ?></button>
                    </div>

                    <!-- make sure that empty infrastructures are also submitted -->
                    <input type="hidden" name="news[infrastructures]" value="">

                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= lang('Connected', 'Verknüpfte') ?> <?= $Settings->infrastructureLabel() ?>:</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="infrastructure-list"><?php
                                                        foreach ($infrastructures as $infra) { ?>
                                <tr>
                                    <td class="w-full">
                                        <input type="hidden" name="news[infrastructures][]" value="<?= $infra['id'] ?>">
                                        <b><?= $infra['name'] ?></b>
                                        <br>
                                        <span class="text-muted">
                                            <?= $infra['subtitle'] ?? '' ?>
                                        </span>
                                    </td>

                                    <td class="w-50">
                                        <button class="btn link text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <script>
                    let infraSelect = $("#infrastructure-select").selectize();

                    function addInfrastructureRow() {
                        const row = $('<tr>')
                        const infraId = $('#infrastructure-select').val();
                        const infraName = $('#infrastructure-select option:selected').text();
                        if (!infraId) {
                            alert('<?= lang('Please select an infrastructure', 'Bitte wähle eine Infrastruktur aus') ?>');
                            return;
                        }
                        // check if infrastructure already exists
                        if ($('#infrastructure-list').find(`input[value="${infraId}"]`).length > 0) {
                            toastError('<?= lang('This infrastructure is already connected', 'Diese Infrastruktur ist bereits verbunden') ?>');
                            return;
                        }
                        row.append(`<td class="w-full">
                            <input type="hidden" name="news[infrastructures][]" value="${infraId}">
                            <b>${infraName}</b>
                            </td>
                            `);
                        row.append(`<td class="w-50">
                            <button class="btn link text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                        </td>`);
                        $('#infrastructure-list').append(row)
                        // reset select
                        infraSelect[0].selectize.clear();
                    }
                </script>

            </details>
        <?php } ?>
    </div>


    <div class="box padded">
        <h2 class="title"><?= lang('Additional options', 'Weitere Optionen') ?></h2 class="title">

        <div class="form-group">
            <label for="type" class="required">
                <?= lang('Type', 'Typ') ?>
            </label>
            <select name="news[type]" id="type" class="form-control w-auto" required>
                <?php
                $vocab = $Vocabulary->getValues('news-category');
                $sel = $news['type'] ?? '';
                foreach ($vocab as $v) { ?>
                    <option value="<?= $v['id'] ?>" <?= $sel == $v['id'] ? 'selected' : '' ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label for="news-date" class="required"><?= lang('Publication Date', 'Veröffentlichungsdatum') ?></label>
            <input type="date" name="news[date]" id="news-date" class="form-control w-auto" value="<?= $news['date'] ?? date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label for="news-visibility" class="required"><?= lang('Visibility', 'Sichtbarkeit') ?></label>
            <select name="news[visibility]" id="news-visibility" class="form-control w-auto" required>
                <option value="internal" <?= (isset($news['visibility']) && $news['visibility'] == 'internal') ? 'selected' : '' ?>><?= lang('Internal', 'Intern') ?></option>
                <option value="public" <?= (isset($news['visibility']) && $news['visibility'] == 'public') ? 'selected' : '' ?>><?= lang('Public', 'Öffentlich') ?></option>
            </select>
            <small class="text-muted"><?= lang('Public news will made public via Portfolio if this feature is enabled. Internal news are only visible within OSIRIS.', 'Öffentliche Nachrichten werden über das Portfolio veröffentlicht, wenn diese Funktion aktiviert ist. Interne Nachrichten sind nur innerhalb von OSIRIS sichtbar.') ?></small>
        </div>

        <?php if ($Settings->featureEnabled('topics')) { ?>
            <?php $Settings->topicChooser(DB::doc2Arr($form['topics'] ?? []), 'news[topics]') ?>
        <?php } ?>


    </div>

    <button type="submit" class="btn primary">
        <i class="ph ph-check"></i>
        <?= lang('Save', 'Speichern') ?>
    </button>
</form>


<script>
    function searchActivities() {
        const section = $('#activity-panel')
        const val = section.find('input[type=text]').val()
        const suggest = section.find('.suggestions');
        suggest.empty().show();
        // prevent enter from submitting form
        $(section).closest('form').on('keypress', function(event) {
            if (event.keyCode == 13) {
                event.preventDefault();
            }
        })
        if (val.length < 3) {
            suggest.append(`<span >${lang('Please type at least 3 characters', 'Mindestens 3 Zeichen erforderlich')}</span>`)
            return;
        }
        $.get(ROOTPATH + '/api/activities-suggest/' + val, function(data) {
            console.log(data);
            if (data.count == 0) {
                suggest.append(`<span >${lang('Nothing found', 'Nichts gefunden')}</span>`)
                return;
            }
            data.data.forEach(function(d) {
                suggest.append(
                    `<a  data-id="${d.id.toString()}">${d.details.icon} ${d.details.plain}</a>`
                )
            })
            suggest.find('a')
                .on('click', function(event) {
                    event.preventDefault();
                    console.log(this);
                    const el = $('<li>')
                        .text($(this).text())
                    el.append(`<input type="hidden" name="news[activities][]" value="${$(this).data('id')}">`)
                    $('#activity-list').append(el);
                })
            // $('#activity-suggest .suggest').html(data);
        })

    }
</script>