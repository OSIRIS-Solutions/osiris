<?php

/**
 * Routing file for projects and collaborations
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


Route::get('/(projects|proposals)', function ($collection) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => $collection == 'projects' ? lang('Projects', 'Projekte') : lang('Project proposals', 'Projektanträge')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/$collection/list.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/(projects|proposals)/new', function ($collection) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => $collection == 'projects' ? lang('Projects', 'Projekte') : lang('Project proposals', 'Projektanträge'), 'path' => "/$collection"],
        ['name' => lang("New", "Neu")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/proposals/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/projects/create-from-proposal/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $collection = 'projects';
    $breadcrumb = [
        ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
        ['name' => lang("New", "Neu")]
    ];
    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $project = $osiris->proposals->findOne(['_id' => $mongo_id]);
    } else {
        $project = $osiris->proposals->findOne(['name' => $id]);
        $id = strval($project['_id'] ?? '');
    }

    global $form;
    $form = DB::doc2Arr($project);

    $from_proposal = true;
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/proposals/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/(projects|proposals)/search', function ($collection) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => $collection == 'projects' ? lang('Projects', 'Projekte') : lang('Project proposals', 'Projektanträge'), 'path' => "/$collection"],
        ['name' => lang("Search", "Suche")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/projects/search.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/(projects|proposals)/statistics', function ($collection) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => $collection == 'projects' ? lang('Projects', 'Projekte') : lang('Project proposals', 'Projektanträge'), 'path' => "/$collection"],
        ['name' => lang("Statistics", "Statistik")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/projects/statistics.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/(projects|proposals)/view/(.*)', function ($collection, $id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

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
        ['name' => $collection == 'projects' ? lang('Projects', 'Projekte') : lang('Project proposals', 'Projektanträge'), 'path' => "/$collection"],
        ['name' => $project['name']]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/$collection/view.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/(projects|proposals)/(edit|collaborators|finance|persons)/([a-zA-Z0-9]*)', function ($collection, $page, $id) {
    include_once BASEPATH . "/php/init.php";
    require_once BASEPATH . "/php/Project.php";

    $user = $_SESSION['username'];

    $mongo_id = $DB->to_ObjectID($id);
    $project = $osiris->$collection->findOne(['_id' => $mongo_id]);
    if (empty($project)) {
        header("Location: " . ROOTPATH . "/$collection?msg=not-found");
        die;
    }
    $Project = new Project($project);

    $user_project = in_array($user, array_column(DB::doc2Arr($project['persons'] ?? []), 'user'));
    $edit_perm = ($project['created_by'] == $_SESSION['username'] || $Settings->hasPermission($collection . '.edit') || ($Settings->hasPermission('projects.edit-own') && $user_project));
    if (!$edit_perm) {
        header("Location: " . ROOTPATH . "/$collection/view/$id?msg=no-permission");
        die;
    }

    switch ($page) {
        case 'collaborators':
            $name = lang("Collaborators", "Kooperationspartner");
            break;
        case 'finance':
            $name = lang("Finance", "Finanzen");
            break;
        case 'persons':
            $name = lang("Persons", "Personen");
            break;
        default:
            $name = lang("Edit", "Bearbeiten");
            break;
    }

    $breadcrumb = [
        ['name' => $collection == 'projects' ? lang('Projects', 'Projekte') : lang('Project proposals', 'Projektanträge'), 'path' => "/$collection"],
        ['name' =>  $project['name'], 'path' => "/$collection/view/$id"],
        ['name' => $name]
    ];

    global $form;
    $form = DB::doc2Arr($project);

    include BASEPATH . "/header.php";
    switch ($page) {
        case 'collaborators':
            include BASEPATH . "/pages/projects/collaborators.php";
            break;
        case 'finance':
            include BASEPATH . "/pages/proposals/finance.php";
            break;
        case 'persons':
            include BASEPATH . "/pages/projects/persons.php";
            break;
        default:
            include BASEPATH . "/pages/proposals/edit.php";
    }
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/projects/subproject/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    if (!$Settings->hasPermission('projects.add-subprojects')) {
        header("Location: " . ROOTPATH . "/projects?msg=no-permission");
        die;
    }
    // get project
    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $project = $osiris->projects->findOne(['_id' => $mongo_id]);
    } else {
        $project = $osiris->projects->findOne(['name' => $id]);
        $id = strval($project['_id'] ?? '');
    }
    // check if project exists
    if (empty($project)) {
        header("Location: " . ROOTPATH . "/projects?msg=not-found");
        die;
    }

    // set breadcrumb
    $breadcrumb = [
        ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
        ['name' => $project['name'], 'path' => "/projects/view/$id"],
        ['name' => lang("Add subproject", "Teilprojekt hinzufügen")]
    ];

    // create new form
    global $form;
    $form = DB::doc2Arr($project);
    // user abbreviation (first letter of first and last name)
    try {
        // in case of unicode errors or sth like that
        $suffix = $USER['first'][0] . $USER['last'][0];
    } catch (\Throwable $th) {
        $suffix = 'XX';
    }

    // add suffix to project name
    $form['name'] = $form['name'] . "-" . $suffix;
    // check if name is unique
    $project_exist = $osiris->projects->findOne(['name' => $form['name']]);
    if (!empty($project_exist)) {
        $form['name'] = $form['name'] . "-" . uniqid();
    }
    // delete stuff that should not be inherited
    $delete = [
        'title',
        'title_de',
        'ressources',
        'personnel',
        'in-kind',
        'created',
        'created_by',
        'updated',
        'updated_by',
        '_id'
    ];
    foreach ($delete as $key) {
        unset($form[$key]);
    }

    // add parent project
    $form['parent'] = $project['name'];
    $form['parent_id'] = strval($project['_id']);
    $form['type'] = $project['type'];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/proposals/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');

function getTemplatePlaceholders($templatePath)
{
    // Die DOCX-Datei als ZIP öffnen
    $zip = new \ZipArchive;
    if ($zip->open($templatePath) === true) {
        // Die Datei word/document.xml aus dem ZIP-Archiv holen
        $content = $zip->getFromName('word/document.xml');
        $zip->close();

        // Mit einer regulären Expression alle Platzhalter finden (z.B. {Platzhalter})
        preg_match_all('/\{(.*?)\}/', $content, $matches);

        // Alle Platzhalter zurückgeben
        return $matches[1]; // Gibt eine Liste von Platzhaltern zurück

    } else {
        return [];
    }
}


// projects/download/:id
Route::post('/proposals/download/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    error_reporting(E_ERROR | E_PARSE);
    // Lade das Template

    $user = $_SESSION['username'];
    $format = $_POST['format'] ?? 'word';

    $mongo_id = $DB->to_ObjectID($id);
    $project = $osiris->proposals->findOne(['_id' => $mongo_id]);
    if (empty($project)) {
        header("Location: " . ROOTPATH . "/proposals?msg=not-found");
        die;
    }
    $project = DB::doc2Arr($project);
    $Project = new Project($project);

    $filename = $project['name'];

    if ($format == 'json') {
        $filename .= ".json";
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($project, JSON_PRETTY_PRINT);
        die;
    }

    $templatePath = BASEPATH . "/templates/project-template.docx";
    $filename .= ".docx";

    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
    $abstract = clean_comment_export(strip_tags($project['abstract'] ?? 'NA'), false);
    $res = $project['ressources'] ?? [];
    $persons = [];
    foreach ($project['persons'] as $p) {
        $persons[] = $p['name'];
    }
    $persons = implode(', ', $persons);
    // dump($project['abstract']);
    $funding_organization = $project['funding_organization'] ?? $project['funder'] ?? null;
    if (DB::is_ObjectID($funding_organization)) {
        $org = $osiris->organizations->findOne(['_id' => $DB->to_ObjectID($funding_organization)]);
        if (!empty($org)) {
            $funding_organization = $org['name'];
        } else {
            $funding_organization = 'NA';
        }
    }

    $contacts = [];
    foreach ($project['applicants'] ?? [] as $applicant) {
        $contacts[] = $DB->getNameFromId($applicant);
    }
    $contacts = implode(', ', $contacts);



    $projectValues = [
        "contact" => $contacts,
        "applicants" => $contacts,
        "name" => $project['name'],
        "title" => $project['title'],
        "funder" => $project['funder'],
        "funding_organization" => $funding_organization,
        "role" => $Project->getRoleRaw(),
        "duration" => $Project->getDuration() . lang(" months", " Monate"),
        "start" => $Project->getStartDate(),
        "end" => $Project->getEndDate(),
        "grant_sum_proposed" => $project['grant_sum_proposed'] ?? 0,
        "grant_income_proposed" => $project['grant_income_proposed'] ?? 0,
        "abstract" => $abstract,
        "personnel" => $project['personnel'] ?? 'NA',
        "countries" => isset($project['countries']) ? implode(', ', $project['countries']) : 'NA',
        "in-kind" => $project['in-kind'] ?? 'NA',
        "public" => $project['public'] ? lang("Yes", "Ja") : lang("No", "Nein"),
        "res:material" => ($res['material'] == 'yes' ? lang("Yes", "Ja") : lang("No", "Nein")),
        "res:material_details" => $res['material_details'] ?? 'NA',
        "res:personnel" => ($res['personnel'] == 'yes' ? lang("Yes", "Ja") : lang("No", "Nein")),
        "res:personnel_details" => $res['personnel_details'] ?? 'NA',
        "res:room" => ($res['room'] == 'yes' ? lang("Yes", "Ja") : lang("No", "Nein")),
        "res:room_details" => $res['room_details'] ?? 'NA',
        "res:other" => ($res['other'] == 'yes' ? lang("Yes", "Ja") : lang("No", "Nein")),
        "res:other_details" => $res['other_details'] ?? 'NA',
        "coordinator" => $project['coordinator'] ?? 'NA',
        "purpose" => $project['purpose'] ?? 'NA',
        "status" => $project['status'] ?? 'NA',
        "persons" => $persons,
        "website" => $project['website'] ?? 'NA',
    ];

    $templateProcessor->setValues($projectValues);
    // die;
    $tempFilePath = BASEPATH . '/uploads/output.docx';
    $templateProcessor->saveAs($tempFilePath);

    header("Content-Description: File Transfer");
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Length: ' . filesize($tempFilePath));
    header('Pragma: public');

    readfile($tempFilePath);

    // Lösche die Datei, falls sie nur temporär ist
    unlink($tempFilePath);
}, 'login');

/**
 * CRUD routes
 */



