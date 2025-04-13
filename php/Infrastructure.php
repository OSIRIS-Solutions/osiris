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
}
