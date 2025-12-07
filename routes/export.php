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


Route::get('/download', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        // ['name' => 'Export', 'path' => "/export"], 
        ['name' => lang("Download")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/download.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/cart', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        // ['name' => 'Export', 'path' => "/export"], 
        ['name' => lang("Collection", "Sammlung")]
    ];
    if (isset($_GET['empty']) && $_GET['empty'] == 1) {
        // empty cart
        emptyCart();
        $_SESSION['msg'] = lang("Collection emptied.", "Sammlung geleert.");
        header("Location: " . ROOTPATH . "/cart");
        exit();
    }

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/cart.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/export/(.*)/(.*)', function ($field, $type) {
    // error_reporting(E_ERROR | E_PARSE);

    require_once BASEPATH . '/php/init.php';
    require_once BASEPATH . '/php/Document.php';


    $collection = $osiris->activities;
    $options = ['sort' => ["subtype" => 1, $field => 1], 'projection' => [$field => 1, 'subtype' => 1]];
    $filter = [
        "type" => $type
    ];

    if (isset($_GET['year'])) $filter['year'] = $_GET['year'];

    $cursor = $collection->find($filter, $options);

    $t = "";
    foreach ($cursor as $doc) {
        if ($doc['subtype'] != $t) {
            echo "<h2>$doc[subtype]</h2>";
            $t = $doc['subtype'];
        }
        echo $doc[$field] . "<br>";
    }
});



