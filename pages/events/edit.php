<?php
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$action = ROOTPATH . "/crud/conferences/add";
$btn = lang('action.add_event');
if (!empty($form ?? []) && isset($form['_id'])) {
    $action = ROOTPATH . "/crud/conferences/update/" . $form['_id'];
    $btn = lang('events.save_event');
}
?>


<?php include_once BASEPATH . '/header-editor.php'; ?>

<div class="container w-600 mw-full">

    <h1>
        <i class="ph-duotone ph-calendar-plus"></i>
        <?= lang('action.add_event') ?>
    </h1>

    <blockquote>
        <i class="ph ph-info text-primary"></i>
        <b><?= lang('common.note') ?></b>
        <?= lang('events.here_you_can_create_events_such_as_conferences_workshops_or_other_events_th') ?>
    </blockquote>

    <form action="<?= $action ?>" method="post" id="conference-form">

        <div class="form-group floating-form">
            <input type="text" name="values[title]" required class="form-control" value="<?= e($form['title'] ?? '') ?>" placeholder="title">
            <label for="title" class="required"><?= lang('common.short_title') ?></label>
        </div>
        <div class="form-group floating-form">
            <input type="text" name="values[title_full]" class="form-control" value="<?= e($form['title_full'] ?? '') ?>" placeholder="title_full">
            <label for="title"><?= lang('common.full_title') ?></label>
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

        <div class="form-row row-eq-spacing">
            <div class="col floating-form">
                <input type="date" name="values[start]" required class="form-control" onchange="$('#conference-end-date').val(this.value)" value="<?= $form['start'] ?? '' ?>" placeholder="start">
                <label for="start" class="required"><?= lang('common.start_date') ?></label>
            </div>
            <div class="col floating-form">
                <input type="date" name="values[end]" class="form-control" id="conference-end-date" value="<?= $form['end'] ?? '' ?>" placeholder="end">
                <label for="end" class="required"><?= lang('common.end_date') ?></label>
            </div>
        </div>


        <div class="form-row row-eq-spacing">
            <div class="col floating-form">
                <input type="text" name="values[location]" required class="form-control" value="<?= e($form['location'] ?? '') ?>" placeholder="location">
                <label for="location" class="required"><?= lang('common.location') ?></label>
            </div>
            <div class="col floating-form">
                <select name="values[country]" class="form-control">
                    <option value=""><?= lang('common.select_country') ?></option>
                    <?php
                    $c = $form['country'] ?? '';
                    foreach ($DB->getCountries(lang('common.field_name_language')) as $key => $value) { ?>
                        <option value="<?= $key ?>" <?= $c == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php } ?>
                </select>
                <label for="country"><?= lang('common.country') ?></label>
            </div>
        </div>

        <div class="form-group floating-form">
            <input type="url" name="values[url]" class="form-control" value="<?= e($form['url'] ?? '') ?>" placeholder="url">
            <label for="url"><?= lang('common.url') ?></label>
        </div>

        <?php if ($Settings->featureEnabled('topics') && $osiris->topics->count() > 0) {
            $Settings->topicChooser($form['topics'] ?? []);
        } ?>

        <?php if ($Settings->featureEnabled('tags') && $Settings->hasPermission('events.tags')) {
            $Settings->tagChooser($form['tags'] ?? []);
        } ?>


        <?php if ($Settings->featureEnabled('portal')) { ?>
            <div class="form-group">
            <b>
                <?= lang('common.portal_settings') ?>
            </b>
                <?php
                $public = $form['public'] ?? true;
                ?>
                <input type="hidden" name="values[public]" value="false">
                <div class="custom-checkbox">
                    <input type="checkbox" id="public" name="values[public]" <?= ($public) ? 'checked' : '' ?> value="true">
                    <label for="public">
                        <?= lang('events.show_this_event_in_the_public_portfolio') ?>
                    </label>
                </div>
            </div>
        <?php } ?>


        <button class="btn mb-10" type="submit"><?= $btn ?></button>
    </form>
</div>