<?php

/**
 * Routing file for users (tables, profiles, searches) and related stuff
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

Route::get('/user/browse', function () {
    // if ($page == 'users') 
    $breadcrumb = [
        ['name' => lang('Users', 'Personen')]
    ];
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/users-table.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/image/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";
    $user = urldecode($user);
    $img = $osiris->userImages->findOne(['user' => $user]);
    if (empty($img)) {
        $img = file_get_contents(BASEPATH . "/img/no-photo.png");
        $type = 'image/png';
    } else {
        $type = $img['ext'];
        if ($img['ext'] == 'svg') {
            $type = 'image/svg+xml';
        } else {
            $type = 'image/' . $img['ext'];
        }
        $img = $img['img']->getData();
        //if image is base64 encoded
        // if (str_starts_with($img, '/')) {
        //     $img = explode(',', $img)[1];
        // }

        $img = base64_decode($img);
    }
    header('Content-Type: ' . $type);
    echo $img;
    die;
});


Route::get('/whats-up', function () {
    $breadcrumb = [
        ['name' => lang('What\'s up?', 'Was ist los?')]
    ];
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/whats-up.php";
    include BASEPATH . "/footer.php";
});

/**
 * Editor routes
 */

Route::get('/user/edit/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Document.php";
    if (!$Settings->hasPermission('user.edit') && $user != $_SESSION['username']) {
        $_SESSION['msg'] = lang("You don't have permission to edit users.", "Du hast keine Berechtigung, Benutzer zu bearbeiten.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/profile/$user");
        die;
    }
    // $id = $DB->to_ObjectID($id);

    $data = $DB->getPerson($user);
    if (empty($data)) {
        header("Location: " . ROOTPATH . "/user/browse");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
        ['name' => $data['name'], 'path' => "/profile/$user"],
        ['name' => lang("Edit", "Bearbeiten")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/user-editor.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/user/units/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.edit') && $user != $_SESSION['username']) {
        $_SESSION['msg'] = lang("You don't have permission to edit users.", "Du hast keine Berechtigung, Benutzer zu bearbeiten.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/profile/$user");
        die;
    }
    $data = $DB->getPerson($user);
    if (empty($data)) {
        header("Location: " . ROOTPATH . "/user/browse");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
        ['name' => $data['name'], 'path' => "/profile/$user"],
        ['name' => lang("Edit units", "Einheiten bearbeiten")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/user-units.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/user/visibility/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";
    // include_once BASEPATH . "/php/Document.php";

    if (!$Settings->hasPermission('user.edit') && $user != $_SESSION['username']) {
        $_SESSION['msg'] = lang("You don't have permission to edit users.", "Du hast keine Berechtigung, Benutzer zu bearbeiten.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/profile/$user");
        die;
    }

    $data = $DB->getPerson($user);
    if (empty($data)) {
        header("Location: " . ROOTPATH . "/user/browse");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
        ['name' => $data['name'], 'path' => "/profile/$user"],
        ['name' => lang("Configure web view", "Webansicht Konfigurieren")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/user-webconfigure.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/user/inactivate/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.inactive')) {
        $_SESSION['msg'] = lang("You don't have permission to inactivate users.", "Du hast keine Berechtigung, Benutzer zu inaktivieren.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/profile/$user");
        die;
    }
    $data = $DB->getPerson($user);
    $data = DB::doc2Arr($data);
    if (empty($data)) {
        header("Location: " . ROOTPATH . "/user/browse");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
        ['name' => $data['name'], 'path' => "/profile/$user"],
        ['name' => lang("Inactivate", "Inaktivieren")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/user/inactivate.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/user/delete/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.delete')) {
        $_SESSION['msg'] = lang("You don't have permission to delete users.", "Du hast keine Berechtigung, Benutzer zu löschen.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/profile/$user");
        die;
    }

    $data = $DB->getPerson($user);
    $data = DB::doc2Arr($data);
    if (empty($data)) {
        header("Location: " . ROOTPATH . "/user/browse");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
        ['name' => $data['name'], 'path' => "/profile/$user"],
        ['name' => lang("Delete", "Löschen")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/user/delete.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/user/ldap-example', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/_login.php";

    $data = getUser($_SESSION['username']);
    $data = DB::doc2Arr($data);
    if (empty($data)) {
        header("Location: " . ROOTPATH . "/user/browse");
        die;
    }

    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
        ['name' => lang("LDAP Example", "LDAP Beispiel")]
    ];

    include BASEPATH . "/header.php";
    dump($data, true);
    include BASEPATH . "/footer.php";
}, 'login');


// Profile

Route::get('/profile/?(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";
    if (empty($user)) $user = $_SESSION['username'];
    if (!empty($user) && DB::to_ObjectID($user)) {
        $mongo_id = DB::to_ObjectID($user);
        $scientist = $osiris->persons->findOne(['_id' => $mongo_id]);
        $user = $scientist['username'];
    } else {
        $scientist = $DB->getPerson($user);
    }
    include_once BASEPATH . "/php/Document.php";
    include_once BASEPATH . "/php/_achievements.php";

    $Format = new Document($user);

    if (empty($scientist)) {
        $_SESSION['msg'] = lang("User not found.", "Benutzer nicht gefunden.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/user/browse");
        die;
    }
    $name = $scientist['displayname'];

    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
        ['name' => $name]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/profile.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/my-year/?(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";

    if (empty($user)) $user = $_SESSION['username'];
    include_once BASEPATH . "/php/Document.php";
    $Format = new Document($user);

    $scientist = $DB->getPerson($user);
    $name = $scientist['displayname'];

    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
        ['name' => lang("$name", "$name"), 'path' => "/profile/$user"],
        ['name' => lang("The Year", "Das Jahr")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/my-year.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/issues', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    $breadcrumb = [
        ['name' => lang('Issues', 'Warnungen')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/issues.php";
    include BASEPATH . "/footer.php";
});

Route::get('/messages', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    $breadcrumb = [
        ['name' => lang('Messages', 'Benachrichtigungen')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/messages.php";
    include BASEPATH . "/footer.php";
});

Route::get('/(expertise|keywords)', function ($collection) {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"]
    ];
    if ($collection == 'keywords') {
        $breadcrumb[] = ['name' => lang('Keywords', 'Schlagwörter')];
    } else if ($collection == 'expertise') {
        $breadcrumb[] = ['name' => lang('Expertise search', 'Experten-Suche')];
    }
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/expertise.php";
    include BASEPATH . "/footer.php";
});


Route::get('/achievements/?(.*)', function ($user) {
    if (empty($user)) $user = $_SESSION['username'];

    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/_achievements.php";

    $scientist = $DB->getPerson($user);
    $name = $scientist['displayname'];

    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
        ['name' => $name, 'path' => "/profile/$user"],
        ['name' => lang('Achievements', 'Errungenschaften')]

    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/achievements.php";
    include BASEPATH . "/footer.php";
});


// not in use
Route::get('/user/picture/(.*)', function ($user, $cls = 'profile-img') {
    include_once BASEPATH . "/php/init.php";
    $default = '<img src="' . ROOTPATH . '/img/no-photo.png" alt="Profilbild" class="' . $cls . '">';
    if ($Settings->featureEnabled('db_pictures')) {
        $img = $osiris->userImages->findOne(['user' => $user]);

        image_type_to_mime_type($img['ext']);
        if (empty($img)) {
            echo $default;
            return;
        }
        if ($img['ext'] == 'svg') {
            $img['ext'] = 'svg+xml';
        }
        echo '<img src="data:image/' . $img['ext'] . ';base64,' . base64_encode($img['img']) . ' " class="' . $cls . '" />';
        return;
    } else {
        $img_exist = file_exists(BASEPATH . "/img/users/$user.jpg");
        if (!$img_exist) {
            echo $default;
            return;
        }
        $img = ROOTPATH . "/img/users/$user.jpg";
        echo ' <img src="' . $img . '" alt="Profilbild" class="' . $cls . '">';
    }
});

// Synchronize users

Route::get('/synchronize-users', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/_login.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/synchronize-users.php";
    include BASEPATH . "/footer.php";
});

Route::post('/synchronize-users', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.synchronize')) {
        echo "<p>Permission denied.</p>";
        die();
    }
    include_once BASEPATH . "/php/_login.php";
    include BASEPATH . "/header.php";

    if (isset($_POST['inactivate'])) {
        $keep = [
            '_id',
            'displayname',
            'formalname',
            'first_abbr',
            'updated',
            'updated_by',
            "academic_title",
            "first",
            "last",
            "name",
            "orcid",
            // "depts",
            "units",
            "username",
            "created",
            "created_by",
            'uniqueid',
        ];
        foreach ($_POST['inactivate'] as $username) {
            $data = $DB->getPerson($username);
            $name = $data['displayname'] ?? $username;
            $arr = [];
            foreach ($data as $key => $value) {
                if (in_array($key, $keep)) continue;
                $arr[$key] = null;
            }
            $arr['is_active'] = false;
            $arr['inactivated'] = date('Y-m-d');
            $osiris->persons->updateOne(
                ['username' => $username],
                ['$set' => $arr]
            );
            if (file_exists(BASEPATH . "/img/users/$username.jpg")) {
                unlink(BASEPATH . "/img/users/$username.jpg");
            }
            echo "<p><i class='ph ph-user-minus text-danger'></i> $name ($username) inactivated and personal data deleted.</p>";
        }
    }


    if (isset($_POST['reactivate'])) {
        foreach ($_POST['reactivate'] as $username) {

            $osiris->persons->updateOne(
                ['username' => $username],
                ['$set' => ['is_active' => ['$ne' => false]]]
            );
            echo "<p><i class='ph ph-user-check text-danger'></i> $name ($username) reactivated.</p>";
        }
    }


    if (isset($_POST['add'])) {
        foreach ($_POST['add'] as $username) {
            // check if user exists
            $USER = $DB->getPerson($username);
            if (!empty($USER)) {
                echo "<p><i class='ph ph-warning text-warning'></i> $username already exists.</p>";
                continue;
            }
            $new_user = newUser($username);
            if (empty($new_user)) {
                echo "<p><i class='ph ph-warning text-danger'></i> $username did not exist.</p>";
                continue;
            }
            $osiris->persons->insertOne($new_user);
            echo "<p><i class='ph ph-user-plus text-success'></i> New user created: <a href='" . ROOTPATH . "/profile/$new_user[username]' target='_blank'> $new_user[displayname]</a> ($new_user[username])</p>";
        }
    }
    if (isset($_POST['blacklist'])) {
        $bl = $Settings->get('ldap-sync-blacklist');
        if (!empty($bl)) {
            $bl = explode(',', $bl);
            $blacklist = array_filter(array_map('trim', $bl));
        } else {
            $blacklist = [];
        }
        foreach ($_POST['blacklist'] as $username) {
            $blacklist[] = $username;
        }
        $Settings->set('ldap-sync-blacklist', implode(',', $blacklist));
        echo "<p>Blacklist updated.</p>";
    }

    echo "User synchronization successful";
    include BASEPATH . "/footer.php";
});


Route::post('/synchronize-attributes', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/_login.php";

    if (!$Settings->hasPermission('user.synchronize')) {
        echo "<p>Permission denied.</p>";
        die();
    }

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/synchronize-attributes-preview.php";
    include BASEPATH . "/footer.php";
});


Route::post('/synchronize-attributes-now', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . '/php/LDAPInterface.php';

    if (!$Settings->hasPermission('user.synchronize')) {
        echo "<p>Permission denied.</p>";
        die();
    }

    include BASEPATH . "/header.php";
    $settings = $osiris->adminGeneral->findOne(['key' => 'ldap_mappings']);
    $ldapMappings = DB::doc2Arr($settings['value'] ?? []);

    if (empty($ldapMappings)) {
        echo "No LDAP mappings found.\n";
        exit;
    }
    $LDAP = new LDAPInterface();
    $success = $LDAP->synchronizeAttributes($ldapMappings, $osiris);
    if ($success) {
        echo "User attributes synchronized successfully.\n";

        $osiris->system->updateOne(
            ['key' => 'ldap-sync'],
            ['$set' => ['value' => date('Y-m-d H:i:s')]],
            ['upsert' => true]
        );
    } else {
        echo "Failed to synchronize user attributes.\n";
    }
    include BASEPATH . "/footer.php";
});


Route::post('/switch-user', function () {

    if (isset($_POST['OSIRIS-SELECT-MAINTENANCE-USER'])) {
        // someone tries to switch users
        include_once BASEPATH . "/php/init.php";
        $realusername = ($_SESSION['realuser'] ?? $_SESSION['username']);
        $username = ($_POST['OSIRIS-SELECT-MAINTENANCE-USER']);

        // check if the user is allowed to do that
        $allowed = $osiris->persons->count(['username' => $username, 'maintenance' => $realusername]);
        // change username if user is allowed
        if ($allowed == 1 || $realusername == $username) {
            $_SESSION['msg'] = lang("You are now logged in as", "Du bist jetzt angemeldet als") . " " . $DB->getNameFromId($username);
            $_SESSION['msg_type'] = "info";
            $_SESSION['realuser'] = $realusername;
            $_SESSION['username'] = $username;
            // reset notifications
            $_SESSION['last_notification_check'] = 0;
            header("Location: " . ROOTPATH . "/profile/$username");
            die;
        }

        // do nothing if user is not allowed
        $_SESSION['msg'] = lang("You are not allowed to switch to this user.", "Du darfst dich nicht als diesen Benutzer anmelden.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/profile/" . $_SESSION['username']);
    }
});

/** 
 * CRUD routes
 */

Route::post('/crud/users/update/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['values'])) abortwith(500, lang('No values provided.', 'Keine Werte angegeben.'));
    if (!$Settings->hasPermission('user.edit') && $user != $_SESSION['username']) {
        $_SESSION['msg'] = lang("You don't have permission to edit users.", "Du hast keine Berechtigung, Benutzer zu bearbeiten.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/profile/$user");
        die;
    }

    $values = $_POST['values'];
    $values = validateValues($values, $DB);
    // separate personal and account information
    $person = $values;
    // $account = [];
    // get old value for rendering
    $old = $DB->getPerson($user);

    // update name information
    if (isset($values['last']) && isset($values['first'])) {

        $person['displayname'] = "$values[first] $values[last]";
        $person['formalname'] = "$values[last], $values[first]";
        $person['first_abbr'] = "";
        foreach (explode(" ", $values['first']) as $name) {
            $person['first_abbr'] .= " " . $name[0] . ".";
        }

        // only update public visibility if complete form (user edit) is submitted
        // name is indicating that
        foreach (["public_image", "public_email", "public_phone", "hide"] as $key) {
            $person[$key] = boolval($values[$key] ?? false);
        }
        require_once BASEPATH . "/php/Render.php";
        $complete_person = array_merge($old, $person);
        $person['search_text'] = build_person_search_text($complete_person);
    }

    $person['updated'] = date('Y-m-d');
    $person['updated_by'] = $_SESSION['username'];

    if (isset($values['cv'])) {
        $cv = $values['cv'];
        foreach ($values['cv'] as $key => $entry) {
            // add time text to entry
            $fromto = $entry['from']['month'] . '/' . $entry['from']['year'];
            $fromto .= " - ";
            if (empty($entry['to']['year'])) {
                $fromto .= "Current";
            } else {
                if (!empty($entry['to']['month'])) {
                    $fromto .= $entry['to']['month'] . '/';
                }
                $fromto .= $entry['to']['year'];
            }
            $cv[$key]['time'] = $fromto;
        }
        // sort cv descending
        usort($cv, function ($a, $b) {
            $a = $a['from']['year'] . '.' . $a['from']['month'];
            $b = $b['from']['year'] . '.' . $b['from']['month'];
            return strnatcmp($b, $a);
        });
        $person['cv'] = $cv;
    }

    if (isset($values['is_active'])) {
        $person['is_active'] = boolval($values['is_active']);
    }

    if (isset($values['roles'])) {
        // remove empty roles
        $person['roles'] = array_filter($values['roles'], function ($role) {
            return !empty($role);
        });
        $person['roles'] = array_values($person['roles']);
    }

    // if new password is set, update password
    if (isset($_POST['password']) && !empty($_POST['password']) && $user == $_SESSION['username']) {
        // check if old password matches
        $account = $osiris->accounts->findOne(['username' => $user]);
        if (!empty($account['password'] ?? null) && !password_verify($_POST['old_password'], $account['password'])) {
            $_SESSION['msg'] = lang("Old password is incorrect.", "Vorheriges Passwort ist falsch.");
        } else if ($_POST['password'] != $_POST['password2']) {
            $_SESSION['msg'] = lang("Passwords do not match.", "Passwörter stimmen nicht überein.");
        } else {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $osiris->accounts->deleteOne(['username' => $user]);
            $osiris->accounts->insertOne([
                'username' => $user,
                'password' => $password
            ]);
            $osiris->persons->updateOne(
                ['username' => $user],
                ['$unset' => ['new' => '']]
            );
        }
    }
    if (isset($values['position_both'])) {
        $pos = explode(";;", $values['position_both']);
        $person['position'] = $pos[0];
        $person['position_de'] = trim($pos[1] ?? '');
        if (empty($person['position_de'])) {
            $person['position_de'] = null;
        }
    }

    $updateResult = $osiris->persons->updateOne(
        ['username' => $user],
        ['$set' => $person]
    );

    if (isset($person['hide'])) {
        // check if hide value changed

        if ($old['hide'] != $person['hide']) {
            // rerender all activities
            include_once BASEPATH . "/php/Render.php";
            renderActivities(['rendered.users' => $user]);
        }
    }

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang("User updated successfully.", "Benutzer erfolgreich aktualisiert.");
        $_SESSION['msg_type'] = "success";
        header("Location: " . $_POST['redirect']);
        die();
    }
    echo json_encode([
        'updated' => $updateResult->getModifiedCount()
    ]);
});


