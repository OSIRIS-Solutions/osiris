<?php

/**
 * Routes for OSIRIS Portal
 * Preview and API
 */

Route::get('/(preview|portal)/(activity|person|profile|project|group|infrastructure|topic)/(.*)', function ($section, $type, $id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Portfolio.php";
    if (! $Settings->featureEnabled('portal')) {
        abortwith(500, lang('The Portfolio feature is not enabled.', 'Die Portfolio-Funktion ist nicht aktiviert.'), "/");
    }
    $Portfolio = new Portfolio(true);
    if ($section == 'portal') {
        if (!$Settings->featureEnabled('portal-public')) {
            abortwith(500, lang('Public portal is disabled.', 'Öffentliches Portal ist deaktiviert.'), "/");
        }
        $base = ROOTPATH . '/portal';
        $Portfolio->setBasePath($base);
    } else {
        $base = $Portfolio->getBasePath();
    }
    if ($type == 'profile') {
        $type = 'person';
    }
    if ($type == 'group') {
        $type = 'unit';
    }

    // Call Portfolio API to get entity details
    $data = $Portfolio->fetch_entity($type, $id, '', lang('en', 'de'));
    // if ($type == 'unit') {
    //     // fetch additional numbers data
    //     $numbers = $Portfolio->fetch_entity('unit', $id, 'numbers', lang('en', 'de'));
    //     $data['numbers'] = $numbers;
    // }
    // display correct breadcrumb
    $breadcrumb = $Portfolio->getBreadCrumb($type, $data, $base, $section);

    // important: NO database connection
    if ($section == 'portal') {
        include BASEPATH . "/header-portal.php";
    } else {
        include BASEPATH . "/header.php";
    }
    if ($data === null) {
        echo "<div class='container w-400 mw-full'>";
        echo "<div class='alert danger'>";
        echo "<h2 class='title'>" . lang("Error", "Fehler") . "</h2>";
        echo lang("Error fetching data.", "Fehler beim Abrufen der Daten.");
        echo "</div>";
        echo "</div>";
        include BASEPATH . "/footer.php";
        die;
    }

    if (empty($data)) {
        echo "<div class='container w-400 mw-full'>";
        echo "<div class='alert danger'>";
        echo "<h2 class='title'>" . lang("Error", "Fehler") . "</h2>";
        echo lang("This dataset could not be found or is not publicly visible.", "Dieser Datensatz wurde nicht gefunden oder ist nicht öffentlich sichtbar.");
        echo "</div>";
        echo "</div>";
        include BASEPATH . "/footer.php";
        die;
    }
    // echo $Portfolio->renderBreadCrumb($type, $data, $base);
    include BASEPATH . "/addons/portal/$type.php";
    include BASEPATH . "/footer.php";
});



Route::get('/(preview|portal)/(info|activities|publications|persons|projects|groups|infrastructures|topics)', function ($section, $pagename) {
    include_once BASEPATH . "/php/init.php";
    if (! $Settings->featureEnabled('portal')) {
        abortwith(500, lang('The Portfolio feature is not enabled.', 'Die Portfolio-Funktion ist nicht aktiviert.'), "/");
    }
    if (!$Settings->featureEnabled('portal-public')) {
        abortwith(500, lang('Public Portfolio is disabled.', 'Öffentliches Portfolio ist deaktiviert.'), "/");
    }
    include_once BASEPATH . "/php/Portfolio.php";
    $Portfolio = new Portfolio(true);
    $base = ROOTPATH . '/portal';
    $Portfolio->setBasePath($base);
    if ($pagename == 'profiles') {
        $pagename = 'persons';
    }
    if ($pagename == 'groups') {
        $pagename = 'units';
    }
    // display correct breadcrumb
    global $breadcrumb;
    $breadcrumb = [
        ['name' => lang('Portal', 'Portal'), 'path' => "/$section/info"],
    ];
    switch ($pagename) {
        case 'activities':
            $breadcrumb[] = ['name' => lang('Activities', "Aktivitäten"), 'path' => "/$section/activities"];
            break;

        case 'publications':
            $breadcrumb[] = ['name' => lang('Publications', 'Publikationen'), 'path' => "/$section/publications"];
            break;

        case 'persons':
            $breadcrumb[] = ['name' => lang('User', 'Personen'), 'path' => "/$section/persons"];
            break;

        case 'projects':
            $breadcrumb[] = ['name' => lang('Projects', 'Projekte'), 'path' => "/$section/projects"];
            break;

        case 'units':
            $breadcrumb[] = ['name' => lang('Units', 'Einheiten'), 'path' => "/$section/groups"];
            break;

        case 'infrastructures':
            $breadcrumb[] = ['name' => lang('Infrastructures', 'Infrastrukturen'), 'path' => "/$section/infrastructures"];
            break;
    }

    // important: NO database connection
    if ($section == 'portal') {
        include BASEPATH . "/header-portal.php";
    } else {
        include BASEPATH . "/header.php";
    }

    // Call Portfolio API to get entity details
    $type = 'unit';
    $id = '0';
    $data = $Portfolio->fetch_entity($type, $id, '', lang('de', 'de'));
    if ($data === null) {
        echo "<div class='container w-400 mw-full'>";
        echo "<div class='alert danger'>";
        echo "<h2 class='title'>" . lang("Error", "Fehler") . "</h2>";
        echo lang("Error fetching data.", "Fehler beim Abrufen der Daten.");
        echo "</div>";
        echo "</div>";
        include BASEPATH . "/footer.php";
        die;
    }

    if (empty($data)) {
        echo "<div class='container w-400 mw-full'>";
        echo "<div class='alert danger'>";
        echo "<h2 class='title'>" . lang("Error", "Fehler") . "</h2>";
        echo lang("This dataset could not be found or is not publicly visible.", "Dieser Datensatz wurde nicht gefunden oder ist nicht öffentlich sichtbar.");
        echo "</div>";
        echo "</div>";
        include BASEPATH . "/footer.php";
        die;
    }
    // $numbers = $Portfolio->fetch_entity('unit', $id, 'numbers', 'de');
    // $data['numbers'] = $numbers;
    // echo $Portfolio->renderBreadCrumb($type, $data, $base);
    include BASEPATH . "/addons/portal/$type.php";
    include BASEPATH . "/footer.php";
    // navigate to correct tab
    if (!empty($pagename) && $pagename != 'info')
        echo "<script> document.addEventListener('DOMContentLoaded', function() { navigate('" . $pagename . "'); } ); </script>";
});



