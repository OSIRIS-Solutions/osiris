<h1>
    <i class="ph-duotone ph-person-simple-circle"></i>
    <?= lang('common.accessibility') ?>
</h1>
<p>
    <?= lang('common.osiris_is_committed_to_making_our_platform_accessible_to_all_users_we_conti') ?> <a href="https://osiris-solutions.de/contact" target="_blank">https://osiris-solutions.de/contact</a>.
</p>

<form action="<?= ROOTPATH ?>/set-preferences" method="get" class="box padded">
    <h2 class="title">
        <?= lang('common.accessibility_settings') ?>
    </h2>
    <input type="hidden" name="accessibility[check]">
    <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">

    <div class="form-group">
        <div class="custom-checkbox">
            <input type="checkbox" id="set-contrast" name="accessibility[contrast]" value="high-contrast" <?= !empty($_COOKIE['D3-accessibility-contrast'] ?? '') ? 'checked' : '' ?>>
            <label for="set-contrast"><?= lang('header.high_contrast') ?></label><br>
            <small class="text-muted">
                <?= lang('header.high_contrast_description') ?>
            </small>
        </div>
    </div>
    <div class="form-group">
        <div class="custom-checkbox">
            <input type="checkbox" id="set-transitions" name="accessibility[transitions]" value="without-transitions" <?= !empty($_COOKIE['D3-accessibility-transitions'] ?? '') ? 'checked' : '' ?>>
            <label for="set-transitions"><?= lang('header.reduce_motion') ?></label><br>
            <small class="text-muted">
                <?= lang('header.reduce_motion_description') ?>
            </small>
        </div>
    </div>
    <div class="form-group">
        <div class="custom-checkbox">
            <input type="checkbox" id="set-dyslexia" name="accessibility[dyslexia]" value="dyslexia" <?= !empty($_COOKIE['D3-accessibility-dyslexia'] ?? '') ? 'checked' : '' ?>>
            <label for="set-dyslexia"><?= lang('header.dyslexia_mode') ?></label><br>
            <small class="text-muted">
                <?= lang('header.dyslexia_mode_description') ?>
            </small>
        </div>
    </div>
    <button class="btn primary"><?= lang('common.apply') ?></button>
</form>