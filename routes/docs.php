<?php

/**
 * Routing file for the documentation
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

Route::get('/docs', function () {

    $breadcrumb = [
        ['name' => lang('Documentation', 'Dokumentation')]
    ];

    include_once BASEPATH . "/php/init.php";
    // include_once BASEPATH . "/php/MyParsedown.php";

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/docs.php";
    include BASEPATH . "/footer.php";
});

// Route::get('/docs/api/(.*)', function ($path) {
//     // header("HTTP/1.0 $error");

//     include BASEPATH . "/pages/docs/api/$path";

// });
Route::get('/docs/([\w-]+)', function ($doc) {
    // header("HTTP/1.0 $error");

    include BASEPATH . "/php/init.php";
    // SassCompiler::run("scss/", "css/");

    $language = lang('en', 'de');

    $breadcrumb = [
        ['name' => lang('Documentation', 'Dokumentation'), 'path' => '/docs'],
        ['name' => lang($doc)]
    ];
    include BASEPATH . "/header.php";
    echo '<link href="' . ROOTPATH . '/css/documentation.css" rel="stylesheet">';
    echo '<script src="' . ROOTPATH . '/js/quill.min.js?v=<?=CSS_JS_VERSION?>"></script>';
    echo '<script src="' . ROOTPATH . '/js/jquery-ui.min.js"></script>';
    $path    = BASEPATH . '/pages/docs/'. $language;


    if (file_exists("$path/$doc.html")) {
        include "$path/$doc.html";
    } elseif (file_exists("$path/$doc.php")) {
        include "$path/$doc.php";
    } elseif (file_exists("$path/$doc.md")) {
        include_once BASEPATH . "/php/MyParsedown.php";
        $text = file_get_contents("$path/$doc.md");
        $parsedown = new Parsedown;

        echo '<div class="row">
            <div class="col-lg-9">';
        echo $parsedown->text($text);
        echo '</div>';

        echo '<div class="col-lg-3 d-none d-lg-block">
        <div class="on-this-page-nav" id="on-this-page-nav">
            <div class="content">
                <div class="title">On this page</div>
                ';
        foreach ($parsedown->header as $h) {
            if ($h['level'] == 1 || $h['level'] > 3) continue;
            $m = 10 * ($h['level'] - 2) + 10;
            echo "<a class='pl-$m' href='#$h[id]'>$h[text]</a>";
        }
        echo '</div>'; // content
        echo '</div>'; // on-this-page-nav
        echo '</div>'; // col-lg-3
        echo '</div>'; // row
    } else {
        echo "Doc not found.";
    }
    ?>

<style>
pre {
  box-shadow: 0px 2px 2px 0px rgba(0, 0, 0, 0.15);
  background-color: var(--gray-color-very-light);
  width: 100%;
  border: var(--border-width) solid #afafaf;
  border-radius: var(--border-radius);
  padding: 1rem;
  overflow-y: auto;
}
</style>
<?php
    // include BASEPATH . "/pages/error.php";
    include BASEPATH . "/footer.php";
});
