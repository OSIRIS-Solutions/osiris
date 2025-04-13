<?php

/**
 * Routing file for the database migration
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


 Route::get('/migrate/countries', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Country.php";
    
    set_time_limit(6000);
    include BASEPATH . "/header.php";
    include_once BASEPATH . "/routes/migration/v1.5.0.php";
    include BASEPATH . "/footer.php";
});

Route::get('/migrate/test', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Groups.php";

    set_time_limit(6000);

    include_once BASEPATH . "/php/Project.php";
    $Project = new Project;
    // Drittmittel
    $osiris->adminProjects->deleteOne(['id' => 'third-party']);
    $osiris->adminProjects->insertOne([
        'id' => 'third-party',
        'icon' => 'hand-coins',
        'color' => '#B61F29',
        'name' => 'Third-party funding',
        'name_de' => 'Drittmittel',
        'phases' => [
            [
                'name' => 'Proposal',
                'name_de' => 'Antrag',
                'id' => 'proposed',
                'color' => 'signal',
                'modules' => [
                    'abstract',
                    'public',
                    // 'internal_number',
                    // 'website',
                    // 'grant_sum',
                    'funder',
                    // 'funding_number',
                    'grant_sum_proposed',
                    'personnel',
                    'ressources',
                    'contact',
                    'purpose',
                    'role',
                    'coordinator',
                    'nagoya',
                ],
                'disabled' => false,
                'portfolio' => false,
                'previous' => null,
            ],
            [
                'name' => 'Accepted',
                'name_de' => 'Angenommen',
                'id' => 'approved',
                'color' => 'success',
                'modules' => [
                    // 'abstract',
                    // 'public',
                    'internal_number',
                    'website',
                    'grant_sum',
                    // 'funder',
                    'funding_number',
                    // 'grant_sum_proposed',
                    // 'personnel',
                    // 'ressources',
                    // 'contact',
                    // 'purpose',
                    // 'role',
                    // 'coordinator',
                    'nagoya',
                    'topics'
                ],
                'disabled' => false,
                'portfolio' => true,
                'previous' => 'proposal',
            ]
        ],
        'topics' => true,
        'disabled' => false,
        'portfolio' => true,
        'has_subprojects' => true,
        'inherits' => [
            'status',
            'website',
            'grant_sum',
            'funder',
            'grant_sum_proposed',
            'purpose',
            'role',
            'coordinator',
        ]
    ]);
    $osiris->projects->updateMany(
        ['type' => 'Drittmittel'],
        ['$set' => ['type' => 'third-party']]
    );

    $osiris->adminProjects->deleteOne(['id' => 'stipendate']);
    $osiris->adminProjects->insertOne([
        'id' => 'stipendate',
        'icon' => 'tip-jar',
        'color' => '#63a308',
        'name' => 'Scholarship',
        'name_de' => 'Stipendium',
        'modules' => [
            'abstract',
            'public',
            'internal_number',
            'website',
            'grant_sum',
            'supervisor',
            'scholar',
            'scholarship',
            'university',
        ],
        'topics' => false,
        'disabled' => false,
        'portfolio' => true
    ]);
    $osiris->projects->updateMany(
        ['type' => 'Stipendium'],
        ['$set' => ['type' => 'stipendiate']]
    );

    $osiris->adminProjects->deleteOne(['id' => 'subproject']);
    $osiris->projects->updateMany(
        ['type' => ['$in' => ['Teilprojekt', 'subproject']]],
        ['$set' => ['type' => 'third-party', 'subproject' => true]]
    );

    $osiris->adminProjects->deleteOne(['id' => 'self-funded']);
    $osiris->adminProjects->insertOne([
        'id' => 'self-funded',
        'icon' => 'piggy-bank',
        'color' => '#ECAF00',
        'name' => 'Self-funded',
        'name_de' => 'Eigenfinanziert',
        'modules' => [
            'abstract',
            'public',
            'internal_number',
            'website',
            'personnel',
            'ressources',
            'contact',
        ],
        'topics' => false,
        'disabled' => false,
        'portfolio' => false
    ]);
    $osiris->projects->updateMany(
        ['type' => 'Eigenfinanziert'],
        ['$set' => ['type' => 'self-funded']]
    );

    echo "<p>Updated project types.</p>";

    // $cursor = $osiris->persons->find(['science_unit' => ['$exists' => false]]);

    // foreach ($cursor as $doc) {
    //     $depts = DB::doc2Arr($doc['depts'] ?? []);
    //     $science_unit = $depts[0] ?? null;
    //     dump($science_unit, true);
    //     $osiris->persons->updateOne(
    //         ['_id' => $doc['_id']],
    //         ['$set' => ['science_unit' => $science_unit]]
    //     );
    // }
    // $cursor = $osiris->activities->find(['units' => ['$exists' => false]]);
    // foreach ($cursor as $doc) {
    //     // calculate depts
    //     dump($doc, true);
    // }
    // $cursor = $osiris->projects->find(['start_date' => ['$exists' => false]]);
    // foreach ($cursor as $doc) {
    //     $osiris->projects->updateOne(
    //         ['_id' => $doc['_id']],
    //         ['$set' => ['start_date' => format_date($doc['start'] ?? '', 'Y-m-d'), 'end_date' => format_date($doc['end'] ?? '', 'Y-m-d')]]
    //     );
    // }
    // $filter = "$and":[{"authors.last":"Eberth"},{"authors.user":{"$ne":"seb14"}}];
    // $filter = ['authors.last' => 'Eberth', 'authors.user' => ['$ne' => 'seb14']];
    // $cursor = $osiris->activities->find($filter);
    // foreach ($cursor as $doc) {
    //     dump($doc, true);
    //     $osiris->activities->updateOne(
    //         ['_id' => $doc['_id']],
    //         ['$set' => ['authors.$[elem].user' => 'seb14']],
    //         ['arrayFilters' => [['elem.last' => 'Eberth']]]
    //     );
    // }
    // writeMail();


    // render teaser from abstract of publications
    // $cursor = $osiris->projects->find(['teaser' => ['$exists' => false]]);
    // foreach ($cursor as $doc) {
    //     $abstract_en = $doc['public_abstract'] ?? $doc['abstract'] ?? '';
    //     $abstract_de = $doc['public_abstract_de'] ?? $abstract_en;
    //     // $teaser_de = substr($doc['abstract'], 0, 200);
    //     // break at words or sentences
    //     $teaser_en = get_preview($abstract_en, 200);
    //     $teaser_de = get_preview($abstract_de, 200);

    //     dump($teaser_en, true);
    //     dump($teaser_de, true);

    //     if (empty($teaser_en) && empty($teaser_de)) continue;

    //     $osiris->projects->updateOne(
    //         ['_id' => $doc['_id']],
    //         ['$set' => ['teaser_en' => $teaser_en, 'teaser_de' => $teaser_de]]
    //     );
    // }

    // echo "Done";
});


Route::get('/install', function () {
    // include_once BASEPATH . "/php/init.php";
    unset($_SESSION['username']);
    $_SESSION['logged_in'] = false;

    include BASEPATH . "/header.php";

    include_once BASEPATH . "/php/DB.php";

    // Database connection
    global $DB;
    $DB = new DB;

    global $osiris;
    $osiris = $DB->db;

    echo "<h1>Willkommen bei OSIRIS</h1>";

    // check version
    $version = $osiris->system->findOne(['key' => 'version']);
    if (!empty($version) && !isset($_GET['force'])) {
        echo "<p>Es sieht so aus, als wäre OSIRIS bereits initialisiert. Falls du eine Neu-Initialisierung erzwingen möchtest, klicke bitte <a href='?force'>hier</a>.</p>";
        include BASEPATH . "/footer.php";
        die;
    }

    echo "<p>Ich initialisiere die Datenbank für dich und werde erst mal die Standardeinstellungen übernehmen. Du kannst alles Weitere später anpassen.</p>";

    $json = file_get_contents(BASEPATH . "/settings.default.json");
    $settings = json_decode($json, true, 512, JSON_NUMERIC_CHECK);
    $file_name = BASEPATH . "/settings.json";
    if (file_exists($file_name)) {
        echo "<p>Ich habe bereits vorhandene Einstellungen in <code>settings.json</code> gefunden. Ich werde versuchen, diese zu übernehmen.</p>";
        $json = file_get_contents($file_name);
        $set = json_decode($json, true, 512, JSON_NUMERIC_CHECK);
        // replace existing keys with new ones
        $settings = array_merge($settings, $set);
    }

    // echo "<h3>Generelle Einstellungen</h3>";
    $osiris->adminGeneral->deleteMany([]);
    $affiliation = $settings['affiliation'];
    $osiris->adminGeneral->insertOne([
        'key' => 'affiliation',
        'value' => $affiliation
    ]);

    $osiris->adminGeneral->insertOne([
        'key' => 'startyear',
        'value' => date('Y')
    ]);
    $roles = $settings['roles']['roles'];
    $osiris->adminGeneral->insertOne([
        'key' => 'roles',
        'value' => $roles
    ]);
    echo "<p>";
    echo "Ich habe die generellen Einstellungen vorgenommen. ";


    $json = file_get_contents(BASEPATH . "/roles.json");
    $rights = json_decode($json, true, 512, JSON_NUMERIC_CHECK);

    $osiris->adminRights->deleteMany([]);
    $rights = $settings['roles']['rights'];
    foreach ($rights as $right => $perm) {
        foreach ($roles as $n => $role) {
            $r = [
                'role' => $role,
                'right' => $right,
                'value' => $perm[$n]
            ];
            $osiris->adminRights->insertOne($r);
        }
    }
    echo "Ich habe Rechte und Rollen etabliert. ";

    // echo "<h3>Aktivitäten</h3>";
    $osiris->adminCategories->deleteMany([]);
    $osiris->adminTypes->deleteMany([]);
    foreach ($settings['activities'] as $type) {
        $t = $type['id'];
        $cat = [
            "id" => $type['id'],
            "icon" => $type['icon'],
            "color" => $type['color'],
            "name" => $type['name'],
            "name_de" => $type['name_de']
        ];
        $osiris->adminCategories->insertOne($cat);
        foreach ($type['subtypes'] as $s => $subtype) {
            $subtype['parent'] = $t;
            $osiris->adminTypes->insertOne($subtype);
        }
    }

    // set up indices
    $indexNames = $osiris->adminCategories->createIndexes([
        ['key' => ['id' => 1], 'unique' => true],
    ]);
    $indexNames = $osiris->adminTypes->createIndexes([
        ['key' => ['id' => 1], 'unique' => true],
    ]);

    echo "Ich habe die Standard-Aktivitäten hinzugefügt. ";


    // echo "<h3>Organisationseinheiten</h3>";
    $osiris->groups->deleteMany([]);

    // add institute as root level
    $dept = [
        'id' => $affiliation['id'] ?? 'INSTITUTE',
        'color' => '#000000',
        'name' => $affiliation['name'],
        'parent' => null,
        'level' => 0,
        'unit' => 'Institute',
    ];
    $osiris->groups->insertOne($dept);
    echo "Ich habe die Organisationseinheiten initialisiert, indem ich eine übergeordnete Einheit hinzugefügt habe. 
        Bitte bearbeite diese und füge weitere Einheiten hinzu. ";


    $json = file_get_contents(BASEPATH . "/achievements.json");
    $achievements = json_decode($json, true, 512, JSON_NUMERIC_CHECK);

    $osiris->achievements->deleteMany([]);
    $osiris->achievements->insertMany($achievements);
    $osiris->achievements->createIndexes([
        ['key' => ['id' => 1], 'unique' => true],
    ]);
    echo "Zu guter Letzt habe ich die Achievements initialisiert. ";

    echo "</p>";

    // last step: write Version number to database
    $osiris->system->deleteMany(['key' => 'version']);
    $osiris->system->insertOne(
        ['key' => 'version', 'value' => OSIRIS_VERSION]
    );

    echo "<h3>Fertig</h3>";
    echo "<p>
        Ich habe alle Einstellungen gespeichert und OSIRIS erfolgreich initialisiert.
        Am besten gehst du als nächstes zum <a href='" . ROOTPATH . "/admin/general'>Admin-Dashboard</a> und nimmst dort die wichtigsten Einstellungen vor.
    </p>";

    if (strtoupper(USER_MANAGEMENT) == 'AUTH') {
        echo '<b style="color:#e95709;">Wichtig:</b> Wie ich sehe benutzt du das Auth-Addon für die Nutzer-Verwaltung. Wenn du deinen Account anlegst, achte bitte darauf, dass der Nutzername mit dem vorkonfigurierten Admin-Namen (in <code>CONFIG.php</code>)  exakt übereinstimmt. Nur der vorkonfigurierte Admin kann die Ersteinstellung übernehmen und weiteren Personen diese Rolle übertragen.';
    }

    include BASEPATH . "/footer.php";
});

Route::get('/migrate', function () {
    set_time_limit(6000);

    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    echo "Please wait...<br>";

    $DBversion = $osiris->system->findOne(['key' => 'version']);

    // check if DB version is current version
    if (!empty($DBversion) && $DBversion['value'] == OSIRIS_VERSION) {
        echo "OSIRIS is already up to date. Nothing to do.";
        include BASEPATH . "/footer.php";
        die;
    }

    if (empty($DBversion)) {
        $DBversion = "1.0.0";
        $osiris->system->insertOne([
            'key' => 'version',
            'value' => $DBversion
        ]);
    } else {
        $DBversion = $DBversion['value'];
    }

    if (version_compare($DBversion, '1.2.0', '<')) {
        include BASEPATH . "/routes/migration/v1.2.0.php";
    }

    if (version_compare($DBversion, '1.2.1', '<')) {
        include BASEPATH . "/routes/migration/v1.2.1.php";
    }

    if (version_compare($DBversion, '1.3.0', '<')) {
        include BASEPATH . "/routes/migration/v1.3.0.php";
    }

    if (version_compare($DBversion, '1.3.3', '<')) {
        include BASEPATH . "/routes/migration/v1.3.3.php";
    }

    if (version_compare($DBversion, '1.3.4', '<')) {
        include BASEPATH . "/routes/migration/v1.3.4.php";
    }

    if (version_compare($DBversion, '1.3.6', '<')) {
        include BASEPATH . "/routes/migration/v1.3.6.php";
    }

    if (version_compare($DBversion, '1.3.7', '<')) {
        include BASEPATH . "/routes/migration/v1.3.7.php";
    }

    if (version_compare($DBversion, '1.3.8', '<')) {
        include BASEPATH . "/routes/migration/v1.3.8.php";
    }

    if (version_compare($DBversion, '1.4.0', '<')) {
        include BASEPATH . "/routes/migration/v1.4.0.php";
    }

    if (version_compare($DBversion, '1.4.1', '<')) {
        include BASEPATH . "/routes/migration/v1.4.1.php";
    }
    
    if (version_compare($DBversion, '1.4.2', '<')) {
        include BASEPATH . "/routes/migration/v1.4.2.php";
    }

    echo "<p>Rerender activities</p>";
    include_once BASEPATH . "/php/Render.php";
    renderActivities();

    echo '<p>Rerender projects</p>';
    renderAuthorUnitsProjects();

    echo "<p>Done.</p>";
    $osiris->system->updateOne(
        ['key' => 'version'],
        ['$set' => ['value' => OSIRIS_VERSION]],
        ['upsert' => true]
    );

    $osiris->system->updateOne(
        ['key' => 'last_update'],
        ['$set' => ['value' => date('Y-m-d')]],
        ['upsert' => true]
    );
    include BASEPATH . "/footer.php";
});


Route::post('/migrate/custom-fields-to-topics', function () {
    include_once BASEPATH . "/php/init.php";

    /**
     * 1. The selected custom field is used to create new research areas on this basis. Don\'t worry, you can still edit them later.
     * 2. All activities for which the custom field was completed are assigned to the respective research areas.
     * 3. The custom field is then deleted, i.e. the field itself, the assignment to forms and the values set for the activities are removed.
     */
    include BASEPATH . "/header.php";
    if (!isset($_POST['field'])) die('No field selected.');
    $field = $_POST['field'];

    // 1. 
    $fieldArr = $osiris->adminFields->findOne(['id' => $field]);
    if (empty($fieldArr) || empty($fieldArr['values'])) die('Field not found.');
    $values = $fieldArr['values'];

    $topics = [];
    foreach ($values as $value) {
        if ($value instanceof \MongoDB\BSON\Document) {
            $value = DB::doc2Arr($value);
        }
        // dump type of value
        if (is_array($value) || is_object($value)) {
            $de = $value[1] ?? $value[0];
            $en = $value[0];
        } else {
            $en = $value;
            $de = $value;
        }
        // add topic
        // generate random soft color
        $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        $id = str_replace(' ', '-', strtolower($en));

        $topic = [
            "id" => $id,
            "color" => $color,
            "name" => $en,
            "subtitle" => null,
            "name_de" => $de,
            "subtitle_de" => null,
            "description" => null,
            "description_de" => null,
            "created" => date('Y-m-d'),
            "created_by" => $_SESSION['username'],
        ];
        $osiris->topics->insertOne($topic);

        $topics[$en] = $id;
    }
    echo count($topics) . " topics created. Colors have been chosen randomly, you can edit them later if you have the permission to do so. <br>";

    // 2. All activities for which the custom field was completed are assigned to the respective research areas.
    $docs = $osiris->activities->find([$field => ['$exists' => true]], ['project' => [$field => 1]])->toArray();
    foreach ($docs as $doc) {
        $id = $doc['_id'];
        $value = $doc[$field];

        if (!array_key_exists($value, $topics)) {
            echo "Topic not found: $value <br>";
            continue;
        }
        $topic = $topics[$value];
        // dump($topic, true);
        $osiris->activities->updateOne(
            ['_id' => $id],
            ['$set' => ['topics' => [$topic]]]
        );
    }
    echo count($docs) . " activities has been assigned to topics. <br>";

    // 3. The custom field is then deleted, i.e. the field itself, the assignment to forms and the values set for the activities are removed.
    $osiris->adminFields->deleteOne(['id' => $field]);
    echo "The Custom Field was deleted. <br>";

    $res = $osiris->adminTypes->updateMany(
        ['modules' => $field],
        ['$pull' => ['modules' => $field]]
    );
    $N = $res->getModifiedCount();

    $res = $osiris->adminTypes->updateMany(
        ['modules' => $field . '*'],
        ['$pull' => ['modules' => $field . '*']]
    );
    $N += $res->getModifiedCount();
    echo "The field has been removed from $N activity forms. <br>";

    $res = $osiris->activities->updateMany(
        [$field => ['$exists' => true]],
        ['$unset' => [$field => '']]
    );
    echo "The field has been removed from " . $res->getModifiedCount() . " activities. <br>";

    include BASEPATH . "/footer.php";
});
