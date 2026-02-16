<?php

require_once "DB.php";
include_once "Groups.php";

/**
 * Represents the application settings, including user roles, features, and activities.
 */
class Settings
{
    /**
     * @deprecated 1.3.0
     */
    public $settings = array();
    // private $user = array();
    public $roles = array();
    public $allowedTypes = array();
    public $allowedFilter = array();
    public $osiris = null;
    private $features = array();
    public $continuousTypes = [];

    function __construct($user = array())
    {
        // construct database object 
        $DB = new DB;
        $this->osiris = $DB->db;

        // set user roles
        if (isset($user['roles'])) {
            $this->roles = DB::doc2Arr($user['roles']);
        } else {
            foreach (['editor', 'admin', 'leader', 'controlling', 'scientist'] as $key) {
                if ($user['is_' . $key] ?? false) $this->roles[] = $key;
            }
        }
        // everyone is a user
        $this->roles[] = 'user';
        if (defined('ADMIN') && isset($user['username']) && $user['username'] == ADMIN) {
            $this->roles[] = 'admin';
        }
        $this->roles = array_values(array_unique($this->roles));

        $catFilter = ['$or' => [
            ['visible_role' => ['$exists' => false]],
            ['visible_role' => null],
            ['visible_role' => ['$in' => $this->roles]]
        ]];
        $allowedTypes = $this->osiris->adminCategories->find($catFilter, ['projection' => ['_id' => 0, 'id' => 1]]);
        $this->allowedTypes = array_column($allowedTypes->toArray(), 'id');

        // init Features
        $featList = $this->osiris->adminFeatures->find([]);
        foreach ($featList as $f) {
            $this->features[$f['feature']] = boolval($f['enabled']);
        }

        // get continuous types
        $continuous = $this->osiris->adminTypes->find(
            ['$or' => [
                ['modules' => 'date-range-ongoing'],
                ['modules' => 'date-range-ongoing*'],
            ]],
            ['projection' => ['_id' => 0, 'id' => 1]]
        )->toArray();
        $this->continuousTypes = array_column($continuous, 'id');
    }

    /**
     * Get the filter for activities based on the provided filter and user.
     *
     * @param array $filter The filter criteria.
     * @param string|null $user The username to filter by, defaults to current user.
     * @return array The MongoDB query filter for activities.
     */
    function getActivityFilter($filter, $user = null, $reduced = false)
    {
        $user = $user ?? ($_SESSION['username']);
        // check if allowed types are actually all types
        $all_types = $this->osiris->adminCategories->distinct('id', []);
        if (count($this->allowedTypes) == count($all_types)) {
            return $filter;
        }
        $filterAllowed = ['type' => ['$in' => $this->allowedTypes]];
        if ($reduced) {
            return $filterAllowed;
        }
        if (empty($filter)) return [
            '$or' => [
                $filterAllowed,
                ['rendered.users' => $user],
                ['user' => $user]
            ]
        ];
        return [
            '$and' => [
                $filter,
                ['$or' => [
                    $filterAllowed,
                    ['rendered.users' => $user],
                    ['user' => $user]
                ]]
            ]
        ];
    }

    function get($key, $default = null)
    {
        switch ($key) {
            case 'affiliation':
            case 'affiliation_details':
                // return $s['affiliation']['id'] ?? '';
                $req = $this->osiris->adminGeneral->findOne(['key' => 'affiliation']);
                if ($key == 'affiliation') return $req['value']['id'] ?? '';
                return DB::doc2Arr($req['value'] ?? array());
            case 'startyear':
                $req = $this->osiris->adminGeneral->findOne(['key' => 'startyear']);
                return intval($req['value'] ?? 2020);
            case 'departments':
                dump("DEPARTMENTS sollten nicht mehr hierüber abgefragt werden.");
                return '';
            case 'activities':
                return $this->getActivities();
                // case 'general':
                //     return $s['general'];
            case 'features':
                return $this->features;
            default:
                $req = $this->osiris->adminGeneral->findOne(['key' => $key]);
                if (!empty($req)) return $req['value'];
                return $default;
                break;
        }
    }
    function set($key, $value)
    {
        $this->osiris->adminGeneral->updateOne(
            ['key' => $key],
            ['$set' => ['value' => $value]],
            ['upsert' => true]
        );
    }