Route::post('/crud/users/units/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Render.php";
    if (!$Settings->hasPermission('user.edit') && $user != $_SESSION['username']) {
        abortwith(403, lang("You don't have permission to edit users.", "Du hast keine Berechtigung, Benutzer zu bearbeiten."));
    }

    if (!isset($_POST['values']) && isset($_POST['id'])) {
        // first get the unit that should be deleted
        $unit = $osiris->persons->findOne(
            ['username' => $user, 'units.id' => $_POST['id']],
            ['projection' => ['units.$' => 1]]
        );
        if (empty($unit)) {
            abortwith(404, lang("Unit", "Einheit"), "/user/units/$user");
        }
        $unit = $unit['units'][0];

        // delete unit
        $osiris->persons->updateOne(
            ['username' => $user],
            ['$pull' => ['units' => ['id' => $_POST['id']]]]
        );

        // update all activities that have this user as author
        if ($unit['scientific']) {
            // only necessary if unit is scientific
            $filter = ['authors.user' => $user];
            if (isset($unit['start'])) {
                $filter['start_date'] = ['$gte' => $unit['start']];
            }
            if (isset($unit['end'])) {
                $filter['start_date'] = ['$lte' => $unit['end']];
            }
            // render all activities that match the filter
            renderAuthorUnitsMany($filter);
        }

        $_SESSION['msg'] = lang("Unit deleted successfully.", "Einheit erfolgreich gelöscht.");
        $_SESSION['msg_type'] = "success";
        header("Location: " . ROOTPATH . "/user/units/$user");
        die();
    }

    // transform values if needed
    $values = $_POST['values'];
    $values['scientific'] = boolval($values['scientific'] ?? false);
    $values['start'] = !empty($values['start']) ? $values['start'] : null;
    $values['end'] = !empty($values['end']) ? $values['end'] : null;

    if (isset($_POST['id'])) {
        // update existing unit
        $values['id'] = $_POST['id'];
        $osiris->persons->updateOne(
            ['username' => $user, 'units.id' => $_POST['id']],
            ['$set' => ['units.$' => $values]]
        );
    } else {
        // add new unit
        $values['id'] = uniqid();
        $osiris->persons->updateOne(
            ['username' => $user],
            ['$push' => ['units' => $values]]
        );
    }

    // update all activities that have this user as author
    $filter = ['authors.user' => $user];
    renderAuthorUnitsMany($filter);

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang("Unit updated successfully.", "Einheit erfolgreich aktualisiert.");
        $_SESSION['msg_type'] = "success";
        header("Location: " . $_POST['redirect']);
        die();
    }
    echo json_encode([
        'updated' => $updateResult->getModifiedCount()
    ]);
});


