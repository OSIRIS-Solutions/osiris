<?php

Route::get('/import', function () {
    $breadcrumb = [
        ['name' => lang('Import')]
    ];
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/import.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/import/googlescholar/(.*)', function ($scholar_id) {
    $breadcrumb = [
        ['name' => lang('Import'), 'path' => '/import'],
        ['name' => lang('search.google_scholar')]
    ];
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/import-googlescholar.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/import/openalex', function () {
    // if no openalex id redirect to import page
    if (!isset($_GET['openalex-id'])) {
        header('Location: ' . ROOTPATH . '/import');
        exit;
    }

    $breadcrumb = [
        ['name' => lang('Import'), 'path' => '/import'],
        ['name' => 'OpenAlex']
    ];
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/import-openalex.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::post('/import/google', function () {
    header("Content-Type: application/json");
    header("Pragma: no-cache");
    header("Expires: 0");
    if (!isset($_POST["user"]) || !isset($_POST['doc']))
        exit - 1;

    include(BASEPATH . '/php/init.php');
    include_once BASEPATH . "/php/Render.php";
    include(BASEPATH . '/php/GoogleScholar.php');
    $user = $_POST["user"];
    $google = new GoogleScholar($user);

    $docid = $_POST["doc"];
    $pub = $google->getDocumentDetails($docid);

    $result = [];

    if (empty($pub['title'])) die('Error: Title was empty.');
    if (empty($pub['Publikationsdatum'])) die('Error: Date was empty.');

    $result['type'] = 'publication';
    $result['title'] = $pub['title'];
    $result['doi'] = empty($pub['doi']) ? null : $pub['doi'];
    $date = explode('/', $pub['Publikationsdatum']);
    $result['year'] = intval($date[0]);
    $result['month'] = isset($date[1]) ? intval($date[1]) : null;
    $result['day'] = isset($date[2]) ? intval($date[2]) : null;

    $result['volume'] = $pub['Band'] ?? null;
    $result['issue'] = $pub['Ausgabe'] ?? null;
    $result['pages'] = $pub['Seiten'] ?? null;

    $result['subtype'] = 'article';

    if (isset($pub['Zeitschrift']) || isset($pub['Quelle'])) {
        $result['journal'] = $pub['Zeitschrift'] ?? $pub['Quelle'];
        $j = new MongoDB\BSON\Regex('^' . trim($result['journal']) . '$', 'i');
        $journal = $osiris->journals->findOne(['journal' => ['$regex' => $j]]);
        if (!empty($journal)) {
            $result['journal_id'] = strval($journal['_id']);
            $result['journal'] = $journal['journal'];
        }
    } else if (isset($pub['Buch'])) {
        $result['book'] = $pub['Buch'];
        $result['publisher'] = $pub['Verlag'] ?? null;
        $result['subtype'] = 'chapter';
    } else {
        $result['publisher'] = $pub['Verlag'] ?? null;
        $result['subtype'] = 'others';
    }

    // add history
    $result['history'] = [
        ['date' => date('Y-m-d'), 'type' => 'imported', 'user' => $_SESSION['username']]
    ];

    // update authors and check if they are in the database
    $result['authors'] = array();
    foreach ($pub['Autoren'] as $i => $a) {
        $a = explode(' ', $a);
        $last = array_pop($a);
        $first = implode(' ', $a);

        $pos = 'middle';
        if ($i == 0) $pos = 'first';
        elseif ($i == count($pub['Autoren']) - 1) $pos = 'last';

        $username = $DB->getUserFromName($last, $first);
        $author = [
            'first' => $first,
            'last' => $last,
            'user' => $username,
            'position'=>$pos,
            'aoi' => !empty($username)
        ];
        $result['authors'][] = $author;
    }

    // insert document into the database
    $result['created'] = date('Y-m-d');
    $result['created_by'] = $_SESSION['username'];


    if (isset($result['doi']) && !empty($result['doi'])) {
        $doi_exist = $$osiris->activities->findOne(['doi' => $result['doi']]);
        if (!empty($doi_exist)) {
            die('DOI already exists. Publication could not be added.');
        }
    }
    $insertOneResult  = $osiris->activities->insertOne($result);
    $id = $insertOneResult->getInsertedId();
    $result['_id'] = $id;
    renderActivities(['_id' => $id]);

    $Format = new Document();
    $Format->setDocument($result);

    echo json_encode([
        'inserted' => $insertOneResult->getInsertedCount(),
        'id' => strval($id),
        'result' => $result,
        'formatted' => $Format->format()
    ]);

    // echo json_encode($result);
});


Route::post('/import/file', function () {
    // if ($page == 'users') 
    $breadcrumb = [
        ['name' => lang('Import'), 'path' => '/import'],
        ['name' => lang('search.from_file')]
    ];
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/import-file.php";
    include BASEPATH . "/footer.php";
}, 'login');
