<?php


Route::get('/nagoya', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";

    $allowed = $Settings->featureEnabled('nagoya') && $Settings->hasPermission('nagoya.view');
    if (!$allowed) {
        header("Location: " . ROOTPATH . "/projects?msg=no-permission");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
        ['name' => 'Nagoya Protocol']
    ];

    $nagoya = $osiris->proposals->find(
        ['nagoya.enabled' => true]
    )->toArray();


    // $nagoya = array_merge($nagoya_proposals, $nagoya_projects);

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/nagoya.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/proposals/nagoya-scope/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $collection = 'proposals';

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $project = $osiris->$collection->findOne(['_id' => $mongo_id]);
    } else {
        $project = $osiris->$collection->findOne(['name' => $id]);
        $id = strval($project['_id'] ?? '');
    }
    if (empty($project)) {
        header("Location: " . ROOTPATH . "/$collection?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Project proposals', 'Projektanträge'), 'path' => "/$collection"],
        ['name' => $project['name'], 'path' => "/$collection/view/$id"],
        ['name' => lang('Nagoya Protocol', 'Nagoya-Protokoll')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/$collection/nagoya-scope.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/proposals/nagoya-editor/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $collection = 'proposals';

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $project = $osiris->$collection->findOne(['_id' => $mongo_id]);
    } else {
        $project = $osiris->$collection->findOne(['name' => $id]);
        $id = strval($project['_id'] ?? '');
    }
    if (empty($project)) {
        header("Location: " . ROOTPATH . "/$collection?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Project proposals', 'Projektanträge'), 'path' => "/$collection"],
        ['name' => $project['name'], 'path' => "/$collection/view/$id"],
        ['name' => lang('Nagoya Review', 'Nagoya Bewertung')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/$collection/nagoya-editor.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::post('/crud/nagoya/review-abs-countries/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Nagoya.php";
    $ids      = $_POST['id'] ?? [];
    $nagoyaParty   = $_POST['nagoyaParty'] ?? [];
    $ownABSMeasures   = $_POST['ownABSMeasures'] ?? [];
    $comment = $_POST['comment'] ?? [];

    $errors = [];
    $map = [];
    $mongo_id = $DB->to_ObjectID($id);
    $project = $osiris->proposals->findOne(['_id' => $mongo_id]);
    if (empty($project) || empty($project['nagoya']['countries'] ?? null)) {
        header("Location: " . ROOTPATH . "/projects/view/$id?error=project-not-found-or-no-nagoya");
        die;
    }
    foreach ($project['nagoya']['countries'] ?? [] as $c) $map[$c['id']] = $c; // by id

    $updates = [];
    for ($i = 0; $i < count($ids); $i++) {
        $cid = $ids[$i];
        if (!isset($map[$cid])) continue;
        $p = in_array(($nagoyaParty[$i] ?? ''), ['yes', 'no', 'unknown']) ? $nagoyaParty[$i] : 'unknown';
        $h = in_array(($ownABSMeasures[$i] ?? ''), ['yes', 'no', 'unknown']) ? $ownABSMeasures[$i] : 'unknown';

        $map[$cid]['review'] = [
            'nagoyaParty'    => $p,
            'ownABSMeasures' => $h,
            'comment'      => trim($comment[$i] ?? ''),
            'reviewed_by'     => $_SESSION['username'],
            'reviewed'     => date('Y-m-d'),
        ];
        $updates[] = $map[$cid];
    }

    if (empty($errors)) {
        $countries = array_values($map);
        $nagoya = DB::doc2Arr($project['nagoya']);
        $nagoya['countries'] = $countries;
        $nagoya['absRationale'] = trim($_POST['overallRationale'] ?? '');
        $nagoya = Nagoya::writeThrough(DB::doc2Arr($project), $nagoya); // setzt nagoya.status etc.

        $osiris->proposals->updateOne(['_id' => $project['_id']], ['$set' => ['nagoya' => $nagoya]]);
        $_SESSION['msg'] = lang("Nagoya review saved.", "Nagoya-Bewertung gespeichert.");
        $_SESSION['msg_type'] = 'success';
    } else {
        $_SESSION['msg'] = implode("; ", $errors);
        $_SESSION['msg_type'] = 'error';
    }
    header("Location: " . ROOTPATH . "/proposals/nagoya-editor/$id");
});

Route::post('/crud/nagoya/notify-researchers', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Nagoya.php";

    $project_id = $_POST['project_id'] ?? '';
    $mongo_id = $DB->to_ObjectID($project_id);
    $project = $osiris->proposals->findOne(['_id' => $mongo_id]);
    if (empty($project) || empty($project['nagoya'] ?? null)) {
        header("Location: " . ROOTPATH . "/proposals/view/$project_id?error=project-not-found-or-no-nagoya");
        die;
    }

    $nagoya = DB::doc2Arr($project['nagoya']);
    if (($nagoya['status'] ?? 'unknown') !== 'researcher-input' || ($nagoya['review']['researcher-notified'] ?? false)) {
        header("Location: " . ROOTPATH . "/proposals/view/$project_id?error=invalid-nagoya-status");
        die;
    }

    // send notification to researchers
    $applicants = [];
    foreach ($project['persons'] ?? [] as $a) {
        if (!in_array($a['role'], ['applicant', 'PI', 'co-PI'])) continue;
        $applicants[] = $a['user'];
    }
    foreach ($applicants as $user) {
        $DB->addMessage(
            $user,
            "The Nagoya Protocol review for your project proposal '" . ($project['name'] ?? '') . "' has been completed. Please view the results and take any necessary actions regarding ABS compliance.",
            "Die Nagoya-Bewertung für deinen Projektantrag '" . ($project['name'] ?? '') . "' wurde abgeschlossen. Bitte schau dir die Ergebnisse an und ergreife gegebenenfalls erforderliche Maßnahmen zur ABS-Compliance.",
            'nagoya',
            "/proposals/view/$project_id"
        );
    }

    // update nagoya.review.researcher-notified
    $nagoya['review']['researcher-notified'] = true;
    $osiris->proposals->updateOne(['_id' => $project['_id']], ['$set' => ['nagoya' => $nagoya]]);

    $_SESSION['msg'] = lang("Researchers have been notified about the completed ABS review.", "Antragstellende wurden über die abgeschlossene ABS-Bewertung benachrichtigt.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/proposals/view/$project_id");
});
