<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-student"></i>
        <?= lang('ORCID Settings', 'ORCID Einstellungen') ?>
    </h1>

    <p>
        <?= lang(
            'The ORCID integration allows users to link their ORCID account to their OSIRIS account. 
            This enables automatic retrieval of publications and other information from ORCID.',
            'Die ORCID-Integration ermöglicht es Benutzern, ihr ORCID-Konto mit ihrem OSIRIS-Konto zu verknüpfen. 
            Dadurch können automatisch Publikationen und andere Informationen von ORCID abgerufen werden.') ?>
    </p>
    <p>
        <?= lang(
            'To use the ORCID integration, you need to register an application at 
            <a href="https://orcid.org/developer-tools" target="_blank">https://orcid.org/developer-tools</a>.', 
            'Um die ORCID-Integration zu nutzen, müssen Sie Ihre OSIRIS Instanz unter 
            <a href="https://orcid.org/developer-tools" target="_blank">https://orcid.org/developer-tools</a> 
            registrieren und die Client-ID und das Client-Geheimnis unten angeben.') ?>
    </p>
    <p>
        <?= lang(
            'Example Settings for ORCID developer tools: <br>
            <b>Application Name: </b> &emsp; OSIRIS-[Your institute acronym] <br>
            <b>Application URL: </b>&emsp; [Your OSIRIS instance URL] <br>
            <b>Application Description: </b>&emsp; OSIRIS-[Your institute acronym] ORCID Integration <br>
            <b>Redirect URI: </b>&emsp; [Your OSIRIS instance URL]/orcid/validate <br>',
            'Beispiel Einstellungen für ORCID-Entwicklertools: <br>
            <b>Anwendungsname: </b>&emsp;OSIRIS-[Ihr Institutsakronym] <br>
            <b>Anwendungs-URL: </b>&emsp;[Ihre OSIRIS-Instanz-URL] <br>
            <b>Anwendungsbeschreibung: </b>&emsp;OSIRIS-[Ihr Institutsakronym] ORCID-Integration <br>
            <b>Umleitungs-URI: </b>&emsp;[Ihre OSIRIS-Instanz-URL]/orcid/validate <br>') ?>
    </p>
    <p>
        <?= lang(
            'For more information on how to register an application, please refer to the 
            <a href="https://info.orcid.org/documentation/integration-guide/registering-a-public-api-client/" target="_blank">ORCID documentation</a>.',
            'Weitere Informationen zur Registrierung einer Anwendung finden Sie in der 
            <a href="https://info.orcid.org/de/documentation/integration-guide/registering-a-public-api-client/" target="_blank">ORCID Dokumentation</a>.') ?>
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