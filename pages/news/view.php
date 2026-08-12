<?php

/**
 * News view page
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /news/view/{id}
 *
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
$news = DB::doc2Arr($news);

$featuredCard = null;
$featured = DB::doc2Arr($news['featured'] ?? []);
if (!empty($featured['type']) && !empty($featured['id'])) {
    $featuredId = (string) $featured['id'];
    $featuredCard = [
        'type' => $featured['type'],
        'type_label' => '',
        'icon' => 'ph-star',
        'title' => '',
        'subtitle' => '',
        'url' => '',
        'text' => lang($featured['text'] ?? null, $featured['text_de'] ?? null)
    ];

    switch ($featured['type']) {
        case 'person':
            $entity = DB::is_ObjectID($featuredId)
                ? $osiris->persons->findOne(['_id' => DB::to_ObjectID($featuredId)])
                : null;
            if ($entity) {
                $featuredCard['type_label'] = lang('Person', 'Person');
                $featuredCard['icon'] = 'ph-user';
                $featuredCard['title'] = $entity['displayname'] ?? '';
                if (!empty($entity['username'])) {
                    $featuredCard['url'] = ROOTPATH . '/profile/' . $entity['username'];
                }
            }
            break;
        case 'activity':
            $entity = $DB->getActivity($featuredId);
            if ($entity) {
                $featuredCard['type_label'] = lang('Research activity', 'Forschungsaktivität');
                $featuredCard['icon'] = 'ph-article';
                $featuredCard['title'] = strip_tags($entity['rendered']['plain'] ?? $entity['title'] ?? '');
                $featuredCard['url'] = ROOTPATH . '/activities/view/' . $featuredId;
            }
            break;
        case 'project':
            $entity = DB::is_ObjectID($featuredId)
                ? $osiris->projects->findOne(['_id' => DB::to_ObjectID($featuredId)])
                : null;
            if ($entity) {
                $featuredCard['type_label'] = lang('Project', 'Projekt');
                $featuredCard['icon'] = 'ph-briefcase';
                $featuredCard['title'] = (!empty($entity['acronym']) ? $entity['acronym'] . ' – ' : '') . ($entity['name'] ?? '');
                $featuredCard['subtitle'] = lang($entity['title'] ?? null, $entity['title_de'] ?? null);
                $featuredCard['url'] = ROOTPATH . '/projects/view/' . $featuredId;
            }
            break;
        case 'event':
            $entity = DB::is_ObjectID($featuredId)
                ? $osiris->conferences->findOne(['_id' => DB::to_ObjectID($featuredId)])
                : null;
            if ($entity) {
                $featuredCard['type_label'] = lang('Event', 'Veranstaltung');
                $featuredCard['icon'] = 'ph-calendar-blank';
                $featuredCard['title'] = $entity['title'] ?? '';
                $eventDetails = [];
                if (!empty($entity['start'])) $eventDetails[] = date('d.m.Y', strtotime($entity['start']));
                if (!empty($entity['location'])) $eventDetails[] = $entity['location'];
                $featuredCard['subtitle'] = implode(' · ', $eventDetails);
                $featuredCard['url'] = ROOTPATH . '/conferences/view/' . $featuredId;
            }
            break;
        case 'infrastructure':
            $entity = $osiris->infrastructures->findOne(['id' => $featuredId]);
            if ($entity) {
                $featuredCard['type_label'] = $Settings->infrastructureLabel();
                $featuredCard['icon'] = 'ph-microscope';
                $featuredCard['title'] = $entity['name'] ?? '';
                $featuredCard['subtitle'] = $entity['subtitle'] ?? '';
                $featuredCard['url'] = ROOTPATH . '/infrastructures/view/' . ($entity['_id'] ?? $featuredId);
            }
            break;
    }

    if (empty($featuredCard['title'])) {
        $featuredCard = null;
    }
}
?>

<style>
    .news-content {
        margin-bottom: 1.5rem;
        font-size: 1.6rem;
    }

    .news-teaser {
        font-size: 1.6rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .metadata {
        border-top: 1px solid var(--border-color);
        padding-top: 1rem;
        display: flex;
        gap: 2rem;
        font-size: 1.2rem;
        color: var(--muted-color);
        margin-top: 1rem;
    }

    .news-image {
        width: 80rem;
        max-height: 30rem;
        object-fit: cover;
        border-radius: 8px;
        background-color: white;
    }

    .featured-entity {
        --featured-color: var(--primary-color);
        float: right;
        width: min(32rem, 42%);
        margin: 0 0 1.5rem 2rem;
        padding: 1.5rem;
        border: var(--border-width) solid var(--featured-color);
        border-top-width: .5rem;
        border-radius: var(--border-radius);
        background: white;
        box-shadow: 0 .4rem 1.5rem rgba(0, 0, 0, .08);
    }

    .featured-entity.person { --featured-color: #3c78a8; }
    .featured-entity.activity { --featured-color: #9f4371; }
    .featured-entity.project { --featured-color: #1c7d72; }
    .featured-entity.event { --featured-color: #7459a6; }
    .featured-entity.infrastructure { --featured-color: #d48646; }

    .featured-entity-label {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: .75rem;
        color: var(--featured-color);
        font-size: 1.2rem;
        font-weight: bold;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .featured-entity-title {
        display: block;
        color: inherit;
        font-size: 1.8rem;
        font-weight: bold;
        line-height: 1.25;
        text-decoration: none;
    }

    .featured-entity-subtitle,
    .featured-entity-text {
        margin-top: .75rem;
    }

    .featured-entity-subtitle {
        color: var(--muted-color);
        font-size: 1.3rem;
    }

    .featured-entity-text {
        padding-top: .75rem;
        border-top: 1px solid var(--border-color);
    }

    .news-content::after {
        content: '';
        display: block;
        clear: both;
    }

    @media (max-width: 600px) {
        .featured-entity {
            float: none;
            width: 100%;
            margin: 0 0 1.5rem;
        }
    }

    <?php foreach ($Vocabulary->getValues('news-category') as $key => $val) {
        echo '.type.' . e($val['id']) . ' {
        background-color: ' . DB::$colors[$key] . '20;
        color: ' . DB::$colors[$key] . ';
    }
    ';
    } ?>
</style>

<?php
if ($Settings->hasPermission('news.edit')) { ?>
    <!-- Modal for updating the profile picture -->
    <div class="modal modal-lg" id="change-picture" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content mw-full">
                <a href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>

                <h2 class="title">
                    <?= lang('Change news image', 'Nachrichtenbild ändern') ?>
                </h2>

                <p>
                    <?= lang('The image should ideally be 800 x 300 pixels. The maximum file size is 2 MB.', 'Das Bild sollte idealerweise 800 x 300 Pixel groß sein. Die maximale Dateigröße beträgt 2 MB.') ?>
                </p>

                <form action="<?= ROOTPATH ?>/crud/news/upload-picture/<?= $id ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                    <div class="custom-file mb-20" id="file-input-div">
                        <input type="file" id="profile-input" name="file" data-default-value="<?= lang("No file chosen", "Keine Datei ausgewählt") ?>" accept="image/*" required>
                        <label for="profile-input"><?= lang('Select new image', 'Wähle ein neues Bild') ?></label>
                        <br><small class="text-danger">Max. 2 MB.</small>
                    </div>

                    <script>
                        var uploadField = document.getElementById("profile-input");

                        uploadField.onchange = function() {
                            if (this.files[0].size > 2097152) {
                                toastError(lang("File is too large! Max. 2MB is supported!", "Die Datei ist zu groß! Max. 2MB werden unterstützt."));
                                this.value = "";
                            };
                        };
                    </script>
                    <button class="btn primary">
                        <i class="ph ph-upload"></i>
                        <?= lang('Upload', 'Hochladen') ?>
                    </button>
                </form>

                <hr>
                <form action="<?= ROOTPATH ?>/crud/news/upload-picture/<?= $id ?>" method="post">
                    <input type="hidden" name="delete" value="true">
                    <button class="btn danger">
                        <i class="ph ph-trash"></i>
                        <?= lang('Delete current picture', 'Aktuelles Bild löschen') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php } ?>



<div class="container w-800 mw-full">
    <div class="btn-toolbar pb-20">
        <span class="badge type <?= $news['type'] ?? 'other' ?> mr-10"><?= $Vocabulary->getValue('news-category', $news['type'] ?? 'other') ?></span>

        <?php if ($Settings->hasPermission('news.edit')) { ?>
            <a href="<?= ROOTPATH ?>/news/edit/<?= e($news['_id']) ?>" class="btn">
                <i class="ph ph-pencil"></i>
                <?= lang('Edit', 'Bearbeiten') ?>
            </a>
            <a href="#change-picture" class="btn">
                <i class="ph ph-image"></i>
                <?= lang('Change image', 'Bild ändern') ?>
            </a>
        <?php } ?>
        <?php if ($Settings->hasPermission('news.delete')) { ?>
            <form action="<?= ROOTPATH ?>/crud/news/delete" method="post" onsubmit="return confirm('<?= lang('Are you sure you want to delete this news item?', 'Sind Sie sicher, dass Sie diese Nachricht löschen möchten?') ?>');" class="d-inline ml-auto">
                <input type="hidden" name="id" value="<?= e($news['_id']) ?>">
                <button type="submit" class="btn text-danger">
                    <i class="ph ph-trash"></i>
                    <?= lang('Delete', 'Löschen') ?>
                </button>
            </form>
        <?php } ?>
    </div>

    <?php if ($Settings->featureEnabled('topics') && is_countable($news['topics'] ?? null) && count($news['topics']) > 0) {
        echo $Settings->printTopics($news['topics'] ?? [], 'mt-20');
    } ?>

    <h1>
        <i class="ph-duotone ph-megaphone"></i>
        <?= e(lang($news['title'] ?? null, $news['title_de'] ?? null)) ?>
    </h1>

    <?php if (isset($news['teaser']) || isset($news['teaser_de'])) { ?>
        <div class="news-teaser">
            <?= lang($news['teaser'] ?? null, $news['teaser_de'] ?? null) ?>
        </div>
    <?php } ?>


    <div class="image-wrapper" style="position: relative; margin-top: 1rem;">
        <?php
        DB::printLogo($news, 'news-image');
        ?>
    </div>

    <?php if ($featuredCard) { ?>
        <aside class="featured-entity <?= e($featuredCard['type']) ?>">
            <div class="featured-entity-label">
                <i class="ph-duotone <?= e($featuredCard['icon']) ?>"></i>
                <?= e($featuredCard['type_label']) ?>
            </div>

            <?php if (!empty($featuredCard['url'])) { ?>
                <a class="featured-entity-title" href="<?= e($featuredCard['url']) ?>">
                    <?= e($featuredCard['title']) ?>
                </a>
            <?php } else { ?>
                <div class="featured-entity-title"><?= e($featuredCard['title']) ?></div>
            <?php } ?>

            <?php if (!empty($featuredCard['subtitle'])) { ?>
                <div class="featured-entity-subtitle"><?= e($featuredCard['subtitle']) ?></div>
            <?php } ?>

            <?php if (!empty($featuredCard['text'])) { ?>
                <div class="featured-entity-text"><?= nl2br(e($featuredCard['text'])) ?></div>
            <?php } ?>
        </aside>
    <?php } ?>


    <div class="news-content">
        <?= lang($news['content'] ?? '', $news['content_de'] ?? null) ?>
    </div>

    <?php if (is_countable($news['persons'] ?? null) && count($news['persons']) > 0) { ?>
        <hr>
        <div class="persons">
            <h4><?= lang('Selected Persons', 'Ausgewählte Personen') ?></h4>
            <?php foreach ($news['persons'] as $i => $p) {
                $person = $osiris->persons->findOne(['_id' => DB::to_ObjectID($p)]);
                echo $person['displayname'] ?? '';
                if ($i < count($news['persons']) - 1) {
                    echo '<br>';
                }
            } ?>
        </div>
    <?php } ?>

    <?php if (is_countable($news['activities'] ?? null) && count($news['activities']) > 0) { ?>
        <hr>
        <div class="activities">
            <h4><?= lang('Selected Research Activities', 'Ausgewählte Forschungsaktivitäten') ?></h4>
            <?php foreach ($news['activities'] as $i => $a) {
                $doc = $DB->getActivity($a);
                echo $doc['rendered']['web'] ?? '';
                if ($i < count($news['activities']) - 1) {
                    echo '<br>';
                }
            } ?>
        </div>
    <?php } ?>

    <?php if (is_countable($news['projects'] ?? null) && count($news['projects']) > 0) { ?>
        <hr>
        <div class="projects">
            <h4><?= lang('Selected Projects', 'Ausgewählte Projekte') ?></h4>
            <?php

            $mongo_ids = DB::to_ObjectIDs($news['projects'] ?? []);
            $projects = $osiris->projects->find(['_id' => ['$in' => $mongo_ids]], [
                'projection' => ['_id' => 1, 'name' => 1, 'acronym' => 1, 'title' => 1, 'title_de' => 1, 'internal_number' => 1],
                'sort' => ['name' => 1]
            ])->toArray();
            foreach ($projects as $i => $p) {
                echo $p['name'];
            } ?>
        </div>
    <?php } ?>

    <?php if (is_countable($news['events'] ?? null) && count($news['events']) > 0) { ?>
        <hr>
        <div class="events">
            <h4><?= lang('Selected Events', 'Ausgewählte Veranstaltungen') ?></h4>
            <?php
            $eventIds = DB::to_ObjectIDs($news['events']);
            $events = $osiris->conferences->find(['_id' => ['$in' => $eventIds]], [
                'projection' => ['title' => 1, 'start' => 1],
                'sort' => ['start' => -1]
            ])->toArray();
            foreach ($events as $event) { ?>
                <a href="<?= ROOTPATH ?>/conferences/view/<?= e($event['_id']) ?>">
                    <?= e($event['title'] ?? '') ?>
                </a>
                <?php if (!empty($event['start'])) { ?>
                    <span class="text-muted">(<?= date('d.m.Y', strtotime($event['start'])) ?>)</span>
                <?php } ?>
                <br>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if (is_countable($news['infrastructures'] ?? null) && count($news['infrastructures']) > 0) { ?>
        <hr>
        <div class="infrastructures">
            <h4><?= lang('Selected Infrastructures', 'Ausgewählte Infrastrukturen') ?></h4>
            <?php

            $infrastructures = $osiris->infrastructures->find(['id' => ['$in' => $news['infrastructures'] ?? []]], [
                'projection' => ['_id' => 1, 'id' => 1, 'name' => 1, 'subtitle' => 1],
                'sort' => ['end_date' => -1, 'start_date' => 1]
            ])->toArray();
            foreach ($infrastructures as $i => $p) {
                echo $p['name'];
            } ?>
        </div>
    <?php } ?>


    <div class="metadata">
        <div>
            <?= lang('Published on', 'Veröffentlicht am') ?>
            <?= date('d.m.Y', strtotime($news['date'])) ?>
        </div>
        <?php if (isset($news['created_by'])) { ?>
            <div>
                <?= lang('Created by', 'Erstellt von') ?>
                <a href="<?= ROOTPATH ?>/profile/<?= e($news['created_by']) ?>"><?= e($DB->getNameFromId($news['created_by'])) ?></a>
                <?= lang('on', 'am') ?>
                <?= date('d.m.Y', strtotime($news['created'])) ?>
            </div>
        <?php } ?>

        <?php if (isset($news['updated_by'])) { ?>
            <div>
                <?= lang('Last updated by', 'Aktualisiert von') ?>
                <a href="<?= ROOTPATH ?>/profile/<?= e($news['updated_by']) ?>"><?= e($DB->getNameFromId($news['updated_by'])) ?></a>
                <?= lang('on', 'am') ?>
                <?= date('d.m.Y', strtotime($news['updated'])) ?>
            </div>
        <?php } ?>

        <?php if (($news['visibility'] ?? '') === 'public') { ?>
            <div>
                <?= lang('Public', 'Öffentlich') ?>
            </div>
        <?php } else { ?>
            <div>
                <?= lang('Internal', 'Intern') ?>
            </div>
        <?php } ?>

    </div>


    <?php if (isset($_GET['verbose'])) {
        dump($news);
    } ?>

</div>
