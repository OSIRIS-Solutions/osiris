<?php

/** News outlet settings, using the standard administration form components. */
$renderOutlet = static function (array $outlet, string $key) {
    $value = static function ($field, $language = null) use ($outlet) {
        $value = $language === null ? ($outlet[$field] ?? '') : ($outlet[$field][$language] ?? '');
        return is_string($value) ? $value : '';
    };
    $text = static fn($field, $language = null) => e($value($field, $language));
    $title = lang($value('name', 'en'), $value('name', 'de') ?: null);
    $saved = !empty($outlet['id']);
?>
    <div class="box padded" data-outlet data-outlet-language="<?= lang('en', 'de') ?>">
        <div class="d-flex justify-content-between align-items-center mb-10">
            <h3 class="title m-0" data-outlet-title><?= e($title ?: lang('New outlet', 'Neues Outlet')) ?></h3>
            <div class="btn-group">
                <button type="button" class="btn small" data-outlet-up aria-label="<?= lang('Move up', 'Nach oben verschieben') ?>" title="<?= lang('Move up', 'Nach oben verschieben') ?>"><i class="ph ph-arrow-up" aria-hidden="true"></i></button>
                <button type="button" class="btn small" data-outlet-down aria-label="<?= lang('Move down', 'Nach unten verschieben') ?>" title="<?= lang('Move down', 'Nach unten verschieben') ?>"><i class="ph ph-arrow-down" aria-hidden="true"></i></button>
                <?php if (!$saved) { ?>
                    <button type="button" class="btn small danger" data-outlet-remove aria-label="<?= lang('Remove new outlet', 'Neues Outlet entfernen') ?>" title="<?= lang('Remove new outlet', 'Neues Outlet entfernen') ?>"><i class="ph ph-trash" aria-hidden="true"></i></button>
                <?php } ?>
            </div>
        </div>
        <input type="hidden" name="configuration[outlets][<?= $key ?>][id]" value="<?= $text('id') ?>">
        <div class="row row-eq-spacing">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="outlet-<?= $key ?>-name"><?= lang('Name (English)', 'Name (Englisch)') ?> <span class="text-danger">*</span></label>
                    <input class="form-control" id="outlet-<?= $key ?>-name" name="configuration[outlets][<?= $key ?>][name][en]" value="<?= $text('name', 'en') ?>" maxlength="120" required data-outlet-name="en">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="outlet-<?= $key ?>-name-de"><?= lang('German name (optional)', 'Deutscher Name (optional)') ?></label>
                    <input class="form-control" id="outlet-<?= $key ?>-name-de" name="configuration[outlets][<?= $key ?>][name][de]" value="<?= $text('name', 'de') ?>" maxlength="120" data-outlet-name="de">
                </div>
            </div>
        </div>
        <div class="row row-eq-spacing">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="outlet-<?= $key ?>-description"><?= lang('English description (optional)', 'Englische Beschreibung (optional)') ?></label>
                    <textarea class="form-control" id="outlet-<?= $key ?>-description" name="configuration[outlets][<?= $key ?>][description][en]" rows="2" maxlength="500"><?= $text('description', 'en') ?></textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="outlet-<?= $key ?>-description-de"><?= lang('German description (optional)', 'Deutsche Beschreibung (optional)') ?></label>
                    <textarea class="form-control" id="outlet-<?= $key ?>-description-de" name="configuration[outlets][<?= $key ?>][description][de]" rows="2" maxlength="500"><?= $text('description', 'de') ?></textarea>
                </div>
            </div>
        </div>
        <fieldset class="form-group">
            <legend><?= lang('Editorial text fields', 'Redaktionelle Textfelder') ?></legend>
            <p class="text-muted"><?= lang('Select the fields available to editors for this outlet. Existing texts are retained when a field is hidden.', 'Wähle aus, welche Felder die Redaktion für dieses Outlet bearbeiten kann. Bereits gespeicherte Texte bleiben beim Ausblenden eines Feldes erhalten.') ?></p>
            <input type="hidden" name="configuration[outlets][<?= $key ?>][fields]" value="">
            <?php foreach (['title' => lang('Title', 'Titel'), 'teaser' => lang('Teaser', 'Teaser'), 'content' => lang('Contribution', 'Beitrag')] as $field => $label) { ?>
                <div class="custom-checkbox mb-10">
                    <input type="checkbox" id="outlet-<?= $key ?>-field-<?= $field ?>" name="configuration[outlets][<?= $key ?>][fields][]" value="<?= $field ?>" <?= in_array($field, NewsOutlets::fields($outlet), true) ? 'checked' : '' ?>>
                    <label for="outlet-<?= $key ?>-field-<?= $field ?>"><?= $label ?></label>
                </div>
            <?php } ?>
        </fieldset>
        <h4><?= lang('Availability and output', 'Verfügbarkeit und Ausgabe') ?></h4>
        <div class="custom-checkbox mb-10">
            <input type="hidden" name="configuration[outlets][<?= $key ?>][active]" value="0">
            <input type="checkbox" id="outlet-<?= $key ?>-active" name="configuration[outlets][<?= $key ?>][active]" value="1" <?= !empty($outlet['active']) ? 'checked' : '' ?>>
            <label for="outlet-<?= $key ?>-active"><?= lang('Active — available for new requests', 'Aktiv – für neue Anfragen auswählbar') ?></label>
        </div>
        <div class="custom-checkbox">
            <input type="hidden" name="configuration[outlets][<?= $key ?>][portfolio]" value="0">
            <input type="checkbox" id="outlet-<?= $key ?>-portfolio" name="configuration[outlets][<?= $key ?>][portfolio]" value="1" <?= !empty($outlet['portfolio']) ? 'checked' : '' ?>>
            <label for="outlet-<?= $key ?>-portfolio"><?= lang('Show released contributions in Portfolio', 'Freigegebene Fassungen in Portfolio anzeigen') ?></label>
        </div>
    </div>
<?php }; ?>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/news-workflow.css?v=<?= OSIRIS_BUILD ?>">
<div class="container w-800 mw-full news-workflow">
    <h1><i class="ph-duotone ph-newspaper" aria-hidden="true"></i> <?= lang('News outlets', 'News-Outlets') ?></h1>
    <p><?= lang('Define the outlets for editorial news contributions and select which sources may be included in Portfolio output.', 'Lege die Outlets für redaktionelle News-Beiträge fest und wähle aus, welche Quellen in die Portfolio-Ausgabe aufgenommen werden dürfen.') ?></p>

    <form action="<?= ROOTPATH ?>/crud/admin/news" method="post" id="news-outlets-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token('news_outlets')) ?>">
        <input type="hidden" name="revision" value="<?= e($revision) ?>">
        <div class="box padded">
            <h2 class="title"><?= lang('Original contribution', 'Originalbeitrag') ?></h2>
            <p><?= lang('The original news remains the contribution maintained by its author and appears internally in OSIRIS as usual. It is always available, independently of the editorial outlets.', 'Die ursprüngliche News bleibt der von der Autorin oder dem Autor gepflegte Beitrag und erscheint wie gewohnt intern in OSIRIS. Sie ist unabhängig von den redaktionellen Outlets immer verfügbar.') ?></p>
            <div class="custom-checkbox">
                <input type="hidden" name="configuration[original_portfolio]" value="0">
                <input type="checkbox" id="original-portfolio" name="configuration[original_portfolio]" value="1" <?= !empty($configuration['original_portfolio']) ? 'checked' : '' ?>>
                <label for="original-portfolio"><?= lang('Include original contributions in Portfolio output', 'Originalbeiträge in die Portfolio-Ausgabe aufnehmen') ?></label>
            </div>
        </div>

        <h2><?= lang('Editorial outlets', 'Redaktionelle Outlets') ?></h2>
        <p class="text-muted"><?= lang('For example: website, LinkedIn or newsletter. Use the arrows to change the order. Saved outlets can be deactivated; existing contributions are retained.', 'Zum Beispiel: Webseite, LinkedIn oder Newsletter. Mit den Pfeilen änderst du die Reihenfolge. Gespeicherte Outlets können deaktiviert werden; vorhandene Beiträge bleiben erhalten.') ?></p>
        <div id="news-outlets">
            <?php foreach ($configuration['outlets'] as $index => $outlet) $renderOutlet($outlet, (string) $index); ?>
        </div>
        <p class="text-muted" id="news-outlets-empty" <?= empty($configuration['outlets']) ? '' : 'hidden' ?>><?= lang('No editorial outlets have been configured yet.', 'Es wurden noch keine redaktionellen Outlets eingerichtet.') ?></p>
        <button type="button" class="btn" id="add-news-outlet"><i class="ph ph-plus" aria-hidden="true"></i> <?= lang('Add outlet', 'Outlet hinzufügen') ?></button>

        <div class="alert primary my-20">
            <?= lang('Portfolio receives the sources selected here. For new news with outlets, this replaces the individual public/internal setting. The internal-only selection always prevents Portfolio output. Older originals retain their previous release until their outlet selection is changed. Editorial outlet texts are included only after their editorial release.', 'Portfolio erhält die hier ausgewählten Quellen. Bei neuen News mit Outlets ersetzt dies die einzelne Einstellung öffentlich/intern. Die Auswahl Nur intern verhindert die Portfolio-Ausgabe immer. Ältere Originalbeiträge behalten ihre bisherige Freigabe, bis ihre Outlet-Auswahl geändert wird. Redaktionelle Outlet-Texte werden erst nach ihrer redaktionellen Freigabe ausgegeben.') ?>
        </div>
        <button type="submit" class="btn primary"><i class="ph ph-floppy-disk" aria-hidden="true"></i> <?= lang('Save', 'Speichern') ?></button>
        <a class="btn" href="<?= ROOTPATH ?>/admin/news"><?= lang('Reload', 'Neu laden') ?></a>
    </form>
</div>

<template id="news-outlet-template">
    <?php $renderOutlet(['active' => true], '__KEY__'); ?>
</template>
<script src="<?= ROOTPATH ?>/js/news-outlets.js?v=<?= OSIRIS_BUILD ?>"></script>