Route::post('/crud/(projects|proposals)/create', function ($collection) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    if (!isset($_POST['values'])) die("no values given");


    $values = validateValues($_POST['values'], $DB);
    if (!isset($values['type']) || !isset($values['name'])) {
        header("Location: " . ROOTPATH . "/$collection/new?msg=missing-parameters");
        die();
    }

    // check if project name already exists:
    // $project_exist = $osiris->$collection->findOne(['name' => $values['name']]);
    // if (!empty($project_exist)) {
    //     header("Location: " . ROOTPATH . "/$collection?msg=project ID does already exist.");
    //     die();
    // }
    // get project type
    $Project = new Project();
    $type = $Project->getProjectType($values['type']);

    // add information on creating process
    $values['created'] = date('Y-m-d');
    $values['end-delay'] = endOfCurrentQuarter(true);
    $values['created_by'] = $_SESSION['username'];

    // add persons
    $persons = [];
    foreach (['contact', 'scholar', 'supervisor'] as $key) {
        if (!isset($values[$key]) || empty($values[$key])) continue;
        $user = $values[$key];
        $persons[] = [
            'user' => $user,
            'role' => ($key == 'contact' ? 'applicant' : $key),
            'name' => $DB->getNameFromId($user),
            'units' => $Groups->getPersonUnit($user)
        ];
    }
    if (isset($values['applicants'])) foreach ($values['applicants'] as $user) {
        $persons[] = [
            'user' => $user,
            'role' => 'applicant',
            'name' => $DB->getNameFromId($user),
            'units' => $Groups->getPersonUnit($user)
        ];
    }
    if (!empty($persons)) {
        $values['persons'] = $persons;
    } else {
        // add current user as applicant
        $values['persons'] = [
            [
                'user' => $_SESSION['username'],
                'role' => 'applicant',
                'name' => $DB->getNameFromId($_SESSION['username'])
            ]
        ];
    }
    if (isset($values['parent_id'])) {
        $values['parent_id'] = $DB->to_ObjectID($values['parent_id']);
        $values['subproject'] = true;
        $parent = $osiris->projects->findOne(['_id' => $values['parent_id']]);
        if (!empty($parent)) {
            $values['parent'] = $parent['name'];
            // inherit persons
            $values['persons'] = $parent['persons'];
        } else {
            unset($values['parent_id']);
        }
        if (isset($values['proposal_id'])) {
            // shared proposal
            $values['proposal_id'] = $DB->to_ObjectID($values['proposal_id']);
        }
    } else if ($collection == 'proposals') {
        $values['status'] = 'proposed';
    } else {
        $values['status'] = 'project';
        if (isset($values['proposal_id'])) {
            // connect project to proposal
            $values['proposal_id'] = $DB->to_ObjectID($values['proposal_id']);
            $proposal = $osiris->proposals->findOne(['_id' => $values['proposal_id']]);
            if (!empty($proposal)) {
                $values['persons'] = $proposal['persons'];
                $values['_id'] = $values['proposal_id'];

                $osiris->proposals->updateOne(
                    ['_id' => $values['proposal_id']],
                    ['$set' => ['project_id' => $values['proposal_id']]]
                );
            } else {
                unset($values['proposal_id']);
            }
        }
    }

    if (isset($values['public'])) {
        $values['public'] = boolval($values['public']);
    }

    if (isset($values['nagoya']) && $collection == 'proposals') {
        $nagoya = ($values['nagoya'] == 'yes');
        $countries = [];
        foreach ($values['nagoya_countries'] as $iso) {
            $countries[] = [
                'id' => uniqid(),
                'code' => $iso,
                'abs' => null
            ];
        }
        $values['nagoya'] = [
            'enabled' => $nagoya,
            'countries' => $countries,
            'status' => (empty($countries) ? 'incomplete': 'abs-review')
        ];
    } else {
        $values['nagoya'] = [
            'enabled' => false,
            'countries' => []
        ];
    }

    if (isset($values['funding_organization']) && DB::is_ObjectID($values['funding_organization'])) {
        $values['funding_organization'] = $DB->to_ObjectID($values['funding_organization']);
    }
    if (isset($values['university']) && DB::is_ObjectID($values['university'])) {
        $values['university'] = $DB->to_ObjectID($values['university']);
    }

    include_once BASEPATH . "/php/Render.php";
    $values = renderProject($values);

    $insertOneResult  = $osiris->$collection->insertOne($values);
    $id = $insertOneResult->getInsertedId();

    if (isset($values['funding_number'])) {
        if (is_integer($values['funding_number'])) {
            $values['funding_number'] = strval($values['funding_number']);
        }
        $values['funding_number'] = explode(',', $values['funding_number']);
        $values['funding_number'] = array_map('trim', $values['funding_number']);

        // check if there are already activities with this funding number
        $osiris->activities->updateMany(
            ['funding' => ['$in' => $values['funding_number']]],
            ['$addToSet' => ['projects' => $id]]
        );
    }

    // check for notifications on create
    if (!empty($type['notification_created'] ?? null)) {
        $creator = ($USER['first'] ?? '') . " " . $USER['last'];
        if ($collection == 'projects') {
            $DB->addMessages(
                $type['notification_created'],
                'New project has been created by ' . $creator . ': <b>' . $values['name'] . '</b>',
                'Ein neues Projekt wurde erstellt von ' . $creator . ': <b>' . $values['name'] . '</b>',
                'project',
                "/$collection/view/" . $id,
            );
        } else {
            $DB->addMessages(
                $type['notification_created'],
                'New project proposal has been created by ' . $creator . ': <b>' . $values['name'] . '</b>',
                'Ein neuer Projektantrag wurde erstellt von ' . $creator . ': <b>' . $values['name'] . '</b>',
                'proposal',
                "/$collection/view/" . $id,
            );
        }

        if (!empty($type['notification_created_email'] ?? null)) {
            include_once BASEPATH . "/php/MailSender.php";
            $mails = $DB->getMessageGroup($type['notification_created'], 'mail');
            if (!empty($mails)) foreach ($mails as $mail) {
                if ($collection == 'projects') {
                    $subject = '[OSIRIS] Neues Projekt erstellt';
                    $title = 'Neues Projekt erstellt';
                    $linkText = 'Projekt anzeigen';
                } else {
                    $subject = '[OSIRIS] Neuer Projektantrag erstellt';
                    $title = 'Neuer Projektantrag erstellt';
                    $linkText = 'Projektantrag anzeigen';
                }
                $linkUrl = '/' . $collection . '/view/' . $id;
                $html = '
                <h3>Details:</h3>
                <ul>
                    <li><b>Kurztitel:</b> ' . $values['name'] . '</li>
                    <li><b>Voller Titel:</b> ' . $values['title'] . '</li>
                    <li><b>Erstellt von:</b> ' . $creator . '</li>
                    <li><b>Erstellt am:</b> ' . date('d.m.Y') . '</li>
                </ul>
                ';
                sendMail(
                    $mail,
                    $subject,
                    buildNotificationMail($title, $html, $linkText, $linkUrl)
                );
            }
        }
    }

    // send messages to applicants
    $applicants = $values['applicants'] ?? [];
    if (!empty($applicants)) {
        foreach ($applicants as $applicant) {
            if ($_SESSION['username'] == $applicant) continue; // do not send message to self
            $creator = ($USER['first'] ?? '') . " " . $USER['last'];
            $tag = $collection == 'projects' ? 'project' : 'proposal';
            $typeOfP = $collection == 'projects' ? lang('the project', 'das Projekt') : lang('the proposal', 'den Projektantrag');
            $DB->addMessage(
                $applicant,
                $creator . ' has created ' . $typeOfP . ' <b>' . $values['name'] . '</b> for which you are entered as applicant.',
                $creator . ' hat ' . $typeOfP . ' <b>' . $values['name'] . '</b> erstellt, bei dem du als Antragstellende Person angegeben wirst.',
                $tag,
                "/$collection/view/" . $id . '#section-history',
            );
        }
    }

    // send messages to nagoya
    if (isset($values['nagoya']) && $values['nagoya']['enabled'] == true) {
        $DB->addMessages(
            'right:nagoya.view',
            'A new project proposal with Nagoya protocol compliance has been created: <b>' . $values['name'] . '</b>',
            'Ein neuer Projektantrag mit Nagoya-Protokoll-Konformität wurde erstellt: <b>' . $values['name'] . '</b>',
            'nagoya',
            "/$collection/view/" . $id,
        );
    }

    // update parent project if subproject
    if (isset($values['parent_id'])) {
        $osiris->projects->updateOne(
            ['_id' => $values['parent_id']],
            ['$push' => ['subprojects' => $id]]
        );
    }

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $red = str_replace("*", $id, $_POST['redirect']);
        header("Location: " . $red . "?msg=success");
        die();
    }

    echo json_encode([
        'inserted' => $insertOneResult->getInsertedCount(),
        'id' => $id,
    ]);
});


