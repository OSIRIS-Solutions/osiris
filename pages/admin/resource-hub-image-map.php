<?php

/**
 * Visual editor for arranging Resource Hub cards on the image map.
 *
 * @link /admin/resource-hub-image-map
 * @package OSIRIS
 * @since 2.2.0
 */

$rh = DB::doc2Arr($Settings->get('resource-hub') ?? []);
$cards = DB::doc2Arr($rh['cards'] ?? []);
$imageMap = DB::doc2Arr($rh['image-map'] ?? []);
$backgroundImage = DB::doc2Arr($imageMap['image'] ?? []);
$placements = DB::doc2Arr($imageMap['placements'] ?? []);
$backgroundFile = (string) ($backgroundImage['file'] ?? '');
$hasBackgroundImage = preg_match('#^resource-hub/[a-f0-9]{24}\.(jpg|png|webp)$#', $backgroundFile)
    && is_file(BASEPATH . '/uploads/' . $backgroundFile);

$editorCards = [];
foreach ($cards as $rawCard) {
    $card = DB::doc2Arr($rawCard);
    $id = (string) ($card['id'] ?? '');
    if ($id === '') continue;

    $title = DB::doc2Arr($card['title'] ?? []);
    $displayTitle = lang($title['en'] ?? '', $title['de'] ?? null);
    if (trim($displayTitle) === '') $displayTitle = lang('Untitled card', 'Unbenannte Karte');

    $placement = DB::doc2Arr($placements[$id] ?? []);
    $hasPlacement = isset($placement['x'], $placement['y'])
        && is_numeric($placement['x']) && is_numeric($placement['y']);

    $editorCards[] = [
        'id' => $id,
        'icon' => preg_match('/^[a-z0-9-]+$/', (string) ($card['icon'] ?? '')) ? (string) $card['icon'] : 'link',
        'title' => $displayTitle,
        'x' => $hasPlacement ? max(0, min(100, (float) $placement['x'])) : null,
        'y' => $hasPlacement ? max(0, min(100, (float) $placement['y'])) : null,
    ];
}
?>

<style>
    .image-map-editor-layout { display: grid; grid-template-columns: minmax(16rem, 21rem) minmax(0, 1fr); gap: 2rem; align-items: start; }
    .image-map-card-list { display: grid; gap: .75rem; }
    .image-map-card { width: 100%; padding: 1rem; display: grid; grid-template-columns: 2.5rem minmax(0, 1fr); gap: .75rem; align-items: center; text-align: left; color: inherit; background: var(--box-bg-color); border: var(--border-width) solid var(--border-color); border-radius: var(--border-radius); cursor: pointer; }
    .image-map-card:hover { border-color: var(--primary-color); }
    .image-map-card.active { border-color: var(--primary-color); box-shadow: 0 0 0 2px var(--primary-color-very-light); }
    .image-map-card-icon { width: 2.5rem; height: 2.5rem; display: grid; place-items: center; border-radius: 50%; color: var(--primary-color); background: var(--primary-color-very-light); font-size: 1.35rem; }
    .image-map-card-content { min-width: 0; }
    .image-map-card-title { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .image-map-card-meta { display: block; margin-top: .2rem; color: var(--muted-color); font-size: .8em; }
    .image-map-stage-shell { padding: 1rem; overflow: auto; background: var(--muted-color-very-light); border: var(--border-width) solid var(--border-color); border-radius: var(--border-radius); }
    .image-map-stage { position: relative; width: 100%; min-width: 32rem; line-height: 0; cursor: crosshair; user-select: none; touch-action: manipulation; }
    .image-map-stage > img { display: block; width: 100%; height: auto; border-radius: calc(var(--border-radius) / 2); pointer-events: none; }
    .image-map-marker { position: absolute; z-index: 2; width: 2.75rem; height: 2.75rem; padding: 0; display: grid; place-items: center; transform: translate(-50%, -50%); color: var(--primary-color); background: var(--box-bg-color); border: 2px solid var(--primary-color); border-radius: 50%; box-shadow: 0 .2rem .8rem rgba(0, 0, 0, .25); cursor: pointer; font-size: 1.35rem; line-height: 1; }
    .image-map-marker:hover, .image-map-marker.active { color: var(--box-bg-color); background: var(--primary-color); transform: translate(-50%, -50%) scale(1.12); }
    .image-map-help { display: flex; align-items: center; gap: .5rem; }
    .image-map-actions { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1rem; margin-top: 2rem; }
    @media (max-width: 991px) {
        .image-map-editor-layout { grid-template-columns: 1fr; }
        .image-map-card-list { grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr)); }
    }
