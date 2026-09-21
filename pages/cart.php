<?php

/**
 * Page to see and edit the download cart
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /cart
 *
 * @package OSIRIS
 * @since 1.0 
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
$cart = readCart();
?>

<div class="container">
    <h1>
        <i class="ph-duotone ph-basket"></i>
        <?= lang('documents.download_collection') ?>
    </h1>

    <?php if (empty($cart)) { ?>

        <div class="alert info">
            <h4 class="title">
                <?= lang('documents.your_collection_is_empty') ?>
            </h4>
            <?= lang('documents.you_can_add_activities_to_your_collection_by_clicking_the_basket_icon_on_th') ?>
        </div>

    <?php
        echo '</div>';
        return;
    } ?>


    <a href="<?= ROOTPATH ?>/cart?empty=1" class="btn float-right">
        <i class="ph-duotone ph-trash text-danger"></i>
        <?= lang('documents.empty_collection') ?>
    </a>

    <form action="<?= ROOTPATH ?>/download" method="post">
        <input type="hidden" name="cart" value="1">
        <p>
            <?= lang('documents.the_following_activities_are_in_your_collection') ?>
        </p>
        <table class="table sm mb-20">
            <?php foreach ($cart as $id) {
                $mongo_id = $DB->to_ObjectID($id);
                $doc = $osiris->activities->findOne(['_id' => $mongo_id], ['projection' => ['rendered' => 1]]);
                if (empty($doc)) {
                    // remove non-existing document from cart
            ?>
                    <script>
                        addToCart(null, '<?= $id ?>');
                    </script>
                <?php
                    continue;
                }
                ?>
                <tr>
                    <td>
                        <span class='mr-10'><?= $doc['rendered']['icon'] ?></span>
                        <?= $doc['rendered']['web'] ?>
                    </td>
                    <td>
                        <button class="btn link small" type="button" onclick="addToCart(null, '<?= $id ?>')"><i class="ph ph-x"></i></button>
                    </td>
                </tr>
            <?php } ?>

        </table>


        <div class="form-group">

            <?= lang('common.highlight') ?>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="highlight" id="highlight-user" value="user" checked="checked">
                <label for="highlight-user"><?= lang('common.me') ?></label>
            </div>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="highlight" id="highlight-aoi" value="aoi">
                <label for="highlight-aoi"><?= $Settings->get('affiliation') ?><?= lang('common.authors') ?></label>
            </div>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="highlight" id="highlight-none" value="">
                <label for="highlight-none"><?= lang('common.none_download') ?></label>
            </div>

        </div>


        <div class="form-group">

            <?= lang('common.file_format') ?>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="format" id="format-word" value="word" checked="checked">
                <label for="format-word">Word</label>
            </div>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="format" id="format-bibtex" value="bibtex">
                <label for="format-bibtex">BibTeX</label>
            </div>

        </div>



        <button class="btn secondary">Download</button>
    </form>
</div>