Route::post('/crud/users/inactivate/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";

    // check permissions
    if (!$Settings->hasPermission('user.inactive')) {
        echo "Permission denied.";
        die();
    }

    $data = $DB->getPerson($user);

    $keep = [
        '_id',
        'displayname',
        'formalname',
        'first_abbr',
        'updated',
        'updated_by',
        "academic_title",
        "first",
        "last",
        "name",
        "orcid",
        // "depts",
        "units",
        "username",
        "created",
        "created_by",
        "search_text",
        'uniqueid',
    ];
    $arr = [];
    foreach ($data as $key => $value) {
        if (in_array($key, $keep)) continue;
        $arr[$key] = null;
    }
    $arr['is_active'] = false;
    $arr['inactivated'] = date('Y-m-d');
    $updateResult = $osiris->persons->updateOne(
        ['username' => $user],
        ['$set' => $arr]
    );



    if (file_exists(BASEPATH . "/img/users/$user.jpg")) {
        unlink(BASEPATH . "/img/users/$user.jpg");
    }

    $_SESSION['msg'] = lang("User inactivated successfully.", "Benutzer erfolgreich deaktiviert.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/profile/" . $user);
    die();
});


Route::post('/crud/users/delete/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";

    // check permissions
    if (!$Settings->hasPermission('user.delete')) {
        $_SESSION['msg'] = lang("You don't have permission to delete users.", "Du hast keine Berechtigung, Benutzer zu löschen.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/profile/$user");
        die();
    }

    $data = $DB->getPerson($user);
    if (empty($data)) {
        $_SESSION['msg'] = lang("User not found.", "Benutzer nicht gefunden.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/user/browse");
        die();
    }

    // delete username from all activities
    $osiris->activities->updateMany(
        ["authors.user" => $user],
        ['$set' => ["authors.$[elem].user" => null]],
        ['arrayFilters' => [["elem.user" => $user]]]
    );

    // delete user from all projects
    $osiris->projects->updateMany(
        ["persons.user" => $user],
        ['$pull' => ["persons" => ["user" => $user]]]
    );
    // delete user from all proposals
    $osiris->proposals->updateMany(
        ["persons.user" => $user],
        ['$pull' => ["persons" => ["user" => $user]]]
    );

    // delete user from infrastructures
    $osiris->infrastructures->updateMany(
        ["persons.user" => $user],
        ['$pull' => ["persons" => ["user" => $user]]]
    );

    // remove user from teaching
    $osiris->teaching->updateMany(
        ['contact_person' => $user],
        ['$set' => ['contact_person' => null]]
    );

    $osiris->accounts->deleteOne(
        ['username' => $user]
    );
    $osiris->persons->deleteOne(
        ['username' => $user]
    );
    $osiris->userImages->deleteOne(
        ['user' => $user]
    );

    if (file_exists(BASEPATH . "/img/users/$user.jpg")) {
        unlink(BASEPATH . "/img/users/$user.jpg");
    }

    $_SESSION['msg'] = lang("User deleted.", "Benutzer gelöscht.");
    // redirect to user browse page
    header("Location: " . ROOTPATH . "/user/browse");
    die();
});


