<?php

class SidebarNav
{
    protected Settings $settings;
    protected string $currentUri;
    protected array $definition = [];
    protected array $favorites = [];
    protected string $page = '';

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->favorites = $settings->sidebarFavorites;


        $uri = substr_replace($this->currentUri, '', 0, strlen(ROOTPATH . "/"));
        $lasturl = explode("/", $uri);
        $this->page =  $page ?? $lasturl[0];

        // -----------------------------
        // SIDEBAR DEFINITION
        // -----------------------------
        $this->definition = [
            [
                'id' => 'sidebar-activities',
                'label' => lang('Content', 'Inhalte'),
                'items' => [
                    [
                        'id' => 'activities',
                        'label' => lang('Activities', 'Aktivitäten'),
                        'icon' => 'folders',
                        'url' => '/activities',
                        'active' => ['^/activities($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => true
                    ],
                    [
                        'id' => 'proposals',
                        'label' => lang('Proposals', 'Anträge'),
                        'icon' => 'tree-structure',
                        'url' => '/proposals',
                        'active' => ['^/proposals($|/)'],
                        'feature' => 'proposals',
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => true
                    ],

                    [
                        'id' => 'projects',
                        'label' => lang('Projects', 'Projekte'),
                        'icon' => 'tree-structure',
                        'url' => '/projects',
                        'active' => ['^/projects($|/)'],
                        'feature' => 'projects',
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => true
                    ],
                    [
                        'id' => 'nagoya',
                        'label' => lang('Nagoya Dashboard', 'Nagoya-Dashboard'),
                        'icon' => 'scales',
                        'url' => '/nagoya',
                        'active' => ['^/nagoya($|/)'],
                        'feature' => 'nagoya',
                        'default' => false,
                        'permission' => 'nagoya.view',
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'journals',
                        'label' => $this->settings->journalLabel(),
                        'icon' => 'stack',
                        'url' => '/journals',
                        'active' => ['^/journals($|/)'],
                        'feature' => 'journals',
                        'default' => false,
                        'permission' => 'journals.view',
                        'favoritable' => true,
                        'hasSearch' => true
                    ],
                    [
                        'id' => 'events',
                        'label' => lang('Events', 'Veranstaltungen'),
                        'icon' => 'calendar-dots',
                        'url' => '/conferences', // legacy
                        'active' => ['^/conferences($|/)'],
                        'feature' => 'events',
                        'default' => true,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => true
                    ],
                    [
                        'id' => 'calendar',
                        'label' => lang('Calendar', 'Kalender'),
                        'icon' => 'calendar',
                        'url' => '/calendar',
                        'active' => ['^/calendar($|/)'],
                        'feature' => 'calendar',
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'teaching-modules',
                        'label' => lang('Teaching Modules', 'Lehrmodule'),
                        'icon' => 'chalkboard-simple',
                        'url' => '/teaching',
                        'active' => ['^/teaching($|/)'],
                        'feature' => 'teaching-modules',
                        'default' => true,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'topics',
                        'label' => $this->settings->topicLabel(),
                        'icon' => 'puzzle-piece',
                        'url' => '/topics',
                        'active' => ['^/topics($|/)'],
                        'feature' => 'topics',
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'infrastructures',
                        'label' => $this->settings->infrastructureLabel(),
                        'icon' => 'cube-transparent',
                        'url' => '/infrastructures',
                        'active' => ['^/infrastructures($|/)'],
                        'feature' => 'infrastructures',
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'documents',
                        'label' => lang('Documents', 'Dokumente'),
                        'icon' => 'files',
                        'url' => '/documents',
                        'active' => ['^/documents($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => 'documents',
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'concepts',
                        'label' => lang('Concepts', 'Konzepte'),
                        'icon' => 'lightbulb',
                        'url' => '/concepts',
                        'active' => ['^/concepts($|/)'],
                        'feature' => 'concepts',
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ]
                ]
            ],
            [
                'id' => 'sidebar-users',
                'label' => lang('Groups', 'Gruppen'),
                'items' => [
                    [
                        'id' => 'users',
                        'label' => lang('Users', 'Personen'),
                        'icon' => 'users',
                        'url' => '/user/browse',
                        'active' => ['^/(user|profile)($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => true
                    ],
                    [
                        'id' => 'groups',
                        'label' => lang('Organisational Units', 'Einheiten'),
                        'icon' => 'users-three',
                        'url' => '/groups',
                        'active' => ['^/groups($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'organizations',
                        'label' => lang('Organisations', 'Organisationen'),
                        'icon' => 'building',
                        'url' => '/organizations',
                        'active' => ['^/organizations($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ]
                ]
            ],
            [
                'id' => 'sidebar-tools',
                'label' => lang('Analysis', 'Analyse'),
                'items' => [
                    [
                        'id' => 'dashboard',
                        'label' => lang('Dashboard'),
                        'icon' => 'chart-line',
                        'url' => '/dashboard',
                        'active' => ['^/dashboard($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'visualize',
                        'label' => lang('Visualisations', 'Visualisierung'),
                        'icon' => 'graph',
                        'url' => '/visualize',
                        'active' => ['^/visualize($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'pivot',
                        'label' => lang('Pivot Tables', 'Pivot-Tabellen'),
                        'icon' => 'table',
                        'url' => '/pivot',
                        'active' => ['^/pivot($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'trips',
                        'label' => $this->settings->tripLabel(),
                        'icon' => 'map-trifold',
                        'url' => '/trips',
                        'active' => ['^/trips($|/)'],
                        'feature' => 'trips',
                        'default' => false,
                        'permission' => null,
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'portal-public',
                        'label' => lang('Go to portal', 'Zum Portal'),
                        'icon' => 'globe-hemisphere-west',
                        'url' => ROOTPATH . '/portal/info',
                        'active' => ['^/portal($|/)'],
                        'feature' => 'portal-public',
                        'default' => false,
                        'permission' => null,
                        'favoritable' => false,
                        'hasSearch' => false
                    ]
                ]
            ],

            [
                'id' => 'sidebar-export',
                'label' => lang('Export &amp; Import', 'Export &amp; Import'),
                'items' => [
                    [
                        'id' => 'download',
                        'label' => lang('Export', 'Export'),
                        'icon' => 'download',
                        'url' => '/download',
                        'active' => ['^/download($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => null,
                        'favoritable' => false,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'cart',
                        'label' => lang('Collection', 'Sammlung'),
                        'icon' => 'basket',
                        'url' => '/cart',
                        'active' => ['^/cart($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => null,
                        'favoritable' => false,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'import',
                        'label' => lang('Import', 'Import'),
                        'icon' => 'upload',
                        'url' => '/import',
                        'active' => ['^/import($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => null,
                        'favoritable' => false,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'queue',
                        'label' => lang('Queue', 'Warteschlange'),
                        'icon' => 'queue',
                        'url' => '/queue/editor',
                        'active' => ['^/queue($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => 'report.queue',
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'reports',
                        'label' => lang('Reports', 'Berichte'),
                        'icon' => 'printer',
                        'url' => '/reports',
                        'active' => ['^/reports($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => 'report.generate',
                        'favoritable' => true,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'ida',
                        'label' => lang('IDA-Integration', 'IDA-Integration'),
                        'icon' => 'clipboard-text',
                        'url' => '/ida/dashboard',
                        'active' => ['^/ida($|/)'],
                        'feature' => 'ida',
                        'default' => false,
                        'permission' => 'report.generate',
                        'favoritable' => true,
                        'hasSearch' => false
                    ]
                ]
            ],
            [
                'id' => 'sidebar-admin',
                'label' => lang('Admin', 'Admin'),
                'items' => [
                    [
                        'id' => 'settings',
                        'label' => lang('Settings', 'Einstellungen'),
                        'icon' => 'gear',
                        'url' => '/admin/general',
                        'active' => ['^/admin/general($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => 'admin.see',
                        'favoritable' => false,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'contents',
                        'label' => lang('Contents', 'Inhalte'),
                        'icon' => 'treasure-chest',
                        'url' => '/admin',
                        'active' => ['^/admin($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => 'admin.see',
                        'favoritable' => false,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'roles',
                        'label' => lang('Roles &amp; Rights', 'Rollen &amp; Rechte'),
                        'icon' => 'shield-check',
                        'url' => '/admin/roles',
                        'active' => ['^/admin/roles($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => 'admin.see',
                        'favoritable' => false,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'report-templates',
                        'label' => lang('Report Templates', 'Berichtsvorlagen'),
                        'icon' => 'clipboard-text',
                        'url' => '/admin/reports',
                        'active' => ['^/admin/reports($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => 'report.templates',
                        'favoritable' => false,
                        'hasSearch' => false
                    ],
                    [
                        'id' => 'users-admin',
                        'label' => lang('User Management', 'Benutzerverwaltung'),
                        'icon' => 'user-gear',
                        'url' => '/admin/users',
                        'active' => ['^/admin/users($|/)'],
                        'feature' => null,
                        'default' => false,
                        'permission' => 'user.synchronize',
                        'favoritable' => false,
                        'hasSearch' => false
                    ]
                ]
            ]
        ];
    }

    /* ------------------------------------
       PUBLIC API
    ------------------------------------ */

    public function get(): array
    {
        $visible = $this->filterVisible($this->definition);

        $favorites = $this->extractFavorites($visible);
        $remaining = $this->removeFavoriteItems($visible);

        $output = [];

        if (!empty($favorites)) {
            $output[] = [
                'id' => 'favorites',
                'label' => lang('Favorites', 'Favoriten'),
                'items' => $favorites
            ];
        }

        foreach ($remaining as $group) {
            if (!empty($group['items'])) {
                $output[] = $group;
            }
        }

        return $output;
    }

    /* ------------------------------------
       VISIBILITY LOGIC
    ------------------------------------ */

    protected function filterVisible(array $groups): array
    {
        foreach ($groups as &$group) {

            $group['items'] = array_filter($group['items'], function ($item) {

                if (
                    !empty($item['feature']) &&
                    !$this->settings->featureEnabled($item['feature'], $item['default'] ?? false)
                ) {
                    return false;
                }

                if (
                    !empty($item['permission']) &&
                    !$this->settings->hasPermission($item['permission'])
                ) {
                    return false;
                }

                return true;
            });

            foreach ($group['items'] as &$item) {
                $item['is_active'] = $this->isActive($item);
            }
        }

        return $groups;
    }

    protected function isActive(array $item): bool
    {
        if (empty($item['active'])) return false;

        foreach ($item['active'] as $pattern) {
            if (@preg_match('#' . $pattern . '#', $this->currentUri)) {
                return true;
            }
        }

        return false;

        // if ($page == $p) return "active";
        // $uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
        // if ((ROOTPATH . "/" . $p) == $uri) return 'active';
        // return "";
    }

    /* ------------------------------------
       FAVORITES HANDLING
    ------------------------------------ */

    protected function extractFavorites(array $groups): array
    {
        $favItems = [];

        foreach ($groups as $group) {
            foreach ($group['items'] as $item) {

                if (
                    !empty($item['favoritable']) &&
                    in_array($item['id'], $this->favorites)
                ) {

                    $favItems[] = $item;
                }
            }
        }

        // sort fav items according to the order in favorites setting
        usort($favItems, function ($a, $b) {
            $posA = array_search($a['id'], $this->favorites);
            $posB = array_search($b['id'], $this->favorites);
            return $posA <=> $posB;
        });

        return $favItems;
    }

    protected function removeFavoriteItems(array $groups): array
    {
        foreach ($groups as &$group) {

            $group['items'] = array_filter($group['items'], function ($item) {
                return !in_array($item['id'], $this->favorites);
            });
        }

        return $groups;
    }

    public function getFavoritableOptions(): array
    {
        $visible = $this->filterVisible($this->definition);

        $options = [];
        foreach ($visible as $group) {
            foreach ($group['items'] as $item) {
                if (empty($item['favoritable'])) continue;

                $options[] = [
                    'id' => $item['id'],
                    'label' => $item['label'],
                    'group' => $group['label'],
                    'icon' => $item['icon'] ?? null,
                ];
            }
        }
        return $options;
    }

    protected function renderItem(array $item): string
    {
        $html = '';
        $activeClass = $item['is_active'] ? ' active' : '';

        if ($item['hasSearch'] ?? false) {
            $html .= '<a href="' . ROOTPATH . $item['url'] . '/search" class="inline-btn" title="' . lang('Advanced Search', 'Erweiterte Suche') . '">';
            $html .= '<i class="ph-duotone ph-magnifying-glass-plus"></i>';
            $html .= '</a>';
        }
        $html .= '<a href="' . ROOTPATH . $item['url'] . '" class="with-icon' . $activeClass . '">';
        $html .= '<i class="ph ph-' . $item['icon'] . '" aria-hidden="true"></i>';
        $html .= $item['label'];
        $html .= '</a>';

        return $html;
    }

    protected function renderGroup(array $group): string
    {
        $html = '<div class="title collapse open" onclick="toggleSidebar(this);" id="' . $group['id'] . '">';
        $html .= $group['label'];
        $html .= '</div>';

        $html .= '<nav>';
        foreach ($group['items'] as $item) {
            $html .= $this->renderItem($item);
        }
        $html .= '</nav>';

        return $html;
    }

    public function render(): string
    {
        $items = [];
        $nav = $this->get();
        foreach ($nav as $group) {
            $items[] = $this->renderGroup($group);
        }
        return implode("\n", $items);
    }
}
