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
    <div class="box padded" data-outlet data-outlet-language="<?= currentLanguage() === 'de' ? 'de' : 'en' ?>">
        <div class="d-flex justify-content-between align-items-center mb-10">
            <h3 class="title m-0" data-outlet-title><?= e($title ?: t('news.new_outlet')) ?></h3>
            <div class="btn-group">
                <button type="button" class="btn small" data-outlet-up aria-label="<?= e(t('news.move_up')) ?>" title="<?= e(t('news.move_up')) ?>"><i class="ph ph-arrow-up" aria-hidden="true"></i></button>
                <button type="button" class="btn small" data-outlet-down aria-label="<?= e(t('news.move_down')) ?>" title="<?= e(t('news.move_down')) ?>"><i class="ph ph-arrow-down" aria-hidden="true"></i></button>
                <?php if (!$saved) { ?>
                    <button type="button" class="btn small danger" data-outlet-remove aria-label="<?= t('news.remove_new_outlet') ?>" title="<?= t('news.remove_new_outlet') ?>"><i class="ph ph-trash" aria-hidden="true"></i></button>
                <?php } ?>
            </div>
        </div>
        <input type="hidden" name="configuration[outlets][<?= $key ?>][id]" value="<?= $text('id') ?>">
        <div class="row row-eq-spacing">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="outlet-<?= $key ?>-name"><?= t('news.name_english') ?> <span class="text-danger">*</span></label>
                    <input class="form-control" id="outlet-<?= $key ?>-name" name="configuration[outlets][<?= $key ?>][name][en]" value="<?= $text('name', 'en') ?>" maxlength="120" required data-outlet-name="en">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="outlet-<?= $key ?>-name-de"><?= t('news.german_name_optional') ?></label>
                    <input class="form-control" id="outlet-<?= $key ?>-name-de" name="configuration[outlets][<?= $key ?>][name][de]" value="<?= $text('name', 'de') ?>" maxlength="120" data-outlet-name="de">
                </div>
            </div>
        </div>
        <div class="row row-eq-spacing">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="outlet-<?= $key ?>-description"><?= t('news.english_description_optional') ?></label>
                    <textarea class="form-control" id="outlet-<?= $key ?>-description" name="configuration[outlets][<?= $key ?>][description][en]" rows="2" maxlength="500"><?= $text('description', 'en') ?></textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="outlet-<?= $key ?>-description-de"><?= t('news.german_description_optional') ?></label>
                    <textarea class="form-control" id="outlet-<?= $key ?>-description-de" name="configuration[outlets][<?= $key ?>][description][de]" rows="2" maxlength="500"><?= $text('description', 'de') ?></textarea>
                </div>
            </div>
        </div>
        <fieldset class="form-group">
            <legend><?= t('news.editorial_text_fields') ?></legend>
            <p class="text-muted mt-0"><?= t('news.select_the_fields_available_to_editors_for_this_outlet_existing_te') ?></p>
            <input type="hidden" name="configuration[outlets][<?= $key ?>][fields]" value="">
            <?php foreach (['title' => t('common.title'), 'teaser' => t('news.teaser'), 'content' => t('news.contribution')] as $field => $label) { ?>
                <div class="custom-checkbox mb-10">
                    <input type="checkbox" id="outlet-<?= $key ?>-field-<?= $field ?>" name="configuration[outlets][<?= $key ?>][fields][]" value="<?= $field ?>" <?= in_array($field, NewsOutlets::fields($outlet), true) ? 'checked' : '' ?>>
                    <label for="outlet-<?= $key ?>-field-<?= $field ?>"><?= $label ?></label>
                </div>
            <?php } ?>
        </fieldset>
        <h4><?= t('news.availability_and_output') ?></h4>
        <div class="custom-checkbox mb-10">
            <input type="hidden" name="configuration[outlets][<?= $key ?>][active]" value="0">
            <input type="checkbox" id="outlet-<?= $key ?>-active" name="configuration[outlets][<?= $key ?>][active]" value="1" <?= !empty($outlet['active']) ? 'checked' : '' ?>>
            <label for="outlet-<?= $key ?>-active"><?= t('news.active_available_for_new_requests') ?></label>
        </div>
        <div class="custom-checkbox">
            <input type="hidden" name="configuration[outlets][<?= $key ?>][portfolio]" value="0">
            <input type="checkbox" id="outlet-<?= $key ?>-portfolio" name="configuration[outlets][<?= $key ?>][portfolio]" value="1" <?= !empty($outlet['portfolio']) ? 'checked' : '' ?>>
            <label for="outlet-<?= $key ?>-portfolio"><?= t('news.show_released_contributions_in_portfolio') ?></label>
        </div>
    </div>
<?php }; ?>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/news-workflow.css?v=<?= OSIRIS_BUILD ?>">
<div class="container w-800 mw-full news-workflow">
    <h1><i class="ph-duotone ph-newspaper" aria-hidden="true"></i> <?= t('admin.news_outlets') ?></h1>
    <p><?= t('news.define_the_outlets_for_editorial_news_contributions_and_select_whi') ?></p>

    <form action="<?= ROOTPATH ?>/crud/admin/news" method="post" id="news-outlets-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token('news_outlets')) ?>">
        <input type="hidden" name="revision" value="<?= e($revision) ?>">
        <div class="box padded">
            <h2 class="title"><?= t('news.original_contribution') ?></h2>
            <p><?= t('news.the_original_news_remains_the_contribution_maintained_by_its_autho') ?></p>
            <div class="custom-checkbox">
                <input type="hidden" name="configuration[original_portfolio]" value="0">
                <input type="checkbox" id="original-portfolio" name="configuration[original_portfolio]" value="1" <?= !empty($configuration['original_portfolio']) ? 'checked' : '' ?>>
                <label for="original-portfolio"><?= t('news.include_original_contributions_in_portfolio_output') ?></label>
            </div>
        </div>

        <h2><?= t('news.editorial_outlets') ?></h2>
        <p class="text-muted"><?= t('news.for_example_website_linkedin_or_newsletter_use_the_arrows_to_chang') ?></p>
        <div id="news-outlets">
            <?php foreach ($configuration['outlets'] as $index => $outlet) $renderOutlet($outlet, (string) $index); ?>
        </div>
        <p class="text-muted" id="news-outlets-empty" <?= empty($configuration['outlets']) ? '' : 'hidden' ?>><?= t('news.no_editorial_outlets_have_been_configured_yet') ?></p>
        <button type="button" class="btn" id="add-news-outlet"><i class="ph ph-plus" aria-hidden="true"></i> <?= t('news.add_outlet') ?></button>

        <div class="alert primary my-20">
            <?= t('news.portfolio_receives_the_sources_selected_here_for_new_news_with_out') ?>
        </div>
        <button type="submit" class="btn primary"><i class="ph ph-floppy-disk" aria-hidden="true"></i> <?= t('action.save') ?></button>
        <a class="btn" href="<?= ROOTPATH ?>/admin/news"><?= t('news.reload') ?></a>
    </form>
</div>

<template id="news-outlet-template">
    <?php $renderOutlet(['active' => true], '__KEY__'); ?>
</template>
<script src="<?= ROOTPATH ?>/js/news-outlets.js?v=<?= OSIRIS_BUILD ?>"></script>
