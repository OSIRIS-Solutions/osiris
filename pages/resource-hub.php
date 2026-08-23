<?php

/**
 * Resource Hub page
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /hub
 *
 * @package     OSIRIS
 * @since       1.2.1
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$rh = DB::doc2Arr($Settings->get('resource-hub') ?? []);
$cards = DB::doc2Arr($rh['cards'] ?? []);

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

    if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
        return ROOTPATH . $url;
    }

    if (str_starts_with($url, '#')) return $url;

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if (in_array($scheme, ['http', 'https', 'mailto'], true)) return $url;

    return null;
};
?>

<style>
    #resource-hub-card-view > [class^="col"] {
        display: flex;
    }

    .resource-hub-card {
        display: flex;
        flex-direction: column;
        width: 100%;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .resource-hub-card-header {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .resource-hub-card-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 4.5rem;
        width: 4.5rem;
        height: 4.5rem;
        color: var(--primary-color);
        background: var(--primary-color-20);
        border-radius: 50%;
        font-size: 2.4rem;
    }

    .resource-hub-card-title {
        margin: 0;
        overflow-wrap: anywhere;
    }

    .resource-hub-card-content {
        flex-grow: 1;
        color: var(--text-color);
    }

    .resource-hub-card-content > :first-child { margin-top: 0; }
    .resource-hub-card-content > :last-child { margin-bottom: 0; }

    .resource-hub-card-links {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
        padding-top: 1.5rem;
        margin-top: 1.5rem;
        border-top: var(--border-width) solid var(--border-color);
    }

    .resource-hub-card-links .btn {
        width: 100%;
        white-space: normal;
        text-align: left;
    }
    .resource-hub-card-links .btn i {
        margin-right: 0.5rem;
        color: var(--primary-color);
    }
</style>

<h1>
    <i class="ph-duotone ph-link"></i>
    <?= $Settings->resourceHubLabel() ?>
</h1>

<?php if (empty($rh)) { ?>
    <div class="alert signal">
        <div class="title">
            <?= lang(
                'The Resource Hub is not configured.',
                'Der Ressourcen-Hub ist nicht konfiguriert.'
            ) ?>
        </div>
        <?php if ($Settings->hasPermission('admin.see')) { ?>
            <?= lang(
                'Please configure it in the "Resource Hub" section of the admin panel.',
                'Bitte konfiguriere ihn im Bereich "Ressourcen-Hub" des Admin-Panels.'
            ) ?>
        <?php } else { ?>
            <?= lang(
                'Please contact your administrator to configure the Resource Hub.',
                'Bitte kontaktiere deinen Administrator, um den Ressourcen-Hub zu konfigurieren.'
            ) ?>
        <?php } ?>
    </div>
<?php return;
} ?>

<?php if (empty($cards)) { ?>
    <div class="alert signal">
        <div class="title"><?= lang('No resources available yet.', 'Noch keine Ressourcen vorhanden.') ?></div>
        <?php if ($Settings->hasPermission('admin.see')) { ?>
            <?= lang('Add the first card in the Resource Hub settings.', 'Füge die erste Karte in den Ressourcen-Hub-Einstellungen hinzu.') ?>
            <a href="<?= ROOTPATH ?>/admin/resource-hub" class="btn small ml-10">
                <i class="ph ph-gear" aria-hidden="true"></i>
                <?= lang('Open settings', 'Einstellungen öffnen') ?>
            </a>
        <?php } else { ?>
            <?= lang('Please try again later.', 'Bitte versuche es später erneut.') ?>
        <?php } ?>
    </div>
<?php } else { ?>
    <div id="resource-hub-card-view" class="row row-eq-spacing">
        <?php foreach ($cards as $rawCard) {
            $card = DB::doc2Arr($rawCard);
            $title = $localizedValue($card['title'] ?? []);
            $content = $localizedValue($card['content'] ?? []);
            $links = DB::doc2Arr($card['links'] ?? []);

            $icon = trim((string) ($card['icon'] ?? ''));
            if (!preg_match('/^[a-z0-9-]+$/', $icon)) $icon = '';

            if ($title === '' && trim(strip_tags($content)) === '') continue;
        ?>
            <div class="col-md-6 col-xl-4">
                <article class="card resource-hub-card" id="resource-hub-card-<?= e($card['id'] ?? uniqid()) ?>">
                    <header class="resource-hub-card-header">
                        <?php if ($icon !== '') { ?>
                            <span class="resource-hub-card-icon">
                                <i class="ph ph-<?= e($icon) ?>" aria-hidden="true"></i>
                            </span>
                        <?php } ?>
                        <?php if ($title !== '') { ?>
                            <h2 class="resource-hub-card-title"><?= e($title) ?></h2>
                        <?php } ?>
                    </header>

                    <?php if (trim(strip_tags($content)) !== '') { ?>
                        <div class="resource-hub-card-content">
                            <?= $content ?>
                        </div>
                    <?php } ?>

                    <?php
                    $validLinks = [];
                    foreach ($links as $rawLink) {
                        $link = DB::doc2Arr($rawLink);
                        $linkTitle = $localizedValue($link['title'] ?? []);
                        $url = $resourceHubUrl($link['url'] ?? '');
                        if ($linkTitle === '' || $url === null) continue;

                        $linkIcon = trim((string) ($link['icon'] ?? ''));
                        if (!preg_match('/^[a-z0-9-]+$/', $linkIcon)) $linkIcon = '';

                        $validLinks[] = [
                            'title' => $linkTitle,
                            'url' => $url,
                            'icon' => $linkIcon
                        ];
                    }
                    ?>

                    <?php if (!empty($validLinks)) { ?>
                        <footer class="resource-hub-card-links">
                            <?php foreach ($validLinks as $link) { ?>
                                <a href="<?= e($link['url']) ?>" class="btn">
                                    <?php if ($link['icon'] !== '') { ?>
                                        <i class="ph ph-<?= e($link['icon']) ?>" aria-hidden="true"></i>
                                    <?php } ?>
                                    <?= e($link['title']) ?>
                                </a>
                            <?php } ?>
                        </footer>
                    <?php } ?>
                </article>
            </div>
        <?php } ?>
    </div>
<?php } ?>


<div id="img-map">
    <!-- TODO: implement image map view -->
</div>