Route::post('/crud/(proposals)/finance/([A-Za-z0-9]*)', function ($collection, $id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    if (!isset($_POST['values'])) die("no values given");

    /**
     * Combine values[grant_years] && values[grant_amounts] to associative array
     */
    $grant_years = $_POST['values']['grant_years'] ?? [];
    $grant_amounts = $_POST['values']['grant_amounts'] ?? [];

    $grant_years = array_map('intval', $grant_years);
    $grant_amounts = array_map('floatval', $grant_amounts);

    $grants = array_combine($grant_years, $grant_amounts);
    // sort by year (key)
    ksort($grants);

    $id = $DB->to_ObjectID($id);
    $updateResult = $osiris->$collection->updateOne(
        ['_id' => $id],
        ['$set' => [
            'grant_years' => $grants
        ]]
    );

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        header("Location: " . $_POST['redirect'] . "?msg=update-success");
        die();
    }
    echo json_encode([
        'inserted' => $updateResult->getModifiedCount(),
        'id' => $id,
    ]);
    die;
});

Route::post('/crud/(projects|proposals)/update/([A-Za-z0-9]*)', function ($collection, $id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    if (!isset($_POST['values'])) die("no values given");

    $project = $osiris->$collection->findOne(['_id' => $DB->to_ObjectID($id)]);
    if (empty($project)) {
        header("Location: " . ROOTPATH . "/$collection?msg=not-found");
        die;
    }

    $values = validateValues($_POST['values'], $DB);
    // add information on creating process
    $values['updated'] = date('Y-m-d');
    $values['updated_by'] = $_SESSION['username'];

    if (isset($values['public'])) {
        $values['public'] = boolval($values['public']);
    }

    if (isset($values['persons']) && !empty($values['persons'])) {
        $values['persons'] = array_values($values['persons']);
    }

    if (isset($values['funding_number'])) {
        if (is_integer($values['funding_number'])) {
            $values['funding_number'] = strval($values['funding_number']);
        }
        $values['funding_number'] = explode(',', $values['funding_number']);
        $values['funding_number'] = array_map('trim', $values['funding_number']);
    }

    if (isset($values['funding_organization']) && DB::is_ObjectID($values['funding_organization'])) {
        $values['funding_organization'] = $DB->to_ObjectID($values['funding_organization']);
    }
    if (isset($values['university']) && DB::is_ObjectID($values['university'])) {
        $values['university'] = $DB->to_ObjectID($values['university']);
    }
    if (isset($values['research-countries']) && is_array($values['research-countries'])) {
        $countries = [];
        foreach ($values['research-countries'] as $country) {
            $country = explode(';', $country, 2);
            $countries[] = [
                'iso' => $country[0],
                'role' => $country[1] ?? ''
            ];
        }
        $values['research-countries'] = $countries;
    }

    if (isset($values['abstract'])) {
        $abstract_en = $values['abstract'];
        $abstract_de = $values['abstract_de'] ?? $abstract_en;

        $values['teaser_en'] = get_preview($abstract_en);
        $values['teaser_de'] = get_preview($abstract_de);
    }

    // nagoya only for proposals and if it was disabled before
    if ($collection == 'proposals' && isset($values['nagoya'])) {
        if (!is_string($project['nagoya']) && isset($project['nagoya']['enabled']) && $project['nagoya']['enabled'] == true && !empty($project['nagoya']['countries'])) {
            // nagoya was already enabled before, do not change anything
            unset($values['nagoya']);
        } else {
            $nagoya = ($values['nagoya'] == 'yes');
            $countries = [];
            foreach ($values['nagoya_countries'] as $iso) {
                $countries[] = [
                    'id' => uniqid(),
                    'code' => $iso,
                    'abs' => null
                ];
            }
            $values['nagoya'] = [
                'enabled' => $nagoya,
                'countries' => $countries,
                'status' => (empty($countries) ? 'incomplete': 'abs-review')
            ];

            if ($nagoya && !empty($countries)) {
                // send notification to admins about nagoya being enabled
                $DB->addMessages(
                    'right:nagoya.view',
                    'Nagoya-relevant information has been shared for the following proposal: <b>' . $project['name'] . '</b>',
                    'Für folgenden Antrag wurden Nagoya-relevante Informationen hinzugefügt: <b>' . $project['name'] . '</b>',
                    'nagoya',
                    "/$collection/view/" . $id,
                );
            }
        }
    }

    $Project = new Project();
    $type = $Project->getProjectType($values['type']);

    // get history of project
    $values = $Project->updateHistory($values, $id, $collection);

    // check for notifications on create
    if (!empty($type['notification_changed'] ?? null)) {
        $creator = ($USER['first'] ?? '') . " " . $USER['last'];
        $tag = $collection == 'projects' ? 'project' : 'proposal';
        $DB->addMessages(
            $type['notification_changed'],
            'Project has been changed by ' . $creator . ': <b>' . $values['name'] . '</b>',
            'Projekt wurde geändert von ' . $creator . ': <b>' . $values['name'] . '</b>',
            $tag,
            "/$collection/view/" . $id . '#section-history',
        );

        if (!empty($type['notification_changed_email'] ?? null)) {
            include_once BASEPATH . "/php/MailSender.php";
            $mails = $DB->getMessageGroup($type['notification_changed'], 'mail');
            if (!empty($mails)) foreach ($mails as $mail) {
                if ($collection == 'projects') {
                    $subject = '[OSIRIS] Projekt bearbeitet';
                    $title = 'Projekt bearbeitet';
                    $linkText = 'Projekt anzeigen';
                } else {
                    $subject = '[OSIRIS] Projektantrag bearbeitet';
                    $title = 'Projektantrag bearbeitet';
                    $linkText = 'Projektantrag anzeigen';
                }
                $linkUrl = '/' . $collection . '/view/' . $id . '#section-history';
                $html = '
                <h3>Details:</h3>
                <ul>
                    <li><b>Kurztitel:</b> ' . $values['name'] . '</li>
                    <li><b>Voller Titel:</b> ' . $values['title'] . '</li>
                    <li><b>Bearbeitet von:</b> ' . $creator . '</li>
                    <li><b>Bearbeitet am:</b> ' . date('d.m.Y') . '</li>
                    <li><b>Status:</b> ' . ($values['status'] ?? 'proposed') . '</li>
                </ul>
                ';
                sendMail(
                    $mail,
                    $subject,
                    buildNotificationMail($title, $html, $linkText, $linkUrl)
                );
            }
        }
    }


    // send messages to applicants
    $applicants = $project['applicants'] ?? [];
    if (!empty($applicants)) {
        foreach ($applicants as $applicant) {
            if ($_SESSION['username'] == $applicant) continue; // do not send message to self
            $creator = ($USER['first'] ?? '') . " " . $USER['last'];
            $tag = $collection == 'projects' ? 'project' : 'proposal';
            $DB->addMessage(
                $applicant,
                'Your project <b>' . $values['name'] . '</b> has been changed by ' . $creator . '',
                'Dein Projekt <b>' . $values['name'] . '</b> wurde aktualisiert von ' . $creator . '',
                $tag,
                "/$collection/view/" . $id . '#section-history',
            );
        }
    }



    $id = $DB->to_ObjectID($id);
    include_once BASEPATH . "/php/Render.php";
    $values = renderProject($values, $id);

    $updateResult = $osiris->$collection->updateOne(
        ['_id' => $id],
        ['$set' => $values]
    );

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        header("Location: " . $_POST['redirect'] . "?msg=update-success");
        die();
    }

    echo json_encode([
        'inserted' => $updateResult->getModifiedCount(),
        'id' => $id,
    ]);
});


