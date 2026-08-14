<?php

/**
 * Image gallery and image management for organizational groups
 *
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       2.0.0
 *
 * @copyright   Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author      Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$groupImages = DB::doc2Arr($group['images'] ?? []);
usort($groupImages, function ($a, $b) {
    $order = ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
    if ($order !== 0) return $order;
    return strcmp($b['uploaded_at'] ?? '', $a['uploaded_at'] ?? '');
});

$galleryImages = [];
foreach ($groupImages as $image) {
    $captionEn = trim($image['caption'] ?? '');
    $captionDe = trim($image['caption_de'] ?? '');
    $caption = lang($captionEn ?: $captionDe, $captionDe ?: $captionEn);
    $details = [];
    if (!empty($image['taken_at'])) {
        $timestamp = strtotime($image['taken_at']);
        if ($timestamp !== false) {
            $details[] = lang(date('F j, Y', $timestamp), date('d.m.Y', $timestamp));
        }
    }
    if (!empty($image['credits'])) {
        $details[] = lang('Photo: ', 'Foto: ') . $image['credits'];
    }
    $galleryImages[] = [
        'src' => ROOTPATH . '/uploads/' . ($image['file'] ?? ''),
        'caption' => $caption,
        'details' => implode(' · ', $details),
    ];
}
?>

<?php if (!empty($groupImages) || $edit_perm) { ?>
    <div id="images" class="mt-20">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="my-0"><?= lang('Images', 'Bilder') ?></h5>
            <?php if ($edit_perm) { ?>
                <a class="btn small" href="#manage-group-images">
                    <i class="ph ph-images ph-fw"></i>
                    <?= lang('Manage images', 'Bilder verwalten') ?>
                </a>
            <?php } ?>
        </div>

        <?php if (empty($groupImages)) { ?>
            <p class="text-muted">
                <?= lang(
                    'No images have been uploaded for this unit yet.',
                    'Für diese Einheit wurden noch keine Bilder hochgeladen.'
                ) ?>
            </p>
        <?php } else { ?>
            <div class="row row-eq-spacing mt-5">
                <?php foreach ($groupImages as $index => $image) {
                    $captionEn = trim($image['caption'] ?? '');
                    $captionDe = trim($image['caption_de'] ?? '');
                    $caption = lang($captionEn ?: $captionDe, $captionDe ?: $captionEn);
                    $alt = $caption ?: lang('Image of the organizational unit', 'Bild der Organisationseinheit');
                ?>
                    <div class="col-sm-6 col-md-4">
                        <a href="#group-image-modal" class="card p-0 overflow-hidden d-block" onclick="showGroupImage(<?= $index ?>)">
                            <img
                                class="group-gallery-image"
                                src="<?= ROOTPATH ?>/uploads/<?= e($image['thumbnail'] ?? $image['file'] ?? '') ?>"
                                alt="<?= e($alt) ?>"
                                loading="lazy"
                                decoding="async"
                            >
                        </a>
                        <?php if ($caption !== '') { ?>
                            <small class="d-block mt-5"><?= e($caption) ?></small>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <style>
        .group-gallery-image {
            display: block;
            width: 100%;
            height: 18rem;
            object-fit: cover;
        }

        .group-image-full {
            display: block;
            max-width: 100%;
            max-height: calc(100vh - 20rem);
            width: auto;
            height: auto;
            margin: 0 auto;
        }
    </style>

    <?php if (!empty($groupImages)) { ?>
        <div class="modal modal-full" id="group-image-modal" tabindex="-1" role="dialog" aria-modal="true" aria-label="<?= lang('Image gallery', 'Bildergalerie') ?>">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="close" role="button" aria-label="<?= lang('Close', 'Schließen') ?>" href="#close-modal">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <img id="group-image-full" class="group-image-full" src="" alt="">
                    <div class="text-center mt-10">
                        <p id="group-image-modal-caption" class="font-weight-bold mb-0"></p>
                        <small id="group-image-details" class="text-muted"></small>
                    </div>
                    <?php if (count($groupImages) > 1) { ?>
                        <div class="btn-group d-flex justify-content-center mt-10">
                            <button type="button" class="btn" onclick="changeGroupImage(-1)">
                                <i class="ph ph-caret-left"></i>
                                <?= lang('Previous', 'Zurück') ?>
                            </button>
                            <button type="button" class="btn" onclick="changeGroupImage(1)">
                                <?= lang('Next', 'Weiter') ?>
                                <i class="ph ph-caret-right"></i>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <script>
            const GROUP_IMAGES = <?= json_encode($galleryImages, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            let currentGroupImage = 0;

            function showGroupImage(index) {
                currentGroupImage = index;
                const image = GROUP_IMAGES[currentGroupImage];
                $('#group-image-full').attr('src', image.src).attr('alt', image.caption);
                $('#group-image-modal-caption').text(image.caption).toggle(image.caption !== '');
                $('#group-image-details').text(image.details).toggle(image.details !== '');
            }

            function changeGroupImage(direction) {
                currentGroupImage = (currentGroupImage + direction + GROUP_IMAGES.length) % GROUP_IMAGES.length;
                showGroupImage(currentGroupImage);
            }

            $(document).on('keydown', function (event) {
                if (window.location.hash !== '#group-image-modal') return;
                if (event.key === 'ArrowLeft') changeGroupImage(-1);
                if (event.key === 'ArrowRight') changeGroupImage(1);
                if (event.key === 'Escape') window.location.hash = '#close-modal';
            });
        </script>
    <?php } ?>
<?php } ?>

<?php if ($edit_perm) { ?>
    <div class="modal" id="manage-group-images" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="manage-group-images-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="close" role="button" aria-label="<?= lang('Close', 'Schließen') ?>" href="#close-modal">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h2 id="manage-group-images-title" class="title"><?= lang('Manage images', 'Bilder verwalten') ?></h2>

                <div class="box padded">
                    <h5 class="mt-0"><?= lang('Upload image', 'Bild hochladen') ?></h5>
                    <form action="<?= ROOTPATH ?>/crud/groups/images/<?= $group['_id'] ?>" method="post" enctype="multipart/form-data">
                        <div class="custom-file">
                            <input type="file" id="group-image-file" name="file" accept="image/jpeg,image/png,image/webp" maxsize="16000000" required>
                            <label for="group-image-file"><?= lang('Select image', 'Bild auswählen') ?></label>
                        </div>
                        <small class="text-muted">
                            <?= lang('JPEG, PNG or WebP; maximum 16 MB.', 'JPEG, PNG oder WebP; maximal 16 MB.') ?>
                        </small>

                        <div class="row row-eq-spacing">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="group-image-caption"><?= lang('Caption', 'Bildunterschrift') ?> (EN)</label>
                                    <input type="text" class="form-control" id="group-image-caption" name="caption" maxlength="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="group-image-caption-de"><?= lang('Caption', 'Bildunterschrift') ?> (DE)</label>
                                    <input type="text" class="form-control" id="group-image-caption-de" name="caption_de" maxlength="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="group-image-date"><?= lang('Date taken', 'Aufnahmedatum') ?></label>
                                    <input type="date" class="form-control" id="group-image-date" name="taken_at">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="group-image-credits"><?= lang('Photo credit', 'Bildnachweis') ?></label>
                                    <input type="text" class="form-control" id="group-image-credits" name="credits" maxlength="255">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="public" value="0">
                            <div class="custom-switch">
                                <input type="checkbox" id="group-image-public" name="public" value="1">
                                <label for="group-image-public">
                                    <?= lang('Make image publicly available', 'Bild öffentlich verfügbar machen') ?>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn primary">
                            <i class="ph ph-upload-simple"></i>
                            <?= lang('Upload image', 'Bild hochladen') ?>
                        </button>
                    </form>
                </div>

                <?php foreach ($groupImages as $image) { ?>
                    <div class="box padded">
                        <div class="row row-eq-spacing mt-0">
                            <div class="col-sm-4">
                                <img
                                    class="img-fluid"
                                    src="<?= ROOTPATH ?>/uploads/<?= e($image['thumbnail'] ?? $image['file'] ?? '') ?>"
                                    alt=""
                                    loading="lazy"
                                >
                            </div>
                            <div class="col-sm-8">
                                <form action="<?= ROOTPATH ?>/crud/groups/images/<?= $group['_id'] ?>/<?= e($image['id'] ?? '') ?>/update" method="post">
                                    <div class="form-group">
                                        <label for="caption-<?= e($image['id'] ?? '') ?>"><?= lang('Caption', 'Bildunterschrift') ?> (EN)</label>
                                        <input type="text" class="form-control" id="caption-<?= e($image['id'] ?? '') ?>" name="caption" maxlength="1000" value="<?= e($image['caption'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="caption-de-<?= e($image['id'] ?? '') ?>"><?= lang('Caption', 'Bildunterschrift') ?> (DE)</label>
                                        <input type="text" class="form-control" id="caption-de-<?= e($image['id'] ?? '') ?>" name="caption_de" maxlength="1000" value="<?= e($image['caption_de'] ?? '') ?>">
                                    </div>
                                    <div class="row row-eq-spacing mt-0">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date-<?= e($image['id'] ?? '') ?>"><?= lang('Date taken', 'Aufnahmedatum') ?></label>
                                                <input type="date" class="form-control" id="date-<?= e($image['id'] ?? '') ?>" name="taken_at" value="<?= e($image['taken_at'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="credits-<?= e($image['id'] ?? '') ?>"><?= lang('Photo credit', 'Bildnachweis') ?></label>
                                                <input type="text" class="form-control" id="credits-<?= e($image['id'] ?? '') ?>" name="credits" maxlength="255" value="<?= e($image['credits'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="hidden" name="public" value="0">
                                        <div class="custom-switch">
                                            <input
                                                type="checkbox"
                                                id="public-<?= e($image['id'] ?? '') ?>"
                                                name="public"
                                                value="1"
                                                <?= !empty($image['public']) ? 'checked' : '' ?>
                                            >
                                            <label for="public-<?= e($image['id'] ?? '') ?>">
                                                <?= lang('Make image publicly available', 'Bild öffentlich verfügbar machen') ?>
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn secondary small">
                                        <i class="ph ph-check"></i>
                                        <?= lang('Save', 'Speichern') ?>
                                    </button>
                                </form>

                                <form
                                    action="<?= ROOTPATH ?>/crud/groups/images/<?= $group['_id'] ?>/<?= e($image['id'] ?? '') ?>/delete"
                                    method="post"
                                    class="d-inline"
                                    onsubmit="return confirm('<?= e(lang('Do you really want to delete this image?', 'Möchtest du dieses Bild wirklich löschen?')) ?>')"
                                >
                                    <button type="submit" class="btn danger small mt-10">
                                        <i class="ph ph-trash"></i>
                                        <?= lang('Delete', 'Löschen') ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>
