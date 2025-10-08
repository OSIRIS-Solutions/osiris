<?php

/**
 * Page to add or edit one activity
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /add-activity
 * @link /activities/edit/<activity_id>
 *
 * @package OSIRIS
 * @since 1.0 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$drafts_enabled = $Settings->featureEnabled('drafts');
$user_drafts = 0;
if ($drafts_enabled) {
    // check if user has drafts
    $user_drafts = $osiris->activitiesDrafts->count([
        '$or' => [
            ['created_by' => $_SESSION['username']],
            ['draft_shared_with' => $_SESSION['username']]
        ]
    ]);
}


$form = $form ?? array();
$copy = $copy ?? false;
$draft = $draft ?? false;

// check if it is a copy of a disabled category
if ($copy && isset($form['subtype'])) {
    $cat = $osiris->adminTypes->findOne(['id' => $form['subtype']]);
    if ($cat === null || (isset($cat['disabled']) && $cat['disabled'] == "true")) {
        echo '<div class="alert signal mb-20">';
        echo lang('The activity you are trying to copy is of a disabled category. Please select a new category.', 'Die Aktivität, die du kopieren möchtest, gehört zu einer deaktivierten Kategorie. Bitte wähle eine neue Kategorie aus.');
        echo '</div>';
        $form['subtype'] = null;
    }
}

$formaction = ROOTPATH;
if (!empty($form) && isset($form['_id']) && !$copy) {
    $formaction .= "/crud/activities/update/" . $form['_id'];
    $btntext = '<i class="ph ph-check"></i> ' . lang("Update", "Aktualisieren");
    $url = ROOTPATH . "/activities/view/" . $form['_id'];
} else {
    $formaction .= "/crud/activities/create";
    $btntext = '<i class="ph ph-check"></i> ' . lang("Save", "Speichern");
    $url = ROOTPATH . "/activities/view/*";
}

function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return htmlspecialchars($val);
    }
    return $val;
}
?>

<style>
    .custom-radio input#open_access:checked~label::before {
        background-color: var(--success-color);
        border-color: var(--success-color);
    }

    .custom-radio input#open_access-0:checked~label::before {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }

    .description {
        color: var(--secondary-color);
        font-style: italic;
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
<?php include_once BASEPATH . '/header-editor.php'; ?>

<script src="<?= ROOTPATH ?>/js/add-activity.js?v=<?= CSS_JS_VERSION ?>"></script>


<div class="modal" id="add-event" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('Add event', 'Event hinzufügen') ?></h5>
            <div id="content" id="new-event">

                <div class="form-group mb-10">
                    <label for="title" class="required"><?= lang('(Short) Title', 'Kurztitel') ?></label>
                    <input type="text" id="event-title" required class="form-control">
                </div>
                <div class="form-group mb-10">
                    <label for="title"><?= lang('Full Title', 'Kompletter Titel') ?></label>
                    <input type="text" id="event-title_full" class="form-control">
                </div>

                <div class="form-row row-eq-spacing mb-10">
                    <div class="col">
                        <label for="start" class="required"><?= lang('Start date', 'Anfangsdatum') ?></label>
                        <input type="date" id="event-start" required class="form-control" onchange="$('#event-end').val(this.value)">
                    </div>
                    <div class="col">
                        <label for="end" class="required"><?= lang('End date', 'Enddatum') ?></label>
                        <input type="date" id="event-end" class="form-control">
                    </div>
                </div>

                <div class="form-group mb-10">
                    <label for="location" class="required"><?= lang('Location', 'Ort') ?></label>
                    <input type="text" id="event-location" required class="form-control">
                </div>

                <div class="form-group mb-10">
                    <label for="url"><?= lang('URL', 'URL') ?></label>
                    <input type="url" id="event-url" class="form-control">
                </div>

                <div class="custom-checkbox">
                    <input type="checkbox" id="event-attended" value="<?= $_SESSION['username'] ?>">
                    <label for="event-attended" class="blank"><?= lang('I have attended', 'Ich habe teilgenommen') ?></label>
                </div>

                <button class="btn mb-10" type="button" onclick="addEvent()"><?= lang('Add event', 'Event hinzufügen') ?></button>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="drafts-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                <span aria-hidden="true">&times;</span>
            </a>
            <div id="content" id="drafts">
            </div>
        </div>
    </div>
</div>


<div class="modal" id="author-help" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="modal-title">
                <?= lang('How to edit the author list', 'Wie bearbeite ich die Autorenliste') ?>?
            </h5>
            <?php if (lang("en", "de") == "en") { ?>
                <p>
                    To <b>add an author</b>, you have to enter him in the field marked "Add author ...". Please use the format <code>last name, first name</code>, so that OSIRIS can assign the authors correctly. <?= $Settings->get('affiliation') ?> authors are suggested in a list. An author from the list will be automatically assigned to <?= $Settings->get('affiliation') ?>.
                </p>

                <p>
                    To <b>remove an author</b>, you have to click on the X after his name.
                </p>
                <p>
                    To <b>change the author order</b>, you can take an author and drag and drop it to the desired position.
                </p>
                <p>
                    To <b>mark an author as belonging to the <?= $Settings->get('affiliation') ?></b>, you can simply double click on it. The name will then be highlighted in blue and the word <?= $Settings->get('affiliation') ?> will appear in front of it. It is important for reporting that all authors are marked according to their affiliation! If authors are <?= $Settings->get('affiliation') ?> employees but were not at the time of the activity, they must not be marked as a <?= $Settings->get('affiliation') ?> author!
                </p>
            <?php } else { ?>
                <p>
                    Um einen <b>Autor hinzuzufügen</b>, musst du ihn in das Feld eintragen, das mit "Add author ..." gekennzeichnet ist. Nutze dafür bitte das Format <code>Nachname, Vorname</code>, damit OSIRIS die Autoren korrekt zuordnen kann. <?= $Settings->get('affiliation') ?>-Autoren werden in einer Liste vorgeschlagen. Ein Autor aus der Liste wird automatisch zur <?= $Settings->get('affiliation') ?> zugeordnet.
                </p>

                <p>
                    Um einen <b>Autor zu entfernen</b>, musst du auf das X hinter seinem Namen klicken.
                </p>
                <p>
                    Um die <b>Autorenreihenfolge zu ändern</b>, kannst du einen Autoren nehmen und ihn mittels Drag & Drop an die gewünschte Position ziehen.
                </p>
                <p>
                    Um einen <b>Autor zur <?= $Settings->get('affiliation') ?> zugehörig zu markieren</b>, kannst du ihn einfach mit Doppelklick anklicken. Der Name wird dann blau markiert und das Wort <?= $Settings->get('affiliation') ?> taucht davor auf. Es ist wichtig für die Berichterstattung, dass alle Autoren ihrer Zugehörigkeit nach markiert sind! Wenn Autoren zwar Beschäftigte der <?= $Settings->get('affiliation') ?> sind, es aber zum Zeitpunkt der Aktivität nicht waren, dürfen sie nicht als <?= $Settings->get('affiliation') ?>-Autor markiert werden!
                </p>

                <p>
                    Verschrieben? Ein Autor wird nicht korrekt einem Nutzer zugeordnet? Nachdem du den Datensatz hinzugefügt hast, kannst du die Autorenliste <b>im Detail noch einmal bearbeiten</b>.
                </p>
            <?php } ?>

            <a href="https://wiki.osiris-app.de/users/content/create_content/" class="btn tour" target="_blank"><?= lang('Read more', 'Lies mehr') ?></a>

        </div>
    </div>
</div>


<div class="modal" id="journal-select" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>

            <label for="journal-search"><?= lang('Search Journal by name or ISSN', 'Suche Journal nach Name oder ISSN') ?></label>
            <div class="input-group">
                <input type="text" class="form-control" onchange="getJournal(this.value)" list="journal-list" id="journal-search" value="<?= $form['journal'] ?? '' ?>">
                <div class="input-group-append">
                    <button class="btn" onclick="getJournal($('#journal-search').val())"><i class="ph ph-magnifying-glass"></i></button>
                </div>
            </div>
            <table class="table simple">
                <tbody id="journal-suggest">

                </tbody>
            </table>

            <p class="text-muted">
                <?= lang(
                    'Note: if you have problems finding the Journal you are looking for, try to enter the Journal\'s ISSN (e.g. 1234-1234).',
                    'Anmerkung: Falls du Probleme hast, ein Journal zu finden, versuch es mit der ISSN (Format: 1234-1234).'
                ) ?>
            </p>
        </div>
    </div>
</div>


<div class="modal" id="teaching-select" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>

            <label for="teaching-search"><?= lang('Search Modules by name or module number', 'Suche Module nach Name oder Modulnummer') ?></label>
            <div class="input-group">
                <input type="text" class="form-control" onchange="getTeaching(this.value)" list="teaching-list" id="teaching-search" value="<?= $form['module'] ?? '' ?>">
                <div class="input-group-append">
                    <button class="btn" onclick="getTeaching($('#teaching-search').val())"><i class="ph ph-magnifying-glass"></i></button>
                </div>
            </div>
            <table class="table simple">
                <tbody id="teaching-suggest">

                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal" id="sws-calc" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <div class="content">
                <h3 class="title"><?= lang('SWS Calculator', 'SWS-Rechner') ?></h3>
            </div>

            <div class="row row-eq-spacing position-relative">
                <div class="col">
                    <div class="pr-20">
                        <label for="per-semester"><?= lang('Hours in the whole semester', 'Anzahl Stunden im Semester') ?> (á 45 min)</label>
                        <input type="number" name="per-semester" class="form-control" id="sws-semester">
                    </div>
                </div>
                <div class="text-divider">OR</div>
                <div class="col">
                    <div class="pl-20">
                        <label for="per-week"><?= lang('Hours per week', 'Anzahl Stunden pro Woche') ?> (á 45 min)</label>
                        <input type="number" name="per-week" class="form-control" id="sws-week">
                    </div>
                </div>
            </div>

            <div class="row row-eq-spacing">
                <div class="col-sm">
                    <label for="supervisors"><?= lang('Count of supervisors', 'Anzahl der Betreuungspersonen in dieser Zeit') ?></label>
                    <input type="number" class="form-control" id="sws-supervisors" name="supervisors">
                </div>
                <div class="col-sm">
                    <label for=""></label>
                    <div class="custom-switch">
                        <input type="checkbox" id="sws-practical" value="1">
                        <label for="sws-practical"><?= lang('Is practical course', 'Ist ein Praktikum') ?></label>
                    </div>
                </div>
            </div>
            <div class="content">

                <button class="btn osiris" type="button" onclick="calcSWS()"><?= lang('Calculate', 'Berechnen') ?></button>



                <div id="" class="font-size-16 mt-20">
                    Result: <span class="highlight-text" id="sws-result"></span>

                    <a href="https://humboldt-reloaded.uni-hohenheim.de/sws-beispielrechnung" target="_blank" rel="noopener noreferrer" class="link link-external float-right"><?= lang('Read more', 'Lies mehr') ?></a>

                </div>
            </div>

            <script>
                function calcSWS() {
                    const per_semester = $('#sws-semester').val()
                    const per_week = $('#sws-week').val()
                    const supervisors = $('#sws-supervisors').val()
                    const practical = $('#sws-practical').prop('checked')

                    var val = 'Error: missing fields'

                    if (per_semester > 0) {
                        val = (parseFloat(per_semester) / 14)
                    } else if (per_week > 0) {
                        val = parseFloat(per_week)
                    } else {
                        $('#sws-result').html(val)
                        return
                    }

                    if (supervisors > 1) {
                        val /= parseInt(supervisors)
                    }

                    if (practical) {
                        val *= 0.3
                    }

                    $('#sws-result').html(val.toFixed(1) + " SWS")

                }
            </script>

        </div>
    </div>
</div>

<?php if ($drafts_enabled && $user_drafts > 0) { ?>
    <a data-target="#drafts-modal" class="btn primary mb-10" id="drafts-btn" onclick="loadDrafts()">
        <i class="ph ph-file-text mr-5"></i>
        <?= lang('You have pending drafts', 'Du hast ausstehende Entwürfe') ?>
        <span class="index"><?= $user_drafts ?></span>
    </a>
<?php } ?>


<a target="_blank" href="https://wiki.osiris-app.de/users/content/create_content/" class="btn tour float-right ml-5" id="docs-btn">
    <i class="ph ph-question mr-5"></i>
    <?= lang('Read the Docs', 'Zur Hilfeseite') ?>
</a>

<?php if (empty($form)) { ?>
    <!-- Create new activity -->
    <h1 class="my-0">
        <i class="ph ph-plus-circle"></i>
        <?= lang('Add activity', 'Füge Aktivität hinzu') ?>
    </h1>

    <a href="<?= ROOTPATH ?>/activities/online-search" class="link mb-10 d-inline-block"><?= lang('Search in Pubmed', 'Suche in Pubmed') ?></a>

    <form method="get" onsubmit="getPubData(event, this)">
        <div class="form-group">
            <label for="doi"><?= lang('Search by DOI or Pubmed-ID', 'Suche über die DOI oder Pubmed-ID') ?>:</label>
            <div class="input-group">
                <input type="text" class="form-control" placeholder="10.1093/nar/gkab961" name="doi" value="" id="search-doi" autofocus>
                <div class="input-group-append">
                    <button class="btn secondary" type="submit"><i class="ph ph-magnifying-glass"></i></button>
                </div>
            </div>
        </div>
    </form>

    <div class="alert danger" id="id-exists" style="display:none;">
        <h4 class="title">
            <?= lang('Duplicate!', 'Duplikat!') ?>
        </h4>
        <p class="mt-10">
            <?= lang(
                'This DOI/Pubmed-ID already exists in the database!',
                'Diese DOI/Pubmed-ID existiert bereits in der Datenbank!'
            ) ?>
        </p>
        <a class="btn text-danger border-danger" href="link"><?= lang('View entry', 'Eintrag anschauen') ?> <i class="ph ph-arrow-fat-line-right"></i></a>
    </div>


    <div class="select-btns" id="select-btns">
        <?php
        foreach ($Categories->categories as $type) {
            $t = $type['id'];

            // check if subtypes are available
            $subtypes = $type['children'] ?? array();
            $subtypes = array_filter(DB::doc2Arr($subtypes), function ($s) {
                return !($s['disabled'] ?? false);
            });
            if (empty($subtypes)) continue;
            $st = array_values($subtypes)[0]['id'] ?? $t;
        ?>
            <button data-type="<?= $t ?>" onclick="togglePubType('<?= $st ?>', false)" class="btn select" id="<?= $t ?>-btn" <?= $Categories->cssVar($t) ?>>
                <span>
                    <i class='ph ph-<?= $type['icon'] ?? 'circle' ?>'></i>
                    <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
                </span>
            </button>
        <?php } ?>
    </div>


<?php } elseif ($draft) { ?>
    <h1 class="mt-0">
        <?= lang('Edit draft', 'Bearbeite Entwurf') ?>
    </h1>
<?php } elseif ($copy) { ?>
    <h1 class="mt-0"><?= lang('Copy activity', 'Kopiere Aktivität') ?></h1>
<?php } else { ?>
    <!-- Edit existing activity -->
    <h1 class="my-0"><?= lang('Edit activity', 'Bearbeite Aktivität') ?>:</h1>
    <div class="mb-10">
        <?php
        $Format = new Document(false);
        $Format->setDocument($form);
        echo $Format->activity_icon() . " ";
        echo $Format->formatShort();
        ?>
    </div>
<?php } ?>

<?php if (!empty($form)) { ?>

    <a href="#close-modal" class="text-decoration-none" onclick="$(this).next().slideToggle()">
        <i class="ph ph-caret-down"></i>
        <?= lang('Change type of activity', 'Ändere die Art der Aktivität') ?>
    </a>
    <div class="mb-20 select-btns" id="select-btns" style="display:none">

        <?php
        foreach ($Categories->categories as $type) {
            $t = $type['id'];

            // check if subtypes are available
            $subtypes = $type['children'] ?? array();
            $subtypes = array_filter(DB::doc2Arr($subtypes), function ($s) {
                return !($s['disabled'] ?? false);
            });
            if (empty($subtypes)) continue;
            $st = array_values($subtypes)[0]['id'] ?? $t;
        ?>
            <button data-type="<?= $t ?>" onclick="togglePubType('<?= $st ?>')" class="btn select" id="<?= $t ?>-btn" <?= $Categories->cssVar($t) ?>>
                <span>
                    <i class='ph ph-<?= $type['icon'] ?? 'circle' ?>'></i>
                    <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
                </span>
            </button>
        <?php } ?>
    </div>


<?php } ?>



<div class="box add-form" style="display:none" id="publication-form">
    <div class="content">
        <button class="btn osiris small float-right" onclick="$('#publication-form').toggleClass('show-examples')"><?= lang('Examples', 'Beispiele') ?></button>

        <?php if (!empty($form) && isset($_GET['epub'])) { ?>
            <div class="alert signal mb-20">
                <div class="title">
                    <?= lang('Please review this entry and mark it as "Not Epub".', 'Bitte überprüfe diesen Eintrag und markiere ihn als "nicht Epub".') ?>
                </div>
                <p>
                    <?= lang(
                        'Review carefully all data, especially the publication date, which has to be the <b>date of the issued publication</b> (not online publication)!',
                        'Überprüfe alle Daten sorgfältig, für den Fall, dass sich Änderungen ergeben haben. Besonders das Publikationsdatum muss überprüft und auf das <b>tatsächliche Datum der Publikation (nicht online)</b> gesetzt werden.'
                    ) ?>
                </p>
                <?php if (isset($form['doi']) && !empty($form['doi'])) { ?>
                    <p class="mb-0">
                        <a class="link" href="http://doi.org/<?= $form['doi'] ?>" target="_blank" rel="noopener noreferrer">
                            <?= lang(
                                'Have a look at the publishers page of your publication for reference.',
                                'Als Referenz kannst du hier die Seite des Publishers zu deiner Publikation sehen.'
                            ) ?>
                        </a>
                    </p>
                <?php } ?>

            </div>

        <?php } ?>

        <!-- SUBTYPES -->
        <?php

        foreach ($Categories->categories as $type) {
            $t = $type['id'];

            // check if subtypes are available
            $subtypes = $type['children'] ?? array();
            $subtypes = array_filter(DB::doc2Arr($subtypes), function ($s) {
                return !($s['disabled'] ?? false);
            });
            if (count($subtypes) <= 1) continue;
        ?>
            <div class="select-btns" data-type="<?= $t ?>">
                <?php foreach ($subtypes as $sub) {
                    if ($sub['disabled'] ?? false) continue;
                    $st = $sub['id'];
                ?>
                    <button onclick="togglePubType('<?= $st ?>')" class="btn select" id="<?= $st ?>-btn" data-subtype="<?= $st ?>" <?= $Categories->cssVar($t) ?>>
                        <span>
                            <i class='ph ph-<?= $sub['icon'] ?? 'circle' ?>'></i>
                            <?= lang($sub['name'], $sub['name_de'] ?? $sub['name']) ?>
                        </span>
                    </button>
                <?php } ?>
            </div>
        <?php
        }
        ?>

        <form action="<?= $formaction ?>" method="post" id="activity-form">
            <input type="hidden" class="hidden" name="redirect" value="<?= $url ?>">
            <input type="hidden" class="form-control disabled" name="values[type]" id="type" readonly>
            <input type="hidden" class="form-control disabled" name="values[subtype]" id="subtype" readonly>

            <?php if ($draft) { ?>
                <input type="hidden" class="form-control disabled" name="values[draft_id]" id="draft_id" value="<?= $form['_id'] ?? '' ?>" readonly>
            <?php } ?>


            <p id="type-description" class="description">
                <!-- filled by togglePubType() in add-activity.js -->
            </p>

            <p id="type-examples" class="examples">
                <!-- filled by togglePubType() in add-activity.js -->
            </p>
            <div id="data-modules" class="row row-eq-spacing">
                <!-- filled by togglePubType() in add-activity.js -->
            </div>

            <?php if (empty($form)) { ?>
                <input type="text" class="hidden" id="funding" name="values[funding]" value="">
            <?php } ?>

            <!-- if topics are registered, you can choose them here -->
            <?php $Settings->topicChooser($form['topics'] ?? []) ?>

            <?php if (!$copy && (!isset($form['comment']) || empty($form['comment']))) { ?>
                <div class="form-group">
                    <a onclick="$(this).next().toggleClass('hidden')">
                        <label onclick="$(this).next().toggleClass('hidden')" for="comment" class="cursor-pointer">
                            <i class="ph ph-plus"></i> <?= lang('Add note', 'Notiz') ?> (<?= lang('Only visible for authors and controlling staff.', 'Nur sichtbar für Autoren und Admins') ?>)
                        </label>
                    </a>
                    <textarea name="values[comment]" id="comment" cols="30" rows="2" class="form-control hidden"><?php if (!$copy) {
                                                                                                                        echo val('comment');
                                                                                                                    } ?></textarea>
                </div>
            <?php } else { ?>
                <div class="form-group">
                    <label for="comment"><?= lang('Comment', 'Kommentar') ?> (<?= lang('Only visible for authors and controlling staff.', 'Nur sichtbar für Autoren und Admins') ?>)</label>
                    <textarea name="values[comment]" id="comment" cols="30" rows="2" class="form-control"><?php if (!$copy) {
                                                                                                                echo val('comment');
                                                                                                            } ?></textarea>
                </div>
            <?php } ?>
            <?php if (!$copy && !empty($form) && isset($form['authors']) && (count($form['authors']) > 1 || ($form['authors'][0]['user'] ?? '') != $_SESSION['username'])) { ?>
                <div class="alert signal without-icon p-10 mb-10">
                    <div class="title">
                        <?= lang('Editorial area', 'Bearbeitungs-Bereich') ?>
                    </div>
                    <!-- <div class="form-group"> -->
                    <label for="editor-comment"><?= lang('Editor comment (tell your co-authors what you have changed)', 'Editor-Kommentar (teile deinen Ko-Autoren mit, was du geändert hast)') ?></label>
                    <textarea name="values[editor-comment]" id="editor-comment" cols="30" rows="2" class="form-control"></textarea>
                    <!-- </div> -->
                    <div class="mt-10">
                        <div class="custom-checkbox" id="minor-div">
                            <input type="checkbox" id="minor" value="1" name="minor">
                            <label for="minor"><?= lang('Changes are minor and coauthors do not need to be notified.', 'Änderungen sind minimal und Koautoren müssen nicht benachrichtigt werden.') ?></label>
                        </div>
                        <small class="text-muted">
                            <?= lang(
                                'Please note that changes to the authors and editors are ignored if this checkmark is set.',
                                'Bitte beachte, dass Änderungen an den Autoren und Herausgebern ignoriert werden, wenn dieser Haken gesetzt ist.'
                            ) ?>
                        </small>
                    </div>
                </div>
            <?php } ?>


            <div class="alert signal mb-10 <?= empty($form) ? '' : 'hidden' ?>" id="doublet-found" style="display:none;">
                <h4 class="title">
                    <i class="ph ph-warning text-osiris"></i>
                    <?= lang('Possible doublet found:', 'Mögliche Doublette erkannt:') ?>
                </h4>
                <p class="m-0">
                    <!-- filled by doubletCheck() in script.js -->
                </p>
            </div>

            <button class="btn secondary" type="submit" id="submit-btn" onclick="verifyForm(event, '#activity-form')"><?= $btntext ?></button>

            <?php if ($drafts_enabled && (empty($form) || $draft)) { ?>
                <button class="btn" type="button" id="draft-btn" onclick="saveDraft()"><?= lang('Save as draft', 'Als Entwurf speichern') ?></button>
            <?php } ?>

        </form>
    </div>
</div>


<datalist id="journal-list">
    <?php
    foreach ($osiris->journals->distinct('journal') as $j) { ?>
        <option><?= $j ?></option>
    <?php } ?>
</datalist>

<datalist id="scientist-list">
    <?php
    foreach ($osiris->persons->find(['last' => ['$ne' => '']], ['projection' => ['last' => 1, 'first' => 1], 'sort' => ['last' => 1]]) as $s) {
        if (empty($s['last'])) continue;
    ?>
        <option><?= $s['last'] ?>, <?= $s['first'] ?></option>
    <?php } ?>
</datalist>



<script>
    let UPDATE = false;
    let ID = null;
    let COPY = false;
    let CONFERENCE = '<?= $_GET['conference'] ?? '' ?>';
    let DRAFT = <?= json_encode($draft) ?>;
</script>

<?php if (!empty($form)) {

    if (isset($form['subtype'])) $t = $form['subtype'];
    else {
        $t = $form['type'];
        // get first available subtype
        $type = $Categories->getCategory($t);
        $subtypes = $type['children'] ?? array();
        $subtypes = array_filter(DB::doc2Arr($subtypes), function ($s) {
            return !($s['disabled'] ?? false);
        });
        if (!empty($subtypes)) {
            $t = array_values($subtypes)[0]['id'] ?? $t;
        }
    }
?>

    <script>
        UPDATE = true
        ID = '<?= $form['_id'] ?>'

        <?php if ($copy) { ?>
            COPY = true;
        <?php } ?>

        $(document).ready(function() {
            togglePubType('<?= $t ?>');

        })
    </script>

<?php } elseif (isset($_GET['teaching'])) { ?>
    <script>
        $(document).ready(function() {
            togglePubType('teaching', function() {
                getTeaching('<?= $_GET['teaching'] ?>');
            });
        })
    </script>
<?php } elseif (isset($_GET['type'])) { ?>
    <script>
        $(document).ready(function() {
            togglePubType('<?= $_GET['type'] ?>');
        })
    </script>
<?php } ?>


<?php if (isset($_GET['doi'])) { ?>

    <script>
        var doi = '<?= $_GET['doi'] ?>'
        // console.log(doi);

        $('#search-doi').val(doi);
        getDOI(doi);
    </script>

<?php } else if (isset($_GET['pubmed'])) { ?>

    <script>
        var pubmed_id = '<?= $_GET['pubmed'] ?>'

        $('#search-doi').val(pubmed_id);
        getPubmed(pubmed_id);
    </script>
<?php } ?>

<!-- for selecting organisations -->


<script src="<?= ROOTPATH ?>/js/organizations.js?v=<?= CSS_JS_VERSION ?>"></script>
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
</script>

<!-- <script src="<?= ROOTPATH ?>/js/tour/add-activity.js?v=<?= CSS_JS_VERSION ?>"></script> -->