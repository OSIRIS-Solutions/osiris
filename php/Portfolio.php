<?php

require_once BASEPATH . '/php/DB.php';
require_once BASEPATH . '/php/Settings.php';

class Portfolio extends Settings
{

    private $apikey = null;
    private $preview = false;
    private $lang = 'en';
    private $apipath = '';
    private $basepath = '';

    // init
    function __construct($preview = false)
    {
        parent::__construct();

        // read portfolio settings
        $apikey = $this->get('portfolio_apikey', null);
        if (!empty($apikey)) {
            $this->apikey = $apikey;
        }
        $this->preview = $preview;

        $this->lang = 'en';
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'de'])) {
            $this->lang = $_GET['lang'];
        }
        $this->apipath = ($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST']) . ROOTPATH . '/portfolio';
        $this->apipath = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $this->apipath;

        // basepath for links depends on portfolio settings
        if ($this->preview) {
            $this->basepath = ROOTPATH . '/preview';
            $this->lang = lang('en', 'de');
        } else {
            $this->basepath = $this->get('portfolio_url', ROOTPATH . '/');
            if (substr($this->basepath, -1) === '/') {
                $this->basepath = substr($this->basepath, 0, -1);
            }
            $this->basepath .= '/' . $this->lang;
        }
    }

    function printProfilePicture($user, $root = null, $class = "")
    {
        if ($root === null) {
            $root = $this->get('portfolio_url', ROOTPATH);
        }
        if ($this->isPreview()) {
            $path = ROOTPATH . '/img';
        } else {
            $path = $root . '/assets/img';
        }
        $default = '<img src="' . $path . '/no-photo.png" alt="Profilbild" class="' . $class . '">';
        $userId = null;
        if (is_array($user)) {
            $userId = $user['id'] ?? null;
        } else {
            $userId = $user;
        }
        if (empty($userId)) return $default;
        $user = $this->osiris->persons->findOne(['_id' => DB::to_ObjectID($userId)]);
        if (empty($user)) return $default;
        if (!($user['public_image'] ?? false)) {
            return $default;
        }
        if ($this->featureEnabled('db_pictures')) {
            $img = $this->osiris->userImages->findOne(['user' => $user['username']]);
            if (empty($img)) {
                return $default;
            }
            if ($img['ext'] == 'svg') {
                $img['ext'] = 'svg+xml';
            }
            return '<img src="data:image/' . $img['ext'] . ';base64,' . base64_encode($img['img']) . ' " class="' . $class . '" />';
        } else {
            $img_exist = file_exists(BASEPATH . "/img/users/{$user['username']}.jpg");
            if (!$img_exist) {
                return $default;
            }
            // make sure that caching is not an issue
            $v = filemtime(BASEPATH . "/img/users/{$user['username']}.jpg");
            $id = $user['_id'];
            if ($this->isPreview()) {
                $id = $user['username'];
            }
            $img = $path . "/users/{$id}.jpg?v=$v";
            return ' <img src="' . $img . '" alt="Profilbild" class="' . $class . '">';
        }
    }

    public function replaceLink($a)
    {
        return str_replace("href='/", "href='" . $this->basepath . "/",  $a);
    }

    public function setBasePath($path)
    {
        $this->basepath = $path;
    }

    public function getBasePath()
    {
        return $this->basepath;
    }

    /**
     * Fetch entity details from Portfolio API
     *
     * @param string $type Entity type (e.g., 'person', 'project', 'activity')
     * @param string $id Entity ID
     * @param string $view Optional view parameter
     * @param string $lang Language ('en' or 'de')
     * @return array|null Entity data or null on failure
     */
    public function fetch_entity($type, $id = '', $view = '')
    {
        // Call Portfolio API to get entity details
        $path = $this->apipath;
        $total_path = $path . '/' . urlencode($type);
        if (!empty($id) || $id === '0') {
            $total_path .= '/' . urlencode($id);
        }
        if (!empty($view)) {
            $total_path .= '/' . urlencode($view);
        }
        $total_path .= '?lang=' . urlencode($this->lang);
        if ($this->apikey !== null) {
            $total_path .= '&apikey=' . urlencode($this->apikey);
        }

        $ch = curl_init($total_path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $response = curl_exec($ch);
        if (!$ch || curl_errno($ch)) {
            return null;
        }

        $data = json_decode($response, true);
        $status = $data['status'] ?? 200;
        if (empty($data) || $status != 200) {
            return null;
        }
        return self::doc2Arr($data['data'] ?? null);
    }

    function isPreview()
    {
        return $this->preview;
    }

    /**
     * Helper function to convert MongoDB documents to arrays
     */
    public static function doc2Arr($doc)
    {
        if (empty($doc)) return array();
        if (is_array($doc)) return $doc;
        if ($doc instanceof MongoDB\Model\BSONArray) {
            return $doc->bsonSerialize();
        }
        if ($doc instanceof MongoDB\Model\BSONDocument) {
            return iterator_to_array($doc);
        }
        if ($doc instanceof MongoDB\Driver\Cursor) {
            return DB::doc2Arr($doc->toArray());
        }
        return $doc;
    }


    function getTopics()
    {
        $topics = $this->fetch_entity('topics');
        if (empty($topics) || !is_array($topics)) {
            return [];
        }
        return $topics;
    }

    /**
     * Build hierarchy array similar to the Vue implementation.
     *
     * @param array $groups List of units (each item should have: id, parent, order, name, name_de)
     * @param string|int $id Active unit id
     * @return array hierarchy rows, each row enriched with fields like: level, active, open, openable
     */
    function build_unit_hierarchy($id): array
    {
        $id = (string)$id;
        if (empty($id)) {
            $id = '0';
        }
        $groups = $this->fetch_entity('units');

        // Build quick lookup maps
        $byId = [];
        $childrenByParent = [];
        foreach ($groups as $g) {
            if (!isset($g['id'])) continue;
            $gid = (string)$g['id'];
            $byId[$gid] = $g;
            if ($g['level'] === 0) {
                $byId['0'] = $g;
            }

            $parentId = isset($g['parent']) ? (string)$g['parent'] : '';
            if (!isset($childrenByParent[$parentId])) {
                $childrenByParent[$parentId] = [];
            }
            $childrenByParent[$parentId][] = $g;
        }

        $getGroup = function ($gid) use ($byId) {
            $gid = (string)$gid;
            return $byId[$gid] ?? null;
        };

        // If group is invalid -> root hierarchy
        $group = $getGroup($id);
        if (!$group) {
            $group = $getGroup('0');
            if (!$group) return []; // no root available
            $id = '0';
        }

        // Get children of active group
        $children = $childrenByParent[(string)($group['id'] ?? '')] ?? [];

        // If no children exist, use parent as active target (like Vue)
        if (count($children) === 0) {
            $parentId = (string)($group['parent'] ?? '0');
            $group = $getGroup($parentId) ?: $getGroup('0');
            if (!$group) return [];
            $children = $childrenByParent[(string)($group['id'] ?? '')] ?? [];
        }

        // Sort children by 'order' (optional)
        usort($children, function ($a, $b) {
            $ao = (int)($a['order'] ?? 0);
            $bo = (int)($b['order'] ?? 0);
            return $ao <=> $bo;
        });

        // Collect parents of active group
        $parents = [];
        $parent = $getGroup((string)($group['parent'] ?? ''));
        while ($parent) {
            $parents[] = $parent;
            $parent = $getGroup((string)($parent['parent'] ?? ''));
        }
        $parents = array_reverse($parents);

        // Helper that produces the "hierarchy value" object
        $getHierarchyValue = function (array $g, bool $isOpen = false, int $level = 0, string $activeId = '') use ($childrenByParent) {
            $isActive = ((string)($g['id'] ?? '') === $activeId);
            if ($activeId === '0' && $g['level'] === 0) {
                $isActive = true;
            }
            // check if group has children
            $hasChildren = false;
            if (isset($childrenByParent[(string)($g['id'] ?? '')])) {
                $hasChildren = count($childrenByParent[(string)($g['id'] ?? '')]) > 0;
            }
            return [
                'id' => (string)($g['id'] ?? ''),
                'parent' => isset($g['parent']) ? (string)$g['parent'] : '',
                'name' => $g['name'] ?? '',
                'name_de' => $g['name_de'] ?? null,
                'order' => $g['order'] ?? null,
                // These fields drive your template logic/classes
                'level' => $level,
                'active' => $isActive,
                'open' => $isOpen,          // corresponds to Vue "true" in parents + active group
                'openable' => $hasChildren,      // tweak if you want only some items to be openable
                'hide' => $g['hide'] ?? false,            // set true if you later want to hide non-relevant nodes
            ];
        };

        // Build hierarchy: parents (open), active group (open+active), children (not open)
        $hierarchy = [];
        $level = 0;

        foreach ($parents as $p) {
            $hierarchy[] = $getHierarchyValue($p, true, $level, $id);
            $level++;
        }

        // Active group
        $hierarchy[] = $getHierarchyValue($group, true, $level, $id);

        // Children of active group
        foreach ($children as $c) {
            $hierarchy[] = $getHierarchyValue($c, false, $level + 1, $id);
        }

        return $hierarchy;
    }

    function getBreadCrumb($type, $data, $base = null, $usecase = 'portfolio')
    {
        $items = [];
        if ($base === null)
            $base = $this->getBasePath();

        $id = $data['id'] ?? '';
        $name = lang($data['name'] ?? $id, $data['name_de'] ?? null);

        // Home
        if ($usecase === 'portfolio') {
            $items[] = [
                'name' => lang('Research', 'Forschung'),
                'path' => $base . '/',
            ];
        } else if ($usecase === 'portal') {
            $items[] = [
                'name' => lang('Portal', 'Portal'),
                'path' => $base . '/info',
            ];
        } else {
            switch ($type) {
                case 'activity':
                    $breadcrumb = [
                        ['name' => lang('Activities', "Aktivitäten"), 'path' => "/activities"],
                        ['name' => $name, 'path' => "/activities/view/$id"],
                    ];
                    break;

                case 'person':
                    $breadcrumb = [
                        ['name' => lang('User', 'Personen'), 'path' => "/user/browse"],
                        ['name' => $name, 'path' => "/profile/$id"],
                    ];
                    break;

                case 'project':
                    $breadcrumb = [
                        ['name' => lang('Projects', 'Projekte'), 'path' => "/projects"],
                        ['name' => $name, 'path' => "/projects/view/$id"],
                    ];
                    break;

                case 'unit':
                    $breadcrumb = [
                        ['name' => lang('Units', 'Einheiten'), 'path' => "/groups"],
                        ['name' => $name, 'path' => "/groups/view/$id"],
                    ];
                    break;
                case 'infrastructure':
                    $breadcrumb = [
                        ['name' => lang('Infrastructures', 'Infrastrukturen'), 'path' => "/infrastructures"],
                        ['name' => $name, 'path' => "/infrastructures/view/$id"],
                    ];
                    break;
                default:
                    break;
            }
            $breadcrumb[] = ['name' => lang("Preview", "Vorschau")];
            return $breadcrumb;
        }
        // Type-specific
        switch ($type) {
            case 'activity':
                if ($data['type'] == 'publication') {
                    $items[] = ['name' => lang('All Publications', "Alle Publikationen"), 'path' => $base . "/publications"];
                } else {
                    $items[] = ['name' => lang('All Activities', "Alle Aktivitäten"), 'path' => $base . "/activities"];
                }
                $items[] = ['name' => $name, 'path' => $base . "/activities/view/$id"];
                break;

            case 'person':
                $items[] = ['name' => lang('All Staff', 'Alle Mitarbeitende'), 'path' => $base . "/persons"];
                $items[] = ['name' => $data['displayname'] ?? $name, 'path' => $base . "/person/$id"];
                break;

            case 'project':
                $items[] = ['name' => lang('All Projects', 'Alle Projekte'), 'path' => $base . "/projects"];
                $items[] = ['name' => $name, 'path' => $base . "/projects/view/$id"];
                break;

            case 'unit':
                // $items[] = ['name' => lang('Units', 'Einheiten'), 'path' => $base."/groups"];
                $items[] = ['name' => $name, 'path' => $base . "/groups/view/$id"];
                break;

            case 'infrastructure':
                $items[] = ['name' => lang('All Infrastructures', 'Alle Infrastrukturen'), 'path' => $base . "/infrastructures"];
                $items[] = ['name' => $name, 'path' => $base . "/infrastructure/$id"];
                break;

            case 'topic':
                // $items[] = ['name' => lang('All Topics', 'Alle Themen'), 'path' => $base . "/topics"];
                $items[] = ['name' => $name, 'path' => $base . "/topic/$id"];
                break;
            default:
                break;
        }
        return $items;
    }

    function renderBreadCrumb($type, $data, $base = null)
    {
        $items = $this->getBreadCrumb($type, $data, $base);

        // Render breadcrumb HTML
        $html = '<div class="breadcrumb-container">
  <nav aria-label="breadcrumbs" class="container-lg">
    <ul class="breadcrumb">';
        foreach ($items as $i => $el) {
            $isLast = ($i === count($items) - 1);
            $name = e($el['name'] ?? '');
            $url = $el['path'] ?? '#';

            if (!$isLast) {
                $html .= '<li>
            <a href="' . $url . '">
              ' . $name . '
            </a>
          </li>';
            } else {
                $html .= '<li class="active" aria-current="page">
            <a>' . $name . '</a>
          </li>';
            }
        }

        $html .= '</ul>
    </nav>
</div>';

        return $html;
    }
}
