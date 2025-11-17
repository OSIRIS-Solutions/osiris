<?php

/**
 * Routes for OSIRIS Portal
 * Preview and API
 */


Route::get('/preview/(activity|person|profile|project|group)/(.*)', function ($type, $id) {
    if ($type == 'profile') {
        $type = 'person';
    }
    if ($type == 'group') {
        $type = 'unit';
    }
    // display correct breadcrumb
    switch ($type) {
        case 'activity':
            $breadcrumb = [
                ['name' => lang('Activities', "Aktivitäten"), 'path' => "/activities"],
                ['name' => $id, 'path' => "/activities/view/$id"],
            ];
            break;

        case 'person':
            $breadcrumb = [
                ['name' => lang('User', 'Personen'), 'path' => "/user/browse"],
                ['name' => $id, 'path' => "/profile/$id"],
            ];
            break;

        case 'project':
            $breadcrumb = [
                ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
                ['name' => $id, 'path' => "/projects/view/$id"],
            ];
            break;

        case 'unit':
            $breadcrumb = [
                ['name' => lang('Units', 'Einheiten'), 'path' => "/groups"],
                ['name' => $id, 'path' => "/groups/view/$id"],
            ];
            break;
        default:
            die('Unknown type: ' . $type);
            break;
    }
    $breadcrumb[] = ['name' => lang("Preview", "Vorschau")];

    // important: NO database connection
    include BASEPATH . "/header.php";

    // Call Portfolio API to get entity details
    $path = $_SERVER['HTTP_HOST'] . ROOTPATH . '/portfolio';
    $total_path = $path . '/' . urlencode($type) . '/' . urlencode($id);
    // important if https is used
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $total_path = 'https://' . $total_path;
    } else {
        $total_path = 'http://' . $total_path;
    }
    $ch = curl_init($total_path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (!$ch || curl_errno($ch)) {
        echo "<div class='container w-400 mw-full'>";
        echo "<div class='alert danger'>";
        echo "<h2 class='title'>" . lang("Error", "Fehler") . "</h2>";
        echo lang("Error fetching data: " . curl_error($ch), "Fehler beim Abrufen der Daten: " . curl_error($ch));
        echo "</div>";
        echo "</div>";
        include BASEPATH . "/footer.php";
        die;
    }
    curl_close($ch);

    $data = json_decode($response, true);
    $status = $data['status'] ?? 200;
    if (empty($data) || $status != 200) {
        echo "<div class='container w-400 mw-full'>";
        echo "<div class='alert danger'>";
        echo "<h2 class='title'>" . lang("Error", "Fehler") . "</h2>";
        echo lang("This dataset could not be found or is not publicly visible.", "Dieser Datensatz wurde nicht gefunden oder ist nicht öffentlich sichtbar.");
        echo "</div>";
        echo "</div>";
        include BASEPATH . "/footer.php";
        die;
    }
    $data = $data['data'] ?? null;
    include BASEPATH . "/addons/portal/$type.php";
    include BASEPATH . "/footer.php";
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
