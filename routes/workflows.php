<?php

/**
 * Routing file for all workflow related routes
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


// Reviewer routes

Route::get('/workflow-reviews', function () {
    include_once BASEPATH . "/php/init.php";

    $breadcrumb = [
        ['name' => lang("Workflow Reviews", "Workflow-Überprüfungen")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/workflows/reviews.php";
    include BASEPATH . "/footer.php";
}, 'login');

// Workflow Management

Route::get('/admin/workflows', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang("You don't have permission to see workflows.", "Du hast keine Berechtigung, Workflows zu sehen."), '/');
    }

    $breadcrumb = [
        ['name' => lang('Content', 'Inhalte'), 'path' => '/admin'],
        ['name' => lang("Workflows", "Workflows")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/workflows.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/workflows/new', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang("You don't have permission to create workflows.", "Du hast keine Berechtigung, Workflows zu erstellen."), '/');
    }

    $form = [];
    $breadcrumb = [
        ['name' => lang('Content', 'Inhalte'), 'path' => '/admin'],
        ['name' => lang("Workflows", "Workflows"), 'path' => '/admin/workflows'],
        ['name' => lang('New workflow', 'Neuer Workflow')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/workflow-new.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/workflows/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang("You don't have permission to see workflows.", "Du hast keine Berechtigung, Workflows zu sehen."), '/');
    }

    $form = $osiris->adminWorkflows->findOne(['id' => $id]);
    if (empty($form)) {
        abortwith(404, lang('Workflow', 'Workflow'), '/');
    }
    $name = $form['name'] ?? $id;

    $breadcrumb = [
        ['name' => lang('Content', 'Inhalte'), 'path' => '/admin'],
        ['name' => lang("Workflows", "Workflows"), 'path' => '/admin/workflows'],
        ['name' => $name]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/workflow.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::post('/crud/workflows/create', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang("You don't have permission to create workflows.", "Du hast keine Berechtigung, Workflows zu erstellen."), '/');
    }

    if (!isset($_POST['values'])) abortwith(500, lang('No values provided.', 'Keine Werte angegeben.'));

    $values = validateValues($_POST['values'], $DB);

    // check if category ID already exists:
    $workflow_exist = $osiris->adminWorkflows->findOne(['id' => $values['id']]);
    if (!empty($workflow_exist)) {
        $_SESSION['msg'] = lang('Workflow ID does already exist.', 'Die Workflow-ID wird bereits verwendet.');
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/admin/workflows/new");
        die();
    }

    $osiris->adminWorkflows->insertOne($values);
    
    $_SESSION['msg'] = lang('Workflow created successfully.', 'Workflow erfolgreich erstellt.');
    $_SESSION['msg_type'] = 'success';

    header("Location: " . ROOTPATH . "/admin/workflows/" . $values['id']);
});

Route::post('/crud/workflows/update/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang("You don't have permission to update workflows.", "Du hast keine Berechtigung, Workflows zu aktualisieren."), '/');
    }

    if (!isset($_POST['values'])) abortwith(500, lang('No values provided.', 'Keine Werte angegeben.'));
    $values = validateValues($_POST['values'], $DB);

    /**
     * Helpers
     */
    function toBool($v): bool
    {
        // Checkboxen senden nur was bei "checked"
        return !empty($v) && $v !== '0' && $v !== 0 && $v !== false;
    }

    function cleanText($s, $max = 120): string
    {
        $s = is_string($s) ? $s : '';
        $s = trim($s);
        $s = strip_tags($s);
        $s = preg_replace('/\s+/', ' ', $s);
        return mb_substr($s, 0, $max);
    }

    function slugify($s, $max = 50): string
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/[^a-z0-9]+/u', '-', $s);
        $s = trim($s, '-');
        if ($s === '') $s = 'step';
        return mb_substr($s, 0, $max);
    }

    /**
     * Normalizer für Steps
     * Erwartet $rawSteps als numerisch indiziertes Array aus $_POST['values']['steps']
     * $allowedRoles = Array der erlaubten Rollen (Strings)
     */
    function normalizeWorkflowSteps(array $rawSteps, array $allowedRoles): array
    {
        $out = [];
        $seenIds = [];

        // Fallback-Role
        $defaultRole = $allowedRoles[0] ?? 'user';

        foreach ($rawSteps as $i => $s) {
            // label (required)
            $label = cleanText($s['label'] ?? '');
            if ($label === '') {
                continue;
            } // leere Zeilen überspringen

            // index (Phase)
            $index = isset($s['index']) ? intval($s['index']) : 0;
            if ($index < 0) $index = 0;

            // role
            $role = cleanText($s['role'] ?? $defaultRole, 60);
            if (!in_array($role, $allowedRoles, true)) {
                $role = $defaultRole;
            }

            // orgScope
            $scope = ($s['orgScope'] ?? 'any') === 'same_org_only' ? 'same_org_only' : 'any';

            // booleans
            $required = toBool($s['required'] ?? 0);
            $locksAfter = toBool($s['locksAfterApproval'] ?? 0);

            // id generieren (stabil, eindeutig)
            // Falls du später ein verstecktes Feld [id] einführst, dann: $id = cleanText($s['id'] ?? '', 64)
            $baseId = slugify($label);
            $id = $baseId;
            $suffix = 2;
            while (isset($seenIds[$id])) {
                $id = $baseId . '-' . $suffix++;
            }
            $seenIds[$id] = true;

            $out[] = [
                'id' => $id,
                'label' => $label,
                'index' => $index,
                'role' => $role,
                'orgScope' => $scope,                 // 'any' | 'same_org_only'
                'required' => $required,              // bool
                'locksAfterApproval' => $locksAfter,  // bool
            ];
        }

        // stabile Sortierung nach index, dann originale Reihenfolge
        usort($out, function ($a, $b) {
            if ($a['index'] === $b['index']) return 0;
            return $a['index'] <=> $b['index'];
        });

        // Reindex numerische Keys
        return array_values($out);
    }

    $req = $osiris->adminGeneral->findOne(['key' => 'roles']);
    $allowedRoles = DB::doc2Arr($req['value'] ?? ['user', 'scientist', 'admin']);

    $values = $_POST['values'] ?? [];
    $stepsNorm = normalizeWorkflowSteps($values['steps'] ?? [], $allowedRoles);

    $doc = [
        'name' => cleanText($values['name'] ?? '', 120),
        'steps' => $stepsNorm
    ];

    $osiris->adminWorkflows->updateOne(
        ['id' => $id],
        ['$set' => $doc],
        ['upsert' => false]
    );

    $_SESSION['msg'] = lang('Workflow updated successfully.', 'Workflow erfolgreich aktualisiert.');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/admin/workflows");
});

