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
    private $osiris = null;
    private $features = array();
    public $continuousTypes = [];

    public const FEATURES = ['coins', 'achievements', 'user-metrics', 'projects', 'guests'];

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
        $user = $user ?? ($_GET['user'] ?? $_SESSION['username']);
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
                ['authors.user' => $user],
                ['editors.user' => $user],
                ['user' => $user]
            ]
        ];
        return [
            '$and' => [
                $filter,
                ['$or' => [
                    $filterAllowed,
                    ['authors.user' => $user],
                    ['editors.user' => $user],
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
            'icon' => 'placeholder'
        ];
    }

    function getActivitiesPortfolio($includePublications = false)
    {
        $filter = ['portfolio' => 1];
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
        $icon = $act['icon'] ?? 'placeholder';

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
        $style = "";

        // foreach ($this->settings['departments'] as $val) {
        //     $style .= "
        //     .text-$val[id] {
        //         color: $val[color] !important;
        //     }
        //     .row-$val[id] {
        //         border-left: 3px solid $val[color] !important;
        //     }
        //     .badge-$val[id] {
        //         color:  $val[color] !important;
        //         background-color:  $val[color]20 !important;
        //     }
        //     ";
        // }
        foreach ($this->getActivities() as $val) {
            $style .= "
            .text-$val[id] {
                color: $val[color] !important;
            }
            .box-$val[id] {
                border-left: 4px solid $val[color] !important;
            }
            .badge-$val[id] {
                color:  $val[color] !important;
                border-color:  $val[color] !important;
            }
            ";
        }
        $style = preg_replace('/\s+/', ' ', $style);

        foreach ($this->osiris->topics->find() as $t) {
            $style .= "
            .topic-" . $t['id'] . " {
                --topic-color: " . $t['color'] . ";
            }
            ";
        }

        $colors = $this->get('colors');
        if (!empty($colors)) {
            $primary = $colors['primary'] ?? '#008083';
            $secondary = $colors['secondary'] ?? '#f78104';
            $primary_hex = sscanf($primary, "#%02x%02x%02x");
            $secondary_hex = sscanf($secondary, "#%02x%02x%02x");

            $style .= "
            :root {
                --primary-color: $primary;
                --primary-color-light: " . adjustBrightness($primary, 20) . ";
                --primary-color-very-light: " . adjustBrightness($primary, 200) . ";
                --primary-color-dark: " . adjustBrightness($primary, -20) . ";
                --primary-color-very-dark: " . adjustBrightness($primary, -200) . ";
                --primary-color-20: rgba($primary_hex[0], $primary_hex[1], $primary_hex[2], 0.2);
                --primary-color-30: rgba($primary_hex[0], $primary_hex[1], $primary_hex[2], 0.3);
                --primary-color-60: rgba($primary_hex[0], $primary_hex[1], $primary_hex[2], 0.6);

                --secondary-color: $secondary;
                --secondary-color-light: " . adjustBrightness($secondary, 20) . ";
                --secondary-color-very-light: " . adjustBrightness($secondary, 200) . ";
                --secondary-color-dark: " . adjustBrightness($secondary, -20) . ";
                --secondary-color-very-dark: " . adjustBrightness($secondary, -200) . ";
                --secondary-color-20: rgba($secondary_hex[0], $secondary_hex[1], $secondary_hex[2], 0.2);
                --secondary-color-30: rgba($secondary_hex[0], $secondary_hex[1], $secondary_hex[2], 0.3);
                --secondary-color-60: rgba($secondary_hex[0], $secondary_hex[1], $secondary_hex[2], 0.6);
            }";
        }

        return "<style>$style</style>";
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

    function tripLabel()
    {
        if (!$this->featureEnabled('topics')) return '';
        $arr = $this->osiris->adminTypes->findOne(['id' => 'travel']);
        if (empty($arr) || !isset($arr['name'])) return lang('Research trips', 'Forschungsreisen');
        return lang($arr['name'], $arr['name_de'] ?? null);
    }

    function topicChooser($selected = [])
    {
        if (!$this->featureEnabled('topics')) return '';

        $topics = $this->osiris->topics->find();
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
                    <div class="pill-checkbox" style="--primary-color:<?= $topic['color'] ?? 'var(--primary-color)' ?>" <?=$subtitle?>>
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
            $html .= "<a class='topic-pill' href='" . ROOTPATH . "/topics/view/$topic[_id]' style='--primary-color:$topic[color]' $subtitle>" . lang($topic['name'], $topic['name_de'] ?? null) . "</a>";
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

    public static function getHistoryType($type) {
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
}
