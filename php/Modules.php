<?php
include_once "_config.php";
include_once "init.php";

$defaultauthors = [
    [
        "last" => "Koblitz",
        "first" => "Julia",
        "aoi" => true,
        "position" => "first",
        "user" => "jkoblitz",
        "approved" => true,
        "sws" => 2
    ],
    [
        "last" => "Koblitz",
        "first" => "Dominic",
        "aoi" => true,
        "position" => "last",
        "user" => "dkoblitz",
        "approved" => true,
        "sws" => 0.3
    ]
];
$defaultstart = ["year" => 2022, "month" => 9, "day" => 6];
$defaultend = ["year" => 2022, "month" => 9, "day" => 8];

class Modules
{
    public $form = array();
    private $copy = false;
    private $authors = "";
    private $editors = "";
    private $preset = array();
    private $first = 1;
    private $last = 1;
    private $authorcount = 0;
    private $user = '';
    private $userlist = array();
    private $conference = array();

    public $all_modules = array(
        "authors" => [
            "fields" => ["authors" => [
                [
                    "last" => "Koblitz",
                    "first" => "Julia",
                    "aoi" => true,
                    "position" => "first",
                    "user" => "jkoblitz",
                    "approved" => true,
                    "sws" => 2
                ],
                [
                    "last" => "Koblitz",
                    "first" => "Dominic",
                    "aoi" => true,
                    "position" => "last",
                    "user" => "dkoblitz",
                    "approved" => true,
                    "sws" => 0.3
                ]
            ]],
            "name" => "Authors",
            "name_de" => "Autoren",
            "description" => "A compressed module for authors, with 'tag-like' input fields for authors. Supports multiple authors, drag-and-drop, auto-suggest and affiliation-flagging via double click.",
            "description_de" => "Ein komprimiertes Modul für Autoren, mit 'tag-ähnlichen' Eingabefeldern für Autoren. Unterstützt mehrere Autoren, Drag-and-Drop, Auto-Suggest und Affiliation-Flagging per Doppelklick."
        ],
        "author-table" => [
            "fields" => ["authors" => [
                [
                    "last" => "Koblitz",
                    "first" => "Julia",
                    "aoi" => true,
                    "position" => "first",
                    "user" => "jkoblitz",
                    "approved" => true,
                    "sws" => 2
                ],
                [
                    "last" => "Koblitz",
                    "first" => "Dominic",
                    "aoi" => true,
                    "position" => "last",
                    "user" => "dkoblitz",
                    "approved" => true,
                    "sws" => 0.3
                ]
            ]],
            "name" => "Authors",
            "name_de" => "Autoren",
            "description" => "A comprehensive table for authors, with individual input fields for authors, including first an last name, position, username and an affiliation checkbox. Supports multiple authors, drag-and-drop, and auto-suggest on username field.",
            "description_de" => "Eine umfassende Tabelle für Autoren, mit individuellen Eingabefeldern für Autoren, einschließlich Vor- und Nachname, Position, Benutzername und einem Affiliation-Checkbox. Unterstützt mehrere Autoren, Drag-and-Drop und Auto-Suggest im Benutzernamenfeld."
        ],
        "book-series" => [
            "fields" => ["series" => 'Book Series on Open Source Systems'],
            "name" => "Book-Series",
            "name_de" => "Bücherreihe",
            "description" => "A field for the book series, where the publication is part of.",
            "description_de" => "Ein Feld für die Buchreihe, zu der die Publikation gehört."
        ],
        "book-title" => [
            "fields" => ["book" => 'Research Information Systems'],
            "name" => "Book Title",
            "name_de" => "Buchtitel",
            "description" => "A field for the book title, where the publication is part of.",
            "description_de" => "Ein Feld für den Buchtitel, zu dem die Publikation gehört."
        ],
        "city" => [
            "fields" => ["city" => 'Helmstedt, Deutschland'],
            "name" => "City",
            "name_de" => "Stadt",
            "description" => "A field for a city, e.g for the publisher of a book or for a conference location.",
            "description_de" => "Ein Feld für eine Stadt, z.B. für den Verlag eines Buches oder für einen Konferenzort."
        ],
        "conference" => [
            "fields" => ["conference" => '1st CRIS Conference'],
            "name" => "Conference",
            "name_de" => "Konferenz",
            "description" => "A field for the name of a conference/event.",
            "description_de" => "Ein Feld für den Namen einer Konferenz/Veranstaltung."
        ],
        "correction" => [
            "fields" => ["correction" => true],
            "name" => "Correction",
            "name_de" => "Correction",
            "description" => "Checkbox for a correction flag.",
            "description_de" => "Checkbox für eine Korrekturmarkierung."
        ],
        "date-range" => [
            "fields" => ["start" => ["year" => 2022, "month" => 9, "day" => 6], "end" => ["year" => 2022, "month" => 9, "day" => 8]],
            "name" => "Date Range",
            "name_de" => "Zeitspanne",
            "description" => "A field for a date range, e.g. for a event. <b>Important</b>: If the end date is not set, the date is assumed to be only one day! If you want an ongoing event, please use the 'date-range-ongoing' module.",
            "description_de" => "Ein Feld für einen Zeitraum, z.B. für eine Konferenz. <b>Wichtig</b>: Wenn das Enddatum nicht gesetzt ist, wird das Datum als nur ein Tag angenommen! Wenn Sie ein laufendes Ereignis möchten, verwenden Sie bitte das Modul 'date-range-ongoing'."
        ],
        "date-range-ongoing" => [
            "fields" => ["start" => ["year" => 2022, "month" => 9, "day" => 6], "end" => null],
            "name" => "Date Range",
            "name_de" => "Zeitspanne",
            "description" => "A field for a possibly ongoing date range, e.g. for a membership. <b>Important</b>: If the end date is not set, the date is assumed to be ongoing! If you want a fixed date range, please use the 'date-range' module.",
            "description_de" => "Ein Feld für einen möglicherweise laufenden Zeitraum, z.B. für eine Mitgliedschaft. <b>Wichtig</b>: Wenn das Enddatum nicht gesetzt ist, wird das Datum als laufend angenommen! Wenn Sie einen festen Zeitraum möchten, verwenden Sie bitte das Modul 'date-range'."
        ],
        "date" => [
            "fields" => ["year" => 2023, "month" => 5, "day" => 4],
            "name" => "Date",
            "name_de" => "Datum",
            "description" => "A field for a date, divided into year, month and day. A shortcut button for today's date is available.",
            "description_de" => "Ein Feld für ein Datum, aufgeteilt in Jahr, Monat und Tag. Ein Shortcut-Button für das heutige Datum ist verfügbar."
        ],
        "details" => [
            "fields" => ["details" => "Weitere Details"],
            "name" => "Details",
            "name_de" => "Details",
            "description" => "A field for additional details, e.g. for a grant.",
            "description_de" => "Ein Feld für weitere Details, z.B. für ein Stipendium."
        ],
        "doctype" => [
            "fields" => ["doc_type" => 'White Paper'],
            "name" => "Doctype",
            "name_de" => "Doctype",
            "description" => "A field for the document type, e.g. for a report.",
            "description_de" => "Ein Feld für den Dokumententyp, z.B. für einen Bericht."
        ],
        "doi" => [
            "fields" => ["doi" => '10.1234/idk/invalid'],
            "name" => "DOI",
            "name_de" => "DOI",
            "description" => "A field for the Digital Object Identifier (DOI) of a publication. This field is used to uniquely identify a publication.",
            "description_de" => "Ein Feld für den Digital Object Identifier (DOI) einer Publikation. Dieses Feld wird verwendet, um eine Publikation eindeutig zu identifizieren."
        ],
        "edition" => [
            "fields" => ["edition" => 2],
            "name" => "Edition",
            "name_de" => "Edition",
            "description" => "A field for the edition of a publication, e.g. for a book.",
            "description_de" => "Ein Feld für die Auflage einer Publikation, z.B. für ein Buch."
        ],
        "editor" => [
            "fields" => ["editors" => [
                [
                    "last" => "Koblitz",
                    "first" => "Julia",
                    "aoi" => true,
                    "position" => "first",
                    "user" => "jkoblitz",
                    "approved" => true,
                    "sws" => 2
                ],
                [
                    "last" => "Koblitz",
                    "first" => "Dominic",
                    "aoi" => true,
                    "position" => "last",
                    "user" => "dkoblitz",
                    "approved" => true,
                    "sws" => 0.3
                ]
            ]],
            "name" => "Editors",
            "name_de" => "Editoren",
            "description" => "A compressed module for editors, with 'tag-like' input fields for editors. Supports multiple editors, drag-and-drop, auto-suggest and affiliation-flagging via double click.",
            "description_de" => "Ein komprimiertes Modul für Editoren, mit 'tag-ähnlichen' Eingabefeldern für Editoren. Unterstützt mehrere Editoren, Drag-and-Drop, Auto-Suggest und Affiliation-Flagging per Doppelklick."
        ],
        "editorial" => [
            "fields" => ["editor_type" => 'Guest Editor'],
            "name" => "Editorial",
            "name_de" => "Editorenschaft",
            "description" => "A field for the editorial type, e.g. for a special issue.",
        ],
        "event-select" => [
            "fields" => [],
            "name" => "Event Selector",
            "name_de" => "Veranstaltungsauswahl",
            "description" => "A field for selecting an event that has been entered in the system. If the user clicks on an event, date, loation and event name are automatically filled in.",
            "description_de" => "Ein Feld zum Auswählen einer Veranstaltung, die bereits im System erfasst wurde. Wenn ein:e Benutzer:in auf eine Veranstaltung klickt, werden Datum, Ort und Veranstaltungsname automatisch ausgefüllt."

        ],
        "guest" => [
            "fields" => ["category" => 'guest scientist'],
            "name" => "Category",
            "name_de" => "Kategorie",
            "description" => "A field for the category of a guest, can be one of the following: guest scientist, lecture internship, student internship, other.",
            "description_de" => "Ein Feld für die Kategorie eines Gastes, kann eine der folgenden sein: Gastwissenschaftler:in, Pflichtpraktikum im Rahmen des Studium, Schülerpraktikum, Sonstiges."
        ],
        "gender" => [
            "fields" => ["gender" => 'f'],
            "name" => "Gender",
            "name_de" => "Geschlecht",
            "description" => "A field to select the gender of a person. Can be one of the following: male, female, non-binary, not specified.",
            "description_de" => "Ein Feld zur Auswahl des Geschlechts einer Person. Kann eines der folgenden sein: männlich, weiblich, divers, nicht spezifiziert."
        ],
        "nationality" => [
            "fields" => ["country" => 'DE'],
            "name" => "Nationality",
            "name_de" => "Nationalität",
            "description" => "A synonym for the 'country' field, used for persons.",
            "description_de" => "Ein Synonym für das Feld 'Land', das für Personen verwendet wird."
        ],
        "country" => [
            "fields" => ["country" => 'DE'],
            "name" => "Country",
            "name_de" => "Land",
            "description" => "A field for a country, that can be selected from a list and is saved as a two-letter ISO country code.",
            "description_de" => "Ein Feld für ein Land, das aus einer Liste ausgewählt werden kann und als zweistelliger ISO-Ländercode gespeichert wird."
        ],
        "abstract" => [
            "fields" => ["abstract" => 'OSIRIS ist einzigartig in seinen Konfigurationsmöglichkeiten. Während sich viele andere CRIS nur auf Publikationen beschränken, kann in OSIRIS eine Vielzahl an Aktivitäten hinzugefügt werden.'],
            "name" => "Abstract",
            "name_de" => "Zusammenfassung",
            "description" => "A field for the abstract, summarizing an activity.",
            "description_de" => "Ein Feld für die Zusammenfassung, die eine Aktivität zusammenfasst."
        ],
        "isbn" => [
            "fields" => ["isbn" => '979-8716615502'],
            "name" => "ISBN",
            "name_de" => "ISBN",
            "description" => "A field for the International Standard Book Number (ISBN) of a publication. This field is used to uniquely identify a book.",
            "description_de" => "Ein Feld für die Internationale Standardbuchnummer (ISBN) einer Publikation. Dieses Feld wird verwendet, um ein Buch eindeutig zu identifizieren."
        ],
        "issn" => [
            "fields" => ["issn" => ["1362-4962", "0305-1048"]],
            "name" => "ISSN",
            "name_de" => "ISSN",
            "description" => "A field for the International Standard Serial Number (ISSN) of a publication. This field is used to uniquely identify a journal. May contain multiple ISSNs, separated by comma.",
            "description_de" => "Ein Feld für die Internationale Standardnummer für fortlaufende Sammelwerke (ISSN) einer Publikation. Dieses Feld wird verwendet, um eine Zeitschrift eindeutig zu identifizieren. Kann mehrere ISSNs enthalten, die durch Komma getrennt sind."
        ],
        "issue" => [
            "fields" => ["issue" => "D1"],
            "name" => "Issue",
            "name_de" => "Issue",
            "description" => "A field for the issue of a publication, e.g. for a journal.",
            "description_de" => "Ein Feld für das Heft einer Publikation, z.B. für eine Zeitschrift."
        ],
        "iteration" => [
            "fields" => ["iteration" => "annual"],
            "name" => "Iteration",
            "name_de" => "Häufigkeit",
            "description" => "A field for the iteration of an event, e.g. for a conference. Possible values are: continously or once.",
            "description_de" => "Ein Feld für die Häufigkeit einer Veranstaltung, z.B. für eine Konferenz. Mögliche Werte sind: kontinuierlich oder einmalig."
        ],
        "journal" => [
            "fields" => ["journal" => 'Information Systems Research', "journal_id" => null],
            "name" => "Journal",
            "name_de" => "Journal",
            "description" => "A field for selecting a journal from the database. If the journal does not exist yet, the user will be prompted to select one from an online catalogue that will be then saved into the database.",
            "description_de" => "Ein Feld zum Auswählen eines Journals (Zeitschrift) aus der Datenbank. Wenn das Journal noch nicht existiert, wird der Benutzer aufgefordert, eines aus einem Online-Katalog auszuwählen, das dann in die Datenbank gespeichert wird."
        ],
        "lecture-invited" => [
            "fields" => ["invited_lecture" => true],
            "name" => "Invited lecture",
            "name_de" => "Eingeladener Vortrag",
            "description" => "Checkbox for an invited lecture flag.",
            "description_de" => "Checkbox für eine eingeladene Vortragsmarkierung."
        ],
        "lecture-type" => [
            "fields" => ["lecture_type" => 'short'],
            "name" => "Lecture-Length",
            "name_de" => "Länge des Vortrags",
            "description" => "A field for the length of a lecture, e.g. for a conference. Possible values are: short, medium, long.",
            "description_de" => "Ein Feld für die Länge eines Vortrags, z.B. für eine Konferenz. Mögliche Werte sind: kurz, mittel, lang."
        ],
        "license" => [
            "fields" => ["license" => 'MIT'],
            "name" => "License",
            "name_de" => "Lizenz",
            "description" => "A field for the license, e.g. for software.",
            "description_de" => "Ein Feld für die Lizenz, z.B. für Software."
        ],
        "link" => [
            "fields" => ["link" => 'https://osiris-app.de'],
            "name" => "Link",
            "name_de" => "Link",
            "description" => "A field for a link, e.g. for an event.",
            "description_de" => "Ein Feld für einen Link, z.B. für eine Veranstaltung."
        ],
        "location" => [
            "fields" => ["location" => 'Braunschweig, Germany'],
            "name" => "Location",
            "name_de" => "Ort",
            "description" => "A field for the more general location, e.g. for an event. If you want to specify a city an country, please use the 'city' and 'country' modules.",
            "description_de" => "Ein Feld für den allgemeineren Ort, z.B. für eine Veranstaltung. Wenn Sie eine Stadt und ein Land angeben möchten, verwenden Sie bitte die Module 'city' und 'country'."
        ],
        "magazine" => [
            "fields" => ["magazine" => 'Apothekenumschau'],
            "name" => "Magazine",
            "name_de" => "Magazin",
            "description" => "A field for the magazine or publication venue, where the publication is part of. Not standardized and typically used if no journal is given.",
            "description_de" => "Ein Feld für das Magazin oder den Veröffentlichungsort, zu dem die Publikation gehört. Nicht standardisiert und typischerweise verwendet, wenn keine Zeitschrift angegeben ist."
        ],
        "online-ahead-of-print" => [
            "fields" => ["epub" => true],
            "name" => "Online Ahead Of Print",
            "name_de" => "Online Ahead Of Print",
            "description" => "Checkbox for an online ahead of print flag. This flag is used to indicate that a publication is available online before it is published in a journal. Checking will lead to a frequent reminder to update the publication status.",
            "description_de" => "Checkbox für eine Online Ahead Of Print-Markierung. Dieses Flag wird verwendet, um anzuzeigen, dass eine Publikation online verfügbar ist, bevor sie in einer Zeitschrift veröffentlicht wird. Das Ankreuzen führt zu einer gelegentlichen Erinnerung, den Publikationsstatus zu überprüfen."
        ],
        "openaccess" => [
            "fields" => ["open_access" => true],
            "name" => "Open-Access",
            "name_de" => "Open-Access",
            "description" => "Checkbox for an open access flag. This flag is used to indicate that a publication is freely available online. Important: If the actual open access status is important, please use the 'openaccess-status' module.",
            "description_de" => "Checkbox für eine Open-Access-Markierung. Dieses Flag wird verwendet, um anzuzeigen, dass eine Publikation frei online verfügbar ist. Wichtig: Wenn der tatsächliche Open-Access-Status wichtig ist, verwenden Sie bitte das Modul 'openaccess-status'."
        ],
        "openaccess-status" => [
            "fields" => ["open_access" => true, "oa_status" => 'gold'],
            "name" => "Open-Access",
            "name_de" => "Open-Access",
            "description" => "A field for the open access status of a publication. Possible values are: gold, green, hybrid, bronze, closed.",
            "description_de" => "Ein Feld für den Open-Access-Status einer Publikation. Mögliche Werte sind: gold, green, hybrid, bronze, closed."
        ],
        "pages" => [
            "fields" => ["pages" => 'D1531-8'],
            "name" => "Pages",
            "name_de" => "Seiten",
            "description" => "A field for the pages of a publication, e.g. for a journal.",
            "description_de" => "Ein Feld für die Seiten einer Publikation, z.B. für eine Zeitschrift."
        ],
        "peer-reviewed" => [
            "fields" => ["peer-reviewed" => true],
            "name" => "Peer-Reviewed",
            "name_de" => "Peer-Reviewed",
            "description" => "Checkbox for a peer-reviewed flag. This flag is used to indicate that a publication has been peer-reviewed.",
            "description_de" => "Checkbox für eine Peer-Reviewed-Markierung. Dieses Flag wird verwendet, um anzuzeigen, dass eine Publikation peer-reviewed wurde."
        ],
        "person" => [
            "fields" => ["name" => "Koblitz, Julia", "affiliation" => "DSMZ", "academic_title" => "Dr."],
            "name" => "Person",
            "name_de" => "Person",
            "description" => "A fieldset for a person, including name, affiliation and academic title.",
            "description_de" => "Ein Feldset für eine Person, einschließlich Name, Affiliation und akademischem Titel."
        ],
        "publisher" => [
            "fields" => ["publisher" => 'Oxford'],
            "name" => "Publisher",
            "name_de" => "Verlag",
            "description" => "A field for the publisher of a publication, e.g. for a book.",
            "description_de" => "Ein Feld für den Verlag einer Publikation, z.B. für ein Buch."
        ],
        "pubmed" => [
            "fields" => ["pubmed" => 1234567],
            "name" => "Pubmed-ID",
            "name_de" => "Pubmed-ID",
            "description" => "A field for the PubMed ID of a publication. This field is used to uniquely identify a publication.",
            "description_de" => "Ein Feld für die PubMed-ID einer Publikation. Dieses Feld wird verwendet, um eine Publikation eindeutig zu identifizieren."
        ],
        "pubtype" => [
            "fields" => ["pubtype" => "article"],
            "name" => "Pubtype",
            "name_de" => "Pubtype",
            "description" => "A field for the publication type, e.g. for a journal article. Only to be used if the publication type is not defined by the subtype.",
            "description_de" => "Ein Feld für den Publikationstyp, z.B. für einen Zeitschriftenartikel. Nur zu nutzen, wenn der Publikationstyp nicht durch den Subtyp festgelegt wird."
        ],
        "review-type" => [
            "fields" => ["review-type" => "Begutachtung eines Forschungsantrages"],
            "name" => "Review Type",
            "name_de" => "Review-Art",
            "description" => "A field for the type of review, e.g. for a grant.",
            "description_de" => "Ein Feld für die Art der Begutachtung, z.B. für ein Stipendium."
        ],
        "role" => [
            "fields" => ["role" => "Organisator:in"],
            "name" => "Role/Function",
            "name_de" => "Rolle/Funktion",
            "description" => "A field for the role or function of a person, e.g. for an event.",
            "description_de" => "Ein Feld für die Rolle oder Funktion einer Person, z.B. für eine Veranstaltung."
        ],
        "scientist" => [
            "fields" => ["authors" =>
            [[
                "last" => "Koblitz",
                "first" => "Dominic",
                "aoi" => true,
                "position" => "last",
                "user" => "dkoblitz",
                "approved" => true,
                "sws" => 0.3

            ]],],
            "name" => "Scientist",
            "name_de" => "Wissenschaftler:in",
            "description" => "A selection field for an author from the institute. Limited to the persons in the database and supports only one author.",
            "description_de" => "Ein Auswahlfeld für einen Autor aus dem Institut. Beschränkt auf die Personen in der Datenbank und unterstützt nur einen Autor."
        ],
        "semester-select" => [
            "fields" => [],
            "name" => "Semester Selector",
            "name_de" => "Semesterauswahl",
            "description" => "A field for preselecting a semester, e.g. the next Summer Semester. If the user clicks on a semester, the date range is automatically adjusted.",
            "description_de" => "Ein Feld zur Vorauswahl eines Semesters, z.B. des nächsten Sommersemesters. Wenn ein:e Benutzer:in auf ein Semester klickt, wird der Zeitraum automatisch angepasst."
        ],
        "scope" => [
            "fields" => ["scope" => "national"],
            "name" => "Scope",
            "name_de" => "Reichweite",
            "description" => "A field for the scope of an event, e.g. for a conference. Possible values are: local, regional, national, international.",
            "description_de" => "Ein Feld für den Geltungsbereich einer Veranstaltung, z.B. für eine Konferenz. Mögliche Werte sind: lokal, regional, national, international."
        ],
        "software-link" => [
            "fields" => ["link" => "https://osiris-app.de"],
            "name" => "Link",
            "name_de" => "Link",
            "description" => "Synonym for a link used for software.",
            "description_de" => "Synonym für einen Link, der für Software verwendet wird."
        ],
        "software-type" => [
            "fields" => ["software_type" => "Database"],
            "name" => "Type of Software",
            "name_de" => "Software-Type",
            "description" => "A field for the type of software, possible vales are software, database, dataset, webtool, report.",
            "description_de" => "Ein Feld für den Typ der Software, mögliche Werte sind Software, Datenbank, Datensatz, Webtool, Bericht."

        ],
        "software-venue" => [
            "fields" => ["software_venue" => "GitHub"],
            "name" => "Venue (Software)",
            "name_de" => "Veröffentlichungsort (Software)",
            "description" => "A field for the venue of a software, e.g. for a repository.",
            "description_de" => "Ein Feld für den Veröffentlichungsort einer Software, z.B. für ein Repository."
        ],
        "status" => [
            "fields" => ["status" => 'completed'],
            "name" => "Status",
            "name_de" => "Status",
            "description" => "A field for the status of an activity, e.g. for a thesis supervision. Possible values are: in progress, completed, aborted.",
            "description_de" => "Ein Feld für den Status einer Aktivität, z.B. für eine Betreuung einer Abschlussarbeit. Mögliche Werte sind: in Bearbeitung, abgeschlossen, abgebrochen."
        ],
        "student-category" => [
            "fields" => ["category" => "doctoral thesis"],
            "name" => "Category of a thesis",
            "name_de" => "Kategorie einer Abschlussarbeit",
            "description" => "A field for the category of a student, can be one of the following: bachelor thesis, master thesis, doctoral thesis, internship, other.",
            "description_de" => "Ein Feld für die Kategorie eines Studierenden, kann eine der folgenden sein: Bachelorarbeit, Masterarbeit, Doktorarbeit, Praktikum, Sonstiges."
        ],
        "thesis" => [
            "fields" => ["category" => 'doctor'],
            "name" => "Category",
            "name_de" => "Kategorie",
            "description" => "A field for the category of a thesis, can be one of the following: bachelor, master, diploma, doctoral, habilitation.",
            "description_de" => "Ein Feld für die Kategorie einer Abschlussarbeit, kann eine der folgenden sein: Bachelor, Master, Diplom, Doktor, Habilitation."
        ],
        "supervisor" => [
            "fields" => ["authors" => [
                [
                    "last" => "Koblitz",
                    "first" => "Julia",
                    "aoi" => true,
                    "position" => "first",
                    "user" => "jkoblitz",
                    "approved" => true,
                    "sws" => 2
                ],
            ]],
            "name" => "Supervisor",
            "name_de" => "Betreuer:in",
            "description" => "A comprehensive table for supervisors, with individual input fields for supervisors, including first an last name, position, username, an affiliation checkbox and semester week hours (sws). Supports multiple supervisors, drag-and-drop, and auto-suggest on username field. Similar to the 'author-table' module, but with SWS.",
            "description_de" => "Eine umfassende Tabelle für Betreuer:innen, mit individuellen Eingabefeldern für Betreuer:innen, einschließlich Vor- und Nachname, Position, Benutzername, einer Affiliation-Checkbox und Semesterwochenstunden (SWS). Unterstützt mehrere Betreuer:innen, Drag-and-Drop und Auto-Suggest im Benutzernamenfeld. Ähnlich dem Modul 'author-table', aber mit SWS."
        ],
        "teaching-category" => [
            "fields" => ["category" => 'practical-lecture'],
            "name" => "Category",
            "name_de" => "Category",
            "description" => "A field for the category of a teaching activity, e.g. for a lecture. Possible values are: lecture, practical-lecture, seminar, project, other.",
            "description_de" => "Ein Feld für die Kategorie einer Lehrveranstaltung, z.B. für eine Vorlesung. Mögliche Werte sind: Vorlesung, Praktikum, Seminar, Projekt, Sonstiges."
        ],
        "teaching-course" => [
            "fields" => ["title" => "Einführung in die Forschungsinformation", "module" => null, "module_id" => null],
            "name" => "Course",
            "name_de" => "Modul",
            "description" => "A field for selecting a teaching course from the database. If the course does not exist yet, the user will be prompted to add a new one that will be then saved into the database.",
            "description_de" => "Ein Feld zum Auswählen eines Lehrmoduls aus der Datenbank. Wenn das Modul noch nicht existiert, wird der Benutzer aufgefordert, ein neues hinzuzufügen, das dann in die Datenbank gespeichert wird."
        ],
        "title" => [
            "fields" => ["title" => "OSIRIS - the Open, Smart, and Intuitive Research Information System"],
            "name" => "Title",
            "name_de" => "Titel",
            "description" => "A field for the title of an activity, e.g. for a journal article. Always mandatory.",
            "description_de" => "Ein Feld für den Titel einer Aktivität, z.B. für einen Zeitschriftenartikel. Immer erforderlich."
        ],
        "university" => [
            "fields" => ["publisher" => 'Technische Universität Braunschweig'],
            "name" => "University",
            "name_de" => "Universität",
            "description" => "A field for the university of a publication, e.g. for a thesis.",
            "description_de" => "Ein Feld für die Universität einer Publikation, z.B. für eine Abschlussarbeit."
        ],
        "version" => [
            "fields" => ["version" => OSIRIS_VERSION],
            "name" => "Version",
            "name_de" => "Version",
            "description" => "A field for the version of a software, e.g. for a release.",
            "description_de" => "Ein Feld für die Version einer Software, z.B. für ein Release."
        ],
        "venue" => [
            "fields" => ["venue" => 'ARD'],
            "name" => 'Publisher / Place of publication',
            "name_de" => 'Herausgeber / Ort der Veröffentlichung',
            "description" => "A field for the publisher or place of publication, e.g. for a report.",
            "description_de" => "Ein Feld für den Herausgeber oder den Ort der Veröffentlichung, z.B. für einen Bericht."
        ],
        "volume" => [
            "fields" => ["volume" => 51],
            "name" => "Volume",
            "name_de" => "Volume",
            "description" => "A field for the volume of a publication, e.g. for a journal.",
            "description_de" => "Ein Feld für den Band einer Publikation, z.B. für eine Zeitschrift."
        ],
        "political_consultation" => [
            "fields" => ["political_consultation" => 'Gutachten'],
            "name" => "Contribution to political and social consulting",
            "name_de" => "Beitrag zur Politik- und Gesellschaftsberatung",
            "description" => "A field for the type of contribution to political and social consulting, e.g. for a report. Possible values are: Gutachten, Stellungnahme, Kommentar, Empfehlung.",
            "description_de" => "Ein Feld für die Art des Beitrags zur Politik- und Gesellschaftsberatung, z.B. für einen Bericht. Mögliche Werte sind: Gutachten, Stellungnahme, Kommentar, Empfehlung."
        ],
    );

