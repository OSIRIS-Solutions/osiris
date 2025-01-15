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


Route::get('/migrate/test', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Groups.php";



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
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    echo "Please wait...<br>";

    set_time_limit(6000);
    $DBversion = $osiris->system->findOne(['key' => 'version']);

    // check if DB version is current version
    // if (!empty($DBversion) && $DBversion['value'] == OSIRIS_VERSION) {
    //     echo "OSIRIS is already up to date. Nothing to do.";
    //     include BASEPATH . "/footer.php";
    //     die;
    // }

    if (empty($DBversion)) {
        $DBversion = "1.0.0";
        $osiris->system->insertOne([
            'key' => 'version',
            'value' => $DBversion
        ]);
    } else {
        $DBversion = $DBversion['value'];
    }

    // $V = explode('.', $version);

    if (version_compare($DBversion, '1.2.0', '<')) {
        echo "<h1>Migrate to Version 1.2.X</h1>";
        $osiris->teachings->drop();
        $osiris->miscs->drop();
        $osiris->posters->drop();
        $osiris->publications->drop();
        $osiris->lectures->drop();
        $osiris->reviews->drop();
        $osiris->lecture->drop();

        $users = $osiris->users->find([])->toArray();

        $person_keys = [
            "first",
            "last",
            "academic_title",
            "displayname",
            "formalname",
            "names",
            "first_abbr",
            "department",
            "unit",
            "telephone",
            "mail",
            "dept",
            "orcid",
            "gender",
            "google_scholar",
            "researchgate",
            "twitter",
            "webpage",
            "expertise",
            "updated",
            "updated_by",
        ];

        $account_keys = [
            "is_active",
            "maintenance",
            "hide_achievements",
            "hide_coins",
            "display_activities",
            "lastlogin",
            "created",
            "approved",
        ];

        $osiris->persons->deleteMany([]);
        $osiris->accounts->deleteMany([]);
        $osiris->achieved->deleteMany([]);

        foreach ($users as $user) {
            $user = iterator_to_array($user);
            $username = strtolower($user['username']);

            $person = ["username" => $username];
            foreach ($person_keys as $key) {
                if (!array_key_exists($key, $user)) continue;
                $person[$key] = $user[$key];
                unset($user[$key]);
            }
            $osiris->persons->insertOne($person);

            $account = ["username" => $username];
            foreach ($account_keys as $key) {
                if (!array_key_exists($key, $user)) continue;
                if ($key)
                    $account[$key] = $user[$key];
                unset($user[$key]);
            }
            $roles = [];
            foreach (['editor', 'admin', 'leader', 'controlling', 'scientist'] as $role) {
                if ($user['is_' . $role] ?? false) {
                    if ($role == 'controlling') $role = 'editor';
                    $roles[] = $role;
                }
            }
            $account['roles'] = $roles;

            $osiris->accounts->insertOne($account);

            if (isset($user['achievements'])) {
                foreach ($user['achievements'] as $ac) {
                    $ac['username'] = $username;
                    $osiris->achieved->insertOne($ac);
                }
                unset($user['achievements']);
            }
        }
        echo "Migrated " . count($users) . " users into a new format.<br> Migration successful. You might close this window now.";
    }

    // if ($V[1] < 2 || ($V[1] == 2 && $V[2] < 1)) {
    if (version_compare($DBversion, '1.2.1', '<')) {
        echo "<p>Migrating persons into new version.</p>";
        $migrated = 0;

        $accounts = $osiris->accounts->find([])->toArray();
        foreach ($accounts as $account) {
            $user = $account['username'];
            // check if user exists
            $person = $osiris->persons->findOne(['username' => $user]);
            if (empty($person)) {
                echo $user;
            } else {
                unset($account['_id']);
                $updated = $osiris->persons->updateOne(
                    ['username' => $user],
                    ['$set' => $account]
                );
                $migrated += $updated->getModifiedCount();
            }
        }

        echo "<p>Migrated $migrated users.</p>";
    }

    if (version_compare($DBversion, '1.3.0', '<')) {
        echo "<h1>Migrate to Version 1.3.X</h1>";

        $json = file_get_contents(BASEPATH . "/settings.default.json");
        $settings = json_decode($json, true, 512, JSON_NUMERIC_CHECK);
        // get custom settings
        $file_name = BASEPATH . "/settings.json";
        if (file_exists($file_name)) {
            $json = file_get_contents($file_name);
            $set = json_decode($json, true, 512, JSON_NUMERIC_CHECK);
            // replace existing keys with new ones
            $settings = array_merge($settings, $set);
        }
        // dump($settings, true);


        echo "<p>Update general settings</p>";
        $osiris->adminGeneral->deleteMany([]);

        $osiris->adminGeneral->insertOne([
            'key' => 'affiliation',
            'value' => $settings['affiliation']
        ]);

        $osiris->adminGeneral->insertOne([
            'key' => 'startyear',
            'value' => $settings['general']['startyear']
        ]);
        $roles = $settings['roles']['roles'];
        $osiris->adminGeneral->insertOne([
            'key' => 'roles',
            'value' => $roles
        ]);


        echo "<p>Update Features</p>";
        $osiris->adminFeatures->deleteMany([]);
        foreach (["coins", "achievements", "user-metrics"] as $key) {
            $osiris->adminFeatures->insertOne([
                'feature' => $key,
                'enabled' => boolval(!$settings['general']['disable-' . $key])
            ]);
        }


        echo "<p>Update Rights and Roles</p>";


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

        echo "<p>Update Activity schema</p>";
        $osiris->adminCategories->deleteMany([]);
        $osiris->adminTypes->deleteMany([]);
        foreach ($settings['activities'] as $type) {
            $t = $type['id'];
            $cat = [
                "id" => $type['id'],
                "icon" => $type['icon'],
                "color" => $type['color'],
                "name" => $type['name'],
                "name_de" => $type['name_de'],
                // "children" => $type['subtypes']
            ];
            $osiris->adminCategories->insertOne($cat);
            foreach ($type['subtypes'] as $s => $subtype) {
                $subtype['parent'] = $t;
                // dump($subtype, true);
                $osiris->adminTypes->insertOne($subtype);
            }
        }

        // set up indices
        $indexNames = $osiris->adminCategories->createIndexes([
            ['key' => ['id' => 1], 'unique' => true],
        ]);


        $osiris->groups->deleteMany([]);

        // add institute as root level
        $affiliation = $settings['affiliation'];
        $dept = [
            'id' => $affiliation['id'],
            'color' => '#000000',
            'name' => $affiliation['name'],
            'parent' => null,
            'level' => 0,
            'unit' => 'Institute',
        ];
        $osiris->groups->insertOne($dept);

        // add departments as children
        $depts = $settings['departments'];
        foreach ($depts as $dept) {
            if ($dept['id'] == 'BIDB') $dept['id'] = 'BID';
            $dept['parent'] = $affiliation['id'];
            $dept['level'] = 1;
            $dept['unit'] = 'Department';
            $osiris->groups->insertOne($dept);
        }

        // migrate person affiliation
        $persons = $osiris->persons->find([])->toArray();
        foreach ($persons as $person) {
            // dump($person, true);
            // $dept = [$affiliation['id']];
            $depts = [];
            if (isset($person['dept']) && !empty($person['dept'])) {
                if ($person['dept'] === 'BIDB') $person['dept'] = 'BID';
                $depts[] = $person['dept'];
            }
            dump($depts);
            // die;
            $updated = $osiris->persons->updateOne(
                ['_id' => $person['_id']],
                ['$set' => ['depts' => $depts]]
            );
        }
    }

    if (version_compare($DBversion, '1.3.3', '<')) {
        // migrate old documents, convert old history (created_by, edited_by) to new history format
        $cursor = $osiris->activities->find(['history' => ['$exists' => false], '$or' => [['created_by' => ['$exists' => true]], ['edited_by' => ['$exists' => true]]]]);
        foreach ($cursor as $doc) {
            if (isset($doc['history'])) continue;
            $id = $doc['_id'];
            $values = ['history' => []];
            if (isset($doc['created_by'])) {
                $values['history'][] = [
                    'date' => $doc['created'],
                    'user' => $doc['created_by'],
                    'type' => 'created',
                    'changes' => []
                ];
            }
            if (isset($doc['edited_by'])) {
                $values['history'][] = [
                    'date' => $doc['edited'],
                    'user' => $doc['edited_by'],
                    'type' => 'edited',
                    'changes' => []
                ];
            }

            // $values['history'][count($values['history']) - 1]['current'] = $doc['rendered']['print'] ?? 'unknown';

            $osiris->activities->updateOne(
                ['_id' => $id],
                ['$set' => $values]
            );
            // remove old fields
            $osiris->activities->updateOne(
                ['_id' => $id],
                ['$unset' => ['edited_by' => '', 'created' => '', 'edited' => '']]
            );
        }
    }
    if (version_compare($DBversion, '1.3.4', '<')) {
        $osiris->activities->createIndex(['rendered.plain' => 'text']);
    }

    if (version_compare($DBversion, '1.3.6', '<')) {
        $cursor = $osiris->activities->find(['subtype' => ['$exists' => false]]);
        foreach ($cursor as $doc) {
            $osiris->activities->updateOne(
                ['_id' => $doc['_id']],
                ['$set' => ['subtype' => $doc['pubtype'], 'history' => [
                    ['date' => date('Y-m-d'), 'type' => 'imported', 'user' => $_SESSION['username']]
                ]]]
            );
        }
    }

    // 
    if (version_compare($DBversion, '1.3.7', '<')) {
        echo "<p>Update descriptions and other things in markdown</p>";

        include(BASEPATH . '/php/MyParsedown.php');
        $parsedown = new Parsedown();

        // start with groups
        $cursor = $osiris->groups->find([]);
        foreach ($cursor as $group) {
            $result = [];
            foreach (['description', 'description_de'] as $key) {
                if (isset($group[$key]) && is_string($group[$key])) {
                    $result[$key] = $parsedown->text($group[$key]);
                }
            }


            if (isset($group['research'])) {
                $result['research'] = $group['research'];

                foreach ($group['research'] as $key => $value) {
                    if (!empty($value['info'] ?? ''))
                        $result['research'][$key]['info'] = $parsedown->text($value['info']);

                    if (!empty($value['info_de'] ?? ''))
                        $result['research'][$key]['info_de'] = $parsedown->text($value['info_de']);
                }
            }
            if (empty($result)) continue;
            $osiris->groups->updateOne(
                ['_id' => $group['_id']],
                ['$set' => $result]
            );
        }

        // then projects
        $cursor = $osiris->projects->find([]);
        foreach ($cursor as $project) {
            $result = [];
            foreach (['public_abstract', 'public_abstract_de'] as $key) {
                if (isset($project[$key]) && is_string($project[$key])) {
                    $result[$key] = $parsedown->text($project[$key]);
                }
            }
            if (empty($result)) continue;
            $osiris->projects->updateOne(
                ['_id' => $project['_id']],
                ['$set' => $result]
            );
        }
    }

    if (version_compare($DBversion, '1.3.8', '<')) {
        echo "<p>Update persons</p>";

        $cursor = $osiris->projects->find(['teaser' => ['$exists' => false]]);
        foreach ($cursor as $doc) {
            $abstract_en = $doc['public_abstract'] ?? $doc['abstract'] ?? '';
            $abstract_de = $doc['public_abstract_de'] ?? $abstract_en;
            // $teaser_de = substr($doc['abstract'], 0, 200);
            // break at words or sentences
            $teaser_en = get_preview($abstract_en, 200);
            $teaser_de = get_preview($abstract_de, 200);

            if (empty($teaser_en) && empty($teaser_de)) continue;

            $osiris->projects->updateOne(
                ['_id' => $doc['_id']],
                ['$set' => ['teaser_en' => $teaser_en, 'teaser_de' => $teaser_de]]
            );
        }
    }

    if (version_compare($DBversion, '1.4.0', '<')) {
        echo "<p>Migrating account settings if necessary</p>";

        // empty accounts 
        // $osiris->accounts->deleteMany([]);
        // use password hashes to encrypt passwords
        $cursor = $osiris->persons->find(['password' => ['$exists' => true]]);
        foreach ($cursor as $doc) {
            $hash = password_hash($doc['password'], PASSWORD_DEFAULT);
            // remove existing password
            $osiris->accounts->deleteOne(['username' => $doc['username']]);
            // move to a new collection
            $osiris->accounts->insertOne([
                'username' => $doc['username'],
                'password' => $hash
            ]);

            // remove password from persons
            $osiris->persons->updateOne(
                ['_id' => $doc['_id']],
                ['$unset' => ['password' => '']]
            );
        }

        $cursor = $osiris->projects->find(['start_date' => ['$exists' => false]]);
        foreach ($cursor as $doc) {
            $osiris->projects->updateOne(
                ['_id' => $doc['_id']],
                ['$set' => ['start_date' => format_date($doc['start'] ?? '', 'Y-m-d'), 'end_date' => format_date($doc['end'] ?? '', 'Y-m-d')]]
            );
        }
        echo "<p>Migrated project date time for better search.</p>";


        // migrate person socials
        $cursor = $osiris->persons->find(['socials' => ['$exists' => false]]);
        $available = [
            'twitter' => 'https://twitter.com/',
            'webpage' => '',
            'linkedin' => 'https://www.linkedin.com/in/',
            'researchgate' => 'https://www.researchgate.net/profile/'
        ];
        foreach ($cursor as $doc) {
            $socials = [];
            foreach ($available as $key => $url) {
                if ($key == 'twitter') {
                    $key = 'X';
                }
                if (isset($doc[$key])) {
                    $socials[] = [
                        'type' => $key,
                        'url' => $url . $doc[$key]
                    ];
                }
            }
            if (empty($socials)) continue;
            $osiris->persons->updateOne(
                ['_id' => $doc['_id']],
                ['$set' => ['socials' => $socials]]
            );
            $osiris->persons->updateOne(
                ['_id' => $doc['_id']],
                ['$unset' => ['twitter' => '', 'webpage' => '', 'linkedin' => '', 'researchgate' => '']]
            );
        }

        echo "<p>Migrated socials.</p>";



    // include_once BASEPATH . "/php/Project.php";
    // $Project = new Project;
    // // Drittmittel
    // $osiris->adminProjects->deleteOne(['id' => 'third-party']);
    // $osiris->adminProjects->insertOne([
    //     'id' => 'third-party',
    //     'icon' => 'hand-coins',
    //     'color' => '#B61F29',
    //     'name' => 'Third-party funding',
    //     'name_de' => 'Drittmittel',
    //     'modules' => [
    //         'abstract',
    //         'public',
    //         'internal_number',
    //         'website',
    //         'grant_sum',
    //         'funder',
    //         'funding_number',
    //         'grant_sum_proposed',
    //         'personnel',
    //         'ressources',
    //         'contact',
    //         'purpose',
    //         'role',
    //         'coordinator',
    //         'nagoya',
    //     ],
    //     'topics' => true,
    //     'disabled' => false,
    //     'portfolio' => true,
    //     'has_subprojects' => true,
    //     'inherits' => [
    //         'status',
    //         'website',
    //         'grant_sum',
    //         'funder',
    //         'grant_sum_proposed',
    //         'purpose',
    //         'role',
    //         'coordinator',
    //     ]
    // ]);
    // $osiris->projects->updateMany(
    //     ['type' => 'Drittmittel'],
    //     ['$set'=> ['type'=> 'third-party']]
    // );

    // $osiris->adminProjects->deleteOne(['id' => 'stipendate']);
    // $osiris->adminProjects->insertOne([
    //     'id' => 'stipendate',
    //     'icon' => 'tip-jar',
    //     'color' => '#63a308',
    //     'name' => 'Scholarship',
    //     'name_de' => 'Stipendium',
    //     'modules' => [
    //         'abstract',
    //         'public',
    //         'internal_number',
    //         'website',
    //         'grant_sum',
    //         'supervisor',
    //         'scholar',
    //         'scholarship',
    //         'university',
    //     ],
    //     'topics' => false,
    //     'disabled' => false,
    //     'portfolio' => true
    // ]);
    // $osiris->projects->updateMany(
    //     ['type' => 'Stipendium'],
    //     ['$set'=> ['type'=> 'stipendiate']]
    // );

    // $osiris->adminProjects->deleteOne(['id' => 'subproject']);
    // $osiris->projects->updateMany(
    //     ['type' => ['$in' => ['Teilprojekt', 'subproject']] ],
    //     ['$set'=> ['type'=> 'third-party', 'subproject' => true] ]
    // );

    // $osiris->adminProjects->deleteOne(['id' => 'self-funded']);
    // $osiris->adminProjects->insertOne([
    //     'id' => 'self-funded',
    //     'icon' => 'piggy-bank',
    //     'color' => '#ECAF00',
    //     'name' => 'Self-funded',
    //     'name_de' => 'Eigenfinanziert',
    //     'modules' => [
    //         'abstract',
    //         'public',
    //         'internal_number',
    //         'website',
    //         'personnel',
    //         'ressources',
    //         'contact',
    //     ],
    //     'topics' => false,
    //     'disabled' => false,
    //     'portfolio' => false
    // ]);
    // $osiris->projects->updateMany(
    //     ['type' => 'Eigenfinanziert'],
    //     ['$set'=> ['type'=> 'self-funded']]
    // );

    // echo "<p>Updated project types.</p>";
    }


    echo "<p>Rerender activities</p>";
    include_once BASEPATH . "/php/Render.php";
    renderActivities();

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