/**
 * Update profile picture
 */
Route::post('/crud/users/profile-picture/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('user.image') && $user != $_SESSION['username']) {
        abortwith(403, lang("You don't have permission to change profile picture.", "Du hast keine Berechtigung, das Profilbild zu ändern."));
    }

    if (isset($_FILES["file"])) {
        // if ($_FILES['file']['type'] != 'image/jpeg') die('Wrong extension, only JPEG is allowed.');

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
            $_SESSION['msg_type'] = 'error';
        } else if ($_FILES["file"]["size"] > 2000000) {
            $_SESSION['msg'] = lang("File is too big: max 2 MB is allowed.", "Die Datei ist zu groß: maximal 2 MB sind erlaubt.");
            $_SESSION['msg_type'] = 'error';
        } else {
            // check image settings
            if ($Settings->featureEnabled('db_pictures')) {
                $file = file_get_contents($_FILES["file"]["tmp_name"]);
                $type = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
                // encode image
                $file = base64_encode($file);
                $img = new MongoDB\BSON\Binary($file, MongoDB\BSON\Binary::TYPE_GENERIC);
                // first: delete old image, then: insert new one
                $osiris->userImages->deleteOne(['user' => $user]);
                $updateResult = $osiris->userImages->insertOne([
                    'user' => $user,
                    'img' => $img,
                    'ext' => $type
                ]);
            } else {
                $target_dir = BASEPATH . "/img/users";
                if (!is_writable($target_dir)) {
                    abortwith(500, "User image directory is unwritable. Please contact admin.");
                }
                $target_dir .= "/";
                $filename = "$user.jpg";
                // upload to file system
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir . $filename)) {
                    $_SESSION['msg'] = lang("Profile picture updated.", "Profilbild aktualisiert.");
                    $_SESSION['msg_type'] = 'success';
                    header("Location: " . ROOTPATH . "/profile/$user");
                    die;
                }
            }
            header("Location: " . ROOTPATH . "/profile/$user");
            die;
            // printMsg(lang("Sorry, there was an error uploading your file.", "Entschuldigung, aber es gab einen Fehler beim Dateiupload."), "error");
        }
    } else if (isset($_POST['delete'])) {
        $filename = "$user.jpg";
        if ($Settings->featureEnabled('db_pictures')) {
            $osiris->userImages->deleteOne(['user' => $user]);
            $_SESSION['msg'] = lang("Profile picture deleted.", "Profilbild gelöscht.");
        } else {
            $target_dir = BASEPATH . "/img/users/";
            if (!is_writable($target_dir)) {
                abortwith(500, "User image directory is unwritable. Please contact admin.");
            } else if (!unlink($target_dir . $filename)) {
                // get error message
                $error = error_get_last();
                $_SESSION['msg'] = lang("Error deleting file.", "Fehler beim Löschen der Datei.") . " " . $error['message'];
                $_SESSION['msg_type'] = 'error';
            } else {
                $_SESSION['msg'] = lang("Profile picture deleted.", "Profilbild gelöscht.");
                $_SESSION['msg_type'] = 'success';
            }
        }
        header("Location: " . ROOTPATH . "/profile/$user");
        die();
    }
});


