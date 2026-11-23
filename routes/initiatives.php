<?php

/** Routes for initiatives and their objectives. */
require_once BASEPATH . '/php/Initiatives.php';

Route::get('/initiatives', function () {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess();
    $breadcrumb = [['name' => t('common.initiatives')]];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/initiatives/list.php';
    include BASEPATH . '/footer.php';
}, 'login');

Route::get('/initiatives/statistics', function () {
    include_once BASEPATH . '/php/init.php';
    require_once BASEPATH . '/php/Initiatives.php';
    Initiatives::requireAccess();
    /** Prepare the same visible linked contents used on initiative/objective detail pages. */
    $statisticsHasObjectives = $osiris->objectives->countDocuments() > 0;
    $statisticsView = $_GET['view'] ?? 'initiatives';
    if (!is_string($statisticsView) || !in_array($statisticsView, ['initiatives', 'objectives'], true)) return abortwith(400, t('error.no_values'));
    if (!$statisticsHasObjectives) $statisticsView = 'initiatives';
    $statisticsSelected = $_GET['id'] ?? '';
    if (!is_string($statisticsSelected) || ($statisticsSelected !== '' && !DB::is_ObjectID($statisticsSelected))) return abortwith(400, t('error.no_values'));
    $statisticsSelected = strtolower($statisticsSelected);
    $statisticsIncludeArchived = ($_GET['include_archived'] ?? '1') !== '0';
    $reportyear = filter_var($_GET['reportyear'] ?? CURRENTYEAR, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900, 'max_range' => 2155]]);
    if ($reportyear === false) return abortwith(400, t('initiatives.invalid_date'));
    try {
        if (!empty($_GET['reportdate'])) {
            $reportstart = $reportend = Initiatives::dateInput($_GET['reportdate']);
            $statisticsDateQuery = ['reportdate' => $reportstart];
        } elseif (isset($_GET['reportstart']) || isset($_GET['reportend'])) {
            $reportstart = Initiatives::dateInput($_GET['reportstart'] ?? null);
            $reportend = Initiatives::dateInput($_GET['reportend'] ?? null);
            $statisticsDateQuery = ['reportstart' => $reportstart, 'reportend' => $reportend];
        } else {
            $reportstart = $reportyear . '-01-01';
            $reportend = $reportyear . '-12-31';
            $statisticsDateQuery = ['reportyear' => $reportyear];
        }
        if ($reportstart === null || $reportend === null || $reportstart > $reportend) throw new InvalidArgumentException();
    } catch (InvalidArgumentException $error) {
        return abortwith(400, t('initiatives.invalid_date_range'));
    }
    $statisticsTimeFrame = $reportstart === $reportend
        ? t('projects.reporting_date') . ': ' . format_date($reportstart)
        : t('common.reporting_period') . ': ' . format_date($reportstart) . ' – ' . format_date($reportend);
    $statisticsLabel = t($statisticsView === 'initiatives' ? 'common.initiatives' : 'initiatives.objectives');
    $statisticsQuery = $statisticsDateQuery + ['view' => $statisticsView, 'id' => $statisticsSelected, 'include_archived' => $statisticsIncludeArchived ? '1' : '0'];
    $statisticsTypes = [
        'publications' => ['label' => t('common.publications'), 'route' => 'activities'],
        'activities' => ['label' => t('common.activities'), 'route' => 'activities'],
    ];
    foreach (['projects' => ['projects', 'common.projects'], 'proposals' => ['projects', 'common.project_proposals'], 'events' => ['events', 'common.events'], 'infrastructures' => ['infrastructures', 'common.infrastructures'], 'deadlines' => ['deadlines', 'dashboard.deadlines']] as $type => [$feature, $label]) {
        if ($Settings->featureEnabled($feature)) $statisticsTypes[$type] = ['label' => t($label), 'route' => $type === 'events' ? 'conferences' : $type];
    }
    $statisticsParents = [];
    if ($statisticsView === 'objectives') foreach ($osiris->initiatives->find() as $parent) $statisticsParents[(string) $parent['_id']] = Initiatives::text($parent['name'] ?? '');
    $statisticsChoices = [];
    $statisticsRows = [];
    foreach (Initiatives::sortCurrentFirst($osiris->$statisticsView->find()->toArray()) as $document) {
        $document = DB::doc2Arr($document);
        $id = (string) $document['_id'];
        $name = Initiatives::text($document['name'] ?? '');
        if ($statisticsView === 'objectives' && !empty($document['code'])) $name = Initiatives::text($document['code']) . ': ' . $name;
        $statisticsChoices[$id] = $name;
        if (($statisticsSelected !== '' && $statisticsSelected !== $id) || (!$statisticsIncludeArchived && !empty($document['inactive']))) continue;
        $status = 'initiatives.current';
        if (!empty($document['inactive'])) $status = 'initiatives.archived';
        elseif (Initiatives::isExpired($document)) $status = 'initiatives.expired';
        elseif ((valueFromDateArray($document['start_date'] ?? $document['start'] ?? null) ?? '') > date('Y-m-d')) $status = 'common.planned';
        $statisticsRows[$id] = [
            'id' => $id,
            'name' => $name,
            'document' => $document,
            'status' => $status,
            'parent_id' => (string) ($document['initiative'] ?? ''),
            'parent' => $statisticsParents[(string) ($document['initiative'] ?? '')] ?? '',
            'url' => '/initiatives/' . ($statisticsView === 'objectives' ? 'objectives/' : '') . 'view/' . $id,
            'counts' => array_fill_keys(array_keys($statisticsTypes), 0),
        ];
    }
    if ($statisticsSelected !== '' && !isset($statisticsChoices[$statisticsSelected])) return abortwith(404, t('initiatives.no_entries'));
    $statisticsDetails = [];
    $statisticsUndated = 0;
    $statisticsContents = $statisticsRows ? Initiatives::linkedContents($osiris, $Settings, [$statisticsView => ['$in' => array_keys($statisticsRows)]]) : [];
    foreach ($statisticsTypes as $type => $definition) {
        foreach ($statisticsContents[$type] ?? [] as $document) {
            if (!empty($document['exclude_from_reports'])) continue;
            try {
                if (in_array($type, ['publications', 'activities'], true)) {
                    $period = Initiatives::activityPeriod($document);
                } elseif (in_array($type, ['proposals', 'deadlines'], true)) {
                    $date = Initiatives::dateInput(valueFromDateArray($document[$type === 'proposals' ? 'submission_date' : 'date'] ?? null));
                    $period = $date === null ? null : [$date, $date];
                } else {
                    $start = Initiatives::dateInput(valueFromDateArray($document['start_date'] ?? $document['start'] ?? null));
                    $end = Initiatives::dateInput(valueFromDateArray($document['end_date'] ?? $document['end'] ?? null));
                    if ($type === 'events' && $end === null) $end = $start;
                    $period = ($type === 'events' && $start === null) || ($start === null && $end === null) || ($start !== null && $end !== null && $start > $end) ? null : [$start, $end];
                }
            } catch (InvalidArgumentException $error) {
                $period = null;
            }
            if ($period === null) {
                $statisticsUndated++;
                continue;
            }
            if (($period[0] ?? '0000-01-01') > $reportend || ($period[1] ?? '9999-12-31') < $reportstart) continue;
            $dateLabel = $period[0] === $period[1] ? format_date($period[0]) : ($period[0] ? format_date($period[0]) : t('common.unknown')) . ' – ' . ($period[1] ? format_date($period[1]) : t('common.open_end'));
            foreach (Initiatives::normalizeLinks($document[$statisticsView] ?? []) as $id) {
                if (!isset($statisticsRows[$id])) continue;
                $statisticsRows[$id]['counts'][$type]++;
                $statisticsDetails[] = [
                    'entity' => $statisticsRows[$id]['name'],
                    'parent' => $statisticsRows[$id]['parent'],
                    'type' => $definition['label'],
                    'title' => Initiatives::text($document['name'] ?? $document['title'] ?? $document['id'] ?? ''),
                    'date' => $dateLabel,
                    'start' => $period[0] ?? '',
                    'end' => $period[1] ?? '',
                    'url' => '/' . $definition['route'] . '/view/' . $document['_id'],
                ];
            }
        }
    }
    $statisticsExportTitle = $statisticsLabel . ' ' . $reportstart . ' – ' . $reportend;

    if (($_GET['download'] ?? '') === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $statisticsView . '-statistics-' . $reportstart . '-' . $reportend . '.csv"');
        header('Cache-Control: no-store');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['period_start', 'period_end', 'entity_type', 'entity_id', 'entity_name', 'initiative_id', 'initiative_name', 'content_type', 'count'], ',', '"', '');
        foreach ($statisticsRows as $row) foreach ($row['counts'] as $type => $count) {
            $values = [$reportstart, $reportend, $statisticsView, $row['id'], $row['name'], $statisticsView === 'initiatives' ? $row['id'] : $row['parent_id'], $statisticsView === 'initiatives' ? $row['name'] : $row['parent'], $type, $count];
            $values = array_map(fn($value) => is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value, $values);
            fputcsv($output, $values, ',', '"', '');
        }
        fclose($output);
        return;
    }
    $breadcrumb = [
        ['name' => t('common.initiatives'), 'path' => '/initiatives'],
        ['name' => t('common.statistics')],
    ];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/initiatives/statistics.php';
    include BASEPATH . '/footer.php';
}, 'login');


