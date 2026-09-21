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
    'api' => lang('admin.standard_rest_api'),
    'mcp' => lang('admin.mcp_api'),
];
$scopeLabels = [
    'activities.read' => lang('admin.read_activities'),
    // 'activities.write' => lang('Modify activities', 'Aktivitäten verändern'), // only to enrich openalex data, so for now not for general use
    'catalogs.read' => lang('admin.read_catalogs_e_g_organizational_groups'),
    'dashboards.read' => lang('admin.read_dashboard_data'),
    'events.read' => lang('admin.read_events_and_calendar_data'),
    'infrastructures.read' => lang('admin.read_infrastructures'),
    'journals.read' => lang('admin.read_journals'),
    'organizations.read' => lang('admin.read_organizations'),
    'persons.read' => lang('admin.read_persons'),
    'projects.read' => lang('admin.read_projects_and_proposals'),
    // 'reviews.read' => lang('Read workflows', 'Workflows lesen'), // only for the interface so currently not for general use
    'teaching.read' => lang('admin.read_teaching_data'),
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
        <?= lang('common.api_clients') ?>
    </h1>

    <p class="text-muted">
        <?= lang('admin.create_separate_credentials_for_applications_that_access_osiris_permissions') ?>
    </p>

    <?php if (!empty($credentials)) { ?>
        <div class="alert success mb-20">
            <h5 class="title">
                <i class="ph ph-warning"></i>
                <?= lang('admin.copy_these_credentials_now') ?>
            </h5>
            <p><?= lang('admin.the_secret_will_not_be_displayed_again') ?></p>
            <div class="form-group">
                <label><?= lang('admin.client_id') ?></label>
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
                <label><?= lang('admin.api_secret') ?></label>
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
        <?= lang('admin.add_api_client') ?>
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
                    <?= lang('admin.add_api_client') ?>
                </h5>

                <form action="<?= ROOTPATH ?>/crud/admin/api-clients/create" method="post">
                    <div class="form-group">
                        <label class="required" for="api-client-name"><?= lang('common.name') ?></label>
                        <input class="form-control" id="api-client-name" name="name" maxlength="200" required>
                    </div>
                    <div class="form-group">
                        <label for="api-client-description"><?= lang('common.description') ?></label>
                        <textarea class="form-control" id="api-client-description" name="description" maxlength="1000" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label><?= lang('admin.allowed_api_areas') ?></label>
                        <?php foreach ($surfaceLabels as $surface => $label) { ?>
                            <div class="custom-checkbox mb-5">
                                <input type="checkbox" id="surface-new-<?= e($surface) ?>" name="surfaces[]" value="<?= e($surface) ?>" <?= $surface === 'mcp' ? 'checked' : '' ?>>
                                <label for="surface-new-<?= e($surface) ?>"><?= e($label) ?></label>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label><?= lang('admin.permissions') ?></label>
                        <button class="btn small link" type="button" onclick="document.querySelectorAll('#add-api-client-modal input[name=\'scopes[]\']').forEach(el => el.checked = true)">
                            <?= lang('admin.select_all') ?>
                        </button>
                        <button class="btn small link" type="button" onclick="document.querySelectorAll('#add-api-client-modal input[name=\'scopes[]\']').forEach(el => el.checked = false)">
                            <?= lang('admin.deselect_all') ?>
                        </button>
                        <?php foreach ($scopeLabels as $scope => $label) { ?>
                            <div class="custom-checkbox mb-5">
                                <input type="checkbox" id="scope-new-<?= e($scope) ?>" name="scopes[]" value="<?= e($scope) ?>" <?= in_array($scope, ['catalogs.read', 'persons.read', 'activities.read', 'projects.read']) ? 'checked' : '' ?>>
                                <label for="scope-new-<?= e($scope) ?>"><?= e($label) ?></label>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="api-client-expires"><?= lang('common.valid_until') ?></label>
                        <input class="form-control" type="date" id="api-client-expires" name="expires_at">
                        <small class="text-muted"><?= lang('admin.leave_empty_for_no_expiration_date') ?></small>
                    </div>
                    <button class="btn success" type="submit">
                        <i class="ph ph-key"></i>
                        <?= lang('admin.create_client') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <h3><?= lang('admin.configured_clients') ?></h3>
    <?php if (empty($clients)) { ?>
        <p><?= lang('admin.no_api_clients_have_been_configured_yet') ?></p>
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
                        <span class="badge danger"><?= lang('common.disabled') ?></span>
                    <?php } elseif ($expired) { ?>
                        <span class="badge warning"><?= lang('projects.expired') ?></span>
                    <?php } else { ?>
                        <span class="badge success"><?= lang('common.active') ?></span>
                    <?php } ?>
                </div>
                <h5 class="m-0"><?= e($client['name'] ?? $clientId) ?></h5>
                <?php if (!empty($client['description'])) { ?>
                    <small><?= e($client['description']) ?></small>
                <?php } ?>
            </summary>
            <div class="collapse-content">
                <p class="mt-0">
                    <b><?= lang('admin.client_id') ?>:</b>
                    <code id="client-id-<?= e($clientId) ?>"><?= e($clientId) ?></code>
                    <button class="btn small" type="button" onclick="copyApiClientValue('client-id-<?= e($clientId) ?>')">
                        <i class="ph ph-clipboard"></i>
                    </button>
                    <br>
                    <b><?= lang('admin.secret') ?>:</b> <code>••••<?= e($client['key_hint'] ?? '') ?></code>
                    <br>
                    <b><?= lang('admin.api_areas') ?>:</b>
                    <?= e(implode(', ', array_map(fn($surface) => $surfaceLabels[$surface] ?? $surface, $surfaces))) ?>
                    <br>
                    <b><?= lang('common.valid_until') ?>:</b>
                    <?= empty($client['expires_at']) ? lang('common.unlimited') : e($client['expires_at']) ?>
                    <br>
                    <b><?= lang('admin.last_used') ?>:</b>
                    <?= empty($client['last_used_at']) ? lang('common.never') : e($client['last_used_at']) ?>
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
                        <?= $enabled ? lang('admin.disable') : lang('admin.enable') ?>
                    </button>
                </form>
                <form action="<?= ROOTPATH ?>/crud/admin/api-clients/rotate/<?= e($clientId) ?>" method="post" class="d-inline" onsubmit="return confirm('<?= lang('admin.the_previous_secret_will_stop_working_immediately_continue') ?>')">
                    <button class="btn" type="submit">
                        <i class="ph ph-arrows-clockwise"></i>
                        <?= lang('admin.rotate_secret') ?>
                    </button>
                </form>
                <form action="<?= ROOTPATH ?>/crud/admin/api-clients/delete/<?= e($clientId) ?>" method="post" class="d-inline" onsubmit="return confirm('<?= lang('admin.delete_this_api_client_permanently') ?>')">
                    <button class="btn danger" type="submit">
                        <i class="ph ph-trash"></i>
                        <?= lang('action.delete') ?>
                    </button>
                </form>

                <details class="collapse-panel mt-20">
                    <summary class="collapse-header">
                        <i class="ph ph-pencil"></i>
                        <?= lang('admin.edit_client') ?>
                    </summary>
                    <div class="collapse-content">
                        <form action="<?= ROOTPATH ?>/crud/admin/api-clients/update/<?= e($clientId) ?>" method="post">
                            <div class="form-group">
                                <label class="required" for="name-<?= e($clientId) ?>"><?= lang('common.name') ?></label>
                                <input class="form-control" id="name-<?= e($clientId) ?>" name="name" maxlength="200" value="<?= e($client['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description-<?= e($clientId) ?>"><?= lang('common.description') ?></label>
                                <textarea class="form-control" id="description-<?= e($clientId) ?>" name="description" maxlength="1000" rows="2"><?= e($client['description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label><?= lang('admin.allowed_api_areas') ?></label>
                                <?php foreach ($surfaceLabels as $surface => $label) { ?>
                                    <div class="custom-checkbox mb-5">
                                        <input type="checkbox" id="surface-<?= e($clientId) ?>-<?= e($surface) ?>" name="surfaces[]" value="<?= e($surface) ?>" <?= in_array($surface, $surfaces, true) ? 'checked' : '' ?>>
                                        <label for="surface-<?= e($clientId) ?>-<?= e($surface) ?>"><?= e($label) ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label><?= lang('admin.permissions') ?></label>
                                <?php foreach ($scopeLabels as $scope => $label) { ?>
                                    <div class="custom-checkbox mb-5">
                                        <input type="checkbox" id="scope-<?= e($clientId) ?>-<?= e($scope) ?>" name="scopes[]" value="<?= e($scope) ?>" <?= in_array($scope, $scopes, true) ? 'checked' : '' ?>>
                                        <label for="scope-<?= e($clientId) ?>-<?= e($scope) ?>"><?= e($label) ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label for="expires-<?= e($clientId) ?>"><?= lang('common.valid_until') ?></label>
                                <input class="form-control" type="date" id="expires-<?= e($clientId) ?>" name="expires_at" value="<?= e($client['expires_at'] ?? '') ?>">
                            </div>
                            <button class="btn success" type="submit">
                                <i class="ph ph-floppy-disk"></i>
                                <?= lang('action.save') ?>
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
        toastSuccess('<?= lang('admin.copied_to_clipboard') ?>');
    }
</script>