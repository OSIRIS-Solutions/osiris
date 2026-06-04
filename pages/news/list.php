<?php

/**
 * News list page
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /news
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
?>
<style>
    .news-item {
        margin-bottom: 1.5rem;
        position: relative;
        padding: 0;
    }

    .news-item .title {
        margin-bottom: 0.5rem;
    }

    .news-item .card-text {
        font-size: 1.4rem;
    }

    .news-item .card-meta {
        font-size: 1.2rem;
        color: var(--muted-color);
        margin-bottom: 0;
    }

    .news-item .news-image {
        width: 100%;
        max-height: 20rem;
        object-fit: cover;
        /* Border radius minus border */
        border-radius: calc(var(--border-radius) - 1px) calc(var(--border-radius) - 1px) 0 0;
        margin-bottom: 0.5rem;
    }

    /* Flag-style badge */
    .news-item .type {
        position: absolute;
        top: 1rem;
        right: 0;
        padding: 0.25rem 0.75rem;
        border-radius: 0;
        font-size: 1.2rem;
        font-weight: bold;
        border-bottom-left-radius: 5px;
        border-top-left-radius: 5px;
    }


    <?php foreach ($Vocabulary->getValues('news-category') as $key => $val) {
        echo '.news-item .type.' . e($val['id']) . ' {
        background-color: ' . lightBackgroundColor(DB::$colors[$key], .9) . ';
        color: ' . DB::$colors[$key] . ';
    }
    ';
    } ?>
</style>

<div class="container w-800 mw-full">
    <h1>
        <i class="ph-duotone ph-megaphone"></i>
        <?= lang('News', 'Nachrichten'); ?>
    </h1>

    <?php if ($Settings->hasPermission('news.edit')) { ?>
        <div class="btn-toolbar mb-20">
            <a href="<?= ROOTPATH ?>/news/add" class="btn primary">
                <i class="ph ph-plus"></i>
                <?= lang('Create news item', 'Nachricht erstellen'); ?>
            </a>
        </div>
    <?php } ?>

    <?php foreach ($osiris->news->find([], ['sort' => ['date' => -1]]) as $news) { ?>
        <div class="card news-item">

            <?php if (isset($news['image'])) {
                DB::printLogo($news, 'news-image img-fluid');
            } ?>
            <div class="badge type <?= $news['type'] ?? 'other' ?>"><?= $Vocabulary->getValue('news-category', $news['type'] ?? 'other') ?></div>
            <div class="content">
                <h2 class="title">
                    <a href="<?= ROOTPATH ?>/news/view/<?= e($news['_id']) ?>">
                        <?= e(lang($news['title'] ?? '', $news['title_de'] ?? null)) ?>
                    </a>
                </h2>
                <p class="card-text">
                    <?= e(lang($news['teaser'] ?? null, $news['teaser_de'] ?? null)) ?>
                </p>
                <p class="card-meta">
                    <span>

                        <?php
                        // if in the future
                        if (strtotime($news['date']) > time()) { ?>
                            <span class="badge signal">
                                <?= lang('Scheduled', 'Geplant') ?>
                            </span>
                        <?php } else { ?>
                            <?= lang('Published', 'Veröffentlicht'); ?>
                        <?php } ?>
                        <?= lang('on', 'am') ?>
                        <?= date('d.m.Y', strtotime($news['date'])) ?>

                    </span>
                    <?php if (isset($news['created_by'])) { ?>
                        &#x2219;
                        <span>
                            <?= lang('by', 'von'); ?>
                            <a href="<?= ROOTPATH ?>/profile/<?= e($news['created_by']) ?>"><?= e($DB->getNameFromId($news['created_by'])) ?></a>
                        </span>
                    <?php } ?>
                    <?php if ($news['visibility'] == 'public') { ?>
                        &#x2219;
                        <?= lang('Public', 'Öffentlich') ?>
                    <?php } elseif ($news['visibility'] == 'internal') { ?>
                        &#x2219;
                        <?= lang('Internal', 'Intern') ?>
                    <?php } else { ?>
                    <?php } ?>
                </p>
            </div>
        </div>
    <?php } ?>

</div>