Route::get('/render/(activity|person|profile|project|group|unit|infrastructure|topic)/(.*)', function ($type, $id) {
    include BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Portfolio.php";
    $Portfolio = new Portfolio(false);

    $lang = $_GET['lang'] ?? 'en';
    $base = $_GET['base'] ?? $Portfolio->getBasePath();
    $view = $_GET['view'] ?? ''; // optional: publications/projects/...
    if ($type == 'profile') {
        $type = 'person';
    }
    if ($type == 'group') {
        $type = 'unit';
    }

    // 1) Fetch data from Portfolio API (same logic you already have)
    $data = $Portfolio->fetch_entity($type, $id, $view, $lang);
    // dump($data);
    if (!$data) {
        http_response_code(404);
        exit;
    }
    // if ($type == 'unit') {
    //     // fetch additional numbers data
    //     $numbers = $Portfolio->fetch_entity('unit', $id, 'numbers', $lang);
    //     $data['numbers'] = $numbers;
    // }
    // 3) Render content (no OSIRIS header/footer)
    ob_start();
    echo $Portfolio->renderBreadCrumb($type, $data, $base);
    include BASEPATH . "/addons/portal/{$type}.php"; // uses $data + url helper
    $content = ob_get_clean();

    header('Content-Type: text/html; charset=utf-8');
    echo $content;
});


Route::get('/portfolio-index', function () {

    require_once BASEPATH . '/php/Portfolio.php';

    $portfolio = new Portfolio(false);

    $pages = [];
    $now = date('c');

    /* ---------- Units (Groups) ---------- */

    $units = $portfolio->fetch_entity('units', '');
    if (is_array($units)) {
        foreach ($units as $unit) {
            $id = $unit['id'] ?? null;
            if (!$id) continue;

            $unitViews = [
                '' => '',
                'research' => 'research',
                'projects' => 'projects',
                'collaborators-map' => 'collaborators-map',
                'cooperation' => 'cooperation',
                'publications' => 'publications',
                'activities' => 'activities',
                // 'numbers' => 'numbers',
                'staff' => 'staff',
            ];

            foreach ($unitViews as $path => $view) {
                $pages[] = "/units/{$id}" . ($path ? "/{$path}/" : "/");
            }
        }
    }

    /* ---------- Persons ---------- */

    $persons = $portfolio->fetch_entity('persons', '');
    if (is_array($persons)) {
        foreach ($persons as $person) {
            $id = $person['_id']['$oid'] ?? null;
            if (!$id) continue;

            $personViews = [
                '' => '',
                'publications' => 'publications',
                'activities' => 'activities',
                'all-activities' => 'all-activities',
                'teaching' => 'teaching',
                'projects' => 'projects',
            ];

            foreach ($personViews as $path => $view) {
                $pages[] = "/people/{$id}" . ($path ? "/{$path}/" : "/");
            }
        }
    }

    /* ---------- Projects ---------- */

    $projects = $portfolio->fetch_entity('projects', '');
    if (is_array($projects)) {
        foreach ($projects as $project) {
            $id = $project['_id']['$oid'] ?? null;
            if (!$id) continue;

            $projectViews = [
                '' => '',
                'staff' => 'staff',
                'collaborators-map' => 'collaborators-map',
                'all-activities' => 'all-activities',
            ];

            foreach ($projectViews as $path => $view) {
                $pages[] = "/projects/{$id}" . ($path ? "/{$path}/" : "/");
            }
        }
    }

    /* ---------- Activities ---------- */

    $activities = $portfolio->fetch_entity('all-activities', '');
    if (is_array($activities)) {
        foreach ($activities as $activity) {
            $id = $activity['_id']['$oid'] ?? null;
            if (!$id) continue;

            $pages[] = "/activities/{$id}/";
        }
    }

    /* ---------- Response ---------- */

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'generated_at' => $now,
        'count' => count($pages),
        'pages' => $pages,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});