Route::post('/crud/workflows/delete/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang("You don't have permission to delete workflows.", "Du hast keine Berechtigung, Workflows zu löschen."), '/');
    }

    $mongo_id = DB::to_ObjectID($id);
    $updateResult = $osiris->adminWorkflows->deleteOne(
        ['_id' => $mongo_id]
    );

    $_SESSION['msg'] = lang('Workflow deleted successfully.', 'Workflow erfolgreich gelöscht.');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/admin/workflows");
});

Route::post('/crud/workflows/apply/(.*)', function ($wfId) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Workflows.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang("You don't have permission to apply workflows.", "Du hast keine Berechtigung, Workflows anzuwenden."), '/');
    }

    $template = $osiris->adminWorkflows->findOne(['id' => $wfId]);
    if (!$template) return JSON::error('Workflow not found', 404);

    $template = DB::doc2Arr($template);
    if (empty($template['steps'])) return JSON::error('Workflow has no steps defined', 400);

    $category = $_POST['category'] ?? null;
    $mode = $_POST['mode'] ?? 'attach-missing';
    $dryrun = isset($_POST['dryrun']) && in_array($_POST['dryrun'], ['1', 'true', 1, true], true);
    $from = $_POST['from'] ?? null;
    $to = $_POST['to'] ?? null;

    if (!$category) return JSON::error('Category required', 400);
    if ($mode !== 'attach-missing') return JSON::error('Unsupported mode', 400);

    // Base-Filter
    $base = ['type' => $category];
    if ($from) $base['created']['$gte'] = $from;
    if ($to)   $base['created']['$lte'] = $to;

    // Nur fehlende ODER null
    $filterMissing = [
        '$and' => [$base, ['$or' => [['workflow' => ['$exists' => false]], ['workflow' => null]]]]
    ];

    // Counts
    $total           = $osiris->activities->countDocuments($base);
    $withWorkflow    = $osiris->activities->countDocuments($base + ['workflow' => ['$ne' => null]]);
    $withoutWorkflow = $osiris->activities->countDocuments($filterMissing);

    if ($dryrun) {
        return JSON::ok([
            'total' => $total,
            'withWorkflow' => $withWorkflow,
            'withoutWorkflow' => $withoutWorkflow,
            'willUpdate' => $withoutWorkflow
        ]);
    }

    // Apply (dünnes Modell)
    $initial = Workflows::buildInitialState($template);
    $updateResult = $osiris->activities->updateMany(
        $filterMissing,                                // <— NICHT weiter einschränken
        ['$set' => ['workflow' => $initial]]
    );

    return JSON::ok([
        'updatedCount' => $updateResult->getModifiedCount(),
        'skippedCount' => $total - $updateResult->getModifiedCount(),
        'total' => $total,
        'withWorkflow' => $withWorkflow + $updateResult->getModifiedCount(),
        'withoutWorkflow' => max(0, $withoutWorkflow - $updateResult->getModifiedCount())
    ]);
});

