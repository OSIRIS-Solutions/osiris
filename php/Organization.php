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
        ];
        $icon = $icons[$ico] ?? $icons['default'];
        return '<i class="ph ph-' . $icon . ' ' . $cls . '" aria-hidden="true"></i>';
    }
}