Route::post('/crud/users/approve', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    if (!isset($_POST['quarter'])) {
        echo "Quarter was not defined";
        die();
    }
    $q = $_POST['quarter'];

    $updateResult = $osiris->persons->updateOne(
        ['username' => $user],
        ['$push' => ["approved" => $q]]
    );

    $_SESSION['last_notification_check'] = 0;

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang("You approved the activities for quarter $q.", "Du hast die Aktivitäten für Quartal $q genehmigt.");
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $_POST['redirect']);
        die();
    }
    echo json_encode([
        'updated' => $updateResult->getModifiedCount()
    ]);
});


Route::post('/crud/queries', function () {
    include_once BASEPATH . "/php/init.php";

    $action = $_POST['action'] ?? 'ADD';

    if (isset($_POST['id']) && $action == 'DELETE') {
        // delete query with _id
        $deleteResult = $osiris->queries->deleteOne(['_id' => DB::to_ObjectID($_POST['id'])]);
        return $deleteResult->getDeletedCount();
        die;
    }
    if ($action == 'SHARE') {
        if (isset($_POST['global'])) {
            $values = ['global' => true];
        } else if (isset($_POST['role'])) {
            $values = ['global' => false, 'role' => $_POST['role']];
        } else {
            die("no visibility given");
        }
        $updateResult = $osiris->queries->updateOne(
            ['_id' => DB::to_ObjectID($_POST['id'])],
            ['$set' => $values]
        );
        return $updateResult->getModifiedCount();
        die;
    }

    if (!isset($_POST['name'])) die("no name given");
    if (!isset($_POST['rules'])) die("no rules given");
    if (!isset($_SESSION['username'])) die("no user given");
    $updateResult = $osiris->queries->insertOne([
        'name' => $_POST['name'],
        'rules' => json_encode($_POST['rules']),
        'user' => $_SESSION['username'],
        'created' => date('Y-m-d'),
        'aggregate' => $_POST['aggregate'] ?? null,
        'columns' => $_POST['columns'] ?? null,
        'type' => $_POST['type'] ?? 'activity',
        'expert' => (($_POST['expert'] ?? 'false') == 'true')
    ]);
    return $updateResult->getInsertedId();
});



