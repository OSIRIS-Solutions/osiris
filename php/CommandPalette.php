<?php

require_once "DB.php";
require_once "Settings.php";

class CommandPalette
{
    protected Settings $settings;
    protected array $items = [];

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;

        $topicLabel = $this->settings->topicLabel();

        $elements = [
            // [
            //     "url" => "/profile/" . $_SESSION['username'],
            //     "type" => "Navigation",
            //     "label" => lang(lang('Profile  of ', 'Profil von ') . ($_SESSION['name'] ?? $_SESSION['username']), null),
            //     "icon" => "student",
            //     "keywords" => ["profile", "user", "person", "me", "my profile", "my account", "mein profil", "mein konto"],
            //     "priority" => 100
            // ],
            [
                "url" => "/my-year",
                "type" => "Navigation",
                "icon" => "calendar",
                "label" => t('common.my_year_docs'),
                "permission" => "scientist",
                "keywords" => ["year", "my year", "calendar", "jahr", "mein jahr", "kalender"],
                "priority" => 60
            ],
            [
                "url" => "/my-activities",
                "type" => "Navigation",
                "icon" => "folder-user",
                "label" => t('activities.my_activities'),
                "permission" => "scientist",
                "keywords" => ["activities", "my activities", "aktivitäten", "meine aktivitäten"],
                "priority" => 60
            ],
            [
                "url" => "/user/edit/" . $_SESSION['username'],
                "type" => t('common.action'),
                "label" => t('navigation.user_settings_CommandPalette'),
                "icon" => "gear",
                "keywords" => ["settings", "preferences", "account", "einstellungen", "präferenzen", "konto"],
                "priority" => 40
            ],
            [
                "url" => "/add-activity",
                "type" => t('common.action'),
                "icon" => "plus-circle",
                "label" => t('activities.add_activity'),
                "keywords" => ["add activity", "new activity", "create activity", "neue aktivität", "aktivität erstellen"],
                "priority" => 70
            ],
            [
                "url" => "/proposals/new",
                "type" => t('common.action'),
                "icon" => "tree-structure",
                "label" => t('navigation.add_project_proposal'),
                "feature" => "projects",
                "permission" => "projects.add",
                "keywords" => ["add project proposal", "new project proposal", "create project proposal", "neuen Projektantrag", "Projektantrag erstellen"],
                "priority" => 70
            ],
            [
                "url" => "/projects/new",
                "type" => t('common.action'),
                "icon" => "tree-structure",
                "label" => t('dashboard.add_project'),
                "feature" => "projects",
                "permission" => "projects.add",
                "keywords" => ["add project", "new project", "create project", "neues projekt", "projekt erstellen"],
                "priority" => 70
            ],
            [
                "url" => "/conferences/new",
                "type" => t('common.action'),
                "icon" => "calendar-plus",
                "label" => t('action.add_event'),
                "feature" => "events",
                "permission" => "conferences.edit",
                "keywords" => ["add event", "new event", "create event", "neues event", "event erstellen"],
                "priority" => 70
            ],
            [
                "url" => "/issues",
                "type" => "Navigation",
                "icon" => "bell",
                "label" => t('dashboard.issues'),
                "keywords" => ["issues", "hinweise"],
                "priority" => 10
            ],
            [
                "url" => "/queue/user",
                "type" => "Navigation",
                "icon" => "queue",
                "label" => t('navigation.queue_to_review'),
                "keywords" => ["queue", "review", "to review", "warteschlange", "überprüfen"],
                "priority" => 10
            ],
            [
                "url" => "/workflow-reviews",
                "type" => "Navigation",
                "icon" => "highlighter",
                "label" => t('dashboard.reviews'),
                "keywords" => ["reviews", "überprüfungen"],
                "priority" => 10
            ],
            [
                "url" => "/messages",
                "type" => "Navigation",
                "icon" => "envelope",
                "label" => t('common.messages'),
                "keywords" => ["messages", "nachrichten"],
                "priority" => 10
            ],
            [
                "url" => "/news",
                "type" => "Navigation",
                "icon" => "bell-ringing",
                "label" => t('common.news'),
                "keywords" => ["news", "neuigkeiten", "changelog", "changes", "änderungen", "updates", "aktualisierungen"],
                "priority" => 10
            ],
            [
                "url" => "/calendar",
                "type" => "Navigation",
                "icon" => "calendar-dots",
                "label" => t('dashboard.calendar'),
                "feature" => "calendar",
                "keywords" => ["calendar", "kalender"],
                "priority" => 20
            ],
            [
                "url" => "/activities/search",
                "type" => t('action.search'),
                "icon" => "magnifying-glass",
                "label" => t('navigation.search_activities'),
                "keywords" => ["search activities", "find activities", "aktivitäten durchsuchen", "aktivitäten finden"],
                "priority" => 30
            ],
            [
                "url" => "/activities",
                "type" => "Navigation",
                "icon" => "folders",
                "label" => t('common.all_activities'),
                "keywords" => ["all activities", "all aktivitäten", "alle aktivitäten", "publications", "publikationen", "transfer"],
                "priority" => 100
            ],
            [
                "url" => "/proposals/search",
                "type" => t('action.search'),
                "icon" => "magnifying-glass",
                "label" => t('navigation.search_proposals'),
                "keywords" => ["search proposals", "find proposals", "anträge durchsuchen", "anträge finden"],
                "priority" => 60
            ],
            [
                "url" => "/proposals",
                "type" => "Navigation",
                "icon" => "tree-structure",
                "label" => t('common.proposals'),
                "feature" => "projects",
                "keywords" => ["proposals", "anträge"],
                "priority" => 70
            ],
            [
                "url" => "/projects/search",
                "type" => t('action.search'),
                "icon" => "magnifying-glass",
                "label" => t('navigation.search_projects'),
                "keywords" => ["search projects", "find projects", "projekte durchsuchen", "projekte finden"],
                "priority" => 60
            ],
            [
                "url" => "/projects",
                "type" => "Navigation",
                "icon" => "tree-structure",
                "label" => t('common.projects'),
                "feature" => "projects",
                "keywords" => ["project", "projekt", "research project", "forschungsprojekt", "projects", "projekte", "drittmittelprojekte", "third-party projects"],
                "priority" => 90
            ],
            [
                "url" => "/nagoya",
                "type" => "Navigation",
                "icon" => "scales",
                "label" => t('navigation.nagoya_dashboard'),
                "feature" => "nagoya",
                "permission" => "nagoya.view",
                "keywords" => ["nagoya", "dashboard", "nagoya dashboard", "nagoya-dashboard", "abs compliance", "access and benefit sharing", "abs", "compliance"],
                "priority" => 50
            ],
            [
                "url" => "/journals/search",
                "type" => t('action.search'),
                "icon" => "magnifying-glass",
                "label" => t('navigation.search_journals'),
                "keywords" => ["search journals", "find journals", "zeitschriften durchsuchen", "zeitschriften finden"],
                "priority" => 30
            ],
            [
                "url" => "/journals",
                "type" => "Navigation",
                "icon" => "stack",
                "label" => lang($this->settings->journalLabel(), null),
                "keywords" => ["journals", "zeitschriften", "periodicals", "periodika"],
                "priority" => 50
            ],
            [
                "url" => "/conferences/search",
                "type" => t('action.search'),
                "icon" => "magnifying-glass",
                "label" => t('navigation.search_events'),
                "feature" => "events",
                "keywords" => ["search events", "find events", "veranstaltungen durchsuchen", "veranstaltungen finden"],
                "priority" => 30
            ],
            [
                "url" => "/conferences",
                "type" => "Navigation",
                "icon" => "calendar-dots",
                "label" => t('common.events'),
                "feature" => "events",
                "keywords" => ["events", "veranstaltungen", "conferences", "konferenzen"],
                "priority" => 50
            ],
            [
                "url" => "/deadlines",
                "type" => "Navigation",
                "icon" => "flag",
                "label" => t('dashboard.deadlines'),
                "feature" => "deadlines",
                "keywords" => ["deadlines", "fristen", "deadlines", "fristen"],
                "priority" => 50
            ],
            [
                "url" => "/teaching",
                "type" => "Navigation",
                "icon" => "chalkboard-simple",
                "label" => t('common.teaching_modules'),
                "feature" => "teaching-modules",
                "keywords" => ["teaching modules", "lehrmodule"],
                "priority" => 40
            ],
            [
                "url" => "/teaching/new",
                "type" => t('common.action'),
                "icon" => "chalkboard-simple",
                "label" => t('navigation.add_teaching_module'),
                "feature" => "teaching-modules",
                "keywords" => ["add teaching module", "lehrmodul hinzufügen", "new teaching module", "neues lehrmodul", "create teaching module", "lehrmodul erstellen"],
                "priority" => 30
            ],
            [
                "url" => "/topics",
                "type" => "Navigation",
                "icon" => "puzzle-piece",
                "label" => $topicLabel,
                "feature" => "topics",
                "keywords" => ["topics", "themen", "forschungsbereiche", "research areas", "program areas", "programmbereiche"],
                "priority" => 90
            ],
            [
                "url" => "/infrastructures",
                "type" => "Navigation",
                "icon" => "cube-transparent",
                "label" => lang($this->settings->infrastructureLabel(), null),
                "feature" => "infrastructures",
                "keywords" => ["infrastructures", "infrastrukturen", "research infrastructures", "forschungsinfrastrukturen", "services", "dienstleistungen"],
                "priority" => 70
            ],
            [
                "url" => "/documents",
                "type" => "Navigation",
                "icon" => "files",
                "label" => t('common.documents'),
                "permission" => "documents",
                "keywords" => ["documents", "dokumente"],
                "priority" => 30
            ],
            [
                "url" => "/spectrum",
                "type" => "Navigation",
                "icon" => "lightbulb",
                "label" => t('common.research_spectrum'),
                "feature" => "spectrum",
                "keywords" => ["research spectrum", "forschungs-spektrum", "spectrum", "spektrum", "topics", "openalex topics", "openalex themen"],
                "priority" => 0
            ],
            [
                "url" => "/persons/search",
                "type" => t('action.search'),
                "icon" => "magnifying-glass",
                "label" => t('navigation.search_persons'),
                "keywords" => ["search persons", "find persons", "personen durchsuchen", "personen finden"],
                "priority" => 30
            ],
            [
                "url" => "/user/browse",
                "type" => "Navigation",
                "icon" => "users",
                "label" => t('common.users'),
                "keywords" => ["users", "personen", "nutzer", "wissenschaftler", "scientists"],
                "priority" => 90
            ],
            [
                "url" => "/groups",
                "type" => "Navigation",
                "icon" => "users-three",
                "label" => t('navigation.organisational_units'),
                "keywords" => ["organisational units", "einheiten", "groups", "gruppen", "units"],
                "priority" => 70
            ],
            [
                "url" => "/organizations",
                "type" => "Navigation",
                "icon" => "building-office",
                "label" => t('common.organizations'),
                "keywords" => ["organizations", "organisations", "institution", "institutions", "company", "companies", "organisationen", "externe organisationen"],
                "priority" => 60
            ],
            [
                "url" => "/guests",
                "type" => "Navigation",
                "icon" => "user-switch",
                "label" => t('common.guests'),
                "feature" => "guests",
                "keywords" => ["guests", "gäste"],
                "priority" => 20
            ],
            [
                "url" => "/dashboard",
                "type" => "Navigation",
                "icon" => "chart-line",
                "label" => t('navigation.dashboard'),
                "keywords" => ["dashboard", "übersicht", "übersichtstafel"],
                "priority" => 30
            ],
            [
                "url" => "/visualize",
                "type" => "Navigation",
                "icon" => "graph",
                "label" => t('navigation.visualisations'),
                "keywords" => ["visualisations", "visualisierung", "visualization"],
                "priority" => 30
            ],
            [
                "url" => "/pivot",
                "type" => "Navigation",
                "icon" => "table",
                "label" => t('navigation.pivot_table'),
                "keywords" => ["pivot table", "pivot-tabelle", "table", "tabelle"],
                "priority" => 30
            ],
            [
                "url" => "/trips",
                "type" => "Navigation",
                "icon" => "map-trifold",
                "label" => lang($this->settings->tripLabel(), null),
                "feature" => "trips",
                "keywords" => ["trips", "reisen", "reisen", "trips"],
                "priority" => 20
            ],
            [
                "url" => "/download",
                "type" => "Navigation",
                "icon" => "download",
                "label" => t('navigation.export_activities'),
                "keywords" => ["export activities", "aktivitäten exportieren"],
                "priority" => 30
            ],
            [
                "url" => "/cart",
                "type" => "Navigation",
                "icon" => "basket",
                "label" => t('reports.collection'),
                "keywords" => ["collection", "sammlung", "download", "export", "word", "bibtex", "cart"],
                "priority" => 30
            ],
            [
                "url" => "/import",
                "type" => "Navigation",
                "icon" => "upload",
                "label" => t('orcid.import'),
                "keywords" => ["import", "importieren", "upload", "hochladen"],
                "priority" => 30
            ],
            [
                "url" => "/queue/editor",
                "type" => "Navigation",
                "icon" => "queue",
                "label" => t('import.queue'),
                "permission" => "report.queue",
                "keywords" => ["queue", "warteschlange"],
                "priority" => 30
            ],
            [
                "url" => "/reports",
                "type" => "Navigation",
                "icon" => "printer",
                "label" => t('common.reports'),
                "permission" => "report.generate",
                "keywords" => ["reports", "berichte"],
                "priority" => 30
            ],
            [
                "url" => "/portal/info",
                "type" => "Navigation",
                "icon" => "globe-hemisphere-west",
                "label" => t('navigation.osiris_portal'),
                "feature" => "portal-public",
                "keywords" => ["portal", "public"],
                "priority" => 20
            ],
            [
                "url" => "/ida/dashboard",
                "type" => "Navigation",
                "icon" => "clipboard-text",
                "label" => t('common.ida_integration'),
                "feature" => "ida",
                "keywords" => ["reports", "leibniz", "ida integration"],
                "priority" => 1
            ],
            [
                "url" => "/admin/general",
                "type" => t('common.action'),
                "icon" => "gear",
                "label" => t('navigation.admin_settings'),
                "permission" => "admin.see",
                "keywords" => ["settings", "einstellungen", "configuration", "konfiguration", "admin"],
                "priority" => 9
            ],
            [
                "url" => "/admin",
                "type" => "Navigation",
                "icon" => "treasure-chest",
                "label" => t('navigation.contents'),
                "permission" => "admin.see",
                "keywords" => ["contents", "inhalte", "settings", "einstellungen", "configuration", "konfiguration", "admin"],
                "priority" => 9
            ],
            [
                "url" => "/admin/roles",
                "type" => "Navigation",
                "icon" => "shield-check",
                "label" => t('admin.roles_amp_rights'),
                "permission" => "admin.see",
                "keywords" => ["roles", "rights", "rollen", "rechte"],
                "priority" => 9
            ],
            [
                "url" => "/admin/reports",
                "type" => "Navigation",
                "icon" => "clipboard-text",
                "label" => t('navigation.report_templates'),
                "permission" => "report.templates",
                "keywords" => ["report templates", "berichtsvorlagen", "templates"],
                "priority" => 9
            ],
            [
                "url" => "/admin/users",
                "type" => "Navigation",
                "icon" => "users",
                "label" => t('people.user_management'),
                "permission" => "user.synchronize",
                "keywords" => ["users", "synchronization", "nutzer", "synchronisierung", "user management", "nutzerverwaltung"],
                "priority" => 9
            ]
        ];

