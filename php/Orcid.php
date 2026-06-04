<?php
class Orcid_Settings
{
    private $orcid_settings;

    public $client_id;
    public $client_secret;
    public $api_base_url;
    public $api_auth_url;

    public function __construct()
    {
        $Settings = new Settings();
        $this->orcid_settings = $Settings->get('orcid');
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