// POST /crud/workflows/reset-action
Route::post('/crud/workflows/reset-action', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Workflows.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang("You don't have permission to reset workflows.", "Du hast keine Berechtigung, Workflows zurückzusetzen."), '/');
    }

    $action = $_POST['action'] ?? null;
    $activity = $_POST['activity'] ?? 'all';

    if (!in_array($action, ['remove', 'reset'], true)) {
        return JSON::error('Invalid action', 400);
    }

    // Base-Filter
    $base = [];
    if ($activity !== 'all') {
        $base['type'] = $activity;
    }
    $base['workflow'] = ['$ne' => null];

    // Counts
    $totalWithWorkflow = $osiris->activities->countDocuments($base);
    if ($totalWithWorkflow === 0) {
        if (isset($_POST['redirect'])) {
            $_SESSION['msg'] = lang('No activities with workflows found.', 'Keine Aktivitäten mit Workflows gefunden.');
            $_SESSION['msg_type'] = 'info';
            header("Location: " . $_POST['redirect']);
            die;
        }
        return JSON::ok(['updatedCount' => 0, 'skippedCount' => 0, 'total' => 0]);
    }

    if ($action === 'remove') {
        // Remove workflow
        $updateResult = $osiris->activities->updateMany(
            $base,
            ['$unset' => ['workflow' => ""]]
        );
        $modified = $updateResult->getModifiedCount();
    } else {
        // Reset to first step
        $allActs = $osiris->activities->find($base);
        $templates = []; // Cache für Templates
        $updatedCount = 0;
        foreach ($allActs as $act) {
            if (empty($act['workflow']['workflow_id'])) {
                continue; // kein Template referenziert
            }
            $wfId = $act['workflow']['workflow_id'];
            if (isset($templates[$wfId])) {
                $tpl = $templates[$wfId];
            } else {
                $tpl = $osiris->adminWorkflows->findOne(['id' => $wfId]);
                $templates[$wfId] = $tpl;
            }
            if (empty($tpl)) {
                continue; // Template nicht gefunden
            }
            $initial = Workflows::buildInitialState(DB::doc2Arr($tpl));
            if (empty($initial)) {
                continue; // Template fehlerhaft
            }
            $osiris->activities->updateOne(
                ['_id' => $act['_id']],
                ['$set' => ['workflow' => $initial]]
            );
            $updatedCount++;
        }
        $modified = $updatedCount;
    }

    if (isset($_POST['redirect'])) {
        $_SESSION['msg'] = lang('Action applied to '.$modified.' activities.', 'Aktion auf '.$modified.' Aktivitäten angewendet.');
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $_POST['redirect']);
        die;
    }

    return JSON::ok([
        'updatedCount' => $modified,
        'skippedCount' => $totalWithWorkflow - $modified,
        'total' => $totalWithWorkflow
    ]);
});