    private $DB;

    function __construct($form = array(), $copy = false, $conference = false)
    {
        global $USER;
        $this->form = $form;

        $this->DB = new DB;


        $this->user = $_SESSION['username'] ?? '';

        $this->copy = $copy ?? false;
        $this->preset = $form['authors'] ?? array();
        if (empty($this->preset) || count($this->preset) === 0)
            $this->preset = array(
                [
                    'last' => $USER['last'],
                    'first' => $USER['first'],
                    'aoi' => true,
                    'user' => strtolower($USER['username'])
                ]
            );

        if (!empty($form) && !empty($form['authors'])) {

            $form['authors'] = DB::doc2Arr($form['authors']);
            if (is_array($form['authors'])) {
                $pos = array_count_values(array_column($form['authors'], 'position'));
                $this->first = $pos['first'] ?? 1;
                $this->last = $pos['last'] ?? 1;
            }
            $this->authorcount = count($form['authors']);
        }
        foreach ($this->preset as $a) {
            $this->authors .= $this->authorForm($a, false);
        }

        $preset_editors = $form['editors'] ?? array();
        foreach ($preset_editors as $a) {
            $this->editors .= $this->authorForm($a, true);
        }

        $this->userlist = $this->DB->db->persons->find([], ['sort' => ['is_active' => -1, 'last' => 1]])->toArray();

        if (!empty($conference)) {
            $conf = $this->DB->db->conferences->findOne(['_id' => DB::to_ObjectID($conference)]);
            if (!empty($conf) && empty($this->form)) {
                $this->form['conference'] = $conf['title'] ?? null;
                // _id as string
                $this->form['conference_id'] = strval($conf['_id']);
                $this->form['location'] = $conf['location'] ?? null;
                $this->form['link'] = $conf['url'] ?? null;
                $this->form['start'] = $conf['start'] ?? null;
                $this->form['end'] = $conf['end'] ?? null;
            }
        }
    }

