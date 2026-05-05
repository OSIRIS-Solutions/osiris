<?php

/**
 * Class for all infrastructure associated methods.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @package OSIRIS
 * @since 1.4.1
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
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
            'head' => ['en' => 'Head', 'de' => 'Leitung'],
            'manager' => ['en' => 'Manager', 'de' => 'Manager:in'],
            'coordinator' => ['en' => 'Coordinator', 'de' => 'Koordinator:in'],
            'admin' => ['en' => 'Admin', 'de' => 'Admin'],
            'maintainer' => ['en' => 'Maintainer', 'de' => 'Betreuer:in'],
            'developer' => ['en' => 'Developer', 'de' => 'Entwickler:in'],
            'curator' => ['en' => 'Curator', 'de' => 'Kurator:in'],
            'support' => ['en' => 'Support/Helpdesk', 'de' => 'Support/Helpdesk'],
            'contact' => ['en' => 'Contact', 'de' => 'Kontakt'],
            'operator' => ['en' => 'Operator', 'de' => 'Operator:in'],
            'analyst' => ['en' => 'Analyst', 'de' => 'Analyst:in'],
            'researcher' => ['en' => 'Researcher', 'de' => 'Forscher:in'],
            'security' => ['en' => 'Security Officer', 'de' => 'Sicherheitsbeauftragte:r '],
            'user' => ['en' => 'User', 'de' => 'Nutzer:in'],
            'other' => ['en' => 'Other', 'de' => 'Sonstige']
        ];
    }

    public function getRoles()
    {
        // map roles to current language
        $lang = lang('en', 'de');
        $roles = [];
        foreach ($this->roles as $key => $role) {
            $roles[$key] = $role[$lang];
        }
        return $roles;
    }

    public function getRole($role, $raw = false)
    {
        if ($raw) {
            return $this->roles[$role] ?? $role;
        }
        return $this->roles[$role][lang('en', 'de')] ?? $role;
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
