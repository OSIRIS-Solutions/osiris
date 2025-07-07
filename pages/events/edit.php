<?php
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$action = ROOTPATH . "/crud/conferences/add";
$btn = lang('Add event', 'Event hinzufügen');
if (!empty($form ?? []) && isset($form['_id'])) {
    $action = ROOTPATH . "/crud/conferences/update/" . $form['_id'];
    $btn = lang('Save event', 'Event speichern');
}
?>


<?php include_once BASEPATH . '/header-editor.php'; ?>

<div class="container w-600 mw-full">

    <h4 class="title mt-0">
        <?= lang('Add event', 'Event hinzufügen') ?>
    </h4>

    <form action="<?= $action ?>" method="post" id="conference-form">

        <div class="form-group floating-form">
            <input type="text" name="values[title]" required class="form-control" value="<?= $form['title'] ?? '' ?>" placeholder="title">
            <label for="title" class="required"><?= lang('(Short) Title', 'Kurztitel') ?></label>
        </div>
        <div class="form-group floating-form">
            <input type="text" name="values[title_full]" class="form-control" value="<?= $form['title_full'] ?? '' ?>" placeholder="title_full">
            <label for="title"><?= lang('Full Title', 'Kompletter Titel') ?></label>
        </div>

        <div class="form-group floating-form">
            <select name="values[type]" id="type" class="form-control" required>
                <?php
                $vocab = $Vocabulary->getValues('event-type');
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

        <div class="form-row row-eq-spacing">
            <div class="col floating-form">
                <input type="date" name="values[start]" required class="form-control" onchange="$('#conference-end-date').val(this.value)" value="<?= $form['start'] ?? '' ?>" placeholder="start">
                <label for="start" class="required"><?= lang('Start date', 'Anfangsdatum') ?></label>
            </div>
            <div class="col floating-form">
                <input type="date" name="values[end]" class="form-control" id="conference-end-date" value="<?= $form['end'] ?? '' ?>" placeholder="end">
                <label for="end" class="required"><?= lang('End date', 'Enddatum') ?></label>
            </div>
        </div>

        <div class="form-group floating-form">
            <input type="text" name="values[location]" required class="form-control" value="<?= $form['location'] ?? '' ?>" placeholder="location">
            <label for="location" class="required"><?= lang('Location', 'Ort') ?></label>
        </div>

        <div class="form-group floating-form">
            <input type="url" name="values[url]" class="form-control" value="<?= $form['url'] ?? '' ?>" placeholder="url">
            <label for="url"><?= lang('URL', 'URL') ?></label>
        </div>

        <?php
        $topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
        if ($topicsEnabled) {
            $Settings->topicChooser(DB::doc2Arr($form['topics'] ?? []));
        }
        ?>

        <button class="btn mb-10" type="submit"><?= $btn ?></button>
    </form>
</div>