    function systemInfo($key)
    {
        $req = $this->osiris->system->findOne(['key' => $key]);
        if (!empty($req)) return $req['value'];
        return '-';
    }

    function printLogo($class = "")
    {
        $logo = $this->osiris->adminGeneral->findOne(['key' => 'logo']);
        if (empty($logo)) return '';
        if ($logo['ext'] == 'svg') {
            $logo['ext'] = 'svg+xml';
        }
        // return '<img src="data:svg;'.base64_encode($logo['value']).' " class="'.$class.'" />';

        // } else {
        return '<img src="data:image/' . $logo['ext'] . ';base64,' . base64_encode($logo['value']) . ' " class="' . $class . '" />';

        // }
    }

    function printProfilePicture($user, $class = "", $embed = false)
    {
        $root = $this->getRequestScheme() . "://" . $_SERVER['HTTP_HOST'] . ROOTPATH;
        $default = '<img src="' . $root . '/img/no-photo.png" alt="Profilbild" class="' . $class . '">';

        if (empty($user)) return $default;
        if ($this->featureEnabled('db_pictures')) {
            $img = $this->osiris->userImages->findOne(['user' => $user]);

            if (empty($img)) {
                return $default;
            }
            if ($img['ext'] == 'svg') {
                $img['ext'] = 'svg+xml';
            }
            if ($embed)
                return '<img src="data:image/' . $img['ext'] . ';base64,' . base64_encode($img['img']) . ' " class="' . $class . '" />';
            return '<img src="' . $root . '/image/' . $user . '" alt="Profilbild" class="' . $class . '">';
        } else {
            $img_exist = file_exists(BASEPATH . "/img/users/$user.jpg");
            if (!$img_exist) {
                return $default;
            }
            // make sure that caching is not an issue
            $v = filemtime(BASEPATH . "/img/users/$user.jpg");
            $img = "$root/img/users/$user.jpg?v=$v";
            return ' <img src="' . $img . '" alt="Profilbild" class="' . $class . '">';
        }
    }


