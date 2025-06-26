<?php

/**
 * Class for all committee associated methods.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @package OSIRIS
 * @since 1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

include_once 'DB.php';

class Committee
{

    function __construct() {}

    public static function getIcon($ico, $cls = null)
    {
        $icons = [
            "editorial_board" => "book-open-text",
            "scientific_board" => "student",
            "jury" => "scales",
            "committee" => "clipboard-text",
            "academy" => "bank",
            "advisory_board" => "chats-circle",
            "professional_body" => "users-three",
            "taskforce" => "git-branch",
            "panel" => "lectern",
            "default" => "identification-card"
        ];
        $icon = $icons[$ico] ?? $icons['default'];
        return '<i class="ph ph-' . $icon . ' ' . $cls . '" aria-hidden="true"></i>';
    }

    public static function getType($type)
    {
        $types = [
            "editorial_board" => lang("Editorial Board", "Editorial Board"),
            "scientific_board" => lang("Scientific Board", "Wissenschaftlicher Beirat"),
            "jury" => lang("Jury", "Jury"),
            "committee" => lang("Committee", "Ausschuss"),
            "academy" => lang("Academy / Society", "Akademie / Gesellschaft"),
            "advisory_board" => lang("Advisory Board", "Beratungsgremium"),
            "professional_body" => lang("Professional Body", "Fachgesellschaft"),
            "taskforce" => lang("Taskforce / Working group", "Taskforce / Arbeitsgruppe"),
            "panel" => lang("Panel", "Panel"),
            "other" => lang("Other", "Andere")
        ];
        return $types[$type] ?? $type;
    }
}
