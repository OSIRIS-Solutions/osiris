<?php

Route::get('/guests/?', function () {
    include_once BASEPATH . "/php/init.php";

    $breadcrumb = [
        ['name' => lang('Guests', 'Gäste')],
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/guests/list.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/guests/overview', function () {
    include_once BASEPATH . "/php/init.php";

    $breadcrumb = [
        ['name' => lang('Guests', 'Gäste'), 'path' => "/guests"],
        ['name' => lang("Overview", "Überblick")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/guests/overview.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/guests/new', function () {
    include_once BASEPATH . "/php/init.php";

    // Generate new id
    $id = uniqid();
    $form = [];

    $breadcrumb = [
        ['name' => lang('Guests', 'Gäste'), 'path' => "/guests"],
        ['name' => lang("New", "Erstellen")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/guests/form.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/guests/edit/([a-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $form = $osiris->guests->findOne(['id' => $id]);
    $breadcrumb = [
        ['name' => lang('Guests', 'Gäste'), 'path' => "/guests"],
        ['name' => $id]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/guests/form.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/guests/view/([a-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $form = $osiris->guests->findOne(['id' => $id]);
    $breadcrumb = [
        ['name' => lang('Guests', 'Gäste'), 'path' => "/guests"],
        ['name' => $id]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/guests/view.php";
    include BASEPATH . "/footer.php";
}, 'login');


// POST METHODS
Route::post('/guests/save', function () {
    include_once BASEPATH . "/php/init.php";

    $collection = $osiris->guests;

    if (!isset($_POST['values'])) {
        echo "no values given";
        die;
    }

    $values = $_POST['values'];
    // get supervisor first, otherwise users are converted into authors
    $supervisor = $DB->getPerson($values['user']);
    if (empty($supervisor)) die('Supervisor does not exist');
    // remove supervisor from OG dataset
    unset($values['user']);

    // standardize inputs
    $values = validateValues($values, $DB);
    // dump($_POST);
    if (!isset($values['id'])) {
        echo "no id given";
        die;
    }
    $id = $values['id'];

    $finished = false;
    $guest_exist = $collection->findOne(['id' => $id]);
    if (!empty($guest_exist)) {
        $finished = $guest_exist['legal']['general'] ?? false;
    } else {
        // add information on creating process
        $values['created'] = date('Y-m-d');
        $values['created_by'] = $_SESSION['username'];

        // check if check boxes are checked
        $values['legal']['general'] = $values['legal']['general'] ?? false;
        $values['legal']['data_security'] = $values['legal']['data_security'] ?? false;
        $values['legal']['data_protection'] = $values['legal']['data_protection'] ?? false;
        $values['legal']['safety_instruction'] = $values['legal']['safety_instruction'] ?? false;

        // add supervisor information
        $values['supervisor'] = [
            "user" => $supervisor['username'],
            "name" => $supervisor['displayname'],
            "email" => $supervisor['mail'],
        ];

        // check if mail should be sent
        if ($Settings->featureEnabled('guest-mails')) {
            $mail = $Settings->get('guest-mails-register');
            // send email to all 

        }
    }

    $msg = lang("Guest saved successfully.", "Gast erfolgreich gespeichert.");

    if (!$finished && $Settings->featureEnabled('guest-forms')) {

        // check if server and secret key are defined
        $guest_server = $Settings->get('guest-forms-server');
        $guest_secret = $Settings->get('guest-forms-secret-key');
        if (empty($guest_server)) {
            $msg = lang("Guest server is not defined. Please contact admin.", "Gast-Server ist nicht definiert. Bitte kontaktieren Sie den Administrator.");
        } else if (empty($guest_secret)) {
            $msg = lang("Secret key is not defined. Please contact admin.", "Geheimer Schlüssel ist nicht definiert. Bitte kontaktieren Sie den Administrator.");
        } else {
            // if server and key is defined:
            // send data to guest server
            $URL = $guest_server . '/api/post';
            $postData = $values;
            $postData['secret'] = $guest_secret;
            $postRes = CallAPI('JSON', $URL, $postData);
            $postRes = json_decode($postRes, true);
            if ($postRes['message'] != 'Success') {
                die($postRes['message']);
            }
        }
    }

    // check if guest already exists:
    if (!empty($guest_exist)) {
        $id = $guest_exist['id'];
        $collection->updateOne(
            ['id' => $id],
            ['$set' => $values]
        );
    } else {
        $insertOneResult  = $collection->insertOne($values);
    }

    $_SESSION['msg'] = $msg;
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/guests/view/$id");
}, 'login');



Route::post('/guests/activity-qr/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('guest-forms')) {
        die("Guest forms are not enabled.");
    }

    $mongo_id = DB::to_ObjectID($id);
    $activity = $osiris->activities->findOne(['id' => $mongo_id]);

    $values = $_POST['values'];
    $msg = lang("QR code updated successfully.", "QR-Code erfolgreich aktualisiert.");
    // check if server and secret key are defined
    $guest_server = $Settings->get('guest-forms-server');
    $guest_secret = $Settings->get('guest-forms-secret-key');
    if (empty($guest_server)) {
        $msg = lang("Guest server is not defined. Please contact admin.", "Gast-Server ist nicht definiert. Bitte kontaktieren Sie den Administrator.");
    } else if (empty($guest_secret)) {
        $msg = lang("Secret key is not defined. Please contact admin.", "Geheimer Schlüssel ist nicht definiert. Bitte kontaktieren Sie den Administrator.");
    } else {
        // if server and key is defined:
        // send data to guest server
        $URL = $guest_server . '/api/post';
        $postData = $values;
        $postData['secret'] = $guest_secret;
        $postRes = CallAPI('JSON', $URL, $postData);
        $postRes = json_decode($postRes, true);
        if ($postRes['message'] != 'Success') {
            die($postRes['message']);
        }
    }

    $osiris->activities->updateOne(
        ['id' => $mongo_id],
        ['$set' => ['guest-qrcode' => true]]
    );

    $_SESSION['msg'] = $msg;
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/guests/view/$id");
}, 'login');




Route::post('/guests/synchronize/([a-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $collection = $osiris->guests;

    $guest_server = $Settings->get('guest-forms-server');
    if (empty($guest_server)) {
        $_SESSION['msg'] = lang("Guest server is not defined. Please contact admin.", "Gast-Server ist nicht definiert. Bitte kontaktieren Sie den Administrator.");
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/guests");
        die;
    }
    $guest_secret = $Settings->get('guest-forms-secret-key');
    if (empty($guest_secret)) {
        $_SESSION['msg'] = lang("Secret key is not defined. Please contact admin.", "Geheimer Schlüssel ist nicht definiert. Bitte kontaktieren Sie den Administrator.");
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/guests");
        die;
    }

    // send data to guest server
    $URL = $guest_server . '/api/get/' . $id;
    if (!str_contains($URL, '//')) $URL = "https://" . $URL;
    $postData = [];
    $postData['secret'] = $guest_secret;
    $postRes = CallAPI('GET', $URL, $postData);
    $values = json_decode($postRes, true);

    // check if guest already exists:
    $guest_exist = $collection->findOne(['id' => $id]);
    if (!empty($guest_exist)) {
        $collection->updateOne(
            ['id' => $id],
            ['$set' => $values]
        );

        $_SESSION['msg'] = lang("Guest synchronized successfully.", "Gast erfolgreich synchronisiert.");
        $_SESSION['msg_type'] = 'success';
        header("Location: " . ROOTPATH . "/guests/view/$id");
        die;
    } else {
        $_SESSION['msg'] = lang("Guest not found.", "Gast nicht gefunden.");
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/guests");
        die;
    }
}, 'login');


/**
 * Update data points within 
 */
Route::post('/guests/update/([a-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $collection = $osiris->guests;
    $values = $_POST['values'];

    $collection->updateOne(
        ['id' => $id],
        ['$set' => $values]
    );

    $_SESSION['msg'] = lang("Guest updated successfully.", "Gast erfolgreich aktualisiert.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/guests/view/$id");
}, 'login');


/**
 * Cancel guest 
 */
Route::post('/guests/cancel/([a-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $collection = $osiris->guests;

    $cancel = boolval($_POST['cancel'] ?? true);

    if ($cancel) {
        $values = [
            'cancelled' => true,
            'cancelled_by' => $_SESSION['username'],
            'cancelled_date' => date('Y-m-d')
        ];
    } else {
        $values = [
            'cancelled' => false,
            'cancelled_by' => null,
            'cancelled_date' => null
        ];
    }
    $collection->updateOne(
        ['id' => $id],
        ['$set' => $values]
    );

    $_SESSION['msg'] = lang("Guest cancelled successfully.", "Gast erfolgreich storniert.");
    $_SESSION['msg_type'] = 'success'; 
    header("Location: " . ROOTPATH . "/guests/view/$id");
}, 'login');



Route::post('/guests/upload-files/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $target_dir = BASEPATH . "/uploads/";
    if (!is_writable($target_dir)) {
        abortwith(500, lang("Upload directory $target_dir is unwritable. Please contact admin.", "Upload-Verzeichnis $target_dir ist nicht beschreibbar. Bitte kontaktieren Sie den Administrator."));
    }
    $target_dir .= "$id/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777);
        echo "<!-- The directory $target_dir was successfully created.-->";
    } else {
        echo "<!-- The directory $target_dir exists.-->";
    }


    if (isset($_FILES["file"])) {

        $filename = e(basename($_FILES["file"]["name"]));
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES["file"]["size"];
        $filepath = ROOTPATH . "/uploads/$id/$filename";

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
        } else if ($filesize > 16000000) {
            $_SESSION['msg'] = lang("File is too big: max 16 MB is allowed.", "Die Datei ist zu groß: maximal 16 MB sind erlaubt.");
            $_SESSION['msg_type'] = 'error';
        } else if (file_exists($target_dir . $filename)) {
            $_SESSION['msg'] = lang("Sorry, file already exists.", "Die Datei existiert bereits. Um sie zu überschreiben, muss sie zunächst gelöscht werden.");
            $_SESSION['msg_type'] = 'error';
        } else if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir . $filename)) {
            $_SESSION['msg'] = lang("The file $filename has been uploaded.", "Die Datei <q>$filename</q> wurde hochgeladen.");
            $_SESSION['msg_type'] = 'success';
            $values = [
                "filename" => $filename,
                "filetype" => $filetype,
                "filesize" => $filesize,
                "filepath" => $filepath,
            ];

            $osiris->guests->updateOne(
                ['id' => $id],
                ['$push' => ["files" => $values]]
            );
            // $files[] = $values;
        } else {
            $_SESSION['msg'] = lang("Sorry, there was an error uploading your file.", "Entschuldigung, aber es gab einen Fehler beim Dateiupload.");
            $_SESSION['msg_type'] = 'error';
        }

        header("Location: " . ROOTPATH . "/guests/view/" . $id);
        die();
    } else if (isset($_POST['delete'])) {
        $filename = $_POST['delete'];
        if (file_exists($target_dir . $filename)) {
            // Use unlink() function to delete a file
            if (!unlink($target_dir . $filename)) {
                $_SESSION['msg'] = "$filename cannot be deleted due to an error.";
                $_SESSION['msg_type'] = 'error';
            } else {
                $_SESSION['msg'] = lang("$filename has been deleted.", "$filename wurde gelöscht.");
                $_SESSION['msg_type'] = 'success';
            }
        }

        $osiris->guests->updateOne(
            ['id' => $id],
            ['$pull' => ["files" => ["filename" => $filename]]]
        );

        header("Location: " . ROOTPATH . "/guests/view/" . $id);
        die();
    }
});


// crud/activities/guests
Route::post('/crud/activities/guests', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['id'])) {
        abortwith(500, lang("No activity id given.", "Keine Aktivitäts-ID angegeben."), "/activities");
    }
    $activity_id = $_POST['id'];
    $mongo_id = DB::to_ObjectID($activity_id);

    $activity = $osiris->activities->findOne(['_id' => $mongo_id]);
    $existing_guests = DB::doc2Arr($activity['guests'] ?? []);

    if (!empty($existing_guests)){
        $existing_guests = array_column($existing_guests, null, 'id');
    }

    $guests = $_POST['guests'];

    // zip arrays with keys
    $result = [];
    foreach ($guests['id'] as $key => $id) {
        $guest = [
            'id' => $id,
            'last' => $guests['last'][$key],
            'first' => $guests['first'][$key],
            'email' => $guests['email'][$key],
            'form' => false,
            'status' => 'new'
        ];

        if (isset($existing_guests[$id])) {
            $guest['qr'] = $existing_guests[$id]['qr'] ?? false;
            $guest['status'] = $existing_guests[$id]['status'] ?? 'new';

            // if if not new but mail changed
            if ($guest['status'] !== 'new' && $existing_guests[$id]['email'] != $guest['email']) {
                $guest['status'] = 'changed';
            }
        }

        $result[] = $guest;
    }

    $osiris->activities->updateOne(
        ['_id' => $mongo_id],
        ['$set' => ['guests' => $result]]
    );
    
    $_SESSION['msg'] = lang("Guests updated successfully.", "Gäste erfolgreich aktualisiert.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/activities/view/$activity_id");
}, 'login');