        if ($this->settings->featureEnabled('initiatives') && ($this->settings->hasPermission('initiatives.view') || $this->settings->hasPermission('initiatives.edit'))) {
            $elements[] = [
                'url' => '/initiatives', 'type' => 'Navigation', 'icon' => 'compass',
                'label' => t('common.initiatives'),
                'keywords' => ['initiatives', 'initiativen', 'missions', 'missionen', 'working groups', 'arbeitsgruppen'],
                'priority' => 80,
            ];
            $elements[] = [
                'url' => '/initiatives?status=objectives', 'type' => 'Navigation', 'icon' => 'target',
                'label' => t('initiatives.objectives'),
                'keywords' => ['objectives', 'goals', 'ziele'],
                'priority' => 70,
            ];
        }

        foreach ($elements as $e) {
            $this->add($e);
        }

        // Resource Hub cards are few and configuration-backed, so keeping them in
        // the local palette index makes their titles and contents instantly searchable.
        if ($this->settings->featureEnabled('resource-hub')) {
            $resourceHub = DB::doc2Arr($this->settings->get('resource-hub') ?? []);
            foreach (DB::doc2Arr($resourceHub['cards'] ?? []) as $index => $rawCard) {
                $card = DB::doc2Arr($rawCard);
                $cardId = trim((string) ($card['id'] ?? 'resource-' . $index));
                if ($cardId === '') continue;

                $titles = DB::doc2Arr($card['title'] ?? []);
                $contents = DB::doc2Arr($card['content'] ?? []);
                $label = $this->localizedText($titles);
                $description = $this->searchText($this->localizedText($contents));
                if ($label === '' && $description === '') continue;

                $keywords = ['resource hub', 'ressourcen-hub', $this->settings->resourceHubLabel()];
                foreach ([$titles, $contents] as $localizedValues) {
                    foreach ((array) $localizedValues as $value) {
                        $keywords[] = $this->searchText($value);
                    }
                }
                foreach (DB::doc2Arr($card['links'] ?? []) as $rawLink) {
                    $link = DB::doc2Arr($rawLink);
                    foreach ((array) DB::doc2Arr($link['title'] ?? []) as $linkTitle) {
                        $keywords[] = $this->searchText($linkTitle);
                    }
                }

                $icon = trim((string) ($card['icon'] ?? ''));
                if (!preg_match('/^[a-z0-9-]+$/', $icon)) $icon = 'link';

                $this->add([
                    'id' => 'resource-hub:' . $cardId,
                    'url' => '/hub?card=' . rawurlencode($cardId),
                    'type' => $this->settings->resourceHubLabel(),
                    'icon' => $icon ?: $this->settings->resourceHubIcon(),
                    'label' => $label ?: $this->settings->resourceHubLabel(),
                    'description' => mb_strlen($description) > 180 ? mb_substr($description, 0, 177) . '…' : $description,
                    'feature' => 'resource-hub',
                    'keywords' => array_values(array_filter(array_unique($keywords))),
                    'priority' => 65,
                ]);
            }
        }