    public function set($vals)
    {
        $this->form = $vals;
        if (isset($vals['authors'])) {
            $this->authors = '';
            foreach ($vals['authors'] as $a) {
                $this->authors .= $this->authorForm($a, false);
            }
            $this->authorcount = count($vals['authors']);
            $this->preset = $vals['authors'];
        }

        if (isset($vals['editors'])) {
            $this->editors = '';
            foreach ($vals['editors'] as $a) {
                $this->editors .= $this->authorForm($a, true);
            }
        }
    }

    private function val($index, $default = '')
    {
        $val = $this->form[$index] ?? $default;
        if (is_string($val)) {
            return htmlspecialchars($val);
        }
        return $val;
    }

    function authorForm($a, $is_editor = false)
    {
        $name = $is_editor ? 'editors' : 'authors';
        $aoi = $a['aoi'] ?? false;
        return "<div class='author " . ($aoi ? 'author-aoi' : '') . "' ondblclick='toggleAffiliation(this);'>
            $a[last], $a[first]<input type='hidden' name='values[$name][]' value='$a[last];$a[first];$aoi'>
            <a onclick='removeAuthor(event, this);'>&times;</a>
            </div>";
    }


    function get_name($module)
    {
        if (!isset($this->all_modules[$module]['name'])) {

            $field = $this->DB->db->adminFields->findOne(['id' => $module]);
            if (!empty($field)) return lang($field['name'], $field['name_de'] ?? $field['name']);
            return ucfirst($module);
        }
        return lang($this->all_modules[$module]['name'], $this->all_modules[$module]['name_de']);
    }

    function get_fields($modules)
    {
        $return = array();
        foreach ($modules as $module) {
            $fields = $this->all_modules[$module]['fields'] ?? array();
            foreach ($fields as $field => $default) {
                $val = $this->form[$field] ?? '';
                if (!is_array($val))
                    $return[ucfirst($field)] = $val;
            }
        }
        return $return;
    }

    function print_all_modules()
    {
        foreach ($this->all_modules as $module => $def) {
            $this->print_module($module);
        }
    }

    function print_modules($modules)
    {
        foreach ($modules as $module) {
            $this->print_module($module);
        }
    }


    function custom_field($module, $req = false)
    {
        $field = $this->DB->db->adminFields->findOne(['id' => $module]);
        if (!isset($field)) {
            echo '<b>Module <code>' . $module . '</code> is not defined. </b>';
            return;
        }
        $required = ($req ? "required" : "");
        $label = lang($field['name'], $field['name_de'] ?? $field['name']);

        if ($field['format'] == 'bool') {
            echo '<div class="data-module col-sm-6" data-module="' . $module . '">';
            echo '<label for="' . $module . '" class="' . $required . ' floating-title">' . $label . '</label>';

            $val = boolval($this->val($module, $field['default'] ?? ''));
            echo '<br>
                <div class="custom-radio d-inline-block">
                    <input type="radio" id="' . $module . '-true" value="true" name="values[' . $module . ']" ' . ($val == true ? 'checked' : '') . '>
                    <label for="' . $module . '-true">' . lang('True', 'Wahr') . '</label>
                </div>
                <div class="custom-radio d-inline-block ml-20">
                    <input type="radio" id="' . $module . '-false" value="false" name="values[' . $module . ']" ' . ($val == false ? 'checked' : '') . '>
                    <label for="' . $module . '-false">' . lang('False', 'Falsch') . '</label>
                </div>';
            echo '</div>';
            return;
        }

        echo '<div class="data-module floating-form col-sm-12" data-module="' . $module . '">';

        switch ($field['format']) {
            case 'string':
                echo '<input type="text" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $required . ' value="' . $this->val($module, $field['default'] ?? '') . '" placeholder="custom-field">';
                break;
            case 'text':
                echo '<textarea name="values[' . $module . ']" id="' . $module . '" cols="30" rows="5" class="form-control" ' . $required . '>' . $this->val($module, $field['default'] ?? '') . '</textarea placeholder="custom-field">';
                break;
            case 'int':
                echo '<input type="number" step="1" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $required . ' value="' . $this->val($module, $field['default'] ?? '') . '" placeholder="custom-field">';
                break;
            case 'float':
                echo '<input type="number" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $required . ' value="' . $this->val($module, $field['default'] ?? '') . '" placeholder="custom-field">';
                break;
            case 'list':
                $multiple = $field['multiple'] ?? false;
                $name = 'values[' . $module . ']' . ($multiple ? '[]' : '');
                echo '<select class="form-control" name="' . $name . '" id="' . $module . '" ' . $required . ' ' . ($multiple ? 'multiple' : '') . '>';
                $val = $this->val($module, $field['default'] ?? '');
                if (!$req) {
                    '<option value="" ' . (empty($val) ? 'selected' : '') . '>-</option>';
                }
                foreach ($field['values'] as $opt) {
                    // if is type MongoDB\Model\BSONArray, convert to array
                    if ($opt instanceof MongoDB\Model\BSONArray) {
                        $opt = DB::doc2Arr($opt);
                    }
                    if (is_array($opt)) {
                        $opt = lang(...$opt);
                    }
                    echo '<option ' . ($val == $opt ? 'selected' : '') . ' value="' . $opt . '">' . $opt . '</option>';
                }
                echo '</select>';
                break;
            case 'date':
                echo '<input type="date" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $required . ' value="' . valueFromDateArray($this->val($module, $field['default'] ?? '')) . '" placeholder="custom-field">';
                break;
            case 'url':
                echo '<input type="url" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $required . ' value="' . $this->val($module, $field['default'] ?? '') . '" placeholder="custom-field">';
                break;
            case 'bool':
                echo '<input type="hidden" name="values[' . $module . ']" value="false">';
                echo '<input type="checkbox" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $required . ' value="true" ' . ($this->val($module, $field['default'] ?? '') == 'true' ? 'checked' : '') . '>';
                break;
            default:
                echo '<input type="text" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $required . ' value="' . $this->val($module, $field['default'] ?? '') . '">';
                break;
        }

        echo '<label for="' . $module . '" class="' . $required . '">' . $label . '</label>';
        echo '</div>';
    }

