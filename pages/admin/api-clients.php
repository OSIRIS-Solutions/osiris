<?php

/**
 * Page for managing API clients and their access
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/api-clients
 *
 * @package OSIRIS
 * @since 2.2.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

include_once BASEPATH . '/php/ApiClient.php';

$surfaceLabels = [
    'api' => lang('Standard REST API', 'Standard-REST-API'),
    'mcp' => lang('MCP API', 'MCP-API'),
];
$scopeLabels = [
    'activities.read' => lang('Read activities', 'Aktivitäten lesen'),
    // 'activities.write' => lang('Modify activities', 'Aktivitäten verändern'), // only to enrich openalex data, so for now not for general use
    'catalogs.read' => lang('Read catalogs (e.g. organizational groups)', 'Kataloge lesen (z.B. Organisationsgruppen)'),
    'dashboards.read' => lang('Read dashboard data', 'Dashboard-Daten lesen'),
    'events.read' => lang('Read events and calendar data', 'Veranstaltungs- und Kalenderdaten lesen'),
    'infrastructures.read' => lang('Read infrastructures', 'Infrastrukturen lesen'),
    'journals.read' => lang('Read journals', 'Zeitschriften lesen'),
    'organizations.read' => lang('Read organizations', 'Organisationen lesen'),
    'persons.read' => lang('Read persons', 'Personen lesen'),
    'projects.read' => lang('Read projects and proposals', 'Projekte und Anträge lesen'),
    // 'reviews.read' => lang('Read workflows', 'Workflows lesen'), // only for the interface so currently not for general use
    'teaching.read' => lang('Read teaching data', 'Lehrdaten lesen'),
];
$clients = $osiris->apiClients->find(
    [],
    [
        'sort' => ['created_at' => -1, 'name' => 1],
        'projection' => ['key_hash' => 0],
    ]
)->toArray();
$credentials = $_SESSION['api_client_credentials'] ?? null;
unset($_SESSION['api_client_credentials']);
?>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-key"></i>
        <?= lang('API Clients', 'API-Clients') ?>
    </h1>

    <p class="text-muted">
        <?= lang(
            'Create separate credentials for applications that access OSIRIS. Permissions and API areas are evaluated for every request. Secrets are stored only as hashes and can only be copied immediately after creation or rotation.',
            'Erstelle separate Zugangsdaten für Anwendungen, die auf OSIRIS zugreifen. Berechtigungen und API-Bereiche werden bei jeder Anfrage geprüft. Secrets werden ausschließlich gehasht gespeichert und können nur direkt nach der Erstellung oder Rotation kopiert werden.'
        ) ?>
    </p>

    <?php if (!empty($credentials)) { ?>
        <div class="alert success mb-20">
            <h5 class="title">
                <i class="ph ph-warning"></i>
                <?= lang('Copy these credentials now', 'Zugangsdaten jetzt kopieren') ?>
            </h5>
            <p><?= lang('The secret will not be displayed again.', 'Das Secret wird nicht erneut angezeigt.') ?></p>
            <div class="form-group">
                <label><?= lang('Client ID', 'Client-ID') ?></label>
                <div class="input-group">
                    <code class="form-control" id="new-api-client-id"><?= e($credentials['client_id']) ?></code>
                    <div class="input-group-append">
                        <button class="btn" type="button" onclick="copyApiClientValue('new-api-client-id')">
                            <i class="ph ph-clipboard"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label><?= lang('API secret', 'API-Secret') ?></label>
                <div class="input-group">
                    <code class="form-control" id="new-api-client-secret"><?= e($credentials['secret']) ?></code>
                    <div class="input-group-append">
                        <button class="btn" type="button" onclick="copyApiClientValue('new-api-client-secret')">
                            <i class="ph ph-clipboard"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <a href="#add-api-client-modal" class="btn">
        <i class="ph ph-plus-circle"></i>
        <?= lang('Add API client', 'API-Client hinzufügen') ?>
    </a>

    <style>
        .form-group>label {
            font-weight: bold;
        }
    </style>

    <div class="modal" id="add-api-client-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a href="#close-modal" class="close" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="title">
                    <i class="ph ph-plus-circle"></i>
                    <?= lang('Add API client', 'API-Client hinzufügen') ?>
                </h5>

                <form action="<?= ROOTPATH ?>/crud/admin/api-clients/create" method="post">
                    <div class="form-group">
                        <label class="required" for="api-client-name"><?= lang('Name', 'Name') ?></label>
                        <input class="form-control" id="api-client-name" name="name" maxlength="200" required>
                    </div>
                    <div class="form-group">
                        <label for="api-client-description"><?= lang('Description', 'Beschreibung') ?></label>
                        <textarea class="form-control" id="api-client-description" name="description" maxlength="1000" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label><?= lang('Allowed API areas', 'Erlaubte API-Bereiche') ?></label>
                        <?php foreach ($surfaceLabels as $surface => $label) { ?>
                            <div class="custom-checkbox mb-5">
                                <input type="checkbox" id="surface-new-<?= e($surface) ?>" name="surfaces[]" value="<?= e($surface) ?>" <?= $surface === 'mcp' ? 'checked' : '' ?>>
                                <label for="surface-new-<?= e($surface) ?>"><?= e($label) ?></label>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label><?= lang('Permissions', 'Berechtigungen') ?></label>
                        <button class="btn small link" type="button" onclick="document.querySelectorAll('#add-api-client-modal input[name=\'scopes[]\']').forEach(el => el.checked = true)">
                            <?= lang('Select all', 'Alle auswählen') ?>
                        </button>
                        <button class="btn small link" type="button" onclick="document.querySelectorAll('#add-api-client-modal input[name=\'scopes[]\']').forEach(el => el.checked = false)">
                            <?= lang('Deselect all', 'Alle abwählen') ?>
                        </button>
                        <?php foreach ($scopeLabels as $scope => $label) { ?>
                            <div class="custom-checkbox mb-5">
                                <input type="checkbox" id="scope-new-<?= e($scope) ?>" name="scopes[]" value="<?= e($scope) ?>" <?= in_array($scope, ['catalogs.read', 'persons.read', 'activities.read', 'projects.read']) ? 'checked' : '' ?>>
                                <label for="scope-new-<?= e($scope) ?>"><?= e($label) ?></label>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="api-client-expires"><?= lang('Valid until', 'Gültig bis') ?></label>
                        <input class="form-control" type="date" id="api-client-expires" name="expires_at">
                        <small class="text-muted"><?= lang('Leave empty for no expiration date.', 'Leer lassen für unbegrenzte Gültigkeit.') ?></small>
                    </div>
                    <button class="btn success" type="submit">
                        <i class="ph ph-key"></i>
                        <?= lang('Create client', 'Client anlegen') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <h3><?= lang('Configured clients', 'Eingerichtete Clients') ?></h3>
    <?php if (empty($clients)) { ?>
        <p><?= lang('No API clients have been configured yet.', 'Es wurden noch keine API-Clients eingerichtet.') ?></p>
    <?php } ?>

    <?php foreach ($clients as $client) {
        $clientId = (string) ($client['client_id'] ?? '');
        $surfaces = DB::doc2Arr($client['surfaces'] ?? []);
        $scopes = DB::doc2Arr($client['scopes'] ?? []);
        $expired = !empty($client['expires_at']) && $client['expires_at'] < date('Y-m-d');
        $enabled = ($client['enabled'] ?? false) === true;
    ?>
        <details class="collapse-panel">
            <summary class="collapse-header">
                <div class="float-right">
                    <?php if (!$enabled) { ?>
                        <span class="badge danger"><?= lang('Disabled', 'Deaktiviert') ?></span>
                    <?php } elseif ($expired) { ?>
                        <span class="badge warning"><?= lang('Expired', 'Abgelaufen') ?></span>
                    <?php } else { ?>
                        <span class="badge success"><?= lang('Active', 'Aktiv') ?></span>
                    <?php } ?>
                </div>
                <h5 class="m-0"><?= e($client['name'] ?? $clientId) ?></h5>
                <?php if (!empty($client['description'])) { ?>
                    <small><?= e($client['description']) ?></small>
                <?php } ?>
            </summary>
            <div class="collapse-content">
                <p class="mt-0">
                    <b><?= lang('Client ID', 'Client-ID') ?>:</b>
                    <code id="client-id-<?= e($clientId) ?>"><?= e($clientId) ?></code>
                    <button class="btn small" type="button" onclick="copyApiClientValue('client-id-<?= e($clientId) ?>')">
                        <i class="ph ph-clipboard"></i>
                    </button>
                    <br>
                    <b><?= lang('Secret', 'Secret') ?>:</b> <code>••••<?= e($client['key_hint'] ?? '') ?></code>
                    <br>
                    <b><?= lang('API areas', 'API-Bereiche') ?>:</b>
                    <?= e(implode(', ', array_map(fn($surface) => $surfaceLabels[$surface] ?? $surface, $surfaces))) ?>
                    <br>
                    <b><?= lang('Valid until', 'Gültig bis') ?>:</b>
                    <?= empty($client['expires_at']) ? lang('Unlimited', 'Unbegrenzt') : e($client['expires_at']) ?>
                    <br>
                    <b><?= lang('Last used', 'Zuletzt verwendet') ?>:</b>
                    <?= empty($client['last_used_at']) ? lang('Never', 'Nie') : e($client['last_used_at']) ?>
                </p>
                <div class="mb-10">
                    <?php foreach ($scopes as $scope) { ?>
                        <span class="badge mr-5 mb-5"><?= e($scopeLabels[$scope] ?? $scope) ?></span>
                    <?php } ?>
                </div>

                <form action="<?= ROOTPATH ?>/crud/admin/api-clients/toggle/<?= e($clientId) ?>" method="post" class="d-inline">
                    <input type="hidden" name="enabled" value="<?= $enabled ? '0' : '1' ?>">
                    <button class="btn <?= $enabled ? 'warning' : 'success' ?>" type="submit">
                        <i class="ph ph-<?= $enabled ? 'pause' : 'play' ?>"></i>
                        <?= $enabled ? lang('Disable', 'Deaktivieren') : lang('Enable', 'Aktivieren') ?>
                    </button>
                </form>
                <form action="<?= ROOTPATH ?>/crud/admin/api-clients/rotate/<?= e($clientId) ?>" method="post" class="d-inline" onsubmit="return confirm('<?= lang('The previous secret will stop working immediately. Continue?', 'Das bisherige Secret wird sofort ungültig. Fortfahren?') ?>')">
                    <button class="btn" type="submit">
                        <i class="ph ph-arrows-clockwise"></i>
                        <?= lang('Rotate secret', 'Secret rotieren') ?>
                    </button>
                </form>
                <form action="<?= ROOTPATH ?>/crud/admin/api-clients/delete/<?= e($clientId) ?>" method="post" class="d-inline" onsubmit="return confirm('<?= lang('Delete this API client permanently?', 'Diesen API-Client dauerhaft löschen?') ?>')">
                    <button class="btn danger" type="submit">
                        <i class="ph ph-trash"></i>
                        <?= lang('Delete', 'Löschen') ?>
                    </button>
                </form>

                <details class="collapse-panel mt-20">
                    <summary class="collapse-header">
                        <i class="ph ph-pencil"></i>
                        <?= lang('Edit client', 'Client bearbeiten') ?>
                    </summary>
                    <div class="collapse-content">
                        <form action="<?= ROOTPATH ?>/crud/admin/api-clients/update/<?= e($clientId) ?>" method="post">
                            <div class="form-group">
                                <label class="required" for="name-<?= e($clientId) ?>"><?= lang('Name', 'Name') ?></label>
                                <input class="form-control" id="name-<?= e($clientId) ?>" name="name" maxlength="200" value="<?= e($client['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description-<?= e($clientId) ?>"><?= lang('Description', 'Beschreibung') ?></label>
                                <textarea class="form-control" id="description-<?= e($clientId) ?>" name="description" maxlength="1000" rows="2"><?= e($client['description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label><?= lang('Allowed API areas', 'Erlaubte API-Bereiche') ?></label>
                                <?php foreach ($surfaceLabels as $surface => $label) { ?>
                                    <div class="custom-checkbox mb-5">
                                        <input type="checkbox" id="surface-<?= e($clientId) ?>-<?= e($surface) ?>" name="surfaces[]" value="<?= e($surface) ?>" <?= in_array($surface, $surfaces, true) ? 'checked' : '' ?>>
                                        <label for="surface-<?= e($clientId) ?>-<?= e($surface) ?>"><?= e($label) ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label><?= lang('Permissions', 'Berechtigungen') ?></label>
                                <?php foreach ($scopeLabels as $scope => $label) { ?>
                                    <div class="custom-checkbox mb-5">
                                        <input type="checkbox" id="scope-<?= e($clientId) ?>-<?= e($scope) ?>" name="scopes[]" value="<?= e($scope) ?>" <?= in_array($scope, $scopes, true) ? 'checked' : '' ?>>
                                        <label for="scope-<?= e($clientId) ?>-<?= e($scope) ?>"><?= e($label) ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label for="expires-<?= e($clientId) ?>"><?= lang('Valid until', 'Gültig bis') ?></label>
                                <input class="form-control" type="date" id="expires-<?= e($clientId) ?>" name="expires_at" value="<?= e($client['expires_at'] ?? '') ?>">
                            </div>
                            <button class="btn success" type="submit">
                                <i class="ph ph-floppy-disk"></i>
                                <?= lang('Save', 'Speichern') ?>
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </details>
    <?php } ?>

</div>

<script>
    function copyApiClientValue(id) {
        const value = document.getElementById(id).textContent;
        navigator.clipboard.writeText(value);
        toastSuccess('<?= lang('Copied to clipboard.', 'In die Zwischenablage kopiert.') ?>');
    }
</script>