</style>

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-10 mb-20">
        <div>
            <h1 class="mb-5">
                <i class="ph-duotone ph-map-pin"></i>
                <?= lang('Arrange image map', 'Image-Map anordnen') ?>
            </h1>
            <p class="text-muted m-0">
                <?= lang('Select a card and click its desired position on the image.', 'Wähle eine Karte aus und klicke auf ihre gewünschte Position im Bild.') ?>
            </p>
        </div>
        <a href="<?= ROOTPATH ?>/admin/resource-hub#image-map-configuration" class="btn">
            <i class="ph ph-arrow-left"></i> <?= lang('Back to settings', 'Zurück zu den Einstellungen') ?>
        </a>
    </div>

    <?php if (!$hasBackgroundImage || empty($editorCards)) { ?>
        <div class="alert signal">
            <div class="title"><?= lang('Image map is not ready yet', 'Die Image-Map ist noch nicht bereit') ?></div>
            <?= lang(
                'Upload a background image and create at least one card before arranging the image map.',
                'Lade ein Hintergrundbild hoch und erstelle mindestens eine Karte, bevor du die Image-Map anordnest.'
            ) ?>
        </div>
    <?php } else { ?>
        <form action="<?= ROOTPATH ?>/crud/admin/resource-hub/image-map" method="post" id="image-map-editor-form">
            <div class="image-map-editor-layout">
                <aside class="box padded m-0">
                    <h2 class="title mt-0 mb-5"><?= lang('Cards', 'Karten') ?></h2>
                    <p class="text-muted mt-0 mb-15"><?= lang('Click a card to select it.', 'Klicke auf eine Karte, um sie auszuwählen.') ?></p>

                    <div class="image-map-card-list" id="image-map-card-list">
                        <?php foreach ($editorCards as $card) { ?>
                            <button type="button" class="image-map-card" data-card-id="<?= e($card['id']) ?>" aria-pressed="false">
                                <span class="image-map-card-icon"><i class="ph ph-<?= e($card['icon']) ?>"></i></span>
                                <span class="image-map-card-content">
                                    <strong class="image-map-card-title"><?= e($card['title']) ?></strong>
                                    <small class="image-map-card-meta">
                                        <?php if ($card['x'] !== null) { ?>
                                            X: <?= number_format($card['x'], 2, '.', '') ?>% · Y: <?= number_format($card['y'], 2, '.', '') ?>%
                                        <?php } else { ?>
                                            <?= lang('Not positioned', 'Nicht positioniert') ?>
                                        <?php } ?>
                                    </small>
                                </span>
                            </button>
                        <?php } ?>
                    </div>

                    <button type="button" class="btn link danger mt-15 d-none" id="remove-image-map-placement">
                        <i class="ph ph-x"></i> <?= lang('Remove position', 'Position entfernen') ?>
                    </button>
                </aside>

                <section>
                    <div class="alert primary image-map-help" id="image-map-help" aria-live="polite">
                        <i class="ph ph-cursor-click"></i>
                        <span><?= lang('Select a card on the left to begin.', 'Wähle links eine Karte aus, um zu beginnen.') ?></span>
                    </div>
                    <div class="image-map-stage-shell">
                        <div class="image-map-stage" id="image-map-stage">
                            <img
                                src="<?= ROOTPATH ?>/uploads/<?= e($backgroundFile) ?>?v=<?= strtotime((string) ($backgroundImage['uploaded'] ?? 'now')) ?>"
                                alt="<?= lang('Image map background', 'Hintergrund der Image-Map') ?>"
                                draggable="false">
                        </div>
                    </div>
                </section>
            </div>

            <div id="image-map-placement-inputs"></div>
            <div class="image-map-actions">
                <span class="text-muted" id="image-map-position-count"></span>
                <button type="submit" class="btn success">
                    <i class="ph ph-floppy-disk"></i> <?= lang('Save positions', 'Positionen speichern') ?>
                </button>
            </div>
        </form>
    <?php } ?>
</div>

