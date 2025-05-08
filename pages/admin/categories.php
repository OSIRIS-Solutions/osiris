<?php

/**
 * Page to browse all categories
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/categories
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<div class="modal" id="order" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title">
                <i class="ph ph-list-numbers"></i>
                <?= lang('Change order', 'Reihenfolge ändern') ?>
            </h5>

            <style>
                tr.ui-sortable-helper {
                    background-color: white;
                    border: 1px solid var(--border-color);
                }
            </style>

            <form action="<?= ROOTPATH ?>/crud/categories/update-order" method="post">
                <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/categories">

                <table class="table w-auto">
                    <tbody id="authors">
                        <?php foreach ($Categories->categories as $type) { ?>
                            <tr>
                                <td class="w-50">
                                    <i class="ph ph-dots-six-vertical text-muted handle cursor-pointer"></i>
                                </td>
                                <td style="color: <?= $type['color'] ?? 'inherit' ?>">
                                    <input type="hidden" name="order[]" value="<?= $type['id'] ?>">
                                    <i class="ph ph-<?= $type['icon'] ?? 'placeholder' ?> mr-10"></i>
                                    <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
                <button class="btn secondary mt-20">
                    <i class="ph ph-check"></i>
                    <?= lang('Submit', 'Bestätigen') ?>
                </button>
            </form>
            <script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>
            <script>
                $(document).ready(function() {
                    $('#authors').sortable({
                        handle: ".handle",
                        // change: function( event, ui ) {}
                    });
                })
            </script>


        </div>
    </div>
</div>

<h1>
    <i class="ph ph-gear"></i>
    <?= lang('Categories', 'Kategorien') ?>
</h1>

<div class="btn-toolbar">
    <a class="btn" href="<?= ROOTPATH ?>/admin/categories/new">
        <i class="ph ph-plus-circle"></i>
        <?= lang('Add category', 'Kategorie hinzufügen') ?>
    </a>
    <div class="dropdown">
        <button class="btn" data-toggle="dropdown" type="button" id="rerender" aria-haspopup="true" aria-expanded="false">
            <i class="ph ph-gear"></i>
            Rerender <i class="ph ph-caret-down"></i>
        </button>
        <div class="dropdown-menu w-400" aria-labelledby="rerender">
            <div class="content">
                <?= lang('In case some activities are not looking right or if you changed something, you can trigger a complete rerendering by clicking the button below:', 'Falls einige Aktivitäten seltsam aussehen, sich die URL eures OSIRIS geändert hat oder ihr einfach Templates angepasst habt, könnt ihr hier ein komplettes neu-rendern der Daten einleiten:') ?>
                <a class="btn block primary" href="<?=ROOTPATH?>/rerender">Rerender now!</a>

                <small class="text-muted">
                    <?=lang('This won\'t change any data. It will only fix the displaying of data.', 'Dies ändert keine Daten, sondern repariert nur die Anzeige der Daten.')?>
                </small>
            </div>
        </div>
    </div>
    <a class="btn ml-auto" href="#order">
        <i class="ph ph-list-numbers"></i>
        <?= lang('Change order', 'Reihenfolge ändern') ?>
    </a>
</div>

<?php
foreach ($Categories->categories as $type) { ?>
    <div class="box px-20 py-10 mb-10">
        <h3 class="title" style="color: <?= $type['color'] ?? 'inherit' ?>">
            <i class="ph ph-<?= $type['icon'] ?? 'placeholder' ?> mr-10"></i>
            <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
        </h3>
        <a href="<?= ROOTPATH ?>/admin/categories/<?= $type['id'] ?>">
            <?= lang('Edit', 'Bearbeiten') ?>
        </a>

        <hr>
        <h5><?= lang('Types', 'Typen') ?>:</h5>
        <ul class="horizontal mb-0">
            <?php
            $children = $osiris->adminTypes->find(['parent' => $type['id']], ['sort' => ['order' => 1]]);
            foreach ($children as $subtype) { ?>
                <li>
                    <a href="<?= ROOTPATH ?>/admin/types/<?= $subtype['id'] ?>">
                        <i class="ph ph-<?= $subtype['icon'] ?? 'placeholder' ?>"></i>
                        <?= lang($subtype['name'], $subtype['name_de'] ?? $subtype['name']) ?>
                    </a>
                </li>
            <?php } ?>
            <li>
                <a class="btn" href="<?= ROOTPATH ?>/admin/types/new?parent=<?= $type['id'] ?>">
                    <i class="ph ph-plus-circle"></i>
                    <span class="sr-only">
                        <?= lang('Add subtype', 'Neuen Typ hinzufügen') ?>
                    </span>
                </a>
            </li>
        </ul>
    </div>
<?php } ?>