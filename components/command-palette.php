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
                    placeholder="<?= lang('Search…', 'Suchen…') ?>"
                    value="" />
                <div class="cp-kbd" aria-hidden="true">
                    <span class="os-kbd"><?= (stripos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Mac') !== false) ? '⌘' : lang('Ctrl', 'Strg') ?></span><span class="os-kbd">K</span>
                </div>
            </div>

            <div class="cp-hint">
                <span class="os-kbd">↑↓</span> <?= lang('Navigate', 'Navigieren') ?>
                <span class="os-kbd">↵</span> <?= lang('Go', 'Los') ?>
                <span class="os-kbd">Esc</span> <?= lang('Close', 'Schließen') ?>
            </div>
        </div>

        <div class="cp-body" id="osCpResults">
        </div>

        <div class="cp-footer">
            <span class="cp-footerLeft"><?= lang('OSIRIS Search', 'OSIRIS Suche') ?></span>
            <span class="cp-footerRight"><?= lang('Type to search for users, groups, organizations and more...', 'Tippe, um nach Benutzern, Gruppen, Organisationen und mehr zu suchen...') ?></span>
        </div>
    </div>
</div>

<script src="<?= ROOTPATH ?>/js/command-palette.js"></script>