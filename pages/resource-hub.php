<?php

/**
 * Resource Hub page
 *
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @link        /hub
 * @package     OSIRIS
 * @since       1.2.1
 *
 * @copyright   Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author      Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$rh = DB::doc2Arr($Settings->get('resource-hub') ?? []);
$cards = DB::doc2Arr($rh['cards'] ?? []);
$imageMap = DB::doc2Arr($rh['image-map'] ?? []);
$backgroundImage = DB::doc2Arr($imageMap['image'] ?? []);
$placements = DB::doc2Arr($imageMap['placements'] ?? []);
$backgroundFile = (string) ($backgroundImage['file'] ?? '');
$hasImageMap = preg_match('#^resource-hub/[a-f0-9]{24}\.(jpg|png|webp)$#', $backgroundFile)
    && is_file(BASEPATH . '/uploads/' . $backgroundFile);

$localizedValue = static function ($value): string {
    $value = DB::doc2Arr($value ?? []);
    if (!is_array($value)) return trim((string) $value);

    $language = lang('en', 'de');
    $fallback = $language === 'de' ? 'en' : 'de';
    $localized = trim((string) ($value[$language] ?? ''));
    return $localized !== '' ? $localized : trim((string) ($value[$fallback] ?? ''));
};

$resourceHubUrl = static function ($url): ?string {
    $url = trim((string) $url);
    if ($url === '') return null;
    if (str_starts_with($url, '/') && !str_starts_with($url, '//')) return ROOTPATH . $url;
    if (str_starts_with($url, '#')) return $url;

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https', 'mailto'], true) ? $url : null;
};

// Both views use the same prepared data so localization and link validation stay in sync.
$displayCards = [];
foreach ($cards as $index => $rawCard) {
    $card = DB::doc2Arr($rawCard);
    $title = $localizedValue($card['title'] ?? []);
    $content = $localizedValue($card['content'] ?? []);
    if ($title === '' && trim(strip_tags($content)) === '') continue;

    $icon = trim((string) ($card['icon'] ?? ''));
    if (!preg_match('/^[a-z0-9-]+$/', $icon)) $icon = '';

    $validLinks = [];
    foreach (DB::doc2Arr($card['links'] ?? []) as $rawLink) {
        $link = DB::doc2Arr($rawLink);
        $linkTitle = $localizedValue($link['title'] ?? []);
        $url = $resourceHubUrl($link['url'] ?? '');
        if ($linkTitle === '' || $url === null) continue;

        $linkIcon = trim((string) ($link['icon'] ?? ''));
        if (!preg_match('/^[a-z0-9-]+$/', $linkIcon)) $linkIcon = '';
        $validLinks[] = ['title' => $linkTitle, 'url' => $url, 'icon' => $linkIcon];
    }

    $cardId = (string) ($card['id'] ?? 'resource-' . $index);
    $placement = DB::doc2Arr($placements[$cardId] ?? []);
    $x = isset($placement['x']) && is_numeric($placement['x']) ? (float) $placement['x'] : null;
    $y = isset($placement['y']) && is_numeric($placement['y']) ? (float) $placement['y'] : null;
    if ($x === null || $y === null || $x < 0 || $x > 100 || $y < 0 || $y > 100) {
        $x = null;
        $y = null;
    }

    $displayCards[] = [
        'id' => $cardId,
        'title' => $title,
        'content' => $content,
        'icon' => $icon,
        'links' => $validLinks,
        'x' => $x,
        'y' => $y,
    ];
}

$preferredView = (string) ($USER['resource_hub_view'] ?? 'cards');
$currentView = $hasImageMap && $preferredView === 'image-map' ? 'image-map' : 'cards';
?>

<style>
    .resource-hub-heading { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 2rem; }
    .resource-hub-heading h1 { margin: 0; }
    #resource-hub-card-view > [class^="col"] { display: flex; }
    .resource-hub-card { display: flex; flex-direction: column; width: 100%; padding: 2rem; margin-bottom: 2rem; }
    .resource-hub-card-header { display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.5rem; }
    .resource-hub-card-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 4.5rem; width: 4.5rem; height: 4.5rem; color: var(--primary-color); background: var(--primary-color-20); border-radius: 50%; font-size: 2.4rem; }
    .resource-hub-card-title { margin: 0; overflow-wrap: anywhere; }
    .resource-hub-card-content { flex-grow: 1; color: var(--text-color); }
    .resource-hub-card-content > :first-child, .resource-hub-popover-content > :first-child { margin-top: 0; }
    .resource-hub-card-content > :last-child, .resource-hub-popover-content > :last-child { margin-bottom: 0; }
    .resource-hub-card-links, .resource-hub-popover-links { display: flex; flex-direction: column; align-items: flex-start; gap: .75rem; padding-top: 1.5rem; margin-top: 1.5rem; border-top: var(--border-width) solid var(--border-color); }
    .resource-hub-card-links .btn, .resource-hub-popover-links .btn { width: 100%; white-space: normal; text-align: left; height: auto; line-height: 20px; padding: .25rem 1rem; }
    .resource-hub-card-links .btn i, .resource-hub-popover-links .btn i { margin-right: .5rem; color: var(--primary-color); }
    .resource-hub-image-map-shell { padding: 1rem; overflow: auto; background: var(--muted-color-very-light); border: var(--border-width) solid var(--border-color); border-radius: var(--border-radius); }
    .resource-hub-image-map { position: relative; width: 100%; min-width: 40rem; line-height: 0; }
    .resource-hub-image-map > img { display: block; width: 100%; height: auto; border-radius: calc(var(--border-radius) / 2); }
    .resource-hub-hotspot { position: absolute; z-index: 2; width: 3.25rem; height: 3.25rem; padding: 0; display: grid; place-items: center; transform: translate(-50%, -50%); color: var(--primary-color); background: var(--box-bg-color); border: 2px solid var(--primary-color); border-radius: 50%; box-shadow: 0 .2rem .9rem rgba(0, 0, 0, .3); font-size: 1.55rem; line-height: 1; cursor: pointer; transition: transform .15s ease, color .15s ease, background .15s ease; }
    .resource-hub-hotspot-label { position: absolute; top: 50%; width: max-content; max-width: 18rem; padding: .7rem 1rem; overflow: hidden; color: var(--text-color); background: #ffffffa0; border: var(--border-width) solid var(--border-color); border-radius: var(--border-radius); box-shadow: 0 .15rem .6rem rgba(0, 0, 0, .2); font-size: 1.2rem; font-weight: 600; line-height: 1.2; text-align: left; text-overflow: ellipsis; white-space: nowrap; transform: translateY(-50%); transition: border-color .15s ease, box-shadow .15s ease; }
    .resource-hub-hotspot.label-right .resource-hub-hotspot-label { left: calc(100% + .5rem); }
    .resource-hub-hotspot.label-left .resource-hub-hotspot-label { right: calc(100% + .5rem); }
    .resource-hub-hotspot:hover, .resource-hub-hotspot:focus, .resource-hub-hotspot.active { z-index: 3; color: var(--box-bg-color); background: var(--primary-color); transform: translate(-50%, -50%) scale(1.12); }
    .resource-hub-hotspot:hover .resource-hub-hotspot-label, .resource-hub-hotspot:focus .resource-hub-hotspot-label, .resource-hub-hotspot.active .resource-hub-hotspot-label { border-color: var(--primary-color); box-shadow: 0 .2rem .8rem rgba(0, 0, 0, .28); background: var(--box-bg-color); }
    .resource-hub-popover { width: 24rem; max-width: calc(100vw - 2rem); }
    .resource-hub-popover .popover-title { font-size: 1.2rem; }
    .resource-hub-popover-title { display: flex; align-items: center; gap: .5rem; }
    .resource-hub-popover-title i { color: var(--primary-color); font-size: 1.8rem !important; }
    .resource-hub-popover-content { line-height: 1.45; font-size: 1.2rem;}
    @media (max-width: 767px) {
        .resource-hub-heading { align-items: flex-start; }
        .resource-hub-view-switch { width: 100%; display: flex; }
        .resource-hub-view-switch .btn { flex: 1; }
    }
</style>

<div class="resource-hub-heading">
    <h1><i class="ph-duotone ph-link"></i> <?= $Settings->resourceHubLabel() ?></h1>

    <?php if ($hasImageMap && !empty($displayCards)) { ?>
        <div class="btn-group resource-hub-view-switch" role="group" aria-label="<?= lang('Resource Hub view', 'Ansicht des Ressourcen-Hubs') ?>">
            <button type="button" class="btn <?= $currentView === 'cards' ? 'active' : '' ?>" data-resource-hub-view="cards" aria-pressed="<?= $currentView === 'cards' ? 'true' : 'false' ?>">
                <i class="ph ph-squares-four" aria-hidden="true"></i> <?= lang('Cards', 'Karten') ?>
            </button>
            <button type="button" class="btn <?= $currentView === 'image-map' ? 'active' : '' ?>" data-resource-hub-view="image-map" aria-pressed="<?= $currentView === 'image-map' ? 'true' : 'false' ?>">
                <i class="ph ph-image" aria-hidden="true"></i> <?= lang('Image map', 'Image-Map') ?>
            </button>
        </div>
    <?php } ?>
</div>

<?php if (empty($rh)) { ?>
    <div class="alert signal">
        <div class="title"><?= lang('The Resource Hub is not configured.', 'Der Ressourcen-Hub ist nicht konfiguriert.') ?></div>
        <?php if ($Settings->hasPermission('admin.see')) { ?>
            <?= lang('Please configure it in the "Resource Hub" section of the admin panel.', 'Bitte konfiguriere ihn im Bereich "Ressourcen-Hub" des Admin-Panels.') ?>
        <?php } else { ?>
            <?= lang('Please contact your administrator to configure the Resource Hub.', 'Bitte kontaktiere deinen Administrator, um den Ressourcen-Hub zu konfigurieren.') ?>
        <?php } ?>
    </div>
<?php return;
} ?>

<?php if (empty($displayCards)) { ?>
    <div class="alert signal">
        <div class="title"><?= lang('No resources available yet.', 'Noch keine Ressourcen vorhanden.') ?></div>
        <?php if ($Settings->hasPermission('admin.see')) { ?>
            <?= lang('Add the first card in the Resource Hub settings.', 'Füge die erste Karte in den Ressourcen-Hub-Einstellungen hinzu.') ?>
            <a href="<?= ROOTPATH ?>/admin/resource-hub" class="btn small ml-10"><i class="ph ph-gear" aria-hidden="true"></i> <?= lang('Open settings', 'Einstellungen öffnen') ?></a>
        <?php } else { ?>
            <?= lang('Please try again later.', 'Bitte versuche es später erneut.') ?>
        <?php } ?>
    </div>
<?php } else { ?>
    <div id="resource-hub-card-view" class="row row-eq-spacing <?= $currentView === 'cards' ? '' : 'd-none' ?>">
        <?php foreach ($displayCards as $card) { ?>
            <div class="col-md-6 col-xl-4">
                <article class="card resource-hub-card" id="resource-hub-card-<?= e($card['id']) ?>">
                    <header class="resource-hub-card-header">
                        <?php if ($card['icon'] !== '') { ?><span class="resource-hub-card-icon"><i class="ph ph-<?= e($card['icon']) ?>" aria-hidden="true"></i></span><?php } ?>
                        <?php if ($card['title'] !== '') { ?><h2 class="resource-hub-card-title"><?= e($card['title']) ?></h2><?php } ?>
                    </header>

                    <?php if (trim(strip_tags($card['content'])) !== '') { ?><div class="resource-hub-card-content"><?= $card['content'] ?></div><?php } ?>

                    <?php if (!empty($card['links'])) { ?>
                        <footer class="resource-hub-card-links">
                            <?php foreach ($card['links'] as $link) { ?>
                                <a href="<?= e($link['url']) ?>" class="btn">
                                    <?php if ($link['icon'] !== '') { ?><i class="ph ph-<?= e($link['icon']) ?>" aria-hidden="true"></i><?php } ?>
                                    <?= e($link['title']) ?>
                                </a>
                            <?php } ?>
                        </footer>
                    <?php } ?>
                </article>
            </div>
        <?php } ?>
    </div>

    <?php if ($hasImageMap) { ?>
        <div id="resource-hub-image-view" class="<?= $currentView === 'image-map' ? '' : 'd-none' ?>">
            <div class="resource-hub-image-map-shell">
                <div class="resource-hub-image-map" id="resource-hub-image-map">
                    <img src="<?= ROOTPATH ?>/uploads/<?= e($backgroundFile) ?>?v=<?= strtotime((string) ($backgroundImage['uploaded'] ?? 'now')) ?>" alt="<?= lang('Resource Hub image map', 'Image-Map des Ressourcen-Hubs') ?>">

                    <?php foreach ($displayCards as $index => $card) {
                        if ($card['x'] === null || $card['y'] === null) continue;
                        $markerIcon = $card['icon'] !== '' ? $card['icon'] : 'map-pin';
                        $labelSide = $card['x'] > 65 ? 'label-left' : 'label-right';
                    ?>
                        <button type="button" class="resource-hub-hotspot <?= $labelSide ?>" style="left: <?= number_format($card['x'], 2, '.', '') ?>%; top: <?= number_format($card['y'], 2, '.', '') ?>%;" data-popover-id="resource-hub-popover-<?= $index ?>" aria-label="<?= e($card['title'] !== '' ? $card['title'] : lang('Open resource', 'Ressource öffnen')) ?>">
                            <i class="ph ph-<?= e($markerIcon) ?>" aria-hidden="true"></i>
                            <?php if ($card['title'] !== '') { ?>
                                <span class="resource-hub-hotspot-label"><?= e($card['title']) ?></span>
                            <?php } ?>
                        </button>
                    <?php } ?>
                </div>
            </div>

            <?php foreach ($displayCards as $index => $card) {
                if ($card['x'] === null || $card['y'] === null) continue;
                $markerIcon = $card['icon'] !== '' ? $card['icon'] : 'map-pin';
            ?>
                <template id="resource-hub-popover-<?= $index ?>">
                    <div class="resource-hub-popover-data">
                        <div class="resource-hub-popover-heading">
                            <span class="resource-hub-popover-title"><i class="ph ph-<?= e($markerIcon) ?>" aria-hidden="true"></i><span><?= e($card['title']) ?></span></span>
                        </div>
                        <div class="resource-hub-popover-body">
                            <?php if (trim(strip_tags($card['content'])) !== '') { ?><div class="resource-hub-popover-content"><?= $card['content'] ?></div><?php } ?>
                            <?php if (!empty($card['links'])) { ?>
                                <div class="resource-hub-popover-links">
                                    <?php foreach ($card['links'] as $link) { ?>
                                        <a href="<?= e($link['url']) ?>" class="btn small">
                                            <?php if ($link['icon'] !== '') { ?><i class="ph ph-<?= e($link['icon']) ?>" aria-hidden="true"></i><?php } ?>
                                            <?= e($link['title']) ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </template>
            <?php } ?>
        </div>
    <?php } ?>
<?php } ?>

<?php if ($hasImageMap && !empty($displayCards)) { ?>
    <script src="<?= ROOTPATH ?>/js/popover.js"></script>
    <script>
        (function() {
            const viewButtons = document.querySelectorAll('[data-resource-hub-view]');
            const cardView = document.getElementById('resource-hub-card-view');
            const imageView = document.getElementById('resource-hub-image-view');
            const hotspots = $('.resource-hub-hotspot');
            let currentView = <?= json_encode($currentView) ?>;

            hotspots.popover({
                placement: 'auto top',
                container: 'body',
                trigger: 'click',
                html: true,
                template: '<div class="popover resource-hub-popover" role="dialog"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
                title: function() {
                    const template = document.getElementById(this.dataset.popoverId);
                    return template ? template.content.querySelector('.resource-hub-popover-heading').innerHTML : '';
                },
                content: function() {
                    const template = document.getElementById(this.dataset.popoverId);
                    return template ? template.content.querySelector('.resource-hub-popover-body').innerHTML : '';
                }
            });

            hotspots.on('click', function(event) {
                event.stopPropagation();
                hotspots.not(this).popover('hide').removeClass('active');
                $(this).toggleClass('active');
            });

            $(document).on('click.resourceHubPopover', function(event) {
                if ($(event.target).closest('.resource-hub-hotspot, .resource-hub-popover').length) return;
                hotspots.popover('hide').removeClass('active');
            });

            function savePreference(view) {
                const body = new URLSearchParams({ key: 'resource_hub_view', value: view });
                fetch(<?= json_encode(ROOTPATH . '/crud/users/set-preference') ?>, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: body.toString(),
                    credentials: 'same-origin'
                }).catch(function(error) {
                    console.warn('Resource Hub preference could not be saved.', error);
                });
            }

            function setResourceHubView(view) {
                if (view !== 'cards' && view !== 'image-map') return;
                cardView.classList.toggle('d-none', view !== 'cards');
                imageView.classList.toggle('d-none', view !== 'image-map');
                viewButtons.forEach(function(button) {
                    const active = button.dataset.resourceHubView === view;
                    button.classList.toggle('active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                hotspots.popover('hide').removeClass('active');
                if (view !== currentView) {
                    currentView = view;
                    savePreference(view);
                }
            }

            viewButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    setResourceHubView(button.dataset.resourceHubView);
                });
            });
        })();
    </script>
<?php } ?>