Route::post('/crud/(projects|proposals)/delete/([A-Za-z0-9]*)', function ($collection, $id) {
    include_once BASEPATH . "/php/init.php";

    $project = $osiris->$collection->findOne(['_id' => $DB->to_ObjectID($id)]);

    // check if user has permission to delete project
    $edit_perm = (
        $Settings->hasPermission($collection . '.delete')
        ||
        ($Settings->hasPermission($collection . '.delete-own') &&
            (
                $project['created_by'] == $_SESSION['username']
                ||
                in_array($_SESSION['username'], array_column(DB::doc2Arr($project['persons']), 'user'))
            ))
    );

    // if user has no permission: redirect to project view
    if (!$edit_perm) {
        header("Location: " . ROOTPATH . "/$collection/view/$id?msg=no-permission");
        die;
    }

    if ($collection == 'projects') {
        // remove project name from activities
        $osiris->activities->updateMany(
            ['projects' => $project['name']],
            ['$pull' => ['projects' => $project['name']]]
        );
    }

    if ($collection == 'proposals') {
        // check if a project with the same ID exists
        $existing_project = $osiris->projects->findOne(['_id' => $DB->to_ObjectID($id)]);
        if (!empty($existing_project)) {
            $_SESSION['msg'] = lang("Project proposal cannot be deleted, because it is connected to a project. Delete the project first.", "Projektantrag kann nicht gelöscht werden, da er mit einem Projekt verbunden ist. Lösche das Projekt zuerst.");
            header("Location: " . ROOTPATH . "/$collection/view/$id");
            die;
        }
    }

    // check if project has documents
    $documents = $osiris->uploads->find(['type' => $collection, 'id' => $id])->toArray();
    if (!empty($documents)) {
        // delete all documents
        foreach ($documents as $doc) {
            $osiris->uploads->deleteOne(['_id' => $doc['_id']]);
            // delete file from filesystem
            if (file_exists(BASEPATH . '/uploads/' . $doc['_id'] . '.' . $doc['extension'])) {
                unlink(BASEPATH . '/uploads/' . $doc['_id'] . '.' . $doc['extension']);
            }
        }
    }

    // remove project
    $osiris->$collection->deleteOne(
        ['_id' => $DB::to_ObjectID($id)]
    );

    $_SESSION['msg'] = lang("Element has been deleted successfully.", "Element wurde erfolgreich gelöscht.");
    header("Location: " . ROOTPATH . "/$collection");
});


