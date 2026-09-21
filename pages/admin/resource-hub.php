<?php

/**
 * Page for admin dashboard for general settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
 *
 * @package OSIRIS
 * @since 2.2.0
 * @todo This is still under implementation
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$rh = DB::doc2Arr($Settings->get('resource-hub') ?? []);
$hubIcon = $Settings->resourceHubIcon();
$cards = DB::doc2Arr($rh['cards'] ?? []);
$cardCount = count($cards);
$imageMap = DB::doc2Arr($rh['image-map'] ?? []);
$backgroundImage = DB::doc2Arr($imageMap['image'] ?? []);
$backgroundFile = (string) ($backgroundImage['file'] ?? '');
$hasBackgroundImage = preg_match('#^resource-hub/[a-f0-9]{24}\.(jpg|png|webp)$#', $backgroundFile)
    && is_file(BASEPATH . '/uploads/' . $backgroundFile);

$filesize = Settings::getMaxFileSize('10M');
?>

<style>
    #resource-hub-cards .resource-hub-card { margin-bottom: 1rem; }
    #resource-hub-cards .collapse-header { display: flex; align-items: center; gap: 1rem; }
    #resource-hub-cards .card-summary-title { flex-grow: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    #resource-hub-cards .card-handle { cursor: move; }
    .resource-hub-link { padding: 1.5rem; margin-bottom: 1rem; border: var(--border-width) solid var(--border-color); border-radius: var(--border-radius); background: var(--box-bg-color); }
    .resource-hub-link .link-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
    .resource-hub-editor .ql-editor { min-height: 12rem; }
    .resource-hub-image-preview { display: block; width: 100%; max-height: 40rem; object-fit: contain; background: var(--muted-color-very-light); border: var(--border-width) solid var(--border-color); border-radius: var(--border-radius); }
    .resource-hub-image-meta { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1rem; }
    .resource-hub-character-count { display: block; text-align: right; color: var(--muted-color); }
    @media (max-width: 575px) { #resource-hub-cards .card-summary-meta { display: none; } }
</style>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-<?= e($hubIcon) ?>"></i>
        <?= lang('admin.resource_hub_settings') ?>
    </h1>

    <?php if (!$Settings->featureEnabled('resource-hub')) { ?>
        <div class="alert signal">
            <?= lang('admin.the_resource_hub_feature_is_not_enabled_please_enable_it_in_the_features_se', replace: ['rootpath' => ROOTPATH]) ?>
        </div>
    <?php } ?>


    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post" id="resource-hub-form">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/resource-hub">

        <?php
        $label = DB::doc2Arr($rh['label'] ?? []);
        $description = DB::doc2Arr($rh['description'] ?? []);
        ?>
        <div class="box padded">
            <h2 class="title">
                <?= lang('admin.title_and_description') ?>
            </h2>
            <div class="form-group">
                <label for="resource_hub_icon"><?= lang('admin.resource_hub_icon') ?></label>
                <div class="input-group" style="max-width: 40rem;">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="ph ph-<?= e($hubIcon) ?> resource-hub-icon-preview"></i></span>
                    </div>
                    <input name="general[resource-hub][icon]" id="resource_hub_icon" type="text" class="form-control resource-hub-icon-input" value="<?= e($hubIcon) ?>" pattern="[a-z0-9-]+" list="resource-hub-icons" placeholder="link">
                </div>
                <small class="text-muted">
                    <?= lang('admin.enter_the_phosphor_icon_name_without_the_ph_prefix') ?>
                    <a href="https://phosphoricons.com/" target="_blank" rel="noopener noreferrer"><?= lang('admin.browse_icons') ?></a>
                </small>
            </div>
            <div class="row row-eq-spacing">
                <div class="col-md-6 mt-10 mt-md-0">
                    <label for="resource_hub_label" class="d-flex"><?= lang('common.label') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                    <input name="general[resource-hub][label][en]" id="resource_hub_label" type="text" class="form-control" value="<?= e($label['en'] ?? 'Resource Hub') ?>">
                    <label for="resource_hub_description" class="mt-15"><?= lang('common.short_description') ?></label>
                    <textarea name="general[resource-hub][description][en]" id="resource_hub_description" class="form-control resource-hub-description" rows="3" maxlength="200" placeholder="Briefly describe the purpose of the Resource Hub."><?= e($description['en'] ?? '') ?></textarea>
                    <small class="resource-hub-character-count"><span>0</span>/200</small>
                </div>
                <div class="col-md-6 mt-10 mt-md-0">
                    <label for="resource_hub_label_de" class="d-flex"><?= lang('common.label') ?> (Deutsch) <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                    <input name="general[resource-hub][label][de]" id="resource_hub_label_de" type="text" class="form-control" value="<?= e($label['de'] ?? 'Ressourcen-Hub') ?>">
                    <label for="resource_hub_description_de" class="mt-15"><?= lang('common.short_description') ?></label>
                    <textarea name="general[resource-hub][description][de]" id="resource_hub_description_de" class="form-control resource-hub-description" rows="3" maxlength="200" placeholder="Beschreibe kurz den Zweck des Ressourcen-Hubs."><?= e($description['de'] ?? '') ?></textarea>
                    <small class="resource-hub-character-count"><span>0</span>/200</small>
                </div>
            </div>
            <small class="text-muted">
                <?= lang('admin.plain_text_only_maximum_200_characters_per_language') ?>
            </small>
        </div>

        <div id="card-configuration" class="box padded">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-10 mb-20">
                <div>
                    <h2 class="title mt-0 mb-5"><?= lang('common.cards') ?></h2>
                    <p class="text-muted m-0">
                        <?= lang('admin.create_the_content_blocks_for_the_resource_hub_drag_the_cards_into_the_desi') ?>
                    </p>
                </div>
                <button type="button" class="btn primary flex-shrink-0" id="add-resource-hub-card">
                    <i class="ph ph-plus"></i> <?= lang('admin.add_card') ?>
                </button>
            </div>

            <div id="resource-hub-empty" class="alert signal <?= $cardCount ? 'd-none' : '' ?>">
                <div class="title"><?= lang('admin.no_cards_yet') ?></div>
                <?= lang('admin.add_the_first_card_to_start_building_your_resource_hub') ?>
            </div>

            <div id="resource-hub-cards">
                <?php foreach ($cards as $i => $rawCard) {
                    $card = DB::doc2Arr($rawCard);
                    $title = DB::doc2Arr($card['title'] ?? []);
                    $content = DB::doc2Arr($card['content'] ?? []);
                    $links = DB::doc2Arr($card['links'] ?? []);
                    $cardId = $card['id'] ?? bin2hex(random_bytes(8));
                    $icon = $card['icon'] ?? 'link';
                    $summaryTitle = lang($title['en'] ?? '', $title['de'] ?? null);
                    if (trim($summaryTitle) === '') $summaryTitle = lang('common.untitled_card');
                ?>
                    <details class="collapse-panel resource-hub-card" data-card-id="<?= e($cardId) ?>">
                        <summary class="collapse-header">
                            <i class="ph ph-dots-six-vertical text-muted card-handle" title="<?= lang('admin.drag_to_reorder') ?>"></i>
                            <i class="ph ph-<?= e($icon) ?> card-summary-icon"></i>
                            <strong class="card-summary-title"><?= e($summaryTitle) ?></strong>
                            <span class="text-muted card-summary-meta"><span class="card-link-count"><?= count($links) ?></span> <?= lang('admin.links') ?></span>
                        </summary>

                        <div class="collapse-content">
                            <input type="hidden" value="<?= e($cardId) ?>" data-card-field="id">

                            <div class="form-group">
                                <label for="resource-hub-icon-<?= $i ?>"><?= lang('admin.card_icon') ?></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="ph ph-<?= e($icon) ?> card-icon-preview"></i></span></div>
                                    <input type="text" class="form-control card-icon-input" id="resource-hub-icon-<?= $i ?>" value="<?= e($icon) ?>" pattern="[a-z0-9-]+" list="resource-hub-icons" placeholder="link" data-card-field="icon">
                                </div>
                                <small class="text-muted">
                                    <?= lang('admin.enter_the_phosphor_icon_name_without_the_ph_prefix') ?>
                                    <a href="https://phosphoricons.com/" target="_blank" rel="noopener noreferrer"><?= lang('admin.browse_icons') ?></a>
                                </small>
                            </div>

                            <div class="row row-eq-spacing my-0">
                                <div class="col-md-6">
                                    <h5 class="mt-0">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                                    <div class="form-group">
                                        <label for="resource-hub-title-en-<?= $i ?>"><?= lang('common.title') ?></label>
                                        <input type="text" class="form-control card-title-input" id="resource-hub-title-en-<?= $i ?>" value="<?= e($title['en'] ?? '') ?>" data-card-field="title.en">
                                    </div>
                                    <div class="form-group resource-hub-editor mb-0">
                                        <label><?= lang('common.content') ?></label>
                                        <div id="resource-hub-content-en-<?= $i ?>-quill"><?= $content['en'] ?? '' ?></div>
                                        <textarea id="resource-hub-content-en-<?= $i ?>" class="d-none" readonly data-card-field="content.en"><?= e($content['en'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mt-0">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                                    <div class="form-group">
                                        <label for="resource-hub-title-de-<?= $i ?>"><?= lang('common.title') ?></label>
                                        <input type="text" class="form-control card-title-input" id="resource-hub-title-de-<?= $i ?>" value="<?= e($title['de'] ?? '') ?>" data-card-field="title.de">
                                    </div>
                                    <div class="form-group resource-hub-editor mb-0">
                                        <label><?= lang('common.content') ?></label>
                                        <div id="resource-hub-content-de-<?= $i ?>-quill"><?= $content['de'] ?? '' ?></div>
                                        <textarea id="resource-hub-content-de-<?= $i ?>" class="d-none" readonly data-card-field="content.de"><?= e($content['de'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-20">
                                <div class="d-flex align-items-center justify-content-between gap-10 mb-10">
                                    <div>
                                        <h4 class="m-0"><?= lang('common.links') ?></h4>
                                        <small class="text-muted"><?= lang('admin.optional_links_displayed_on_this_card') ?></small>
                                    </div>
                                    <button type="button" class="btn small add-resource-hub-link"><i class="ph ph-plus"></i> <?= lang('admin.add_link') ?></button>
                                </div>

                                <div class="resource-hub-links">
                                    <?php foreach ($links as $rawLink) {
                                        $link = DB::doc2Arr($rawLink);
                                        $linkTitle = DB::doc2Arr($link['title'] ?? []);
                                        $linkIcon = $link['icon'] ?? 'arrow-square-out';
                                    ?>
                                        <div class="resource-hub-link">
                                            <div class="link-head">
                                                <strong><i class="ph ph-<?= e($linkIcon) ?> link-icon-preview"></i> <?= lang('common.link') ?></strong>
                                                <button type="button" class="btn link danger small remove-resource-hub-link" title="<?= lang('admin.remove_link') ?>"><i class="ph ph-trash"></i></button>
                                            </div>
                                            <div class="row row-eq-spacing my-0">
                                                <div class="col-md-6">
                                                    <label>English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                                    <input type="text" class="form-control" value="<?= e($linkTitle['en'] ?? '') ?>" placeholder="<?= lang('admin.link_title') ?>" data-link-field="title.en">
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                                    <input type="text" class="form-control" value="<?= e($linkTitle['de'] ?? '') ?>" placeholder="<?= lang('admin.link_title') ?>" data-link-field="title.de">
                                                </div>
                                            </div>
                                            <div class="row row-eq-spacing mb-0">
                                                <div class="col-md-4">
                                                    <label><?= lang('admin.icon') ?></label>
                                                    <input type="text" class="form-control link-icon-input" value="<?= e($linkIcon) ?>" pattern="[a-z0-9-]+" list="resource-hub-icons" placeholder="arrow-square-out" data-link-field="icon">
                                                </div>
                                                <div class="col-md-8">
                                                    <label><?= lang('admin.target') ?></label>
                                                    <input type="text" class="form-control link-url-input" value="<?= e($link['url'] ?? '') ?>" placeholder="https://example.org or /documents" data-link-field="url">
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="text-right mt-20 border-top pt-10">
                                <button type="button" class="btn link danger remove-resource-hub-card"><i class="ph ph-trash"></i> <?= lang('admin.delete_card') ?></button>
                            </div>
                        </div>
                    </details>
                <?php } ?>
            </div>
        </div>

        <datalist id="resource-hub-icons">
            <?php foreach (['link', 'book-open', 'books', 'file-text', 'folder-open', 'globe', 'users-three', 'chat-circle-dots', 'calendar-dots', 'graduation-cap', 'lightbulb', 'arrow-square-out'] as $suggestedIcon) { ?>
                <option value="<?= $suggestedIcon ?>">
            <?php } ?>
        </datalist>

        <div id="image-map-configuration" class="box padded">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-10 mb-20">
                <div>
                    <h2 class="title mt-0 mb-5"><?= lang('common.image_map_background') ?></h2>
                    <p class="text-muted m-0">
                        <?= lang('admin.upload_the_background_image_independently_from_the_card_configuration_then') ?>
                    </p>
                </div>
                <div class="d-flex gap-10 flex-shrink-0">
                    <?php if ($hasBackgroundImage && $cardCount > 0) { ?>
                        <a href="<?= ROOTPATH ?>/admin/resource-hub-image-map" class="btn">
                            <i class="ph ph-map-pin"></i>
                            <?= lang('admin.arrange_cards') ?>
                        </a>
                    <?php } ?>
                    <a href="#resource-hub-image-upload" class="btn primary">
                        <i class="ph ph-<?= $hasBackgroundImage ? 'arrows-clockwise' : 'upload-simple' ?>"></i>
                        <?= $hasBackgroundImage ? lang('admin.replace_image') : lang('common.upload_image') ?>
                    </a>
                </div>
            </div>

            <?php if ($hasBackgroundImage) { ?>
                <img
                    src="<?= ROOTPATH ?>/uploads/<?= e($backgroundFile) ?>?v=<?= strtotime((string) ($backgroundImage['uploaded'] ?? 'now')) ?>"
                    alt="<?= lang('admin.current_image_map_background') ?>"
                    class="resource-hub-image-preview">
                <div class="resource-hub-image-meta">
                    <span class="badge">
                        <i class="ph ph-arrows-out"></i>
                        <?= (int) ($backgroundImage['width'] ?? 0) ?> × <?= (int) ($backgroundImage['height'] ?? 0) ?> px
                    </span>
                    <span class="badge">
                        <i class="ph ph-hard-drives"></i>
                        <?= formatBytes((int) ($backgroundImage['size'] ?? 0)) ?>
                    </span>
                    <span class="badge">
                        <?= e(strtoupper(pathinfo($backgroundFile, PATHINFO_EXTENSION))) ?>
                    </span>
                </div>
            <?php } else { ?>
                <div class="alert signal mb-0">
                    <div class="title"><?= lang('admin.no_background_image_uploaded') ?></div>
                    <?= lang('admin.the_cards_view_can_already_be_used_an_image_is_only_required_for_the_option') ?>
                </div>
            <?php } ?>
        </div>


        <button type="submit" class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('action.save') ?>
        </button>
    </form>
</div>

<div class="modal" id="resource-hub-image-upload" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="resource-hub-image-upload-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="<?= lang('action.close') ?>">
                <span aria-hidden="true">&times;</span>
            </a>
            <h2 id="resource-hub-image-upload-title" class="title">
                <?= $hasBackgroundImage ? lang('admin.replace_background_image') : lang('admin.upload_background_image') ?>
            </h2>

            <p class="text-muted">
                <?= lang('admin.a_wide_landscape_image_works_best_we_recommend_an_aspect_ratio_close_to_16') ?>
            </p>

            <blockquote>
                <b><?= lang('admin.image_requirements') ?></b>
                <ul class="mb-0">
                    <li><?= lang('admin.jpeg_png_or_webp') ?></li>
                    <li><?= lang('admin.landscape_format') ?></li>
                    <li><?= lang('admin.between_1200_x_600_and_5000_x_3000_pixels') ?></li>
                    <li><?= lang('admin.maximum_15_megapixels_and_10_mb') ?></li>
                </ul>
            </blockquote>

            <form action="<?= ROOTPATH ?>/crud/admin/resource-hub/image" method="post" enctype="multipart/form-data">
                <input type="hidden" name="MAX_FILE_SIZE" value="<?= $filesize['bytes'] ?>">
                <div class="custom-file">
                    <input
                        type="file"
                        id="resource-hub-background-file"
                        name="image"
                        accept="image/jpeg,image/png,image/webp"
                        maxsize="<?= $filesize['bytes'] ?>"
                        onchange="previewResourceHubImage(this)"
                        required>
                    <label for="resource-hub-background-file"><?= lang('common.select_image') ?></label>
                </div>

                <div id="resource-hub-upload-preview" class="mt-20 d-none">
                    <img src="" alt="<?= lang('admin.preview_of_the_selected_image') ?>" class="resource-hub-image-preview">
                    <p class="text-muted mb-0 mt-5" id="resource-hub-upload-preview-meta"></p>
                </div>

                <button type="submit" class="btn primary mt-20">
                    <i class="ph ph-upload-simple"></i>
                    <?= $hasBackgroundImage ? lang('admin.replace_image') : lang('common.upload_image') ?>
                </button>
            </form>

            <?php if ($hasBackgroundImage) { ?>
                <hr>
                <form action="<?= ROOTPATH ?>/crud/admin/resource-hub/image/delete" method="post" onsubmit="return confirm('<?= e(lang('admin.remove_the_current_background_image')) ?>')">
                    <button type="submit" class="btn link danger">
                        <i class="ph ph-trash"></i>
                        <?= lang('admin.remove_current_image') ?>
                    </button>
                </form>
            <?php } ?>
        </div>
    </div>
</div>

<script>
    function previewResourceHubImage(input) {
        const file = input.files && input.files[0];
        const container = document.getElementById('resource-hub-upload-preview');
        if (!file) {
            container.classList.add('d-none');
            return;
        }

        const preview = container.querySelector('img');
        const objectUrl = URL.createObjectURL(file);
        preview.onload = function() {
            document.getElementById('resource-hub-upload-preview-meta').textContent =
                preview.naturalWidth + ' × ' + preview.naturalHeight + ' px · ' + formatFileSize(file.size);
            URL.revokeObjectURL(objectUrl);
        };
        preview.src = objectUrl;
        container.classList.remove('d-none');
    }

    function formatFileSize(bytes) {
        if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    (function() {
        let nextCardIndex = <?= max($cardCount, 1) ?>;
        const labels = <?= json_encode([
            'untitled' => lang('common.untitled_card'),
            'links' => lang('admin.links'),
            'link' => lang('common.link'),
            'linkTitle' => lang('admin.link_title'),
            'icon' => lang('admin.icon'),
            'target' => lang('admin.target'),
            'removeLink' => lang('admin.remove_link'),
            'removeCard' => lang('admin.delete_card'),
            'confirmCardRemoval' => lang('admin.delete_this_card_and_all_of_its_links'),
            'titleRequired' => lang('admin.please_give_each_card_a_title_in_at_least_one_language'),
            'linkIncomplete' => lang('admin.each_link_needs_a_target_and_a_title_in_at_least_one_language'),
            'drag' => lang('admin.drag_to_reorder'),
            'cardIcon' => lang('admin.card_icon'),
            'iconHint' => lang('admin.enter_the_phosphor_icon_name_without_the_ph_prefix'),
            'browseIcons' => lang('admin.browse_icons'),
            'title' => lang('common.title'),
            'content' => lang('common.content'),
            'optionalLinks' => lang('admin.optional_links_displayed_on_this_card'),
            'addLink' => lang('admin.add_link')
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        function createId() {
            return 'rh-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
        }

        function safeIcon(value, fallback) {
            const icon = String(value || '').trim().replace(/^ph-/, '');
            return /^[a-z0-9-]+$/.test(icon) ? icon : fallback;
        }

        function resourceHubEditor(id) {
            quillEditor(id);
            $('#' + id + '-quill').prev('.ql-toolbar').find('.ql-image').remove();
        }

        function linkTemplate() {
            return `
                <div class="resource-hub-link">
                    <div class="link-head">
                        <strong><i class="ph ph-arrow-square-out link-icon-preview"></i> ${labels.link}</strong>
                        <button type="button" class="btn link danger small remove-resource-hub-link" title="${labels.removeLink}"><i class="ph ph-trash"></i></button>
                    </div>
                    <div class="row row-eq-spacing my-0">
                        <div class="col-md-6">
                            <label>English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                            <input type="text" class="form-control" placeholder="${labels.linkTitle}" data-link-field="title.en">
                        </div>
                        <div class="col-md-6">
                            <label>Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                            <input type="text" class="form-control" placeholder="${labels.linkTitle}" data-link-field="title.de">
                        </div>
                    </div>
                    <div class="row row-eq-spacing mb-0">
                        <div class="col-md-4">
                            <label>${labels.icon}</label>
                            <input type="text" class="form-control link-icon-input" value="arrow-square-out" pattern="[a-z0-9-]+" list="resource-hub-icons" placeholder="arrow-square-out" data-link-field="icon">
                        </div>
                        <div class="col-md-8">
                            <label>${labels.target}</label>
                            <input type="text" class="form-control link-url-input" placeholder="https://example.org or /documents" data-link-field="url">
                        </div>
                    </div>
                </div>`;
        }

        function cardTemplate(index) {
            const id = createId();
            return `
                <details class="collapse-panel resource-hub-card" data-card-id="${id}" open>
                    <summary class="collapse-header">
                        <i class="ph ph-dots-six-vertical text-muted card-handle" title="${labels.drag}"></i>
                        <i class="ph ph-link card-summary-icon"></i>
                        <strong class="card-summary-title">${labels.untitled}</strong>
                        <span class="text-muted card-summary-meta"><span class="card-link-count">0</span> ${labels.links}</span>
                    </summary>
                    <div class="collapse-content">
                        <input type="hidden" value="${id}" data-card-field="id">
                        <div class="form-group">
                            <label for="resource-hub-icon-${index}">${labels.cardIcon}</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="ph ph-link card-icon-preview"></i></span></div>
                                <input type="text" class="form-control card-icon-input" id="resource-hub-icon-${index}" value="link" pattern="[a-z0-9-]+" list="resource-hub-icons" placeholder="link" data-card-field="icon">
                            </div>
                            <small class="text-muted">${labels.iconHint} <a href="https://phosphoricons.com/" target="_blank" rel="noopener noreferrer">${labels.browseIcons}</a></small>
                        </div>
                        <div class="row row-eq-spacing my-0">
                            <div class="col-md-6">
                                <h5 class="mt-0">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                                <div class="form-group"><label for="resource-hub-title-en-${index}">${labels.title}</label><input type="text" class="form-control card-title-input" id="resource-hub-title-en-${index}" data-card-field="title.en"></div>
                                <div class="form-group resource-hub-editor mb-0"><label>${labels.content}</label><div id="resource-hub-content-en-${index}-quill"></div><textarea id="resource-hub-content-en-${index}" class="d-none" readonly data-card-field="content.en"></textarea></div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mt-0">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                                <div class="form-group"><label for="resource-hub-title-de-${index}">${labels.title}</label><input type="text" class="form-control card-title-input" id="resource-hub-title-de-${index}" data-card-field="title.de"></div>
                                <div class="form-group resource-hub-editor mb-0"><label>${labels.content}</label><div id="resource-hub-content-de-${index}-quill"></div><textarea id="resource-hub-content-de-${index}" class="d-none" readonly data-card-field="content.de"></textarea></div>
                            </div>
                        </div>
                        <div class="mt-20">
                            <div class="d-flex align-items-center justify-content-between gap-10 mb-10">
                                <div><h4 class="m-0">${labels.links}</h4><small class="text-muted">${labels.optionalLinks}</small></div>
                                <button type="button" class="btn small add-resource-hub-link"><i class="ph ph-plus"></i> ${labels.addLink}</button>
                            </div>
                            <div class="resource-hub-links"></div>
                        </div>
                        <div class="text-right mt-20 border-top pt-10"><button type="button" class="btn link danger remove-resource-hub-card"><i class="ph ph-trash"></i> ${labels.removeCard}</button></div>
                    </div>
                </details>`;
        }

        function updateCardSummary(card) {
            const currentLanguage = <?= json_encode(lang('common.this_language')) ?>;
            const preferred = card.querySelector(`[data-card-field="title.${currentLanguage}"]`).value.trim();
            const fallbackLanguage = currentLanguage === 'de' ? 'en' : 'de';
            const fallback = card.querySelector(`[data-card-field="title.${fallbackLanguage}"]`).value.trim();
            card.querySelector('.card-summary-title').textContent = preferred || fallback || labels.untitled;
            card.querySelector('.card-link-count').textContent = card.querySelectorAll('.resource-hub-link').length;
        }

        function updateEmptyState() {
            $('#resource-hub-empty').toggleClass('d-none', $('.resource-hub-card').length > 0);
        }

        function fieldName(base, path) {
            return base + path.split('.').map(function(part) { return '[' + part + ']'; }).join('');
        }

        $('#resource-hub-cards').sortable({
            handle: '.card-handle',
            items: '> .resource-hub-card',
            placeholder: 'box padded'
        });

        $('#resource-hub-cards textarea[data-card-field^="content."]').each(function() {
            resourceHubEditor(this.id);
        });

        $('#resource-hub-cards').on('click', '.card-handle', function(event) {
            event.preventDefault();
        });

        $('#add-resource-hub-card').on('click', function() {
            const index = nextCardIndex++;
            $('#resource-hub-cards').append(cardTemplate(index));
            resourceHubEditor('resource-hub-content-en-' + index);
            resourceHubEditor('resource-hub-content-de-' + index);
            updateEmptyState();
            const card = $('#resource-hub-cards .resource-hub-card').last()[0];
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            card.querySelector('[data-card-field="title.en"]').focus({ preventScroll: true });
        });

        $('#resource-hub-cards').on('click', '.add-resource-hub-link', function() {
            const card = this.closest('.resource-hub-card');
            $(card).find('.resource-hub-links').append(linkTemplate());
            updateCardSummary(card);
            $(card).find('.resource-hub-link').last().find('[data-link-field="title.en"]').focus();
        });

        $('#resource-hub-cards').on('click', '.remove-resource-hub-link', function() {
            const card = this.closest('.resource-hub-card');
            this.closest('.resource-hub-link').remove();
            updateCardSummary(card);
        });

        $('#resource-hub-cards').on('click', '.remove-resource-hub-card', function() {
            if (!confirm(labels.confirmCardRemoval)) return;
            this.closest('.resource-hub-card').remove();
            updateEmptyState();
        });

        $('#resource-hub-cards').on('input', '.card-title-input', function() { updateCardSummary(this.closest('.resource-hub-card')); });
        $('#resource-hub-cards').on('input', '.card-icon-input', function() {
            const card = this.closest('.resource-hub-card');
            const icon = safeIcon(this.value, 'link');
            card.querySelector('.card-icon-preview').className = 'ph ph-' + icon + ' card-icon-preview';
            card.querySelector('.card-summary-icon').className = 'ph ph-' + icon + ' card-summary-icon';
        });
        $('#resource-hub-cards').on('input', '.link-icon-input', function() {
            const icon = safeIcon(this.value, 'arrow-square-out');
            this.closest('.resource-hub-link').querySelector('.link-icon-preview').className = 'ph ph-' + icon + ' link-icon-preview';
        });

        $('.resource-hub-description').on('input', function() {
            $(this).siblings('.resource-hub-character-count').find('span').text(this.value.length);
        }).trigger('input');

        $('.resource-hub-icon-input').on('input', function() {
            const icon = safeIcon(this.value, 'link');
            document.querySelector('.resource-hub-icon-preview').className = 'ph ph-' + icon + ' resource-hub-icon-preview';
        });

        $('#resource-hub-form').on('submit', function(event) {
            let valid = true;
            let message = '';
            $('#resource-hub-cards > .resource-hub-card').each(function(cardIndex) {
                const card = this;
                const cardBase = `general[resource-hub][cards][${cardIndex}]`;
                card.querySelectorAll('[data-card-field]').forEach(function(input) { input.name = fieldName(cardBase, input.dataset.cardField); });

                const titleEn = card.querySelector('[data-card-field="title.en"]').value.trim();
                const titleDe = card.querySelector('[data-card-field="title.de"]').value.trim();
                if (!titleEn && !titleDe && valid) {
                    valid = false;
                    message = labels.titleRequired;
                    card.open = true;
                    card.querySelector('[data-card-field="title.en"]').focus();
                }

                card.querySelectorAll('.resource-hub-link').forEach(function(link, linkIndex) {
                    const linkBase = `${cardBase}[links][${linkIndex}]`;
                    link.querySelectorAll('[data-link-field]').forEach(function(input) { input.name = fieldName(linkBase, input.dataset.linkField); });
                    const titleEn = link.querySelector('[data-link-field="title.en"]').value.trim();
                    const titleDe = link.querySelector('[data-link-field="title.de"]').value.trim();
                    const url = link.querySelector('[data-link-field="url"]').value.trim();
                    if ((!url || (!titleEn && !titleDe)) && valid) {
                        valid = false;
                        message = labels.linkIncomplete;
                        card.open = true;
                        link.querySelector(!url ? '[data-link-field="url"]' : '[data-link-field="title.en"]').focus();
                    }
                });
            });

            if (!valid) {
                event.preventDefault();
                toastError(message);
            }
        });
    })();
</script>