<?php if ($hasBackgroundImage && !empty($editorCards)) { ?>
<script>
    (function() {
        const cards = <?= json_encode($editorCards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const labels = <?= json_encode([
            'choosePosition' => lang('Now click the desired position on the image.', 'Klicke jetzt auf die gewünschte Position im Bild.'),
            'notPositioned' => lang('Not positioned', 'Nicht positioniert'),
            'positioned' => lang('cards positioned', 'Karten positioniert'),
            'unsaved' => lang('You have unsaved changes to the image map.', 'Du hast ungespeicherte Änderungen an der Image-Map.'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const stage = document.getElementById('image-map-stage');
        const form = document.getElementById('image-map-editor-form');
        const removeButton = document.getElementById('remove-image-map-placement');
        const placements = {};
        let selectedId = null;
        let dirty = false;

        cards.forEach(card => {
            if (card.x !== null && card.y !== null) placements[card.id] = { x: card.x, y: card.y };
        });

        function selectCard(cardId) {
            selectedId = cardId;
            document.querySelectorAll('.image-map-card').forEach(element => {
                const active = element.dataset.cardId === cardId;
                element.classList.toggle('active', active);
                element.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            document.querySelectorAll('.image-map-marker').forEach(element => {
                element.classList.toggle('active', element.dataset.cardId === cardId);
            });
            removeButton.classList.toggle('d-none', !placements[cardId]);
            document.querySelector('#image-map-help span').textContent = labels.choosePosition;
        }

        function render() {
            stage.querySelectorAll('.image-map-marker').forEach(marker => marker.remove());

            cards.forEach(card => {
                const placement = placements[card.id];
                const cardElement = document.querySelector('.image-map-card[data-card-id="' + CSS.escape(card.id) + '"]');
                const meta = cardElement.querySelector('.image-map-card-meta');

                if (!placement) {
                    meta.textContent = labels.notPositioned;
                    return;
                }

                meta.textContent = 'X: ' + placement.x.toFixed(2) + '% · Y: ' + placement.y.toFixed(2) + '%';
                const marker = document.createElement('button');
                marker.type = 'button';
                marker.className = 'image-map-marker' + (selectedId === card.id ? ' active' : '');
                marker.dataset.cardId = card.id;
                marker.style.left = placement.x + '%';
                marker.style.top = placement.y + '%';
                marker.title = card.title;
                marker.setAttribute('aria-label', card.title);
                marker.innerHTML = '<i class="ph ph-' + card.icon + '"></i>';
                marker.addEventListener('click', event => {
                    event.stopPropagation();
                    selectCard(card.id);
                });
                stage.appendChild(marker);
            });

            document.getElementById('image-map-position-count').textContent =
                Object.keys(placements).length + ' / ' + cards.length + ' ' + labels.positioned;
            removeButton.classList.toggle('d-none', !selectedId || !placements[selectedId]);
        }

        document.getElementById('image-map-card-list').addEventListener('click', event => {
            const card = event.target.closest('.image-map-card');
            if (card) selectCard(card.dataset.cardId);
        });

        stage.addEventListener('click', event => {
            if (!selectedId || event.target.closest('.image-map-marker')) return;
            const bounds = stage.getBoundingClientRect();
            placements[selectedId] = {
                x: Math.max(0, Math.min(100, (event.clientX - bounds.left) / bounds.width * 100)),
                y: Math.max(0, Math.min(100, (event.clientY - bounds.top) / bounds.height * 100))
            };
            dirty = true;
            render();
        });

        removeButton.addEventListener('click', () => {
            if (!selectedId || !placements[selectedId]) return;
            delete placements[selectedId];
            dirty = true;
            render();
        });

        form.addEventListener('submit', () => {
            const inputContainer = document.getElementById('image-map-placement-inputs');
            inputContainer.innerHTML = '';
            Object.entries(placements).forEach(([cardId, placement]) => {
                ['x', 'y'].forEach(axis => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'placements[' + cardId + '][' + axis + ']';
                    input.value = placement[axis].toFixed(2);
                    inputContainer.appendChild(input);
                });
            });
            dirty = false;
        });

        window.addEventListener('beforeunload', event => {
            if (!dirty) return;
            event.preventDefault();
            event.returnValue = labels.unsaved;
        });

        render();
    })();
</script>
<?php } ?>