    function print_module($module, $req = false)
    {
        if (str_ends_with($module, '*')) {
            $module = str_replace('*', '', $module);
            $req = true;
        }
        if (!array_key_exists($module, $this->all_modules)) {
            return $this->custom_field($module, $req);
        }

        $required = ($req ? "required" : "");


        $m = $this->all_modules[$module] ?? [];
        $label = lang($m['name'], $m['name_de'] ?? $m['name']);
        switch ($module) {
            case 'gender':
                $val = $this->val('gender');
?>
                <div class="data-module floating-form col-sm-6" data-module="teaching-gender">
                    <select name="values[gender]" id="teaching-cat" class="form-control" <?= $required ?>>
                        <option value="" <?= empty($val) ? 'selected' : '' ?>><?= lang('unknown', 'unbekannt') ?></option>
                        <option value="f" <?= $val == 'f' ? 'selected' : '' ?>><?= lang('female', 'weiblich') ?></option>
                        <option value="m" <?= $val == 'm' ? 'selected' : '' ?>><?= lang('male', 'männlich') ?></option>
                        <option value="d" <?= $val == 'd' ? 'selected' : '' ?>><?= lang('non-binary', 'divers') ?></option>
                        <option value="-" <?= $val == '-' ? 'selected' : '' ?>><?= lang('not specified', 'keine Angabe') ?></option>
                    </select>
                    <label for="teaching-cat" class="<?= $required ?>"><?= $label ?></label>
                </div>
            <?php
                break;
            case 'nationality':
            case 'country':
                $val = $this->val('country');
            ?>
                <div class="data-module floating-form col-sm-6" data-module="country">
                    <select name="values[country]" id="country" class="form-control" <?= $required ?>>
                        <option value="" <?= empty($val) ? 'selected' : '' ?>><?= lang('unknown', 'unbekannt') ?></option>
                        <?php foreach ($this->DB->getCountries(lang('name', 'name_de')) as $code => $country) { ?>
                            <option value="<?= $code ?>" <?= $val == $code ? 'selected' : '' ?>><?= $country ?></option>
                        <?php } ?>
                    </select>
                    <label for="country" class="<?= $required ?> element-cat">
                        <?= $label ?>
                    </label>
                </div>
            <?php
                break;
            case 'abstract':
            ?>
                <div class="data-module floating-form col-sm-12" data-module="abstract">
                    <textarea name="values[abstract]" id="abstract" cols="30" rows="5" class="form-control" placeholder="abstract"><?= $this->val('abstract') ?></textarea>
                    <label for="abstract" class="<?= $required ?> "><?= lang('Abstract', 'Abstract') ?></label>
                </div>
            <?php
                break;
            case "title":
                $id = rand(1000, 9999);
            ?>
                <div class="data-module col-12" data-module="title">
                    <div class="lang-<?= lang('en', 'de') ?>">
                        <label for="title" class="<?= $required ?> floating-title">
                            <?= lang('Title / Topic / Description', 'Titel / Thema / Beschreibung') ?>
                        </label>

                        <div class="form-group title-editor" id="title-editor-<?= $id ?>"><?= $this->form['title'] ?? '' ?></div>
                        <input type="text" class="form-control hidden" name="values[title]" id="title" <?= $required ?> value="<?= $this->val('title') ?>">
                    </div>
                </div>
                <script>
                    initQuill(document.getElementById('title-editor-<?= $id ?>'));
                </script>
            <?php
                break;

            case "pubtype":
            ?>
                <div class="data-module col-12" data-module="pubtype">
                    <!-- not visible, is selected via subtype buttons -->
                    <select class="form-control" name="values[pubtype]" id="pubtype" readonly <?= $required ?>>
                        <option value="article">Journal article (refereed)</option>
                        <option value="book"><?= lang('Book', 'Buch') ?></option>
                        <option value="chapter"><?= lang('Book chapter', 'Buchkapitel') ?></option>
                        <option value="preprint">Preprint (non refereed)</option>
                        <option value="conference"><?= lang('Conference preceedings', 'Konfrenzbeitrag') ?></option>
                        <option value="magazine"><?= lang('Magazine article (non refereed)', 'Magazin-Artikel (non-refereed)') ?></option>
                        <option value="dissertation"><?= lang('Thesis') ?></option>
                        <option value="others"><?= lang('Others', 'Weiteres') ?></option>
                    </select>
                </div>
            <?php
                break;

            case "teaching-course":
            ?>
                <div class="data-module col-12" data-module="teaching-course">
                    <label for="teaching" class="floating-title <?= $required ?>">
                        <?= lang('Course for the following module', 'Veranstaltung zu folgendem Modul') ?>
                    </label>
                    <a href="#teaching-select" id="teaching-field" class="module">
                        <span class="float-right text-secondary"><i class="ph ph-edit"></i></span>

                        <div id="selected-teaching">
                            <?php if (!empty($this->form) && isset($this->form['module_id'])) :
                                $module = $this->DB->getConnected('teaching', $this->form['module_id']);
                            ?>
                                <h5 class="m-0"><span class="highlight-text"><?= $module['module'] ?></span> <?= $module['title'] ?></h5>
                                <span class="text-muted"><?= $module['affiliation'] ?></span>
                            <?php else : ?>
                                <span class="title"><?= lang('No module selected', 'Kein Modul ausgewählt') ?></span>

                            <?php endif; ?>
                        </div>

                        <input type="hidden" class="form-control hidden" name="values[title]" value="<?= $this->val('title') ?>" id="module-title" <?= $required ?> readonly>
                        <input type="hidden" class="form-control hidden" name="values[module]" value="<?= $this->val('module') ?>" id="module" <?= $required ?> readonly>
                        <input type="hidden" class="form-control hidden" name="values[module_id]" value="<?= $this->val('module_id') ?>" id="module_id" <?= $required ?> readonly>
                    </a>
                </div>
            <?php
                break;
            case "author-table":
            ?>
                <div class="data-module col-12" data-module="author-table">
                    <label for="authors" class="<?= $required ?> floating-title"><?= lang('Author(s)', 'Autor(en)') ?></label>
                    <div class="module p-0">
                        <table class="table simple small">
                            <thead>
                                <tr>
                                    <th><label for="user">Username</label></th>
                                    <th><label for="last" class="required"><?= lang('Last name', 'Nachname') ?></label></th>
                                    <th><label for="first" class="required"><?= lang('First name', 'Vorname') ?></label></th>
                                    <th><label for="position"><?= lang('Position', 'Position') ?></label></th>
                                    <th><label for="aoi"><?= lang('Affiliated', 'Affiliert') ?></label></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="authors">
                                <?php foreach ($this->preset ?? [] as $i => $author) {
                                    if (!isset($author['position'])) $author['position'] = 'middle';
                                ?>
                                    <tr>
                                        <td>
                                            <input data-type="user" name="values[authors][<?= $i ?>][user]" type="text" class="form-control" list="user-list" value="<?= $author['user'] ?>" onchange="selectUsername(this)">
                                        </td>
                                        <td>
                                            <input data-type="last" name="values[authors][<?= $i ?>][last]" type="text" class="form-control" value="<?= $author['last'] ?>" required>
                                        </td>
                                        <td>
                                            <input data-type="first" name="values[authors][<?= $i ?>][first]" type="text" class="form-control" value="<?= $author['first'] ?>">
                                        </td>
                                        <td>
                                            <select name="values[authors][<?= $i ?>][position]" class="form-control">
                                                <option value="first" <?= ($author['position'] == 'first' ? 'selected' : '') ?>>first</option>
                                                <option value="middle" <?= ($author['position'] == 'middle' ? 'selected' : '') ?>>middle</option>
                                                <option value="corresponding" <?= ($author['position'] == 'corresponding' ? 'selected' : '') ?>>corresponding</option>
                                                <option value="last" <?= ($author['position'] == 'last' ? 'selected' : '') ?>>last</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="custom-checkbox">
                                                <input data-type="aoi" type="checkbox" id="checkbox-<?= $i ?>" name="values[authors][<?= $i ?>][aoi]" value="1" <?= (($author['aoi'] ?? 0) == '1' ? 'checked' : '') ?>>
                                                <label for="checkbox-<?= $i ?>" class="blank"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn text-danger" type="button" onclick="removeAuthorRow(this)"><i class="ph ph-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6">
                                        <button class="btn text-secondary" type="button" onclick="addAuthorRow()"><i class="ph ph-plus"></i></button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <script>
                        function removeAuthorRow(el) {
                            // check if row is the only one left
                            if ($(el).closest('tbody').find('tr').length > 1) {
                                $(el).closest('tr').remove()
                            } else {
                                toastError(lang('At least one author is needed.', 'Mindestens ein Autor muss angegeben werden.'))
                            }
                        }

                        function selectUsername(el) {
                            let username = el.value
                            let user = $('#user-list option[value=' + username + ']')
                            if (!user || user === undefined || user.length === 0) return;

                            console.log(user);
                            let name = user.html()
                            name = name.replace(/\(.+\)/, '');
                            name = name.split(', ')
                            if (name.length !== 2) return;

                            let tr = $(el).closest('tr')
                            console.log(tr);
                            tr.find('[data-type=last]').val(name[0])
                            tr.find('[data-type=first]').val(name[1])
                            tr.find('[data-type=aoi]').prop('checked', true)
                        }

                        var counter = <?= $i ?>;

                        function addAuthorRow(data = {}) {
                            if (data.last !== undefined && data.first !== undefined) {
                                // data.first = data.first.replace(/\s/g, ' ') 
                                let firstname = data.first.replace(/\s.*$/, '')
                                let name = data.last + ', ' + firstname
                                let user = $('#user-list option:contains(' + name + ')')
                                if (user && user !== undefined && user.length !== 0) {
                                    data.user = user.val()
                                }
                                console.log(data);
                            }
                            counter++;
                            const POSITIONS = ['first', 'middle', 'corresponding', 'last']
                            var pos = data.position ?? 'middle';
                            if (!POSITIONS.includes(pos)) pos = 'middle';

                            var tr = $('<tr>')
                            tr.append('<td><input data-type="user" name="values[authors][' + counter + '][user]" type="text" class="form-control" list="user-list" value="' + (data.user ?? '') + '" onchange="selectUsername(this)"></td>')
                            tr.append('<td><input data-type="last" name="values[authors][' + counter + '][last]" type="text" class="form-control" required value="' + (data.last ?? '') + '"></td>')
                            tr.append('<td><input data-type="first" name="values[authors][' + counter + '][first]" type="text" class="form-control" value="' + (data.first ?? '') + '"></td>')

                            var select = $('<select data-type="position" name="values[authors][' + counter + '][position]" class="form-control">');
                            POSITIONS.forEach(p => {
                                select.append('<option value="' + p + '" ' + (pos == p ? 'selected' : '') + '>' + p + '</option>')
                            });
                            tr.append($('<td>').append(select))
                            tr.append('<td><div class="custom-checkbox"><input data-type="aoi" type="checkbox" id="checkbox-' + counter + '" name="values[authors][' + counter + '][aoi]" value="1" ' + (data.aoi == true ? 'checked' : '') + '><label for="checkbox-' + counter + '" class="blank"></label></div></td>')
                            var btn = $('<button class="btn text-danger" type="button">').html('<i class="ph ph-trash"></i>').on('click', function() {
                                $(this).closest('tr').remove();
                            });
                            tr.append($('<td>').append(btn))
                            $('#authors').append(tr)
                        }
                    </script>

                    <datalist id="user-list">
                        <?php
                        foreach ($this->userlist as $s) { ?>
                            <option value="<?= $s['username'] ?>"><?= "$s[last], $s[first] ($s[username])" ?></option>
                        <?php } ?>
                    </datalist>

                </div>
            <?php
                break;

            case "supervisor":
            ?>
                <div class="data-module col-12" data-module="supervisor">
                    <label for="supervisor" class="<?= $required ?> floating-title"><?= lang('Supervisor', 'Betreuer_in') ?></label>
                    <div class="module p-0">
                        <table class="table simple small">
                            <thead>
                                <tr>
                                    <th><?= lang('Last name', 'Nachname') ?></th>
                                    <th><?= lang('First name', 'Vorname') ?></th>
                                    <th><?= lang('Affiliated', 'Affiliert') ?></th>
                                    <th>Username</th>
                                    <th><?= lang('SWS', 'Anteil in SWS') ?> <span class="text-danger">*</span></th>
                                    <th>
                                        <a href="#sws-calc" class="btn link"><i class="ph ph-calculator"></i></a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="supervisors">
                                <?php foreach ($this->preset ?? [] as $i => $author) { ?>
                                    <tr>
                                        <td>
                                            <input name="values[authors][<?= $i ?>][last]" type="text" class="form-control" value="<?= $author['last'] ?>" required>
                                        </td>
                                        <td>
                                            <input name="values[authors][<?= $i ?>][first]" type="text" class="form-control" value="<?= $author['first'] ?>">
                                        </td>
                                        <td>
                                            <div class="custom-checkbox">
                                                <input type="checkbox" id="checkbox-<?= $i ?>" name="values[authors][<?= $i ?>][aoi]" value="1" <?= (($author['aoi'] ?? 0) == '1' ? 'checked' : '') ?>>
                                                <label for="checkbox-<?= $i ?>" class="blank"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <input name="values[authors][<?= $i ?>][user]" type="text" class="form-control" list="user-list" value="<?= $author['user'] ?>">
                                        </td>

                                        <td>
                                            <input type="number" step="0.1" class="form-control" name="values[authors][<?= $i ?>][sws]" id="teaching-sws" value="<?= $author['sws'] ?? '' ?>" required>
                                        </td>
                                        <td>
                                            <button class="btn text-danger" type="button" onclick="removeSupervisorRow(this)"><i class="ph ph-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6">
                                        <button class="btn text-secondary" type="button" onclick="addSupervisorRow()"><i class="ph ph-plus"></i></button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <script>
                        function removeSupervisorRow(el) {
                            // check if row is the only one left
                            if ($(el).closest('tbody').find('tr').length > 1) {
                                $(el).closest('tr').remove()
                            } else {
                                toastError(lang('At least one supervisor is needed.', 'Mindestens ein Betreuer muss angegeben werden.'))
                            }
                        }

                        var counter = <?= $i ?>;

                        function addSupervisorRow() {
                            counter++;
                            var tr = $('<tr>')
                            tr.append('<td><input name="values[authors][' + counter + '][last]" type="text" class="form-control" required></td>')
                            tr.append('<td><input name="values[authors][' + counter + '][first]" type="text" class="form-control"></td>')
                            tr.append('<td><div class="custom-checkbox"><input type="checkbox" id="checkbox-' + counter + '" name="values[authors][' + counter + '][aoi]" value="1"><label for="checkbox-' + counter + '" class="blank"></label></div></td>')
                            tr.append('<td> <input name="values[authors][' + counter + '][user]" type="text" class="form-control" list="user-list"></td>')
                            tr.append('<td><input type="number" step="0.1" class="form-control" name="values[authors][' + counter + '][sws]" id="teaching-sws" value="" required></td>')
                            var btn = $('<button class="btn" type="button">').html('<i class="ph ph-trash"></i>').on('click', function() {
                                $(this).closest('tr').remove();
                            });
                            tr.append($('<td>').append(btn))
                            $('#supervisors').append(tr)
                        }
                    </script>

                    <datalist id="user-list">
                        <?php
                        foreach ($this->userlist as $s) { ?>
                            <option value="<?= $s['username'] ?>"><?= "$s[last], $s[first] ($s[username])" ?></option>
                        <?php } ?>
                    </datalist>

                </div>
            <?php
                break;

            case "teaching-category":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="teaching-category">
                    <select name="values[category]" id="teaching-cat" class="form-control" <?= $required ?>>
                        <option value="lecture" <?= $this->val('category') == 'lecture' ? 'selected' : '' ?>><?= lang('Lecture', 'Vorlesung') ?></option>
                        <option value="practical" <?= $this->val('category') == 'practical' ? 'selected' : '' ?>><?= lang('Practical course', 'Praktikum') ?></option>
                        <option value="practical-lecture" <?= $this->val('category') == 'practical-lecture' ? 'selected' : '' ?>><?= lang('Lecture and practical course', 'Vorlesung und Praktikum') ?></option>
                        <option value="practical-seminar" <?= $this->val('category') == 'practical-seminar' ? 'selected' : '' ?>><?= lang('Practical course and seminar', 'Praktikum und Seminar') ?></option>
                        <option value="lecture-seminar" <?= $this->val('category') == 'lecture-seminar' ? 'selected' : '' ?>><?= lang('Lecture and seminar', 'Vorlesung und Seminar') ?></option>
                        <option value="lecture-practical-seminar" <?= $this->val('category') == 'lecture-practical-seminar' ? 'selected' : '' ?>><?= lang('Lecture, seminar, practical course', 'Vorlesung, Seminar und Praktikum') ?></option>
                        <option value="seminar" <?= $this->val('category') == 'seminar' ? 'selected' : '' ?>><?= lang('Seminar') ?></option>
                        <option value="other" <?= $this->val('category') == 'other' ? 'selected' : '' ?>><?= lang('Other', 'Sonstiges') ?></option>
                    </select>
                    <label for="teaching-cat" class="<?= $required ?> element-cat"><?= lang('Category', 'Kategorie') ?></label>
                </div>
            <?php
                break;

            case "semester-select":
            ?>
                <div class="data-module col-sm-6" data-module="semester-select">
                    <label for="teaching-cat" class="floating-title"><?= lang('Fast select time', 'Schnellwahl Zeit') ?></label>

                    <div class="btn-group d-flex">
                        <button class="btn" type="button" onclick="selectSemester('SS', '<?= CURRENTYEAR - 1 ?>')">SS <?= CURRENTYEAR - 1 ?></button>
                        <button class="btn" type="button" onclick="selectSemester('WS', '<?= CURRENTYEAR - 1 ?>')">WS <?= CURRENTYEAR - 1 ?></button>
                        <button class="btn" type="button" onclick="selectSemester('SS', '<?= CURRENTYEAR ?>')">SS <?= CURRENTYEAR ?></button>
                        <button class="btn" type="button" onclick="selectSemester('WS', '<?= CURRENTYEAR ?>')">WS <?= CURRENTYEAR ?></button>
                    </div>
                    <script>
                        function selectSemester(sem, year) {
                            year = parseInt(year)
                            var start = year + '-'
                            start += (sem == 'WS' ? '10-01' : '04-01')
                            $('#date_start').val(start)

                            var end = (sem == 'WS' ? year + 1 : year) + '-'
                            end += (sem == 'WS' ? '03-31' : '09-30')
                            $('#date_end').val(end)
                        }
                    </script>
                </div>
            <?php
                break;

            case "event-select":
                $events = $this->DB->db->conferences->find(
                    ['end' => ['$lte' => date('Y-m-d', strtotime('+5 days'))]],
                    ['sort' => ['start' => -1], 'limit' => 10]
                );
            ?>

                <div class="data-module col-sm-6" data-module="event-select">
                    <label class="floating-title"><?= lang('Select an event', 'Wähle ein Event') ?></label>

                    <div id="event-select-container w-full">
                        <div class="btn-group d-flex" id="event-select-btns">
                            <a class="btn" href="#add-event">
                                <i class="ph ph-plus"></i>
                            </a>
                            <?php foreach ($events as $event) { ?>
                                <button class="btn primary" type="button" onclick="selectEvent('<?= $event['_id'] ?>', '<?= addslashes($event['title']) ?>', '<?= $event['start'] ?>', '<?= $event['end'] ?>', '<?= $event['location'] ?>')">
                                    <?= $event['title'] ?>
                                    <small><?= fromToDate($event['start'], $event['end']) ?></small>
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                    <style>
                        #event-select-container {
                            border-radius: var(--border-radius);
                            position: relative;
                            margin: 0 -2rem;
                        }

                        /* white scrim */
                        #event-select-container::after {
                            content: '';
                            position: absolute;
                            right: 0;
                            bottom: 0;
                            width: 2rem;
                            height: 100%;
                            background: linear-gradient(270deg, #fff, #ffffff 30%, transparent);
                            z-index: 1;
                        }

                        #event-select-container::before {
                            content: '';
                            position: absolute;
                            left: 0;
                            bottom: 0;
                            width: 2rem;
                            height: 100%;
                            background: linear-gradient(90deg, #fff, #ffffff 30%, transparent);
                            z-index: 1;
                        }

                        #event-select-btns {
                            overflow-x: auto;
                            padding: 0 2rem;
                        }

                        #event-select-btns .btn {
                            width: 100%;
                            text-align: left;
                            display: flex;
                            flex-direction: column;
                            height: auto;
                            line-height: 1.6;
                            padding: .5rem 1.5rem;
                            justify-content: center;
                        }

                        .btn-group .btn:not(.disabled):not(:disabled):focus,
                        .btn-group .btn:not(.disabled):not(:disabled).active {
                            box-shadow: none;
                        }

                        #event-select-btns .btn small {
                            display: block;
                            font-size: 0.8em;
                            color: var(--muted-color);
                        }
                    </style>
                </div>
            <?php
                break;

            case "authors":
            ?>
                <div class="data-module col-12" data-module="authors">
                    <a class="float-right" href="#author-help"><i class="ph ph-question" style="line-height:0;"></i> <?= lang('Help', 'Hilfe') ?></a>
                    <label for="author" class="floating-title <?= $required ?>">
                        <!-- <span data-visible="students,guests"><?= lang('Responsible scientist', 'Verantwortliche Person') ?></span> -->
                        <?= lang('Author(s) / Responsible person', 'Autor(en) / Verantwortliche Person') ?>
                        <small class="text-muted"><?= lang('(in correct order, format: Last name, First name)', '(in korrekter Reihenfolge, Format: Nachname, Vorname)') ?></small>
                    </label>

                    <div class="author-widget" id="author-widget">
                        <div class="author-list p-10" id="author-list">
                            <?= $this->authors ?>
                        </div>
                        <div class="footer">

                            <div class="input-group small d-inline-flex w-auto">
                                <input type="text" placeholder="<?= lang('Add author ...', 'Füge Autor hinzu ...') ?>" onkeypress="addAuthor(event);" id="add-author" list="scientist-list">
                                <div class="input-group-append">
                                    <button class="btn secondary h-full" type="button" onclick="addAuthor(event);">
                                        <i class="ph ph-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="ml-auto" id="author-numbers">
                                <label for="first-authors"><?= lang('Number of first authors:', 'Anzahl der Erstautoren:') ?></label>
                                <input type="number" name="values[first_authors]" id="first-authors" value="<?= $this->first ?>" class="form-control sm w-50 d-inline-block mr-10" autocomplete="off">
                                <label for="last-authors"><?= lang('last authors:', 'Letztautoren:') ?></label>
                                <input type="number" name="values[last_authors]" id="last-authors" value="<?= $this->last ?>" class="form-control sm w-50 d-inline-block" autocomplete="off">
                            </div>
                        </div>

                    </div>
                    <small class="text-muted">
                        <?= lang('Note: A detailed author editor is available after adding the activity.', 'Anmerkung: Ein detaillierter Autoreneditor ist verfügbar, nachdem der Datensatz hinzugefügt wurde.') ?>
                    </small>
                    <div class="alert danger mb-20 affiliation-warning" style="display: none;">
                        <h5 class="title">
                            <i class="ph ph-warning-circle"></i>
                            <?= lang("Attention: No affiliated authors added.", 'Achtung: Keine affilierten Autoren angegeben.') ?>
                        </h5>
                        <?= lang(
                            'Please double click on every affiliated author in the list above, to mark them as affiliated. Only affiliated authors will receive points and are shown in reports.',
                            'Bitte doppelklicken Sie auf jeden affilierten Autor in der Liste oben, um ihn als zugehörig zu markieren. Nur zugehörige Autoren erhalten Punkte und werden in Berichten berücksichtigt.'
                        ) ?>
                    </div>
                </div>
            <?php
                break;

            case "person":
            ?>
                <h6 class="col-12 m-0 floating-title <?= $required ?>">
                    <?= lang('Details about the person', 'Angaben zu der Person') ?>
                </h6>
                <div class="data-module col-12 row" data-module="person">
                    <div class="col-sm-5 floating-form">
                        <input type="text" class="form-control" name="values[name]" id="guest-name" <?= $required ?> value="<?= $this->val('name') ?>" placeholder="name">
                        <label for="guest-name" class="<?= $required ?> element-other">
                            <?= lang('Name of the person', 'Name der Person') ?>
                            <?= lang('(last name, given name)', '(Nachname, Vorname)') ?>
                        </label>
                    </div>
                    <div class="col-sm-5 floating-form">
                        <input type="text" class="form-control" name="values[affiliation]" id="guest-affiliation" <?= $required ?> value="<?= $this->val('affiliation') ?>" placeholder="affiliation">
                        <label for="guest-affiliation" class="<?= $required ?> element-other"><?= lang('Affiliation (Name, City, Country)', 'Einrichtung (Name, Ort, Land)') ?></label>
                    </div>
                    <div class="col-sm-2 floating-form">
                        <input type="text" class="form-control" name="values[academic_title]" id="guest-academic_title" value="<?= $this->val('academic_title') ?>" placeholder="academic_title">
                        <label for="guest-academic_title"><?= lang('Academ. title', 'Akadem. Titel') ?></label>
                    </div>
                </div>
            <?php
                break;

            case "student-category":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="student-category">
                    <select name="values[category]" id="category-students" class="form-control" <?= $required ?>>
                        <option value="doctoral thesis" <?= $this->val('category') == 'doctoral thesis' ? 'selected' : '' ?>><?= lang('Doctoral Thesis', 'Doktorand:in') ?></option>
                        <option value="master thesis" <?= $this->val('category') == 'master thesis' ? 'selected' : '' ?>><?= lang('Master Thesis', 'Masterarbeit') ?></option>
                        <option value="bachelor thesis" <?= $this->val('category') == 'bachelor thesis' ? 'selected' : '' ?>><?= lang('Bachelor Thesis', 'Bachelorarbeit') ?></option>
                        <option value="internship" <?= $this->val('category') == 'internship' ? 'selected' : '' ?>><?= lang('Internship', 'Praktikum') ?></option>
                        <option value="other" <?= $this->val('category') == 'other' ? 'selected' : '' ?>><?= lang('Other', 'Sonstiges') ?></option>
                    </select>
                    <label for="category-students" class="<?= $required ?>"><?= lang('Category', 'Kategorie') ?></label>
                </div>
            <?php
                break;

            case "thesis":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="thesis">
                    <select name="values[thesis]" id="thesis" class="form-control" <?= $required ?>>
                        <option value=""><?= lang('Thesis', 'Abschlussarbeit') ?></option>
                        <option value="doctor" <?= $this->val('thesis') == 'doctor' ? 'selected' : '' ?>><?= lang('Doctoral Thesis', 'Doktorarbeit') ?></option>
                        <option value="diploma" <?= $this->val('thesis') == 'diploma' ? 'selected' : '' ?>><?= lang('Diploma Thesis', 'Diplomarbeit') ?></option>
                        <option value="master" <?= $this->val('thesis') == 'master' ? 'selected' : '' ?>><?= lang('Master Thesis', 'Masterarbeit') ?></option>
                        <option value="bachelor" <?= $this->val('thesis') == 'bachelor' ? 'selected' : '' ?>><?= lang('Bachelor Thesis', 'Bachelorarbeit') ?></option>
                        <option value="habilitation" <?= $this->val('thesis') == 'habilitation' ? 'selected' : '' ?>><?= lang('Habilitation', 'Habilitation') ?></option>
                    </select>
                    <label for="thesis" class="<?= $required ?> element-cat"><?= lang('Thesis type', 'Art der Abschlussarbeit') ?></label>
                </div>
            <?php
                break;

            case "status":
            ?>
                <div class="data-module col-sm-6" data-module="status" style="align-self: center;">
                    <label for="status" class="<?= $required ?> floating-title">Status</label>
                    <div id="end-question">
                        <div class="custom-radio d-inline-block">
                            <input type="radio" name="values[status]" id="status-preparation" value="preparation" checked="checked" value="1">
                            <label for="status-preparation"><?= lang('In preparation', 'In Vorbereitung') ?></label>
                        </div>

                        <div class="custom-radio d-inline-block">
                            <input type="radio" name="values[status]" id="status-in-progress" value="in progress" checked="checked" value="1">
                            <label for="status-in-progress"><?= lang('In progress', 'In Progress') ?></label>
                        </div>

                        <div class="custom-radio d-inline-block">
                            <input type="radio" name="values[status]" id="status-completed" value="completed" value="1">
                            <label for="status-completed"><?= lang('Completed', 'Abgeschlossen') ?></label>
                        </div>

                        <div class="custom-radio d-inline-block">
                            <input type="radio" name="values[status]" id="status-aborted" value="aborted" value="1">
                            <label for="status-aborted"><?= lang('Aborted', 'Abgebrochen') ?></label>
                        </div>
                    </div>
                </div>
            <?php
                break;

            case "guest":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="guest">
                    <select name="values[category]" id="category-guest" class="form-control" <?= $required ?>>
                        <option value="guest scientist" <?= $this->val('category') == 'guest scientist' ? 'selected' : '' ?>><?= lang('Guest Scientist', 'Gastwissenschaftler:in') ?></option>
                        <option value="lecture internship" <?= $this->val('category') == 'lecture internship' ? 'selected' : '' ?>><?= lang('Lecture Internship', 'Pflichtpraktikum im Rahmen des Studium') ?></option>
                        <option value="student internship" <?= $this->val('category') == 'student internship' ? 'selected' : '' ?>><?= lang('Student Internship', 'Schülerpraktikum') ?></option>
                        <option value="other" <?= $this->val('category') == 'other' ? 'selected' : '' ?>><?= lang('Other', 'Sonstiges') ?></option>
                    </select>
                    <label for="category-guest" class="<?= $required ?> element-cat"><?= lang('Category', 'Kategorie') ?></label>
                </div>
            <?php
                break;

            case "details":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="details">
                    <input type="text" class="form-control" name="values[details]" id="details" <?= $required ?> value="<?= $this->val('details') ?>" placeholder="details">
                    <label for="details" class="<?= $required ?>">
                        <?= lang('Details') ?>
                    </label>
                </div>
            <?php
                break;

            case "date":
            ?>

                <div class="data-module floating-form col-12 row" data-module="date">
                    <div class="col-sm floating-form">
                        <input type="number" min="1901" max="2155" step="1" class="form-control" name="values[year]" id="year" <?= $required ?> value="<?= $this->val('year') ?>" placeholder="2024">
                        <label for="year" class="<?= $required ?> element-time">Year</label>
                    </div>
                    <div class="col-sm floating-form">
                        <input type="number" min="1" max="12" step="1" class="form-control" name="values[month]" id="month" <?= $required ?> value="<?= $this->val('month') ?>" placeholder="12">
                        <label for="month" class="<?= $required ?> element-time">Month</label>
                    </div>
                    <div class="col-sm floating-form">
                        <input type="number" min="1" max="31" step="1" class="form-control" name="values[day]" id="day" value="<?= $this->val('day') ?>" placeholder="24">
                        <label for="day" class="element-time">Day</label>
                    </div>
                    <div class="col flex-grow-0">
                        <button class="btn primary" type="button" onclick="dateToday()" style="height: calc(4rem + 1px); font-size:small; line-height:0">
                            <i class="ph ph-calendar-dot"></i>
                            <?= lang('Today', 'Heute') ?>
                        </button>
                    </div>
                </div>
                <script>
                    function dateToday() {
                        var today = new Date();
                        $('#year').val(today.getFullYear());
                        $('#month').val(today.getMonth() + 1);
                        $('#day').val(today.getDate());
                    }
                </script>
            <?php
                break;

            case "lecture-type":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="lecture-type">
                    <select name="values[lecture_type]" id="lecture_type" class="form-control" autocomplete="off">
                        <option value="short" <?= $this->val('lecture_type') == 'short' ? 'selected' : '' ?>><?= lang('short', 'kurz') ?> (5-15 min.)</option>
                        <option value="medium" <?= $this->val('lecture_type') == 'medium' ? 'selected' : '' ?>><?= lang('medium', 'mittel') ?> (15-30 min.)</option>
                        <option value="long" <?= $this->val('lecture_type') == 'long' ? 'selected' : '' ?>><?= lang('long', 'lang') ?> (> 30 min.)</option>
                        <option value="repetition" <?= $this->val('lecture_type') == 'repetition' || $this->copy === true ? 'selected' : '' ?>><?= lang('repetition', 'Wiederholung') ?></option>
                    </select>
                    <label class="<?= $required ?> " for="lecture_type"><?= lang('Type of lecture', 'Art des Vortrages') ?></label>
                </div>
            <?php
                break;

            case "lecture-invited":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="lecture-invited">
                    <select name="values[invited_lecture]" id="invited_lecture" class="form-control" autocomplete="off" <?= $required ?>>
                        <option value="0" <?= $this->val('invited_lecture', false) ? '' : 'selected' ?>><?= lang('No', 'Nein') ?></option>
                        <option value="1" <?= $this->val('invited_lecture', false) ? 'selected' : '' ?>><?= lang('Yes', 'Ja') ?></option>
                    </select>
                    <label class="<?= $required ?>" for="lecture_type"><?= lang('Invited lecture') ?></label>
                </div>
            <?php
                break;

            case "date-range":
            ?>
                <div class="data-module col-sm-8 col-md-6" data-module="date-range">
                    <label class="<?= $required ?> floating-title" for="date_start">
                        <?= lang('Date range', "Zeitspanne") ?>
                        <span data-toggle="tooltip" data-title="<?= lang('Leave end date empty if only one day', 'Ende leer lassen, falls es nur ein Tag ist') ?>"><i class="ph ph-question" style="line-height:0;"></i></span>
                        <!-- <button class="btn small" id="daterange-toggle-btn" type="button" onclick="rebuild_datepicker(this);"><?= lang('Multiple days', 'Mehrtägig') ?></button> -->
                    </label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="values[start]" id="date_start" <?= $required ?> value="<?= valueFromDateArray($this->val('start')) ?>">
                        <input type="date" class="form-control" name="values[end]" id="date_end" value="<?= valueFromDateArray($this->val('end')) ?>">
                    </div>
                </div>
            <?php
                break;

            case "date-range-ongoing":
            ?>
                <div class="data-module col-sm-8 col-md-6" data-module="date-range-ongoing">
                    <label class="<?= $required ?> element-time floating-title" for="date_start">
                        <?= lang('Date range', "Zeitspanne") ?>
                        <span data-toggle="tooltip" data-title="<?= lang('Leave end date empty ongoing activity', 'Ende leer lassen, falls es eine zurzeit laufende Aktivität ist') ?>"><i class="ph ph-question"></i></span>
                    </label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="values[start]" id="date_start" <?= $required ?> value="<?= valueFromDateArray($this->val('start')) ?>">
                        <input type="date" class="form-control" name="values[end]" id="date_end" value="<?= valueFromDateArray($this->val('end')) ?>">
                    </div>
                </div>
            <?php
                break;
            // case "date-range-ongoing-simple"

            case "software-venue":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="software-venue">
                    <input type="text" class="form-control" <?= $required ?> name="values[software_venue]" id="software_venue" value="<?= $this->val('software_venue') ?>" placeholder="software_venue">
                    <label class="element-other <?= $required ?>" for="software_venue"><?= lang('Publication venue', 'Ort der Veröffentlichung') ?><small><?= lang(', e.g. GitHub, Zenodo ...', ', z.B. GitHub, Zenodo ...') ?></small>
                    </label>
                </div>
            <?php
                break;

            case "venue":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="software-venue">
                    <input type="text" class="form-control" <?= $required ?> name="values[venue]" id="venue" value="<?= $this->val('venue') ?>" placeholder="venue">
                    <label class="element-other <?= $required ?>" for="venue"><?= lang('Publisher / Place of publication', 'Herausgeber / Ort der Veröffentlichung') ?>
                    </label>
                </div>
            <?php
                break;
            case "software-link":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="software-link">
                    <input type="text" class="form-control" <?= $required ?> name="values[link]" id="software_link" value="<?= $this->val('link') ?>" placeholder="link">
                    <label class="element-link <?= $required ?>" for="software_link"><?= lang('Complete link to the software/database', 'Kompletter Link zur Software/Datenbank') ?></label>
                </div>
            <?php
                break;

            case "version":
            ?>
                <div class="data-module floating-form col-sm-2" data-module="version">
                    <input type="text" class="form-control" <?= $required ?> name="values[version]" id="software_version" value="<?= $this->val('version') ?>" placeholder="version">
                    <label class="element-other <?= $required ?>" for="software_version"><?= lang('Version') ?></label>
                </div>
            <?php
                break;

            case "software-type":
            ?>
                <div class="data-module floating-form col-sm-4" data-module="software-type">
                    <select name="values[software_type]" id="software_type" class="form-control" <?= $required ?>>
                        <option value="" <?= empty($this->val('software_type')) ? 'selected' : '' ?>>Not specified</option>
                        <option value="software" <?= $this->val('software_type') == 'software' ? 'selected' : '' ?>>Computer Software</option>
                        <option value="database" <?= $this->val('software_type') == 'database' ? 'selected' : '' ?>>Database</option>
                        <option value="dataset" <?= $this->val('software_type') == 'dataset' ? 'selected' : '' ?>>Dataset</option>
                        <option value="webtool" <?= $this->val('software_type') == 'webtool' ? 'selected' : '' ?>>Website</option>
                        <option value="report" <?= $this->val('software_type') == 'report' ? 'selected' : '' ?>>Report</option>
                    </select>
                    <label class="element-cat <?= $required ?>" for="software_type"><?= lang('Type of software', 'Art der Software') ?></label>
                </div>
            <?php
                break;

            case "iteration":
            ?>
                <div class="data-module floating-form col-sm-4" data-module="misc">
                    <select name="values[iteration]" id="iteration" class="form-control" <?= $required ?> value="<?= $this->val('iteration') ?>" onchange="togglePubType('misc-'+this.value)">
                        <option value="once"><?= lang('once', 'einmalig') ?></option>
                        <option value="annual"><?= lang('continously', 'stetig') ?></option>
                    </select>
                    <label class="<?= $required ?>" for="iteration"><?= lang('Iteration', 'Häufigkeit') ?></label>
                </div>
            <?php
                break;

            case "conference":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="conference">
                    <input type="hidden" class="hidden" name="values[conference_id]" id="conference_id" value="<?= $this->val('conference_id', null) ?>">
                    <input type="text" class="form-control" <?= $required ?> name="values[conference]" id="conference" list="conference-list" placeholder="VAAM 2022" value="<?= $this->val('conference') ?>" oninput="resetConference()">
                    <label for="conference" class="element-other <?= $required ?>"><?= lang('Event', 'Event') ?></label>
                    <p class="m-0 font-size-12 position-absolute text-primary" id="connected-conference">
                        <?php
                        if (!empty($this->form) && isset($this->form['conference_id'])) {
                            $conference = $this->DB->getConnected('conference', $this->form['conference_id']);
                            echo lang('Connected to ', 'Verknüpft mit ') . $conference['title'];
                        } else {
                            echo lang('No event connected', 'Kein Event verknüpft');
                        }
                        ?>
                    </p>
                    <!-- <p class="m-0 font-size-12 ">
                        <?= lang('Latest', 'Zuletzt') ?>:
                        <?php
                        $conferences = $this->DB->db->conferences->find(
                            ['start' => ['$lte' => date('Y-m-d', strtotime('today'))], 'none' => true],
                            ['sort' => ['start' => -1], 'limit' => 5, 'projection' => ['title' => 1]]
                        )->toArray();
                        foreach ($conferences as $c) {
                        ?>
                            <a onclick="selectConference(this)" class="mr-5" data-id="<?= $c['_id'] ?>"><?= $c['title'] ?></a>
                        <?php } ?>
                    </p> -->
                    <script>
                        function resetConference() {
                            $('#conference_id').val('')
                            $('#connected-conference').html(lang('No event connected', 'Kein Event verknüpft'))
                        }

                        function selectConference(el) {
                            var id = $(el).data('id')
                            $('#conference').val(el.innerHTML)
                            $('#conference_id').val(id)
                            $('#connected-conference').html(lang('Connected to ', 'Verknüpft mit ') + el.innerHTML)
                        }
                    </script>
                </div>

                <datalist id="conference-list">
                    <?php
                    foreach ($this->DB->db->conferences->distinct('title') as $c) { ?>
                        <option><?= $c ?></option>
                    <?php } ?>
                </datalist>
                <!-- TODO:<datalist id="conference-list">
                    <?php
                    foreach ($this->DB->db->conferences->find() as $c) { ?>
                        <option data-id="<?= $c['_id'] ?>"><?= $c['title'] . ': ' . $c['title_full'] ?></option>
                    <?php } ?>
                </datalist> -->

            <?php
                break;

            case "location":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="location">
                    <input type="text" class="form-control" <?= $required ?> name="values[location]" id="location" placeholder="Berlin, Germany" value="<?= $this->val('location') ?>" placeholder="location">
                    <label for="location" class="element-other <?= $required ?>"><?= lang('Location', 'Ort') ?></label>
                </div>
            <?php
                break;

            case "issn":
                $issn = $this->val('issn');
                if (is_array($issn)) {
                    $issn = implode(', ', $issn);
                }
            ?>
                <div class="data-module floating-form col-sm-6" data-module="issn">
                    <input type="text" class="form-control" <?= $required ?> name="values[issn]" id="issn" value="<?= $issn ?>" placeholder="issn">
                    <label for="issn" class="element-other <?= $required ?>"><?= lang('ISSN') ?></label>
                </div>
            <?php
                break;

            case "journal":
            ?>
                <div class="data-module col-12" data-module="journal">
                    <a href="<?= ROOTPATH ?>/docs/add-activities#das-journal-bearbeiten" target="_blank" class="<?= $required ?> float-right">
                        <i class="ph ph-question"></i> <?= lang('Help', 'Hilfe') ?>
                    </a>
                    <label for="journal" class="floating-title <?= $required ?>">Journal</label>
                    <a href="#journal-select" id="journal-field" class="module">
                        <!-- <a class="btn link" ><i class="ph ph-edit"></i> <?= lang('Edit Journal', 'Journal bearbeiten') ?></a> -->
                        <span class="float-right text-secondary"><i class="ph ph-edit"></i></span>

                        <div id="selected-journal">
                            <?php if (!empty($this->form) && isset($this->form['journal_id'])) :
                                $journal = $this->DB->getConnected('journal', $this->form['journal_id']);
                            ?>
                                <h5 class="m-0"><?= $journal['journal'] ?></h5>
                                <span class="float-right text-muted"><?= $journal['publisher'] ?></span>
                                <span class="text-muted">ISSN: <?= print_list($journal['issn']) ?></span>
                            <?php else : ?>
                                <span class="title"><?= lang('No Journal selected', 'Kein Journal ausgewählt') ?></span>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" class="form-control hidden" name="values[journal]" value="<?= $this->val('journal') ?>" id="journal" list="journal-list" <?= $required ?> readonly>
                        <input type="hidden" class="form-control hidden" name="values[journal_id]" value="<?= $this->val('journal_id') ?>" id="journal_id" <?= $required ?> readonly>

                    </a>
                </div>
            <?php
                break;

            case "magazine":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="magazine">
                    <input type="text" class="form-control" <?= $required ?> name="values[magazine]" value="<?= $this->val('magazine') ?>" id="magazine" placeholder="magazine">
                    <label for="magazine" class="element-cat <?= $required ?>"><?= lang('Magazine / Venue', 'Zeitschrift / Veröffentlichungsort') ?></label>
                </div>
            <?php
                break;

            case "link":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="link">
                    <input type="text" class="form-control" <?= $required ?> name="values[link]" value="<?= $this->val('link') ?>" id="link" placeholder="link">
                    <label for="link" class="element-link <?= $required ?>">Link</label>
                </div>
            <?php
                break;

            case "book-title":
            ?>
                <div class="data-module floating-form col-6" data-module="book-title">
                    <input type="text" class="form-control" name="values[book]" value="<?= $this->val('book') ?>" id="book" <?= $required ?> placeholder="book-title">
                    <label for="book" class="<?= $required ?> element-cat"><?= lang('Book title', 'Buchtitel') ?></label>
                </div>
            <?php
                break;

            case "book-series":
            ?>
                <div class="data-module floating-form col-6" data-module="book-series">
                    <input type="text" class="form-control" <?= $required ?> name="values[series]" value="<?= $this->val('series') ?>" id="series" placeholder="series">
                    <label for="series" class="element-other <?= $required ?>"><?= lang('Series', 'Buchreihe') ?></label>
                </div>
            <?php
                break;

            case "edition":
            ?>
                <div class="data-module floating-form col-sm-4" data-module="edition">
                    <input type="number" class="form-control" <?= $required ?> name="values[edition]" value="<?= $this->val('edition') ?>" id="edition" placeholder="edition">
                    <label for="edition" class="element-other <?= $required ?>">Edition</label>
                </div>
            <?php
                break;

            case "issue":
            ?>
                <div class="data-module floating-form col-sm-4" data-module="issue">
                    <input type="text" class="form-control" <?= $required ?> name="values[issue]" value="<?= $this->val('issue') ?>" id="issue" placeholder="issue">
                    <label for="issue" class="element-other <?= $required ?>">Issue</label>
                </div>
            <?php
                break;

            case "volume":
            ?>
                <div class="data-module floating-form col-sm-4" data-module="volume">
                    <input type="text" class="form-control" <?= $required ?> name="values[volume]" value="<?= $this->val('volume') ?>" id="volume" placeholder="volume">
                    <label for="volume" class="element-other <?= $required ?>">Volume</label>
                </div>
            <?php
                break;

            case "pages":
            ?>
                <div class="data-module floating-form col-sm-4" data-module="pages">
                    <input type="text" class="form-control" <?= $required ?> name="values[pages]" value="<?= $this->val('pages') ?>" id="pages" placeholder="pages">
                    <label for="pages" class="element-other <?= $required ?>">Pages</label>
                </div>
            <?php
                break;


            case "peer-reviewed":
            ?>
                <div class="data-module col-sm-12" data-module="pages">
                    <div class="custom-radio d-inline-block" id="peer_reviewed-div">
                        <input type="radio" id="peer_reviewed-0" value="false" name="values[peer_reviewed]" <?= $this->val('peer_reviewed', false) ? '' : 'checked' ?>>
                        <label for="peer_reviewed-0"><i class="icon-closed-access text-danger"></i> Non-refereed</label>
                    </div>
                    <div class="custom-radio d-inline-block ml-20" id="peer_reviewed-div">
                        <input type="radio" id="peer_reviewed" value="true" name="values[peer_reviewed]" <?= $this->val('peer_reviewed', false) ? 'checked' : '' ?>>
                        <label for="peer_reviewed"><i class="icon-open-access text-success"></i> Peer-Reviewed</label>
                    </div>
                </div>
            <?php
                break;

            case "publisher":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="publisher">
                    <input type="text" class="form-control" <?= $required ?> name="values[publisher]" value="<?= $this->val('publisher') ?>" id="publisher" placeholder="publisher">
                    <label for="publisher" class="element-other <?= $required ?>"><?= lang('Publisher', 'Verlag') ?></label>
                </div>
            <?php
                break;

            case "university":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="university">
                    <input type="text" class="form-control" <?= $required ?> name="values[publisher]" value="<?= $this->val('publisher') ?>" id="publisher" placeholder="publisher">
                    <label for="publisher" class="element-other <?= $required ?>"><?= lang('University', 'Universität') ?></label>
                </div>
            <?php
                break;

            case "city":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="city">
                    <input type="text" class="form-control" <?= $required ?> name="values[city]" value="<?= $this->val('city') ?>" id="city" placeholder="city">
                    <label for="city" class="element-other <?= $required ?>"><?= lang('Location (City, Country)', 'Ort (Stadt, Land)') ?></label>
                </div>
            <?php
                break;

            case "editor":
            ?>
                <div class="data-module col-12" data-module="editor">
                    <label for="editor" class="<?= $required ?> floating-title"><?= lang('Editor(s) (in correct order)', 'Herausgeber (in korrekter Reihenfolge)') ?></label>
                    <div class="author-widget" id="editor-widget">
                        <div class="author-list p-10" id="editor-list">
                            <?= $this->editors ?>
                        </div>
                        <div class="footer">
                            <div class="input-group small d-inline-flex w-auto">
                                <input type="text" placeholder="<?= lang('Add editor ...', 'Füge Editor hinzu ...') ?>" onkeypress="addAuthor(event, true);" id="add-editor" list="scientist-list">
                                <div class="input-group-append">
                                    <button class="btn secondary h-full" type="button" onclick="addAuthor(event, true);">
                                        <i class="ph ph-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                break;

            case "doi":
            ?>
                <?php if (empty($this->form)) { ?>
                    <div class="data-module floating-form col-sm-6" data-module="doi">
                        <input type="text" class="form-control" <?= $required ?> name="values[doi]" value="<?= $this->val('doi') ?>" id="doi" placeholder="doi">
                        <label for="doi" class="element-link <?= $required ?>">DOI</label>
                    </div>
                <?php } else { ?>
                    <div class="data-module col-sm-6" data-module="doi">
                        <label for="doi" class="floating-title <?= $required ?>">DOI</label>

                        <div class="input-group ">
                            <input type="text" class="form-control" <?= $required ?> name="values[doi]" value="<?= $this->val('doi') ?>" id="doi" placeholder="doi">
                            <div class="input-group-append" data-toggle="tooltip" data-title="<?= lang('Retreive updated information via DOI', 'Aktualisiere die Daten via DOI') ?>">
                                <button class="btn" type="button" onclick="getPubData(event, this)"><i class="ph ph-arrows-clockwise"></i></button>
                                <span class="sr-only">
                                    <?= lang('Retreive updated information via DOI', 'Aktualisiere die bibliographischen Daten via DOI') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php
                break;

            case "pubmed":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="pubmed">
                    <input type="number" class="form-control" <?= $required ?> name="values[pubmed]" value="<?= $this->val('pubmed') ?>" id="pubmed" placeholder="pubmed">
                    <label for="pubmed" class="<?= $required ?>">Pubmed</label>
                </div>
            <?php
                break;

            case "isbn":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="isbn">
                    <input type="text" class="form-control" <?= $required ?> name="values[isbn]" value="<?= $this->val('isbn') ?>" id="isbn" placeholder="isbn">
                    <label for="isbn" class="<?= $required ?>">ISBN</label>
                </div>
            <?php
                break;

            case "doctype":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="doctype">
                    <input type="text" class="form-control" <?= $required ?> name="values[doc_type]" value="<?= $this->val('doc_type') ?>" id="doctype" placeholder="Report" placeholder="doc_type">
                    <label for="doc_type" class="<?= $required ?>"><?= lang('Document type', 'Dokumententyp') ?></label>
                </div>
            <?php
                break;

            case "openaccess":
            ?>
                <div class="data-module col-12" data-module="openaccess">
                    <div class="custom-radio d-inline-block" id="open_access-div">
                        <input type="radio" id="open_access-0" value="false" name="values[open_access]" <?= $this->val('open_access', false) ? '' : 'checked' ?>>
                        <label for="open_access-0"><i class="icon-closed-access text-danger"></i> Closed access</label>
                    </div>
                    <div class="custom-radio d-inline-block ml-20" id="open_access-div">
                        <input type="radio" id="open_access" value="true" name="values[open_access]" <?= $this->val('open_access', false) ? 'checked' : '' ?>>
                        <label for="open_access"><i class="icon-open-access text-success"></i> Open access</label>
                    </div>
                </div>
            <?php
                break;

            case "openaccess-status":
                $status = $this->val('oa_status', false);
                if (!$status) $status = $this->val('open_access', false) ? 'open' : 'closed';
            ?>
                <!-- oa_status -->
                <div class="data-module floating-form col-3" data-module="openaccess-status">
                    <select class="form-control" id="oa_status" name="values[oa_status]" <?= $required ?> autocomplete="off">
                        <option value="closed" <?= $status == 'closed' ? 'selected' : '' ?>>Closed Access</option>
                        <option value="open" <?= $status == 'open' ? 'selected' : '' ?>>Open Access (<?= lang('unknown status', 'Unbekannter Status') ?>)</option>
                        <option value="diamond" <?= $status == 'diamond' ? 'selected' : '' ?>>Open Access (Diamond)</option>
                        <option value="gold" <?= $status == 'gold' ? 'selected' : '' ?>>Open Access (Gold)</option>
                        <option value="green" <?= $status == 'green' ? 'selected' : '' ?>>Open Access (Green)</option>
                        <option value="hybrid" <?= $status == 'hybrid' ? 'selected' : '' ?>>Open Access (Hybrid)</option>
                        <option value="bronze" <?= $status == 'bronze' ? 'selected' : '' ?>>Open Access (Bronze)</option>
                    </select>
                    <label for="oa_status" class="<?= $required ?>"><?= lang('Open Access Status', 'Open Access Status') ?></label>
                </div>
            <?php
                break;

            case "online-ahead-of-print":
            ?>
                <div class="data-module col-12" data-module="online-ahead-of-print">
                    <div class="custom-checkbox <?= isset($_GET['epub']) ? 'text-danger' : '' ?>" id="epub-div">
                        <input type="checkbox" id="epub" value="1" name="values[epub]" <?= (!isset($_GET['epub']) && $this->val('epub', false)) ? 'checked' : '' ?>>
                        <label for="epub">Online ahead of print</label>
                    </div>
                </div>
            <?php
                break;

            case "correction":
            ?>
                <div class="data-module col-12" data-module="correction">
                    <div class="custom-checkbox" id="correction-div">
                        <input type="checkbox" id="correction" value="1" name="values[correction]" <?= $this->val('correction', false) ? 'checked' : '' ?>>
                        <label for="correction"><?= lang('Correction') ?></label>
                    </div>
                </div>
            <?php
                break;

            case "scientist":
            ?>
                <div class="data-module floating-form col-sm-4" data-module="scientist">
                    <select class="form-control" id="username" name="values[user]" <?= $required ?> autocomplete="off">
                        <?php
                        foreach ($this->userlist as $j) { ?>
                            <option value="<?= $j['username'] ?>" <?= $j['username'] == ($this->form['user'] ?? $this->user) ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                        <?php } ?>
                    </select>
                    <label class="<?= $required ?> element-author" for="username">
                        <?= lang('Scientist', 'Wissenschaftler:in') ?>
                    </label>
                </div>
            <?php
                break;

            case "scope":
                $scope = $this->val('scope', false);
            ?>
                <div class="data-module floating-form col-sm-4" data-module="scope">
                    <select class="form-control" id="scope" name="values[scope]" <?= $required ?> autocomplete="off">
                        <option <?= $scope == 'local' ? 'selected' : '' ?>>local</option>
                        <option <?= $scope == 'regional' ? 'selected' : '' ?>>regional</option>
                        <option <?= $scope == 'national' ? 'selected' : '' ?>>national</option>
                        <option <?= $scope == 'international' ? 'selected' : '' ?>>international</option>
                    </select>
                    <label class="<?= $required ?>" for="scope">
                        <?= lang('Scope', 'Reichweite') ?>
                    </label>
                </div>
            <?php
                break;

            case "role":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="role">
                    <input type="text" class="form-control" id="role" value="<?= $this->val('role') ?>" name="values[role]" <?= $required ?> placeholder="role">
                    <label class="<?= $required ?>" for="role">
                        <?= lang('Role/Function', 'Rolle/Funktion') ?>
                    </label>
                </div>
            <?php
                break;

            case "license":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="license">
                    <input type="text" class="form-control" id="license" value="<?= $this->val('license') ?>" name="values[license]" <?= $required ?> placeholder="license">
                    <label class="<?= $required ?>" for="license">
                        <?= lang('License', 'Lizenz') ?>
                    </label>

                    <small class="help-text">
                        <?= lang('If applicable, enter', 'Falls möglich, die') ?>
                        <a href="https://opensource.org/licenses/" target="_blank" rel="noopener noreferrer"><?= lang('SPDX-ID from', 'SPDX-ID der') ?> OSI</a>
                        <?= lang('or CC license from', 'oder die CC-Lizenz von') ?>
                        <a href="https://creativecommons.org/share-your-work/cclicenses/" target="_blank" rel="noopener noreferrer">Creative Commons</a>.
                        <?= lang('', 'angeben') ?>.
                    </small>
                </div>
            <?php
                break;

            case "review-type":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="review-type">
                    <input type="text" class="form-control" id="review-type" value="<?= $this->val('review-type', 'Begutachtung eines Forschungsantrages') ?>" name="values[review-type]" <?= $required ?> placeholder="review-type">
                    <label class="element-cat <?= $required ?>" for="review-type">
                        <?= lang('Type of review', 'Art der Begutachtung') ?>
                    </label>
                </div>
            <?php
                break;

            case "editorial":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="editorial">
                    <input type="text" class="form-control" <?= $required ?> name="values[editor_type]" id="editor_type" value="<?= $this->val('editor_type') ?>" placeholder="Guest Editor for Research Topic 'XY'" placeholder="editor_type">
                    <label for="editor_type" class="element-cat <?= $required ?>">
                        <?= lang('Details', 'Details') ?>
                    </label>
                </div>
            <?php
                break;

            case "political_consultation":
            ?>
                <div class="data-module floating-form col-sm-6" data-module="political_consultation">
                    <select type="text" class="form-control" <?= $required ?> name="values[political_consultation]" id="political_consultation">
                        <!-- Gutachten,Positionspapier,Studie,Sonstiges -->
                        <?php if (!$required) { ?>
                            <option value="" <?= empty($this->val('political_consultation')) ? 'selected' : '' ?>><?= lang('No political consultation', 'Keine politische Beratung') ?></option>
                        <?php } ?>
                        <option value="Gutachten" <?= $this->val('political_consultation') == 'Gutachten' ? 'selected' : '' ?>><?= lang('Review', 'Gutachten') ?></option>
                        <option value="Positionspapier" <?= $this->val('political_consultation') == 'Positionspapier' ? 'selected' : '' ?>><?= lang('Position paper', 'Positionspapier') ?></option>
                        <option value="Studie" <?= $this->val('political_consultation') == 'Studie' ? 'selected' : '' ?>><?= lang('Study', 'Studie') ?></option>
                        <option value="Sonstiges" <?= $this->val('political_consultation') == 'Sonstiges' ? 'selected' : '' ?>><?= lang('Other', 'Sonstiges') ?></option>
                    </select>

                    <label for="political_consultation" class="<?= $required ?>">
                        <?= lang('Contribution to political and social consulting', 'Beitrag zur Politik- und Gesellschaftsberatung') ?>
                    </label>
                </div>
            <?php
                break;

            default:
            ?>
                <div class="data-module alert danger col-12">
                    <?= lang('Module ' . $module . ' is not defined.', 'Modul ' . $module . ' existiert nicht.') ?>
                </div>
<?php
                break;
        }
    }
}
