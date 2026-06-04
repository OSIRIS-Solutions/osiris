<?php
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$action = ROOTPATH . "/crud/deadlines/add";
$btn = lang('Add deadline', 'Deadline hinzufügen');
if (!empty($form ?? []) && isset($form['_id'])) {
    $action = ROOTPATH . "/crud/deadlines/update/" . $form['_id'];
    $btn = lang('Save deadline', 'Deadline speichern');
}
?>


<?php include_once BASEPATH . '/header-editor.php'; ?>

<div class="container w-600 mw-full">

    <h1>
        <i class="ph-duotone ph-flag-pennant"></i>
        <?= lang('Add deadline', 'Deadline hinzufügen') ?>
    </h1>

    <p class="text-muted">
        <?= lang('The deadline will be shown on the start page of people. It can be used to inform users about important dates, e.g. the end of a call for papers or the end of a registration period.', 'Die Deadline wird auf der Startseite der Nutzer angezeigt. Sie kann verwendet werden, um Nutzer über wichtige Termine zu informieren, z.B. das Ende einer Einreichungsfrist oder das Ende einer Registrierungsphase.') ?>
    </p>

    <form action="<?= $action ?>" method="post" id="deadline-form">

        <div class="form-group floating-form">
            <input type="text" name="values[title]" class="form-control" value="<?= e($form['title'] ?? '') ?>" placeholder="title" required>
            <label for="title" class="required"><?= lang('Title', 'Titel') ?></label>
        </div>

        <div class="form-group floating-form">
            <input type="date" name="values[date]" class="form-control" id="conference-end-date" value="<?= $form['date'] ?? '' ?>" placeholder="date">
            <label for="date" class="required"><?= lang('Deadline Date', 'Fristdatum') ?></label>
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
                <?= lang('Type', 'Typ') ?>
            </label>
        </div>

        <div class="form-group">
            <label for="description" class="floating-title"><?= lang('Description', 'Beschreibung') ?></label>

            <div class="form-group title-editor" id="description-quill"><?= $form['description'] ?? '' ?></div>
            <textarea name="values[description]" id="description" class="d-none" readonly><?= $form['description'] ?? '' ?></textarea>

            <script>
                quillEditor('description');
            </script>
        </div>



        <div class="form-group floating-form">
            <input type="url" name="values[url]" class="form-control" value="<?= e($form['url'] ?? '') ?>" placeholder="url">
            <label for="url"><?= lang('Link', 'Link') ?></label>
        </div>


        <div class="form-group">
            <b class="floating-title"><?= lang('Roles', 'Rollen') ?></b><br>
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
                <?= lang('The deadline will only be shown to users with the selected roles. If no role is selected, the deadline will be shown to all users.', 'Die Deadline wird nur Nutzern mit den ausgewählten Rollen angezeigt. Wenn keine Rolle ausgewählt ist, wird die Deadline allen Nutzern angezeigt.') ?>
            </small>
        </div>

        <button class="btn success" type="submit">
            <i class="ph ph-floppy-disk"></i>
            <?= $btn ?>
        </button>
    </form>
</div>