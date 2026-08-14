<?php

/**
 * Routing for spectrum
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


Route::get('/spectrum', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang("Research Spectrum", "Forschungs-Spektrum"), 'path' => "/spectrum"]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/spectrum/list.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/spectrum/visualize', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang("Research Spectrum", "Forschungs-Spektrum"), 'path' => "/spectrum"],
        ['name' => lang("Visualize", "Visualisieren")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/spectrum/visualize.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/spectrum/evolution', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang("Research Spectrum", "Forschungs-Spektrum"), 'path' => "/spectrum"],
        ['name' => lang("Evolution", "Entwicklung")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/spectrum/evolution.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/spectrum/visualize', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang("Research Spectrum", "Forschungs-Spektrum"), 'path' => "/spectrum"],
        ['name' => lang("Visualize", "Visualisieren")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/spectrum/visualize.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/spectrum/(domain|field|subfield|topic)/(.*)', function ($level, $id) {
    include_once BASEPATH . "/php/init.php";

    switch ($level) {
        case 'domain':
            $idField = 'domain_id';
            $nameField = 'domain';
            break;
        case 'field':
            $idField = 'field_id';
            $nameField = 'field';
            break;
        case 'subfield':
            $idField = 'subfield_id';
            $nameField = 'subfield';
            break;
        default:
            $idField = 'id';
            $nameField = 'name';
    }
    $match = [
        'openalex.topics' => ['$exists' => true, '$ne' => []],
        'affiliated' => true,
        'openalex.topics.' . $idField => $id
    ];

    // Load topic meta from first match
    $topicMeta = $osiris->activities->findOne($match, [
        'projection' => ['openalex.topics' => 1]
    ]);
    if (!$topicMeta || !isset($topicMeta['openalex']['topics']) || count($topicMeta['openalex']['topics']) == 0) {
        abortwith(404, lang("Research Spectrum", "Forschungs-Spektrum"), "/spectrum", lang("Back to spectrum overview", "Zurück zur Spektrum Übersicht"));
    }
    $spectrum = null;
    $name = '';
    foreach ($topicMeta['openalex']['topics'] as $t) {
        if ($t[$idField] === $id) {
            $spectrum = $t;
            $name = $t[$nameField];
            break;
        }
    }

    if (!$spectrum) {
        abortwith(404, lang("Research Spectrum", "Forschungs-Spektrum"), "/spectrum", lang("Back to spectrum overview", "Zurück zur Spektrum Übersicht"));
    }

    $totalPublications = $osiris->activities->count($match);

    // Total institute publications with spectrum data
    $instituteTotal = $osiris->activities->count([
        'openalex.topics' => ['$exists' => true]
    ]);

    $share = $instituteTotal > 0 ? $totalPublications / $instituteTotal : 0;

    $breadcrumb = [
        ['name' => lang("Research Spectrum", "Forschungs-Spektrum"), 'path' => "/spectrum"],
        ['name' => $name]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/spectrum/view.php";
    include BASEPATH . "/footer.php";
}, 'login');


// crud/activities/update-spectrum
Route::post('/crud/activities/update-spectrum/([a-zA-Z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $mongo_id = DB::to_ObjectID($id);
    $doc = $osiris->activities->findOne(['_id' => $mongo_id]);
    if (!$doc) {
        abortwith(404, lang("Activity not found", "Aktivität nicht gefunden"), "/activities", lang("Back to activities", "Zurück zu Aktivitäten"));
    }

    // check if user has permission to update spectrum
    $user_activity = $DB->isUserActivity($doc, $_SESSION['username']);
    $edit_perm = ($user_activity || $Settings->hasPermission('activities.edit'));
    if (!$edit_perm) {
        abortwith(403, lang('You do not have permission to edit this activity.', 'Du hast keine Berechtigung, diese Aktivität zu bearbeiten.'), '/activities/view/' . $id, lang('Go back to activity', 'Zurück zur Aktivität'));
    }

    $openalex = DB::doc2Arr($doc['openalex'] ?? []);

    // Load the local topic catalog for metadata enrichment
    $catalogData = json_decode(
        file_get_contents(BASEPATH . '/data/openalex-topics.json'),
        true
    );

    $catalogTopics = $catalogData['topics'] ?? $catalogData;
    $catalogById = array_column($catalogTopics, null, 'id');

    if (isset($_POST['restore']) && !empty($openalex['automatic_topics'] ?? false)) {
        $openalex['topics'] = [];
        foreach ($openalex['automatic_topics'] as $r) {
            $topic = $catalogById[$r['id']] ?? $r;
            unset($topic['search']);
            $topic['score'] = $r['score'] ?? 1;
            $topic['manual'] = false;
            $openalex['topics'][] = $topic;
        }

        unset(
            $openalex['automatic_topics'],
            $openalex['manual'],
            $openalex['manual_at']
        );

        $history = $doc['history'] ?? [];
        $hist = [
            'date' => date('Y-m-d'),
            'user' => $_SESSION['username'] ?? 'system',
            'type' => 'restored-spectrum'
        ];
        $history[] = $hist;

        $osiris->activities->updateOne(['_id' => $mongo_id], ['$set' => ['openalex' => $openalex, 'history' => $history]]);
        header('Location: ' . ROOTPATH . '/activities/view/' . $id);
        return;
    }

    $openalex['manual_at'] = date('Y-m-d');
    
    if (empty($_POST['topics'] ?? [])) {
        $openalex['topics'] = [];
        $openalex['manual'] = true;

        $history = $doc['history'] ?? [];
        $hist = [
            'date' => date('Y-m-d'),
            'user' => $_SESSION['username'] ?? 'system',
            'type' => 'modified-spectrum'
        ];
        $history[] = $hist;

        $osiris->activities->updateOne(['_id' => $mongo_id], ['$set' => ['openalex' => $openalex, 'history' => $history]]);
        header('Location: ' . ROOTPATH . '/activities/view/' . $id);
        return;
    }


    // check if automatic spectrum is saved
    $selectedIds = array_values(array_unique(
        array_filter(
            $_POST['topics'] ?? [],
            fn($id) => preg_match('/^T\d+$/', $id)
        )
    ));


    // Preserve the last automatic assignment before the first manual edit.
    if (!array_key_exists('automatic_topics', $openalex)) {
        // backup id and score of automatic topics
        $backup = array_map(
            fn($topic) => [
                'id' => $topic['id'],
                'score' => $topic['score']
            ],
            DB::doc2Arr($openalex['topics'] ?? [])
        );
        $openalex['automatic_topics'] = $backup;
    }

    $automaticById = array_column(
        DB::doc2Arr($openalex['automatic_topics']),
        null,
        'id'
    );

    $topics = [];

    foreach ($selectedIds as $topicId) {
        $topic = $catalogById[$topicId];
        if (isset($automaticById[$topicId])) {
            // Preserve the original OpenAlex score.
            $topic['score'] = $automaticById[$topicId]['score'] ?? 1;
            $topic['manual'] = false;
        } elseif (isset($catalogById[$topicId])) {
            // Add metadata from the local topic catalog.

            unset($topic['search']);

            $topic['score'] = 1;
            $topic['manual'] = true;
        } else {
            throw new RuntimeException(
                "Unknown OpenAlex topic: $topicId"
            );
        }

        $topics[] = $topic;
    }

    $openalex['topics'] = $topics;
    $openalex['manual'] = true;

    // Add a history entry for the manual modification of the spectrum.
    $history = $doc['history'] ?? [];
    $hist = [
        'date' => date('Y-m-d'),
        'user' => $_SESSION['username'] ?? 'system',
        'type' => 'modified-spectrum'
    ];
    $history[] = $hist;

    $osiris->activities->updateOne(['_id' => $mongo_id], ['$set' => ['openalex' => $openalex, 'history' => $history]]);

    header('Location: ' . ROOTPATH . '/activities/view/' . $id);
}, 'login');
