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

    <blockquote>
        <i class="ph ph-info text-primary"></i>
        <b><?= lang('Note:', 'Anmerkung:') ?></b>
        <?= lang(
            'Here you can create events such as conferences, workshops or other events. These are created centrally and can be viewed by other users. It is then easy to add contributions such as presentations or posters to these events. <b>An event is not an activity!</b> It is not assigned to a person and is not a service in itself. The event is only used for the central administration of events and for linking to contributions.',
            'Hier kannst du Veranstaltungen, wie Konferenzen, Workshops oder andere Events anlegen. Diese werden zentral angelegt und können von anderen Nutzenden gesehen werden. Es ist danach einfach möglich, Beiträge wie zum Beispiel Vorträge oder Poster zu diesen Veranstaltungen hinzuzufügen. <b>Ein Event ist keine Aktivität!</b> Es wird keiner Person zugeordnet und ist auch an sich keine Leistung. Das Event dient lediglich der zentralen Verwaltung von Veranstaltungen und der Verknüpfung mit Beiträgen.'
        ) ?>
    </blockquote>

    <form action="<?= $action ?>" method="post" id="conference-form">

        <div class="form-group floating-form">
            <input type="text" name="values[title]" required class="form-control" value="<?= e($form['title'] ?? '') ?>" placeholder="title">
            <label for="title" class="required"><?= lang('(Short) Title', 'Kurztitel') ?></label>
        </div>
        <div class="form-group floating-form">
            <input type="text" name="values[title_full]" class="form-control" value="<?= e($form['title_full'] ?? '') ?>" placeholder="title_full">
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


        <div class="form-row row-eq-spacing">
            <div class="col floating-form">
                <input type="text" name="values[location]" required class="form-control" value="<?= e($form['location'] ?? '') ?>" placeholder="location">
                <label for="location" class="required"><?= lang('Location', 'Ort') ?></label>
            </div>
            <div class="col floating-form">
                <select name="values[country]" class="form-control">
                    <option value=""><?= lang('Select country', 'Land auswählen') ?></option>
                    <?php 
                    $c = $form['country'] ?? '';
                    foreach ($DB->getCountries(lang('name', 'name_de')) as $key => $value) { ?>
                        <option value="<?= $key ?>" <?= $c == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php } ?>
                </select>
                <label for="country"><?= lang('Country', 'Land') ?></label>
            </div>
        </div>

        <div class="form-group floating-form">
            <input type="url" name="values[url]" class="form-control" value="<?= e($form['url'] ?? '') ?>" placeholder="url">
            <label for="url"><?= lang('URL', 'URL') ?></label>
        </div>

        <?php if ($Settings->featureEnabled('topics') && $osiris->topics->count() > 0) {
            $Settings->topicChooser($form['topics'] ?? []);
        } ?>

        <?php if ($Settings->featureEnabled('tags') && $Settings->hasPermission('events.tags')) {
            $Settings->tagChooser($form['tags'] ?? []);
        } ?>


        <button class="btn mb-10" type="submit"><?= $btn ?></button>
    </form>
</div>