        foreach ($this->settings->topics as $t) {
            $this->add([
                "url" => "/topics/view/" . $t['id'],
                "type" => $topicLabel,
                "icon" => "puzzle-piece",
                "label" => lang($t['name'], $t['name_de'] ?? null),
                "feature" => "topics",
                "keywords" => [$topicLabel, $t['name'], $t['name_de'] ?? $t['name'], $t['id']],
                "priority" => 80
            ]);
        }

        foreach ($this->settings->activityCategories as $cat) {
            $this->add([
                "url" => "/activities#type=" . $cat['id'],
                "type" => t('common.category'),
                "icon" => "bookmarks",
                "label" => lang($cat['name'], $cat['name_de'] ?? null),
                "keywords" => [$cat['name'], $cat['name_de'] ?? $cat['name'], $cat['id']],
                "priority" => 80
            ]);
        }

        // --- Advanced search queries
        $filter = [
            '$or' => [
                ['user' => $_SESSION['username']],
                ['global' => true],
                ['role' => ['$in' => $this->settings->roles]]
            ]
        ];
        $queries = $this->settings->osiris->queries->find($filter);
        $collectionMap = [
            'activities' => t('common.activities'),
            'projects' => t('common.projects'),
            'proposals' => t('common.proposals'),
            'conferences' => t('common.events'),
            'journals' => t('common.journals'),
            'persons' => t('common.persons')
        ];
        foreach ($queries as $query) {
            $this->add([
                "url" => "/" . ($query['type'] ?? 'activities') . "/search?query=" . $query['_id'],
                "type" => t('navigation.saved_search'),
                "icon" => "magnifying-glass",
                "label" => $query['name'],
                "description" => (isset($collectionMap[$query['type']]) ? t('navigation.search_in') . $collectionMap[$query['type']] : null),
                "keywords" => [$query['name'], "saved search", "gespeicherte suche", "advanced search", "erweiterte suche"],
                "priority" => 80
            ]);
        }
        
    }

    public function add(array $item): void
    {
        if (!$this->isVisible($item)) {
            return;
        }

        $this->items[] = $item;
    }

    public function get(): array
    {
        return $this->groupAndSort($this->items);
    }

    protected function localizedText($value): string
    {
        $value = DB::doc2Arr($value ?? []);
        if (!is_array($value)) return trim((string) $value);

        $language = currentLanguage() === 'de' ? 'de' : 'en';
        $fallback = $language === 'de' ? 'en' : 'de';
        $localized = trim((string) ($value[$language] ?? ''));
        return $localized !== '' ? $localized : trim((string) ($value[$fallback] ?? ''));
    }

    protected function searchText($value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    protected function isVisible(array $item): bool
    {
        if (
            !empty($item['feature'])
            && !$this->settings->featureEnabled($item['feature'])
        ) {
            return false;
        }

        if (
            !empty($item['permission'])
            && !$this->settings->hasPermission($item['permission'])
        ) {
            return false;
        }

        return true;
    }

    protected function groupAndSort(array $items): array
    {
        usort($items, function ($a, $b) {
            return ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0);
        });

        return [
            'groups' => [
                [
                    'id' => 'main',
                    'label' => t('navigation.go_to'),
                    'items' => $items
                ]
            ]
        ];
    }
}