// Route::get('/preview/(activities|persons|projects|groups)', function ($type) {
//     // display correct breadcrumb
//     switch ($type) {
//         case 'activities':
//             $breadcrumb = [
//                 ['name' => lang('Activities', "Aktivitäten"), 'path' => "/activities"],
//             ];
//             break;

//         case 'persons':
//             $breadcrumb = [
//                 ['name' => lang('User', 'Personen'), 'path' => "/user/browse"],
//             ];
//             break;

//         case 'projects':
//             $breadcrumb = [
//                 ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
//             ];
//             break;

//         case 'groups':
//             $breadcrumb = [
//                 ['name' => lang('Units', 'Einheiten'), 'path' => "/groups"],
//             ];
//             break;
//         default:
//             # code...
//             break;
//     }
//     $breadcrumb[] = ['name' => lang("Preview", "Vorschau")];

//     // important: NO database connection
//     include BASEPATH . "/header.php";
//     include BASEPATH . "/addons/portal/preview.php";
//     include BASEPATH . "/footer.php";
// });


// Route::get('/portal/activity/(.*)', function ($id) {
//     include BASEPATH . "/php/init.php";
//     $id = ($id);
//     include_once BASEPATH . "/php/Modules.php";
//     $doc = $DB->getActivity($id);
//     if (empty($doc)) {
//         echo "Activity does not exist.";
//         die;
//     }
//     include BASEPATH . "/addons/portal/activity.php";
// });


// Route::get('/portal/group/(.*)', function ($id) {
//     include BASEPATH . "/php/init.php";
//     $id = ($id);
//     if (DB::is_ObjectID($id)) {
//         $mongo_id = $DB->to_ObjectID($id);
//         $group = $osiris->groups->findOne(['_id' => $mongo_id]);
//         $id = $group['id'];
//     } else {
//         $group = $osiris->groups->findOne(['id' => $id]);
//     }
//     if (empty($group)) {
//         echo "Group does not exist.";
//         die;
//     }
//     include BASEPATH . "/addons/portal/group.php";
// });

// Route::get('/portal/person/(.*)', function ($user) {
//     include BASEPATH . "/php/init.php";
//     $id = $user;
//     if (DB::is_ObjectID($user)) {
//         $mongo_id = $DB->to_ObjectID($user);
//         $scientist = $osiris->persons->findOne(['_id' => $mongo_id]);
//         $user = $scientist['username'];
//     } else {
//         $scientist = $DB->getPerson($user);
//         $id = strval($scientist['_id']);
//     }
//     if (empty($scientist)) {
//         echo "Person does not exist.";
//         die;
//     }
//     include BASEPATH . "/addons/portal/person.php";
// });

// Route::get('/portal/project/(.*)', function ($id) {
//     include BASEPATH . "/php/init.php";
//     $id = ($id);
//     $mongo_id = $DB->to_ObjectID($id);
//     $project = $osiris->projects->findOne(['_id' => $mongo_id]);
//     if (empty($project)) {
//         echo "Project does not exist.";
//         die;
//     }
//     if (!($project['public'] ?? true)) {
//         die('Project is private.');
//     }
//     include BASEPATH . "/addons/portal/project.php";
// });



// Route::get('/portal/activities', function () {
//     include BASEPATH . "/php/init.php";
//     include_once BASEPATH . "/php/Modules.php";

//     // $data = $osiris->activities->find(['type'=>['$in'=>$types]]);
//     // $data = DB::doc2Arr($data);
//     include BASEPATH . "/addons/portal/activities.php";
// });

// Route::get('/portal/groups', function () {
//     include BASEPATH . "/php/init.php";
//     include BASEPATH . "/addons/portal/groups.php";
// });

// Route::get('/portal/persons', function () {
//     include BASEPATH . "/php/init.php";
//     $data = $osiris->persons->find(['username' => ['$ne' => null], 'is_active' => ['$ne' => false]]);
//     $data = DB::doc2Arr($data);
//     include BASEPATH . "/addons/portal/persons.php";
// });

// Route::get('/portal/projects', function () {
//     include BASEPATH . "/php/init.php";
//     $data = $osiris->projects->find(['status' => 'approved', 'public' => true]);
//     $data = DB::doc2Arr($data);
//     include BASEPATH . "/addons/portal/projects.php";
// });