Route::post('/crud/activities/guests/qr', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['id'])) {
        abortwith(500, lang("No activity id given.", "Keine Aktivitäts-ID angegeben."), "/activities");
    }
    $activity_id = $_POST['id'];
    $mongo_id = DB::to_ObjectID($activity_id);

    $activity = $osiris->activities->findOne(['_id' => $mongo_id]);
    $existing_guests = DB::doc2Arr($activity['guests'] ?? []);

    if (!empty($existing_guests)){
        $existing_guests = array_column($existing_guests, null, 'id');
    }

    $guests = $_POST['guests'];

    // zip arrays with keys
    $result = [];
    foreach ($guests['id'] as $key => $id) {
        $guest = [
            'id' => $id,
            'last' => $guests['last'][$key],
            'first' => $guests['first'][$key],
            'email' => $guests['email'][$key],
            'qr' => $guests['qr'][$key] ?? false,
            'status' => 'new'
        ];

        if (isset($existing_guests[$id])) {
            $guest['status'] = $existing_guests[$id]['status'] ?? 'new';
        }

        $result[] = $guest;
    }

    $osiris->activities->updateOne(
        ['_id' => $mongo_id],
        ['$set' => ['guests' => $result]]
    );

    $_SESSION['msg'] = lang("Guest QR codes updated successfully.", "QR-Codes der Gäste erfolgreich aktualisiert.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/activities/view/$activity_id");
}, 'login');