    /**
     * Determines the request scheme (http or https) based on server variables.
     *
     * @return string The request scheme ('http' or 'https').
     */
    public function getRequestScheme(): string
    {
        if (!empty($_SERVER['REQUEST_SCHEME'])) {
            return $_SERVER['REQUEST_SCHEME'];
        }

        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === 443)
        ) {
            return 'https';
        }

        return 'http';
    }

    /**
     * Checks if current user has a permission
     *
     * @param string $right
     * @return boolean
     */
    function hasPermission(string $right)
    {
        if (!isset($_SESSION['username'])) return false;
        if ($right == 'admin.see'  && ADMIN == $_SESSION['username']) return true;
        $permission = $this->osiris->adminRights->findOne([
            'role' => ['$in' => $this->roles],
            'right' => $right,
            'value' => true
        ]);
        return !empty($permission);
    }

    /**
     * Check if feature is active
     *
     * @param string $feature
     * @return boolean
     */
    function featureEnabled($feature, $default = false)
    {
        return $this->features[$feature] ?? $default;
    }

    /**
     * Get Activity categories
     *
     * @param $type
     * @return array
     */
    function getActivities($type = null)
    {
        if ($type === null)
            return $this->osiris->adminCategories->find()->toArray();

        $arr = $this->osiris->adminCategories->findOne(['id' => $type]);
        if (!empty($arr)) return DB::doc2Arr($arr);
        // default
        return [
            'name' => $type,
            'name_de' => $type,
            'color' => '#cccccc',
            'icon' => 'folder-open'
        ];
    }

    function getActivitiesPortfolio($includePublications = false)
    {
        $filter = ['portfolio' => ['$in' => [true, 'true', 1]]];
        if (!$includePublications) $filter['parent'] = ['$ne' => 'publication'];
        return $this->osiris->adminTypes->distinct('id', $filter);
    }

    /**
     * Get Activity settings for cat and type
     *
     * @param string $cat
     * @param string $type
     * @return array
     */
    function getActivity($cat, $type = null)
    {
        if ($type === null) {
            $act = $this->osiris->adminCategories->findOne(['id' => $cat]);
            return DB::doc2Arr($act);
        }

        $act = $this->osiris->adminTypes->findOne(['id' => $type]);
        return DB::doc2Arr($act);
    }

    /**
     * Helper function to get the label of an activity type
     *
     * @param [type] $cat
     * @param [type] $type
     * @return string
     */
    function title($cat, $type = null)
    {
        $act = $this->getActivity($cat, $type);
        if (empty($act)) return 'unknown';
        return lang($act['name'], $act['name_de'] ?? $act['name']);
    }

    /**
     * Helper function to get the icon of an activity type
     *
     * @param [type] $cat
     * @param [type] $type
     * @return string
     */
    function icon($cat, $type = null, $tooltip = true)
    {
        $act = $this->getActivity($cat, $type);
        $icon = $act['icon'] ?? 'folder-open';

        $icon = "<i class='ph text-$cat ph-$icon'></i>";
        if ($tooltip) {
            $name = $this->title($cat);
            return "<span data-toggle='tooltip' data-title='$name'>
                $icon
            </span>";
        }
        return $icon;
    }


    function generateStyleSheet()
    {
        $style = '';
        $root = '--affiliation: "' . $this->get('affiliation') . '";';

        foreach ($this->getActivities() as $val) {
            $style .= "
            .text-$val[id] { color: $val[color] !important; }
            .box-$val[id] { border-left: 4px solid $val[color] !important; }
            .badge-$val[id] { color:  $val[color] !important; border-color:  $val[color] !important; }
            ";
        }
        $style = preg_replace('/\s+/', ' ', $style);

        foreach ($this->osiris->topics->find() as $t) {
            $style .= " .topic-" . $t['id'] . " { --topic-color: " . $t['color'] . "; } ";
        }

        $colors = $this->get('colors');
        if (!empty($colors)) {
            $primary = $colors['primary'] ?? '#008083';
            $secondary = $colors['secondary'] ?? '#f78104';
            $primary_hex = sscanf($primary, "#%02x%02x%02x");
            $secondary_hex = sscanf($secondary, "#%02x%02x%02x");
            $root .= "--primary-color: $primary; --primary-color-light: " . adjustBrightness($primary, 20) . "; --primary-color-very-light: " . adjustBrightness($primary, 200) . "; --primary-color-dark: " . adjustBrightness($primary, -20) . "; --primary-color-very-dark: " . adjustBrightness($primary, -200) . "; --primary-color-20: rgba($primary_hex[0], $primary_hex[1], $primary_hex[2], 0.2); --primary-color-30: rgba($primary_hex[0], $primary_hex[1], $primary_hex[2], 0.3); --primary-color-60: rgba($primary_hex[0], $primary_hex[1], $primary_hex[2], 0.6);";
            $root .= "--secondary-color: $secondary; --secondary-color-light: " . adjustBrightness($secondary, 20) . "; --secondary-color-very-light: " . adjustBrightness($secondary, 200) . "; --secondary-color-dark: " . adjustBrightness($secondary, -20) . "; --secondary-color-very-dark: " . adjustBrightness($secondary, -200) . "; --secondary-color-20: rgba($secondary_hex[0], $secondary_hex[1], $secondary_hex[2], 0.2); --secondary-color-30: rgba($secondary_hex[0], $secondary_hex[1], $secondary_hex[2], 0.3); --secondary-color-60: rgba($secondary_hex[0], $secondary_hex[1], $secondary_hex[2], 0.6); ";
        }

        $design = $this->get('design');

        if (!empty($design)) {

            $font = $design['font_preset'] ?? 'rubik';

            $setBody = function (string $css) use (&$root) {
                $root .= "--font-family:$css;";
            };
            $setHeader = function (string $css) use (&$root) {
                $root .= "--header-font:$css;";
            };

            $bodyCss = "'Rubik', Helvetica, sans-serif"; // fallback default

            switch ($font) {
                case 'rubik':
                    $bodyCss = "'Rubik', Helvetica, sans-serif";
                    break;
                case 'tiktok':
                    $bodyCss = "'TikTok Sans', Helvetica, sans-serif";
                    break;
                case 'system':
                    $bodyCss = "-apple-system, system-ui, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif";
                    break;
                case 'custom':
                    if (!empty($design['font_family'])) {
                        // Quote font family safely
                        $family = str_replace('"', '\"', (string)$design['font_family']);
                        $bodyCss = "\"{$family}\", Rubik, Helvetica, sans-serif";
                    }
                    break;
            }

            $setBody($bodyCss);

            // --------------------
            // Header font handling
            // --------------------
            $headerPreset = $design['header_font_preset'] ?? null;

            // Legacy fallback (old setting): if header preset is not set, emulate previous behavior
            if ($headerPreset === null) {
                // old default: headers were TikTok unless font_headers=yes in custom case
                $headerCss = "'TikTok Sans', Helvetica, sans-serif";

                if (($design['font_headers'] ?? 'no') === 'yes' && !empty($design['font_family'])) {
                    $family = str_replace('"', '\"', (string)$design['font_family']);
                    $headerCss = "\"{$family}\", Rubik, Helvetica, sans-serif";
                }

                $setHeader($headerCss);
            } else {
                // New behavior
                switch ($headerPreset) {
                    case 'body':
                        $setHeader($bodyCss);
                        break;

                    case 'rubik':
                        $setHeader("'Rubik', Helvetica, sans-serif");
                        break;

                    case 'tiktok':
                        $setHeader("'TikTok Sans', Helvetica, sans-serif");
                        break;

                    case 'system':
                        $setHeader("-apple-system, system-ui, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif");
                        break;

                    case 'custom':
                        if (!empty($design['header_font_family'])) {
                            $family = str_replace('"', '\"', (string)$design['header_font_family']);
                            $setHeader("\"{$family}\", 'TikTok Sans', Helvetica, sans-serif");
                        } else {
                            // sensible fallback if custom selected but empty
                            $setHeader("'TikTok Sans', Helvetica, sans-serif");
                        }
                        break;

                    default:
                        $setHeader("'TikTok Sans', Helvetica, sans-serif");
                        break;
                }
            }
            // $font = $design['font_preset'] ?? 'rubik';
            // switch ($font) {
            //     case 'rubik':
            //         $root .= "--font-family:'Rubik', Helvetica, sans-serif;";
            //         break;
            //     case 'tiktok':
            //         $root .= "--font-family:'TikTok Sans', Helvetica, sans-serif;";
            //         break;
            //     case 'system':
            //         $root .= "--font-family:-apple-system, system-ui, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;";
            //         break;
            //     case 'custom':
            //         if (!empty($design['font_family'])) {
            //             $root .= '--font-family: "' . $design['font_family'] . '", Rubik, Helvetica, sans-serif;';
            //             if (($design['font_headers'] ?? 'no') == 'yes') {
            //                 $root .= "--header-font: " . $design['font_family'] . ", Rubik, Helvetica, sans-serif;";
            //             }
            //         }
            //         break;
            // }


            if (!empty($design['border_width'])) {
                switch ($design['border_width']) {
                    case 'normal':
                        $root .= "--border-width: 1px;";
                        break;
                    case 'thick':
                        $root .= "--border-width: 2px;";
                        break;
                    case 'none':
                        $root .= "--border-width: 0px;";
                        break;
                }
            }
            if (!empty($design['border_corners'])) {
                switch ($design['border_corners']) {
                    case 'sharp':
                        $root .= "--border-radius: 0px; --padding-threshold: 0.5rem;";
                        break;
                    case 'rounded':
                        $root .= "--border-radius: 0.5rem; --padding-threshold: 0.5rem;";
                        break;
                    case 'more-rounded':
                        $root .= "--border-radius: 1rem; --padding-threshold: 1rem;";
                        break;
                    case 'very-rounded':
                        $root .= "--border-radius: 1.5rem; --padding-threshold: 1.5rem;";
                        break;
                }
            }
            if (!empty($design['border_color'])) {
                $root .= "--border-color: " . $design['border_color'] . ";";
            }

            if (isset($design['logo_filter'])) {
                switch ($design['logo_filter']) {
                    case 'none':
                        // $style .= "#osiris-logo { filter: none;} ";
                        break;
                    case 'grayscale':
                        $style .= "#osiris-logo { filter: grayscale(1);} ";
                        break;
                    case 'invert':
                        $style .= "#osiris-logo { filter: invert(1);} ";
                        break;
                    case 'sepia':
                        $style .= "#osiris-logo { filter: sepia(1);} ";
                        break;
                    case 'black':
                        $style .= "#osiris-logo { filter: brightness(0);} ";
                        break;
                    case 'green':
                        $style .= "#osiris-logo { filter: hue-rotate(62deg);} ";
                        break;
                    case 'blue':
                        $style .= "#osiris-logo { filter: hue-rotate(182deg);} ";
                        break;
                    case 'red':
                        $style .= "#osiris-logo { filter: hue-rotate(327deg);} ";
                        break;
                    case "pink":
                        $style .= "#osiris-logo { filter: hue-rotate(300deg);} ";
                        break;
                    default:
                        // $style .= "#osiris-logo { filter: none;} ";
                        break;
                }
            }
            if (isset($design['navbar_height'])) {
                switch ($design['navbar_height']) {
                    case 'narrow':
                        $root .= "--navbar-height: 6rem;";
                        break;
                    case 'default':
                        $root .= "--navbar-height: 8rem;";
                        break;
                    case 'wide':
                        $root .= "--navbar-height: 10rem;";
                        break;
                    case 'none':
                        $root .= "--navbar-height: 0rem;--footer-logo-display: block; ";
                        break;
                }
            }
            if (isset($design['table_striped'])) {
                switch ($design['table_striped']) {
                    case 'enabled':
                        $root .= "--table-stripe-color: var(--gray-color-very-light);";
                        break;
                    case 'disabled':
                        $root .= "--table-stripe-color: white;";
                        break;
                }
            }
            if (isset($design['box_shadow'])) {
                switch ($design['box_shadow']) {
                    case 'strong':
                        $root .= "--box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);";
                        break;
                    case 'disabled':
                        $root .= "--box-shadow: none;";
                        break;
                }
            }
            if (isset($design['icon_style'])) {
                switch ($design['icon_style']) {
                    case 'filled':
                        $root .= "--icon-font: 'Phosphor-Fill';";
                        $style .= '.ph {font-family: "Phosphor-Fill" !important;} ';
                        break;
                    case 'duotone':
                        $root .= "--icon-font: 'Phosphor';";
                        // $style .= '.ph {font-family: "Phosphor-Duotone" !important;} ' ;
                        break;
                    default:
                        $root .= "--icon-font: 'Phosphor';";
                        break;
                }
            }
            if (isset($design['link_style'])) {
                switch ($design['link_style']) {
                    case 'underline':
                        $style .= "a:not(.btn, .link, .item) { text-decoration: underline; }";
                        break;
                    case 'underline-hover':
                        $style .= "a:not(.btn, .link, .item):hover { text-decoration: underline; }";
                        break;
                }
            }
        }

        if (!empty($root)) {
            $style = ":root { $root } " . $style;
        }

        return $style;
    }

    /**
     * Build <link> tags for webfonts based on design settings.
     * Returns an empty string if no external font stylesheet is needed.
     */
    function renderAdditionalStylesheetLinks(): string
    {
        $design = $this->get('design');
        $out = '';

        $iconStyle = $design['icon_style'] ?? 'ph';
        if ($iconStyle == 'filled') {
            $out .= '<link rel="stylesheet" href="' . ROOTPATH . '/css/phosphoricons/fill/style_general.css?v=' . OSIRIS_BUILD . '">' . "\n";
        } elseif ($iconStyle == 'duotone') {
            $out .= '<link rel="stylesheet" href="' . ROOTPATH . '/css/phosphoricons/duotone/style_general.css?v=' . OSIRIS_BUILD . '">' . "\n";
        } else {
            // $out .= '<link rel="stylesheet" href="' . ROOTPATH . '/css/phosphoricons/regular/style.css?v=' . OSIRIS_BUILD . '">' . "\n";
        }

        $preset = $design['font_preset'] ?? 'rubik';
        $googleUsed = false;
        if (!empty($preset) && $preset == 'custom') {

            // Determine which CSS URL to load (if any)
            $cssUrl = trim((string)($design['font_css_url'] ?? ''));

            // Basic validation: https only, no whitespace, no quotes, no "<" / ">"
            if ($cssUrl === '') return '';
            if (!preg_match('~^https://[^\s"\'<>]+$~i', $cssUrl)) return '';

            $cssUrlEsc = e($cssUrl);

            // Preconnect only makes sense for Google Fonts
            if (strpos($cssUrl, 'fonts.googleapis.com') !== false) {
                $out  .= '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
                $out .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
                $googleUsed = true;
            }
            $out .= '<link rel="stylesheet" href="' . $cssUrlEsc . '">' . "\n";
        }
        $headerPreset = $design['header_font_preset'] ?? null;
        if (!empty($headerPreset) && $headerPreset == 'custom') {
            $cssUrl = trim((string)($design['header_font_css_url'] ?? ''));
            if ($cssUrl === '') return $out;
            if (!preg_match('~^https://[^\s"\'<>]+$~i', $cssUrl)) return $out;

            $cssUrlEsc = e($cssUrl);

            if (strpos($cssUrl, 'fonts.googleapis.com') !== false && !$googleUsed) {
                $out  .= '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
                $out .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
            }
            $out .= '<link rel="stylesheet" href="' . $cssUrlEsc . '">' . "\n";
        }

        return $out;
    }

    private function adjustBrightness($hex, $steps)
    {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));

        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
        }

        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';

        foreach ($color_parts as $color) {
            $color   = hexdec($color); // Convert to decimal
            $color   = max(0, min(255, $color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }

        return $return;
    }

    function infrastructureLabel()
    {
        if (!$this->featureEnabled('infrastructures')) return '';
        $settings = $this->get('infrastructures_label');
        if (empty($settings) || !isset($settings['en'])) return lang('Infrastructures', 'Infrastrukturen');
        return lang($settings['en'], $settings['de'] ?? null);
    }

    function topicLabel()
    {
        if (!$this->featureEnabled('topics')) return '';
        $settings = $this->get('topics_label');
        if (empty($settings) || !isset($settings['en'])) return lang('Research Topics', 'Forschungsbereiche');
        return lang($settings['en'], $settings['de'] ?? null);
    }

    function tagLabel()
    {
        if (!$this->featureEnabled('tags')) return '';
        $settings = $this->get('tags_label');
        if (empty($settings) || !isset($settings['en'])) return lang('Tags', 'Schlagwörter');
        return lang($settings['en'], $settings['de'] ?? null);
    }

    function journalLabel()
    {
        $settings = $this->get('journals_label');
        if (empty($settings) || !isset($settings['en'])) return lang('Journals', 'Journale');
        return lang($settings['en'], $settings['de'] ?? null);
    }

    function tripLabel()
    {
        if (!$this->featureEnabled('trips')) return '';
        $arr = $this->osiris->adminTypes->findOne(['id' => 'travel']);
        if (empty($arr) || !isset($arr['name'])) return lang('Research trips', 'Forschungsreisen');
        return lang($arr['name'], $arr['name_de'] ?? null);
    }

    function topicChooser($selected = [])
    {
        if (!$this->featureEnabled('topics')) return '';

        $topics = $this->osiris->topics->find([], ['sort' => ['inactive' => 1]]);
        if (empty($topics)) return '';

        $selected = DB::doc2Arr($selected);
?>
        <div class="form-group" id="topic-widget">
            <h5><?= $this->topicLabel() ?></h5>
            <!-- make suire that an empty value is submitted in case no checkbox is ticked -->
            <input type="hidden" name="values[topics]" value="">
            <div>
                <?php
                foreach ($topics as $topic) {
                    $checked = in_array($topic['id'], $selected);
                    $subtitle = '';
                    if (!empty($topic['subtitle'])) {
                        $subtitle = 'data-toggle="tooltip" data-title="' . lang($topic['subtitle'], $topic['subtitle_de'] ?? null) . '"';
                    }
                ?>
                    <div class="pill-checkbox <?= ($topic['inactive'] ?? false) ? 'inactive' : '' ?>" style="--primary-color:<?= $topic['color'] ?? 'var(--primary-color)' ?>" <?= $subtitle ?>>
                        <input type="checkbox" id="topic-<?= $topic['id'] ?>" value="<?= $topic['id'] ?>" name="values[topics][]" <?= $checked ? 'checked' : '' ?>>
                        <label for="topic-<?= $topic['id'] ?>">
                            <?= lang($topic['name'], $topic['name_de'] ?? null) ?>
                        </label>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php }

    function printTopics($topics, $class = "", $header = false)
    {
        if (!$this->featureEnabled('topics')) return '';
        if (empty($topics) || empty($topics[0])) return '';
        if (is_string($topics)) {
            $topics = DB::doc2Arr(explode(',', $topics));
        }

        $topics = $this->osiris->topics->find(['id' => ['$in' => $topics]]);
        $html = '<div class="topics ' . $class . '">';
        if ($header) {
            $html .= '<h5 class="m-0">' . $this->topicLabel() . '</h5>';
        }
        foreach ($topics as $topic) {
            $subtitle = '';
            if (!empty($topic['subtitle'])) {
                $html .= '<span data-toggle="tooltip" data-title="' . lang($topic['subtitle'], $topic['subtitle_de'] ?? null) . '">';
            }
            $html .= "<a class='topic-pill " . ($topic['inactive'] ?? false ? 'inactive' : '') . "' href='" . ROOTPATH . "/topics/view/$topic[_id]' style='--primary-color:$topic[color]' $subtitle>" . lang($topic['name'], $topic['name_de'] ?? null) . "</a>";
            if (!empty($topic['subtitle'])) {
                $html .= '</span>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    function printTopic($topic)
    {
        $topic = $this->osiris->topics->findOne(['id' => $topic]);
        if (empty($topic)) return '';
        return "<a class='topic-pill' href='" . ROOTPATH . "/topics/view/$topic[_id]' style='--primary-color:$topic[color]'>" . lang($topic['name'], $topic['name_de'] ?? null) . "</a>";
    }

    public function tagChooser($selected = [])
    {
        if (!$this->featureEnabled('tags')) return '';
        $tags = $this->get('tags') ?? [];
        if (empty($tags)) return '';
        $selected = DB::doc2Arr($selected);
        $tagName = $this->tagLabel();
    ?>
        <div class="form-group" data-module="tags">
            <label for="tag-select" class="floating-title">
                <?= $tagName ?>
            </label>
            <select class="form-control" name="values[tags][]" id="tag-select" multiple>
                <?php
                foreach ($tags as $val) {
                    $sel = in_array($val, $selected);
                ?>
                    <option <?= ($sel ? 'selected' : '') ?> value="<?= $val ?>"><?= $val ?></option>
                <?php
                }
                ?>
            </select>
            <script>
                $('#tag-select').multiSelect({
                    noneText: '<?= lang('No ' . $tagName . ' selected', 'Keine ' . $tagName . ' ausgewählt') ?>',
                    allText: '<?= lang('All ' . $tagName . 's', 'Alle ' . $tagName . 's') ?>',
                });
            </script>
        </div>
<?php
    }

    public function printTags($tags, $linkbase = false, $class = "", $header = false)
    {
        if (!$this->featureEnabled('tags')) return '';
        if (empty($tags) || empty($tags[0])) return '';

        $html = '<div class="tags ' . $class . '">';
        if ($header) {
            $html .= '<h5 class="m-0">' . $this->tagLabel() . '</h5>';
        }

        foreach ($tags as $tag) {
            if ($linkbase) {
                $html .= "<a class='badge primary mr-5 mb-5' href='" . ROOTPATH . "/$linkbase#tags=$tag'><i class='ph ph-tag'></i> $tag</a>";
            } else {
                $html .= "<span class='badge primary mr-5 mb-5' id='$tag-btn' data-type='$tag'><i class='ph ph-tag'></i> $tag</span>";
            }
        }
        $html .= '</div>';
        return $html;
    }

    public function canProjectsBeCreated()
    {
        $ability = $this->osiris->adminProjects->count(['disabled' => false, 'process' => 'project']);
        if ($ability > 0) {
            return ($this->hasPermission('projects.add'));
        }
        return false;
    }

    public function canProposalsBeCreated()
    {
        $ability = $this->osiris->adminProjects->count(['disabled' => false, 'process' => 'proposal']);
        if ($ability > 0) {
            return ($this->hasPermission('proposals.add'));
        }
        return false;
    }

    public function getRoles()
    {

        $req = $this->osiris->adminGeneral->findOne(['key' => 'roles']);
        $roles =  DB::doc2Arr($req['value'] ?? array('user', 'scientist', 'admin'));

        // if user and scientist are not in the roles, add them
        if (!in_array('user', $roles)) {
            $roles[] = 'user';
        }
        if (!in_array('scientist', $roles)) {
            $roles[] = 'scientist';
        }
        // sort admin last
        $roles = array_diff($roles, ['admin']);
        $roles = array_merge($roles, ['admin']);
        return $roles;
    }

    public function getRolesWithPermission($right)
    {
        $roles = $this->osiris->adminRights->distinct('role', [
            'right' => $right,
            'value' => true
        ]);
        return DB::doc2Arr($roles);
    }
    public function getRegex()
    {
        $regex = $this->get('regex');
        if (empty($regex)) $regex = $this->get('affiliation');

        // check if regex starts with a slash and remove it
        if (str_starts_with($regex, '/')) {
            $regex = substr($regex, 1);
        }
        // check if string ends with a slash and flag
        if (preg_match('/\/[a-z]*$/', $regex)) {
            $flags = substr($regex, strrpos($regex, '/') + 1);
            $regex = substr($regex, 0, strrpos($regex, '/'));
        } else {
            $flags = '';
        }
        return $regex;
    }

    public static function getHistoryType($type)
    {
        $mapping = [
            'created' => lang('Created by ', 'Erstellt von '),
            'edited' => lang('Edited by ', 'Bearbeitet von '),
            'imported' => lang('Imported by ', 'Importiert von '),
            'workflow-reset' => lang('Workflow reset by ', 'Workflow zurückgesetzt von '),
            'workflow-approve' => lang('Workflow step approved by ', 'Workflow-Schritt genehmigt von '),
            'workflow-reject' => lang('Workflow step rejected by ', 'Workflow-Schritt abgelehnt von '),
            'workflow-reply' => lang('Workflow rejection commented by ', 'Workflow-Ablehnung kommentiert von ')
        ];
        return $mapping[$type] ?? ucfirst($type) . lang(' by ', ' von ');
    }

    public function getDOImappings()
    {
        $mappings = $this->get('doi_mappings');
        if (empty($mappings)) {
            return [
                // CrossRef
                "crossref.journal-article" => "article",
                "crossref.magazine-article" => "magazine",
                "crossref.book-chapter" => "chapter",
                "crossref.publication" => "article",
                "crossref.doctoral-thesis" => "students",
                "crossref.master-thesis" => "students",
                "crossref.bachelor-thesis" => "students",
                "crossref.guest-scientist" => "guests",
                "crossref.lecture-internship" => "guests",
                "crossref.student-internship" => "guests",
                "crossref.reviewer" => "review",
                "crossref.editor" => "editorial",
                "crossref.monograph" => "book",
                "crossref.misc" => "misc",
                "crossref.edited-book" => "book",
                // DataCite
                'datacite.book' => 'book',
                'datacite.bookchapter' => 'chapter',
                'datacite.journal' => 'article',
                'datacite.journalarticle' => 'article',
                'datacite.conferencepaper' => 'article',
                'datacite.conferenceproceeding' => 'article',
                'datacite.dissertation' => 'dissertation',
                'datacite.preprint' => 'preprint',
                'datacite.software' => 'software',
                'datacite.computationalnotebook' => 'software',
                'datacite.model' => 'software',
                'datacite.datapaper' => 'dataset',
                'datacite.dataset' => 'dataset',
                'datacite.peerreview' => 'review',
                'datacite.audiovisual' => 'misc',
                'datacite.collection' => 'misc',
                'datacite.event' => 'misc',
                'datacite.image' => 'misc',
                'datacite.report' => 'others',
                'datacite.interactiveresource' => 'misc',
                'datacite.outputmanagementplan' => 'misc',
                'datacite.physicalobject' => 'misc',
                'datacite.service' => 'misc',
                'datacite.sound' => 'misc',
                'datacite.standard' => 'misc',
                'datacite.text' => 'misc',
                'datacite.workflow' => 'misc',
                'datacite.other' => 'misc',
                'datacite.presentation' => 'lecture',
                'datacite.poster' => 'poster'
            ];
        }
        return DB::doc2Arr($mappings);
    }
}