Route::post('/crud/(projects|proposals)/update-persons/([A-Za-z0-9]*)', function ($collection, $id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    $values = $_POST['persons'];
    foreach ($values as $i => $p) {
        $values[$i]['name'] =  $DB->getNameFromId($p['user']);
    }
    // avoid object transformation
    $values = array_values($values);
    $values = ['persons' => $values];

    $id = $DB->to_ObjectID($id);
    include_once BASEPATH . "/php/Render.php";
    $values = renderProject($values, $collection, $id);


    $Project = new Project();
    $type = $Project->getProjectType($values['type'] ?? 'project');

    // get history of project
    $values = $Project->updateHistory($values, $id, $collection);

    // check for notifications on create
    if (!empty($type['notification_changed'] ?? null)) {
        $project = $osiris->$collection->findOne(['_id' => $id]);
        $creator = ($USER['first'] ?? '') . " " . $USER['last'];
        $tag = $collection == 'projects' ? 'project' : 'proposal';
        $DB->addMessages(
            $type['notification_changed'],
            'Persons of a project have been changed by ' . $creator . ': <b>' . $project['name'] . '</b>',
            'Die Personen im Projekt wurden geändert von ' . $creator . ': <b>' . $project['name'] . '</b>',
            $tag,
            "/$collection/view/" . $id . '#section-history',
        );


        if (!empty($type['notification_changed_email'] ?? null)) {
            include_once BASEPATH . "/php/MailSender.php";
            $mails = $DB->getMessageGroup($type['notification_changed'], 'mail');
            if (!empty($mails)) {
                if ($collection == 'projects') {
                    $subject = '[OSIRIS] Projekt bearbeitet';
                    $title = 'Personen des Projekts bearbeitet';
                    $linkText = 'Projekt anzeigen';
                } else {
                    $subject = '[OSIRIS] Projektantrag bearbeitet';
                    $title = 'Personen des Projektantrags bearbeitet';
                    $linkText = 'Projektantrag anzeigen';
                }
                $linkUrl = '/' . $collection . '/view/' . $id . '#section-history';
                $html = '
                <h3>Details:</h3>
                <ul>
                    <li><b>Kurztitel:</b> ' . $project['name'] . '</li>
                    <li><b>Voller Titel:</b> ' . $project['title'] . '</li>
                    <li><b>Bearbeitet von:</b> ' . $creator . '</li>
                    <li><b>Bearbeitet am:</b> ' . date('d.m.Y') . '</li>
                </ul>
                ';
                foreach ($mails as $mail) {
                    sendMail(
                        $mail,
                        $subject,
                        buildNotificationMail($title, $html, $linkText, $linkUrl)
                    );
                }
            }
        }
    }

    $osiris->$collection->updateOne(
        ['_id' => $DB::to_ObjectID($id)],
        ['$set' => $values]
    );

    header("Location: " . ROOTPATH . "/$collection/view/$id?msg=update-success");
});

