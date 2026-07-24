<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-student"></i>
        <?= lang('ORCID Settings', 'ORCID Einstellungen') ?>
    </h1>

    <p>
        <?= lang('To use the ORCID integration, you need to register an application at <a href="https://orcid.org/developer-tools" target="_blank">https://orcid.org/developer-tools</a>. Follow this  https://orcid.org/developer-tools', 'Um die ORCID-Integration zu nutzen, müssen Sie eine Anwendung unter <a href="https://orcid.org/developer-tools" target="_blank">https://orcid.org/developer-tools</a> registrieren und die Client-ID und das Client-Geheimnis unten angeben.') ?>
    </p>

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">

        <?php
        $orcid = $Settings->get('orcid');
        ?>
        <div class="form-group">
            <label for="client_id">Client ID</label>
            <input type="float" class="form-control" name="general[orcid][client_id]" value="<?= $orcid['client_id'] ?? '' ?>">
        </div>
        <div class="form-group">
            <label for="client_secret">Client Secret</label>
            <input type="float" class="form-control" name="general[orcid][client_secret]" value="<?= $orcid['client_secret'] ?? '' ?>">
        </div>
        <div class="form-group">
            <label for="orcid_api"><?= lang('Choose ORCID API', 'Wähle ORCID API') ?></label>
            <select class="form-control" name="general[orcid][api]" id="orcid_api">
                <option value="public" <?= ($orcid['api'] ?? 'public') == 'public' ? 'selected' : '' ?>><?= lang('Public API', 'Öffentliche API') ?></option>
                <option value="member" <?= ($orcid['api'] ?? 'public') == 'member' ? 'selected' : '' ?>><?= lang('Member API', 'Mitglieder API') ?></option>
                <option value="sandbox" <?= ($orcid['api'] ?? 'public') == 'sandbox' ? 'selected' : '' ?>><?= lang('Sandbox API', 'Sandbox API') ?></option>
            </select>
        </div>

        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>

    </form>
</div>