Route::get('/sws-analysis', function () {
    include_once BASEPATH . "/php/init.php";
    $year = ['$exists' => true];
    if (isset($_GET['year'])) {
        $year = intval($_GET['year']);
    }
    $sws = $osiris->activities->aggregate([
        ['$match' => ['authors.sws' => ['$exists' => true], 'year' => $year]],
        // ['$project' => ['authors' => 1, 'title'=>1, 'start'=>1]],
        ['$unwind' => '$authors'],
        ['$match' => ['authors.aoi' => ['$in' => [true, 'true', 1, '1']]]],
    ])->toArray();
    echo "<table>";
    echo "<tr><thead>";
    echo "<th>Titel</th>";
    echo "<th>Start</th>";
    echo "<th>Ende</th>";
    echo "<th>Betreuende Person</th>";
    echo "<th>SWS</th>";
    echo "</tr></thead>";
    foreach ($sws as $a) {
        echo "<tr>";
        echo "<td>$a[title]</td>";
        echo "<td>" . format_date($a['start']) . "</td>";
        echo "<td>" . format_date($a['end'] ?? $a['start']) . "</td>";
        echo "<td>" . $a["authors"]["last"] . ", " . $a["authors"]["first"] . "</td>";
        echo "<td>" . $a["authors"]["sws"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    // dump($sws, true);
});


Route::get('/guest-analysis', function () {
    include_once BASEPATH . "/php/init.php";
    $year = ['$exists' => true];
    if (isset($_GET['year'])) {
        $year = intval($_GET['year']);
    }
    $sws = $osiris->activities->aggregate([
        ['$match' => ['subtype' => 'guests', 'year' => $year]],
        // ['$project' => ['authors' => 1, 'title'=>1, 'start'=>1]],
    ])->toArray();
    echo "<table>";
    echo "<tr><thead>";
    echo "<th>Titel</th>";
    echo "<th>Start</th>";
    echo "<th>Ende</th>";
    echo "<th>Name der Person</th>";
    echo "<th>Affiliation</th>";
    echo "<th>Land</th>";
    echo "</tr></thead>";
    foreach ($sws as $a) {
        echo "<tr>";
        echo "<td>$a[title]</td>";
        echo "<td>" . format_date($a['start']) . "</td>";
        echo "<td>" . format_date($a['end'] ?? $a['start']) . "</td>";
        echo "<td>" . $a['name'] . "</td>";
        echo "<td>" . ($a["affiliation"] ?? '') . "</td>";
        echo "<td>" . ($a["country"] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    // dump($sws, true);
});


Route::get('/download/user-groups', function () {
    error_reporting(E_ERROR | E_PARSE);

    require_once BASEPATH . '/php/init.php';

    $users = $osiris->persons->find(['is_active' => ['$ne' => false]], ['projection' => ['username' => 1, 'formalname' => 1, 'units' => 1], 'sort' => ['formalname' => 1]]);

    header("Content-Type: text/plain; charset=utf-8");
    header('Content-Disposition: attachment; filename="user-groups.csv"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');

    echo "Username;Name;Groups" . PHP_EOL;
    foreach ($users as $u) {
        $units = [];
        foreach ($Groups->getPersonUnit($u['username'], null, false, false) as $unit) {
            $units[] = $Groups->getName($unit['unit']);
        }
        echo "$u[username];$u[formalname];" . implode(", ", $units) . PHP_EOL;
    }
});

Route::post('/download', function () {
    error_reporting(E_ERROR | E_PARSE);

    require_once BASEPATH . '/php/init.php';
    require_once BASEPATH . '/php/Document.php';

    $params = $_POST['filter'] ?? array();


    $timefilter = false;
    $filename = "osiris";

    $highlight = false;
    if ($_POST['format'] == 'word' && isset($_POST['highlight']) && !empty($_POST['highlight'])) {
        if ($_POST['highlight'] == 'user') {
            $highlight = $_SESSION['username'];
        } elseif ($_POST['highlight'] == 'aoi') {
            $highlight = true;
        }
    }
    $Format = new Document($highlight, 'word');
    // $Format->full = true;

    $categories = $Categories->categories;
    $order = array_column($categories, 'id');

    // select data
    $collection = $osiris->activities;
    $options = ['sort' => ["type" => 1, "year" => 1, "month" => 1]];
    // date_desc
    // date_asc
    // type_asc
    // type_desc
    // title_asc
    // title_desc
    if (isset($_POST['sortby']) && !empty($_POST['sortby'])) {
        $sortfield = 'year';
        $sortorder = 1;
        if ($_POST['sortby'] == 'date_desc') {
            $sortorder = -1;
        } elseif ($_POST['sortby'] == 'type_asc') {
            $sortfield = 'type';
        } elseif ($_POST['sortby'] == 'type_desc') {
            $sortfield = 'type';
            $sortorder = -1;
        } elseif ($_POST['sortby'] == 'title_asc') {
            $sortfield = 'title';
        } elseif ($_POST['sortby'] == 'title_desc') {
            $sortfield = 'title';
            $sortorder = -1;
        } else if ($_POST['sortby'] == 'print_asc') {
            $sortfield = 'rendered.print';
            $sortorder = 1;
        } else if ($_POST['sortby'] == 'print_desc') {
            $sortfield = 'rendered.print';
            $sortorder = -1;
        }

        $options['sort'] = [$sortfield => $sortorder];
    }
    // dump($params, true);
    $filter = [];
    if (isset($params['type']) && !empty($params['type'])) {
        $filter['$and'][] = array('type' => trim($params['type']));
        $filename .= "_" . trim($params['type']);
    }
    if (isset($params['user']) && !empty($params['user'])) {
        $filter['$and'][] = array('$or' => [['authors.user' => $params['user']], ['editors.user' => $params['user']]]);
        $filename .= "_" . trim($params['user']);
    }
    if (isset($params['dept']) && !empty($params['dept'])) {
        $filter['$and'][] = array('units' => $params['dept']);
        $filename .= "_" . trim($params['dept']);
    }
    if (isset($params['id']) && !empty($params['id'])) {
        $id = DB::to_ObjectID($params['id']);
        $filter['$and'][] = array('_id' => $id);
        $filename .= "_" . trim($params['id']);
    }

    if (isset($params['project']) && !empty($params['project'])) {
        $filter['$and'][] = array('projects' => trim($params['project']));
        $filename .= "_" . trim($params['project']);
    }

    if (isset($params['topic']) && !empty($params['topic'])) {
        $filter['$and'][] = array('topics' => trim($params['topic']));
        $filename .= "_" . trim($params['topic']);
    }

    if (isset($params['workflow']) && !empty($params['workflow'])) {
        if ($params['workflow'] == 'verified-or-empty') {
            $filter['$and'][] = array('$or' => [['workflow.status' => 'verified'], ['workflow' => ['$exists' => false]], ['workflow' => null]]);
        } else {
            $filter['$and'][] = array('workflow.status' => trim($params['workflow']));
        }
        $filename .= "_" . trim($params['workflow']);
    }

    // if (isset($params['year']) && !empty($params['year'])) {
    //     $filter['year'] = intval($params['year']);
    // }

    if (isset($params['time']) && !empty($params['time'])) {
        $timefilter = true;
        $startyear = intval($params['time']['from']['year']);
        $endyear = intval($params['time']['to']['year']);
        $startmonth = intval($params['time']['from']['month']);
        $endmonth = intval($params['time']['to']['month']);

        if (!empty($startyear) && !empty($endyear)) {
            // this is for the monthly filter below.
            // if we want to see the whole year, we must not filter
            if (($startmonth == 1 && $endmonth == 12) || (empty($startmonth) && empty($endmonth))) $timefilter = false;

            // create year range from start to end
            $years = [];
            for ($i = $startyear; $i <= $endyear; $i++) {
                $years[] = intval($i);
            }

            $filter['$and'][] =   array(
                '$or' => [
                    [
                        "start.year" => array('$lte' => intval($startyear)),
                        '$and' => array(
                            ['$or' => array(
                                ['end.year' => array('$gte' => intval($endyear))],
                                ['end' => null]
                            )],
                            ['$or' => array(
                                ['type' => 'misc', 'subtype' => 'annual'],
                                ['type' => 'review', 'subtype' =>  'editorial'],
                            )]
                        )
                    ],
                    ['year' => ['$in' => $years]]
                ]
            );
            $filename .= "_" . implode("-", $years);
        }
    }

    if (isset($_POST['cart'])) {
        $cursor = array();
        $cart = readCart();
        if (!empty($cart)) {
            foreach ($cart as $id) {
                $mongo_id = DB::to_ObjectID($id);
                $cursor[] = $osiris->activities->findOne(['_id' => $mongo_id]);
            }
            $filename .= "_cart";
        } else {
            die("Cart is empty!");
        }
    } else {
        $cursor = $collection->find($filter, $options);
    }

    $headers = [];

    if ($_POST['format'] == "word") {
        // Creating the new document...
        \PhpOffice\PhpWord\Settings::setZipClass(\PhpOffice\PhpWord\Settings::PCLZIP);
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $phpWord->addTitleStyle(1, ["bold" => true, "size" => 16], ["spaceBefore" => 12]);
        $phpWord->addTitleStyle(2, ["bold" => true, "size" => 14], ["spaceBefore" => 8]);
        $phpWord->addTitleStyle(3, ["bold" => false, "size" => 11], ["spaceBefore" => 0]);
        // $phpWord->setOutputEscapingEnabled(true);
        /* Note: any element you append to a document must reside inside of a Section. */
        // $phpWord->addTableStyle('CVTable', ['borderSize' => 0, 'borderColor' => '#ffffff', 'cellMargin' => 60, 'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50]);
        $table_style = new \PhpOffice\PhpWord\Style\Table;
        $table_style->setBorderColor('ffffff');
        $table_style->setBorderSize(0);
        $table_style->setUnit(\PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT);
        $table_style->setWidth(100 * 50);

        $styleCell = ['borderColor' => 'ffffff', 'borderSize' => 0]; //'valign' => 'center'
        $styleText = [];
        $styleTextBold = ['bold' => true];
        $styleParagraph =  ['spaceBefore' => 0, 'spaceAfter' => 0];
        $styleParagraphCenter =  ['spaceBefore' => 0, 'spaceAfter' => 0, 'align' => 'center'];


        $headerlvl = 1;
        // Adding an empty Section to the document...
        $section = $phpWord->addSection();
        // CV
        if (isset($_POST['type']) && $_POST['type'] == 'cv') {
            $headerlvl = 2;

            $scientist = $DB->getPerson($params['user']);

            $section->addTitle($scientist['displayname'], 1);
            $section->addTitle(lang($scientist['position'] ?? '', $scientist['position_de'] ?? null), 3);

            // add break
            $section->addTextBreak(1, $styleParagraphCenter);

            $filename = "CV_" . str_replace(' ', '', $scientist['last']);

            if (isset($scientist['research']) && !empty($scientist['research'])) {
                $section->addTitle(lang('Research interest', 'Forschungsinteressen'), 2);
                foreach ($scientist['research'] as $key) {
                    $paragraph = $section->addListItemRun(0);
                    $line = clean_comment_export($key, false);
                    \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line);
                }
            }

            if (isset($scientist['cv']) && !empty($scientist['cv'])) {
                $section->addTitle(lang('Curriculum Vitae'), 2);

                $table = $section->addTable($table_style);


                foreach ($scientist['cv'] as $entry) {

                    $table->addRow();
                    $cell = $table->addCell(1600, $styleCell);
                    $cell->addText($entry['time'],  ['bold' => false], $styleParagraph);
                    $cell = $table->addCell(5000, $styleCell);
                    $cell->addText($entry['position'],  ['bold' => true], $styleParagraph);

                    $table->addRow();
                    $cell = $table->addCell(1600, $styleCell);
                    $cell = $table->addCell(5000, $styleCell);
                    $cell->addText($entry['affiliation'],  ['italic' => true], $styleParagraph);
                }
            }

            if (isset($scientist['keywords']) && !empty($scientist['keywords'])) {
                $kw_name = $Settings->get('staff-keyword-name', 'Keywords');
                $section->addTitle($kw_name, 2);
                foreach ($scientist['keywords'] as $key) {
                    $paragraph = $section->addListItemRun(0);
                    $paragraph->addText($key, ['bold' => false], $styleParagraph);
                }
            }

            if (isset($scientist['research_profile']) && !empty($scientist['research_profile'])) {
                $section->addTitle(lang('Research profile', 'Forschungsprofil'), 2);
                $paragraph = $section->addTextRun($styleParagraph);
                $line = clean_comment_export(lang($scientist['research_profile'], $scientist['research_profile_de'] ?? null), false);
                \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line, false, false);
            }

            if (isset($scientist['biography']) && !empty($scientist['biography'])) {
                $section->addTitle(lang('Biography', 'Biografie'), 2);
                $paragraph = $section->addTextRun($styleParagraph);
                $line = clean_comment_export(lang($scientist['biography'], $scientist['biography_de'] ?? null), false);
                \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line, false, false);
            }

            if (isset($scientist['education']) && !empty($scientist['education'])) {
                $section->addTitle(lang('Education', 'Ausbildung'), 2);
                $paragraph = $section->addTextRun($styleParagraph);
                $line = clean_comment_export(lang($scientist['education'], $scientist['education_de'] ?? null), false);
                \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line, false, false);
            }
        }


        // sort the elements
        if ($cursor instanceof MongoDB\Driver\Cursor) {
            $cursor = $cursor->toArray();
        }
        usort($cursor, function ($a, $b) use ($order) {
            // TODO: sort undefined to the back
            $pos_a = array_search($a['type'], $order);
            $pos_b = array_search($b['type'], $order);
            return $pos_a - $pos_b;
        });

        // dump($endmonth);
        foreach ($cursor as $doc) {
            // filtering by month is to much effort, so we just do not show activities out
            if ($timefilter && $startyear == $doc['year'] && $startmonth > $doc['month']) continue;
            // dump($doc['month']);

            if ($timefilter && $endyear == $doc['year'] && $endmonth < $doc['month']) continue;
            if (!in_array($doc['type'], $headers)) {
                $headers[] = $doc['type'];
                $section->addTitle($Settings->title($doc['type']), $headerlvl);
            }
            $Format->setDocument($doc);
            $paragraph = $section->addTextRun();
            $line = $Format->format();
            $line = clean_comment_export($line, false);
            \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line, false, false);
        }

        // Download file
        $file = $filename . '.docx';
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $xmlWriter->save("php://output");

        // Saving the document as OOXML file...
        // $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        // $objWriter->save('helloWorld.docx');
        // // Saving the document as HTML file...
        // $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
        // $objWriter->save('helloWorld.html');

    } else if ($_POST['format'] == "bibtex") {
        header("Content-Type: text/plain; charset=utf-8");
        header('Content-Disposition: attachment; filename="' . $filename . '.bib"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $bibentries = [
            'journal-article' => "article",
            'article' => "article",
            'Journal Article' => "article",
            'book' => "book",
            'chapter' => "inbook",
            "misc" => "misc"
        ];
        $ids = [];
        foreach ($cursor as $doc) {
            // filtering by month is to much effort, so we just do not show activities out
            if ($timefilter && $startyear == $doc['year'] && $startmonth < $doc['month']) continue;
            if ($timefilter && $endyear == $doc['year'] && $endmonth > $doc['month']) continue;

            // generate a unique ID 
            $id = $doc['authors'][0]['last'] . $doc['year'];
            $oid = $id;
            $i = 'a';
            while (in_array($id, $ids)) {
                // append letter if not unique
                $id = $oid . $i++;
            }
            $ids[] = $id;

            echo '@' . ($bibentries[trim($doc['subtype'] ?? $doc['pubtype'] ?? 'misc')] ?? 'misc') . '{' . $id . ',' . PHP_EOL;


            if (isset($doc['title']) and ($doc['title'] != '')) {
                echo '  Title = {' . strip_tags($doc['title']) . '},' . PHP_EOL;
            }
            if (isset($doc['authors']) and ($doc['authors'] != '')) {
                $authors = [];
                foreach ($doc['authors'] as $a) {
                    $author = $a['last'];
                    if (!empty($a['first'])) {
                        $author .= ", " . $a['first'];
                    }
                    $authors[] = $author;
                }
                echo '  Author = {' . implode(' and ', $authors) . '},' . PHP_EOL;
            }
            if (isset($doc['editors']) and ($doc['editors'] != '')) {
                $editors = [];
                foreach ($doc['editors'] as $a) {
                    $editors[] = Document::abbreviateAuthor($a['last'], $a['first']);
                }
                echo '  Editor = {' . implode(' and ', $editors) . '},' . PHP_EOL;
            }
            if (isset($doc['journal']) and ($doc['journal'] != '')) echo '  Journal = {' . $doc['journal'] . '},' . PHP_EOL;
            if (isset($doc['year']) and ($doc['year'] != '')) echo '  Year = {' . $doc['year'] . '},' . PHP_EOL;
            if (isset($doc['number']) and ($doc['number'] != '')) echo '  Number = {' . $doc['number'] . '},' . PHP_EOL;
            if (isset($doc['pages']) and ($doc['pages'] != '')) echo '  Pages = {' . $doc['pages'] . '},' . PHP_EOL;
            if (isset($doc['volume']) and ($doc['volume'] != '')) echo '  Volume = {' . $doc['volume'] . '},' . PHP_EOL;
            if (isset($doc['doi']) and ($doc['doi'] != '')) echo '  Doi = {' . $doc['doi'] . '},' . PHP_EOL;
            if (isset($doc['isbn']) and ($doc['isbn'] != '')) echo '  Isbn = {' . $doc['isbn'] . '},' . PHP_EOL;
            if (isset($doc['publisher']) and ($doc['publisher'] != '')) echo '  Publisher = {' . $doc['publisher'] . '},' . PHP_EOL;
            if (isset($doc['book']) and ($doc['book'] != '')) echo '  Booktitle = {' . $doc['book'] . '},' . PHP_EOL;
            // if (isset($doc['chapter']) and ($doc['chapter'] != '')) echo '  Chapter = {' . $doc['chapter'] . '},' . PHP_EOL;
            if (isset($doc['abstract']) and ($doc['abstract'] != '')) echo '  Abstract = {' . $doc['abstract'] . '},' . PHP_EOL;
            if (isset($doc['keywords']) and ($doc['keywords'] != '')) {
                echo '  Keywords = {';
                foreach ($doc['keywords'] as $keyword) echo $keyword . PHP_EOL;
                echo '},' . PHP_EOL;
            }

            echo '}' . PHP_EOL . PHP_EOL;
        }
    }
}, 'login');



