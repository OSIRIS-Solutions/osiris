<?php
/**
 * Orcid classes
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       2.1.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julius Witte <julius.witte@osiris-solutions.de>
 * @license     MIT
 */



class Orcid_Settings extends Settings
{
    private $orcid_settings;

    public $client_id;
    public $client_secret;
    public $api_base_url;
    public $api_auth_url;

    public function __construct()
    {
        // Initialize parent
        parent::__construct();
        $this->orcid_settings = $this->get('orcid');
        $this->client_id = $this->orcid_settings['client_id'];
        $this->client_secret = $this->orcid_settings['client_secret'];
        if (isset($this->orcid_settings['api'])) {
            switch ($this->orcid_settings['api']) {
                case 'member':
                    $this->api_auth_url = 'https://orcid.org/';
                    $this->api_base_url = 'https://api.orcid.org/v3.0/';
                    break;
                case 'sandbox':
                    $this->api_auth_url = 'https://sandbox.orcid.org/';
                    $this->api_base_url = 'https://pub.sandbox.orcid.org/v3.0/';
                    break;
                case 'public':
                default:
                    $this->api_auth_url = 'https://orcid.org/';
                    $this->api_base_url = 'https://pub.orcid.org/v3.0/';
                    break;
            }
        } else {
            $this->api_auth_url = 'https://orcid.org/';
            $this->api_base_url = 'https://pub.orcid.org/v3.0/';
        }
    }
}
