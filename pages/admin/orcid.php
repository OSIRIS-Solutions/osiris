<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-student"></i>
        <?= lang('admin.orcid_settings') ?>
    </h1>

    <p>
        <?= lang('admin.the_orcid_integration_allows_users_to_link_their_orcid_account_to_their_osi') ?>
    </p>
    <p>
        <?= lang('admin.to_use_the_orcid_integration_you_need_to_register_an_application_at_https_o') ?>
    </p>
    <p>
        <?= lang('admin.example_settings_for_orcid_developer_tools_application_name_emsp_osiris_you') ?>
    </p>
    <p>
        <?= lang('admin.for_more_information_on_how_to_register_an_application_please_refer_to_the') ?>
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
            <label for="orcid_api"><?= lang('admin.choose_orcid_api') ?></label>
            <select class="form-control" name="general[orcid][api]" id="orcid_api">
                <option value="public" <?= ($orcid['api'] ?? 'public') == 'public' ? 'selected' : '' ?>><?= lang('admin.public_api') ?></option>
                <option value="member" <?= ($orcid['api'] ?? 'public') == 'member' ? 'selected' : '' ?>><?= lang('admin.member_api') ?></option>
                <option value="sandbox" <?= ($orcid['api'] ?? 'public') == 'sandbox' ? 'selected' : '' ?>><?= lang('admin.sandbox_api') ?></option>
            </select>
        </div>

        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('action.save') ?>
        </button>

    </form>
</div>