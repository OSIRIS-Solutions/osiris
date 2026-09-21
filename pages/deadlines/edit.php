<?php
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

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
        <?= lang('deadlines.add_deadline') ?>
    </h1>

    <p class="text-muted">
        <?= lang('deadlines.the_deadline_will_be_shown_on_the_start_page_of_people_it_can_be_used_to_in') ?>
    </p>

    <form action="<?= $action ?>" method="post" id="deadline-form">

        <div class="form-group floating-form">
            <input type="text" name="values[title]" class="form-control" value="<?= e($form['title'] ?? '') ?>" placeholder="title" required>
            <label for="title" class="required"><?= lang('common.title') ?></label>
        </div>

        <div class="form-group floating-form">
            <input type="date" name="values[date]" class="form-control" id="conference-end-date" value="<?= $form['date'] ?? '' ?>" placeholder="date">
            <label for="date" class="required"><?= lang('deadlines.deadline_date') ?></label>
        </div>

        <div class="form-group floating-form">
            <select name="values[type]" id="type" class="form-control" required>
                <?php
                $vocab = $Vocabulary->getValues('deadline-type');
                $sel = $form['type'] ?? '';
                foreach ($vocab as $v) { ?>
                    <option value="<?= $v['id'] ?>" <?= $sel == $v['id'] ? 'selected' : '' ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                <?php } ?>
            </select>
            <label for="type" class="required">
                <?= lang('common.type') ?>
            </label>
        </div>

        <div class="form-group">
            <label for="description" class="floating-title"><?= lang('common.description') ?></label>

            <div class="form-group title-editor" id="description-quill"><?= $form['description'] ?? '' ?></div>
            <textarea name="values[description]" id="description" class="d-none" readonly><?= $form['description'] ?? '' ?></textarea>

            <script>
                quillEditor('description');
            </script>
        </div>



        <div class="form-group floating-form">
            <input type="url" name="values[url]" class="form-control" value="<?= e($form['url'] ?? '') ?>" placeholder="url">
            <label for="url"><?= lang('common.link') ?></label>
        </div>


        <div class="form-group">
            <b class="floating-title"><?= lang('common.roles') ?></b><br>
            <?php
            $req = $osiris->adminGeneral->findOne(['key' => 'roles']);
            $roles =  DB::doc2Arr($req['value'] ?? array('user', 'scientist', 'admin'));
            $selected = DB::doc2Arr($form['roles'] ?? []);
            foreach ($roles as $role) {
                $checked = in_array($role, $selected) ? 'checked' : '';
            ?>
                <div class="pill-checkbox ">
                    <input type="checkbox" id="role-<?= $role ?>" value="1" name="values[roles][<?= $role ?>]" <?= $checked ?>>
                    <label for="role-<?= $role ?>"><?= strtoupper($role) ?></label>
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