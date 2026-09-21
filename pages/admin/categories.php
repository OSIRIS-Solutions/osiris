<?php

/**
 * Page to browse all categories
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/categories
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
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
                <?= lang('common.change_order') ?>
            </h5>

            <style>
                tr.ui-sortable-helper {
                    background-color: white;
                    border: var(--border-width) solid var(--border-color);
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
                                    <i class="ph ph-<?= $type['icon'] ?? 'folder-open' ?> mr-10"></i>
                                    <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
                <button class="btn secondary mt-20">
                    <i class="ph ph-check"></i>
                    <?= lang('action.submit') ?>
                </button>
            </form>
            <?php include_once BASEPATH . '/header-editor.php'; ?>
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
    <i class="ph-duotone ph-gear"></i>
    <?= lang('admin.activity_categories') ?>
</h1>

<div class="btn-toolbar">
    <a class="btn primary" href="<?= ROOTPATH ?>/admin/categories/new">
        <i class="ph ph-plus-circle"></i>
        <?= lang('common.add_category') ?>
    </a>
    <a href="<?= ROOTPATH ?>/admin/doi-mappings" class="btn primary">
        <i class="ph ph-link-simple"></i>
        <?= lang('admin.doi_mappings') ?>
    </a>
    <div class="dropdown">
        <button class="btn" data-toggle="dropdown" type="button" id="rerender" aria-haspopup="true" aria-expanded="false">
            <i class="ph ph-gear"></i>
            Rerender <i class="ph ph-caret-down"></i>
        </button>
        <div class="dropdown-menu w-400" aria-labelledby="rerender">
            <div class="content">
                <?= lang('admin.in_case_some_activities_are_not_looking_right_or_if_you_changed_something_y') ?>
                <a class="btn block primary" href="<?= ROOTPATH ?>/rerender">Rerender now!</a>

                <small class="text-muted">
                    <?= lang('admin.this_won_t_change_any_data_it_will_only_fix_the_displaying_of_data') ?>
                </small>
            </div>
        </div>
    </div>
    <a class="btn ml-auto" href="#order">
        <i class="ph ph-list-numbers"></i>
        <?= lang('common.change_order') ?>
    </a>
</div>

<?php
foreach ($Categories->categories as $type) {
    ?>
    <div class="box px-20 py-10 mb-10 adjust-color-<?= $type['id'] ?>">
        <h3 class="title text-primary">
            <i class="ph ph-<?= $type['icon'] ?? 'folder-open' ?> mr-10"></i>
            <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
        </h3>
        <a href="<?= ROOTPATH ?>/admin/categories/<?= $type['id'] ?>" class="btn filled primary">
            <i class="ph ph-edit"></i>
            <?= lang('action.edit') ?>
        </a>
        <div class="d-flex align-items-baseline flex-wrap">
            <h5><?= lang('common.types') ?>:</h5>
            <?php
            $children = $osiris->adminTypes->find(['parent' => $type['id']], ['sort' => ['order' => 1]]);
            foreach ($children as $subtype) { ?>
                <a class="btn small ml-10 text-primary" href="<?= ROOTPATH ?>/admin/types/<?= $subtype['id'] ?>">
                    <i class="ph ph-<?= $subtype['icon'] ?? 'folder-open' ?>"></i>
                    <?= lang($subtype['name'], $subtype['name_de'] ?? $subtype['name']) ?>
                </a>
            <?php } ?>
            <a class="btn small ml-10" href="<?= ROOTPATH ?>/admin/types/new?parent=<?= $type['id'] ?>">
                <i class="ph ph-plus-circle"></i>
                <span class="sr-only">
                    <?= lang('common.add_subtype') ?>
                </span>
            </a>
        </div>
    </div>
<?php } ?>