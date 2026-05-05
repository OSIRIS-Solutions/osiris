<?php

/**
 * Manage institute settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.2
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-building" aria-hidden="true"></i>
        <?= lang('Institution', 'Einrichtung') ?>
    </h1>

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <div class="row row-eq-spacing mt-0">
            <div class="col-sm-2">
                <label for="icon" class="required"><?= lang('Abbreviation', 'Kürzel') ?></label>
                <input type="text" class="form-control" name="general[affiliation][id]" required value="<?= $affiliation['id'] ?>">
            </div>
            <div class="col-sm">
                <label for="name" class="required "><?= lang('Name', 'Name') ?></label>
                <input type="text" class="form-control" name="general[affiliation][name]" required value="<?= $affiliation['name'] ?? '' ?>">
            </div>
            <div class="col-sm">
                <label for="link" class="required "><?= lang('Link', 'Link') ?></label>
                <input type="text" class="form-control" name="general[affiliation][link]" required value="<?= $affiliation['link'] ?? '' ?>">
            </div>
        </div>
        <h5>
            <?= lang('Affiliation matching', 'Zugehörigkeitsabgleich') ?>
        </h5>
        <div class="form-group">
            <label for="regex">
                <?= lang('Regular Expression (Regex) for affiliation', 'Regulärer Ausdruck (Regex) für Zugehörigkeit') ?>
            </label>
            <input type="text" class="form-control" name="general[regex]" value="<?= $Settings->getRegex(); ?>" style="font-family: monospace;">
            <small class="text-muted">
                <?= lang('This pattern is used to match the affiliation in online repositories such as CrossRef. If you leave this empty, the institute abbreviation is used as is.', 'Dieses Muster wird verwendet, um die Zugehörigkeit in Online-Repositorien wie CrossRef abzugleichen. Wenn du dieses Feld leer lässt, wird die Institutsabkürzung unverändert verwendet.') ?>
                <?= lang('As a reference, see', 'Als Referenz, siehe') ?> <a href="https://regex101.com/" target="_blank" rel="noopener noreferrer">Regex101</a> <?= lang('with flavour JavaScript', 'mit Flavour JavaScript') ?>.
            </small>
        </div>
        <h5>
            <?= lang('External IDs', 'Externe IDs') ?>
        </h5>
        <div class="row row-eq-spacing mt-0">
            <div class="col-sm">
                <label for="openalex">
                    <?= lang('OpenAlex ID', 'OpenAlex-ID') ?>
                </label>
                <input type="text" class="form-control" name="general[affiliation][openalex]" value="<?= $affiliation['openalex'] ?? '' ?>">
                <small class="text-primary">
                    <?= lang('Needed for OpenAlex imports!', 'Diese ID ist notwendig um OpenAlex-Importe zu ermöglichen!') ?>
                </small>
            </div>
            <div class="col-sm">
                <label for="ror"><?= lang('ROR (inkl. URL)', 'ROR (inkl. URL)') ?></label>
                <input type="text" class="form-control" name="general[affiliation][ror]" value="<?= $affiliation['ror'] ?? 'https://ror.org/' ?>">
                <a class="font-size-12" href="https://ror.org/" target="_blank" rel="noopener noreferrer">
                    <?= lang('Find your ROR ID here', 'Finde deine ROR-ID hier') ?>
                </a>
            </div>
        </div>
        <h5>
            <?= lang('Location', 'Standort') ?>
        </h5>
        <div class="row row-eq-spacing mt-0">
            <div class="col-sm">
                <label for="location"><?= lang('Location', 'Ort') ?></label>
                <input type="text" class="form-control" name="general[affiliation][location]" value="<?= $affiliation['location'] ?? '' ?>">
            </div>
            <div class="col-sm">
                <label for="country"><?= lang('Country Code (2lttr)', 'Ländercode (2 Buchstaben)') ?></label>
                <input type="text" class="form-control" name="general[affiliation][country]" value="<?= $affiliation['country'] ?? 'DE' ?>">
            </div>
        </div>

        <h5>
            <?= lang('Coordinates (for map display)', 'Koordinaten (für Kartenanzeige)') ?>
        </h5>
        <div class="row row-eq-spacing mt-0">
            <div class="col-sm">
                <label for="lat"><?= lang('Latitude', 'Breitengrad') ?></label>
                <input type="float" class="form-control" name="general[affiliation][lat]" value="<?= $affiliation['lat'] ?? '' ?>">
            </div>
            <div class="col-sm">
                <label for="lng"><?= lang('Longitude', 'Längengrad') ?></label>
                <input type="float" class="form-control" name="general[affiliation][lng]" value="<?= $affiliation['lng'] ?? '' ?>">
            </div>
        </div>

        <button class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
    </form>
</div>