// POST /crud/activities/workflow/approve/{id}
Route::post('/crud/activities/workflow/approve/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Workflows.php";

    $stepId = $_POST['stepId'] ?? null;
    if (!$stepId) return JSON::error('stepId required', 400);

    $act = $osiris->activities->findOne(['_id' => $DB->to_ObjectID($id)]);
    if (!$act || empty($act['workflow'])) return JSON::error('No workflow', 404);

    $tpl = $osiris->adminWorkflows->findOne(['id' => $act['workflow']['workflow_id']]);
    if (!$tpl) return JSON::error('Workflow template not found', 404);

    $units = DB::doc2Arr($USER['units'] ?? []);
    if (!empty($units)) {
        $units = array_column($units, 'unit');
    }
    $user = [
        'username' => $_SESSION['username'] ?? null,
        'roles'    => $Settings->roles ?? [],
        'units'   => $units
    ];

    try {
        $wf = Workflows::approveStep(DB::doc2Arr($act), DB::doc2Arr($tpl), DB::doc2Arr($act['workflow']), $stepId, $user);
    } catch (RuntimeException $e) {
        return JSON::error($e->getMessage(), 403);
    }

    // check if we need to lock the activity
    $tplArr = DB::doc2Arr($tpl);
    $shouldLock = false;
    foreach (DB::doc2Arr($tplArr['steps'] ?? []) as $s) {
        if (($s['id'] ?? null) === $stepId && !empty($s['locksAfterApproval'])) {
            $shouldLock = true;
            break;
        }
    }

    // Update document
    $set = ['workflow' => $wf];
    if ($shouldLock && empty($act['locked'])) {
        $set['locked'] = true;  // set global lock
    }

    $history = $act['history'] ?? [];
    $hist = [
        'date' => date('Y-m-d'),
        'user' => $_SESSION['username'] ?? 'system',
        'type' => 'workflow-approve',
        'comment' => 'Approved step ' . $stepId
    ];
    $history[] = $hist;
    $set['history'] = $history;

    $osiris->activities->updateOne(['_id' => $act['_id']], ['$set' => $set]);

    JSON::ok([
        'workflow_status' => $wf['status'],
        'locked' => !empty($set['locked']) || !empty($act['locked'])
    ]);
});


// POST /crud/activities/workflow/reject-reply/{id}
Route::post('/crud/activities/workflow/reject-reply/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Workflows.php";

    $act = $osiris->activities->findOne(['_id' => $DB->to_ObjectID($id)]);
    if (!$act || empty($act['workflow'])) return JSON::error('No workflow', 404);
    $wf = DB::doc2Arr($act['workflow']);

    if ($wf['status'] !== 'rejected' || empty($wf['rejectedDetails'])) {
        return JSON::error('Not in rejected state', 400);
    }

    $comment = trim($_POST['comment'] ?? '');

    // Reset workflow to before rejection
    $wf['status'] = 'in_progress';
    $wf['rejectedDetails']['reply'] = [
        'by'      => $_SESSION['username'] ?? null,
        'at'      => Workflows::nowIso(),
        'comment' => $comment
    ];


    $history = $act['history'] ?? [];
    $hist = [
        'date' => date('Y-m-d'),
        'user' => $_SESSION['username'] ?? 'system',
        'type' => 'workflow-reject',
        'comment' => $comment
    ];
    $history[] = $hist;

    $osiris->activities->updateOne(['_id' => $act['_id']], ['$set' => ['workflow' => $wf, 'locked' => false, 'history' => $history]]);

    if (!empty($_POST['redirect'])) {
        header("Location: " . $_POST['redirect'] . "#workflow-modal");
        die;
    }
    JSON::ok();
});

