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
        <div class="custom-checkbox mb-10">
            <input type="hidden" name="configuration[outlets][<?= $key ?>][active]" value="0">
            <input type="checkbox" id="outlet-<?= $key ?>-active" name="configuration[outlets][<?= $key ?>][active]" value="1" <?= !empty($outlet['active']) ? 'checked' : '' ?>>
            <label for="outlet-<?= $key ?>-active"><?= lang('Active — available for new requests', 'Aktiv – für neue Anfragen auswählbar') ?></label>
        </div>
        <div class="custom-checkbox">
            <input type="hidden" name="configuration[outlets][<?= $key ?>][portfolio]" value="0">
            <input type="checkbox" id="outlet-<?= $key ?>-portfolio" name="configuration[outlets][<?= $key ?>][portfolio]" value="1" <?= !empty($outlet['portfolio']) ? 'checked' : '' ?>>
            <label for="outlet-<?= $key ?>-portfolio"><?= lang('Include approved public contributions in Portfolio output', 'Freigegebene öffentliche Beiträge in die Portfolio-Ausgabe aufnehmen') ?></label>
        </div>
        <?php if ($saved) { ?>
            <p class="text-muted font-size-12 mb-0"><?= lang('ID', 'Kennung') ?>: <code><?= $text('id') ?></code></p>
        <?php } ?>
    </div>
<?php }; ?>

<div class="container w-800 mw-full">
    <h1><i class="ph-duotone ph-newspaper" aria-hidden="true"></i> <?= lang('News outlets', 'News-Outlets') ?></h1>
    <p><?= lang('Define the outlets for editorial news contributions and select which sources may be included in Portfolio output.', 'Lege die Outlets für redaktionelle News-Beiträge fest und wähle aus, welche Quellen in die Portfolio-Ausgabe aufgenommen werden dürfen.') ?></p>

    <form action="<?= ROOTPATH ?>/crud/admin/news" method="post" id="news-outlets-form">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['news_outlets_csrf']) ?>">
        <input type="hidden" name="revision" value="<?= e($revision) ?>">
        <div class="box padded">
            <h2 class="title"><?= lang('Original contribution', 'Originalbeitrag') ?></h2>
            <p><?= lang('The original news remains the contribution maintained by its author and appears internally in OSIRIS as usual. It is always available, independently of the editorial outlets.', 'Die ursprüngliche News bleibt der von der Autorin oder dem Autor gepflegte Beitrag und erscheint wie gewohnt intern in OSIRIS. Sie ist unabhängig von den redaktionellen Outlets immer verfügbar.') ?></p>
            <div class="custom-checkbox">
                <input type="hidden" name="configuration[original_portfolio]" value="0">
                <input type="checkbox" id="original-portfolio" name="configuration[original_portfolio]" value="1" <?= !empty($configuration['original_portfolio']) ? 'checked' : '' ?>>
                <label for="original-portfolio"><?= lang('Include public original contributions in Portfolio output', 'Öffentliche Originalbeiträge in die Portfolio-Ausgabe aufnehmen') ?></label>
            </div>
        </div>

        <h2><?= lang('Editorial outlets', 'Redaktionelle Outlets') ?></h2>
        <p class="text-muted"><?= lang('For example: website, LinkedIn or newsletter. Use the arrows to change the order. Saved outlets can be deactivated; existing contributions are retained.', 'Zum Beispiel: Webseite, LinkedIn oder Newsletter. Mit den Pfeilen änderst du die Reihenfolge. Gespeicherte Outlets können deaktiviert werden; vorhandene Beiträge bleiben erhalten.') ?></p>
        <div id="news-outlets">
            <?php foreach ($configuration['outlets'] as $index => $outlet) $renderOutlet($outlet, (string) $index); ?>
        </div>
        <p class="text-muted" id="news-outlets-empty" <?= empty($configuration['outlets']) ? '' : 'hidden' ?>><?= lang('No editorial outlets have been configured yet.', 'Es wurden noch keine redaktionellen Outlets eingerichtet.') ?></p>
        <button type="button" class="btn" id="add-news-outlet"><i class="ph ph-plus" aria-hidden="true"></i> <?= lang('Add outlet', 'Outlet hinzufügen') ?></button>

        <div class="alert primary mt-20">
            <?= lang('Portfolio selection does not publish a news item. Original contributions must also be explicitly public. Output of editorial outlet texts will follow with the editorial workflow. Links to people, activities and other content are shared by the whole news item.', 'Die Portfolio-Auswahl veröffentlicht keine News. Originalbeiträge müssen zusätzlich ausdrücklich öffentlich sein. Die Ausgabe redaktioneller Outlet-Texte folgt mit dem Redaktionsworkflow. Verknüpfungen zu Personen, Aktivitäten und weiteren Inhalten gelten gemeinsam für die gesamte News.') ?>
        </div>
        <button type="submit" class="btn primary"><i class="ph ph-floppy-disk" aria-hidden="true"></i> <?= lang('Save', 'Speichern') ?></button>
        <a class="btn" href="<?= ROOTPATH ?>/admin/news"><?= lang('Reload', 'Neu laden') ?></a>
    </form>
</div>

<template id="news-outlet-template">
    <?php $renderOutlet(['active' => true], '__KEY__'); ?>
</template>
<script src="<?= ROOTPATH ?>/js/news-outlets.js?v=<?= OSIRIS_BUILD ?>"></script>
