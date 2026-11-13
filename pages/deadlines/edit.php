<?php
require_once BASEPATH . '/php/DeadlineForms.php';

$action = ROOTPATH . "/crud/deadlines/add";
$btn = lang('deadlines.add_deadline');
if (!empty($form ?? []) && isset($form['_id'])) {
    $action = ROOTPATH . "/crud/deadlines/update/" . $form['_id'];
    $btn = lang('deadlines.save_deadline');
}
?>


<?php include_once BASEPATH . '/header-editor.php'; ?>

<div class="container w-600 mw-full">

    <h1>
        <i class="ph-duotone ph-flag-pennant"></i>
        <?= isset($form['_id']) ? lang('deadlines.edit_deadline') : lang('deadlines.add_deadline') ?>
    </h1>

    <p class="text-muted">
        <?= lang('deadlines.the_deadline_will_be_shown_on_the_start_page_of_people_it_can_be_used_to_in') ?>
    </p>

    <form action="<?= $action ?>" method="post" id="deadline-form">

        <div id="data-modules" class="row row-eq-spacing">
            <?php DeadlineForms::render($osiris, $Settings, DB::doc2Arr($form ?? [])); ?>
        </div>

        <div class="form-group">
            <b class="floating-title"><?= lang('common.roles') ?></b><br>
            <?php
            $req = $osiris->adminGeneral->findOne(['key' => 'roles']);
            $roles =  DB::doc2Arr($req['value'] ?? array('user', 'scientist', 'admin'));
            $selected = DB::doc2Arr($form['roles'] ?? []);
            $roles = array_unique(array_merge($roles, $selected));
            foreach ($roles as $role) {
                $checked = in_array($role, $selected) ? 'checked' : '';
            ?>
                <div class="pill-checkbox ">
                    <input type="checkbox" id="role-<?= e($role) ?>" value="1" name="values[roles][<?= e($role) ?>]" <?= $checked ?>>
                    <label for="role-<?= e($role) ?>"><?= e(strtoupper($role)) ?></label>
                </div>
            <?php
            }
            ?><br>
            <small class="text-muted">
                <?= lang('deadlines.the_deadline_will_only_be_shown_to_users_with_the_selected_roles_if_no_role') ?>
            </small>
        </div>

        <button class="btn success" type="submit">
            <i class="ph ph-floppy-disk"></i>
            <?= $btn ?>
        </button>
    </form>
</div>