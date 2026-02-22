<?php

/**
 * Routing file for organizational groups
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

Route::get('/groups', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang("Units", "Einheiten")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/groups/groups.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/groups/new', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang("Units", "Einheiten"), 'path' => "/groups"],
        ['name' => lang("New", "Neu")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/groups/add.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/groups/view/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $group = $osiris->groups->findOne(['_id' => $mongo_id]);
        $id = $group['id'];
    } else {
        // wichtig für umlaute
        $group = $osiris->groups->findOne(['id' => $id]);
        // $id = strval($group['_id'] ?? '');
    }
    if (empty($group)) {
        abortwith(404, lang("Unit", "Einheit"), '/groups');
    }
    $breadcrumb = [
        ['name' => lang("Units", "Einheiten"), 'path' => "/groups"],
        ['name' => $group['id']]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/groups/group.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/groups/(edit|public)/(.*)', function ($page, $id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $group = $osiris->groups->findOne(['_id' => $mongo_id]);
        $id = $group['id'];
    } else {
        // wichtig für umlaute
        $group = $osiris->groups->findOne(['id' => $id]);
        // $id = strval($group['_id'] ?? '');
    }
    if (empty($group)) {
        abortwith(404, lang("Unit", "Einheit"), '/groups');
    }
    $breadcrumb = [
        ['name' => lang("Units", "Einheiten"), 'path' => "/groups"],
        ['name' =>  $group['id'], 'path' => "/groups/view/$id"],
    ];
    if ($page == 'edit') {
        $breadcrumb[] = ['name' => lang("Edit", "Bearbeiten")];
    }

    global $form;
    $form = DB::doc2Arr($group);

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/groups/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::post('/crud/groups/create', function () {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['values'])) abortwith(500, lang('No values provided.', 'Keine Werte angegeben.'));
    $collection = $osiris->groups;

    $values = validateValues($_POST['values'], $DB);

    // check if group name already exists:
    $group_exist = $collection->findOne(['id' => $values['id']]);
    if (!empty($group_exist)) {
        $_SESSION['msg'] = lang("Group ID does already exist.", "Gruppen-ID existiert bereits.");
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/groups/new");
        die();
    }

    // add information on creating process
    $values['created'] = date('Y-m-d');
    $values['created_by'] = $_SESSION['username'];

    if (!empty($values['parent'])) {
        $parent = $Groups->getGroup($values['parent']);
        if ($parent['color'] != '#000000') $values['color'] = $parent['color'];
    }

    if (isset($values['head'])) {
        foreach ($values['head'] as $head) {
            $osiris->persons->updateOne(
                ['username' => $head],
                ['$push' => [
                    "units" => [
                        'id' => uniqid(),
                        'unit' => $values['id'],
                        'start' => date('Y-m-d'),
                        'end' => null,
                        'scientific' => true
                    ]
                ]]
            );
        }
    }

    if (!empty($values['parent'])) {
        $parent = $Groups->getGroup($values['parent']);
        if ($parent['color'] != '#000000') $values['color'] = $parent['color'];
        $values['level'] = $parent['level'] + 1;
    }

    $insertOneResult  = $collection->insertOne($values);
    $id = $insertOneResult->getInsertedId();

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $red = str_replace("*", $id, $_POST['redirect']);
        $_SESSION['msg'] = lang("Group created successfully.", "Gruppe erfolgreich erstellt.");
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $red);
        die();
    }

    echo json_encode([
        'inserted' => $insertOneResult->getInsertedCount(),
        'id' => $id,
    ]);
});

Route::post('/crud/groups/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['values'])) abortwith(500, lang('No values provided.', 'Keine Werte angegeben.'));

    $id = $DB->to_ObjectID($id);

    $group = $osiris->groups->findOne(['_id' => $id]);

    $values = validateValues($_POST['values'], $DB);
    // add information on creating process
    $values['updated'] = date('Y-m-d');
    $values['updated_by'] = $_SESSION['username'];

    // dump($values);
    // die;
    $id_changed = false;
    if (isset($values['hide'])) $values['hide'] = boolval($values['hide']);
    // check if ID has changes
    if (isset($values['id']) && $group['id'] != $values['id']) {
        $osiris->persons->updateMany(
            ["units.unit" => $group['id']],
            ['$set' => ["units.$.unit" => $values['id']]]
        );
        // change ID of child elements
        $osiris->groups->updateMany(
            ['parent' => $group['id']],
            ['$set' => ['parent' => $values['id']]]
        );
        $id_changed = true;
        // change top-level units: replace all occurrences of old id in units[]
        foreach (['activities', 'projects', 'proposals'] as $collection) {
            $osiris->$collection->updateMany(
                ['units' => $group['id']],
                ['$set' => ['units.$[u]' => $values['id']]],
                ['arrayFilters' => [['u' => $group['id']]]]
            );
            if ($collection == 'activities') {
                $keys = ['authors', 'editors', 'supervisors'];
            } else {
                $keys = ['persons'];
            }
            foreach ($keys as $key) {
                $osiris->$collection->updateMany(
                    [$key . '.units' => $group['id']],
                    ['$set' => [$key . '.$[a].units.$[u]' => $values['id']]],
                    ['arrayFilters' => [
                        ['a.units' => $group['id']],  // only authors where units contains oldId
                        ['u' => $group['id']]         // only replace matching unit entries
                    ]]
                );
            }
        }
    }

    if (isset($values['id'])) {
        // check if the right form is used
        if (!empty($values['parent'])) {
            $parent = $Groups->getGroup($values['parent']);
            $values['level'] = $parent['level'] + 1;
            if ($values['level'] == 1) {
                // spread color to all children
                $osiris->groups->updateMany(
                    ['parent' => $values['id']],
                    ['$set' => ['color' => $values['color']]]
                );
            } else {
                $values['color'] = $parent['color'] ?? '#000000';
            }
        } else {
            $values['level'] = 0;
        }
        if ($values['level'] != $group['level']) {
            // change level of all children
            $osiris->groups->updateMany(
                ['parent' => $values['id']],
                ['$set' => ['level' => $values['level'] + 1]]
            );
        }
    }

    if (isset($values['research'])) {
        if (!empty($values['research']) && is_array($values['research'])) {
            $values['research'] = array_values($values['research']);
        } else {
            $values['research'] = [];
        }
    }

    if (isset($values['synonyms'])) {
        if (!empty($values['synonyms'])) {
            $values['synonyms'] = array_map('trim', explode(';', $values['synonyms']));
            $values['synonyms'] = array_values($values['synonyms']);
        } else {
            $values['synonyms'] = null;
        }
    }


    // check if head is connected 
    if (isset($values['head'])) {
        foreach ($values['head'] as $head) {
            $N = $osiris->persons->count(['username' => $head, 'units.unit' => $values['id']]);
            if ($N == 0) {
                $osiris->persons->updateOne(
                    ['username' => $head],
                    ['$push' => [
                        "units" => [
                            'id' => uniqid(),
                            'unit' => $values['id'],
                            'start' => date('Y-m-d'),
                            'end' => null,
                            'scientific' => true
                        ]
                    ]]
                );
            }
        }
    }
    $updateResult = $osiris->groups->updateOne(
        ['_id' => $id],
        ['$set' => $values]
    );

    if ($id_changed) {
        include_once BASEPATH . "/php/Render.php";
        renderAuthorUnitsMany(['authors.units' => $group['id']]);
    }

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang("Unit updated successfully.", "Einheit erfolgreich aktualisiert.");
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $_POST['redirect']);
        die();
    }

    echo json_encode([
        'inserted' => $updateResult->getModifiedCount(),
        'id' => $id,
    ]);
});

Route::post('/crud/groups/delete/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    // select the right collection

    // prepare id
    $id = $DB->to_ObjectID($id);

    // remove from all users
    $group = $osiris->groups->findOne(['_id' => $id]);
    $osiris->persons->updateOne(
        ['units' => $group['id']],
        [
            '$pull' => ['units' => ['unit' => $group['id']]]
        ],
        ['multi' => true]
    );

    $updateResult = $osiris->groups->deleteOne(
        ['_id' => $id]
    );

    $deletedCount = $updateResult->getDeletedCount();

    // addUserActivity('delete');
    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang("Unit deleted successfully.", "Einheit erfolgreich gelöscht.");
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $_POST['redirect']);
        die();
    }
    echo json_encode([
        'deleted' => $deletedCount
    ]);
});


Route::post('/crud/groups/addperson/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['username'])) die("no username given");
    $user = $_POST['username'];

    $mode = $_POST['change-or-add'] ?? 'add';
    if ($mode == 'change' && isset($_POST['start'])) {
        // set end date of all other units with null date to one day before start date
        $osiris->persons->updateMany(
            ['username' => $user, 'units.end' => null],
            [
                '$set' => ['units.$[elem].end' => date('Y-m-d', strtotime($_POST['start'] . ' -1 day'))]
            ],
            ['arrayFilters' => [['elem.end' => null]]]
        );
    }
    // add id to person dept
    $osiris->persons->updateOne(
        ['username' => $user],
        [
            '$push' => ["units" => [
                'id' => uniqid(),
                'unit' => $id,
                'start' => $_POST['start'] ?? null,
                'end' => null,
                'scientific' => boolval($_POST['scientific'] ?? true)
            ]]
        ]
    );
    // update activities from the period the person was in the group
    include_once BASEPATH . "/php/Render.php";
    if (isset($_POST['start'])) {
        renderAuthorUnitsMany(['rendered.affiliated_users' => $user, 'date' => ['$gte' => $_POST['start']]]);
    } else {
        renderAuthorUnitsMany(['rendered.affiliated_users' => $user]);
    }

    $_SESSION['msg'] = lang("Person added successfully.", "Person erfolgreich hinzugefügt.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/groups/edit/$id#section-personnel");
});

Route::post('/crud/groups/removeperson/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    // add id to person dept
    $updateResult = $osiris->persons->updateOne(
        ['username' => $_POST['username']],
        ['$pull' => ["units" => ['unit' => $id]]]
    );

    // update activities from the period the person was in the group
    include_once BASEPATH . "/php/Render.php";
    renderAuthorUnitsMany(['authors.user' => $_POST['username']]);

    $_SESSION['msg'] = lang("Person removed successfully.", "Person erfolgreich entfernt.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/groups/edit/$id#section-personnel");
});


// delegate editing rights
Route::post('/crud/groups/editorperson/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['username'])) die("no username given");
    // add id to person dept
    $action = $_POST['action'] ?? 'add';
    $updateResult = $osiris->persons->updateOne(
        ['username' => $_POST['username']],
        // set units.editor to true where unit is the group id
        ['$set' => ["units.$[elem].editor" => ($action == 'add')]],
        [
            'arrayFilters' => [['elem.unit' => $id]]
        ]
    );

    $_SESSION['msg'] = lang("Editor rights updated successfully.", "Bearbeitungsrechte erfolgreich aktualisiert.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/groups/edit/$id#section-personnel");
});


Route::post('/crud/groups/reorder/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $order = $_POST['order'];
    $i = 0;
    foreach ($order as $o) {
        $osiris->groups->updateOne(
            ['_id' => $DB->to_ObjectID($o)],
            ['$set' => ['order' => $i]]
        );
        $i++;
    }

    $_SESSION['msg'] = lang("Group reordered successfully.", "Gruppe erfolgreich neu geordnet.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/groups/view/$id");
});
