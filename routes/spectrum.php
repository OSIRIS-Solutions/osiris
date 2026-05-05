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
