<?php

/**
 * Routing for export
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


Route::get('/reports', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        // ['name' => 'Export', 'path' => "/export"],
        ['name' => lang("Reports", "Berichte")]
    ];
    if (!$Settings->hasPermission('report.generate')) {
        abortwith(403, lang('You do not have permission to generate reports.', 'Du hast keine Berechtigung, Berichte zu erstellen.'), "/", lang('Go back', 'Zurück'));
    }
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
    if (!$Settings->hasPermission('report.templates')) {
        abortwith(403, lang('You do not have permission to manage report templates.', 'Du hast keine Berechtigung, Berichtsvorlagen zu verwalten.'), "/reports", lang('Go back', 'Zurück'));
    }
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
    if (!$Settings->hasPermission('report.templates')) {
        abortwith(403, lang('You do not have permission to manage report templates.', 'Du hast keine Berechtigung, Berichtsvorlagen zu verwalten.'), "/", lang('Go back', 'Zurück'));
    }

    $report = [];
    $title = '';
    $steps = [];

    if (DB::is_ObjectID($id)) {
        $report = $osiris->adminReports->findOne(['_id' => DB::to_ObjectID($id)]);
        $title = $report['title'];
        $steps = $report['steps'];
    }

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
    if (!$Settings->hasPermission('report.templates')) {
        abortwith(403, lang('You do not have permission to manage report templates.', 'Du hast keine Berechtigung, Berichtsvorlagen zu verwalten.'), "/", lang('Go back', 'Zurück'));
    }
    $report = $osiris->adminReports->findOne(['_id' => DB::to_ObjectID($id)]);
    if (empty($report)) {
        abortwith(404, lang('Report', 'Bericht'), "/admin/reports");
    }

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
    $_SESSION['msg'] = lang("Report template has been created successfully.", "Berichtsvorlage wurde erfolgreich erstellt.");
    $_SESSION['msg_type'] = "success";
    header("Location: " . ROOTPATH . "/admin/reports/builder/$id");
}, 'login');


Route::post('/crud/reports/delete', function () {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['id'])) {
        die('No id provided');
    }
    $id = $_POST['id'];
    $osiris->adminReports->deleteOne(['_id' => DB::to_ObjectID($id)]);

    $_SESSION['msg'] = lang("Report template has been deleted successfully.", "Berichtsvorlage wurde erfolgreich gelöscht.");
    $_SESSION['msg_type'] = "success";
    header("Location: " . ROOTPATH . "/admin/reports");
}, 'login');


Route::post('/crud/reports/update', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['id'])) {
        die('No ID provided');
    }
    $id = $_POST['id'];

    $title = $_POST['title'];
    $values = $_POST['values'];
    if (empty($values)) {
        $steps = [];
    } else {
        $steps = array_values($values);
    }
    // array_values for steps.sort, because the indexes might be non-consecutive
    foreach ($steps as &$step) {
        $step['sort'] = array_values($step['sort'] ?? []);
    }

    $varsIn = $_POST['variables'] ?? [];
    $varsOut = [];
    foreach ($varsIn as $v) {
        $key = trim($v['key'] ?? '');
        if ($key === '') continue;
        $type = $v['type'] ?? 'string';
        $def  = $v['default'] ?? null;

        // cast default by type
        if ($type === 'int' && $def !== '' && $def !== null)   $def = (int)$def;
        if ($type === 'bool' && $def !== '' && $def !== null)  $def = filter_var($def, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($type === 'float' && $def !== '' && $def !== null) $def = (float)$def;

        $varsOut[$key] = [
            'key'    => $key,
            'type'   => $type,
            'label'  => $v['label'] ?? '',
            'default' => $def
        ];
    }
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
                'steps' => $steps,
                'variables' => array_values($varsOut)
            ]
        ]
    );

    $_SESSION['msg'] = lang("Report template has been updated successfully.", "Berichtsvorlage wurde erfolgreich aktualisiert.");
    $_SESSION['msg_type'] = "success";
    header("Location: " . ROOTPATH . "/admin/reports/builder/$id");
}, 'login');


Route::post('/crud/reports/update-order', function () {
    include_once BASEPATH . "/php/init.php";
    $collection = $osiris->adminReports;

    foreach ($_POST['order'] as $i => $id) {
        $collection->updateOne(
            ['_id' => DB::to_ObjectID($id)],
            ['$set' => ['order' => $i]]
        );
    }

    $_SESSION['msg'] = lang("Order updated", "Reihenfolge aktualisiert");
    $_SESSION['msg_type'] = 'success';
    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        header("Location: " . $_POST['redirect']);
        die();
    }
});


// Report export


Route::post('/reports', function () {
    // Keep warnings and incidental output out of both export formats.
    $bufferLevel = ob_get_level();
    $previousDisplayErrors = ini_set('display_errors', '0');
    $temporaryFile = null;
    ob_start();
    try {
        require_once BASEPATH . '/php/init.php';
        if (!isset($_POST['id'])) {
            abortwith(500, lang('No report ID provided.', 'Keine Bericht-ID angegeben.'), "/reports");
        }
        $id = $_POST['id'];
        $report = $osiris->adminReports->findOne(['_id' => DB::to_ObjectID($id)]);
        if (empty($report)) {
            abortwith(404, lang('Report not found.', 'Bericht nicht gefunden.'), "/reports", lang('Go back to reports', 'Zurück zu den Berichten'));
        }

        require_once BASEPATH . '/php/ReportExport.php';
        $export = new ReportExport();

        // Creating the new document...
        \PhpOffice\PhpWord\Settings::setZipClass(\PhpOffice\PhpWord\Settings::PCLZIP);
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);


        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->getSettings()->setUpdateFields(true);

        // Apply export design styles
        include_once BASEPATH . "/php/ReportTemplate.php";
        $ReportTemplate = new ReportTemplate();
        $exportStyle = $ReportTemplate->getStyle();
        $ReportTemplate->applyReportStyle($phpWord);

        // Adding an empty Section to the document...
        $section = $ReportTemplate->addReportSection($phpWord);

        $ReportTemplate->addReportFooter($section);

        $styleCell = ['valign' => 'center'];
        $styleText = [];
        $styleTextBold = ['bold' => true];
        $styleParagraph =  ['spaceBefore' => 0, 'spaceAfter' => 0];
        $styleParagraphCenter =  ['spaceBefore' => 0, 'spaceAfter' => 0, 'align' => 'center'];
        $styleParagraphRight =  ['spaceBefore' => 0, 'spaceAfter' => 0, 'align' => 'right'];


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

        // set time and variables for the report
        $Report->setTime($startyear, $endyear, $startmonth, $endmonth);
        $vars = $_POST['var'] ?? [];
        $Report->setVariables($vars);

        // Generate report content
        foreach ($Report->steps as $stepIndex => $step) {
            $export->setBlock($stepIndex + 1, $step);
            try {
                $export->checked(function () use ($step, $Report, $export, $section, $styleCell, $styleText, $styleTextBold, $styleParagraph, $styleParagraphCenter, $styleParagraphRight) {
                    switch ($step['type']) {
                        case 'toc':
                            $section->addTOC();
                            break;
                        case 'text':
                            $text = $Report->getText($step);
                            $level = $step['level'] ?? 'p';
                            switch ($level) {
                                case 'h1':
                                    // Add h1 as a heading, not as a paragraph
                                    $textrun = new \PhpOffice\PhpWord\Element\TextRun(['styleName' => 'Heading1']);
                                    $export->addHtml($textrun, $text);
                                    $section->addTitle($textrun, 1);
                                    break;
                                case 'h2':
                                    $textrun = new \PhpOffice\PhpWord\Element\TextRun(['styleName' => 'Heading2']);
                                    $export->addHtml($textrun, $text);
                                    $section->addTitle($textrun, 2);
                                    break;
                                case 'h3':
                                    $textrun = new \PhpOffice\PhpWord\Element\TextRun(['styleName' => 'Heading3']);
                                    $export->addHtml($textrun, $text);
                                    $section->addTitle($textrun, 3);
                                    break;
                                case 'h4':
                                    $textrun = new \PhpOffice\PhpWord\Element\TextRun(['styleName' => 'Heading4']);
                                    $export->addHtml($textrun, $text);
                                    $section->addTitle($textrun, 4);
                                    break;
                                default:
                                    $run = $section->addTextRun();
                                    $export->addHtml($run, $text);
                                    break;
                            }
                            break;
                        case 'line':
                            $section->addTextBreak(1);
                            break;
                        case 'list':
                            $list = $Report->prepareList($step);
                            // Only lists with additional columns contain a header row.
                            $hasHeader = !empty($list) && count($list[0]) > 1;
                            if (empty($list) || ($hasHeader && count($list) === 1)) {
                                $name = $step['name'] ?? lang('List', 'Liste');
                                $section->addText(lang('No data available for the selected criteria of ' . $name . '.', 'Keine Daten für die ausgewählten Kriterien von ' . $name . ' verfügbar.'), ['italic' => true]);
                                break;
                            }
                            if ($hasHeader) {
                                $table = $section->addTable('ReportTable');
                                // table head
                                $table->addRow();
                                foreach ($list[0] as $h) {
                                    $cell = $table->addCell(3000, $styleCell);
                                    $line = clean_comment_export($h);
                                    $export->addHtml($cell, $line);
                                    // ->addText($h, $styleTextBold, $styleParagraph);
                                }
                                // table body
                                foreach (array_slice($list, 1) as $row) {
                                    $table->addRow();
                                    foreach ($row as $text) {
                                        $style = $styleParagraph;
                                        if (is_numeric($text)) {
                                            $style = $styleParagraphRight;
                                        }
                                        $cell = $table->addCell(3000, $styleCell); //->addText($cell, $styleText, $style);
                                        $line = clean_comment_export($text);
                                        $export->addHtml($cell, $line);
                                    }
                                }
                                break;
                            } else {
                                foreach ($list as $element) {
                                    $line = $element[0];
                                    $paragraph = $section->addTextRun();
                                    $export->addHtml($paragraph, $line);
                                }
                            }
                            break;
                        case 'activities':
                            $data = $Report->getActivities($step);
                            foreach ($data as $d) {
                                $paragraph = $section->addTextRun();
                                $line = clean_comment_export($d, false);
                                $export->addHtml($paragraph, $line);
                            }
                            break;
                        case 'activities-field':
                            $field = $step['field'] ?? 'impact';
                            $data = $Report->getActivities($step, $field);
                            $table = $section->addTable();
                            $table->addRow();
                            $cell = $table->addCell(9000);
                            $cell = $table->addCell(1000);
                            $label = $Report->fields[$field]['label'] ?? $field;
                            $cell->addText($label, ['bold' => true, 'underline' => 'single'], $styleParagraphCenter);
                            foreach ($data as $d) {
                                [$line, $val] = $d;
                                $table->addRow();
                                $cell = $table->addCell(9000);
                                $line = clean_comment_export($line);
                                $export->addHtml($cell, $line);
                                $cell = $table->addCell(1000);
                                $cell->addText($val, $styleTextBold, $styleParagraphCenter);
                            }
                            break;
                        case 'table':
                            $result = $Report->getTable($step);

                            if (count($result) > 0) {
                                $table = $section->addTable('ReportTable');

                                // table head
                                $table->addRow();
                                foreach ($result[0] as $h) {
                                    $run = $table->addCell(2000, $styleCell)->addTextRun($styleParagraph);
                                    $export->addHtml($run, '<b>' . $h . '</b>');
                                }
                                // table body
                                foreach (array_slice($result, 1) as $row) {
                                    $table->addRow();
                                    foreach ($row as $cell) {
                                        $style = $styleParagraph;
                                        if (is_numeric($cell)) {
                                            $style = $styleParagraphRight;
                                        }
                                        $run = $table->addCell(2000, $styleCell)->addTextRun($style);
                                        $export->addHtml($run, $cell);
                                    }
                                }
                            }
                            break;
                        default:
                            throw new InvalidArgumentException('Unknown report step type: ' . ($step['type'] ?? 'unknown'));
                    }
                });
            } catch (Throwable $error) {
                $export->issue($section,
                    lang('This block could not be fully exported. Please check its results.',
                        'Dieser Baustein konnte nicht vollständig exportiert werden. Bitte die Ergebnisse prüfen.'),
                    $error->getMessage());
            }
        }


        // Finish serialization before sending any download headers or bytes.
        $isHtml = ($_POST['format'] ?? 'word') === 'html';
        $temporaryFile = tempnam(sys_get_temp_dir(), 'osiris-report-');
        if ($temporaryFile === false) {
            throw new RuntimeException('Could not create a temporary report file.');
        }
        $export->checked(function () use ($phpWord, $isHtml, $temporaryFile) {
            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, $isHtml ? 'HTML' : 'Word2007');
            $writer->save($temporaryFile);
        });
        $fileSize = filesize($temporaryFile);
        if ($fileSize === false || $fileSize === 0) {
            throw new RuntimeException('The report writer did not produce a document.');
        }

        if ($export->getIssues()) {
            $_SESSION['msg'] = lang(
                'The report was exported with warnings. Please check the marked blocks in the document.',
                'Der Bericht wurde mit Warnungen exportiert. Bitte die markierten Bausteine im Dokument prüfen.'
            );
            $_SESSION['msg_type'] = 'warning';
        }

        // Discard incidental output collected during generation, never the document.
        while (ob_get_level() > $bufferLevel) ob_end_clean();
        if ($isHtml) {
            header('Content-Type: text/html; charset=utf-8');
        } else {
            $filename = preg_replace('/[\x00-\x1F\x7F"\\\\\/]/', '_', $report['title'] . '_' . $year) . '.docx';
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        }
        header('Cache-Control: no-store');
        header('Content-Length: ' . $fileSize);
        readfile($temporaryFile);
    } catch (Throwable $error) {
        error_log('Report export failed: ' . $error->getMessage());
        while (ob_get_level() > $bufferLevel) ob_end_clean();
        $_SESSION['msg'] = lang(
            'The report could not be exported. Please check the template or contact support.',
            'Der Bericht konnte nicht exportiert werden. Bitte die Vorlage prüfen oder den Support kontaktieren.'
        );
        $_SESSION['msg_type'] = 'error';
        header('Location: ' . ROOTPATH . '/reports', true, 303);
    } finally {
        if (is_string($temporaryFile) && is_file($temporaryFile)) unlink($temporaryFile);
        while (ob_get_level() > $bufferLevel) ob_end_clean();
        ini_set('display_errors', $previousDisplayErrors);
    }
}, 'login');
