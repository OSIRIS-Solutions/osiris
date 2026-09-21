<!-- Command Palette -->
<div class="cp" role="dialog" aria-modal="true" aria-label="Global search">
    <div class="cp-backdrop"></div>

    <div class="cp-panel" role="document">
        <div class="cp-header">
            <div class="cp-search">
                <span class="cp-icon" aria-hidden="true">⌕</span>
                <input
                    class="cp-input"
                    id="osCpInput"
                    type="text"
                    placeholder="<?= lang('navigation.search') ?>"
                    value="" />
                <div class="cp-kbd" aria-hidden="true">
                    <span class="os-kbd"><?= (stripos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Mac') !== false) ? '⌘' : lang('common.ctrl') ?></span><span class="os-kbd">K</span>
                </div>
            </div>

            <div class="cp-hint">
                <span class="os-kbd">↑↓</span> <?= lang('navigation.navigate') ?>
                <span class="os-kbd">↵</span> <?= lang('navigation.go') ?>
                <span class="os-kbd">Esc</span> <?= lang('action.close') ?>
            </div>
        </div>

        <div class="cp-body" id="osCpResults">
        </div>

        <div class="cp-footer">
            <span class="cp-footerLeft"><?= lang('navigation.osiris_search') ?></span>
            <span class="cp-footerRight"><?= lang('navigation.type_to_search_for_users_groups_organizations_and_more') ?></span>
        </div>
    </div>
</div>

<script src="<?= ROOTPATH ?>/js/command-palette.js"></script>