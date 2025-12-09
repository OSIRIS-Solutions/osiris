<?php
include_once "_config.php";
include_once "init.php";
include_once "Vocabulary.php";

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
    private $fields = array();

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
            "label" => "Author(s) / Responsible person",
            "label_de" => "Autor(en) / Verantwortliche Person",
            "description" => "A compressed module for authors, with 'tag-like' input fields for authors. Supports multiple authors, drag-and-drop, auto-suggest and affiliation-flagging via double click.",
            "description_de" => "Ein komprimiertes Modul für Autoren, mit 'tag-ähnlichen' Eingabefeldern für Autoren. Unterstützt mehrere Autoren, Drag-and-Drop, Auto-Suggest und Affiliation-Flagging per Doppelklick.",
            "width" => 0,
            "tags" => ['authors']
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
            "name" => "Author Table",
            "name_de" => "Autoren-Tabelle",
            "label" => "Author(s) / Responsible person",
            "label_de" => "Autor(en) / Verantwortliche Person",
            "description" => "A comprehensive table for authors, with individual input fields for authors, including first an last name, position, username and an affiliation checkbox. Supports multiple authors, drag-and-drop, and auto-suggest on username field.",
            "description_de" => "Eine umfassende Tabelle für Autoren, mit individuellen Eingabefeldern für Autoren, einschließlich Vor- und Nachname, Position, Benutzername und einem Affiliation-Checkbox. Unterstützt mehrere Autoren, Drag-and-Drop und Auto-Suggest im Benutzernamenfeld.",
            "width" => 0,
            "tags" => ['authors']
        ],
        "book-series" => [
            "fields" => ["series" => 'Book Series on Open Source Systems'],
            "name" => "Book-Series",
            "name_de" => "Bücherreihe",
            "label" => "Series",
            "label_de" => "Bücherreihe",
            "description" => "A field for the book series, where the publication is part of.",
            "description_de" => "Ein Feld für die Buchreihe, zu der die Publikation gehört.",
            "width" => 6,
            "tags" => ['publication']
        ],
        "book-title" => [
            "fields" => ["book" => 'Research Information Systems'],
            "name" => "Book Title",
            "name_de" => "Buchtitel",
            "label" => "Book Title",
            "label_de" => "Buchtitel",
            "description" => "A field for the book title, where the publication is part of.",
            "description_de" => "Ein Feld für den Buchtitel, zu dem die Publikation gehört.",
            "width" => 6,
            "tags" => ['publication']
        ],
        "city" => [
            "fields" => ["city" => 'Helmstedt, Deutschland'],
            "name" => "City",
            "name_de" => "Stadt",
            "label" => "City",
            "label_de" => "Stadt",
            "description" => "A field for a city, e.g for the publisher of a book or for a conference location.",
            "description_de" => "Ein Feld für eine Stadt, z.B. für den Verlag eines Buches oder für einen Konferenzort.",
            "width" => 6,
            "tags" => ['general', 'location']
        ],
        "conference" => [
            "fields" => ["conference" => '1st CRIS Conference'],
            "name" => "Conference",
            "name_de" => "Konferenz",
            "label" => "Event",
            "label_de" => "Veranstaltung",
            "description" => "A field for the name of a conference/event.",
            "description_de" => "Ein Feld für den Namen einer Konferenz/Veranstaltung.",
            "width" => 6,
            "tags" => ['event']
        ],
        "correction" => [
            "fields" => ["correction" => true],
            "name" => "Correction",
            "name_de" => "Correction",
            "label" => "Correction",
            "label_de" => "Correction",
            "description" => "Checkbox for a correction flag.",
            "description_de" => "Checkbox für eine Korrekturmarkierung.",
            "width" => 12,
            "tags" => ['publication']
        ],
        "date-range" => [
            "fields" => ["start" => ["year" => 2022, "month" => 9, "day" => 6], "end" => ["year" => 2022, "month" => 9, "day" => 8]],
            "name" => "Date Range",
            "name_de" => "Zeitspanne",
            "label" => "Date Range",
            "label_de" => "Zeitspanne",
            "description" => "A field for a date range, e.g. for a event. <b>Important</b>: If the end date is not set, the date is assumed to be only one day! If you want an ongoing event, please use the 'date-range-ongoing' module.",
            "description_de" => "Ein Feld für einen Zeitraum, z.B. für eine Konferenz. <b>Wichtig</b>: Wenn das Enddatum nicht gesetzt ist, wird das Datum als nur ein Tag angenommen! Wenn Sie ein laufendes Ereignis möchten, verwenden Sie bitte das Modul 'date-range-ongoing'.",
            "width" => 6,
            "tags" => ['date', 'important']
        ],
        "date-range-ongoing" => [
            "fields" => ["start" => ["year" => 2022, "month" => 9, "day" => 6], "end" => null],
            "name" => "Date Range Ongoing",
            "name_de" => "Zeitspanne laufend",
            "label" => "Date Range",
            "label_de" => "Zeitspanne",
            "description" => "A field for a possibly ongoing date range, e.g. for a membership. <b>Important</b>: If the end date is not set, the date is assumed to be ongoing! If you want a fixed date range, please use the 'date-range' module.",
            "description_de" => "Ein Feld für einen möglicherweise laufenden Zeitraum, z.B. für eine Mitgliedschaft. <b>Wichtig</b>: Wenn das Enddatum nicht gesetzt ist, wird das Datum als laufend angenommen! Wenn Sie einen festen Zeitraum möchten, verwenden Sie bitte das Modul 'date-range'.",
            "width" => 6,
            "tags" => ['date', 'important']
        ],
        "date" => [
            "fields" => ["year" => 2023, "month" => 5, "day" => 4],
            "name" => "Date",
            "name_de" => "Datum",
            "label" => null,
            "label_de" => null,
            "description" => "A field for a date, divided into year, month and day. A shortcut button for today's date is available.",
            "description_de" => "Ein Feld für ein Datum, aufgeteilt in Jahr, Monat und Tag. Ein Shortcut-Button für das heutige Datum ist verfügbar.",
            "width" => 12,
            "tags" => ['date', 'important']
        ],
        "details" => [
            "fields" => ["details" => "Weitere Details"],
            "name" => "Details",
            "name_de" => "Details",
            "label" => "Details",
            "label_de" => "Details",
            "description" => "A field for additional details, e.g. for a grant.",
            "description_de" => "Ein Feld für weitere Details, z.B. für ein Stipendium.",
            "width" => 6,
            "tags" => ['general']
        ],
        "doctype" => [
            "fields" => ["doc_type" => 'White Paper'],
            "name" => "Document type",
            "name_de" => "Dokumententyp",
            "label" => "Document type",
            "label_de" => "Dokumententyp",
            "description" => "A field for the document type, e.g. for a report.",
            "description_de" => "Ein Feld für den Dokumententyp, z.B. für einen Bericht.",
            "width" => 6,
            "tags" => ['publication']
        ],
        "doi" => [
            "fields" => ["doi" => '10.1234/idk/invalid'],
            "name" => "DOI",
            "name_de" => "DOI",
            "label" => "DOI",
            "label_de" => "DOI",
            "description" => "A field for the Digital Object Identifier (DOI) of a publication. This field is used to uniquely identify a publication.",
            "description_de" => "Ein Feld für den Digital Object Identifier (DOI) einer Publikation. Dieses Feld wird verwendet, um eine Publikation eindeutig zu identifizieren.",
            "width" => 6,
            "tags" => ['important', 'publication']
        ],
        "edition" => [
            "fields" => ["edition" => 2],
            "name" => "Edition",
            "name_de" => "Edition",
            "label" => "Edition",
            "label_de" => "Edition",
            "description" => "A field for the edition of a publication, e.g. for a book.",
            "description_de" => "Ein Feld für die Auflage einer Publikation, z.B. für ein Buch.",
            "width" => 6,
            "tags" => ['publication']
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
            "name_de" => "Herausgeber",
            "label" => "Editor(s)",
            "label_de" => "Herausgeber",
            "description" => "A compressed module for editors, with 'tag-like' input fields for editors. Supports multiple editors, drag-and-drop, auto-suggest and affiliation-flagging via double click.",
            "description_de" => "Ein komprimiertes Modul für Herausgeber, mit 'tag-ähnlichen' Eingabefeldern für Herausgeber. Unterstützt mehrere Herausgeber, Drag-and-Drop, Auto-Suggest und Affiliation-Flagging per Doppelklick.",
            "width" => 12,
            "tags" => ['authors', 'publication']
        ],
        "editorial" => [
            "fields" => ["editor_type" => 'Guest Editor'],
            "name" => "Editor Type",
            "name_de" => "Art der Herausgabe",
            "label" => "Type of Editorial",
            "label_de" => "Art der Herausgabe",
            "description" => "A field for the editorial type, e.g. for a special issue.",
            "description_de" => "Ein Feld für den Herausgebertyp, z.B. für eine Sonderausgabe.",
            "width" => 6,
            "tags" => ['publication']
        ],
        "event-select" => [
            "fields" => [],
            "name" => "Event Selector",
            "name_de" => "Veranstaltungsauswahl",
            "label" => "Select an event to auto-fill date, location and event",
            "label_de" => "Wähle ein Event aus, um Datum, Ort und Veranstaltung automatisch zu füllen",
            "description" => "A field for selecting an event that has been entered in the system. If the user clicks on an event, date, loation and event name are automatically filled in.",
            "description_de" => "Ein Feld zum Auswählen einer Veranstaltung, die bereits im System erfasst wurde. Wenn ein:e Benutzer:in auf eine Veranstaltung klickt, werden Datum, Ort und Veranstaltungsname automatisch ausgefüllt.",
            "width" => 0,
            "tags" => ['event', 'modificator']
        ],
        "funding_type" => [
            "fields" => ["funding_type" => 'Third-Party Funded'],
            "name" => "Funding Type",
            "name_de" => "Art der Finanzierung",
            "label" => "Type of Funding",
            "label_de" => "Art der Finanzierung",
            "description" => "A field for the type of financing, e.g., for a scholarship. The values are managed via the vocabulary and are identical to the values from the data field in projects.",
            "description_de" => "Ein Feld für die Art der Finanzierung, z.B. für ein Stipendium. Die Werte werden über das Vokabular verwaltet und sind identisch zu den Werten aus dem Datenfeld in Projekten.",
            "width" => 6,
            "tags" => ['general']
        ],
        "guest-category" => [
            "fields" => ["category" => 'guest scientist'],
            "name" => "Guest Category",
            "name_de" => "Gäste-Kategorie",
            "label" => "Category of the guest",
            "label_de" => "Art des Gastes",
            "description" => "A field for the category of a guest, can be one of the following: guest scientist, lecture internship, student internship, other.",
            "description_de" => "Ein Feld für die Kategorie eines Gastes, kann eine der folgenden sein: Gastwissenschaftler:in, Pflichtpraktikum im Rahmen des Studium, Schülerpraktikum, Sonstiges.",
            "width" => 6,
            "tags" => ['people']
        ],
        "gender" => [
            "fields" => ["gender" => 'f'],
            "name" => "Gender",
            "name_de" => "Geschlecht",
            "label" => "Gender",
            "label_de" => "Geschlecht",
            "description" => "A field to select the gender of a person. Can be one of the following: male, female, non-binary, not specified.",
            "description_de" => "Ein Feld zur Auswahl des Geschlechts einer Person. Kann eines der folgenden sein: männlich, weiblich, divers, nicht spezifiziert.",
            "width" => 6,
            "tags" => ['people']
        ],
        "nationality" => [
            "fields" => ["country" => 'DE'],
            "name" => "Nationality",
            "name_de" => "Nationalität",
            "label" => "Nationality",
            "label_de" => "Nationalität",
            "description" => "A synonym for the 'country' field, used for persons.",
            "description_de" => "Ein Synonym für das Feld 'Land', das für Personen verwendet wird.",
            "width" => 6,
            "tags" => ['people', 'location']
        ],
        "country" => [
            "fields" => ["country" => 'DE'],
            "name" => "Country",
            "name_de" => "Land",
            "label" => "Country",
            "label_de" => "Land",
            "description" => "A field for a country, that can be selected from a list and is saved as a two-letter ISO country code.",
            "description_de" => "Ein Feld für ein Land, das aus einer Liste ausgewählt werden kann und als zweistelliger ISO-Ländercode gespeichert wird.",
            "width" => 6,
            "tags" => ['location']
        ],
        "countries" => [
            "fields" => ["countries" => ['DE', 'AT', 'CH']],
            "name" => "Countries",
            "name_de" => "Länder",
            "label" => "Countries",
            "label_de" => "Länder",
            "description" => "A field for a list of countries, that can be selected from a list and is saved as a two-letter ISO country code.",
            "description_de" => "Ein Feld für eine Liste von Ländern, die aus einer Liste ausgewählt werden können und als zweistelliger ISO-Ländercode gespeichert werden.",
            "width" => 6,
            "tags" => ['location']
        ],
        "abstract" => [
            "fields" => ["abstract" => 'OSIRIS ist einzigartig in seinen Konfigurationsmöglichkeiten. Während sich viele andere CRIS nur auf Publikationen beschränken, kann in OSIRIS eine Vielzahl an Aktivitäten hinzugefügt werden.'],
            "name" => "Abstract",
            "name_de" => "Zusammenfassung",
            "label" => "Abstract",
            "label_de" => "Zusammenfassung",
            "description" => "A field for the abstract, summarizing an activity.",
            "description_de" => "Ein Feld für die Zusammenfassung, die eine Aktivität zusammenfasst.",
            "width" => 0,
            "tags" => ['publication', 'important']
        ],
        "isbn" => [
            "fields" => ["isbn" => '979-8716615502'],
            "name" => "ISBN",
            "name_de" => "ISBN",
            "label" => "ISBN",
            "label_de" => "ISBN",
            "description" => "A field for the International Standard Book Number (ISBN) of a publication. This field is used to uniquely identify a book.",
            "description_de" => "Ein Feld für die Internationale Standardbuchnummer (ISBN) einer Publikation. Dieses Feld wird verwendet, um ein Buch eindeutig zu identifizieren.",
            "width" => 6,
            "tags" => ['publication']
        ],
        "issn" => [
            "fields" => ["issn" => ["1362-4962", "0305-1048"]],
            "name" => "ISSN",
            "name_de" => "ISSN",
            "label" => "ISSN",
            "label_de" => "ISSN",
            "description" => "A field for the International Standard Serial Number (ISSN) of a publication. This field is used to uniquely identify a journal. May contain multiple ISSNs, separated by comma.",
            "description_de" => "Ein Feld für die Internationale Standardnummer für fortlaufende Sammelwerke (ISSN) einer Publikation. Dieses Feld wird verwendet, um eine Zeitschrift eindeutig zu identifizieren. Kann mehrere ISSNs enthalten, die durch Komma getrennt sind.",
            "width" => 6,
            "tags" => ['publication', 'journal']
        ],
        "issue" => [
            "fields" => ["issue" => "D1"],
            "name" => "Issue",
            "name_de" => "Issue",
            "label" => "Issue",
            "label_de" => "Issue",
            "description" => "A field for the issue of a publication, e.g. for a journal.",
            "description_de" => "Ein Feld für das Heft einer Publikation, z.B. für eine Zeitschrift.",
            "width" => 6,
            "tags" => ['publication', 'journal']
        ],
        "iteration" => [
            "fields" => ["iteration" => "annual"],
            "name" => "Iteration",
            "name_de" => "Häufigkeit",
            "label" => "Iteration",
            "label_de" => "Häufigkeit",
            "description" => "A field for the iteration of an event, e.g. for a conference. Possible values are: continously or once.",
            "description_de" => "Ein Feld für die Häufigkeit einer Veranstaltung, z.B. für eine Konferenz. Mögliche Werte sind: kontinuierlich oder einmalig.",
            "width" => 6,
            "tags" => ['event']
        ],
        "journal" => [
            "fields" => ["journal" => 'Information Systems Research', "journal_id" => null],
            "name" => "Journal",
            "name_de" => "Journal",
            "label" => "Journal",
            "label_de" => "Zeitschrift",
            "description" => "A field for selecting a journal from the database. If the journal does not exist yet, the user will be prompted to select one from an online catalogue that will be then saved into the database.",
            "description_de" => "Ein Feld zum Auswählen eines Journals (Zeitschrift) aus der Datenbank. Wenn das Journal noch nicht existiert, wird der Benutzer aufgefordert, eines aus einem Online-Katalog auszuwählen, das dann in die Datenbank gespeichert wird.",
            "width" => 12,
            "tags" => ['publication', 'journal']
        ],
        "lecture-invited" => [
            "fields" => ["invited_lecture" => true],
            "name" => "Invited lecture",
            "name_de" => "Eingeladener Vortrag",
            "label" => "Invited lecture",
            "label_de" => "Eingeladener Vortrag",
            "description" => "Checkbox for an invited lecture flag.",
            "description_de" => "Checkbox für eine eingeladene Vortragsmarkierung.",
            "width" => 6,
            "tags" => ['event']
        ],
        "lecture-type" => [
            "fields" => ["lecture_type" => 'short'],
            "name" => "Lecture Length",
            "name_de" => "Vortragslänge",
            "label" => "Length of the lecture",
            "label_de" => "Länge des Vortrags",
            "description" => "A field for the length of a lecture, e.g. for a conference. Possible values are: short, medium, long.",
            "description_de" => "Ein Feld für die Länge eines Vortrags, z.B. für eine Konferenz. Mögliche Werte sind: kurz, mittel, lang.",
            "width" => 6,
            "tags" => ['event']
        ],
        "license" => [
            "fields" => ["license" => 'MIT'],
            "name" => "License",
            "name_de" => "Lizenz",
            "label" => "License",
            "label_de" => "Lizenz",
            "description" => "A field for the license, e.g. for software.",
            "description_de" => "Ein Feld für die Lizenz, z.B. für Software.",
            "width" => 6,
            "tags" => ['software', 'publication']
        ],
        "link" => [
            "fields" => ["link" => 'https://osiris-app.de'],
            "name" => "Link",
            "name_de" => "Link",
            "label" => "Link",
            "label_de" => "Link",
            "description" => "A field for a link, e.g. for an event.",
            "description_de" => "Ein Feld für einen Link, z.B. für eine Veranstaltung.",
            "width" => 6,
            "tags" => ['general', 'important']
        ],
        "location" => [
            "fields" => ["location" => 'Braunschweig, Germany'],
            "name" => "Location",
            "name_de" => "Ort",
            "label" => "Location",
            "label_de" => "Ort",
            "description" => "A field for the more general location, e.g. for an event. If you want to specify a city an country, please use the 'city' and 'country' modules.",
            "description_de" => "Ein Feld für den allgemeineren Ort, z.B. für eine Veranstaltung. Wenn Sie eine Stadt und ein Land angeben möchten, verwenden Sie bitte die Module 'city' und 'country'.",
            "width" => 6,
            "tags" => ['location']
        ],
        "magazine" => [
            "fields" => ["magazine" => 'Apothekenumschau'],
            "name" => "Magazine",
            "name_de" => "Magazin",
            "label" => "Magazine / Venue",
            "label_de" => "Zeitschrift / Veröffentlichungsort",
            "description" => "A field for the magazine or publication venue, where the publication is part of. Not standardized and typically used if no journal is given.",
            "description_de" => "Ein Feld für das Magazin oder den Veröffentlichungsort, zu dem die Publikation gehört. Nicht standardisiert und typischerweise verwendet, wenn keine Zeitschrift angegeben ist.",
            "width" => 6,
            "tags" => ['publication', 'journal']
        ],
        "online-ahead-of-print" => [
            "fields" => ["epub" => true],
            "name" => "Online Ahead Of Print",
            "name_de" => "Online Ahead Of Print",
            "label" => "Online Ahead Of Print",
            "label_de" => "Online Ahead Of Print",
            "description" => "Checkbox for an online ahead of print flag. This flag is used to indicate that a publication is available online before it is published in a journal. Checking will lead to a frequent reminder to update the publication status.",
            "description_de" => "Checkbox für eine Online Ahead Of Print-Markierung. Dieses Flag wird verwendet, um anzuzeigen, dass eine Publikation online verfügbar ist, bevor sie in einer Zeitschrift veröffentlicht wird. Das Ankreuzen führt zu einer gelegentlichen Erinnerung, den Publikationsstatus zu überprüfen.",
            "width" => 12,
            "tags" => ['publication', 'important']
        ],
        "openaccess" => [
            "fields" => ["open_access" => true],
            "name" => "Open Access",
            "name_de" => "Open Access",
            "label" => null,
            "label_de" => null,
            "description" => "Checkbox for an open access flag. This flag is used to indicate that a publication is freely available online. Important: If the actual open access status is important, please use the 'openaccess-status' module.",
            "description_de" => "Checkbox für eine Open-Access-Markierung. Dieses Flag wird verwendet, um anzuzeigen, dass eine Publikation frei online verfügbar ist. Wichtig: Wenn der tatsächliche Open-Access-Status wichtig ist, verwenden Sie bitte das Modul 'openaccess-status'.",
            "width" => 12,
            "tags" => ['publication', 'important']
        ],
        "openaccess-status" => [
            "fields" => ["open_access" => true, "oa_status" => 'gold'],
            "name" => "Open Access Status",
            "name_de" => "Open Access Status",
            "label" => "Open Access Status",
            "label_de" => "Open Access Status",
            "description" => "A field for the open access status of a publication. Possible values are: gold, green, hybrid, bronze, closed.",
            "description_de" => "Ein Feld für den Open-Access-Status einer Publikation. Mögliche Werte sind: gold, green, hybrid, bronze, closed.",
            "width" => 6,
            "tags" => ['publication', 'important']
        ],
        "pages" => [
            "fields" => ["pages" => 'D1531-8'],
            "name" => "Pages",
            "name_de" => "Seiten",
            "label" => "Pages",
            "label_de" => "Seiten",
            "description" => "A field for the pages of a publication, e.g. for a journal.",
            "description_de" => "Ein Feld für die Seiten einer Publikation, z.B. für eine Zeitschrift.",
            "width" => 6,
            "tags" => ['publication', 'journal']
        ],
        "peer-reviewed" => [
            "fields" => ["peer-reviewed" => true],
            "name" => "Peer-Reviewed",
            "name_de" => "Peer-Reviewed",
            "label" => "Peer-Reviewed",
            "label_de" => "Peer-Reviewed",
            "description" => "Checkbox for a peer-reviewed flag. This flag is used to indicate that a publication has been peer-reviewed.",
            "description_de" => "Checkbox für eine Peer-Reviewed-Markierung. Dieses Flag wird verwendet, um anzuzeigen, dass eine Publikation peer-reviewed wurde.",
            "width" => 6,
            "tags" => ['publication', 'important']
        ],
        "person" => [
            "fields" => ["name" => "Koblitz, Julia", "affiliation" => "DSMZ", "academic_title" => "Dr."],
            "name" => "Person",
            "name_de" => "Person",
            "label" => "Name of the person (last name, given name)",
            "label_de" => "Name der Person (Nachname, Vorname)",
            "description" => "A fieldset for a person, including name, affiliation and academic title.",
            "description_de" => "Ein Feldset für eine Person, einschließlich Name, Affiliation und akademischem Titel.",
            "width" => 0,
            "tags" => ['people']
        ],
        "projects" => [
            "fields" => ["projects" => ['OSIRIS', 'CRIS2023']],
            "name" => "Projects",
            "name_de" => "Projekte",
            "label" => "Projects",
            "label_de" => "Projekte",
            "description" => "A field for a list of projects, that can be selected from a list of existing projects in the database.",
            "description_de" => "Ein Feld für eine Liste von Projekten, die aus einer Liste von bestehenden Projekten in der Datenbank ausgewählt werden können.",
            "width" => 12,
            "tags" => ['general', 'important']
        ],
        "pub-language" => [
            "fields" => ["pub_language" => 'de'],
            "name" => "Publication Language",
            "name_de" => "Publikationssprache",
            "label" => "Publication Language",
            "label_de" => "Publikationssprache",
            "description" => "A field for the language of a publication, values defined by vocabulary. Possible values are: de, en, fr, es, it, other.",
            "description_de" => "Ein Feld für die Sprache einer Publikation, Werte werden über das Vokabular definiert. Mögliche Werte sind: de, en, fr, es, it, other.",
            "width" => 6,
            "tags" => ['publication']
        ],
        "publisher" => [
            "fields" => ["publisher" => 'Oxford'],
            "name" => "Publisher",
            "name_de" => "Verlag",
            "label" => "Publisher",
            "label_de" => "Verlag",
            "description" => "A field for the publisher of a publication, e.g. for a book.",
            "description_de" => "Ein Feld für den Verlag einer Publikation, z.B. für ein Buch.",
            "width" => 6,
            "tags" => ['publication', 'journal']
        ],
        "pubmed" => [
            "fields" => ["pubmed" => 1234567],
            "name" => "Pubmed-ID",
            "name_de" => "Pubmed-ID",
            "label" => "Pubmed-ID",
            "label_de" => "Pubmed-ID",
            "description" => "A field for the PubMed ID of a publication. This field is used to uniquely identify a publication.",
            "description_de" => "Ein Feld für die PubMed-ID einer Publikation. Dieses Feld wird verwendet, um eine Publikation eindeutig zu identifizieren.",
            "width" => 6,
            "tags" => ['publication']
        ],
        "pubtype" => [
            "fields" => ["pubtype" => "article"],
            "name" => "Publication Type",
            "name_de" => "Publikationstyp",
            "label" => "Publication Type",
            "label_de" => "Publikationstyp",
            "description" => "A field for the publication type, e.g. for a journal article. Only to be used if the publication type is not defined by the subtype.",
            "description_de" => "Ein Feld für den Publikationstyp, z.B. für einen Zeitschriftenartikel. Nur zu nutzen, wenn der Publikationstyp nicht durch den Subtyp festgelegt wird.",
            "width" => 6,
            "tags" => ['publication']
        ],
        "review-type" => [
            "fields" => ["review-type" => "Begutachtung eines Forschungsantrages"],
            "name" => "Review Type",
            "name_de" => "Review-Art",
            "label" => "Type of review",
            "label_de" => "Art der Begutachtung",
            "description" => "A field for the type of review, e.g. for a grant.",
            "description_de" => "Ein Feld für die Art der Begutachtung, z.B. für ein Stipendium.",
            "width" => 6,
            "tags" => ['publication']
        ],
        "role" => [
            "fields" => ["role" => "Organisator:in"],
            "name" => "Role/Function",
            "name_de" => "Rolle/Funktion",
            "label" => "Role/Function",
            "label_de" => "Rolle/Funktion",
            "description" => "A field for the role or function of a person, e.g. for an event.",
            "description_de" => "Ein Feld für die Rolle oder Funktion einer Person, z.B. für eine Veranstaltung.",
            "width" => 6,
            "tags" => ['people', 'event']
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
            "label" => "Scientist",
            "label_de" => "Wissenschaftler:in",
            "description" => "A selection field for an author from the institute. Limited to the persons in the database and supports only one author.",
            "description_de" => "Ein Auswahlfeld für einen Autor aus dem Institut. Beschränkt auf die Personen in der Datenbank und unterstützt nur einen Autor.",
            "width" => 6,
            "tags" => ['authors', 'important']
        ],
        "semester-select" => [
            "fields" => [],
            "name" => "Semester Selector",
            "name_de" => "Semesterauswahl",
            "label" => "Select a semester",
            "label_de" => "Wähle ein Semester",
            "description" => "A field for preselecting a semester, e.g. the next Summer Semester. If the user clicks on a semester, the date range is automatically adjusted.",
            "description_de" => "Ein Feld zur Vorauswahl eines Semesters, z.B. des nächsten Sommersemesters. Wenn ein:e Benutzer:in auf ein Semester klickt, wird der Zeitraum automatisch angepasst.",
            "width" => 6,
            "tags" => ['teaching', 'modificator']
        ],
        "scope" => [
            "fields" => ["scope" => "national"],
            "name" => "Scope",
            "name_de" => "Reichweite",
            "label" => "Scope",
            "label_de" => "Reichweite",
            "description" => "A field for the scope of an event, e.g. for a conference. Possible values are: local, regional, national, international.",
            "description_de" => "Ein Feld für den Geltungsbereich einer Veranstaltung, z.B. für eine Konferenz. Mögliche Werte sind: lokal, regional, national, international.",
            "width" => 6,
            "tags" => ['event']
        ],
        "software-link" => [
            "fields" => ["link" => "https://osiris-app.de"],
            "name" => "Link (Software)",
            "name_de" => "Link (Software)",
            "label" => "Complete link to the software/database",
            "label_de" => "Kompletter Link zur Software/Datenbank",
            "description" => "Synonym for a link used for software.",
            "description_de" => "Synonym für einen Link, der für Software verwendet wird.",
            "width" => 6,
            "tags" => ['software']
        ],
        "software-type" => [
            "fields" => ["software_type" => "Database"],
            "name" => "Type of Software",
            "name_de" => "Software-Type",
            "label" => "Type of software",
            "label_de" => "Art der Software",
            "description" => "A field for the type of software, possible vales are software, database, dataset, webtool, report.",
            "description_de" => "Ein Feld für den Typ der Software, mögliche Werte sind Software, Datenbank, Datensatz, Webtool, Bericht.",
            "width" => 6,
            "tags" => ['software']
        ],
        "software-venue" => [
            "fields" => ["software_venue" => "GitHub"],
            "name" => "Venue (Software)",
            "name_de" => "Veröffentlichungsort (Software)",
            "label" => "Place of Publication, e.g. GitHub, Zenodo ...",
            "label_de" => "Veröffentlichungsort, z.B. GitHub, Zenodo ...",
            "description" => "A field for the venue of a software, e.g. for a repository.",
            "description_de" => "Ein Feld für den Veröffentlichungsort einer Software, z.B. für ein Repository.",
            "width" => 6,
            "tags" => ['software']
        ],
        "status" => [
            "fields" => ["status" => 'completed'],
            "name" => "Status",
            "name_de" => "Status",
            "label" => "Status",
            "label_de" => "Status",
            "description" => "A field for the status of an activity, e.g. for a thesis supervision. Possible values are: in progress, completed, aborted.",
            "description_de" => "Ein Feld für den Status einer Aktivität, z.B. für eine Betreuung einer Abschlussarbeit. Mögliche Werte sind: in Bearbeitung, abgeschlossen, abgebrochen.",
            "width" => 12,
            "tags" => ['general', 'important']
        ],
        "student-category" => [
            "fields" => ["category" => 'doctoral thesis'],
            "name" => "Student Category",
            "name_de" => "Studierenden-Kategorie",
            "label" => "Category of a student",
            "label_de" => "Kategorie eines Studierenden",
            "description" => "A field for the category of a student, can be one of the following: bachelor thesis, master thesis, doctoral thesis, internship, other.",
            "description_de" => "Ein Feld für die Kategorie eines Studierenden, kann eine der folgenden sein: Bachelorarbeit, Masterarbeit, Doktorarbeit, Praktikum, Sonstiges.",
            "width" => 6,
            "tags" => ['people', 'publication']
        ],
        "tags" => [
            "fields" => ["tags" => ['OSIRIS', 'CRIS2023']],
            "name" => "Tags",
            "name_de" => "Schlagworte",
            "label" => "Tags",
            "label_de" => "Schlagworte",
            "description" => "A field for a list of tags, that can be selected from a list of existing tags in the database. These can be managed in the admin area.",
            "description_de" => "Ein Feld für eine Liste von Schlagworten, die aus einer Liste von bestehenden Schlagworten in der Datenbank ausgewählt werden können. Diese können im Admin-Bereich verwaltet werden.",
            "width" => 12,
            "tags" => ['general']
        ],
        "thesis" => [
            "fields" => ["category" => 'doctor'],
            "name" => "Thesis Category",
            "name_de" => "Abschlussarbeit-Kategorie",
            "label" => "Thesis type",
            "label_de" => "Art der Abschlussarbeit",
            "description" => "A field for the category of a thesis, values defined by vocabulary. Standard values are: bachelor, master, diploma, doctor, habilitation.",
            "description_de" => "Ein Feld für die Kategorie einer Abschlussarbeit, Werte werden über das Vokabular definiert. Standardwerte sind: Bachelor, Master, Diplom, Doktor, Habilitation.",
            "width" => 6,
            "tags" => ['publication', 'people']
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
            "name" => "Supervisor (with weekly hours)",
            "name_de" => "Betreuer:in (mit SWS)",
            "label" => "Supervisor",
            "label_de" => "Betreuende",
            "description" => "A comprehensive table for supervisors, with individual input fields for supervisors, including first an last name, position, username, an affiliation checkbox and semester week hours (sws). Supports multiple supervisors, drag-and-drop, and auto-suggest on username field. Similar to the 'author-table' module, but with SWS.",
            "description_de" => "Eine umfassende Tabelle für Betreuer:innen, mit individuellen Eingabefeldern für Betreuer:innen, einschließlich Vor- und Nachname, Position, Benutzername, einer Affiliation-Checkbox und Semesterwochenstunden (SWS). Unterstützt mehrere Betreuer:innen, Drag-and-Drop und Auto-Suggest im Benutzernamenfeld. Ähnlich dem Modul 'author-table', aber mit SWS.",
            "width" => 0,
            "tags" => ['authors', 'people']
        ],
        "supervisor-thesis" => [
            "fields" => ["authors"],
            "name" => "Supervisor (Thesis)",
            "name_de" => "Betreuer:in (Abschlussarbeit)",
            "label" => "Supervisor of the thesis",
            "label_de" => "Betreuende der Abschlussarbeit",
            "description" => "A comprehensive table for supervisors of a thesis, with individual input fields for supervisors, including first and last name, position, username, an affiliation checkbox and the role. Supports multiple supervisors, drag-and-drop, and auto-suggest on username field. Similar to the 'author-table' module, but for theses.",
            "description_de" => "Eine umfassende Tabelle für Betreuer:innen einer Abschlussarbeit, mit individuellen Eingabefeldern für Betreuer:innen, einschließlich Vor- und Nachname, Position, Benutzername, einer Affiliation-Checkbox und der Rolle. Unterstützt mehrere Betreuer:innen, Drag-and-Drop und Auto-Suggest im Benutzernamenfeld. Ähnlich dem Modul 'author-table', aber für Abschlussarbeiten.",
            "width" => 0,
            "tags" => ['authors', 'people', 'publication']
        ],
        "teaching-category" => [
            "fields" => ["category" => 'practical-lecture'],
            "name" => "Teaching Category",
            "name_de" => "Lehr-Kategorie",
            "label" => "Category of the teaching activity",
            "label_de" => "Kategorie der Lehrveranstaltung",
            "description" => "A field for the category of a teaching activity, e.g. for a lecture. Possible values are: lecture, practical-lecture, seminar, project, other.",
            "description_de" => "Ein Feld für die Kategorie einer Lehrveranstaltung, z.B. für eine Vorlesung. Mögliche Werte sind: Vorlesung, Praktikum, Seminar, Projekt, Sonstiges.",
            "width" => 6,
            "tags" => ['teaching']
        ],
        "teaching-course" => [
            "fields" => ["title" => "Einführung in die Forschungsinformation", "module" => null, "module_id" => null],
            "name" => "Course",
            "name_de" => "Modul",
            "label" => "Course for the following module",
            "label_de" => "Veranstaltung zu folgendem Modul",
            "description" => "A field for selecting a teaching course from the database. If the course does not exist yet, the user will be prompted to add a new one that will be then saved into the database.",
            "description_de" => "Ein Feld zum Auswählen eines Lehrmoduls aus der Datenbank. Wenn das Modul noch nicht existiert, wird der Benutzer aufgefordert, ein neues hinzuzufügen, das dann in die Datenbank gespeichert wird.",
            "width" => 12,
            "tags" => ['teaching']
        ],
        "title" => [
            "fields" => ["title" => 'OSIRIS - Open Source Research Information System'],
            "name" => "Title",
            "name_de" => "Titel",
            "label" => "Title / Topic / Description",
            "label_de" => "Titel / Thema / Beschreibung",
            "description" => "A field for the title of an activity, e.g. for a journal article. Always mandatory.",
            "description_de" => "Ein Feld für den Titel einer Aktivität, z.B. für einen Zeitschriftenartikel. Immer erforderlich.",
            "width" => 0,
            "tags" => ['general', 'important']
        ],
        "subtitle" => [
            "fields" => ["subtitle" => 'A subtitle for the activity, e.g. for a journal article.'],
            "name" => "Subtitle",
            "name_de" => "Untertitel",
            "label" => "Subtitle",
            "label_de" => "Untertitel",
            "description" => "A field for the subtitle of an activity.",
            "description_de" => "Ein Feld für den Untertitel einer Aktivität.",
            "width" => 12,
            "tags" => ['general']
        ],
        "university" => [
            "fields" => ["publisher" => 'Springer Nature'],
            "name" => "University",
            "name_de" => "Universität",
            "label" => "University",
            "label_de" => "Universität",
            "description" => "A field for the university of a publication, e.g. for a thesis.",
            "description_de" => "Ein Feld für die Universität einer Publikation, z.B. für eine Abschlussarbeit.",
            "width" => 6,
            "tags" => ['teaching', 'people']
        ],
        "version" => [
            "fields" => ["version" => OSIRIS_VERSION],
            "name" => "Version",
            "name_de" => "Version",
            "label" => "Version",
            "label_de" => "Version",
            "description" => "A field for the version of a software, e.g. for a release.",
            "description_de" => "Ein Feld für die Version einer Software, z.B. für ein Release.",
            "width" => 6,
            "tags" => ['software']
        ],
        "venue" => [
            "fields" => ["venue"],
            "name" => 'Place of publication',
            "name_de" => 'Veröffentlichungsort',
            "label" => 'Place of publication',
            "label_de" => 'Ort der Veröffentlichung',
            "description" => "A field for the publisher or place of publication, e.g. for a report.",
            "description_de" => "Ein Feld für den Herausgeber oder den Ort der Veröffentlichung, z.B. für einen Bericht.",
            "width" => 6,
            "tags" => ['publication', 'journal']
        ],
        "volume" => [
            "fields" => ["volume" => 51],
            "name" => "Volume",
            "name_de" => "Volume",
            "label" => "Volume",
            "label_de" => "Volume",
            "description" => "A field for the volume of a publication, e.g. for a journal.",
            "description_de" => "Ein Feld für den Band einer Publikation, z.B. für eine Zeitschrift.",
            "width" => 6,
            "tags" => ['publication', 'journal']
        ],
        "political_consultation" => [
            "fields" => ["political_consultation" => 'Gutachten'],
            "name" => "Political Consultation",
            "name_de" => "Politik- und Gesellschaftsberatung",
            "label" => "Contribution to political and social consulting",
            "label_de" => "Beitrag zur Politik- und Gesellschaftsberatung",
            "description" => "A field for the type of contribution to political and social consulting, e.g. for a report. Possible values are: Gutachten, Stellungnahme, Kommentar, Empfehlung.",
            "description_de" => "Ein Feld für die Art des Beitrags zur Politik- und Gesellschaftsberatung, z.B. für einen Bericht. Mögliche Werte sind: Gutachten, Stellungnahme, Kommentar, Empfehlung.",
            "width" => 6,
            "tags" => ['event', 'publication']
        ],
        "organization" => [
            "fields" => ["organization" => 'Technische Universität Braunschweig'],
            "name" => "Organisation",
            "name_de" => "Organisation",
            "label" => "Organisation",
            "label_de" => "Organisation",
            "description" => "A field for an organisation that is connected to an activity, e.g. a university or a company. This field is used to identify the organisation that is responsible for the activity.",
            "description_de" => "Ein Feld für eine Organisation, die mit einer Aktivität verbunden ist, z.B. eine Universität oder ein Unternehmen. Dieses Feld wird verwendet, um die Organisation zu identifizieren, die für die Aktivität verantwortlich ist.",
            "width" => 12,
            "tags" => ['organizations', 'people']
        ],
        "organizations" => [
            "fields" => ["organizations" => ['Technische Universität Braunschweig', 'Deutsche Forschungsgemeinschaft']],
            "name" => "Organisations",
            "name_de" => "Organisationen",
            "label" => "Organisations",
            "label_de" => "Organisationen",
            "description" => "A field for a list of organisations that are connected to an activity, e.g. a university or a company. This field is used to identify the organisations that are responsible for the activity.",
            "description_de" => "Ein Feld für eine Liste von Organisationen, die mit einer Aktivität verbunden sind, z.B. eine Universität oder ein Unternehmen. Dieses Feld wird verwendet, um die Organisationen zu identifizieren, die für die Aktivität verantwortlich sind.",
            "width" => 0,
            "tags" => ['organizations', 'people']
        ],
    );

    private $DB;

    function __construct($form = array(), $copy = false, $conference = false)
    {
        global $USER;
        $this->form = DB::doc2Arr($form);

        $this->DB = new DB;


        $this->user = $_SESSION['username'] ?? '';

        $this->copy = $copy ?? false;
        $this->preset = $form['authors'] ?? array();
        if (empty($form) && (empty($this->preset) || count($this->preset) === 0) && isset($USER['username']))
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

        if (!empty($form) && !empty($form['type']) && !empty($form['subtype'])) {
            $typeArr = $this->DB->db->adminTypes->findOne(['id' => $form['subtype']]);
            if (!empty($typeArr) && !empty($typeArr['fields'])) {
                $fields = DB::doc2Arr($typeArr['fields']);
                $fields = array_filter($fields, function ($f) {
                    return ($f['type'] ?? 'field') === 'field' || ($f['type'] ?? 'field') === 'custom';
                });
                $this->fields = array_column($fields, 'props', 'id');
            } elseif (!empty($typeArr) && !empty($typeArr['modules'])) {
                // collect fields from modules
                $fields = [];
                foreach ($typeArr['modules'] as $module) {
                    $req = false;
                    if (str_ends_with($module, '*')) {
                        $module = str_replace('*', '', $module);
                        $req = true;
                    }
                    $fields[$module] = [
                        'required' => $req
                    ];
                }
                $this->fields = $fields;
            }
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

    function getFields()
    {
        if (!empty($this->fields)) {
            return $this->fields;
        }
    }

    function get_name($module)
    {
        if (!empty($this->fields) && !empty($this->fields[$module]) && isset($this->fields[$module]['label'])) {
            return lang($this->fields[$module]['label'], $this->fields[$module]['label_de'] ?? null);
        }
        if (!isset($this->all_modules[$module]['label'])) {
            $field = $this->DB->db->adminFields->findOne(['id' => $module]);
            if (!empty($field)) return lang($field['name'], $field['name_de'] ?? $field['name']);
            return ucfirst($module);
        }
        return lang($this->all_modules[$module]['label'], $this->all_modules[$module]['label_de']);
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
            $this->print_module($module, false);
        }
    }

    function print_modules($modules)
    {
        foreach ($modules as $module) {
            $req = false;
            if (str_ends_with($module, '*')) {
                $module = str_replace('*', '', $module);
                $req = true;
            }
            $this->print_module($module, $req);
        }
    }

    function print_form($type)
    {
        $typeArr = $this->DB->db->adminTypes->findOne(['id' => $type]);
        if (!isset($typeArr)) {
            echo '<b>Type <code>' . $type . '</code> is not defined. </b>';
            return;
        }

        $fields = $typeArr['fields'] ?? [];
        if (empty($fields) && !empty($typeArr['modules'])) {
            $this->print_modules($typeArr['modules']);
            return;
        }
        foreach ($fields as $f) {
            $props = $f['props'] ?? [];
            switch ($f['type'] ?? 'field') {
                case 'field':
                    $this->print_module($f['id'], $props['required'] ?? false, $props);
                    break;
                case 'custom';
                    $this->custom_field($f['id'], $props['required'] ?? false, $props);
                    break;
                case 'heading':
                    echo '<div class="data-module col-sm-12 pb-0" data-module="heading">';
                    echo '<h5 class="m-0">' . lang($props['text'] ?? null, $props['text_de'] ?? null) . '</h5>';
                    echo '</div>';
                    break;
                case 'paragraph':
                    echo '<div class="data-module col-sm-12 py-0" data-module="paragraph">';
                    echo '<p class="m-0">' . lang($props['text'] ?? null, $props['text_de'] ?? null) . '</p>';
                    echo '</div>';
                    break;
                case 'hr':
                    echo '<div class="data-module col-sm-12 py-0" data-module="hr">';
                    echo '<hr class="my-5" />';
                    echo '</div>';
                    break;
                default:
                    # code...
                    break;
            }
        }
    }


    function custom_field($module, $req = false, $props = [])
    {
        $field = $this->DB->db->adminFields->findOne(['id' => $module]);
        if (!isset($field)) {
            echo '<b>Module <code>' . $module . '</code> is not defined. </b>';
            return;
        }
        $width = $field['width'] ?? 12;
        // $width = 12 / $width;
        $labelClass = ($req ? "required" : "");
        $label = lang($field['name'] ?? $field['id'], $field['name_de'] ?? null);
        $help = '';

        if (isset($props['label'])) {
            $label = lang($props['label'], $props['label_de'] ?? null);
        }
        if (isset($props['width']) && $props['width'] > 0 && $props['width'] <= 12) {
            $width = $props['width'];
        }
        if (isset($props['required']) && $props['required'] == true) {
            $labelClass = "required";
        }
        if (isset($props['help'])) {
            $help = lang($props['help'], $props['help_de'] ?? null);
            $labelClass .= " has-help";
        }

        if ($field['format'] == 'bool') {
            echo '<div class="data-module col-sm-' . $width . '" data-module="' . $module . '">';
            echo '<label for="' . $module . '" class="' . $labelClass . ' floating-title">' . $label . '</label>';

            $val = boolval($this->val($module, $field['default'] ?? ''));
            echo '<br>
                <div class="custom-radio d-inline-block">
                    <input type="radio" id="' . $module . '-true" value="true" name="values[' . $module . ']" ' . ($val == true ? 'checked' : '') . '>
                    <label for="' . $module . '-true">' . lang('Yes', 'Ja') . '</label>
                </div>
                <div class="custom-radio d-inline-block ml-20">
                    <input type="radio" id="' . $module . '-false" value="false" name="values[' . $module . ']" ' . ($val == false ? 'checked' : '') . '>
                    <label for="' . $module . '-false">' . lang('No', 'Nein') . '</label>
                </div>';
            echo $this->render_help($help);
            echo '</div>';
            return;
        } elseif ($field['format'] == 'bool-check') {
            echo '<div class="data-module col-sm-' . $width . '" data-module="' . $module . '">';
            echo '<input type="hidden" name="values[' . $module . ']" value="false">';
            echo '<div class="custom-checkbox">';
            echo '<input type="checkbox" id="' . $module . '" name="values[' . $module . ']" value="true" ' . ($this->val($module, $field['default'] ?? '') == 'true' ? 'checked' : '') . '>';
            echo '<label for="' . $module . '">' . $label . '</label>';
            echo '</div>';
            echo $this->render_help($help);
            echo '</div>';
            return;
        }
        $value = ($this->val($module, ''));
        if (!array_key_exists($module, $this->form) && isset($field['default']) && !empty($field['default'])) {
            $value = $field['default'];
        }

        if ($field['format'] == 'list' && ($field['multiple'] ?? false)) {
?>
            <div class="data-module col-sm-<?= $width ?>" data-module="<?= $module ?>">
                <label for="<?= $module ?>" class="<?= $labelClass ?> floating-title"><?= $label ?>

                    <?= $this->render_help($help) ?>
                </label>
                <select class="form-control" name="values[<?= $module ?>][]" id="<?= $module ?>" <?= $labelClass ?> multiple <?= $labelClass ?>>
                    <?php
                    if ($value instanceof MongoDB\Model\BSONArray) {
                        $value = DB::doc2Arr($value);
                    }
                    foreach ($field['values'] as $opt) {
                        // if is type MongoDB\Model\BSONArray, convert to array
                        if ($opt instanceof MongoDB\Model\BSONArray) $opt = DB::doc2Arr($opt);
                        $val = $opt;
                        if (is_array($opt)) {
                            $val = $opt[0];
                            $opt = lang(...$opt);
                        }
                        $selected = false;
                        if (is_array($value)) {
                            $selected = in_array($val, $value);
                        } else {
                            $selected = ($value == $val);
                        }
                    ?>
                        <option <?= ($selected ? 'selected' : '') ?> value="<?= $val ?>"><?= $opt ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <script>
                $('#<?= $module ?>').multiSelect({
                    // containerHTML: '<div class="btn-group">',
                    // menuHTML: '<div class="dropdown-menu">',
                    // buttonHTML: '<div class="form-control">',
                    // menuItemHTML: '<label>',
                    // noneText: '-- Choisir --',
                    // allText: 'Tout le monde',
                });
            </script>
            <?php
            return;
        }

        echo '<div class="data-module floating-form col-sm-' . $width . '" data-module="' . $module . '">';

        switch ($field['format']) {
            case 'string':
                // make sure that value is string
                if (!is_string($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
                echo '<input type="text" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . $value . '" placeholder="custom-field">';
                break;
            case 'text':
                echo '<textarea name="values[' . $module . ']" id="' . $module . '" cols="30" rows="5" class="form-control" placeholder="custom-field" ' . $labelClass . '>' . $value . '</textarea>';
                break;
            case 'int':
                echo '<input type="number" step="1" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . $value . '" placeholder="custom-field">';
                break;
            case 'float':
                echo '<input type="number" step="0.0001" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . $value . '" placeholder="custom-field">';
                break;
            case 'list':
                $multiple = $field['multiple'] ?? false;
                $name = 'values[' . $module . ']' . ($multiple ? '[]' : '');
                echo '<select class="form-control" name="' . $name . '" id="' . $module . '" ' . $labelClass . ' ' . ($multiple ? 'multiple' : '') . '>';
                if (!$req) {
                    echo '<option value="" ' . (empty($value) ? 'selected' : '') . '>-</option>';
                }
                if ($value instanceof MongoDB\Model\BSONArray) {
                    $value = DB::doc2Arr($value);
                }
                // keep track if any field was selected
                $any = empty($value) || $value == ($field['default'] ?? ''); // empty value is also a selection

                foreach ($field['values'] as $opt) {
                    // if is type MongoDB\Model\BSONArray, convert to array
                    if ($opt instanceof MongoDB\Model\BSONArray) $opt = DB::doc2Arr($opt);
                    $val = $opt;
                    if (is_array($opt)) {
                        $val = $opt[0];
                        $opt = lang(...$opt);
                    }
                    $selected = false;
                    if (is_array($value)) {
                        $selected = in_array($val, $value);
                    } else {
                        $selected = ($value == $val);
                    }
                    if ($selected) $any = true;
                    echo '<option ' . ($selected ? 'selected' : '') . ' value="' . $val . '">' . $opt . '</option>';
                }
                if ($field['others'] ?? false) {
                    // if nothing was selected but value is not empty, select others
                    echo '<option ' . (!$any ? 'selected' : '') . ' value="others">' . lang('Others (please specify)', 'Sonstiges (bitte angeben)') . ':</option>';
                    // echo '</select>';
            ?>
            <?php
                }
                echo '</select>';
                break;
            case 'date':
                echo '<input type="date" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . valueFromDateArray($value) . '" placeholder="custom-field">';
                break;
            case 'url':
                echo '<input type="url" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . $value . '" placeholder="custom-field">';
                break;
            default:
                echo '<input type="text" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . $value . '">';
                break;
        }

        echo '<label for="' . $module . '" class="' . $labelClass . '">' . $label . '</label>';
        echo $this->render_help($help);

        if ($field['format'] == 'list' && ($field['others'] ?? false)) {
            $any = $any ?? true;
            echo '<input type="text" class="other-input ' . ($any ? 'hidden" disabled' : '"') . ' name="values[' . $module . ']" id="' . $module . '-others" ' . $labelClass . ' value="' . $value . '">';
            ?>
            <script>
                $('#<?= $module ?>').on('change', function() {
                    if ($(this).val() == 'others') {
                        $('#<?= $module ?>-others').removeClass('hidden').attr('disabled', false);
                    } else {
                        $('#<?= $module ?>-others').addClass('hidden').attr('disabled', true);
                    }
                });
            </script>
            <style>
                .other-input {
                    color: var(--text-color);
                    background-color: white;
                    border: var(--border-width) solid var(--border-color);
                    border-radius: var(--border-radius);
                }

                .other-input::before {
                    content: '<?= lang('Other', 'Weiteres') ?>';
                    display: inline-block;
                }
            </style>
            <?php
        }
        echo '</div>';
    }

    function render_help(?string $text): string
    {
        if (!$text) return '';
        // $hid = 'help-'.preg_replace('/[^a-z0-9_-]+/i','-',$inputId);
        return '<div class="form-help" role="note">' . $text . '</div>';
    }

    function print_module($module, $req = false, $props = [])
    {
        if (!array_key_exists($module, $this->all_modules)) {
            return $this->custom_field($module, $req, $props);
        }
        $Vocabulary = new Vocabulary();

        $labelClass = ($req ? "required" : "");

        $m = $this->all_modules[$module] ?? [];
        $label = lang($m['label'], $m['label_de'] ?? $m['label']);

        $width = 12;
        if ($m['width'] ?? 6 > 0) {
            $width = $m['width'] ?? 2;
        }

        $help = '';

        if (isset($props['width']) && $props['width'] > 0 && $props['width'] <= 12) {
            $width = $props['width'];
        }
        if (isset($props['required']) && $props['required'] == true) {
            $labelClass = "required";
        }
        if (isset($props['label'])) {
            $label = lang($props['label'], $props['label_de'] ?? null);
        }
        if (isset($props['help'])) {
            $help = lang($props['help'], $props['help_de'] ?? null);
            $labelClass .= ' has-help';
        }

        switch ($module) {
            case 'gender':
                $val = $this->val('gender');
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="teaching-gender">
                    <select name="values[gender]" id="gender" class="form-control" <?= $labelClass ?>>
                        <option value="" <?= empty($val) ? 'selected' : '' ?>><?= lang('unknown', 'unbekannt') ?></option>
                        <option value="f" <?= $val == 'f' ? 'selected' : '' ?>><?= lang('female', 'weiblich') ?></option>
                        <option value="m" <?= $val == 'm' ? 'selected' : '' ?>><?= lang('male', 'männlich') ?></option>
                        <option value="d" <?= $val == 'd' ? 'selected' : '' ?>><?= lang('non-binary', 'divers') ?></option>
                        <option value="-" <?= $val == '-' ? 'selected' : '' ?>><?= lang('not specified', 'keine Angabe') ?></option>
                    </select>
                    <label for="gender" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;
            case 'nationality':
            case 'country':
                $val = $this->val('country');
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="country">
                    <select name="values[country]" id="country" class="form-control" <?= $labelClass ?>>
                        <option value="" <?= empty($val) ? 'selected' : '' ?>><?= lang('unknown', 'unbekannt') ?></option>
                        <?php foreach ($this->DB->getCountries(lang('name', 'name_de')) as $code => $country) { ?>
                            <option value="<?= $code ?>" <?= $val == $code ? 'selected' : '' ?>><?= $country ?></option>
                        <?php } ?>
                    </select>
                    <label for="country" class="<?= $labelClass ?> ">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;
            case 'countries':
                $countries = $this->val('countries', []);
            ?>

                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="countries">
                    <b>
                        <?= $label ?>
                    </b>
                    <div class="author-widget">
                        <div class="author-list p-10" id="country-list">
                            <?php
                            foreach ($countries as $k) { ?>
                                <div class='author'>
                                    <?= $this->DB->getCountry($k, lang('name', 'name_de')) ?>
                                    <input type='hidden' name='values[countries][]' value='<?= $k ?>'>
                                    <a onclick='$(this).parent().remove()'>&times;</a>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="footer">
                            <div class="input-group small d-inline-flex w-auto">
                                <select class="form-control" id="country-select">
                                    <option value="" disabled selected><?= lang("Add country ...", "Füge Land hinzu ...") ?></option>
                                    <?php foreach ($this->DB->getCountries(lang('name', 'name_de')) as $iso => $name) { ?>
                                        <option value="<?= $iso ?>"><?= $name ?></option>
                                    <?php } ?>
                                </select>
                                <div class="input-group-append">
                                    <button class="btn secondary h-full" type="button" onclick="addCountry(event);">
                                        <i class="ph ph-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        function addCountry(event) {
                            var el = $('#country-select')
                            var iso = el.val()
                            var name = el.find('option:selected').text()
                            if ((event.type == 'keypress' && event.keyCode == '13') || event.type == 'click') {
                                event.preventDefault();
                                if (iso) {
                                    var html = `<div class='author'>${name} <input type='hidden' name='values[countries][]' value='${iso}'> <a onclick='$(this).parent().remove()'>&times;</a></div>`;
                                    $('#country-list').append(html)
                                }
                                $(el).val('')
                                return false;
                            }
                        }
                    </script>

                </div>

            <?php
                break;
            case 'abstract':
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="abstract">
                    <textarea name="values[abstract]" id="abstract" cols="30" rows="5" class="form-control" placeholder="abstract"><?= $this->val('abstract') ?></textarea>
                    <label for="abstract" class="<?= $labelClass ?> "><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;
            case "title":
                $id = rand(1000, 9999);
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="title">
                    <div class="lang-<?= lang('en', 'de') ?>">
                        <label for="title" class="<?= $labelClass ?> floating-title">
                            <?= $label ?>
                        </label>

                        <div class="form-group title-editor" id="title-editor-<?= $id ?>"><?= $this->form['title'] ?? '' ?></div>
                        <input type="text" class="form-control hidden" name="values[title]" id="title" <?= $labelClass ?> value="<?= $this->val('title') ?>">
                    </div>
                    <?= $this->render_help($help) ?>
                </div>
                <script>
                    initQuill(document.getElementById('title-editor-<?= $id ?>'));
                </script>
            <?php
                break;

            case "subtitle":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="subtitle">
                    <input type="text" class="form-control" name="values[subtitle]" id="subtitle" <?= $labelClass ?> value="<?= $this->val('subtitle') ?>" placeholder="subtitle">
                    <label for="subtitle" class="<?= $labelClass ?>">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "pubtype":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="pubtype">
                    <select class="form-control" name="values[pubtype]" id="pubtype" <?= $labelClass ?>>
                        <option value="article">Journal article (refereed)</option>
                        <option value="book"><?= lang('Book', 'Buch') ?></option>
                        <option value="chapter"><?= lang('Book chapter', 'Buchkapitel') ?></option>
                        <option value="preprint">Preprint (non refereed)</option>
                        <option value="conference"><?= lang('Conference preceedings', 'Konferenzbeitrag') ?></option>
                        <option value="magazine"><?= lang('Magazine article (non refereed)', 'Magazin-Artikel (non-refereed)') ?></option>
                        <option value="dissertation"><?= lang('Thesis') ?></option>
                        <option value="others"><?= lang('Others', 'Weiteres') ?></option>
                    </select>
                    <label for="pubtype" class="<?= $labelClass ?> floating-title">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "teaching-course":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="teaching-course">
                    <label for="teaching" class="floating-title <?= $labelClass ?>">
                        <?= $label ?>
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

                        <input type="hidden" class="form-control hidden" name="values[module]" value="<?= $this->val('module') ?>" id="module" <?= $labelClass ?> readonly>
                        <input type="hidden" class="form-control hidden" name="values[module_id]" value="<?= $this->val('module_id') ?>" id="module_id" <?= $labelClass ?> readonly>
                    </a>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;
            case "author-table":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="author-table">
                    <label for="authors" class="<?= $labelClass ?> floating-title"><?= $label ?></label>
                    <div class="module p-0">
                        <table class="table simple small">
                            <thead>
                                <tr>
                                    <th><label for="user">Username</label></th>
                                    <th><label for="last" class="required"><?= lang('Last name', 'Nachname') ?></label></th>
                                    <th><label for="first" class="required"><?= lang('First name', 'Vorname') ?></label></th>
                                    <th><label for="position"><?= lang('Position', 'Position') ?></label></th>
                                    <th><label for="aoi"><?= lang('Affiliated', 'Affiliiert') ?></label></th>
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
                        <?= $this->render_help($help) ?>
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

                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "supervisor":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="supervisor">
                    <label for="supervisor" class="<?= $labelClass ?> floating-title"><?= $label ?></label>

                    <?php if (!$req) { ?>
                        <input type="hidden" name="values[authors]" value="">
                    <?php } ?>

                    <div class="module p-0">
                        <table class="table simple small">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th><?= lang('Last name', 'Nachname') ?></th>
                                    <th><?= lang('First name', 'Vorname') ?></th>
                                    <th><?= lang('Affiliated', 'Affiliiert') ?></th>
                                    <th><?= lang('SWS', 'Anteil in SWS') ?> <span class="text-danger">*</span></th>
                                    <th>
                                        <a href="#sws-calc" class="btn link"><i class="ph ph-calculator"></i></a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="supervisors">
                                <?php
                                $i = 0;
                                foreach ($this->preset ?? [] as $i => $author) { ?>
                                    <tr>
                                        <td>
                                            <input name="values[authors][<?= $i ?>][user]" type="text" class="form-control" list="user-list" value="<?= $author['user'] ?>" onchange="selectUsernameSupervisor(this)">
                                        </td>
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
                                        <div class="d-flex justify-content-between">
                                            <button class="btn text-secondary" type="button" onclick="addSupervisorRow()"><i class="ph ph-plus"></i></button>
                                            <small class="text-muted float-left align-items-center">
                                                <?= lang('Selecting a user name will fill in the first and last name fields automatically.', 'Die Auswahl eines Benutzernamens füllt die Felder für Vor- und Nachname automatisch aus.') ?>
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <?= $this->render_help($help) ?>
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

                        function selectUsernameSupervisor(el) {
                            let username = el.value
                            let user = $('#user-list option[value=' + username + ']')
                            if (!user || user === undefined || user.length === 0) return;

                            console.log(user);
                            let name = user.html()
                            name = name.replace(/\(.+\)/, ''); // remove username in brackets
                            name = name.split(', ')
                            if (name.length !== 2) return;

                            let tr = $(el).closest('tr')
                            console.log(tr);
                            tr.find('td:nth-child(2) input').val(name[0])
                            tr.find('td:nth-child(3) input').val(name[1])
                            tr.find('td:nth-child(4) input').prop('checked', true)
                        }

                        var counter = <?= $i ?>;

                        function addSupervisorRow() {
                            counter++;
                            var tr = $('<tr>')
                            tr.append('<td> <input name="values[authors][' + counter + '][user]" type="text" class="form-control" list="user-list" onchange="selectUsernameSupervisor(this)"> </td>')
                            tr.append('<td><input name="values[authors][' + counter + '][last]" type="text" class="form-control" required></td>')
                            tr.append('<td><input name="values[authors][' + counter + '][first]" type="text" class="form-control"></td>')
                            tr.append('<td><div class="custom-checkbox"><input type="checkbox" id="checkbox-' + counter + '" name="values[authors][' + counter + '][aoi]" value="1"><label for="checkbox-' + counter + '" class="blank"></label></div></td>')
                            tr.append('<td><input type="number" step="0.1" class="form-control" name="values[authors][' + counter + '][sws]" id="teaching-sws" value="" required></td>')
                            var btn = $('<button class="btn text-danger" type="button">').html('<i class="ph ph-trash"></i>').on('click', function() {
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


            case "supervisor-thesis":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="supervisor-thesis">
                    <label for="supervisor" class="<?= $labelClass ?> floating-title"><?= $label ?></label>

                    <?php if (!$req) { ?>
                        <input type="hidden" name="values[authors]" value="">
                    <?php } ?>

                    <div class="module p-0">
                        <table class="table simple small">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th><?= lang('Last name', 'Nachname') ?></th>
                                    <th><?= lang('First name', 'Vorname') ?></th>
                                    <th><?= lang('Affiliated', 'Affiliiert') ?></th>
                                    <th><?= lang('Role', 'Rolle') ?> <span class="text-danger">*</span></th>
                                    <th>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="supervisors">
                                <?php
                                $i = 0;
                                foreach ($this->preset ?? [] as $i => $author) {
                                    $role = $author['role'] ?? 'supervisor';
                                ?>
                                    <tr>
                                        <td>
                                            <input name="values[authors][<?= $i ?>][user]" type="text" class="form-control" list="user-list-thesis" value="<?= $author['user'] ?>" onchange="selectUsernameSupervisor(this)">
                                        </td>
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
                                            <select name="values[authors][<?= $i ?>][role]" class="form-control">
                                                <option value="supervisor" <?= ($role == 'supervisor' ? 'selected' : '') ?>><?= lang('Supervisor', 'Betreuer') ?></option>
                                                <option value="first-reviewer" <?= ($role == 'first-reviewer' ? 'selected' : '') ?>><?= lang('First reviewer', 'Erster Gutachter') ?></option>
                                                <option value="second-reviewer" <?= ($role == 'second-reviewer' ? 'selected' : '') ?>><?= lang('Second reviewer', 'Zweiter Gutachter') ?></option>
                                                <option value="third-reviewer" <?= ($role == 'third-reviewer' ? 'selected' : '') ?>><?= lang('Third reviewer', 'Dritter Gutachter') ?></option>
                                                <option value="committee-member" <?= ($role == 'committee-member' ? 'selected' : '') ?>><?= lang('Committee member', 'Ausschussmitglied') ?></option>
                                                <option value="chair" <?= ($role == 'chair' ? 'selected' : '') ?>><?= lang('Chair', 'Vorsitzender') ?></option>
                                                <option value="mentor" <?= ($role == 'mentor' ? 'selected' : '') ?>><?= lang('Mentor', 'Mentor') ?></option>
                                                <option value="other" <?= ($role == 'other' ? 'selected' : '') ?>><?= lang('Other', 'Sonstiges') ?></option>
                                            </select>
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
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button class="btn text-secondary" type="button" onclick="addSupervisorRow()"><i class="ph ph-plus"></i></button>
                                            <small class="text-muted">
                                                <?= lang('Selecting a user name will fill in the first and last name fields automatically.', 'Die Auswahl eines Benutzernamens füllt die Felder für Vor- und Nachname automatisch aus.') ?>
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?= $this->render_help($help) ?>
                    <script>
                        function removeSupervisorRow(el) {
                            // check if row is the only one left
                            if ($(el).closest('tbody').find('tr').length > 1) {
                                $(el).closest('tr').remove()
                            } else {
                                toastError(lang('At least one supervisor is needed.', 'Mindestens ein Betreuer muss angegeben werden.'))
                            }
                        }

                        function selectUsernameSupervisor(el) {
                            let username = el.value
                            let user = $('#user-list-thesis option[value=' + username + ']')
                            if (!user || user === undefined || user.length === 0) return;

                            console.log(user);
                            let name = user.html()
                            name = name.replace(/\(.+\)/, ''); // remove username in brackets
                            name = name.split(', ')
                            if (name.length !== 2) return;

                            let tr = $(el).closest('tr')
                            console.log(tr);
                            tr.find('td:nth-child(2) input').val(name[0])
                            tr.find('td:nth-child(3) input').val(name[1])
                            tr.find('td:nth-child(4) input').prop('checked', true)
                        }

                        var counter = <?= $i ?>;

                        function addSupervisorRow() {
                            counter++;
                            var tr = $('<tr>')
                            tr.append('<td> <input name="values[authors][' + counter + '][user]" type="text" class="form-control" list="user-list-thesis" onchange="selectUsernameSupervisor(this)"> </td>')
                            tr.append('<td><input name="values[authors][' + counter + '][last]" type="text" class="form-control" required></td>')
                            tr.append('<td><input name="values[authors][' + counter + '][first]" type="text" class="form-control"></td>')
                            tr.append('<td><div class="custom-checkbox"><input type="checkbox" id="checkbox-' + counter + '" name="values[authors][' + counter + '][aoi]" value="1"><label for="checkbox-' + counter + '" class="blank"></label></div></td>')
                            var select = $('<select name="values[authors][' + counter + '][role]" class="form-control">');
                            var roles = {
                                'supervisor': lang('Supervisor', 'Betreuer'),
                                'first-reviewer': lang('First reviewer', 'Erster Gutachter'),
                                'second-reviewer': lang('Second reviewer', 'Zweiter Gutachter'),
                                'third-reviewer': lang('Third reviewer', 'Dritter Gutachter'),
                                'committee-member': lang('Committee member', 'Ausschussmitglied'),
                                'chair': lang('Chair', 'Vorsitzender'),
                                'mentor': lang('Mentor', 'Mentor'),
                                'other': lang('Other', 'Sonstiges')
                            }
                            for (const [key, value] of Object.entries(roles)) {
                                select.append('<option value="' + key + '">' + value + '</option>')
                            }
                            tr.append($('<td>').append(select))
                            var btn = $('<button class="btn text-danger" type="button">').html('<i class="ph ph-trash"></i>').on('click', function() {
                                $(this).closest('tr').remove();
                            });
                            tr.append($('<td>').append(btn))
                            $('#supervisors').append(tr)
                        }
                    </script>

                    <datalist id="user-list-thesis">
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
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="teaching-category">
                    <select name="values[category]" id="teaching-cat" class="form-control" <?= $labelClass ?>>
                        <option value="lecture" <?= $this->val('category') == 'lecture' ? 'selected' : '' ?>><?= lang('Lecture', 'Vorlesung') ?></option>
                        <option value="practical" <?= $this->val('category') == 'practical' ? 'selected' : '' ?>><?= lang('Practical course', 'Praktikum') ?></option>
                        <option value="practical-lecture" <?= $this->val('category') == 'practical-lecture' ? 'selected' : '' ?>><?= lang('Lecture and practical course', 'Vorlesung und Praktikum') ?></option>
                        <option value="practical-seminar" <?= $this->val('category') == 'practical-seminar' ? 'selected' : '' ?>><?= lang('Practical course and seminar', 'Praktikum und Seminar') ?></option>
                        <option value="lecture-seminar" <?= $this->val('category') == 'lecture-seminar' ? 'selected' : '' ?>><?= lang('Lecture and seminar', 'Vorlesung und Seminar') ?></option>
                        <option value="lecture-practical-seminar" <?= $this->val('category') == 'lecture-practical-seminar' ? 'selected' : '' ?>><?= lang('Lecture, seminar, practical course', 'Vorlesung, Seminar und Praktikum') ?></option>
                        <option value="seminar" <?= $this->val('category') == 'seminar' ? 'selected' : '' ?>><?= lang('Seminar') ?></option>
                        <option value="other" <?= $this->val('category') == 'other' ? 'selected' : '' ?>><?= lang('Other', 'Sonstiges') ?></option>
                    </select>
                    <label for="teaching-cat" class="<?= $labelClass ?> "><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "semester-select":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="semester-select">
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
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "event-select":
                $events = $this->DB->db->conferences->find(
                    ['end' => ['$lte' => date('Y-m-d', strtotime('+5 days'))]],
                    ['sort' => ['start' => -1], 'projection' => ['title' => 1, 'start' => 1, 'end' => 1, 'location' => 1, 'country' => 1]]
                    // ['sort' => ['start' => -1], 'limit' => 10]
                );
            ?>

                <div class="data-module col-sm-12 d-flex" data-module="event-select">
                    <!-- <label class="floating-title"></label> -->

                    <!-- dropdown with scroll and search, onclick -->
                    <div class="dropdown" id="event-select-dropdown">
                        <button id="event-select-button" class="btn primary" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                            <i class="ph ph-calendar-dots ph-2x mr-10"></i>
                            <span>
                                <b><?= lang('Select event', 'Veranstaltung auswählen') ?></b>
                                <br>
                                <small><?= lang('to fill date, event name and location fields automatically.', 'um automatisch Datum, Veranstaltungsname und Ort auszufüllen.') ?></small>
                            </span>
                            <i class="ph ph-caret-down ml-auto" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdown-1">
                            <small class="text-muted">
                                <i class="ph ph-info text-signal"></i>
                                <?= $help ?>
                            </small>
                            <input type="text" placeholder="<?= lang('Search event...', 'Veranstaltung suchen...') ?>" id="event-select-search" onkeyup="filterEvents();" class="form-control">

                            <div class="events-content">
                                <?php foreach ($events as $ev) { ?>
                                    <a onclick="selectEvent('<?= $ev['_id'] ?>', '<?= htmlspecialchars(addslashes($ev['title'])) ?>', '<?= $ev['start'] ?>', '<?= $ev['end'] ?>', '<?= htmlspecialchars(addslashes($ev['location'] ?? '')) ?>', '<?= $ev['country'] ?? '' ?>'); return false;">
                                        <strong><?= htmlspecialchars($ev['title']) ?></strong><br>
                                        <small class="text-muted">
                                            <?= date('d.m.Y', strtotime($ev['start'])) ?> - <?= date('d.m.Y', strtotime($ev['end'])) ?>
                                            <?php if (!empty($ev['location'])) { ?>
                                                | <?= htmlspecialchars($ev['location']) ?>
                                            <?php } ?>
                                        </small>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <a href="#add-event" class="btn" id="add-event-button" data-toggle="tooltip" data-title="<?= lang('Add new event', 'Neue Veranstaltung hinzufügen') ?>">
                        <i class="ph ph-calendar-plus ph-2x"></i>
                    </a>
                    <style>
                        #add-event-button {
                            margin-left: 10px;
                            height: auto;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                        }


                        #event-select-button {
                            width: 100%;
                            text-align: left;
                            height: auto;
                            line-height: 1.4;
                            padding: .5rem 1rem;
                            display: flex;
                            justify-content: flex-start;
                            align-items: center;
                        }

                        #event-select-dropdown {
                            position: relative;
                            display: inline-block;
                            width: 100%;
                        }

                        #event-select-dropdown .dropdown-menu {
                            width: 100%;
                            max-height: 300px;
                            overflow-y: auto;
                            padding: 0.5rem;
                        }

                        #event-select-dropdown .dropdown-menu a {
                            display: block;
                            padding: 0.5rem;
                            text-decoration: none;
                            color: var(--text-color);
                            border-bottom: 1px solid var(--border-color);
                        }

                        #event-select-dropdown .dropdown-menu a:hover {
                            background-color: var(--hover-bg-color);
                        }

                        #event-select-dropdown .dropdown-menu .events-content {
                            max-height: 250px;
                            overflow-y: auto;
                        }
                    </style>
                    <script>
                        function filterEvents() {
                            var input, filter, div, a, i, txtValue;
                            input = document.getElementById("myInput");
                            filter = input.value.toUpperCase();
                            div = document.querySelector("#event-select-dropdown .events-content");
                            a = div.getElementsByTagName("a");
                            for (i = 0; i < a.length; i++) {
                                txtValue = a[i].textContent || a[i].innerText;
                                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                                    a[i].style.display = "";
                                } else {
                                    a[i].style.display = "none";
                                }
                            }
                        }
                    </script>
                </div>
            <?php
                break;

            case "authors":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="authors">
                    <a class="float-right" href="#author-help"><i class="ph ph-question" style="line-height:0;"></i> <?= lang('Help', 'Hilfe') ?></a>
                    <label for="author" class="floating-title <?= $labelClass ?>">
                        <?= $label ?>
                        <small class="text-muted"><?= lang('(in correct order, format: Last name, First name)', '(in korrekter Reihenfolge, Format: Nachname, Vorname)') ?></small>
                    </label>

                    <?php if (!$req) { ?>
                        <input type="hidden" name="values[authors]" value="">
                    <?php } ?>

                    <div class="author-widget" id="author-widget">
                        <div class="author-list p-10" id="author-list">
                            <?= $this->authors ?>
                        </div>
                        <div class="footer">

                            <div class="input-group small d-inline-flex w-auto">
                                <input type="text" placeholder="<?= lang('Add person ...', 'Füge Person hinzu ...') ?>" onkeypress="addAuthor(event);" id="add-author" list="scientist-list">
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

                        <?= $this->render_help($help) ?>
                    </div>
                    <small class="text-muted">
                        <?= lang('Note: A detailed person editor is available after adding the activity.', 'Anmerkung: Ein detaillierter Personeneditor ist verfügbar, nachdem der Datensatz hinzugefügt wurde.') ?>
                    </small>
                    <div class="alert signal my-20 affiliation-warning" style="display: none;">
                        <h5 class="title">
                            <?= lang("Attention: No affiliated persons added.", 'Achtung: Keine affiliierten Personen angegeben.') ?>
                        </h5>
                        <?= lang(
                            'Please double click on every affiliated person in the list above, to mark them as affiliated. Only affiliated persons will receive points and are shown in reports.',
                            'Bitte doppelklicken Sie auf jede affiliierte Person in der Liste oben, um sie als zugehörig zu markieren. Nur zugehörige Personen erhalten Punkte und werden in Berichten berücksichtigt.'
                        ) ?>
                    </div>
                </div>
            <?php
                break;

            case "person":
            ?>
                <h6 class="col-12 m-0 floating-title <?= $labelClass ?>">
                    <?= lang('Details about the person', 'Angaben zu der Person') ?>
                </h6>
                <div class="data-module col-sm-<?= $width ?> row" data-module="person">
                    <div class="col-sm-5 floating-form">
                        <input type="text" class="form-control" name="values[name]" id="guest-name" <?= $labelClass ?> value="<?= $this->val('name') ?>" placeholder="name" autocomplete="off">
                        <label for="guest-name" class="<?= $labelClass ?> element-other">
                            <?= $label ?>
                        </label>
                    </div>
                    <div class="col-sm-5 floating-form">
                        <input type="text" class="form-control" name="values[affiliation]" id="guest-affiliation" <?= $labelClass ?> value="<?= $this->val('affiliation') ?>" placeholder="affiliation">
                        <label for="guest-affiliation" class="<?= $labelClass ?> element-other"><?= lang('Affiliation (Name, City, Country)', 'Einrichtung (Name, Ort, Land)') ?></label>
                    </div>
                    <div class="col-sm-2 floating-form">
                        <input type="text" class="form-control" name="values[academic_title]" id="guest-academic_title" value="<?= $this->val('academic_title') ?>" placeholder="academic_title">
                        <label for="guest-academic_title"><?= lang('Academ. title', 'Akadem. Titel') ?></label>
                    </div>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "student-category":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="student-category">
                    <select name="values[category]" id="category-students" class="form-control" <?= $labelClass ?>>
                        <option value="doctoral student" <?= $this->val('category') == 'doctoral thesis' ? 'selected' : '' ?>><?= lang('Doctoral Student', 'Doktorand:in') ?></option>
                        <option value="master student" <?= $this->val('category') == 'master thesis' ? 'selected' : '' ?>><?= lang('Master Student', 'Masterstudent') ?></option>
                        <option value="bachelor student" <?= $this->val('category') == 'bachelor thesis' ? 'selected' : '' ?>><?= lang('Bachelor Student', 'Bachelorstudent') ?></option>
                        <option value="intern" <?= $this->val('category') == 'internship' ? 'selected' : '' ?>><?= lang('Intern', 'Praktikant') ?></option>
                        <option value="other" <?= $this->val('category') == 'other' ? 'selected' : '' ?>><?= lang('Other', 'Sonstiges') ?></option>
                    </select>
                    <label for="category-students" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "thesis":
                $val = $this->val('thesis') ?? '';
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="thesis">
                    <select name="values[thesis]" id="thesis" class="form-control" <?= $labelClass ?>>
                        <?php
                        $vocab = $Vocabulary->getValues('thesis');
                        foreach ($vocab as $v) { ?>
                            <option value="<?= $v['id'] ?>" <?= $v['id'] == $val ? 'selected' : '' ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                        <?php } ?>
                    </select>
                    <label for="thesis" class="<?= $labelClass ?> "><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;
            case "pub-language":
                $val = $this->val('pub-language') ?? '';
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="pub-language">
                    <select name="values[pub-language]" id="pub-language" class="form-control" <?= $labelClass ?>>
                        <?php if (!$req) { ?>
                            <option value="" <?= $val == '' ? 'selected' : '' ?>><?= lang('Select language', 'Sprache auswählen') ?></option>
                        <?php } ?>

                        <?php
                        $vocab = $Vocabulary->getValues('pub-language');
                        foreach ($vocab as $v) { ?>
                            <option value="<?= $v['id'] ?>" <?= $v['id'] == $val ? 'selected' : '' ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                        <?php } ?>
                    </select>
                    <label for="pub-language" class="<?= $labelClass ?> "><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "status":
                $status = $this->val('status') ?? 'preparation';
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="status" style="align-self: center;">
                    <label for="status" class="<?= $labelClass ?> floating-title"><?= $label ?></label>
                    <div id="end-question">
                        <div class="custom-radio d-inline-block">
                            <input type="radio" name="values[status]" id="status-preparation" value="preparation" value="1" <?= $status == 'preparation' ? 'checked' : '' ?>>
                            <label for="status-preparation"><?= lang('In preparation', 'In Vorbereitung') ?></label>
                        </div>

                        <div class="custom-radio d-inline-block">
                            <input type="radio" name="values[status]" id="status-in-progress" value="in progress" value="1" <?= $status == 'in progress' ? 'checked' : '' ?>>
                            <label for="status-in-progress"><?= lang('In progress', 'In Progress') ?></label>
                        </div>

                        <div class="custom-radio d-inline-block">
                            <input type="radio" name="values[status]" id="status-completed" value="completed" value="1" <?= $status == 'completed' ? 'checked' : '' ?>>
                            <label for="status-completed"><?= lang('Completed', 'Abgeschlossen') ?></label>
                        </div>

                        <div class="custom-radio d-inline-block">
                            <input type="radio" name="values[status]" id="status-aborted" value="aborted" value="1" <?= $status == 'aborted' ? 'checked' : '' ?>>
                            <label for="status-aborted"><?= lang('Aborted', 'Abgebrochen') ?></label>
                        </div>
                    </div>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "guest":
            case "guest-category":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="guest">
                    <select name="values[category]" id="category-guest" class="form-control" <?= $labelClass ?>>
                        <option value="guest scientist" <?= $this->val('category') == 'guest scientist' ? 'selected' : '' ?>><?= lang('Guest Scientist', 'Gastwissenschaftler:in') ?></option>
                        <option value="lecture internship" <?= $this->val('category') == 'lecture internship' ? 'selected' : '' ?>><?= lang('Lecture Internship', 'Pflichtpraktikum im Rahmen des Studium') ?></option>
                        <option value="student internship" <?= $this->val('category') == 'student internship' ? 'selected' : '' ?>><?= lang('Student Internship', 'Schülerpraktikum') ?></option>
                        <option value="other" <?= $this->val('category') == 'other' ? 'selected' : '' ?>><?= lang('Other', 'Sonstiges') ?></option>
                    </select>
                    <label for="category-guest" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "details":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="details">
                    <input type="text" class="form-control" name="values[details]" id="details" <?= $labelClass ?> value="<?= $this->val('details') ?>" placeholder="details">
                    <label for="details" class="<?= $labelClass ?>">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "date":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?> row" data-module="date">
                    <div class="col-sm floating-form">
                        <input type="number" min="1901" max="2155" step="1" class="form-control" name="values[year]" id="year" <?= $labelClass ?> value="<?= $this->val('year') ?>" placeholder="2024">
                        <label for="year" class="<?= $labelClass ?> element-time"><?= lang('Year', 'Jahr') ?></label>
                    </div>
                    <div class="col-sm floating-form">
                        <input type="number" min="1" max="12" step="1" class="form-control" name="values[month]" id="month" <?= $labelClass ?> value="<?= $this->val('month') ?>" placeholder="12">
                        <label for="month" class="<?= $labelClass ?> element-time"><?= lang('Month', 'Monat') ?></label>
                    </div>
                    <div class="col-sm floating-form">
                        <input type="number" min="1" max="31" step="1" class="form-control" name="values[day]" id="day" value="<?= $this->val('day') ?>" placeholder="24">
                        <label for="day" class="element-time"><?= lang('Day', 'Tag') ?></label>
                        <?= $this->render_help($help) ?>
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
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="lecture-type">
                    <select name="values[lecture_type]" id="lecture_type" class="form-control" autocomplete="off">
                        <option value="short" <?= $this->val('lecture_type') == 'short' ? 'selected' : '' ?>><?= lang('short', 'kurz') ?> (5-15 min.)</option>
                        <option value="medium" <?= $this->val('lecture_type') == 'medium' ? 'selected' : '' ?>><?= lang('medium', 'mittel') ?> (15-30 min.)</option>
                        <option value="long" <?= $this->val('lecture_type') == 'long' ? 'selected' : '' ?>><?= lang('long', 'lang') ?> (> 30 min.)</option>
                        <option value="repetition" <?= $this->val('lecture_type') == 'repetition' || $this->copy === true ? 'selected' : '' ?>><?= lang('repetition', 'Wiederholung') ?></option>
                    </select>
                    <label class="<?= $labelClass ?> " for="lecture_type"><?= lang('Type of lecture', 'Art des Vortrages') ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "lecture-invited":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="lecture-invited">
                    <select name="values[invited_lecture]" id="invited_lecture" class="form-control" autocomplete="off" <?= $labelClass ?>>
                        <option value="0" <?= $this->val('invited_lecture', false) ? '' : 'selected' ?>><?= lang('No', 'Nein') ?></option>
                        <option value="1" <?= $this->val('invited_lecture', false) ? 'selected' : '' ?>><?= lang('Yes', 'Ja') ?></option>
                    </select>
                    <label class="<?= $labelClass ?>" for="lecture_type"><?= lang('Invited lecture') ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "date-range":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="date-range">
                    <label class="<?= $labelClass ?> floating-title" for="date_start">
                        <?= $label ?>
                        <span data-toggle="tooltip" data-title="<?= lang('Leave end date empty if only one day', 'Ende leer lassen, falls es nur ein Tag ist') ?>"><i class="ph ph-question" style="line-height:0;"></i></span>
                        <!-- <button class="btn small" id="daterange-toggle-btn" type="button" onclick="rebuild_datepicker(this);"><?= lang('Multiple days', 'Mehrtägig') ?></button> -->
                    </label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="values[start]" id="date_start" <?= $labelClass ?> value="<?= valueFromDateArray($this->val('start')) ?>">
                        <input type="date" class="form-control" name="values[end]" id="date_end" value="<?= valueFromDateArray($this->val('end')) ?>">
                    </div>
                    <?= $this->render_help($help) ?>
                </div>
                <script>
                    // make sure the end date is always after the start date
                    document.getElementById('date_start').addEventListener('blur', function() {
                        var startDate = new Date(this.value);
                        var endDate = new Date(document.getElementById('date_end').value);
                        if (endDate < startDate) {
                            document.getElementById('date_end').value = this.value;
                            toastWarning(lang('End date cannot be before start date. Setting end date to start date.', 'Enddatum kann nicht vor Startdatum liegen. Setze Enddatum auf Startdatum.'));
                        }
                    });
                    document.getElementById('date_end').addEventListener('blur', function() {
                        var startDate = new Date(document.getElementById('date_start').value);
                        var endDate = new Date(this.value);
                        if (endDate < startDate) {
                            this.value = document.getElementById('date_start').value;
                            toastWarning(lang('End date cannot be before start date. Setting end date to start date.', 'Enddatum kann nicht vor Startdatum liegen. Setze Enddatum auf Startdatum.'));
                        }
                    });
                </script>
            <?php
                break;

            case "date-range-ongoing":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="date-range-ongoing">
                    <label class="<?= $labelClass ?> element-time floating-title" for="date_start">
                        <?= $label ?>
                        <span data-toggle="tooltip" data-title="<?= lang('Leave end date empty ongoing activity', 'Ende leer lassen, falls es eine zurzeit laufende Aktivität ist') ?>"><i class="ph ph-question"></i></span>
                    </label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="values[start]" id="date_start" <?= $labelClass ?> value="<?= valueFromDateArray($this->val('start')) ?>">
                        <input type="date" class="form-control" name="values[end]" id="date_end" value="<?= valueFromDateArray($this->val('end')) ?>">
                    </div>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;
            case "software-venue":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="software-venue">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[software_venue]" id="software_venue" value="<?= $this->val('software_venue') ?>" placeholder="software_venue">
                    <label class="<?= $labelClass ?>" for="software_venue"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "venue":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="software-venue">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[venue]" id="venue" value="<?= $this->val('venue') ?>" placeholder="venue">
                    <label class="<?= $labelClass ?>" for="venue"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;
            case "software-link":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="software-link">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[link]" id="software_link" value="<?= $this->val('link') ?>" placeholder="link">
                    <label class="element-link <?= $labelClass ?>" for="software_link"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "version":
            ?>
                <div class="data-module floating-form col-sm-2" data-module="version">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[version]" id="software_version" value="<?= $this->val('version') ?>" placeholder="version">
                    <label class="<?= $labelClass ?>" for="software_version"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "software-type":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="software-type">
                    <select name="values[software_type]" id="software_type" class="form-control" <?= $labelClass ?>>
                        <option value="" <?= empty($this->val('software_type')) ? 'selected' : '' ?>>Not specified</option>
                        <option value="software" <?= $this->val('software_type') == 'software' ? 'selected' : '' ?>>Computer Software</option>
                        <option value="database" <?= $this->val('software_type') == 'database' ? 'selected' : '' ?>>Database</option>
                        <option value="dataset" <?= $this->val('software_type') == 'dataset' ? 'selected' : '' ?>>Dataset</option>
                        <option value="webtool" <?= $this->val('software_type') == 'webtool' ? 'selected' : '' ?>>Website</option>
                        <option value="report" <?= $this->val('software_type') == 'report' ? 'selected' : '' ?>>Report</option>
                    </select>
                    <label class="<?= $labelClass ?>" for="software_type"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "iteration":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="misc">
                    <select name="values[iteration]" id="iteration" class="form-control" <?= $labelClass ?> value="<?= $this->val('iteration') ?>">
                        <option value="once"><?= lang('once', 'einmalig') ?></option>
                        <option value="annual"><?= lang('continously', 'stetig') ?></option>
                    </select>
                    <label class="<?= $labelClass ?>" for="iteration"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "conference":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="conference">
                    <input type="hidden" class="hidden" name="values[conference_id]" id="conference_id" value="<?= $this->val('conference_id', null) ?>">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[conference]" id="conference" list="conference-list" placeholder="VAAM 2022" value="<?= $this->val('conference') ?>" oninput="resetConference()">
                    <label for="conference" class="<?= $labelClass ?>"><?= $label ?></label>
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
                    <script>
                        function resetConference() {
                            // check if conference from datalist is selected
                            var input = $('#conference').val()
                            var option = $('#conference-list option').filter(function() {
                                return $(this).val() === input;
                            })
                            if (option.length) {
                                var id = option.data('id')
                                $('#conference_id').val(id)
                                $('#connected-conference').html(lang('Connected to ', 'Verknüpft mit ') + input)
                                return
                            }
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
                    <?= $this->render_help($help) ?>
                </div>

                <datalist id="conference-list">
                    <?php
                    foreach ($this->DB->db->conferences->find([]) as $c) { ?>
                        <option data-id="<?= $c['_id'] ?>"><?= $c['title'] ?></option>
                    <?php } ?>
                </datalist>
            <?php
                break;

            case "location":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="location">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[location]" id="location" placeholder="Berlin, Germany" value="<?= $this->val('location') ?>" placeholder="location">
                    <label for="location" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "issn":
                $issn = $this->val('issn');
                if (is_array($issn)) {
                    $issn = implode(', ', DB::doc2Arr($issn));
                }
                if ($issn instanceof MongoDB\BSON\BSONArray || is_object($issn)) {
                    $issn = implode(', ', DB::doc2Arr($issn));
                }
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="issn">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[issn]" id="issn" value="<?= $issn ?>" placeholder="issn">
                    <label for="issn" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "journal":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="journal">
                    <a href="<?= ROOTPATH ?>/docs/add-activities#das-journal-bearbeiten" target="_blank" class="<?= $labelClass ?> float-right">
                        <i class="ph ph-question"></i> <?= lang('Help', 'Hilfe') ?>
                    </a>
                    <label for="journal" class="floating-title <?= $labelClass ?>"><?= $label ?></label>
                    <a href="#journal-select" id="journal-field" class="module">
                        <span class="float-right text-secondary"><i class="ph ph-edit"></i></span>

                        <div id="selected-journal">
                            <?php if (!empty($this->form) && isset($this->form['journal_id'])) :
                                $journal = $this->DB->getConnected('journal', $this->form['journal_id']);
                            ?>
                                <h5 class="m-0"><?= $journal['journal'] ?></h5>
                                <span class="float-right text-muted"><?= $journal['publisher'] ?></span>
                                <span class="text-muted">ISSN: <?= print_list($journal['issn']) ?></span>
                            <?php else : ?>
                                <span class="font-weight-bold"><?= lang('Not selected', 'Nichts ausgewählt') ?></span>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" class="form-control hidden" name="values[journal]" value="<?= $this->val('journal') ?>" id="journal" list="journal-list" <?= $labelClass ?> readonly>
                        <input type="hidden" class="form-control hidden" name="values[journal_id]" value="<?= $this->val('journal_id') ?>" id="journal_id" <?= $labelClass ?> readonly>

                    </a>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "magazine":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="magazine">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[magazine]" value="<?= $this->val('magazine') ?>" id="magazine" placeholder="magazine" list="magazine-list">
                    <label for="magazine" class=" <?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
                <datalist id="magazine-list">
                    <?php
                    foreach ($this->DB->db->activities->distinct('magazine') as $m) { ?>
                        <option><?= $m ?></option>
                    <?php } ?>
                </datalist>
            <?php
                break;

            case "link":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="link">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[link]" value="<?= $this->val('link') ?>" id="link" placeholder="link">
                    <label for="link" class="element-link <?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "book-title":
            ?>
                <div class="data-module floating-form col-6" data-module="book-title">
                    <input type="text" class="form-control" name="values[book]" value="<?= $this->val('book') ?>" id="book" <?= $labelClass ?> placeholder="book-title">
                    <label for="book" class="<?= $labelClass ?> "><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "book-series":
            ?>
                <div class="data-module floating-form col-6" data-module="book-series">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[series]" value="<?= $this->val('series') ?>" id="series" placeholder="series">
                    <label for="series" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "edition":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="edition">
                    <input type="number" class="form-control" <?= $labelClass ?> name="values[edition]" value="<?= $this->val('edition') ?>" id="edition" placeholder="edition">
                    <label for="edition" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "issue":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="issue">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[issue]" value="<?= $this->val('issue') ?>" id="issue" placeholder="issue">
                    <label for="issue" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "volume":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="volume">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[volume]" value="<?= $this->val('volume') ?>" id="volume" placeholder="volume">
                    <label for="volume" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "pages":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="pages">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[pages]" value="<?= $this->val('pages') ?>" id="pages" placeholder="pages">
                    <label for="pages" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;


            case "peer-reviewed":
            ?>
                <div class="data-module col-sm-<?= $width ?> d-flex" data-module="pages" style="gap: 2rem;">
                    <label for="peer_reviewed" class="<?= $labelClass ?> floating-title"><?= $label ?></label>
                    <div class="custom-radio" id="peer_reviewed-div">
                        <input type="radio" id="peer_reviewed" value="true" name="values[peer_reviewed]" <?= $this->val('peer_reviewed', true) ? 'checked' : '' ?>>
                        <label for="peer_reviewed"><i class="ph ph-user-circle-check text-success"></i> <?= lang('Yes', 'Ja') ?></label>
                    </div>
                    <div class="custom-radio" id="peer_reviewed-div">
                        <input type="radio" id="peer_reviewed-0" value="false" name="values[peer_reviewed]" <?= $this->val('peer_reviewed', true) ? '' : 'checked' ?>>
                        <label for="peer_reviewed-0"><i class="ph ph-user-circle-dashed text-danger"></i> <?= lang('No', 'Nein') ?></label>
                    </div>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "publisher":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="publisher">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[publisher]" value="<?= $this->val('publisher') ?>" id="publisher" placeholder="publisher">
                    <label for="publisher" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "university":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="university">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[publisher]" value="<?= $this->val('publisher') ?>" id="publisher" placeholder="publisher">
                    <label for="publisher" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "city":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="city">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[city]" value="<?= $this->val('city') ?>" id="city" placeholder="city">
                    <label for="city" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "editor":
            case "editors":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="editor">
                    <label for="editor" class="<?= $labelClass ?> floating-title">
                        <?= $label ?>
                        <small class="text-muted"><?= lang('(in correct order, format: Last name, First name)', '(in korrekter Reihenfolge, Format: Nachname, Vorname)') ?></small>
                    </label>

                    <?php if (!$req) { ?>
                        <input type="hidden" name="values[editors]" value="">
                    <?php } ?>

                    <div class="author-widget" id="editor-widget">
                        <div class="author-list p-10" id="editor-list">
                            <?= $this->editors ?>
                        </div>
                        <div class="footer">
                            <div class="input-group small d-inline-flex w-auto">
                                <input type="text" placeholder="<?= lang('Add person ...', 'Füge Person hinzu ...') ?>" onkeypress="addAuthor(event, true);" id="add-editor" list="scientist-list">
                                <div class="input-group-append">
                                    <button class="btn secondary h-full" type="button" onclick="addAuthor(event, true);">
                                        <i class="ph ph-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "doi":
            ?>
                <?php if (empty($this->form)) { ?>
                    <div class="data-module floating-form col-sm-<?= $width ?>" data-module="doi">
                        <input type="text" class="form-control" <?= $labelClass ?> name="values[doi]" value="<?= $this->val('doi') ?>" id="doi" placeholder="doi">
                        <label for="doi" class="element-link <?= $labelClass ?>"><?= $label ?></label>
                        <?= $this->render_help($help) ?>
                    </div>
                <?php } else { ?>
                    <div class="data-module col-sm-<?= $width ?>" data-module="doi">
                        <label for="doi" class="floating-title <?= $labelClass ?>"><?= $label ?></label>

                        <div class="input-group ">
                            <input type="text" class="form-control" <?= $labelClass ?> name="values[doi]" value="<?= $this->val('doi') ?>" id="doi" placeholder="doi">
                            <div class="input-group-append" data-toggle="tooltip" data-title="<?= lang('Retreive updated information via DOI', 'Aktualisiere die Daten via DOI') ?>">
                                <button class="btn" type="button" onclick="getPubData(event, this)"><i class="ph ph-arrows-clockwise"></i></button>
                                <span class="sr-only">
                                    <?= lang('Retreive updated information via DOI', 'Aktualisiere die bibliographischen Daten via DOI') ?>
                                </span>
                            </div>
                        </div>
                        <?= $this->render_help($help) ?>
                    </div>
                <?php } ?>
            <?php
                break;

            case "pubmed":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="pubmed">
                    <input type="number" class="form-control" <?= $labelClass ?> name="values[pubmed]" value="<?= $this->val('pubmed') ?>" id="pubmed" placeholder="pubmed">
                    <label for="pubmed" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "isbn":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="isbn">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[isbn]" value="<?= $this->val('isbn') ?>" id="isbn" placeholder="isbn">
                    <label for="isbn" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "doctype":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="doctype">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[doc_type]" value="<?= $this->val('doc_type') ?>" id="doctype" placeholder="Report" placeholder="doc_type">
                    <label for="doc_type" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "openaccess":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="openaccess">
                    <div class="custom-radio d-inline-block" id="open_access-div">
                        <input type="radio" id="open_access-0" value="false" name="values[open_access]" <?= $this->val('open_access', false) ? '' : 'checked' ?>>
                        <label for="open_access-0"><i class="icon-closed-access text-danger"></i> Closed access</label>
                    </div>
                    <div class="custom-radio d-inline-block ml-20" id="open_access-div">
                        <input type="radio" id="open_access" value="true" name="values[open_access]" <?= $this->val('open_access', false) ? 'checked' : '' ?>>
                        <label for="open_access"><i class="icon-open-access text-success"></i> Open access</label>
                    </div>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "openaccess-status":
                $status = $this->val('oa_status', false);
                if (!$status) $status = $this->val('open_access', false) ? 'open' : 'closed';
            ?>
                <!-- oa_status -->
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="openaccess-status">
                    <select class="form-control" id="oa_status" name="values[oa_status]" <?= $labelClass ?> autocomplete="off">
                        <option value="closed" <?= $status == 'closed' ? 'selected' : '' ?>>Closed Access</option>
                        <option value="open" <?= $status == 'open' ? 'selected' : '' ?>>Open Access (<?= lang('unknown status', 'Unbekannter Status') ?>)</option>
                        <option value="diamond" <?= $status == 'diamond' ? 'selected' : '' ?>>Open Access (Diamond)</option>
                        <option value="gold" <?= $status == 'gold' ? 'selected' : '' ?>>Open Access (Gold)</option>
                        <option value="green" <?= $status == 'green' ? 'selected' : '' ?>>Open Access (Green)</option>
                        <option value="hybrid" <?= $status == 'hybrid' ? 'selected' : '' ?>>Open Access (Hybrid)</option>
                        <option value="bronze" <?= $status == 'bronze' ? 'selected' : '' ?>>Open Access (Bronze)</option>
                    </select>
                    <label for="oa_status" class="<?= $labelClass ?>"><?= $label ?></label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "online-ahead-of-print":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="online-ahead-of-print">
                    <input type="hidden" name="values[epub]" value="0">
                    <div class="custom-checkbox <?= isset($_GET['epub']) ? 'text-danger' : '' ?>" id="epub-div">
                        <input type="checkbox" id="epub" value="1" name="values[epub]" <?= (!isset($_GET['epub']) && $this->val('epub', false)) ? 'checked' : '' ?>>
                        <label for="epub"><?= $label ?></label>
                    </div>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "correction":
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="correction">
                    <input type="hidden" name="values[correction]" value="0">
                    <div class="custom-checkbox" id="correction-div">
                        <input type="checkbox" id="correction" value="1" name="values[correction]" <?= $this->val('correction', false) ? 'checked' : '' ?>>
                        <label for="correction"><?= $label ?></label>
                    </div>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "scientist":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="scientist">
                    <select class="form-control" id="username" name="values[user]" <?= $labelClass ?> autocomplete="off">
                        <?php
                        foreach ($this->userlist as $j) { ?>
                            <option value="<?= $j['username'] ?>" <?= $j['username'] == ($this->form['user'] ?? $this->user) ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                        <?php } ?>
                    </select>
                    <label class="<?= $labelClass ?> element-author" for="username">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "scope":
                $scope = $this->val('scope', false);
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="scope">
                    <select class="form-control" id="scope" name="values[scope]" <?= $labelClass ?> autocomplete="off">
                        <option <?= $scope == 'local' ? 'selected' : '' ?>>local</option>
                        <option <?= $scope == 'regional' ? 'selected' : '' ?>>regional</option>
                        <option <?= $scope == 'national' ? 'selected' : '' ?>>national</option>
                        <option <?= $scope == 'international' ? 'selected' : '' ?>>international</option>
                    </select>
                    <label class="<?= $labelClass ?>" for="scope">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "role":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="role">
                    <input type="text" class="form-control" id="role" value="<?= $this->val('role') ?>" name="values[role]" <?= $labelClass ?> placeholder="role">
                    <label class="<?= $labelClass ?>" for="role">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "license":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="license">
                    <input type="text" class="form-control" id="license" value="<?= $this->val('license') ?>" name="values[license]" <?= $labelClass ?> placeholder="license">
                    <label class="<?= $labelClass ?>" for="license">
                        <?= $label ?>
                    </label>

                    <small class="help-text">
                        <?= lang('If applicable, enter', 'Falls möglich, die') ?>
                        <a href="https://opensource.org/licenses/" target="_blank" rel="noopener noreferrer"><?= lang('SPDX-ID from', 'SPDX-ID der') ?> OSI</a>
                        <?= lang('or CC license from', 'oder die CC-Lizenz von') ?>
                        <a href="https://creativecommons.org/share-your-work/cclicenses/" target="_blank" rel="noopener noreferrer">Creative Commons</a>.
                        <?= lang('', 'angeben') ?>.
                    </small>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "review-type":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="review-type">
                    <input type="text" class="form-control" id="review-type" value="<?= $this->val('review-type', 'Begutachtung eines Forschungsantrages') ?>" name="values[review-type]" <?= $labelClass ?> placeholder="review-type">
                    <label class=" <?= $labelClass ?>" for="review-type">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "editorial":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="editorial">
                    <input type="text" class="form-control" <?= $labelClass ?> name="values[editor_type]" id="editor_type" value="<?= $this->val('editor_type') ?>" placeholder="Guest Editor for Research Topic 'XY'" placeholder="editor_type">
                    <label for="editor_type" class=" <?= $labelClass ?>">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "political_consultation":
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="political_consultation">
                    <select type="text" class="form-control" <?= $labelClass ?> name="values[political_consultation]" id="political_consultation">
                        <!-- Gutachten,Positionspapier,Studie,Sonstiges -->
                        <?php if (!$labelClass) { ?>
                            <option value="" <?= empty($this->val('political_consultation')) ? 'selected' : '' ?>><?= lang('No political consultation', 'Keine politische Beratung') ?></option>
                        <?php } ?>
                        <option value="Gutachten" <?= $this->val('political_consultation') == 'Gutachten' ? 'selected' : '' ?>><?= lang('Review', 'Gutachten') ?></option>
                        <option value="Positionspapier" <?= $this->val('political_consultation') == 'Positionspapier' ? 'selected' : '' ?>><?= lang('Position paper', 'Positionspapier') ?></option>
                        <option value="Studie" <?= $this->val('political_consultation') == 'Studie' ? 'selected' : '' ?>><?= lang('Study', 'Studie') ?></option>
                        <option value="Sonstiges" <?= $this->val('political_consultation') == 'Sonstiges' ? 'selected' : '' ?>><?= lang('Other', 'Sonstiges') ?></option>
                    </select>

                    <label for="political_consultation" class="<?= $labelClass ?>">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "organization":
                $org_id = $this->val('organization', null);
                $rand_id = rand(1000, 9999);
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="organization">
                    <label for="organization" class="floating-title <?= $labelClass ?>">
                        <?= $label ?>
                    </label>
                    <a id="organization" class="module" href="#organization-modal-<?= $rand_id ?>">
                        <i class="ph ph-edit float-right"></i>
                        <input hidden readonly name="values[organization]" value="<?= $org_id ?>" <?= $labelClass ?> readonly id="org-<?= $rand_id ?>-organization" <?= $labelClass ?> />
                        <span class="text-danger mr-10 float-right" data-toggle="tooltip" data-title="<?= lang('Remove connected organization', 'Verknüpfte Organisation entfernen') ?>">
                            <i class="ph ph-trash" onclick="$('#org-<?= $rand_id ?>-organization').val(''); $('#org-<?= $rand_id ?>-value').html('<?= lang('No organization connected', 'Keine Organisation verknüpft') ?>'); return false;"></i>
                        </span>
                        <input hidden readonly name="values[organization]" value="<?= $org_id ?>" <?= $labelClass ?> readonly id="org-<?= $rand_id ?>-organization" />

                        <div id="org-<?= $rand_id ?>-value">
                            <?php if (empty($org_id) || !DB::is_ObjectID($org_id)) { ?>

                                <?= lang('No organization selected', 'Keine Organisation ausgewählt') ?>
                                <?php if (!empty($org_id)) { ?>
                                    <br><small class="text-muted"><?= $org_id ?></small>
                                <?php } ?>
                                <?php } else {
                                $org_id = DB::to_ObjectID($org_id);
                                $collab = $this->DB->db->organizations->findOne(['_id' => $org_id]);
                                if (!empty($collab)) { ?>
                                    <b><?= $collab['name'] ?></b>
                                    <br><small class="text-muted"><?= $collab['location'] ?></small>
                                <?php } else { ?>
                                    <?= lang('No organization selected', 'Keine Organisation ausgewählt') ?>:
                                    <br><small class="text-muted"><?= $org_id ?></small>
                            <?php }
                            } ?>
                        </div>
                    </a>

                    <div class="modal" id="organization-modal-<?= $rand_id ?>" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a href="#close-modal" class="close" role="button" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <label for="org-<?= $rand_id ?>-search"><?= lang('Search organization', 'Suche nach Organisation') ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="org-<?= $rand_id ?>-search" onkeydown="selectOrgEvent(event, '<?= $rand_id ?>')" placeholder="<?= lang('Search for an organization', 'Suche nach einer Organisation') ?>" autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn" type="button" onclick="selectOrgEvent(null, '<?= $rand_id ?>')"><i class="ph ph-magnifying-glass"></i></button>
                                    </div>
                                </div>
                                <p id="org-<?= $rand_id ?>-search-comment"></p>
                                <table class="table simple">
                                    <tbody id="org-<?= $rand_id ?>-suggest">
                                    </tbody>
                                </table>
                                <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>
                            </div>
                        </div>
                    </div>

                    <?= $this->render_help($help) ?>
                </div>

            <?php
                break;

            case "organizations":
                $organizations = $this->val('organizations', []);
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="organizations">
                    <label for="organization" class="floating-title <?= $labelClass ?>">
                        <?= $label ?>
                    </label>
                    <!-- for empty save -->
                    <input type="hidden" name="values[organizations]" value="">
                    <table class="table">
                        <tbody id="collaborators">
                            <?php
                            foreach ($organizations as $org_id) {
                                $collab = $this->DB->db->organizations->findOne(['_id' => DB::to_ObjectID($org_id)]);
                                // if (empty($collab)) continue;
                            ?>
                                <tr data-row="<?= $org_id ?>">
                                    <td>
                                        <?= $collab['name'] ?? $org_id ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= $collab['location'] ?? null ?>
                                        </small>
                                        <input type="hidden" name="values[organizations][]" value="<?= $org_id ?>" class="form-control">
                                    </td>
                                    <td class="w-50"><button type="button" class="btn danger remove-collab" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">
                                    <label for="organization-search"><?= lang('Add Organisation', 'Organisation hinzufügen') ?></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="organization-search" onkeydown="handleKeyDown(event)" placeholder="<?= lang('Search for an organization', 'Suche nach einer Organisation') ?>" autocomplete="off">
                                        <div class="input-group-append">
                                            <button class="btn" type="button" onclick="getOrganization($('#organization-search').val())"><i class="ph ph-magnifying-glass"></i></button>
                                        </div>
                                    </div>
                                    <p id="search-comment"></p>
                                    <table class="table simple mb-0">
                                        <tbody id="organization-suggest">
                                        </tbody>
                                    </table>
                                    <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>
                                    <script>
                                        $(document).ready(function() {
                                            SUGGEST = $('#organization-suggest')
                                            INPUT = $('#organization-search')
                                            SELECTED = $('#collaborators')
                                            COMMENT = $('#search-comment')
                                            USE_RADIO = false;
                                            DATAFIELD = 'organizations'
                                        });

                                        function handleKeyDown(event) {
                                            if (event.key === 'Enter') {
                                                event.preventDefault();
                                                getOrganization($('#organization-search').val());
                                            }
                                        }
                                    </script>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case "projects":
                $projects = $this->val('projects', []);
                $projects = DB::doc2Arr($projects);
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="projects">
                    <label for="project" class="floating-title <?= $labelClass ?>"><?= $label ?></label>
                    <?php
                    global $Settings;
                    $full_permission = $Settings->hasPermission('projects.edit') || $Settings->hasPermission('projects.connect');
                    $filter = [];
                    if (!$full_permission) {
                        // make sure to include currently selected projects
                        $filter = ['$or' => [['persons.user' => $_SESSION['username']], ['_id' => ['$in' => $projects ?? []]]]];
                    }
                    $project_list = $this->DB->db->projects->find($filter, [
                        'projection' => ['_id' => 1, 'name' => 1, 'title' => 1, 'title_de' => 1, 'internal_number' => 1],
                        'sort' => ['name' => 1]
                    ])->toArray();
                    ?>

                    <!-- make sure that empty projects are saved as well -->
                    <input type="hidden" name="values[projects]" value="">
                    <table class="table">
                        <tbody id="project-list"><?php
                                                    foreach ($projects ?? [] as $i => $con) {
                                                        if (empty($con)) continue;
                                                        if (is_string($con))
                                                            $con = DB::to_ObjectID($con);
                                                        $p = $this->DB->db->projects->findOne(['_id' => $con]);
                                                        if (empty($p)) continue;
                                                    ?>
                                <tr id="project-<?= $con ?>">
                                    <td class="w-full">
                                        <input type="hidden" name="values[projects][]" value="<?= $p['_id'] ?>">
                                        <b><?= $p['name'] ?></b>
                                        <br>
                                        <span class="text-muted">
                                            <?= $p['title'] ?? '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">
                                    <b>
                                        <?= lang('Connect a project', 'Verknüpfe ein Projekt') ?>:
                                    </b>
                                    <div class="input-group">
                                        <select id="project-select" class="form-control" placeholder="<?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?>">
                                            <option value=""><?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?></option>
                                            <?php
                                            foreach ($project_list as $s) { ?>
                                                <option value="<?= $s['_id'] ?>"><?= $s['name'] ?>: <?= lang($s['title'], $s['title_de'] ?? null) ?> <?= isset($s['internal_number']) ? ('(ID ' . $s['internal_number'] . ')') : '' ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <?php if ($full_permission) { ?>
                                        <small class="text-muted">
                                            <i class="ph ph-info"></i>
                                            <?= lang('Note: only projects are shown here. You cannot connect proposals.', 'Bemerkung: nur Projekte werden hier gezeigt. Du kannst keine Anträge verknüpfen.') ?>
                                        </small>
                                    <?php } else { ?>
                                        <small class="text-muted">
                                            <i class="ph ph-info"></i>
                                            <?= lang('Note: only your own projects are shown here. You cannot connect proposals.', 'Bemerkung: nur deine eigenen Projekte werden hier gezeigt. Du kannst keine Anträge verknüpfen.') ?>
                                        </small>
                                    <?php } ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>


                    <script>
                        function addProjectRow(projectId = null, projectName = null) {
                            const row = $('<tr>')
                            console.log(projectId);
                            if (!projectId) projectId = $('#project-select').val();
                            if (!projectName) projectName = $('#project-select option:selected').text();

                            if (!projectId) {
                                alert('<?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?>');
                                return;
                            }
                            // check if project already exists
                            if ($('#project-list').find(`#project-${projectId}`).length > 0) {
                                // toastError('<?= lang('This project is already connected', 'Dieses Projekt ist bereits verbunden') ?>');
                                return;
                            }
                            row.append(`<td class="w-full">
                                <input type="hidden" name="values[projects][]" value="${projectId}">
                                <b>${projectName}</b>
                                </td>
                                `);
                            row.append(`<td>
                                <button class="btn danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                            </td>`);
                            row.attr('id', `project-${projectId}`);
                            $('#project-list').append(row)

                            // reset selectize
                            var control = $('#project-select')[0].selectize;
                            control.clear();
                        }

                        $("#project-select").selectize({
                            onChange: function(value) {
                                if (!value.length) return;
                                addProjectRow(value, $("#project-select option[value='" + value + "']").text());
                            }
                        });
                    </script>

                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;

            case 'tags':
                $Settings = new Settings;
                if (!$Settings->featureEnabled('tags')) break;
                $all_tags = $Settings->get('tags') ?? [];
                $tags = DB::doc2Arr($this->val('tags', []));
            ?>
                <div class="data-module col-sm-<?= $width ?>" data-module="tags">
                    <label for="tag-select" class="floating-title <?= $labelClass ?>">
                        <?= $label ?>
                    </label>
                    <select class="form-control" name="values[tags][]" id="tag-select" multiple <?= $labelClass ?> autocomplete="off">
                        <?php
                        foreach ($all_tags as $val) {
                            $selected = in_array($val, $tags);
                        ?>
                            <option <?= ($selected ? 'selected' : '') ?> value="<?= $val ?>"><?= $val ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <script>
                    $('#tag-select').multiSelect({
                        noneText: '<?= lang('No tags selected', 'Keine Tags ausgewählt') ?>',
                        allText: '<?= lang('All tags', 'Alle Tags') ?>',
                    });
                </script>
            <?php
                break;

            case 'funding_type': // funding_type
                $val = $this->val('funding_type', null);
            ?>
                <div class="data-module floating-form col-sm-<?= $width ?>" data-module="funding_type">
                    <select class="form-control" name="values[funding_type]" id="funding_type" <?= $labelClass ?> autocomplete="off">
                        <?php
                        $vocab = $Vocabulary->getValues('funding-type');
                        foreach ($vocab as $v) { ?>
                            <option value="<?= $v['id'] ?>" <?= $v['id'] == $val ? 'selected' : '' ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                        <?php } ?>
                    </select>
                    <label for="funding_type" class=" <?= $labelClass ?>">
                        <?= $label ?>
                    </label>
                    <?= $this->render_help($help) ?>
                </div>
            <?php
                break;


            default:
            ?>
                <div class="data-module alert danger col-sm-<?= $width ?>">
                    <?= lang('Module ' . $module . ' is not defined.', 'Modul ' . $module . ' existiert nicht.') ?>
                </div>
<?php
                break;
        }
    }
}
