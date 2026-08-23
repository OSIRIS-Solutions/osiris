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
$cards = DB::doc2Arr($rh['cards'] ?? []);
$cardCount = count($cards);
?>

<style>
    #resource-hub-cards .resource-hub-card { margin-bottom: 1rem; }
    #resource-hub-cards .collapse-header { display: flex; align-items: center; gap: 1rem; }
    #resource-hub-cards .card-summary-title { flex-grow: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    #resource-hub-cards .card-handle { cursor: move; }
    .resource-hub-link { padding: 1.5rem; margin-bottom: 1rem; border: var(--border-width) solid var(--border-color); border-radius: var(--border-radius); background: var(--box-bg-color); }
    .resource-hub-link .link-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
    .resource-hub-editor .ql-editor { min-height: 12rem; }
    @media (max-width: 575px) { #resource-hub-cards .card-summary-meta { display: none; } }
</style>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-link"></i>
        <?= lang('Resource Hub Settings', 'Ressourcen-Hub Einstellungen') ?>
    </h1>

    <?php if (!$Settings->featureEnabled('resource-hub')) { ?>
        <div class="alert signal">
            <?= lang(
                'The Resource Hub feature is not enabled. Please enable it in the <a href="'.ROOTPATH.'/admin/features#resource-hub">Features</a> section of the admin panel to use the hub.',
                'Die Ressourcen-Hub Funktion ist nicht aktiviert. Bitte aktiviere sie im Bereich <a href="'.ROOTPATH.'/admin/features#resource-hub">"Funktionen"</a> des Admin-Panels, um den Hub nutzen zu können.'
            ) ?>
        </div>
    <?php } ?>


    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post" id="resource-hub-form">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/resource-hub">

        <?php
        $label = $rh['label'] ?? [];
        ?>
        <div class="box padded">
            <h2 class="title">
                <?= lang('Label for Resource Hub', 'Bezeichnung für den Ressourcen-Hub') ?>
            </h2>
            <div class="row row-eq-spacing">
                <div class="col-md-6 mt-10 mt-md-0">
                    <label for="resource_hub_label" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                    <input name="general[resource-hub][label][en]" id="resource_hub_label" type="text" class="form-control" value="<?= e($label['en'] ?? 'Resource Hub') ?>">
                </div>
                <div class="col-md-6 mt-10 mt-md-0">
                    <label for="resource_hub_label_de" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (Deutsch) <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                    <input name="general[resource-hub][label][de]" id="resource_hub_label_de" type="text" class="form-control" value="<?= e($label['de'] ?? 'Ressourcen-Hub') ?>">
                </div>
            </div>
        </div>

        <div id="card-configuration" class="box padded">
            <div class="d-flex align-items-center justify-content-between gap-10 mb-20">
                <div>
                    <h2 class="title mt-0 mb-5"><?= lang('Cards', 'Karten') ?></h2>
                    <p class="text-muted m-0">
                        <?= lang(
                            'Create the content blocks for the Resource Hub. Drag the cards into the desired order.',
                            'Erstelle die Inhaltsblöcke für den Ressourcen-Hub. Ziehe die Karten in die gewünschte Reihenfolge.'
                        ) ?>
                    </p>
                </div>
                <button type="button" class="btn primary flex-shrink-0" id="add-resource-hub-card">
                    <i class="ph ph-plus"></i> <?= lang('Add card', 'Karte hinzufügen') ?>
                </button>
            </div>

            <div id="resource-hub-empty" class="alert signal <?= $cardCount ? 'd-none' : '' ?>">
                <div class="title"><?= lang('No cards yet', 'Noch keine Karten') ?></div>
                <?= lang('Add the first card to start building your Resource Hub.', 'Füge die erste Karte hinzu, um deinen Ressourcen-Hub aufzubauen.') ?>
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
                    if (trim($summaryTitle) === '') $summaryTitle = lang('Untitled card', 'Unbenannte Karte');
                ?>
                    <details class="collapse-panel resource-hub-card" data-card-id="<?= e($cardId) ?>">
                        <summary class="collapse-header">
                            <i class="ph ph-dots-six-vertical text-muted card-handle" title="<?= lang('Drag to reorder', 'Zum Sortieren ziehen') ?>"></i>
                            <i class="ph ph-<?= e($icon) ?> card-summary-icon"></i>
                            <strong class="card-summary-title"><?= e($summaryTitle) ?></strong>
                            <span class="text-muted card-summary-meta"><span class="card-link-count"><?= count($links) ?></span> <?= lang('links', 'Links') ?></span>
                        </summary>

                        <div class="collapse-content">
                            <input type="hidden" value="<?= e($cardId) ?>" data-card-field="id">

                            <div class="form-group">
                                <label for="resource-hub-icon-<?= $i ?>"><?= lang('Card icon', 'Karten-Icon') ?></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="ph ph-<?= e($icon) ?> card-icon-preview"></i></span></div>
                                    <input type="text" class="form-control card-icon-input" id="resource-hub-icon-<?= $i ?>" value="<?= e($icon) ?>" pattern="[a-z0-9-]+" list="resource-hub-icons" placeholder="link" data-card-field="icon">
                                </div>
                                <small class="text-muted">
                                    <?= lang('Enter the Phosphor icon name without the “ph-” prefix.', 'Gib den Namen des Phosphor-Icons ohne das Präfix „ph-“ ein.') ?>
                                    <a href="https://phosphoricons.com/" target="_blank" rel="noopener noreferrer"><?= lang('Browse icons', 'Icons durchsuchen') ?></a>
                                </small>
                            </div>

                            <div class="row row-eq-spacing my-0">
                                <div class="col-md-6">
                                    <h5 class="mt-0">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                                    <div class="form-group">
                                        <label for="resource-hub-title-en-<?= $i ?>"><?= lang('Title', 'Titel') ?></label>
                                        <input type="text" class="form-control card-title-input" id="resource-hub-title-en-<?= $i ?>" value="<?= e($title['en'] ?? '') ?>" data-card-field="title.en">
                                    </div>
                                    <div class="form-group resource-hub-editor mb-0">
                                        <label><?= lang('Content', 'Inhalt') ?></label>
                                        <div id="resource-hub-content-en-<?= $i ?>-quill"><?= $content['en'] ?? '' ?></div>
                                        <textarea id="resource-hub-content-en-<?= $i ?>" class="d-none" readonly data-card-field="content.en"><?= e($content['en'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mt-0">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                                    <div class="form-group">
                                        <label for="resource-hub-title-de-<?= $i ?>"><?= lang('Title', 'Titel') ?></label>
                                        <input type="text" class="form-control card-title-input" id="resource-hub-title-de-<?= $i ?>" value="<?= e($title['de'] ?? '') ?>" data-card-field="title.de">
                                    </div>
                                    <div class="form-group resource-hub-editor mb-0">
                                        <label><?= lang('Content', 'Inhalt') ?></label>
                                        <div id="resource-hub-content-de-<?= $i ?>-quill"><?= $content['de'] ?? '' ?></div>
                                        <textarea id="resource-hub-content-de-<?= $i ?>" class="d-none" readonly data-card-field="content.de"><?= e($content['de'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-20">
                                <div class="d-flex align-items-center justify-content-between gap-10 mb-10">
                                    <div>
                                        <h4 class="m-0"><?= lang('Links', 'Links') ?></h4>
                                        <small class="text-muted"><?= lang('Optional links displayed on this card.', 'Optionale Links, die auf dieser Karte angezeigt werden.') ?></small>
                                    </div>
                                    <button type="button" class="btn small add-resource-hub-link"><i class="ph ph-plus"></i> <?= lang('Add link', 'Link hinzufügen') ?></button>
                                </div>

                                <div class="resource-hub-links">
                                    <?php foreach ($links as $rawLink) {
                                        $link = DB::doc2Arr($rawLink);
                                        $linkTitle = DB::doc2Arr($link['title'] ?? []);
                                        $linkIcon = $link['icon'] ?? 'arrow-square-out';
                                    ?>
                                        <div class="resource-hub-link">
                                            <div class="link-head">
                                                <strong><i class="ph ph-<?= e($linkIcon) ?> link-icon-preview"></i> <?= lang('Link', 'Link') ?></strong>
                                                <button type="button" class="btn link danger small remove-resource-hub-link" title="<?= lang('Remove link', 'Link entfernen') ?>"><i class="ph ph-trash"></i></button>
                                            </div>
                                            <div class="row row-eq-spacing my-0">
                                                <div class="col-md-6">
                                                    <label>English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                                    <input type="text" class="form-control" value="<?= e($linkTitle['en'] ?? '') ?>" placeholder="<?= lang('Link title', 'Linktitel') ?>" data-link-field="title.en">
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                                    <input type="text" class="form-control" value="<?= e($linkTitle['de'] ?? '') ?>" placeholder="<?= lang('Link title', 'Linktitel') ?>" data-link-field="title.de">
                                                </div>
                                            </div>
                                            <div class="row row-eq-spacing mb-0">
                                                <div class="col-md-4">
                                                    <label><?= lang('Icon', 'Icon') ?></label>
                                                    <input type="text" class="form-control link-icon-input" value="<?= e($linkIcon) ?>" pattern="[a-z0-9-]+" list="resource-hub-icons" placeholder="arrow-square-out" data-link-field="icon">
                                                </div>
                                                <div class="col-md-8">
                                                    <label><?= lang('Target', 'Ziel') ?></label>
                                                    <input type="text" class="form-control link-url-input" value="<?= e($link['url'] ?? '') ?>" placeholder="https://example.org or /documents" data-link-field="url">
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="text-right mt-20 border-top pt-10">
                                <button type="button" class="btn link danger remove-resource-hub-card"><i class="ph ph-trash"></i> <?= lang('Delete card', 'Karte löschen') ?></button>
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

        <!-- 
        TODO: add an image map feature for the resource hub, where users can upload an image and define the position of the 
        defined cards on the image.
        -->
        <div id="image-map-configuration" class="box padded">
        </div>


        <button type="submit" class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
    </form>
</div>

<script>
    (function() {
        let nextCardIndex = <?= max($cardCount, 1) ?>;
        const labels = <?= json_encode([
            'untitled' => lang('Untitled card', 'Unbenannte Karte'),
            'links' => lang('links', 'Links'),
            'link' => lang('Link', 'Link'),
            'linkTitle' => lang('Link title', 'Linktitel'),
            'icon' => lang('Icon', 'Icon'),
            'target' => lang('Target', 'Ziel'),
            'removeLink' => lang('Remove link', 'Link entfernen'),
            'removeCard' => lang('Delete card', 'Karte löschen'),
            'confirmCardRemoval' => lang('Delete this card and all of its links?', 'Diese Karte und alle zugehörigen Links löschen?'),
            'titleRequired' => lang('Please give each card a title in at least one language.', 'Bitte gib jeder Karte in mindestens einer Sprache einen Titel.'),
            'linkIncomplete' => lang('Each link needs a target and a title in at least one language.', 'Jeder Link benötigt ein Ziel und einen Titel in mindestens einer Sprache.'),
            'drag' => lang('Drag to reorder', 'Zum Sortieren ziehen'),
            'cardIcon' => lang('Card icon', 'Karten-Icon'),
            'iconHint' => lang('Enter the Phosphor icon name without the “ph-” prefix.', 'Gib den Namen des Phosphor-Icons ohne das Präfix „ph-“ ein.'),
            'browseIcons' => lang('Browse icons', 'Icons durchsuchen'),
            'title' => lang('Title', 'Titel'),
            'content' => lang('Content', 'Inhalt'),
            'optionalLinks' => lang('Optional links displayed on this card.', 'Optionale Links, die auf dieser Karte angezeigt werden.'),
            'addLink' => lang('Add link', 'Link hinzufügen')
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
            const currentLanguage = <?= json_encode(lang('en', 'de')) ?>;
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