function clean_comment_export($subject, $front_addition_text = '')
{

    // Clean escaped quotes
    $subject = str_replace('\"', '"', $subject);

    // Clean style tags
    $subject = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $subject);

    // Remove multiple br tags
    $subject = preg_replace('#(<br */?>\s*)+#i', '<br />', $subject);

    // Try and catch all the weird spaces...
    // $subject = str_replace(array("<br />", "<br/>"), "</p><p>", '<p>' . $subject . '</p>');

    // Sort the spacing
    $subject = preg_replace('#\R+#', "\r\n", preg_replace("/[\r\n]+/", "\r\n", $subject));

    // Remove Tabs 
    $subject = trim(preg_replace('/\t+/', '', $subject));

    // Remove multiple backslashes
    $subject = preg_replace('~\\+~', '', $subject);

    // Remove multiple single quote
    $subject = preg_replace("~'+~", "'", $subject);

    // Remove \' 
    $subject = str_replace("\'", "'", $subject);

    // Turn closing and opening p tags into breaks
    $subject = str_replace("</p><p>", "###", $subject);

    // Remove the double ######
    $subject = str_replace("######", "", $subject);

    // ...and ### ### as these happen apparently.
    $subject = str_replace("### ###", "###", $subject);

    // Remove nbsp
    // $subject = str_replace("&nbsp;", ' ', $subject);

    // Remove double spaces 
    $subject = preg_replace('!\s+!', ' ', $subject);

    // Strip all tags, except <b><i><strike><u><sub><sup>
    $subject = strip_tags($subject, '<b><i><u><strike><sub><sup><br>');

    // Remove any ### at the start
    $subject = rtrim(ltrim($subject, "#"), "#");

    // Add in the <p> tags in the text.
    $subject = str_replace("###", "</p><br/><p>", $subject);

    // Sort the breaks before and after formatting tags
    if ($front_addition_text !== false) {
        $subject = '<p>' . nl2br($front_addition_text . $subject) . '</p>';
    }

    return $subject;
}


