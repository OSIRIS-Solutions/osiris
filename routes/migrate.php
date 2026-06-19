<?php

/**
 * Routing file for the database migration
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


function migrationCard($title_en, $title_de, $text_en, $text_de, $count = null, $label_en = null, $label_de = null)
{
    echo '<div class="migration-card">';
    echo '<h3 class="migration-ok">✓ ' . lang($title_en, $title_de) . '</h3>';
    echo '<p>' . lang($text_en, $text_de) . '</p>';

    if ($count !== null) {
        echo '<div class="migration-summary">';
        echo '<div class="migration-stat">';
        echo '<strong>' . intval($count) . '</strong>';
        echo '<span>' . lang($label_en ?? 'documents updated', $label_de ?? 'Dokumente aktualisiert') . '</span>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';
}

Route::get('/migration-needed', function () {
    include_once BASEPATH . "/php/init.php";
    // check if we need to run the migration
    $version = $osiris->system->findOne(['key' => 'version']);
    if (empty($version) || version_compare($version['value'], OSIRIS_VERSION, '<')) {
        // migration needed
    } else {
        $_SESSION['msg'] = lang('Your OSIRIS installation is up to date.', 'Deine OSIRIS-Installation ist auf dem neuesten Stand.');
        header('Location: ' . ROOTPATH . '/');
    }
    include_once BASEPATH . "/header.php";

    if ($Settings->hasPermission('admin.see')) {
?>
        <div class="container text-center" style="max-width: 70rem;">
            <img src="<?= ROOTPATH ?>/img/sophie/sophie-maintenance.png" alt="Maintenance" style="width: 100%; max-width: 50rem; margin: 0 auto; display: block;">
            <h1 class="mt-0">
                <?= lang('A new OSIRIS version has been found', 'Eine neue OSIRIS-Version wurde gefunden') ?>!
            </h1>
            <p>
                <?= lang(
                    'OSIRIS will be updated and set up automatically. Depending on the version, this might take some time, so please make sure not to reload or close the page during the process.',
                    'OSIRIS wird automatisch aktualisiert und eingerichtet. Abhängig von der Version kann dies eine ganze Weile dauern, stelle also bitte sicher, dass du die Seite während des Prozesses nicht neu lädst oder schließt.'
                ) ?>
            </p>
            <p class="text-muted">
                <small><?= lang('Installed', 'Installiert') ?>: <?= $version['value'] ?></small> | <small><?= lang('Latest', 'Neueste') ?>: <?= OSIRIS_VERSION ?></small>
            </p>
            <a href="<?= ROOTPATH ?>/migrate" class="btn cta large">
                <?= lang('Update OSIRIS', 'OSIRIS aktualisieren') ?>
            </a>
        </div>
    <?php
    } else {
    ?>
        <div class="container text-center">
            <img src="<?= ROOTPATH ?>/img/sophie/sophie-maintenance.png" alt="Maintenance" style="width: 100%; max-width: 50rem; margin: 0 auto; display: block;">
            <h1 class="mt-0">
                <?= lang('OSIRIS is being updated', 'OSIRIS wird aktualisiert') ?>...
            </h1>

            <p>
                <?= lang(
                    'OSIRIS is currently being updated to the latest version. Please check back later.',
                    'OSIRIS wird gerade auf die neueste Version aktualisiert. Bitte schau später noch einmal vorbei.'
                ) ?>
            </p>

            <div class="spacer h-100"></div>
            <small class="text-muted">
                <?= lang('In case you are seeing this message for a long time, please contact your administrator.', 'Falls du diese Nachricht über einen längeren Zeitraum siehst, kontaktiere bitte deinen Administrator.') ?>
            </small>
        </div>
    <?php
    }
    include_once BASEPATH . "/footer.php";
    die;
});

Route::get('/migrate/countries', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Country.php";

    set_time_limit(6000);
    include BASEPATH . "/header.php";
    include_once BASEPATH . "/routes/migration/v1.4.2.php";
    include BASEPATH . "/footer.php";
});


Route::get('/migrate/files', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Country.php";

    set_time_limit(6000);
    include BASEPATH . "/header.php";
    include_once BASEPATH . "/routes/migration/filemigration.php";
    include BASEPATH . "/footer.php";
});

Route::get('/migrate/(.*)', function ($v) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Country.php";
    include_once BASEPATH . "/php/Render.php";
    if (!$Settings->hasPermission('admin.see')) {
        return abortwith(403);
    }
    set_time_limit(6000);
    include BASEPATH . "/header.php";

    echo '<div class="migration-report">';

    echo "<h1>" . lang('Migrating OSIRIS to Version <span class="version">' . OSIRIS_VERSION . '</span>', 'OSIRIS wird auf Version <span class="version">' . OSIRIS_VERSION . '</span> migriert') . "</h1>";
    flush();
    ob_flush();

    // check if version file exists
    if (!file_exists(BASEPATH . "/routes/migration/v$v.php")) {
        echo "<p>Migration file for version $v not found. Please check if the file <code>v$v.php</code> exists in the <code>routes/migration</code> folder.</p>";
    } else {
        include_once BASEPATH . "/routes/migration/v$v.php";
    }
    echo '</div>';
    include BASEPATH . "/footer.php";
});


Route::get('/migrate/cv', function () {
    // error_reporting(E_ERROR | E_PARSE);
    set_time_limit(6000);
    include_once BASEPATH . "/php/init.php";

    include BASEPATH . "/header.php";
    include_once BASEPATH . "/php/Render.php";

    // migrate cv dates
    $users = $osiris->persons->find(['cv' => ['$exists' => true]])->toArray();
    foreach ($users as $user) {
        $cv = $user['cv'];
        if (empty($cv)) continue;
        foreach ($cv as $i => $con) {
            $con = json_encode($con);
            $con = json_decode($con, true); // convert to array if it is still an object
            if (!isset($con['from']) || (is_array($con['from']) && array_key_exists('year', $con['from']) && is_null($con['from']['year']))) {
                $con['from'] = null;
            }
            if (!is_string($con['from'] ?? null) && isset($con['from']['year'])) {
                $con['from'] = ($con['from']['year'] ?? '') . '-' . str_pad(($con['from']['month'] ?? ''), 2, '0', STR_PAD_LEFT);
            }
            if (!isset($con['to']) || (is_array($con['to']) && array_key_exists('year', $con['to']) && is_null($con['to']['year']))) {
                $con['to'] = null;
            }
            if (!is_string($con['to'] ?? null) && isset($con['to']['year'])) {
                $con['to'] = ($con['to']['year'] ?? '') . '-' . str_pad(($con['to']['month'] ?? ''), 2, '0', STR_PAD_LEFT);
            }
            $cv[$i] = $con;
        }
        $osiris->persons->updateOne(
            ['_id' => $user['_id']],
            ['$set' => ['cv' => $cv]]
        );
    }
    echo lang('CV dates migrated successfully.', 'CV-Daten wurden erfolgreich migriert.');

    include BASEPATH . "/footer.php";
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

    // make sure to create an index
    $osiris->activities->createIndex(['rendered.plain' => 'text']);

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
    // show all errors for debugging
    error_reporting(E_ALL);

    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Render.php";

    include BASEPATH . "/header.php";


    // check if user is logged in and has admin rights
    if (!$Settings->hasPermission('admin.see')) {
        echo "<p class='alert danger'>" . lang('You do not have permission to access this page.', 'Du hast keine Berechtigung, diese Seite zu betreten.') . "</p>";
        include BASEPATH . "/footer.php";
        die;
    }

    echo '<div class="migration-report">';

    echo "<h1>" . lang('Migrating OSIRIS to Version <span class="version">' . OSIRIS_VERSION . '</span>', 'OSIRIS wird auf Version <span class="version">' . OSIRIS_VERSION . '</span> migriert') . "</h1>";
    flush();
    ob_flush();

    $DBversion = $osiris->system->findOne(['key' => 'version']);

    // check if DB version is current version
    if (!empty($DBversion) && $DBversion['value'] == OSIRIS_VERSION) {
        echo '
        <div class="migration-report">
            <div class="migration-card">
                <h3 class="migration-ok">✓ ' . lang('Everything is up to date', 'Alles ist auf dem neuesten Stand') . '</h3>

                <p>' . lang(
            'No migration steps were required. OSIRIS is already using the latest database schema and configuration.',
            'Es waren keine Migrationsschritte erforderlich. OSIRIS verwendet bereits das aktuelle Datenbankschema und die aktuelle Konfiguration.'
        ) . '</p>
            </div>
        </div>
        </div>';
        include BASEPATH . "/footer.php";
        die;
    }

    ?>
    <h2><?= lang('Database migration report', 'Bericht zur Datenbankmigration') ?></h2>
    <p class="migration-muted">
        <?= lang(
            'OSIRIS is updating required database structures for the current version. Existing data is preserved and adapted where necessary.',
            'OSIRIS aktualisiert notwendige Datenbankstrukturen für die aktuelle Version. Bestehende Daten bleiben erhalten und werden bei Bedarf angepasst.'
        ) ?>
    </p>
<?php

    if (empty($DBversion)) {
        $DBversion = "1.0.0";
        $osiris->system->insertOne([
            'key' => 'version',
            'value' => $DBversion
        ]);
    } else {
        $DBversion = $DBversion['value'];
    }

    $rerender = false;

    if (version_compare($DBversion, '1.2.0', '<')) {
        include BASEPATH . "/routes/migration/v1.2.0.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.2.1', '<')) {
        include BASEPATH . "/routes/migration/v1.2.1.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.3.0', '<')) {
        include BASEPATH . "/routes/migration/v1.3.0.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.3.3', '<')) {
        include BASEPATH . "/routes/migration/v1.3.3.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.3.4', '<')) {
        include BASEPATH . "/routes/migration/v1.3.4.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.3.6', '<')) {
        include BASEPATH . "/routes/migration/v1.3.6.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.3.7', '<')) {
        include BASEPATH . "/routes/migration/v1.3.7.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.3.8', '<')) {
        include BASEPATH . "/routes/migration/v1.3.8.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.4.0', '<')) {
        include BASEPATH . "/routes/migration/v1.4.0.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.4.1', '<')) {
        include BASEPATH . "/routes/migration/v1.4.1.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.4.2', '<')) {
        include BASEPATH . "/routes/migration/v1.4.2.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.5.0', '<')) {
        include BASEPATH . "/routes/migration/v1.5.0.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if (version_compare($DBversion, '1.6.2', '<')) {
        include BASEPATH . "/routes/migration/v1.6.2.php";
        flush();
        ob_flush();
        $rerender = false;
    }

    if (version_compare($DBversion, '1.7.0', '<')) {
        include BASEPATH . "/routes/migration/v1.7.0.php";
        flush();
        ob_flush();
        $rerender = true;
    }
    if (version_compare($DBversion, '1.7.1', '<')) {
        include BASEPATH . "/routes/migration/v1.7.1.php";
        flush();
        ob_flush();
        $rerender = true;
    }
    if (version_compare($DBversion, '1.8.0', '<')) {
        include BASEPATH . "/routes/migration/v1.8.0.php";
        flush();
        ob_flush();
        $rerender = true;
    }
    if (version_compare($DBversion, '2.0.0', '<')) {
        include BASEPATH . "/routes/migration/v2.0.0.php";
        flush();
        ob_flush();
        $rerender = true;
    }

    if ($rerender) {
        echo "<p>Rerender activities, please wait ...</p>";
        flush();
        ob_flush();

        renderActivities();
    }


    echo "<p>" . lang('Migration completed successfully.', 'Die Migration wurde erfolgreich abgeschlossen.') . "</p>";
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

    echo "</div>";
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
