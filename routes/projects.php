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


 Route::get('/proposals', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang("Project proposals", "Projektanträge")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/projects/proposals.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/proposals/new', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang("Project proposals", "Projektanträge"), 'path' => "/proposals"],
        ['name' => lang("New", "Neu")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/projects/proposal-edit.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/projects', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang("Projects", "Projekte")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/projects/projects.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/projects/new', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
        ['name' => lang("New", "Neu")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/projects/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/projects/search', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
        ['name' => lang("Search", "Suche")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/projects/search.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/projects/statistics', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
        ['name' => lang("Statistics", "Statistik")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/projects/statistics.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/projects/view/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $project = $osiris->projects->findOne(['_id' => $mongo_id]);
    } else {
        $project = $osiris->projects->findOne(['name' => $id]);
        $id = strval($project['_id'] ?? '');
    }
    if (empty($project)) {
        header("Location: " . ROOTPATH . "/projects?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
        ['name' => $project['name']]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/projects/project.php";
    include BASEPATH . "/footer.php";
}, 'login');


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

    $nagoya = $osiris->projects->find(
        ['nagoya' => 'yes']
    )->toArray();

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/nagoya.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/projects/(edit|collaborators|finance|public)/([a-zA-Z0-9]*)', function ($page, $id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    $mongo_id = $DB->to_ObjectID($id);
    $project = $osiris->projects->findOne(['_id' => $mongo_id]);
    if (empty($project)) {
        header("Location: " . ROOTPATH . "/projects?msg=not-found");
        die;
    }

    switch ($page) {
        case 'collaborators':
            $name = lang("Collaborators", "Kooperationspartner");
            break;
        case 'finance':
            $name = lang("Finance", "Finanzen");
            break;
        case 'public':
            $name = lang("Public representation", "Öffentliche Darstellung");
            break;
        default:
            $name = lang("Edit", "Bearbeiten");
    }

    $breadcrumb = [
        ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
        ['name' =>  $project['name'], 'path' => "/projects/view/$id"],
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
            include BASEPATH . "/pages/projects/finance.php";
            break;
        case 'public':
            include BASEPATH . "/pages/projects/public.php";
            break;
        default:
            include BASEPATH . "/pages/projects/edit.php";
    }
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/projects/subproject/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

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
    unset($form['title']);
    unset($form['ressources']);
    unset($form['personnel']);
    unset($form['in-kind']);
    unset($form['_id']);

    // add parent project
    $form['parent'] = $project['name'];
    $form['parent_id'] = strval($project['_id']);

    // set type to subproject
    $type = 'Teilprojekt';

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/projects/edit.php";
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
Route::post('/projects/download/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    error_reporting(E_ERROR | E_PARSE);
    // Lade das Template

    $user = $_SESSION['username'];
    $format = $_POST['format'] ?? 'word';

    $mongo_id = $DB->to_ObjectID($id);
    $project = $osiris->projects->findOne(['_id' => $mongo_id]);
    if (empty($project)) {
        header("Location: " . ROOTPATH . "/projects?msg=not-found");
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
    $projectValues = [
        "contact" => $DB->getNameFromId($project['contact']),
        "name" => $project['name'],
        "title" => $project['title'],
        "funder" => $project['funder'],
        "funding_organization" => $project['funding_organization'] ?? $project['funder'] ?? null,
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

Route::post('/crud/projects/create', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    if (!isset($_POST['values'])) die("no values given");
    $collection = $osiris->projects;

    $values = validateValues($_POST['values'], $DB);

    // check if project name already exists:
    $project_exist = $collection->findOne(['name' => $values['name']]);
    if (!empty($project_exist)) {
        header("Location: " . $red . "?msg=project ID does already exist.");
        die();
    }

    // add information on creating process
    $values['created'] = date('Y-m-d');
    $values['end-delay'] = endOfCurrentQuarter(true);
    $values['created_by'] = $_SESSION['username'];

    // add false checkbox values
    $values['public'] = boolval($values['public'] ?? false);

    // add persons
    $persons = [];
    foreach (['contact', 'scholar', 'supervisor'] as $key) {
        if (!isset($values[$key]) || empty($values[$key])) continue;
        $persons[] = [
            'user' => $values[$key],
            'role' => ($key == 'contact' ? 'applicant' : $key),
            'name' => $DB->getNameFromId($values[$key])
        ];
    }
    if (!empty($persons)) {
        $values['persons'] = $persons;
    }

    if (isset($values['funding_number'])) {
        $values['funding_number'] = explode(',', $values['funding_number']);
        $values['funding_number'] = array_map('trim', $values['funding_number']);

        // check if there are already activities with this funding number
        $osiris->activities->updateMany(
            ['funding' => ['$in' => $values['funding_number']]],
            ['$push' => ['projects' => $values['name']]]
        );
    }

    // check if type is Teilprojekt
    if (isset($values['parent_id'])) {
        // get parent project
        $parent = $osiris->projects->findOne(['_id' => $DB->to_ObjectID($values['parent_id'])]);

        // take over parent projects parameters
        if (!empty($parent)) {
            $values['type'] = 'Teilprojekt';
            $values['parent'] = $parent['name'];
            foreach (Project::INHERITANCE as $key) {
                if (isset($parent[$key])) {
                    $values[$key] = $parent[$key];
                }
            }
            // add project to parent project
            $osiris->projects->updateOne(
                ['_id' => $DB->to_ObjectID($values['parent_id'])],
                ['$push' => ['subprojects' => $values['name']]]
            );
        }
    }

    include_once BASEPATH . "/php/Render.php";
    $values = renderAuthorUnits($values, [], 'persons');

    $insertOneResult  = $collection->insertOne($values);
    $id = $insertOneResult->getInsertedId();

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


Route::post('/crud/projects/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['values'])) die("no values given");
    $collection = $osiris->projects;

    // $user_project = in_array($user, array_column(DB::doc2Arr($project['persons']), 'user'));
    // $edit_perm = ($Settings->hasPermission('projects.edit') || ($Settings->hasPermission('projects.edit-own') && $user_project));

    // if (!$edit_perm) {
    //     header("Location: " . ROOTPATH . "/projects/view/$id?msg=no-permission");
    //     die;
    // }

    $values = validateValues($_POST['values'], $DB);
    // add information on creating process
    $values['updated'] = date('Y-m-d');
    $values['updated_by'] = $_SESSION['username'];

    $values['public'] = boolval($values['public'] ?? false);

    if (isset($values['persons']) && !empty($values['persons'])) {
        $values['persons'] = array_values($values['persons']);
    }

    if (isset($values['funding_number'])) {
        $values['funding_number'] = explode(',', $values['funding_number']);
        $values['funding_number'] = array_map('trim', $values['funding_number']);
    }

    // update all children
    if ($osiris->projects->count(['parent_id' => $id]) > 0) {
        include_once BASEPATH . "/php/Project.php";
        $sub = [];
        foreach ($values as $key => $value) {
            if (in_array($key, Project::INHERITANCE)) {
                $sub[$key] = $value;
            }
        }
        $collection->updateMany(
            ['parent_id' => $id],
            ['$set' => $sub]
        );
    }

    include_once BASEPATH . "/php/Render.php";
    $values = renderAuthorUnits($values, [], 'persons');

    $id = $DB->to_ObjectID($id);
    $updateResult = $collection->updateOne(
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


Route::post('/crud/projects/delete/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $project = $osiris->projects->findOne(['_id' => $DB->to_ObjectID($id)]);

    // check if user has permission to delete project
    $edit_perm = (
        $Settings->hasPermission('projects.delete')
        ||
        ($Settings->hasPermission('projects.delete-own') &&
            (
                $project['created_by'] == $_SESSION['username']
                ||
                in_array($_SESSION['username'], array_column(DB::doc2Arr($project['persons']), 'user'))
            ))
    );

    // if user has no permission: redirect to project view
    if (!$edit_perm) {
        header("Location: " . ROOTPATH . "/projects/view/$id?msg=no-permission");
        die;
    }

    // remove project name from activities
    $osiris->activities->updateMany(
        ['projects' => $project['name']],
        ['$pull' => ['projects' => $project['name']]]
    );

    // remove project
    $osiris->projects->deleteOne(
        ['_id' => $DB::to_ObjectID($id)]
    );

    $_SESSION['msg'] = lang("Project has been deleted successfully.", "Projekt wurde erfolgreich gelöscht.");
    header("Location: " . ROOTPATH . "/projects");
});


Route::post('/crud/projects/update-persons/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $values = $_POST['persons'];
    foreach ($values as $i => $p) {
        $values[$i]['name'] =  $DB->getNameFromId($p['user']);
    }
    // avoid object transformation
    $values = array_values($values);

    $osiris->projects->updateOne(
        ['_id' => $DB::to_ObjectID($id)],
        ['$set' => ["persons" => $values]]
    );

    header("Location: " . ROOTPATH . "/projects/view/$id?msg=update-success");
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
        }
        $collaborators[$i]['organization'] = $coll_id;
    }

    $osiris->projects->updateOne(
        ['_id' => $DB::to_ObjectID($id)],
        ['$set' => ["collaborators" => $collaborators]]
    );

    header("Location: " . ROOTPATH . "/projects/view/$id?msg=update-success");
});


Route::post('/crud/projects/update-public/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $values = $_POST['values'];

    $values['public'] = boolval($values['public'] ?? false);

    foreach (['public_abstract', 'public_abstract_de', 'public_image', 'teaser_en', 'teaser_de', 'public_subtitle_de', 'public_title_de', 'public_subtitle', 'public_title'] as $key) {
        if (!isset($values[$key]) || empty($values[$key])) {
            $values[$key] = null;
        }
    }

    if (isset($values['public_abstract']) && !empty($values['public_abstract'])) {
        $abstract_en = $values['public_abstract'];
        $abstract_de = $values['public_abstract_de'] ?? $abstract_en;

        $values['teaser_en'] = get_preview($abstract_en);
        $values['teaser_de'] = get_preview($abstract_de);
    }

    // dump($values, true);
    // die;

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
        $values['public_image'] = "projects/" . $filename;

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
            printMsg($errorMsg, "error");
        } else if ($filesize > 16000000) {
            printMsg(lang("File is too big: max 16 MB is allowed.", "Die Datei ist zu groß: maximal 16 MB sind erlaubt."), "error");
        } else if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir . $filename)) {
            printMsg(lang("The file $filename has been uploaded.", "Die Datei <q>$filename</q> wurde hochgeladen."), "success");
        } else {
            $_SESSION['msg'] = lang("Sorry, there was an error uploading your file.", "Entschuldigung, aber es gab einen Fehler beim Dateiupload.");
        }
    } else if (isset($_POST['delete'])) {
        $filename = $_POST['delete'];
        if (file_exists($target_dir . $filename)) {
            // Use unlink() function to delete a file
            if (!unlink($target_dir . $filename)) {
                $_SESSION['msg'] = lang("$filename cannot be deleted due to an error.", "$filename kann nicht gelöscht werden, da ein Fehler aufgetreten ist.");
            } else {
                $_SESSION['msg'] = lang("$filename has been deleted.", "$filename wurde gelöscht.");
            }
        }

        $osiris->projects->updateOne(
            ['_id' => $DB::to_ObjectID($id)],
            ['$set' => ["public_image" => null]]
        );
        // printMsg("File has been deleted from the database.", "success");

        header("Location: " . ROOTPATH . "/projects/view/$id?msg=update-success");
        die();
    }

    $osiris->projects->updateOne(
        ['_id' => $DB::to_ObjectID($id)],
        ['$set' => $values]
    );

    header("Location: " . ROOTPATH . "/projects/view/$id?msg=update-success");
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

    $project = $_POST['project'];
    $activity = $_POST['activity'];

    if (isset($_POST['delete'])) {
        $osiris->activities->updateOne(
            ['_id' => $DB::to_ObjectID($activity)],
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