// POST /crud/activities/workflow/reject/{id}
Route::post('/crud/activities/workflow/reject/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Workflows.php";

    $stepId = $_POST['stepId'] ?? null;
    $comment = trim($_POST['comment'] ?? '');
    if (!$stepId) return JSON::error('stepId required', 400);

    $act = $osiris->activities->findOne(['_id' => $DB->to_ObjectID($id)]);
    if (!$act || empty($act['workflow'])) return JSON::error('No workflow', 404);

    // simple: jeder Pending-Step darf rejected werden, Permission-Check kannst du analog zu approve einbauen
    $wf = DB::doc2Arr($act['workflow']);
    $wf['rejectedDetails'] = [
        'stepId' => $stepId,
        'action' => 'rejected',
        'by'     => $_SESSION['username'] ?? null,
        'at'     => Workflows::nowIso(),
        'comment' => $comment
    ];
    $wf['status'] = 'rejected';

    $history = $act['history'] ?? [];
    $hist = [
        'date' => date('Y-m-d'),
        'user' => $_SESSION['username'] ?? 'system',
        'type' => 'workflow-reject',
        'comment' => $comment
    ];
    $history[] = $hist;

    $osiris->activities->updateOne(['_id' => $act['_id']], ['$set' => ['workflow' => $wf, 'locked' => false, 'history' => $history]]);
    JSON::ok();
});

Route::post('/crud/activities/workflow/reject-resolve/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Workflows.php";

    $act = $osiris->activities->findOne(['_id' => $DB->to_ObjectID($id)]);
    if (!$act || empty($act['workflow'])) {
        abortwith(404, lang('Activity or workflow', 'Aktivität oder Workflow'), '/activities/view/' . $id, lang('Go back to activity', 'Zurück zur Aktivität'));
    }
    $wf = DB::doc2Arr($act['workflow']);

    // Nur derjenige, der rejected hat, oder jemand mit action-Rechten kann das als resolved markieren
    $canResolve = false;
    if (($wf['rejectedDetails']['by'] ?? null) === ($_SESSION['username'] ?? null)) {
        $canResolve = true;
    } else {
        $units = DB::doc2Arr($USER['units'] ?? []);
        if (!empty($units)) {
            $units = array_column($units, 'unit');
        }
        $user = [
            'username' => $_SESSION['username'] ?? null,
            'roles'    => $Settings->roles ?? [],
            'units'   => $units
        ];
        $tpl = $osiris->adminWorkflows->findOne(['id' => $wf['workflow_id']]);
        if ($tpl) {
            $tpl = DB::doc2Arr($tpl);
            $currentIndex = Workflows::currentPhaseIndex($tpl, $wf);
            if ($currentIndex !== null) {
                $view = Workflows::view($tpl, $wf);
                foreach ($view as $s) {
                    if ($s['state'] !== 'pending' || intval($s['index']) !== $currentIndex) continue;
                    if (Workflows::canApprove(DB::doc2Arr($act), $tpl, $wf, $s['id'], $user)) {
                        $canResolve = true;
                        break;
                    }
                }
            }
        }
    }

    if (!$canResolve) {
        abortwith(403, lang("You don't have permission to resolve this rejection.", "Du hast keine Berechtigung, diese Ablehnung als gelöst zu markieren."), '/activities/view/' . $id, lang('Go back to activity', 'Zurück zur Aktivität'));
    }
    // Reset workflow to before rejection
    unset($wf['rejectedDetails']);
    $wf['status'] = 'in_progress';
    $osiris->activities->updateOne(['_id' => $act['_id']], ['$set' => ['workflow' => $wf]]);
    $_SESSION['msg'] = lang('Rejection marked as resolved.', 'Ablehnung als gelöst markiert.');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/activities/view/$id");
});

// POST /crud/activities/workflow/reset/{id}
Route::post('/crud/activities/workflow/reset/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Workflows.php";

    if (!$Settings->hasPermission('workflows.reset')) {
        abortwith(403, lang("You don't have permission to reset this workflow.", "Du hast keine Berechtigung, diesen Workflow zurückzusetzen."), '/activities/view/' . $id, lang('Go back to activity', 'Zurück zur Aktivität'));
    }

    $act = $osiris->activities->findOne(['_id' => $DB->to_ObjectID($id)]);
    if (!$act || empty($act['workflow'])) {
        abortwith(404, lang('Activity or workflow', 'Aktivität oder Workflow'), '/activities/view/' . $id, lang('Go back to activity', 'Zurück zur Aktivität'));
    }
    $wf = DB::doc2Arr($act['workflow']);
    $tpl = $osiris->adminWorkflows->findOne(['id' => $wf['workflow_id']]);
    if (!$tpl) {
        abortwith(404, lang('Workflow template not found', 'Workflow-Vorlage nicht gefunden'), '/activities/view/' . $id, lang('Go back to activity', 'Zurück zur Aktivität'));
    }
    $tpl = DB::doc2Arr($tpl);

    $initial = Workflows::buildInitialState($tpl);

    $history = $act['history'] ?? [];
    $hist = [
        'date' => date('Y-m-d'),
        'user' => $_SESSION['username'] ?? 'system',
        'type' => 'workflow-reset'
    ];
    $history[] = $hist;
    $osiris->activities->updateOne(['_id' => $act['_id']], ['$set' => ['workflow' => $initial, 'locked' => false, 'history' => $history]]);
    $_SESSION['msg'] = lang('Workflow reset successfully.', 'Workflow erfolgreich zurückgesetzt.');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/activities/view/$id");
});