Route::get('/initiatives/new', function () {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess(true);
    $initiative = [];
    if (isset($_SESSION['initiative_form']['new'])) {
        $initiative = $_SESSION['initiative_form']['new'];
        unset($_SESSION['initiative_form']['new']);
    }
    $breadcrumb = [
        ['name' => t('common.initiatives'), 'path' => '/initiatives'],
        ['name' => t('common.new')],
    ];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/initiatives/edit.php';
    include BASEPATH . '/footer.php';
}, 'login');

Route::get('/initiatives/(.*)/objectives/new', function ($id) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess(true);
    $initiative = Initiatives::find($osiris, $id);
    $objective = [];
    $breadcrumb = [
        ['name' => t('common.initiatives'), 'path' => '/initiatives'],
        ['name' => Initiatives::text($initiative['name'] ?? ''), 'path' => '/initiatives/view/' . $id],
        ['name' => t('initiatives.add_objective')],
    ];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/initiatives/objective-edit.php';
    include BASEPATH . '/footer.php';
}, 'login');

Route::get('/initiatives/objectives/edit/(.*)', function ($id) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess(true);
    $objective = Initiatives::findObjective($osiris, $id);
    $initiative = Initiatives::find($osiris, (string) $objective['initiative']);
    $breadcrumb = [
        ['name' => t('common.initiatives'), 'path' => '/initiatives'],
        ['name' => Initiatives::text($initiative['name'] ?? ''), 'path' => '/initiatives/view/' . $initiative['_id']],
        ['name' => Initiatives::text($objective['name'] ?? ''), 'path' => '/initiatives/objectives/view/' . $id],
        ['name' => t('action.edit')],
    ];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/initiatives/objective-edit.php';
    include BASEPATH . '/footer.php';
}, 'login');

