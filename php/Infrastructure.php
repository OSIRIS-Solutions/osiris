<?php

/**
 * Class for all infrastructure associated methods.
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

class Infrastructure extends DB
{
    public $roles = null;

    function __construct()
    {
        parent::__construct('infrastructures');
        $this->roles = [
            'head' => lang('Head', 'Leitung'),
            'manager' => lang('Manager', 'Manager:in'),
            'coordinator' => lang('Coordinator', 'Koordinator:in'),
            'admin' => lang('Admin', 'Admin'),
            'maintainer' => lang('Maintainer', 'Betreuer:in'),
            'developer' => lang('Developer', 'Entwickler:in'),
            'curator' => lang('Curator', 'Kurator:in'),
            'support' => lang('Support/Helpdesk', 'Support/Helpdesk'),
            'contact' => lang('Contact', 'Kontakt'),
            'operator' => lang('Operator', 'Operator:in'),
            'analyst' => lang('Analyst', 'Analyst:in'),
            'researcher' => lang('Researcher', 'Forscher:in'),
            'security' => lang('Security Officer', 'Sicherheitsbeauftragte:r '),
            'user' => lang('User', 'Nutzer:in'),
            'other' => lang('Other', 'Sonstige')
        ];
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getRole($role)
    {
        return $this->roles[$role] ?? $role;
    }


    public static function getLogo($infrastructure, $class = "infrastructure-logo", $alt = "")
    {
        $placeholder = '<div class="infrastructure-logo-placeholder"><i class="ph-duotone ph-cube-transparent"></i></div> ';
        if (!isset($infrastructure) || empty($infrastructure) || !isset($infrastructure['image'])) {
            return $placeholder;
        }
        $img = $infrastructure['image'];
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

    public static function printLogo($infrastructure, $class = "infrastructure-logo", $alt = "")
    {
        echo self::getLogo($infrastructure, $class, $alt);
    }
}