// API


Route::get('/api/workflow-reviews/count', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Workflows.php";

    // --- User-Kontext (dein Format)
    $units = DB::doc2Arr($USER['units'] ?? []);
    if (!empty($units)) $units = array_column($units, 'unit');
    $user = [
        'username' => $_SESSION['username'] ?? null,
        'roles'    => $Settings->roles ?? [],   // z. B. ['library','head_of_department']
        'units'    => $units
    ];

    // 1) Workflows vorladen, die für diese Rollen relevant sind
    $roleSet = array_flip($user['roles']);
    $wfCursor = $osiris->adminWorkflows->find([], ['projection' => ['id' => 1, 'name' => 1, 'steps' => 1]]);
    $tplById = [];
    $wfIdsForUser = [];

    foreach ($wfCursor as $tpl) {
        $tpl = DB::doc2Arr($tpl);
        foreach (DB::doc2Arr($tpl['steps'] ?? []) as $s) {
            if (isset($roleSet[$s['role'] ?? ''])) {
                $tplById[$tpl['id']] = $tpl;
                $wfIdsForUser[$tpl['id']] = true;
                break;
            }
        }
    }
    if (empty($wfIdsForUser)) return JSON::ok(['count' => 0]);

    // 2) Kandidaten-Aktivitäten: haben diesen Workflow und sind nicht verified
    $filter = [
        'workflow.workflow_id' => ['$in' => array_keys($wfIdsForUser)],
        'workflow.status'      => ['$nin' => ['verified', 'rejected']]
    ];
    // (Optional) wenn du "archiviert" o.ä. hast, hier ausfiltern

    $cursor = $osiris->activities->find($filter, [
        'projection' => [
            '_id' => 1,
            'workflow' => 1,
            'type' => 1,
            'units' => 1,
            'orgIds' => 1
        ]
    ]);

    // 3) Counten: ist in der aktuellen Phase mind. ein Step für diesen User "approvable"?
    $count = 0;
    foreach ($cursor as $act) {
        $actArr = DB::doc2Arr($act);
        $wf     = DB::doc2Arr($actArr['workflow'] ?? []);
        $tpl    = DB::doc2Arr($tplById[$wf['workflow_id']] ?? null);
        if (empty($tpl)) continue;

        // aktuelle Phase
        $currentIndex = Workflows::currentPhaseIndex($tpl, $wf);
        if ($currentIndex === null) continue;

        // alle Steps dieser Phase durchgehen (pending) und canApprove prüfen
        $view = Workflows::view($tpl, $wf); // [{id,label,index,required,state}]
        $hasAction = false;
        foreach ($view as $s) {
            if ($s['state'] !== 'pending' || intval($s['index']) !== $currentIndex) continue;
            if (Workflows::canApprove($actArr, $tpl, $wf, $s['id'], $user)) {
                $hasAction = true;
                break;
            }
        }
        if ($hasAction) $count++;
    }

    JSON::ok(['count' => $count]);
});