Route::get('/initiatives/objectives/view/(.*)', function ($id) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess();
    $objective = Initiatives::findObjective($osiris, $id);
    $initiative = Initiatives::find($osiris, (string) $objective['initiative']);
    $breadcrumb = [
        ['name' => t('common.initiatives'), 'path' => '/initiatives'],
        ['name' => Initiatives::text($initiative['name'] ?? ''), 'path' => '/initiatives/view/' . $initiative['_id']],
        ['name' => Initiatives::text($objective['name'] ?? '')],
    ];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/initiatives/objective.php';
    include BASEPATH . '/footer.php';
}, 'login');

Route::get('/initiatives/view/(.*)', function ($id) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess();
    $initiative = Initiatives::find($osiris, $id);
    $breadcrumb = [
        ['name' => t('common.initiatives'), 'path' => '/initiatives'],
        ['name' => Initiatives::text($initiative['name'] ?? '')],
    ];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/initiatives/view.php';
    include BASEPATH . '/footer.php';
}, 'login');

Route::get('/initiatives/edit/(.*)', function ($id) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess(true);
    $initiative = DB::doc2Arr(Initiatives::find($osiris, $id));
    $initiativeName = Initiatives::text($initiative['name'] ?? '');
    if (isset($_SESSION['initiative_form'][$id])) {
        $initiative = array_replace($initiative, $_SESSION['initiative_form'][$id]);
        unset($_SESSION['initiative_form'][$id]);
    }
    $breadcrumb = [
        ['name' => t('common.initiatives'), 'path' => '/initiatives'],
        ['name' => Initiatives::text($initiative['name'] ?? ''), 'path' => '/initiatives/view/' . $id],
        ['name' => t('action.edit')],
    ];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/initiatives/edit.php';
    include BASEPATH . '/footer.php';
}, 'login');