Route::post('/crud/projects/update-collaborators/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $values = $_POST['values'];

    $collaborators = [];
    foreach ($values as $key => $values) {
        foreach ($values as $i => $val) {
            $collaborators[$i][$key] = $val;
        }
    }
    foreach ($collaborators as $i => $p) {
        // check if organisation already exists
        $coll_id = $osiris->organizations->findOne(['$or' => [
            ['name' => $p['name'], 'country' => $p['country']],
            ['ror' => $p['ror']]
        ]]);
        if (empty($coll_id)) {
            $new_org = $osiris->organizations->insertOne([
                'name' => $p['name'],
                'type' => $p['type'] ?? 'other',
                'location' => $p['location'] ?? null,
                'country' => $p['country'],
                'ror' => $p['ror'],
                'lat' => $p['lat'] ?? null,
                'lng' => $p['lng'] ?? null,
                'created_by' => $_SESSION['username'],
                'created' => date('Y-m-d')
            ]);
            $coll_id = $new_org->getInsertedId();
        } else {
            $coll_id = $coll_id['_id'];
        }
        $collaborators[$i]['organization'] = $coll_id;
    }

    $osiris->projects->updateOne(
        ['_id' => $DB::to_ObjectID($id)],
        ['$set' => ["collaborators" => $collaborators]]
    );

    header("Location: " . ROOTPATH . "/projects/view/$id?msg=update-success");
});


