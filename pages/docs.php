<?php
/**
 * Page to see the documentation
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2023, Julia Koblitz
 * 
 * @link        /docs
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2023, Julia Koblitz
 * @author		Julia Koblitz <julia.koblitz@dsmz.de>
 * @license     MIT
 */
?>

<h1>
    <?= lang('Documentation', 'Dokumentation') ?>
</h1>

<!-- 
<div class="select-btns" id="select-btns">
    <a href="<?= ROOTPATH ?>/docs/add-activities" class="btn select osiris" id="poster-btn"><i class="ph-lg ph ph-plus-circle"></i><?= lang('Add activities', 'Aktivitäten hinzufügen') ?></a>
    <a href="<?= ROOTPATH ?>/docs/my-year" class="btn select osiris" id="poster-btn"><i class="ph-lg ph ph-calendar"></i><?= lang('My year', 'Mein Jahr') ?></a>
    <a href="<?= ROOTPATH ?>/docs/warnings" class="btn select osiris" id="poster-btn"><i class="ph-lg ph ph-warning"></i><?= lang('Warnings', 'Warnungen') ?></a>
    <a href="<?= ROOTPATH ?>/docs/faq" class="btn select osiris" id="publication-btn"><i class="ph ph-lg ph-chat-dots"></i>FAQ</a>

</div> -->


<div class="box p-10">
    <div class="row align-items-center">
        <div class="col flex-grow-0">
            <div class="w-100 text-center text-osiris">
                <i class="ph-3x ph ph-book-open"></i>
            </div>
        </div>
        <div class="col">
            <h4 class="title">
                <a href="<?= ROOTPATH ?>/docs/add-activities" class="link colorless">
                    <?= lang('Add activities', 'Aktivitäten hinzufügen') ?>
                </a>
            </h4>
            <p>
                <?= lang('Learn how to add and edit an activity.', 'Lerne, wie du Aktivitäten hinzufügst und bearbeitest.') ?>
            </p>
        </div>
    </div>
</div>
<div class="box p-10">
    <div class="row align-items-center">
        <div class="col flex-grow-0">
            <div class="w-100 text-center text-success">
                <i class="ph-3x ph ph-calendar"></i>
            </div>
        </div>
        <div class="col">
            <h4 class="title">
                <a href="<?= ROOTPATH ?>/docs/my-year" class="link colorless">
                    <?= lang('My year', 'Mein Jahr') ?>
                </a>
            </h4>
            <p>
                <?= lang('Learn how to check your activities and approve the past quarter.', 'Lerne, wie du Aktivitäten überprüfst und das vergangene Quartal bestätigst.') ?>
            </p>
        </div>
    </div>
</div>
<div class="box p-10">
    <div class="row align-items-center">
        <div class="col flex-grow-0">
            <div class="w-100 text-center text-danger">
                <i class="ph-3x ph ph-warning"></i>
            </div>
        </div>
        <div class="col">
            <h4 class="title">
                <a href="<?= ROOTPATH ?>/docs/warnings" class="link colorless">
                    <?= lang('Warnings', 'Warnungen') ?>
                </a>
            </h4>
            <p>
                <?= lang('Learn how to solve warnings and why we show them.', 
                'Lerne, wie du Warnungen auflöst und warum wir sie dir zeigen.') ?>
            </p>
        </div>
    </div>
</div>
<div class="box p-10">
    <div class="row align-items-center">
        <div class="col flex-grow-0">
            <div class="w-100 text-center text-muted">
                <i class="ph-3x ph ph-user-list"></i>
            </div>
        </div>
        <div class="col">
            <h4 class="title">
                <a href="<?= ROOTPATH ?>/docs/profile" class="link colorless">
                    <?= lang('Profile editing', 'Profilbearbeitung') ?>
                </a>
            </h4>
            <p>
                <?= lang('Learn how to update your profile, provide alternative names for author matching, and delegate the maintenance of your profile to someone else.', 
                'Lerne, wie du dein Profil aktualisierst, alternative Namen fürs Autoren-Matching angibst und die Pflege deines Profils an jemand anderes überträgst.') ?>
            </p>
        </div>
    </div>
</div>
<div class="box p-10">
    <div class="row align-items-center">
        <div class="col flex-grow-0">
            <div class="w-100 text-center text-muted">
                <i class="ph-3x ph ph-chat-dots"></i>
            </div>
        </div>
        <div class="col">
            <h4 class="title">
                <a href="<?= ROOTPATH ?>/docs/faq" class="link colorless">
                    FAQ
                </a>
            </h4>
            <p>
                <?= lang('Have a look at the frequently asked questions.', 'Schau dir die häufig gestellten Fragen an.') ?>
            </p>
        </div>
    </div>
</div>





<p>
    <?= lang('The following docs are currently under construction:', 'Die folgenden Docs sind zurzeit in Arbeit:') ?>
</p>

<ul class="list">
    <li><?= lang('Advanced search', 'Erweiterte Suche') ?></li>
    <li><?= lang('Download functions', 'Download-Funktionen') ?></li>
    <li><?= lang('Visualizations', 'Visualisierungen') ?></li>
    <li><?= lang('Improvement of FAQ', 'Erweiterung des FAQ') ?></li>
    <li><?= lang('Translation in english! Sorry...', 'Übersetzungen ins Englische') ?></li>
</ul>


<h2 id="presentations">
    <?= lang('Presentations', 'Präsentationen') ?>
</h2>

<h4 class="mb-0">
    <?= lang('OSIRIS presentation from the Betriebsversammlung', 'OSIRIS-Präsentation von der Betriebsversammlung') ?>
</h4>

<small class="text-muted">
    <?= lang(
        'The globally selected language affects whether you see the German or English presentation.',
        'Die global ausgewählte Sprache beeinflusst, ob du die deutsche oder englische Präsentation siehst.'
    ) ?>
</small>

<div class="box">
    <object data="<?= ROOTPATH ?>/uploads/OSIRIS_<?= lang('en', 'de') ?>.pdf" width="100%" height="500">
    </object>
</div>