$saveInitiative = function ($id = null) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess(true);
    $initiative = $id === null ? [] : DB::doc2Arr(Initiatives::find($osiris, $id));
    $input = $_POST['values'] ?? [];
    $config = new FormBuilder(
        entityLabel: t('common.initiatives'),
        redirectUrl: '',
        entity: 'initiatives',
        systemFields: FormBuilder::INITIATIVE_SYSTEM_FIELDS
    );
    $fields = Initiatives::fields($osiris);
    $user = $osiris->persons->findOne(['username' => $_SESSION['username']]);
    $fields = Initiatives::visibleFields($fields, DB::doc2Arr($user['roles'] ?? []));
    try {
        if (!is_array($input)) throw new InvalidArgumentException(t('error.no_values'));
        $raw = Initiatives::formValues($input, $fields, $config->modules);
        // Activity normalization derives publication years from dates; keep initiative dates separate.
        $values = validateValues(array_diff_key($raw, ['start' => true, 'end' => true]), $DB, 'initiatives');
        if ($Settings->featureEnabled('portal') && isset($input['public'])) $values['public'] = filter_var($input['public'], FILTER_VALIDATE_BOOLEAN);
        if (is_string($raw['name'])) $values['name'] = $raw['name'];
        foreach (['start', 'end'] as $key) {
            if (array_key_exists($key, $raw)) {
                $values[$key] = $raw[$key] === null ? null : valiDate($raw[$key]);
                $values[$key . '_date'] = $raw[$key];
            }
        }
    } catch (InvalidArgumentException $e) {
        // Keep only editable fields when redisplaying invalid input.
        $_SESSION['initiative_form'][$id ?? 'new'] = is_array($input) ? Initiatives::editableValues($input, $fields, $config->modules) : [];
        if ($Settings->featureEnabled('portal') && isset($input['public'])) $_SESSION['initiative_form'][$id ?? 'new']['public'] = filter_var($input['public'], FILTER_VALIDATE_BOOLEAN);
        $_SESSION['msg'] = e($e->getMessage());
        $_SESSION['msg_type'] = 'error';
        header('Location: ' . ROOTPATH . ($id === null ? '/initiatives/new' : '/initiatives/edit/' . $id));
        return;
    }
    if ($id === null) {
        $objectId = new MongoDB\BSON\ObjectId();
        $values += [
            '_id' => $objectId,
            'id' => (string) $objectId,
            'inactive' => false,
            'created' => date('Y-m-d'),
            'created_by' => $_SESSION['username'],
        ];
        $osiris->initiatives->insertOne($values);
        $id = (string) $objectId;
    } else {
        $values['updated'] = date('Y-m-d');
        $values['updated_by'] = $_SESSION['username'];
        $osiris->initiatives->updateOne(['_id' => $initiative['_id']], ['$set' => $values]);
    }
    $_SESSION['msg'] = t('initiatives.saved');
    $_SESSION['msg_type'] = 'success';
    header('Location: ' . ROOTPATH . '/initiatives/view/' . $id);
};
Route::post('/crud/initiatives/create', $saveInitiative, 'login');
Route::post('/crud/initiatives/update/(.*)', $saveInitiative, 'login');

