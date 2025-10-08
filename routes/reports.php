<?php

/**
 * Routing for export
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


Route::get('/reports', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        // ['name' => 'Export', 'path' => "/export"],
        ['name' => lang("Reports", "Berichte")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/reports.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/reports', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang('Reports', 'Berichte'), 'path' => "/reports"],
        ['name' => lang('Templates', 'Vorlagen')],
    ];
    $page = 'reports-templates';
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/reports-templates.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/reports/builder/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang('Reports', 'Berichte'), 'path' => "/reports"],
        ['name' => lang('Templates', 'Vorlagen'), 'path' => "/admin/reports"],
        ['name' => lang("Builder", "Editor")]
    ];

    $report = [];
    $title = '';
    $steps = [];

    if (DB::is_ObjectID($id)) {
        $report = $osiris->adminReports->findOne(['_id' => DB::to_ObjectID($id)]);
        $title = $report['title'];
        $steps = $report['steps'];
    }

    $page = 'admin/reports';
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/report-builder.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/reports/preview/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang('Reports', 'Berichte'), 'path' => "/reports"],
        ['name' => lang('Templates', 'Vorlagen'), 'path' => "/admin/reports"],
        ['name' => lang('Builder', 'Editor'), 'path' => "/admin/reports/builder/$id"],
        ['name' => lang("Preview", "Vorschau")]
    ];
    if (!DB::is_ObjectID($id)) {
        die('The Report does not exist.');
    }
    $report = $osiris->adminReports->findOne(['_id' => DB::to_ObjectID($id)]);

    $page = 'admin/reports';
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/report-preview.php";
    include BASEPATH . "/footer.php";
}, 'login');



// CRUD

Route::post('/crud/reports/create', function () {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['title'])) {
        die('No title provided');
    }
    $insertOneResult = $osiris->adminReports->insertOne([
        'title' => $_POST['title'],
        'description' => $_POST['description'] ?? '',
        'start' => $_POST['start'] ?? 1,
        'duration' => $_POST['duration'] ?? 12,
        'steps' => []
    ]);
    $id = $insertOneResult->getInsertedId();
    header("Location: " . ROOTPATH . "/admin/reports/builder/$id?msg=success");
}, 'login');


Route::post('/crud/reports/delete', function () {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['id'])) {
        die('No id provided');
    }
    $id = $_POST['id'];
    $osiris->adminReports->deleteOne(['_id' => DB::to_ObjectID($id)]);

    header("Location: " . ROOTPATH . "/admin/reports?msg=deleted");
}, 'login');


Route::post('/crud/reports/update', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['id'])) {
        die('No ID provided');
    }
    $title = $_POST['title'];
    $values = $_POST['values'];
    if (empty($values)) {
        $steps = [];
    } else {
        $steps = array_values($values);
    }

    // dump($steps, true);
    $id = $_POST['id'];
    // upsert adminReports
    $osiris->adminReports->updateOne(
        [
            '_id' => DB::to_ObjectID($id)
        ],
        [
            '$set' => [
                'title' => $title,
                'description' => $_POST['description'] ?? '',
                'start' => $_POST['start'] ?? 1,
                'duration' => $_POST['duration'] ?? 12,
                'steps' => $steps
            ]
        ]
    );

    // id
    header("Location: " . ROOTPATH . "/admin/reports/builder/$id?msg=success");
}, 'login');


// Report export


Route::post('/reports', function () {
    // hide deprecated because PHPWord has a lot of them
    error_reporting(E_ERROR);
    // hide errors! otherwise they will break the word document
    if ($_POST['format'] == 'word') {
        // error_reporting(E_ERROR);
        ini_set('display_errors', 0);
    }
    require_once BASEPATH . '/php/init.php';
    if (!isset($_POST['id'])) {
        die('No Report ID provided');
    }
    $id = $_POST['id'];
    $report = $osiris->adminReports->findOne(['_id' => DB::to_ObjectID($id)]);
    if (empty($report)) {
        die('Report not found');
    }

    // select reportable data
    // $cursor = $DB->get_reportable_activities($_POST['start'], $_POST['end']);

    // Creating the new document...
    \PhpOffice\PhpWord\Settings::setZipClass(\PhpOffice\PhpWord\Settings::PCLZIP);
    \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
    $phpWord = new \PhpOffice\PhpWord\PhpWord();

    $phpWord->setDefaultFontName('Calibri');
    $phpWord->setDefaultFontSize(11);

    $phpWord->addNumberingStyle(
        'hNum',
        array(
            'type' => 'multilevel',
            'levels' => array(
                array('pStyle' => 'Heading1', 'format' => 'decimal', 'text' => '%1'),
                array('pStyle' => 'Heading2', 'format' => 'decimal', 'text' => '%1.%2'),
                array('pStyle' => 'Heading3', 'format' => 'decimal', 'text' => '%1.%2.%3'),
            )
        )
    );

    $phpWord->addTitleStyle(1, ["bold" => true, "size" => 16], ["spaceBefore" => 8, 'numStyle' => 'hNum', 'numLevel' => 0]);
    $phpWord->addTitleStyle(2, ["bold" => true, "size" => 14], ["spaceBefore" => 8, 'numStyle' => 'hNum', 'numLevel' => 1]);
    $phpWord->addTitleStyle(3, ["bold" => true, "size" => 14], ["spaceBefore" => 8, 'numStyle' => 'hNum', 'numLevel' => 2]);

    $phpWord->addTableStyle('ReportTable', ['borderSize' => 1, 'borderColor' => 'grey', 'cellMargin' => 80]);

    $styleCell = ['valign' => 'center'];
    $styleText = [];
    $styleTextBold = ['bold' => true];
    $styleParagraph =  ['spaceBefore' => 0, 'spaceAfter' => 0];
    $styleParagraphCenter =  ['spaceBefore' => 0, 'spaceAfter' => 0, 'align' => 'center'];
    $styleParagraphRight =  ['spaceBefore' => 0, 'spaceAfter' => 0, 'align' => 'right'];

    // Adding an empty Section to the document...
    $section = $phpWord->addSection();


    require_once BASEPATH . '/php/Report.php';
    $Report = new Report($report);
    $year = $_POST['startyear'];

    $startyear = $year;
    $endyear = $year;
    $startmonth = $_POST['startmonth'] ?? $Report->report['start'] ?? 1;
    $duration = $_POST['duration'] ?? $Report->report['duration'] ?? 12;
    $endmonth = $startmonth + $duration - 1;
    if ($endmonth > 12) {
        $endmonth -= 12;
        $endyear++;
    }

    $Report->setTime($startyear, $endyear, $startmonth, $endmonth);

    foreach ($Report->steps as $step) {
        switch ($step['type']) {
            case 'text':
                $text = $Report->getText($step);
                $level = $step['level'] ?? 'p';
                switch ($level) {
                    case 'h1':
                        $section->addTitle($text, 1);
                        break;
                    case 'h2':
                        $section->addTitle($text, 2);
                        break;
                    case 'h3':
                        $section->addTitle($text, 3);
                        break;
                    default:
                        $paragraph = $section->addTextRun();
                        // $text = clean_comment_export($text, false);
                        \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $text, false, false);
                        break;
                }
                break;
            case 'line':
                $section->addTextBreak(1);
                break;
            case 'activities':
                $data = $Report->getActivities($step);
                foreach ($data as $d) {
                    $paragraph = $section->addTextRun();
                    $line = clean_comment_export($d, false);
                    \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line, false, false);
                }
                break;
            case 'activities-impact':
                $data = $Report->getActivities($step, $impact = true);
                $table = $section->addTable();
                $table->addRow();
                $cell = $table->addCell(9000);
                $cell = $table->addCell(1000);
                $cell->addText('Impact Factor', ['bold' => true, 'underline' => 'single'], $styleParagraphCenter);

                foreach ($data as $d) {
                    [$line, $impact] = $d;
                    $table->addRow();
                    $cell = $table->addCell(9000);
                    $line = clean_comment_export($line);
                    \PhpOffice\PhpWord\Shared\Html::addHtml($cell, $line, false, false);
                    if (isset($impact)) {
                        $if = $impact;
                    } else {
                        $if = $DB->get_impact($doc);
                    }
                    if (empty($if)) {
                        $if = "IF not yet available";
                    } else {
                        $if = number_format($if, 3, ',', '.');
                    }
                    $cell = $table->addCell(1000);
                    $cell->addText($if, $styleTextBold, $styleParagraphCenter);
                }
                break;
            case 'table':
                $result = $Report->getTable($step);

                if (count($result) > 0) {
                    $table = $section->addTable('ReportTable');

                    // table head
                    $table->addRow();
                    foreach ($result[0] as $h) {
                        $table->addCell(2000, $styleCell)->addText($h, $styleTextBold, $styleParagraph);
                    }
                    // table body
                    foreach (array_slice($result, 1) as $row) {
                        $table->addRow();
                        foreach ($row as $cell) {
                            $style = $styleParagraph;
                            if (is_numeric($cell)) {
                                $style = $styleParagraphRight;
                            }
                            $table->addCell(2000, $styleCell)->addText($cell, $styleText, $style);
                        }
                    }
                }
                break;
        }
    }

    $html = clean_comment_export($html);
    \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);

    // Save file
    if ($_POST['format'] == 'html') {
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
        $objWriter->save('php://output');
        die;
    }

    // Download file
    $filename = $report['title'] . '_' . $year . '.docx';
    header("Content-Description: File Transfer");
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $xmlWriter->save("php://output");
}, 'login');
