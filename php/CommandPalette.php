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
                "label" => lang("My year", "Mein Jahr"),
                "permission" => "scientist",
                "keywords" => ["year", "my year", "calendar", "jahr", "mein jahr", "kalender"],
                "priority" => 60
            ],
            [
                "url" => "/my-activities",
                "type" => "Navigation",
                "icon" => "folder-user",
                "label" => lang("My activities", "Meine Aktivitäten"),
                "permission" => "scientist",
                "keywords" => ["activities", "my activities", "aktivitäten", "meine aktivitäten"],
                "priority" => 60
            ],
            [
                "url" => "/user/edit/" . $_SESSION['username'],
                "type" => lang("Action", "Aktion"),
                "label" => lang("User Settings", "Benutzereinstellungen"),
                "icon" => "gear",
                "keywords" => ["settings", "preferences", "account", "einstellungen", "präferenzen", "konto"],
                "priority" => 40
            ],
            [
                "url" => "/add-activity",
                "type" => lang("Action", "Aktion"),
                "icon" => "plus-circle",
                "label" => lang("Add activity", "Aktivität hinzufügen"),
                "keywords" => ["add activity", "new activity", "create activity", "neue aktivität", "aktivität erstellen"],
                "priority" => 70
            ],
            [
                "url" => "/proposals/new",
                "type" => lang("Action", "Aktion"),
                "icon" => "tree-structure",
                "label" => lang("Add project proposal", "Projektantrag hinzufügen"),
                "feature" => "projects",
                "permission" => "projects.add",
                "keywords" => ["add project proposal", "new project proposal", "create project proposal", "neuen Projektantrag", "Projektantrag erstellen"],
                "priority" => 70
            ],
            [
                "url" => "/projects/new",
                "type" => lang("Action", "Aktion"),
                "icon" => "tree-structure",
                "label" => lang("Add project", "Projekt hinzufügen"),
                "feature" => "projects",
                "permission" => "projects.add",
                "keywords" => ["add project", "new project", "create project", "neues projekt", "projekt erstellen"],
                "priority" => 70
            ],
            [
                "url" => "/conferences/new",
                "type" => lang("Action", "Aktion"),
                "icon" => "calendar-plus",
                "label" => lang("Add event", "Event hinzufügen"),
                "feature" => "events",
                "permission" => "conferences.edit",
                "keywords" => ["add event", "new event", "create event", "neues event", "event erstellen"],
                "priority" => 70
            ],
            [
                "url" => "/issues",
                "type" => "Navigation",
                "icon" => "bell",
                "label" => lang("Issues", "Hinweise"),
                "keywords" => ["issues", "hinweise"],
                "priority" => 10
            ],
            [
                "url" => "/queue/user",
                "type" => "Navigation",
                "icon" => "queue",
                "label" => lang("Queue to review", "Warteschlange zum Überprüfen"),
                "keywords" => ["queue", "review", "to review", "warteschlange", "überprüfen"],
                "priority" => 10
            ],
            [
                "url" => "/workflow-reviews",
                "type" => "Navigation",
                "icon" => "highlighter",
                "label" => lang("Reviews", "Überprüfungen"),
                "keywords" => ["reviews", "überprüfungen"],
                "priority" => 10
            ],
            [
                "url" => "/messages",
                "type" => "Navigation",
                "icon" => "envelope",
                "label" => lang("Messages", "Nachrichten"),
                "keywords" => ["messages", "nachrichten"],
                "priority" => 10
            ],
            [
                "url" => "/new-stuff",
                "type" => "Navigation",
                "icon" => "bell-ringing",
                "label" => lang("News", "Neuigkeiten"),
                "keywords" => ["news", "neuigkeiten", "changelog", "changes", "änderungen", "updates", "aktualisierungen"],
                "priority" => 10
            ],
            [
                "url" => "/calendar",
                "type" => "Navigation",
                "icon" => "calendar-dots",
                "label" => lang("Calendar", "Kalender"),
                "feature" => "calendar",
                "keywords" => ["calendar", "kalender"],
                "priority" => 20
            ],
            [
                "url" => "/activities/search",
                "type" => lang("Search", "Suchen"),
                "icon" => "magnifying-glass",
                "label" => lang("Search activities", "Aktivitäten durchsuchen"),
                "keywords" => ["search activities", "find activities", "aktivitäten durchsuchen", "aktivitäten finden"],
                "priority" => 30
            ],
            [
                "url" => "/activities",
                "type" => "Navigation",
                "icon" => "folders",
                "label" => lang("All activities", "Alle Aktivitäten"),
                "keywords" => ["all activities", "all aktivitäten", "alle aktivitäten", "publications", "publikationen", "transfer"],
                "priority" => 100
            ],
            [
                "url" => "/proposals/search",
                "type" => lang("Search", "Suchen"),
                "icon" => "magnifying-glass",
                "label" => lang("Search proposals", "Anträge durchsuchen"),
                "keywords" => ["search proposals", "find proposals", "anträge durchsuchen", "anträge finden"],
                "priority" => 60
            ],
            [
                "url" => "/proposals",
                "type" => "Navigation",
                "icon" => "tree-structure",
                "label" => lang("Proposals", "Anträge"),
                "feature" => "projects",
                "keywords" => ["proposals", "anträge"],
                "priority" => 70
            ],
            [
                "url" => "/projects/search",
                "type" => lang("Search", "Suchen"),
                "icon" => "magnifying-glass",
                "label" => lang("Search projects", "Projekte durchsuchen"),
                "keywords" => ["search projects", "find projects", "projekte durchsuchen", "projekte finden"],
                "priority" => 60
            ],
            [
                "url" => "/projects",
                "type" => "Navigation",
                "icon" => "tree-structure",
                "label" => lang("Projects", "Projekte"),
                "feature" => "projects",
                "keywords" => ["project", "projekt", "research project", "forschungsprojekt", "projects", "projekte", "drittmittelprojekte", "third-party projects"],
                "priority" => 90
            ],
            [
                "url" => "/nagoya",
                "type" => "Navigation",
                "icon" => "scales",
                "label" => lang("Nagoya Dashboard", "Nagoya-Dashboard"),
                "feature" => "nagoya",
                "permission" => "nagoya.view",
                "keywords" => ["nagoya", "dashboard", "nagoya dashboard", "nagoya-dashboard", "abs compliance", "access and benefit sharing", "abs", "compliance"],
                "priority" => 50
            ],
            [
                "url" => "/journals/search",
                "type" => lang("Search", "Suchen"),
                "icon" => "magnifying-glass",
                "label" => lang("Search journals", "Zeitschriften durchsuchen"),
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
                "type" => lang("Search", "Suchen"),
                "icon" => "magnifying-glass",
                "label" => lang("Search events", "Veranstaltungen durchsuchen"),
                "feature" => "events",
                "keywords" => ["search events", "find events", "veranstaltungen durchsuchen", "veranstaltungen finden"],
                "priority" => 30
            ],
            [
                "url" => "/conferences",
                "type" => "Navigation",
                "icon" => "calendar-dots",
                "label" => lang("Events", "Veranstaltungen"),
                "feature" => "events",
                "keywords" => ["events", "veranstaltungen", "conferences", "konferenzen"],
                "priority" => 50
            ],
            [
                "url" => "/teaching",
                "type" => "Navigation",
                "icon" => "chalkboard-simple",
                "label" => lang("Teaching modules", "Lehrmodule"),
                "feature" => "teaching-modules",
                "keywords" => ["teaching modules", "lehrmodule"],
                "priority" => 40
            ],
            [
                "url" => "/teaching/new",
                "type" => lang("Action", "Aktion"),
                "icon" => "chalkboard-simple",
                "label" => lang("Add teaching module", "Lehrmodul hinzufügen"),
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
                "label" => lang("Documents", "Dokumente"),
                "permission" => "documents",
                "keywords" => ["documents", "dokumente"],
                "priority" => 30
            ],
            [
                "url" => "/concepts",
                "type" => "Navigation",
                "icon" => "lightbulb",
                "label" => lang("Concepts", "Konzepte"),
                "feature" => "concepts",
                "keywords" => ["concepts", "konzepte"],
                "priority" => 0
            ],
            [
                "url" => "/persons/search",
                "type" => lang("Search", "Suchen"),
                "icon" => "magnifying-glass",
                "label" => lang("Search persons", "Personen durchsuchen"),
                "keywords" => ["search persons", "find persons", "personen durchsuchen", "personen finden"],
                "priority" => 30
            ],
            [
                "url" => "/user/browse",
                "type" => "Navigation",
                "icon" => "users",
                "label" => lang("Users", "Personen"),
                "keywords" => ["users", "personen", "nutzer", "wissenschaftler", "scientists"],
                "priority" => 90
            ],
            [
                "url" => "/groups",
                "type" => "Navigation",
                "icon" => "users-three",
                "label" => lang("Organisational Units", "Einheiten"),
                "keywords" => ["organisational units", "einheiten", "groups", "gruppen", "units"],
                "priority" => 70
            ],
            [
                "url" => "/organizations",
                "type" => "Navigation",
                "icon" => "building-office",
                "label" => lang("Organisations", "Organisationen"),
                "keywords" => ["organizations", "organisations", "institution", "institutions", "company", "companies", "organisationen", "externe organisationen"],
                "priority" => 60
            ],
            [
                "url" => "/guests",
                "type" => "Navigation",
                "icon" => "user-switch",
                "label" => lang("Guests", "Gäste"),
                "feature" => "guests",
                "keywords" => ["guests", "gäste"],
                "priority" => 20
            ],
            [
                "url" => "/dashboard",
                "type" => "Navigation",
                "icon" => "chart-line",
                "label" => lang("Dashboard", "Dashboard"),
                "keywords" => ["dashboard", "übersicht", "übersichtstafel"],
                "priority" => 30
            ],
            [
                "url" => "/visualize",
                "type" => "Navigation",
                "icon" => "graph",
                "label" => lang("Visualisations", "Visualisierung"),
                "keywords" => ["visualisations", "visualisierung", "visualization"],
                "priority" => 30
            ],
            [
                "url" => "/pivot",
                "type" => "Navigation",
                "icon" => "table",
                "label" => lang("Pivot table", "Pivot-Tabelle"),
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
                "label" => lang("Export Activities", "Aktivitäten exportieren"),
                "keywords" => ["export activities", "aktivitäten exportieren"],
                "priority" => 30
            ],
            [
                "url" => "/cart",
                "type" => "Navigation",
                "icon" => "basket",
                "label" => lang("Collection", "Sammlung"),
                "keywords" => ["collection", "sammlung", "download", "export", "word", "bibtex", "cart"],
                "priority" => 30
            ],
            [
                "url" => "/import",
                "type" => "Navigation",
                "icon" => "upload",
                "label" => lang("Import", "Importieren"),
                "keywords" => ["import", "importieren", "upload", "hochladen"],
                "priority" => 30
            ],
            [
                "url" => "/queue/editor",
                "type" => "Navigation",
                "icon" => "queue",
                "label" => lang("Queue", "Warteschlange"),
                "permission" => "report.queue",
                "keywords" => ["queue", "warteschlange"],
                "priority" => 30
            ],
            [
                "url" => "/reports",
                "type" => "Navigation",
                "icon" => "printer",
                "label" => lang("Reports", "Berichte"),
                "permission" => "report.generate",
                "keywords" => ["reports", "berichte"],
                "priority" => 30
            ],
            [
                "url" => "/portal/info",
                "type" => "Navigation",
                "icon" => "globe-hemisphere-west",
                "label" => lang("OSIRIS portal", "OSIRIS Portal"),
                "feature" => "portal-public",
                "keywords" => ["portal", "public"],
                "priority" => 20
            ],
            [
                "url" => "/ida/dashboard",
                "type" => "Navigation",
                "icon" => "clipboard-text",
                "label" => lang("IDA-Integration", "IDA-Integration"),
                "feature" => "ida",
                "keywords" => ["reports", "leibniz", "ida integration"],
                "priority" => 1
            ],
            [
                "url" => "/admin/general",
                "type" => lang("Action", "Aktion"),
                "icon" => "gear",
                "label" => lang("Admin Settings", "Admin-Einstellungen"),
                "permission" => "admin.see",
                "keywords" => ["settings", "einstellungen", "configuration", "konfiguration", "admin"],
                "priority" => 9
            ],
            [
                "url" => "/admin",
                "type" => "Navigation",
                "icon" => "treasure-chest",
                "label" => lang("Contents", "Inhalte"),
                "permission" => "admin.see",
                "keywords" => ["contents", "inhalte", "settings", "einstellungen", "configuration", "konfiguration", "admin"],
                "priority" => 9
            ],
            [
                "url" => "/admin/roles",
                "type" => "Navigation",
                "icon" => "shield-check",
                "label" => lang("Roles &amp; Rights", "Rollen &amp; Rechte"),
                "permission" => "admin.see",
                "keywords" => ["roles", "rights", "rollen", "rechte"],
                "priority" => 9
            ],
            [
                "url" => "/admin/reports",
                "type" => "Navigation",
                "icon" => "clipboard-text",
                "label" => lang("Report templates", "Berichtsvorlagen"),
                "permission" => "report.templates",
                "keywords" => ["report templates", "berichtsvorlagen", "templates"],
                "priority" => 9
            ],
            [
                "url" => "/admin/users",
                "type" => "Navigation",
                "icon" => "users",
                "label" => lang("User Management", "Nutzerverwaltung"),
                "permission" => "user.synchronize",
                "keywords" => ["users", "synchronization", "nutzer", "synchronisierung", "user management", "nutzerverwaltung"],
                "priority" => 9
            ]
        ];

        foreach ($elements as $e) {
            $this->add($e);
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
                "type" => lang("Category", "Kategorie"),
                "icon" => "bookmarks",
                "label" => lang($cat['name'], $cat['name_de'] ?? null),
                "keywords" => [$cat['name'], $cat['name_de'] ?? $cat['name'], $cat['id']],
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
                    'label' => lang('Go to...', 'Gehe zu...'),
                    'items' => $items
                ]
            ]
        ];
    }
}