Route::post('/crud/initiatives/archive/(.*)', function ($id) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess(true);
    $initiative = Initiatives::find($osiris, $id);
    $osiris->initiatives->updateOne(['_id' => $initiative['_id']], ['$set' => [
        'inactive' => ($_POST['inactive'] ?? '') === '1',
        'updated' => date('Y-m-d'),
        'updated_by' => $_SESSION['username'],
    ]]);
    header('Location: ' . ROOTPATH . '/initiatives/view/' . $id);
}, 'login');

Route::post('/crud/initiatives/(.*)/objectives/create', function ($id) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess(true);
    $initiative = Initiatives::find($osiris, $id);
    $target = ROOTPATH . '/initiatives/' . $id . '/objectives/new';
    try {
        if (!is_array($_POST['values'] ?? null)) throw new InvalidArgumentException(t('error.no_values'));
        $values = Initiatives::objectiveValues($_POST['values']);
        if ($Settings->featureEnabled('portal') && isset($_POST['values']['public'])) $values['public'] = filter_var($_POST['values']['public'], FILTER_VALIDATE_BOOLEAN);
        $values += [
            '_id' => new MongoDB\BSON\ObjectId(),
            'initiative' => (string) $initiative['_id'],
            'inactive' => false,
            'created' => date('Y-m-d'),
            'created_by' => $_SESSION['username'],
        ];
        $osiris->objectives->insertOne($values);
        $target = ROOTPATH . '/initiatives/objectives/view/' . $values['_id'];
        $_SESSION['msg'] = t('initiatives.saved');
        $_SESSION['msg_type'] = 'success';
    } catch (InvalidArgumentException $e) {
        $_SESSION['objective_form'][$id]['new'] = Initiatives::objectiveDraft(is_array($_POST['values'] ?? null) ? $_POST['values'] : []);
        $_SESSION['msg'] = e($e->getMessage());
        $_SESSION['msg_type'] = 'error';
    }
    header('Location: ' . $target);
}, 'login');

Route::post('/crud/initiatives/(.*)/objectives/(update|archive)/(.*)', function ($id, $action, $objectiveId) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess(true);
    $initiative = Initiatives::find($osiris, $id);
    $filter = ['initiative' => (string) $initiative['_id']];
    if (!DB::is_ObjectID($objectiveId)) {
        http_response_code(404);
        abortwith(404, t('initiatives.objective'), '/initiatives/view/' . $id);
    }
    $filter['_id'] = DB::to_ObjectID($objectiveId);
    if (!$osiris->objectives->findOne($filter)) {
        http_response_code(404);
        abortwith(404, t('initiatives.objective'), '/initiatives/view/' . $id);
    }
    $target = ROOTPATH . '/initiatives/objectives/view/' . $objectiveId;
    try {
        if ($action === 'archive') {
            $values = ['inactive' => ($_POST['inactive'] ?? '') === '1'];
        } else {
            if (!is_array($_POST['values'] ?? null)) throw new InvalidArgumentException(t('error.no_values'));
            $values = Initiatives::objectiveValues($_POST['values']);
            if ($Settings->featureEnabled('portal') && isset($_POST['values']['public'])) $values['public'] = filter_var($_POST['values']['public'], FILTER_VALIDATE_BOOLEAN);
        }
        $values['updated'] = date('Y-m-d');
        $values['updated_by'] = $_SESSION['username'];
        $osiris->objectives->updateOne($filter, ['$set' => $values]);
        $_SESSION['msg'] = t('initiatives.saved');
        $_SESSION['msg_type'] = 'success';
    } catch (InvalidArgumentException $e) {
        $target = ROOTPATH . '/initiatives/objectives/edit/' . $objectiveId;
        $_SESSION['objective_form'][$id][$objectiveId] = Initiatives::objectiveDraft(is_array($_POST['values'] ?? null) ? $_POST['values'] : []);
        $_SESSION['msg'] = e($e->getMessage());
        $_SESSION['msg_type'] = 'error';
    }
    header('Location: ' . $target);
}, 'login');
