<?php

/**
 * Page to add and edit categories
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/categories/new
 * @link        /admin/categories/<id>
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$form = $form ?? array();

$color = $form['color'] ?? '#000000';
$member = 0;

$id = $form['id'] ?? null;

$formaction = ROOTPATH;
if (!empty($form) && isset($form['id'])) {
    $formaction .= "/crud/categories/update/" . $form['_id'];
    $btntext = '<i class="ph ph-check"></i> ' . lang('action.update');
    $url = ROOTPATH . "/admin/categories/" . $id;
    $title = $name;

    $member = $osiris->activities->count(['type' => $id]);
} else {
    $formaction .= "/crud/categories/create";
    $btntext = '<i class="ph ph-check"></i> ' . lang('action.save');
    $url = ROOTPATH . "/admin/categories/*";
    $title = lang('common.new_category');
}

function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return e($val);
    }
    return $val;
}

function sel($index, $value)
{
    return val($index) == $value ? 'selected' : '';
}

$children = $osiris->adminTypes->find(['parent' => $id], ['sort' => ['order' => 1]])->toArray();

$type = $form;
$t = $id;
$color = $type['color'] ?? '';
$member = $osiris->activities->count(['type' => $t]);
?>

<?php include_once BASEPATH . '/header-editor.php'; ?>

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

            <form action="<?= ROOTPATH ?>/crud/types/update-order" method="post">
                <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/categories/<?= $id ?>">

                <table class="table w-auto">
                    <tbody id="authors">
                        <?php foreach ($children as $ch) { ?>
                            <tr>
                                <td class="w-50">
                                    <i class="ph ph-dots-six-vertical text-muted handle cursor-pointer"></i>
                                </td>
                                <td style="color: <?= $color ?? 'inherit' ?>">
                                    <input type="hidden" name="order[]" value="<?= $ch['id'] ?>">
                                    <i class="ph ph-<?= $ch['icon'] ?? 'folder-open' ?> mr-10"></i>
                                    <?= lang($ch['name'], $ch['name_de'] ?? $ch['name']) ?>
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

<div class="modal" id="unique" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('common.id_must_be_unique') ?></h5>
            <p>
                <?= lang('common.each_category_and_each_activity_type_must_have_a_unique_id_with_which_it_is') ?>
            </p>
            <p>
                <?= lang('common.as_the_id_must_be_unique_the_following_previously_used_ids_and_keywords_new') ?>
            </p>
            <ul class="list" id="IDLIST">
                <?php foreach ($osiris->adminCategories->distinct('id') as $k) { ?>
                    <li><?= $k ?></li>
                <?php } ?>
                <li>new</li>
            </ul>
            <div class="text-right mt-20">
                <a href="#/" class="btn secondary" role="button"><?= lang('common.understand') ?></a>
            </div>
        </div>
    </div>
</div>



<form action="<?= $formaction ?>" method="post" id="group-form">
    <input type="hidden" class="hidden" name="redirect" value="<?= $url ?>">

    <div class="box type" id="type-<?= $t ?>" style="">

        <h4 class="header" style="background-color:<?= $color ?>20; color:<?= $color ?>">
            <?php if (!empty($type)) { ?>
                <i class="ph ph-<?= $type['icon'] ?? 'folder-open' ?> mr-10"></i>
                <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
                <?php if ($type['disabled'] ?? false) { ?>
                    <span class="badge danger ml-20">DISABLED</span>
                <?php } ?>

            <?php } else { ?>
                <?= lang('admin.new_category_of_activity_types') ?>
            <?php } ?>
        </h4>

        <div class="content">
            <input type="hidden" name="values[id]" value="<?= $t ?>">
            <input type="hidden" name="add" value="type">

            <div class="row row-eq-spacing">
                <?php if (isset($type['id'])) { ?>
                    <input type="hidden" name="original_id" value="<?= $type['id'] ?>">
                <?php } ?>

                <div class="col-sm">
                    <label for="id" class="required">ID</label>
                    <input type="text" class="form-control" name="values[id]" required value="<?= $type['id'] ?? '' ?>" data-value="<?= $type['id'] ?? '' ?>" oninput="sanitizeID(this)">
                    <small><a href="#unique"><i class="ph ph-info"></i> <?= lang('common.must_be_unqiue') ?></a></small>
                </div>
                <div class="col-sm">
                    <label for="icon" class="required element-time"><a href="https://phosphoricons.com/" class="link" target="_blank" rel="noopener noreferrer">Icon</a> </label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="values[icon]" required value="<?= $type['icon'] ?? 'folder-open' ?>" onchange="iconTest(this.value)">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="ph ph-<?= $type['icon'] ?? 'folder-open' ?>" id="test-icon"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm">
                    <label for="name_de" class="">Color</label>
                    <input type="color" class="form-control" name="values[color]" value="<?= $type['color'] ?? '' ?>">
                </div>
            </div>

            <div class="row row-eq-spacing">
                <div class="col-sm">
                    <label for="name" class="required ">Name (en)</label>
                    <input type="text" class="form-control" name="values[name]" required value="<?= $type['name'] ?? '' ?>">
                </div>
                <div class="col-sm">
                    <label for="name_de" class="">Name (de)</label>
                    <input type="text" class="form-control" name="values[name_de]" value="<?= $type['name_de'] ?? '' ?>">
                </div>
            </div>


            <div class="form-group mt-20">
                <label for="visible-role"><?= lang('admin.role_that_can_see_this_type') ?></label>
                <select class="form-control" name="values[visible_role]" id="visible-role">
                    <option value="" <?= sel('visible_role', '') ?>><?= lang('admin.all_users') ?></option>
                    <?php
                    $req = $osiris->adminGeneral->findOne(['key' => 'roles']);
                    $roles =  DB::doc2Arr($req['value'] ?? array('user', 'scientist', 'admin'));

                    foreach ($roles as $role) {
                        if ($role == 'user') continue;
                    ?>
                        <option value="<?= $role ?>" <?= sel('visible_role', $role) ?>>
                            <?= strtoupper($role) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <?php
            $upload = $type['upload'] ?? true;
            ?>
            <div class="form-group">
                <input type="hidden" name="values[upload]" value="false">
                <div class="custom-checkbox">
                    <input type="checkbox" id="upload-checkbox" value="true" name="values[upload]" <?= $upload ? 'checked' : '' ?>>
                    <label for="upload-checkbox"><?= lang('admin.upload_of_documents_possible') ?></label>
                </div>
            </div>

        </div>

        <?php if ($Settings->featureEnabled('quality-workflow')) { ?>        
        <hr>
        <div class="content">
            <!-- quality workflow -->
            <h5><?= lang('admin.quality_workflow') ?></h5>

           <?php
            $workflow = $type['workflow'] ?? false;
            $workflows = $osiris->adminWorkflows->find([], ['sort' => ['name' => 1]])->toArray();
            if (empty($workflows)) {
           ?>
                <p class="text-muted">
                    <?= lang('admin.no_workflow_defined_yet_please_create_a_workflow_first') ?>
                    <br>
                    <a href="<?= ROOTPATH ?>/admin/workflows/new"><i class="ph ph-plus-circle"></i> <?= lang('admin.create_workflow') ?></a>
                </p>
            <?php } else { ?>
                <div class="form-group">
                    <select class="form-control" name="values[workflow]" id="quality-workflow">
                        <option value="" <?= sel('workflow', '') ?>><?= lang('admin.no_workflow') ?></option>
                        <?php
                        foreach ($workflows as $wf) {
                        ?>
                            <option value="<?= $wf['id'] ?>" <?= sel('workflow', $wf['id']) ?>>
                                <?= lang($wf['name'], $wf['name_de'] ?? $wf['name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                    <small><?= lang('admin.if_a_workflow_is_selected_here_it_will_be_automatically_assigned_to_all_act') ?></small>
                </div>
            <?php } ?>
        </div>
        <?php } ?>


        <?php if (!empty($type)) { ?>
            <hr>
            <div class="content">
                <a class="btn float-right" href="#order">
                    <i class="ph ph-list-numbers"></i>
                    <?= lang('common.change_order') ?>
                </a>
                <h5><?= lang('common.types') ?>:</h5>
                <div>
                    <?php
                    foreach ($children as $subtype) { ?>
                        <a class="btn mb-5 text-<?= $type['id'] ?>" href="<?= ROOTPATH ?>/admin/types/<?= $subtype['id'] ?>">
                            <i class="ph ph-<?= $subtype['icon'] ?? 'folder-open' ?>"></i>
                            <?= lang($subtype['name'], $subtype['name_de'] ?? $subtype['name']) ?>
                        </a>
                    <?php } ?>
                    <a class="btn" href="<?= ROOTPATH ?>/admin/types/new?parent=<?= $id ?>"><i class="ph ph-plus-circle"></i>
                        <?= lang('common.add_subtype') ?>
                    </a>

                </div>
            </div>
        <?php } ?>


    </div>

    <button class="btn success" id="submitBtn"><?= $btntext ?></button>

</form>

<?php if (!empty($form)) { ?>


    <?php if ($member == 0) { ?>
        <div class="alert danger mt-20">
            <form action="<?= ROOTPATH ?>/crud/categories/delete/<?= $id ?>" method="post">
                <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/categories">
                <button class="btn danger"><i class="ph ph-trash"></i> <?= lang('action.delete') ?></button>
                <span class="ml-20"><?= lang('common.warning_cannot_be_undone') ?></span>
            </form>
        </div>
    <?php } else { ?>

        <div class="alert danger mt-20">
            <?= lang('admin.can_t_delete_category_member_activities_associated', replace: ['member' => $member]) ?><br>
            <a href='<?= ROOTPATH ?>/activities/search#{"$and":[{"type":"<?= $id ?>"}]}' target="_blank" class="text-danger">
                <i class="ph ph-search"></i>
                <?= lang('common.view_activities') ?>
            </a>

        </div>
    <?php } ?>


<?php } ?>

<script src="<?= ROOTPATH ?>/js/admin-categories.js"></script>