<?php

/**
 * Page to edit research interests of a group
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /groups/new
 *
 * @package     OSIRIS
 * @since       1.3.5
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<div id="research-list">
    <?php
    if (isset($form['research']) && !empty($form['research'])) {

        foreach ($form['research'] as $i => $con) { ?>

            <div class="alert mb-10">
                <div class="row row-eq-spacing my-0">
                    <div class="col-md-6">
                        <h5 class="mt-0 ">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                        <div class="form-group my-10">
                            <input name="values[research][<?= $i ?>][title_de]" type="text" class="form-control large" value="<?= e($con['title_de'] ?? '') ?>" placeholder="Title">
                        </div>
                        <div class="form-group mb-0">
                            <textarea name="values[research][<?= $i ?>][info_de]" id="" cols="30" rows="5" class="form-control" value="" placeholder="Information (Markdown support)"><?= e($con['info_de'] ?? '') ?></textarea>
                            <small class="text-muted">
                                <a href="https://www.markdownguide.org/basic-syntax/" target="_blank" rel="noopener noreferrer">
                                    <?= lang('Markdown supported', 'Markdown unterstützt') ?> <i class="ph ph-info"></i>
                                </a>
                            </small>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <h5 class="mt-0 ">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                        <div class="form-group my-10">
                            <input name="values[research][<?= $i ?>][title]" type="text" class="form-control large" value="<?= e($con['title'] ?? '') ?>" placeholder="Title" required>
                        </div>
                        <div class="form-group mb-0">
                            <textarea name="values[research][<?= $i ?>][info]" id="" cols="30" rows="5" class="form-control" value="" placeholder="Information (Markdown support)" required><?= e($con['info'] ?? '') ?></textarea>
                            <small class="text-muted">
                                <a href="https://www.markdownguide.org/basic-syntax/" target="_blank" rel="noopener noreferrer">
                                    <?= lang('Markdown supported', 'Markdown unterstützt') ?> <i class="ph ph-info"></i>
                                </a>
                            </small>
                        </div>

                    </div>
                </div>
                <button class="btn danger small my-10" type="button" onclick="$(this).closest('.alert').remove()"><i class="ph ph-trash"></i></button>
            </div>
    <?php }
    } ?>

</div>