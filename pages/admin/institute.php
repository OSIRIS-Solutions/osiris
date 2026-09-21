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
        <?= lang('admin.institution') ?>
    </h1>

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <div class="row row-eq-spacing mt-0">
            <div class="col-sm-2">
                <label for="icon" class="required"><?= lang('admin.abbreviation') ?></label>
                <input type="text" class="form-control" name="general[affiliation][id]" required value="<?= $affiliation['id'] ?>">
            </div>
            <div class="col-sm">
                <label for="name" class="required "><?= lang('common.name') ?></label>
                <input type="text" class="form-control" name="general[affiliation][name]" required value="<?= $affiliation['name'] ?? '' ?>">
            </div>
            <div class="col-sm">
                <label for="link" class="required "><?= lang('common.link') ?></label>
                <input type="text" class="form-control" name="general[affiliation][link]" required value="<?= $affiliation['link'] ?? '' ?>">
            </div>
        </div>
        <h2 class="font-size-18">
            <?= lang('admin.affiliation_matching') ?>
        </h2>
        <div class="form-group">
            <label for="regex">
                <?= lang('admin.regular_expression_regex_for_affiliation') ?>
            </label>
            <input type="text" class="form-control" name="general[regex]" value="<?= $Settings->getRegex(); ?>" style="font-family: monospace;">
            <small class="text-muted">
                <?= lang('admin.this_pattern_is_used_to_match_the_affiliation_in_online_repositories_such_a') ?>
                <?= lang('admin.as_a_reference_see') ?> <a href="https://regex101.com/" target="_blank" rel="noopener noreferrer">Regex101</a> <?= lang('admin.with_flavour_javascript') ?>.
            </small>
        </div>
        <h2 class="font-size-18">
            <?= lang('admin.external_ids') ?>
        </h2>
        <div class="row row-eq-spacing mt-0">
            <div class="col-sm">
                <label for="openalex">
                    <?= lang('admin.openalex_id') ?>
                </label>
                <input type="text" class="form-control" name="general[affiliation][openalex]" value="<?= $affiliation['openalex'] ?? '' ?>">
                <small class="text-primary">
                    <?= lang('admin.needed_for_openalex_imports') ?>
                </small>
            </div>
            <div class="col-sm">
                <label for="ror"><?= lang('admin.ror_inkl_url') ?></label>
                <input type="text" class="form-control" name="general[affiliation][ror]" value="<?= $affiliation['ror'] ?? 'https://ror.org/' ?>">
                <a class="font-size-12" href="https://ror.org/" target="_blank" rel="noopener noreferrer">
                    <?= lang('admin.find_your_ror_id_here') ?>
                </a>
            </div>
        </div>
        <h2 class="font-size-18">
            <?= lang('common.location_edit') ?>
        </h2>
        <div class="row row-eq-spacing mt-0">
            <div class="col-sm">
                <label for="location"><?= lang('common.location') ?></label>
                <input type="text" class="form-control" name="general[affiliation][location]" value="<?= $affiliation['location'] ?? '' ?>">
            </div>
            <div class="col-sm">
                <label for="country"><?= lang('admin.country_code_2lttr') ?></label>
                <input type="text" class="form-control" name="general[affiliation][country]" value="<?= $affiliation['country'] ?? 'DE' ?>">
            </div>
        </div>

        <h2 class="font-size-18">
            <?= lang('admin.coordinates_for_map_display') ?>
        </h2>
        <div class="row row-eq-spacing mt-0">
            <div class="col-sm">
                <label for="lat"><?= lang('common.latitude') ?></label>
                <input type="float" class="form-control" name="general[affiliation][lat]" value="<?= $affiliation['lat'] ?? '' ?>">
            </div>
            <div class="col-sm">
                <label for="lng"><?= lang('common.longitude') ?></label>
                <input type="float" class="form-control" name="general[affiliation][lng]" value="<?= $affiliation['lng'] ?? '' ?>">
            </div>
        </div>

        <button class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('action.save') ?>
        </button>
    </form>
</div>