Route::post('/crud/projects/image/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $target_dir = BASEPATH . "/uploads/";
    if (!is_writable($target_dir)) {
        die("Upload directory $target_dir is unwritable. Please contact admin.");
    }
    $target_dir .= "projects/";
    if (isset($_FILES["file"]) && $_FILES["file"]["size"] > 0) {

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777);
        }
        // random filename
        $filename = $id . "." . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        // $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES["file"]["size"];

        if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
            $errorMsg = match ($_FILES['file']['error']) {
                1 => lang('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'Die hochgeladene Datei überschreitet die Richtlinie upload_max_filesize in php.ini'),
                2 => lang("File is too big: max 16 MB is allowed.", "Die Datei ist zu groß: maximal 16 MB sind erlaubt."),
                3 => lang('The uploaded file was only partially uploaded.', 'Die hochgeladene Datei wurde nur teilweise hochgeladen.'),
                4 => lang('No file was uploaded.', 'Es wurde keine Datei hochgeladen.'),
                6 => lang('Missing a temporary folder.', 'Der temporäre Ordner fehlt.'),
                7 => lang('Failed to write file to disk.', 'Datei konnte nicht auf die Festplatte geschrieben werden.'),
                8 => lang('A PHP extension stopped the file upload.', 'Eine PHP-Erweiterung hat den Datei-Upload gestoppt.'),
                default => lang('Something went wrong.', 'Etwas ist schiefgelaufen.') . " (" . $_FILES['file']['error'] . ")"
            };
            $_SESSION['msg'] = $errorMsg;
        } else if ($filesize > 16000000) {
            $_SESSION['msg'] = lang("File is too big: max 16 MB is allowed.", "Die Datei ist zu groß: maximal 16 MB sind erlaubt.");
        } else if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir . '/' . $filename)) {
            $_SESSION['msg'] = lang("The file $filename has been uploaded.", "Die Datei <q>$filename</q> wurde hochgeladen.");
            // update project with new image
            $osiris->projects->updateOne(
                ['_id' => $DB::to_ObjectID($id)],
                ['$set' => ["image" => "projects/" . $filename]]
            );
        } else {
            $_SESSION['msg'] = lang("Sorry, there was an error uploading your file.", "Entschuldigung, aber es gab einen Fehler beim Dateiupload.");
        }
    } else if (isset($_POST['delete'])) {
        $filename = $_POST['delete'];
        if (file_exists($target_dir . '/' . $filename)) {
            // Use unlink() function to delete a file
            if (!unlink($target_dir . '/' . $filename)) {
                $_SESSION['msg'] = lang("$filename cannot be deleted due to an error.", "$filename kann nicht gelöscht werden, da ein Fehler aufgetreten ist.");
            } else {
                $_SESSION['msg'] = lang("$filename has been deleted.", "$filename wurde gelöscht.");
            }
        }

        $osiris->projects->updateOne(
            ['_id' => $DB::to_ObjectID($id)],
            ['$set' => ["image" => null]]
        );
    } else {
        $_SESSION['msg'] = lang("No file was uploaded.", "Es wurde keine Datei hochgeladen.");
    }

    header("Location: " . ROOTPATH . "/projects/view/$id");
    die;
});


Route::post('/crud/projects/connect-activities', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['project']) || empty($_POST['project'])) {
        header("Location: " . $_POST['redirect'] . "?error=no-project-given");
        die;
    }
    if (!isset($_POST['activity']) || empty($_POST['activity'])) {
        header("Location: " . $_POST['redirect'] . "?error=no-activity-given");
        die;
    }

    $project = DB::to_ObjectID($_POST['project']);
    $activity = DB::to_ObjectID($_POST['activity']);

    if (isset($_POST['delete'])) {
        $osiris->activities->updateOne(
            ['_id' => $activity],
            ['$pull' => ["projects" => $project]]
        );
        header("Location: " . $_POST['redirect'] . "?msg=disconnected-activity-from-project#add-activity");
        die;
    }

    $osiris->activities->updateOne(
        ['_id' => $DB::to_ObjectID($activity)],
        ['$push' => ["projects" => $project]]
    );

    header("Location: " . $_POST['redirect'] . "?msg=connected-activity-to-project#add-activity");
    die;

    header("Location: " . ROOTPATH . "/activities/view/$id?msg=update-success");
});
