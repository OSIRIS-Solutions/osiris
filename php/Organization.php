<?php

/**
 * Class for all organization associated methods.
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

class Organization
{

    function __construct() {}

    public static function getIcon($ico, $cls = null)
    {
        $icons = [
            'Education' => 'graduation-cap',
            'Healthcare' => 'heartbeat',
            'Company' => 'buildings',
            'Archive' => 'archive',
            'Nonprofit' => 'hand-heart',
            'Government' => 'bank',
            'Facility' => 'warehouse',
            'Other' => 'house',
            'default' => 'house',
            'Funder' => 'hand-coins',
            'education' => 'graduation-cap',
            'healthcare' => 'heartbeat',
            'company' => 'buildings',
            'archive' => 'archive',
            'nonprofit' => 'hand-heart',
            'government' => 'bank',
            'facility' => 'warehouse',
            'other' => 'house',
            'default' => 'house',
            'funder' => 'hand-coins',
        ];
        $icon = $icons[$ico] ?? $icons['default'];
        return '<i class="ph ph-' . $icon . ' ' . $cls . '" aria-hidden="true"></i>';
    }


    public static function getLogo($org, $class = "org-logo", $alt = "", $type = "")
    {
        $icon = self::getIcon($org['type'] ?? 'default');
        $placeholder = '<div class="org-logo-placeholder">' . $icon . '</div> ';
        if (!isset($org) || empty($org) || !isset($org['image'])) {
            return $placeholder;
        }
        $img = $org['image'];
        if (!isset($img) || empty($img)) {
            return $placeholder;
        }
        $type = $img['type'];
        if ($img['type'] == 'svg') {
            $type = 'image/svg+xml';
        } else {
            $type = 'image/' . $img['type'];
        }
        $img = $img['data']->getData();
        return "<img src='data:$type;base64,$img' alt='" . e($alt) . "' class='$class'>";
    }

    public static function printLogo($org, $class = "org-logo", $alt = "")
    {
        echo self::getLogo($org, $class, $alt);
    }
}
