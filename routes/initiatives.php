<?php

/** Routes for initiatives and their objectives. */
require_once BASEPATH . '/php/Initiatives.php';

Route::get('/initiatives', function () {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess();
    $breadcrumb = [['name' => translate('common.initiatives')]];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/initiatives/list.php';
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
        ['name' => translate('common.initiatives'), 'path' => '/initiatives'],
        ['name' => translate('common.new')],
    ];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/initiatives/edit.php';
    include BASEPATH . '/footer.php';
}, 'login');

Route::get('/initiatives/view/(.*)', function ($id) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess();
    $initiative = Initiatives::find($osiris, $id);
    $breadcrumb = [
        ['name' => translate('common.initiatives'), 'path' => '/initiatives'],
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
    if (isset($_SESSION['initiative_form'][$id])) {
        $initiative = array_replace($initiative, $_SESSION['initiative_form'][$id]);
        unset($_SESSION['initiative_form'][$id]);
    }
    $breadcrumb = [
        ['name' => translate('common.initiatives'), 'path' => '/initiatives'],
        ['name' => Initiatives::text($initiative['name'] ?? ''), 'path' => '/initiatives/view/' . $id],
        ['name' => translate('action.edit')],
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
        entityLabel: translate('common.initiatives'), redirectUrl: '', entity: 'initiatives',
        systemFields: FormBuilder::INITIATIVE_SYSTEM_FIELDS
    );
    $fields = Initiatives::fields($osiris);
    $user = $osiris->persons->findOne(['username' => $_SESSION['username']]);
    $fields = Initiatives::visibleFields($fields, DB::doc2Arr($user['roles'] ?? []));
    try {
        if (!is_array($input)) throw new InvalidArgumentException(translate('error.no_values'));
        $raw = Initiatives::formValues($input, $fields, $config->modules);
        // Activity normalization derives publication years from dates; keep initiative dates separate.
        $values = validateValues(array_diff_key($raw, ['start' => true, 'end' => true]), $DB, 'initiatives');
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
        $_SESSION['msg'] = e($e->getMessage());
        $_SESSION['msg_type'] = 'error';
        header('Location: ' . ROOTPATH . ($id === null ? '/initiatives/new' : '/initiatives/edit/' . $id));
        return;
    }
    if ($id === null) {
        $objectId = new MongoDB\BSON\ObjectId();
        $values += [
            '_id' => $objectId, 'id' => (string) $objectId, 'inactive' => false,
            'created' => date('Y-m-d'), 'created_by' => $_SESSION['username'],
        ];
        $osiris->initiatives->insertOne($values);
        $id = (string) $objectId;
    } else {
        $values['updated'] = date('Y-m-d');
        $values['updated_by'] = $_SESSION['username'];
        $osiris->initiatives->updateOne(['_id' => $initiative['_id']], ['$set' => $values]);
    }
    $_SESSION['msg'] = translate('initiatives.saved');
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
        'updated' => date('Y-m-d'), 'updated_by' => $_SESSION['username'],
    ]]);
    header('Location: ' . ROOTPATH . '/initiatives/view/' . $id);
}, 'login');

Route::post('/crud/initiatives/(.*)/objectives/create', function ($id) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess(true);
    $initiative = Initiatives::find($osiris, $id);
    $target = '#objectives';
    try {
        if (!is_array($_POST['values'] ?? null)) throw new InvalidArgumentException(translate('error.no_values'));
        $values = Initiatives::objectiveValues($_POST['values']);
        $values += [
            'initiative' => $initiative['_id'], 'inactive' => false,
            'created' => date('Y-m-d'), 'created_by' => $_SESSION['username'],
        ];
        $osiris->objectives->insertOne($values);
    } catch (InvalidArgumentException $e) {
        $_SESSION['objective_form'][$id]['new'] = Initiatives::objectiveDraft(is_array($_POST['values'] ?? null) ? $_POST['values'] : []);
        $target = '#add-objective';
        $_SESSION['msg'] = e($e->getMessage());
        $_SESSION['msg_type'] = 'error';
    }
    header('Location: ' . ROOTPATH . '/initiatives/view/' . $id . $target);
}, 'login');

Route::post('/crud/initiatives/(.*)/objectives/(update|archive)/(.*)', function ($id, $action, $objectiveId) {
    include_once BASEPATH . '/php/init.php';
    Initiatives::requireAccess(true);
    $initiative = Initiatives::find($osiris, $id);
    $filter = ['initiative' => $initiative['_id']];
    if (!DB::is_ObjectID($objectiveId)) {
        http_response_code(404);
        abortwith(404, translate('initiatives.objective'), '/initiatives/view/' . $id);
    }
    $filter['_id'] = DB::to_ObjectID($objectiveId);
    if (!$osiris->objectives->findOne($filter)) {
        http_response_code(404);
        abortwith(404, translate('initiatives.objective'), '/initiatives/view/' . $id);
    }
    $target = '#objectives';
    try {
        if ($action === 'archive') {
            $values = ['inactive' => ($_POST['inactive'] ?? '') === '1'];
        } else {
            if (!is_array($_POST['values'] ?? null)) throw new InvalidArgumentException(translate('error.no_values'));
            $values = Initiatives::objectiveValues($_POST['values']);
        }
        $values['updated'] = date('Y-m-d');
        $values['updated_by'] = $_SESSION['username'];
        $osiris->objectives->updateOne($filter, ['$set' => $values]);
    } catch (InvalidArgumentException $e) {
        $target = '#edit-objective-' . $objectiveId;
        $_SESSION['objective_form'][$id][$objectiveId] = Initiatives::objectiveDraft(is_array($_POST['values'] ?? null) ? $_POST['values'] : []);
        $_SESSION['msg'] = e($e->getMessage());
        $_SESSION['msg_type'] = 'error';
    }
    header('Location: ' . ROOTPATH . '/initiatives/view/' . $id . $target);
}, 'login');