// GET /api/reviews/list?q=&role=&category=&ou=&since=&scope=&page=&pageSize=
Route::get('/api/workflow-reviews/list', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Workflows.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $roles = $Settings->roles ?? [];
    // === 1) Parameter ===
    $q       = trim($_GET['q'] ?? '');
    // $role    = $roles;
    $category = trim($_GET['category'] ?? '');
    // $ou      = trim($_GET['ou'] ?? '');
    $since   = trim($_GET['since'] ?? '');
    $scope   = trim($_GET['scope'] ?? ''); // z. B. "same_org_only"
    $page    = max(1, intval($_GET['page'] ?? 1));
    $pageSize = max(1, min(100, intval($_GET['pageSize'] ?? 25)));

    // === 2) User-Kontext ===
    $units = DB::doc2Arr($USER['units'] ?? []);
    if (!empty($units)) $units = array_column($units, 'unit');
    $user = [
        'username' => $_SESSION['username'] ?? null,
        'roles'    => $Settings->roles ?? [],
        'units'    => $units
    ];

    // === 3) Relevante Workflows finden ===
    $wfCursor = $osiris->adminWorkflows->find([], ['projection' => ['id' => 1, 'steps' => 1]]);
    $tplById = [];
    $wfIdsForUser = [];
    foreach ($wfCursor as $tpl) {
        $tpl = DB::doc2Arr($tpl);
        foreach (DB::doc2Arr($tpl['steps'] ?? []) as $s) {
            if (in_array($s['role'] ?? '', $user['roles'], true)) {
                $tplById[$tpl['id']] = $tpl;
                $wfIdsForUser[$tpl['id']] = true;
                break;
            }
        }
    }
    if (empty($wfIdsForUser)) return JSON::ok(['items' => [], 'total' => 0]);

    // === 4) Basis-Filter ===
    $filter = [
        'workflow.workflow_id' => ['$in' => array_keys($wfIdsForUser)],
        'workflow.status'      => ['$nin' => ['verified', 'rejected']]
    ];
    if ($category) $filter['type'] = $category;
    if ($since) $filter['created']['$gte'] = $since;
    if ($q) $filter['title'] = ['$regex' => $q, '$options' => 'i'];
    // OU-Filter (vereinfacht)
    if ($scope == 'same_org_only') $filter['units'] = ['$in' => [$units]];
    // === 5) Aktivitäten holen ===
    $cursor = $osiris->activities->find($filter, [
        'projection' => [
            '_id' => 1,
            'title' => 1,
            'type' => 1,
            'units' => 1,
            'workflow' => 1,
            'rendered' => 1,
        ],
        'sort' => ['updated' => -1, 'created' => -1],
    ]);

    $items = [];

    foreach ($cursor as $i => $act) {
        if ($i < ($page - 1) * $pageSize) continue;
        if (count($items) >= $pageSize) break;
        $actArr = DB::doc2Arr($act);
        $wf     = DB::doc2Arr($actArr['workflow'] ?? []);
        $tpl    = DB::doc2Arr($tplById[$wf['workflow_id']] ?? null);
        if (empty($tpl)) continue;

        // aktuelle Phase bestimmen
        $currentIndex = Workflows::currentPhaseIndex($tpl, $wf);
        if ($currentIndex === null) continue;

        // Steps dieser Phase prüfen
        $view = Workflows::view($tpl, $wf);
        foreach ($view as $s) {
            if ($s['state'] !== 'pending' || intval($s['index']) !== $currentIndex) continue;
            if (!Workflows::canApprove($actArr, $tpl, $wf, $s['id'], $user)) continue;
            // Treffer gefunden → in Liste
            $items[] = [
                'id' => (string)$actArr['_id'],
                'title' => $actArr['rendered']['print'] ?? '',
                'category' => $actArr['type'] ?? '',
                'ou' => implode(', ', DB::doc2Arr($actArr['units'] ?? [])),
                'workflow_id' => $wf['workflow_id'],
                'phaseIndex' => $currentIndex,
                'completed' => count($wf['approved'] ?? []) . '/' . count($tpl['steps'] ?? []), 
                'step' => ['id' => $s['id'], 'label' => $s['label']],
                'parallelPending' => count(array_filter($view, fn($x) => $x['state'] === 'pending' && intval($x['index']) === $currentIndex)) - 1,
                'updated' => $actArr['updated'] ?? null
            ];
            break; // pro Aktivität nur 1 Eintrag
        }
    }

    $total = count($items);
    JSON::ok(['items' => $items, 'total' => $total]);
});