Route::get('/claim/?(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";

    if (empty($user)) $user = $_SESSION['username'];

    $scientist = $DB->getPerson($user);
    $name = $scientist['displayname'];

    $breadcrumb = [
        ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
        ['name' => lang("$name", "$name"), 'path' => "/profile/$user"],
        ['name' => lang("Claim", "Beanspruchen")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/claim.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::post('/claim/?(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";

    if (empty($user)) $user = $_SESSION['username'];

    if (empty($_POST['activity'])) {
        $_SESSION['msg'] = lang("No activity selected.", "Keine Aktivität ausgewählt.");
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/claim/$user");
        die;
    }

    if (empty($_POST['last']) || empty($_POST['first'])) {
        $_SESSION['msg'] = lang("No valid submission.", "Keine gültige Eingabe.");
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/claim/$user");
        die;
    }

    $last = explode(";", $_POST['last']);
    $first = explode(";", $_POST['first']);
    $activities = $_POST['activity'];

    $N = 0;
    foreach ($activities as $key) {
        $mongo_id = DB::to_ObjectID($key);
        // update specific author (by name) in activity
        $updateResult = $osiris->activities->updateOne(
            ['_id' => $mongo_id, 'authors' => ['$elemMatch' => ['user' => null, 'last' => ['$in' => $last], 'first' => ['$in' => $first]]]],
            ['$set' => [
                'authors.$.user' => $user
            ]]
        );
        $N += $updateResult->getModifiedCount();
    }

    $_SESSION['msg'] = lang("Claim successful: You claimed $N activities.", "Beanspruchung erfolgreich: Du hast $N Aktivitäten beansprucht.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/profile/$user");
}, 'login');



Route::post('/crud/messages/mark-as-read/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $user = $_SESSION['username'];
    $updateResult = $osiris->notifications->updateOne(
        ['user' => $user],
        ['$set' => ['messages.$[elem].read' => true]],
        ['arrayFilters' => [['elem.id' => $id]]]
    );
    $updated = $updateResult->getModifiedCount();
    if ($updated > 0) {
        $_SESSION['last_notification_check'] = 0;
    }
    echo json_encode([
        'updated' => $updated,
        'success' => $updated > 0
    ]);
}, 'login');


Route::post('/crud/messages/mark-all-as-read', function () {
    include_once BASEPATH . "/php/init.php";

    $user = $_SESSION['username'];
    $updateResult = $osiris->notifications->updateOne(
        ['user' => $user],
        ['$set' => ['messages.$[].read' => true]]
    );
    $updated = $updateResult->getModifiedCount();
    if ($updated > 0) {
        $_SESSION['last_notification_check'] = 0;
    }
    echo json_encode([
        'updated' => $updated,
        'success' => $updated > 0
    ]);
}, 'login');


Route::post('/crud/messages/delete/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $user = $_SESSION['username'];
    $updateResult = $osiris->notifications->updateOne(
        ['user' => $user],
        ['$pull' => ['messages' => ['id' => $id]]]
    );
    $updated = $updateResult->getModifiedCount();
    if ($updated > 0) {
        $_SESSION['last_notification_check'] = 0;
    }
    echo json_encode([
        'updated' => $updated,
        'success' => $updated > 0
    ]);
}, 'login');

Route::post('/crud/messages/delete-all', function () {
    include_once BASEPATH . "/php/init.php";

    $user = $_SESSION['username'];
    $updateResult = $osiris->notifications->updateOne(
        ['user' => $user],
        ['$set' => ['messages' => []]]
    );
    $updated = $updateResult->getModifiedCount();
    if ($updated > 0) {
        $_SESSION['last_notification_check'] = 0;
    }
    echo json_encode([
        'updated' => $updated,
        'success' => $updated > 0
    ]);
}, 'login');
