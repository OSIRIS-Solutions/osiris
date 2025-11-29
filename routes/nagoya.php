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
        ['name' => lang('Project proposals', 'Projektanträge'), 'path' => "/proposals"],
        ['name' => 'Nagoya Protocol']
    ];

    $nagoya = $osiris->proposals->find(
        ['nagoya.enabled' => true]
    )->toArray();

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/nagoya.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/proposals/nagoya-scope/([A-Za-z0-9]*)', function ($id) {
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

Route::get('/proposals/nagoya-countries/([A-Za-z0-9]*)', function ($id) {
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
    include BASEPATH . "/pages/$collection/nagoya-countries.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/proposals/nagoya-evaluation/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Nagoya.php";
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
        ['name' => lang('Nagoya Evaluation', 'Nagoya-Bewertung')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/$collection/nagoya-evaluation.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/proposals/nagoya-permits/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Nagoya.php";
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
        ['name' => lang('Nagoya Permits', 'Nagoya-Genehmigungen')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/$collection/nagoya-permits.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/proposals/nagoya-permits/([A-Za-z0-9]*)/([A-Za-z0-9]*)', function ($id, $cid) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Nagoya.php";
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
    $nagoya = DB::doc2Arr($project['nagoya'] ?? []);
    $countries = DB::doc2Arr($nagoya['countries'] ?? []);
    $found = false;
    $country = null;
    foreach ($countries as $c) {
        if (($c['id'] ?? '') === $cid) {
            $found = true;
            $country = $c;
            break;
        }
    }
    if (!$found) {
        header("Location: " . ROOTPATH . "/$collection/nagoya-permits/$id?msg=country-not-found");
        die;
    }

    $breadcrumb = [
        ['name' => lang('Project proposals', 'Projektanträge'), 'path' => "/$collection"],
        ['name' => $project['name'], 'path' => "/$collection/view/$id"],
        ['name' => lang('Nagoya Permits', 'Nagoya-Genehmigungen'), 'path' => "/$collection/nagoya-permits/$id"],
        ['name' => $DB->getCountry($country['code'], lang('name', 'name_de'))]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/$collection/nagoya-permits-country.php";
    include BASEPATH . "/footer.php";
}, 'login');



/** POST Routes */

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
    header("Location: " . ROOTPATH . "/proposals/nagoya-countries/$id");
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

Route::post('/crud/nagoya/add-abs-scope/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Nagoya.php";

    $mongo_id = $DB->to_ObjectID($id);
    $project  = $osiris->proposals->findOne(['_id' => $mongo_id]);

    if (empty($project) || empty($project['nagoya'] ?? null)) {
        header("Location: " . ROOTPATH . "/proposals/view/$id?error=project-not-found-or-no-nagoya");
        die;
    }

    $nagoya        = DB::doc2Arr($project['nagoya']);
    $countries     = DB::doc2Arr($nagoya['countries'] ?? []);
    $scopeInput    = $_POST['scope'] ?? [];
    $updatedCountries = [];

    foreach ($countries as $country) {
        $cid = $country['id'] ?? null;
        if (!$cid) {
            $updatedCountries[] = $country;
            continue;
        }

        // Nur für ABS-relevante Länder Scope speichern, andere unverändert lassen
        if (!($country['abs'] ?? false)) {
            $updatedCountries[] = $country;
            continue;
        }

        $countryScopeIn  = $scopeInput[$cid] ?? null;
        $countryScopeOut = [];

        if ($countryScopeIn) {
            // --- Scope-Gruppen einlesen ---
            $groupsIn = $countryScopeIn['groups'] ?? null;

            // Fallback: falls doch noch "flaches" Scope gesendet wird, in eine Gruppe mappen
            if ($groupsIn === null) {
                $groupsIn = [$countryScopeIn];
            }

            $groupsOut = [];

            foreach ($groupsIn as $g) {
                if (!is_array($g)) continue;

                $geo   = trim($g['geo'] ?? '');
                $temp  = trim($g['temporal'] ?? '');
                $ongo  = !empty($g['temporal_ongoing']);

                // Material normalisieren
                $mat = $g['material'] ?? [];
                if (!is_array($mat)) {
                    $mat = array_filter(array_map('trim', explode(',', (string)$mat)));
                }
                $mat = array_values(array_unique(array_filter($mat, fn($v) => $v !== '')));

                // Utilization normalisieren
                $util = $g['utilization'] ?? [];
                if (!is_array($util)) {
                    $util = array_filter(array_map('trim', explode(',', (string)$util)));
                }
                $util = array_values(array_unique(array_filter($util, fn($v) => $v !== '')));

                // Leere Gruppen komplett ignorieren
                if ($geo === '' && $temp === '' && !$ongo && empty($mat) && empty($util)) {
                    continue;
                }

                $groupsOut[] = [
                    'geo'              => $geo,
                    'temporal'    => $temp,
                    'temporal_ongoing' => $ongo ? true : false,
                    'material'         => $mat,
                    'utilization'      => $util,
                ];
            }

            if (!empty($groupsOut)) {
                $countryScopeOut['groups'] = $groupsOut;
            }

            // --- aTK & Notes auf Country-Ebene ---
            $atkUsed = !empty($countryScopeIn['atk_used']);
            $countryScopeOut['atk_used'] = $atkUsed;
            $atkDetails = trim($countryScopeIn['atk_details'] ?? '');
            if ($atkUsed && $atkDetails !== '') {
                $countryScopeOut['atk_details'] = $atkDetails;
            } else {
                // keine Details speichern, wenn aTK nicht angehakt oder leer
                if (isset($countryScopeOut['atk_details'])) {
                    unset($countryScopeOut['atk_details']);
                }
            }

            $notes = trim($countryScopeIn['notes'] ?? '');
            if ($notes !== '') {
                $countryScopeOut['notes'] = $notes;
            }

            // Nur Scope setzen, wenn es überhaupt Inhalte gibt
            if (!empty($countryScopeOut)) {
                $country['scope'] = $countryScopeOut;
            } else {
                // komplett leeren, wenn nichts geliefert wurde
                unset($country['scope']);
            }
        }

        $updatedCountries[] = $country;
    }

    $nagoya['countries'] = array_values($updatedCountries);

    $action = $_POST['action'] ?? 'save';
    // Scope-Workflow-Flags
    $nagoya['scopeSubmitted'] = ($nagoya['scopeSubmitted'] ?? false);

    if ($action === 'submit' && !($nagoya['scopeSubmitted'])) {
        // Nur abschicken, wenn wirklich komplett
        if (Nagoya::scopeComplete($nagoya)) {
            $nagoya['scopeSubmitted']   = true;
            $nagoya['scopeSubmittedAt'] = date('Y-m-d');
            $nagoya['scopeSubmittedBy'] = $_SESSION['username'] ?? null;

            // send messages to nagoya team
            $DB->addMessages(
                'right:nagoya.view',
                "The ABS scope for the project proposal '" . ($project['name'] ?? $id) . "' has been submitted for review.",
                "Der ABS-Scope für den Projektantrag '" . ($project['name'] ?? $id) . "' wurde zur Prüfung eingereicht.",
                'nagoya',
                "/$collection/nagoya-evaluation/" . $id,
            );
        } else {
            $_SESSION['msg'] = lang(
                'Scope is not complete yet. Please fill all required fields before submitting.',
                'Der Scope ist noch nicht vollständig. Bitte alle Pflichtfelder ausfüllen, bevor Sie einreichen.'
            );
            $_SESSION['msg_type'] = 'error';
            header("Location: " . ROOTPATH . "/proposals/nagoya-scope/$id");
            exit;
        }
    }

    // Status & Projektionen neu berechnen
    $nagoya = Nagoya::writeThrough(
        DB::doc2Arr($project),
        $nagoya,
        $_SESSION['username'] ?? null
    );

    $osiris->proposals->updateOne(
        ['_id' => $project['_id']],
        ['$set' => ['nagoya' => $nagoya]]
    );

    $_SESSION['msg'] = lang('Scope information saved.', 'Scope-Informationen gespeichert.');
    $_SESSION['msg_type'] = 'success';

    header("Location: " . ROOTPATH . "/proposals/nagoya-scope/$id#nagoya");
    exit;
});


Route::post('/crud/nagoya/evaluate-abs/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Nagoya.php";

    // Optional: Permission check
    if (!$Settings->hasPermission('nagoya.view')) {
        $_SESSION['msg'] = lang('You are not allowed to edit ABS evaluations.', 'Du darfst ABS-Bewertungen nicht bearbeiten.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/view/$id#nagoya");
        exit;
    }

    $mongo_id = $DB->to_ObjectID($id);
    $project  = $osiris->proposals->findOne(['_id' => $mongo_id]);

    if (empty($project) || empty($project['nagoya'] ?? null)) {
        header("Location: " . ROOTPATH . "/proposals/view/$id?error=project-not-found-or-no-nagoya");
        exit;
    }

    $nagoya      = DB::doc2Arr($project['nagoya']);
    $countries   = DB::doc2Arr($nagoya['countries'] ?? []);
    $input       = $_POST['evaluation'] ?? [];
    $username    = $_SESSION['username'] ?? null;
    $today       = date('Y-m-d');

    // --- 1. Pro Land Evaluation updaten ------------------------------------
    $updatedCountries = [];

    foreach ($countries as $c) {
        $cid = $c['id'] ?? null;
        if (!$cid) {
            $updatedCountries[] = $c;
            continue;
        }

        // nur ABS-relevante Länder bewerten
        if (!($c['abs'] ?? false)) {
            $updatedCountries[] = $c;
            continue;
        }

        $in = $input[$cid] ?? null;
        if ($in === null) {
            // nichts gesendet → alte Evaluation behalten
            $updatedCountries[] = $c;
            continue;
        }

        $label     = trim($in['label'] ?? '');
        $rationale = trim($in['rationale'] ?? '');

        // Permits normalisieren
        $permitsIn  = $in['permits'] ?? [];
        $permitsOut = [];

        if (is_array($permitsIn)) {
            foreach ($permitsIn as $p) {
                if (!is_array($p)) continue;
                $name    = trim($p['name'] ?? '');
                $status  = trim($p['status'] ?? '');
                $comment = trim($p['comment'] ?? '');

                // komplett leere Zeilen überspringen
                if ($name === '' && $status === '' && $comment === '') {
                    continue;
                }

                $permitsOut[] = [
                    'name'    => $name,
                    'status'  => $status !== '' ? $status : null,
                    'comment' => $comment !== '' ? $comment : null,
                ];
            }
        }

        // Evaluation nur setzen, wenn überhaupt Inhalte da sind
        if ($label === '' && $rationale === '' && empty($permitsOut)) {
            // ggf. alte Evaluation löschen
            if (isset($c['evaluation'])) {
                unset($c['evaluation']);
            }
        } else {
            $eval = [
                'label'     => $label !== '' ? $label : null,
                'rationale' => $rationale !== '' ? $rationale : null,
                'permits'   => $permitsOut,
                'by'        => $username,
                'at'        => $today,
            ];
            $c['evaluation'] = $eval;
        }

        $updatedCountries[] = $c;
    }

    $nagoya['countries'] = array_values($updatedCountries);

    // --- 2. Status etc. durch Nagoya-Logik laufen lassen -------------------
    $nagoya = Nagoya::writeThrough(
        DB::doc2Arr($project),
        $nagoya,
        $username
    );

    $osiris->proposals->updateOne(
        ['_id' => $project['_id']],
        ['$set' => ['nagoya' => $nagoya]]
    );

    $_SESSION['msg'] = lang('ABS evaluation saved.', 'ABS-Bewertung gespeichert.');
    $_SESSION['msg_type'] = 'success';

    header("Location: " . ROOTPATH . "/proposals/nagoya-evaluation/$id");
    exit;
});


Route::post('/crud/nagoya/add-permit-note/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Nagoya.php";

    $mongo_id = $DB->to_ObjectID($id);
    $project  = $osiris->proposals->findOne(['_id' => $mongo_id]);

    if (empty($project) || empty($project['nagoya'] ?? null)) {
        $_SESSION['msg'] = lang('Project not found or no Nagoya information.', 'Projekt nicht gefunden oder keine Nagoya-Informationen.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/view/$id");
        exit;
    }

    $countryId = $_POST['country_id'] ?? null;
    $message   = trim($_POST['message'] ?? '');

    if ($message === '') {
        $_SESSION['msg'] = lang('Note is empty.', 'Notiz ist leer.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/nagoya-permits/$id?country=" . urlencode($countryId));
        exit;
    }

    $username = $_SESSION['username'] ?? null;

    $nagoya = DB::doc2Arr($project['nagoya']);
    $nagoya['permitNotes'] = DB::doc2Arr($nagoya['permitNotes'] ?? []);
    if (!is_array($nagoya['permitNotes'])) {
        $nagoya['permitNotes'] = [];
    }

    $nagoya['permitNotes'][] = [
        'id'         => uniqid('note_'),
        'country_id' => $countryId,          // can be used later to filter per country
        'message'    => $message,
        'by'         => $username,
        'at'         => date('Y-m-d H:i'),
    ];

    // let Nagoya logic run if needed
    $nagoya = Nagoya::writeThrough(DB::doc2Arr($project), $nagoya, $username);

    $osiris->proposals->updateOne(
        ['_id' => $project['_id']],
        ['$set' => ['nagoya' => $nagoya]]
    );

    $_SESSION['msg'] = lang('Note added.', 'Notiz hinzugefügt.');
    $_SESSION['msg_type'] = 'success';

    header("Location: " . ROOTPATH . "/proposals/nagoya-permits/$id/" . urlencode($countryId));
    exit;
});


Route::post('/crud/nagoya/update-permits/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Nagoya.php";

    $countryId = $_GET['country'] ?? null;

    $mongo_id = $DB->to_ObjectID($id);
    $project  = $osiris->proposals->findOne(['_id' => $mongo_id]);

    if (empty($project) || empty($project['nagoya'] ?? null) || !$countryId) {
        $_SESSION['msg'] = lang('Project or country not found.', 'Projekt oder Land nicht gefunden.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/view/$id");
        exit;
    }

    $nagoya    = DB::doc2Arr($project['nagoya']);
    $countries = DB::doc2Arr($nagoya['countries'] ?? []);
    $inputPermits = $_POST['permits'] ?? [];
    $username  = $_SESSION['username'] ?? null;

    // helper: build map of existing permits for this country to keep docs
    $countryIndex = null;
    $country      = null;

    foreach ($countries as $idx => $c) {
        if (($c['id'] ?? null) === $countryId) {
            $countryIndex = $idx;
            $country      = $c;
            break;
        }
    }

    if ($countryIndex === null) {
        $_SESSION['msg'] = lang('Country not found for this project.', 'Land wurde für dieses Projekt nicht gefunden.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/view/$id");
        exit;
    }

    $existingPermits = [];
    foreach (($country['evaluation']['permits'] ?? []) as $p) {
        if (!empty($p['id'])) {
            $existingPermits[$p['id']] = $p;
        }
    }

    $newPermits = [];

    foreach ($inputPermits as $pid => $p) {
        if (!is_array($p)) continue;

        $pid        = (string)$pid;
        $name       = trim($p['name'] ?? '');
        $status     = trim($p['status'] ?? '');
        $identifier = trim($p['identifier'] ?? '');
        $provider   = trim($p['provider'] ?? '');
        $comment    = trim($p['comment'] ?? '');
        $checked    = !empty($p['checked']);

        // reuse existing docs if present
        $docs = $existingPermits[$pid]['docs'] ?? [];

        // skip completely empty permits (no text, no docs)
        if (
            $name === '' &&
            $status === '' &&
            $identifier === '' &&
            $provider === '' &&
            $comment === '' &&
            empty($docs)
        ) {
            continue;
        }

        $newPermits[] = [
            'id'         => $pid,
            'name'       => $name,
            'status'     => $status !== '' ? $status : null,
            'identifier' => $identifier !== '' ? $identifier : null,
            'provider'   => $provider !== '' ? $provider : null,
            'comment'    => $comment !== '' ? $comment : null,
            'checked'    => $checked,
            'docs'       => $docs,
        ];
    }

    // write back to country
    if (!isset($country['evaluation']) || !is_array($country['evaluation'])) {
        $country['evaluation'] = [];
    }
    $country['evaluation']['permits'] = $newPermits;

    $countries[$countryIndex] = $country;
    $nagoya['countries']      = array_values($countries);

    // optional: recompute project A/B/C label if you want, but permits themselves do not change label.
    // let Nagoya::writeThrough handle status / consistency
    $nagoya = Nagoya::writeThrough(DB::doc2Arr($project), $nagoya, $username);

    $osiris->proposals->updateOne(
        ['_id' => $project['_id']],
        ['$set' => ['nagoya' => $nagoya]]
    );

    $_SESSION['msg'] = lang('Permit information saved.', 'Genehmigungsinformationen gespeichert.');
    $_SESSION['msg_type'] = 'success';

    header("Location: " . ROOTPATH . "/proposals/nagoya-permits/$id/" . urlencode($countryId));
    exit;
});


Route::post('/crud/nagoya/upload-permit-doc/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Nagoya.php";

    $countryId = $_POST['country_id'] ?? null;
    $permitId  = $_POST['permit_id'] ?? null;
    $docType   = trim($_POST['doc_type'] ?? 'OTHER');
    $docComment= trim($_POST['doc_comment'] ?? '');
    $username  = $_SESSION['username'] ?? null;

    if (!$countryId || !$permitId) {
        $_SESSION['msg'] = lang('Missing country or permit information.', 'Fehlende Angaben zu Land oder Genehmigung.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/view/$id");
        exit;
    }

    if (empty($_FILES['file']['tmp_name'])) {
        $_SESSION['msg'] = lang('No file uploaded.', 'Keine Datei hochgeladen.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/nagoya-permits/$id?country=" . urlencode($countryId));
        exit;
    }

    // basic file handling
    $file      = $_FILES['file'];
    $origName  = $file['name'] ?? 'document';
    $ext       = pathinfo($origName, PATHINFO_EXTENSION);
    $safeName  = preg_replace('/[^A-Za-z0-9_\.-]/', '_', basename($origName));
    $docId     = uniqid('doc_');
    $storedName= $docId . ($ext ? '.' . $ext : '');

    // TODO: adjust storage path and public URL to your setup
    $storageDir = BASEPATH . '/data/nagoya_docs/' . $id . '/';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0770, true);
    }

    $targetPath = $storageDir . $storedName;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $_SESSION['msg'] = lang('File upload failed.', 'Datei-Upload fehlgeschlagen.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/nagoya-permits/$id?country=" . urlencode($countryId));
        exit;
    }

    // public URL: adapt to your file serving setup
    $publicUrl = ROOTPATH . '/data/nagoya_docs/' . $id . '/' . $storedName;

    // load project and attach document
    $mongo_id = $DB->to_ObjectID($id);
    $project  = $osiris->proposals->findOne(['_id' => $mongo_id]);

    if (empty($project) || empty($project['nagoya'] ?? null)) {
        $_SESSION['msg'] = lang('Project not found or no Nagoya information.', 'Projekt nicht gefunden oder keine Nagoya-Informationen.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/view/$id");
        exit;
    }

    $nagoya    = DB::doc2Arr($project['nagoya']);
    $countries = DB::doc2Arr($nagoya['countries'] ?? []);

    $countryIndex = null;
    foreach ($countries as $idx => $c) {
        if (($c['id'] ?? null) === $countryId) {
            $countryIndex = $idx;
            break;
        }
    }

    if ($countryIndex === null) {
        $_SESSION['msg'] = lang('Country not found for this project.', 'Land wurde für dieses Projekt nicht gefunden.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/view/$id");
        exit;
    }

    $country = $countries[$countryIndex];
    if (!isset($country['evaluation']) || !is_array($country['evaluation'])) {
        $country['evaluation'] = [];
    }
    if (!isset($country['evaluation']['permits']) || !is_array($country['evaluation']['permits'])) {
        $country['evaluation']['permits'] = [];
    }

    // find permit by id
    $permits = $country['evaluation']['permits'];
    $found   = false;

    foreach ($permits as &$p) {
        if (($p['id'] ?? null) === $permitId) {
            if (!isset($p['docs']) || !is_array($p['docs'])) {
                $p['docs'] = [];
            }
            $p['docs'][] = [
                'id'          => $docId,
                'filename'    => $origName,
                'stored_name' => $storedName,
                'type'        => $docType,
                'comment'     => $docComment !== '' ? $docComment : null,
                'uploaded'    => date('Y-m-d'),
                'uploaded_by' => $username,
                'url'         => $publicUrl,
            ];
            $found = true;
            break;
        }
    }
    unset($p);

    if (!$found) {
        // permit not found – optional: create a new one? For now, just error.
        $_SESSION['msg'] = lang('Permit not found for this country.', 'Genehmigung wurde für dieses Land nicht gefunden.');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/proposals/nagoya-permits/$id?country=" . urlencode($countryId));
        exit;
    }

    $country['evaluation']['permits'] = $permits;
    $countries[$countryIndex]         = $country;
    $nagoya['countries']              = array_values($countries);

    // let Nagoya logic run (status might later consider permit documents too)
    $nagoya = Nagoya::writeThrough(DB::doc2Arr($project), $nagoya, $username);

    $osiris->proposals->updateOne(
        ['_id' => $project['_id']],
        ['$set' => ['nagoya' => $nagoya]]
    );

    $_SESSION['msg'] = lang('Document uploaded.', 'Dokument hochgeladen.');
    $_SESSION['msg_type'] = 'success';

    header("Location: " . ROOTPATH . "/proposals/nagoya-permits/$id?country=" . urlencode($countryId));
    exit;
});