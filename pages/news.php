<?php

/**
 * Page to see latest changes
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /new-stuff
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<style>
    code.code {
        font-size: 1em;
    }

    h2 {
        /* font-family: 'Menlo', 'Courier New', Courier, monospace; */
        color: var(--primary-color);
    }

    h4 i.ph {
        color: var(--secondary-color);
    }

    blockquote {
        border-left: 4px solid var(--primary-color);
        padding-left: 1em;
        color: var(--text-secondary-color);
        margin: 0 2rem;
        font-style: italic;
    }

    blockquote p:first-of-type {
        margin-top: 0;
    }

    blockquote p:last-of-type {
        margin-bottom: 0;
    }

    .container {
        max-width: 80rem;
    }

    time {
        color: var(--secondary-color);
        font-weight: bold;
        float: right;
        font-size: 1.6rem;
        font-family: "SFMono-Regular", Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

a.anchor {
    display: block;
    position: relative;
}
a.anchor::before {
    content: "#";
    position: absolute;
    left: -3rem;
    top: 1rem;
    color: var(--link-color);
    font-size: 1.8rem;
    font-family: "SFMono-Regular", Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

hr {
    margin: 4rem 0;
}

ul ul {
    margin-top: 0.5rem;
}
</style>


<div class='container'>
    <?php
    $text = file_get_contents(BASEPATH . "/news.md");
    $parsedown = new Parsedown;
    echo $parsedown->text($text);
    ?>
</div>