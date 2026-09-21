<?php

/**
 * Module helper page
 * 
 * This page shows an overview of all data fields that are available in the system.
 * Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <?= lang('admin.data_field_overview') ?>
</h1>

<p>
    <?= lang('admin.this_page_shows_an_overview_of_all_data_fields_that_are_available_in_the_sy') ?>
</p>

<?php include_once BASEPATH . '/header-editor.php'; ?>

<!-- search bar -->
<input type="search" id="search" class="form-control" placeholder="<?= lang('common.search') ?>">
<br>

<table class="table" id="modules">
    <tbody>

        <?php

        // include_once BASEPATH . '/php/example-document.php';
        $Modules = new Modules();
        foreach ($Modules->all_modules as $key => $vals) {
            if ($key == 'journal') {
                // get any journal from collection
                $journal = $osiris->journals->findOne();
                $Modules->form['journal'] = $journal['journal'];
                $Modules->form['journal_id'] = strval($journal['_id']);
            } elseif ($key == 'teaching-course') {
                $module = $osiris->teaching->findOne();
                $Modules->form['module'] = $module['module'];
                $Modules->form['module_id'] = strval($module['_id']);
            } else {
                $Modules->set($vals['fields']);
            }

            $activities = $osiris->adminTypes->find([
                '$or' => [
                    ['modules' => ['$in' => [$key, $key . '*']]],
                    ['fields.id' => $key]
                ]
            ], [
                'projection' => ['icon' => 1, 'name' => 1, 'id' => 1, 'name_de' => 1, 'parent' => 1]
            ])->toArray();
        ?>
            <tr>
                <td>
                    <div class="search-text">
                        <h4 class="mt-0">
                            <?= lang($vals['name'], $vals['name_de']) ?>
                            <span class="code font-size-16 border ml-10"><?= $key ?></span>
                        </h4>
                        <p class="text-muted ">
                            <?= lang($vals['description'] ?? '', $vals['description_de'] ?? null) ?>
                        </p>
                        <p>
                            <?= lang('admin.saved_fields') ?>:
                            <?php foreach ($vals['fields'] as $f => $_) { ?>
                                <code class="badge primary"><?= $f ?></code>
                            <?php } ?>
                        </p>
                    </div>
                    <div class="<?= $key == 'event-select' ? 'w-800' : '' ?> my-10 border p-10 rounded bg-light">
                        <?php
                        $Modules->print_module($key);
                        ?>
                    </div>

                    <?php if (count($activities) > 0) { ?>
                        <?= lang('admin.this_field_is_used_in_the_following_activity_types') ?>
                        <?php foreach ($activities as $a) { ?>
                            <a href="<?= ROOTPATH ?>/admin/types/<?= $a['id'] ?>" class="badge badge-<?= $a['parent'] ?> mb-5">
                                <i class="ph ph-<?= $a['icon'] ?? 'folder-open' ?>"></i>
                                <?= lang($a['name'] ?? $a['id'], $a['name_de'] ?? null) ?>
                            </a>
                        <?php } ?>
                    <?php } else { ?>
                        <em class="text-muted">
                            <?= lang('admin.this_field_is_currently_not_used_in_any_activity_types') ?>
                        </em>
                    <?php } ?>

                </td>
            </tr>
        <?php } ?>


    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('table#modules>tbody>tr').filter(function() {
                $(this).toggle($(this).find('.search-text').text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>