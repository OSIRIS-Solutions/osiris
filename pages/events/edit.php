<?php
$action = ROOTPATH . "/crud/conferences/add";
if (!empty($form ?? []) && isset($form['_id'])) {
    $action = ROOTPATH . "/crud/conferences/update/".$form['_id'];
} 
?>


<script src="<?= ROOTPATH ?>/js/quill.min.js?v=<?= CSS_JS_VERSION ?>"></script>

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

        <div class="form-group">
            <label for="description" class="floating-title"><?= lang('Description', 'Beschreibung') ?></label>

            <div class="form-group title-editor" id="description-quill"><?= $form['description'] ?? '' ?></div>
            <input type="text" class="form-control hidden" name="values[description]" id="description" value="<?= $form['description'] ?? '' ?>">

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

        <button class="btn mb-10" type="submit"><?= lang('Add event', 'Event hinzufügen') ?></button>
    </form>
</div>