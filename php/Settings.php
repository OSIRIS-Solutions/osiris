<?php

require_once "DB.php";
include_once "Groups.php";

class Settings
{
    /**
     * @deprecated 1.3.0
     */
    public $settings = array();
    // private $user = array();
    public $roles = array();
    private $osiris = null;
    private $features = array();

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

        // init Features
        $featList = $this->osiris->adminFeatures->find([]);
        foreach ($featList as $f) {
            $this->features[$f['feature']] = boolval($f['enabled']);
        }
    }

    function get($key)
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
                return '';
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
        $root = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . ROOTPATH;
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
    function featureEnabled($feature)
    {
        return $this->features[$feature] ?? false;
        // $active = $this->osiris->adminFeatures->findOne([
        //     'feature'=>$feature
        // ]);
        // return boolval($active['enabled'] ?? false);
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
                --primary-color-light: ".adjustBrightness($primary, 20).";
                --primary-color-very-light: ".adjustBrightness($primary, 200).";
                --primary-color-dark: ".adjustBrightness($primary, -20).";
                --primary-color-very-dark: ".adjustBrightness($primary, -200).";
                --primary-color-20: rgba($primary_hex[0], $primary_hex[1], $primary_hex[2], 0.2);
                --primary-color-30: rgba($primary_hex[0], $primary_hex[1], $primary_hex[2], 0.3);
                --primary-color-60: rgba($primary_hex[0], $primary_hex[1], $primary_hex[2], 0.6);

                --secondary-color: $secondary;
                --secondary-color-light: ".adjustBrightness($secondary, 20).";
                --secondary-color-very-light: ".adjustBrightness($secondary, 200).";
                --secondary-color-dark: ".adjustBrightness($secondary, -20).";
                --secondary-color-very-dark: ".adjustBrightness($secondary, -200).";
                --secondary-color-20: rgba($secondary_hex[0], $secondary_hex[1], $secondary_hex[2], 0.2);
                --secondary-color-30: rgba($secondary_hex[0], $secondary_hex[1], $secondary_hex[2], 0.3);
                --secondary-color-60: rgba($secondary_hex[0], $secondary_hex[1], $secondary_hex[2], 0.6);
            }";
        }

        return "<style>$style</style>";
    }

    private function adjustBrightness($hex, $steps) {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));
    
        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
        }
    
        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';
    
        foreach ($color_parts as $color) {
            $color   = hexdec($color); // Convert to decimal
            $color   = max(0,min(255,$color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }
    
        return $return;
    }

    function topicLabel(){
        if (!$this->featureEnabled('topics')) return '';
        $settings = $this->get('topics_label');
        if (empty($settings) || !isset($settings['en'])) return lang('Research Topics', 'Forschungsbereiche');
        return lang($settings['en'], $settings['de'] ?? null);
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
                ?>
                    <div class="pill-checkbox" style="--primary-color:<?= $topic['color'] ?? 'var(--primary-color)' ?>">
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
            $html .= '<h5 class="m-0">' . lang('Research Topics', 'Forschungsbereiche') . '</h5>';
        }
        foreach ($topics as $topic) {
            $html .= "<a class='topic-pill' href='" . ROOTPATH . "/topics/view/$topic[_id]' style='--primary-color:$topic[color]'>" . lang($topic['name'], $topic['name_de'] ?? null) . "</a>";
        }
        $html .= '</div>';
        return $html;
    }
}