Route::post('/reports/old', function () {
    // hide errors! otherwise they will break the word document
    if ($_POST['format'] == 'word') {
        error_reporting(E_ERROR);
        ini_set('display_errors', 0);
    }
    require_once BASEPATH . '/php/init.php';
    require_once BASEPATH . '/php/Document.php';
    $Format = new Document(true, 'word');
    $Format->full = true;

    // prepare user dict with all departments
    $users_cursor = $osiris->persons->find([], ['projection' => ['username' => 1, 'dept' => 1]]);
    $users = array();
    foreach ($users_cursor as $u) {
        $users[$u['username']] = $u['dept'];
    }

    // select reportable data
    $cursor = $DB->get_reportable_activities($_POST['start'], $_POST['end']);

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

    $phpWord->addTableStyle('CountTable', ['borderSize' => 1, 'borderColor' => 'grey', 'cellMargin' => 80]);

    $styleCell = ['valign' => 'center'];
    $styleText = [];
    $styleTextBold = ['bold' => true];
    $styleParagraph =  ['spaceBefore' => 0, 'spaceAfter' => 0];
    $styleParagraphCenter =  ['spaceBefore' => 0, 'spaceAfter' => 0, 'align' => 'center'];

    // Adding an empty Section to the document...
    $section = $phpWord->addSection();



    if ($_POST['style'] === 'research-report') {

        $result = [
            'publication' => [],
            'lecture' => [],
            'poster' => [],
            'review' => [],
            'misc' => [],
            'students' => [],
            'software' => []
        ];

        $depts = [
            "BUG" => "Bioeconomy and Health Research",
            "MIOS" => "Microorganisms",
            "Services" => "Services Microorganisms and Patent Deposits",
            "MÖD" => "Microbial Ecology and Diversity Research",
            "BIDB" => "Central Bioinformatics and Databases",
            "MIG" => "Microbial Genome Research (DZIF)",
            "MB" => "Junior Research Group: Microbial Biotechnology",
            "MuTZ" => "Human Cell Biology",
            "PFVI" => "Plant Virology",
            "VI" => "Junior Research Group: VirusInteract",
            "SPI" => "Science Policy and Internationalization",
        ];
        $countTables = [
            "autonom" => [],
            "collab" => [],
            "transfer" => []
        ];

        foreach ($cursor as $doc) {
            $Format->setDocument($doc);
            $doc['format'] = $Format->format();
            $doc['file'] = "";
            $type = $doc['type'];
            if ($type == 'misc' && $doc['subtype'] == 'misc-once') continue;
            if (in_array($type, ["software", "awards"])) {
                $type = 'misc';
            }

            if ($type == 'publication') {
                // get the category of research
                $cat = 'collab';
                // get the departments
                $dept = [];

                foreach ($doc['authors'] as $a) {
                    $aoi = boolval($a['aoi'] ?? false);
                    $pos = $a['position'] ?? 'middle';
                    if ($aoi) {
                        if ($pos == 'first' || $pos == 'last') {
                            $cat = 'autonom';
                            // break;
                        }
                        if (isset($a['user']) && isset($users[$a['user']])) {
                            $dept[] = $users[$a['user']];
                        }
                    }
                }

                $dept = array_count_values($dept);

                $pubtype = $doc['subtype'] ?? $doc['pubtype'] ?? 'article';
                if (!empty($pubtype) && in_array($pubtype, ['chapter', 'article'])) {
                    foreach ($dept as $d => $v) {
                        if (array_key_exists($d, $depts)) {
                            if (!array_key_exists($d, $countTables[$cat])) {
                                $countTables[$cat][$d] = ["chapter" => 0, "article" => 0];
                            }
                            $countTables[$cat][$d][$pubtype] += 1;
                        }
                    }
                } elseif (!empty($pubtype) && in_array($pubtype, ['magazine', 'other', 'preprint'])) {
                    $pubtype = 'magazine';
                    foreach ($dept as $d => $v) {
                        if (array_key_exists($d, $depts)) {
                            if (!array_key_exists($d, $countTables['transfer'])) {
                                $countTables['transfer'][$d] = ["magazine" => 0, "lecture" => 0, "poster" => 0];
                            }
                            $countTables['transfer'][$d]['magazine'] += 1;
                        }
                    }
                }

                $doc['cat'] = $cat;
                $doc['dept'] = $dept;
                if (array_key_exists('MuTZ', $dept)) $D = "MuTZ";
                else if (array_key_exists('PFVI', $dept)) $D = "PFVI";
                else $D = "MIOS";

                $result[$type][$cat][$D][$pubtype][] = $doc;
            } else if ($type == 'review') {
                switch (strtolower($doc['subtype'] ?? $doc['role'] ?? '')) {
                    case 'editorial':
                    case 'editor':
                        $type = "editorial";
                        break;
                    case 'grant-rev':
                        $type = "misc";
                        break;
                    case 'thesis-rev':
                        $type = "misc";
                        break;
                    default:
                        $type = 'review';
                        break;
                }
                if (isset($doc['journal'])) {
                    $journal = $DB->getJournalName($doc);
                    if (!empty($journal))
                        $result[$type][$journal][] = $DB->getTitleLastname($doc['authors'][0]['user'] ?? '');
                } else
                    $result['misc'][] = $doc;
            } else {

                $dept = [];

                if ($type == 'misc') {
                    $type = $doc['subtype'] ?? 'misc';
                }
                foreach ($doc['authors'] as $a) {
                    $aoi = boolval($a['aoi'] ?? false);
                    // $aoi_exists = $aoi_exists || $aoi;
                    if ($aoi) {
                        if (isset($a['user']) && isset($users[$a['user']])) {
                            $dept[] = $users[$a['user']];
                        }
                    }
                }
                // if (!$aoi_exists) continue;

                $dept = array_count_values($dept);

                if (isset($type) && in_array($type, ['lecture', 'poster'])) {
                    foreach ($dept as $d => $v) {
                        if (array_key_exists($d, $depts)) {
                            if (!array_key_exists($d, $countTables['transfer'])) {
                                $countTables['transfer'][$d] = ["magazine" => 0, "lecture" => 0, "poster" => 0];
                            }
                            $countTables['transfer'][$d][$type] += 1;
                        }
                    }
                }


                $result[$type][] = $doc;
            }
        }


        $section->addTitle("Foreword", 1);
        $section->addTitle("Science and Services", 1);

        // publications
        foreach (
            [
                "autonom" => "Autonomous Research",
                "collab" => "Collaborative Research"
            ] as $cat => $title1
        ) {
            $section->addTitle($title1, 1);

            // count tables
            if (!empty($countTables[$cat])) {
                $table = $section->addTable("CountTable");

                $table->addRow();
                $cell = $table->addCell(5000, $styleCell);
                $cell->addText("Department", $styleTextBold, $styleParagraph);
                $cell = $table->addCell(2500, $styleCell);
                $n = array_sum(array_column($countTables[$cat], 'chapter'));
                $cell->addText("Book Chapters (total: $n)", ["bold" => true,]);
                $cell = $table->addCell(2500, $styleCell);
                $n = array_sum(array_column($countTables[$cat], 'article'));
                $cell->addText("Refereed Publications (total: $n)", ["bold" => true,]);

                foreach ($depts as $dept_short => $dept_name) {
                    $table->addRow();
                    // in twip (1mm = 56,6928 twip)
                    $cell = $table->addCell(5000, $styleCell);
                    $cell->addText($dept_name, $styleTextBold, $styleParagraph);
                    $data = $countTables[$cat][$dept_short] ?? array("chapter" => 0, "article" => 0);
                    $cell = $table->addCell(2500, $styleCell);
                    $cell->addText($data['chapter'] == 0 ? '-' : $data['chapter'], $styleText, $styleParagraphCenter);
                    $cell = $table->addCell(2500, $styleCell);
                    $cell->addText($data['article'] == 0 ? '-' : $data['article'], $styleText, $styleParagraphCenter);
                }
            }

            $section->addTextRun();

            foreach (
                [
                    "MIOS" => "Microbiology",
                    "MuTZ" => "Human Cell Biology",
                    "PFVI" => "Plant Virology"
                ] as $D => $title2
            ) {

                if (isset($result['publication'][$cat][$D])) {
                    $section->addTitle($title2, 2);

                    foreach (
                        [
                            "chapter" => "Book Chapters",
                            "article" => "Refereed Publications",
                            "magazine" => "Non-Refereed Publications"
                        ] as $pubtype => $title3
                    ) {

                        if (isset($result['publication'][$cat][$D][$pubtype])) {
                            $content = $result['publication'][$cat][$D][$pubtype];
                            // dump($content);
                            usort($content, 'sortByFormat');
                            $section->addTitle($title3, 3);
                            if ($pubtype == 'article') {
                                $table = $section->addTable();
                                $table->addRow();
                                $cell = $table->addCell(9000);
                                $cell = $table->addCell(1000);
                                $cell->addText('Impact Factor', ['bold' => true, 'underline' => 'single'], $styleParagraphCenter);
                            }
                            foreach ($content as $doc) {
                                $line = $doc['format'];
                                if ($pubtype == 'article') {
                                    $table->addRow();
                                    // in twip (1mm = 56,6928 twip)
                                    $cell = $table->addCell(9000);

                                    // echo $line ."<br>";
                                    // dump([$doc['dept'], $D]);
                                    $line = clean_comment_export($line);
                                    \PhpOffice\PhpWord\Shared\Html::addHtml($cell, $line, false, false);
                                    if (isset($doc['impact'])) {
                                        $if = $doc['impact'];
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
                                } else {
                                    $paragraph = $section->addTextRun();
                                    // $line = clean_comment_export($line);
                                    $line = clean_comment_export($line, false);
                                    \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line);
                                }
                            }
                        }
                    }
                }
            }
        }


        $section->addTitle("Knowledge Transfer", 1);

        // count tables
        if (!empty($countTables['transfer'])) {
            $table = $section->addTable("CountTable");

            $table->addRow();
            $cell = $table->addCell(5000, $styleCell);
            $cell->addText("Department", $styleTextBold, $styleParagraphCenter);
            $cell = $table->addCell(1500, $styleCell);
            $n = array_sum(array_column($countTables['transfer'], 'magazine'));
            $cell->addText("Non-Refereed contributions (total: $n)", $styleTextBold, $styleParagraphCenter);
            $cell = $table->addCell(1500, $styleCell);
            $n = array_sum(array_column($countTables['transfer'], 'lecture'));
            $cell->addText("Lectures (total: $n)", $styleTextBold, $styleParagraphCenter);
            $cell = $table->addCell(1500, $styleCell);
            $n = array_sum(array_column($countTables['transfer'], 'poster'));
            $cell->addText("Posters (total: $n)", $styleTextBold, $styleParagraphCenter);

            foreach ($depts as $dept_short => $dept_name) {
                $table->addRow();
                // in twip (1mm = 56,6928 twip)
                $cell = $table->addCell(5000, $styleCell);
                $cell->addText($dept_name, $styleTextBold, $styleParagraphCenter);
                $data = $countTables['transfer'][$dept_short] ?? ["magazine" => 0, "lecture" => 0, "poster" => 0];
                $cell = $table->addCell(2500, $styleCell);
                $cell->addText($data['magazine'] == 0 ? '-' : $data['magazine'], $styleText, $styleParagraphCenter);
                $cell = $table->addCell(2500, $styleCell);
                $cell->addText($data['lecture'] == 0 ? '-' : $data['lecture'], $styleText, $styleParagraphCenter);
                $cell = $table->addCell(2500, $styleCell);
                $cell->addText($data['poster'] == 0 ? '-' : $data['poster'], $styleText, $styleParagraphCenter);
            }
        }

        $section->addTextRun();

        foreach (
            [
                "Lectures" => ["lecture"],
                "Posters" => ["poster"],
                "Committee involvement" => ["misc-annual"],
                "Organisation of events" => ["organisation"],
                "Public outreach" => ["outreach"],
                "Other activities" => ["misc"]
            ] as $title => $types
        ) {
            $empty = true;
            foreach ($types as $type) {
                if (!empty($result[$type] ?? [])) $empty = false;
            }
            if (!$empty) {
                $section->addTitle($title, 2);
                foreach ($types as $type) {
                    $content = $result[$type] ?? [];
                    // dump($content);
                    usort($content, 'sortByFormat');

                    foreach ($content ?? [] as $doc) {
                        $paragraph = $section->addTextRun();
                        $line = $doc['format'];
                        if (!str_ends_with(trim($line), '.')) $line = trim($line) . '.';
                        // $line .= "<br>";
                        $line = clean_comment_export($line, false);
                        \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line, false, false);
                        // $paragraph = $section->addTextRun();
                    }
                }
            }
        }

        $section->addTitle('Editorial Boards of Scientific Journals and Reviewing for Scientific Journals', 2);

        foreach (
            [
                "editorial" => "Editorial Board of Journals:",
                "review" => "Reviewing for Journals:"
            ] as $type => $title
        ) {

            if (!empty($result[$type])) {
                $paragraph = $section->addTextRun()->addText($title, ['underline' => 'single']);

                $data = $result[$type];
                ksort($data);

                foreach ($data as $journal => $authors) {

                    $paragraph = $section->addTextRun();

                    $authors = array_unique($authors);
                    sort($authors);

                    $line = "$journal (" . implode(", ", $authors) . ")";
                    // $line = clean_comment_export($line);
                    $line = clean_comment_export($line, false);
                    \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line);
                }
            }
        }


        /**
         * ---------------------------------------------
         */
    } elseif ($_POST['style'] === 'programm-budget') {

        $result = [];
        $departmentwise = [];


        foreach ($cursor as $doc) {
            $Format->setDocument($doc);
            $doc['format'] = $Format->format();
            $doc['file'] = "";
            $type = $doc['type'];
            if (in_array($type, ["software", "awards"])) {
                continue;
            }

            if ($type == 'publication') {
                // get the category of research
                $cat = 'collab';
                // get the departments
                foreach ($doc['authors'] as $a) {
                    $aoi = boolval($a['aoi'] ?? false);
                    $pos = $a['position'] ?? 'middle';
                    if ($aoi) {
                        if ($pos == 'first' || $pos == 'last') {
                            $cat = 'autonom';
                            // break;
                        }
                    }
                }

                $pubtype = $doc['subtype'] ?? $doc['pubtype'] ?? 'article';
                if (!empty($pubtype) && in_array($pubtype, ['magazine', 'other', 'preprint'])) {
                    $pubtype = 'magazine';
                }

                $doc['cat'] = $cat;

                $result[$type][$cat][$pubtype][] = $doc['format'];
            } else if ($type == 'review' || ($type == 'misc')) {
                switch (strtolower($doc['subtype'] ?? $doc['role'] ?? '')) {
                    case 'editorial':
                    case 'editor':
                        $type = "editorial";
                        break;
                    case 'grant-rev':
                        $type = "gutachten";
                        break;
                    case 'thesis-rev':
                        $type = "gutachten";
                        break;
                    case 'misc-annual':
                        $type = "gremien";
                        break;
                    case 'misc-once':
                        continue 2;
                        $type = "stop";
                        break;
                    default:
                        $type = 'review';
                        break;
                }
                $dept = $Groups->getDeptFromAuthors($doc['authors'] ?? array());
                if (!$dept) continue;
                $dept = $dept[0];
                if (isset($doc['journal'])) {
                    $journal = $DB->getJournalName($doc);
                    if (!empty($journal))
                        $departmentwise[$dept][$type][$journal][] = $DB->getTitleLastname($doc['authors'][0]['user'] ?? '');
                } else {
                    $title = $doc['title'];
                    $departmentwise[$dept][$type][$title][] = $DB->getTitleLastname($doc['authors'][0]['user'] ?? '');
                }
            } else {
                $result[$type][] = $doc['format'];
            }
        }




        $section->addTitle("Foreword", 1);

        // publications
        foreach (
            [
                "autonom" => "Eigenständige Forschungstätigkeit",
                "collab" => "Externe Zusammenarbeit"
            ] as $cat => $title1
        ) {
            $section->addTitle($title1, 1);

            $section->addTextRun();

            if (isset($result['publication'][$cat])) {

                foreach (
                    [
                        "chapter" => "Buchkapitel",
                        "article" => "Referierte Publikationen",
                        "magazine" => "Nicht-Referierte Publikationen"
                    ] as $pubtype => $title3
                ) {

                    if (isset($result['publication'][$cat][$pubtype])) {
                        $content = $result['publication'][$cat][$pubtype];
                        sort($content);
                        // dump($content);
                        // usort($content, 'sortByFormat');
                        $section->addTitle($title3, 2);
                        foreach ($content as $line) {
                            $paragraph = $section->addTextRun();
                            $line = clean_comment_export($line, false);
                            \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line);
                        }
                    }
                }
            }
        }


        $section->addTitle("Wissenstransfer", 1);

        $section->addTextRun();

        foreach (
            [
                "Vorträge" => ["lecture"],
                "Poster" => ["poster"]
            ] as $title => $types
        ) {
            $empty = true;
            foreach ($types as $type) {
                if (!empty($result[$type] ?? [])) $empty = false;
            }
            if (!$empty) {
                $section->addTitle($title, 2);
                foreach ($types as $type) {
                    $content = $result[$type] ?? [];
                    // dump($content);
                    // usort($content, 'sortByFormat');
                    sort($content);

                    foreach ($content ?? [] as $doc) {
                        $line = $doc['format'];
                        if (empty($line)) continue;
                        $paragraph = $section->addTextRun();
                        if (!str_ends_with(trim($line), '.')) $line = trim($line) . '.';
                        // $line .= "<br>";
                        $line = clean_comment_export($line, false);
                        \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line, false, false);
                        // $paragraph = $section->addTextRun();
                    }
                }
            }
        }

        // $section->addTitle('Editorial Boards of Scientific Journals and Reviewing for Scientific Journals', 2);

        foreach ($departmentwise as $dept => $data) {
            $department = $Groups->getName($dept);
            $section->addTitle($department, 1);
            foreach (
                [
                    "editorial" => "Editorenschaften in Zeitschriften:",
                    "review" => "Peer-Reviews für Zeitschriften:",
                    "gutachten" => "Begutachtungen:",
                    "gremien" => "Beschäftigte der Abteilung $department waren in folgenden Gremien aktiv:"
                ] as $type => $title
            ) {
                if (!empty($data[$type])) {
                    $paragraph = $section->addTextRun()->addText($title, ['underline' => 'single']);
                    ksort($data[$type]);

                    switch ($type) {
                        case 'editorial':
                        case 'gremien':
                            foreach ($data[$type] as $journal => $authors) {
                                $paragraph = $section->addListItemRun(0);
                                $authors = array_unique($authors);
                                sort($authors);
                                $line = "$journal (" . implode(", ", $authors) . ")";
                                $line = clean_comment_export($line, false);
                                \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line);
                            }
                            break;
                        case 'review':
                        case 'gutachten':
                            foreach ($data[$type] as $journal => $authors) {
                                $paragraph = $section->addListItemRun(0);
                                $line = "$journal: " . count($authors) . "";
                                $line = clean_comment_export($line, false);
                                \PhpOffice\PhpWord\Shared\Html::addHtml($paragraph, $line);
                            }
                            break;
                        default:
                            # code...
                            break;
                    }
                }
            }
        }
    }
    // dump($result, true);
    // die;

    if ($_POST['format'] == 'html') {
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
        $objWriter->save('.data/report.html');
        include_once '.data/report.html';
        die;
    }

    // Download file
    $file = 'Report.docx';
    header("Content-Description: File Transfer");
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $xmlWriter->save("php://output");
}, 'login');


function sortByFormat($a, $b)
{
    $a = strip_tags($a["format"] ?? '');
    $b = strip_tags($b["format"] ?? '');
    return strcasecmp($a, $b);
}
