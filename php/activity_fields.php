<?php
require_once "fields.php";

class ActivityFields extends Fields
{

    function __construct()
    {
        parent::__construct();
        $Settings = new Settings();
        $DB = new DB();
        $osiris = $DB->db;
        $types = $osiris->adminCategories->find()->toArray();
        $types = array_column($types, lang('name', 'name_de'), 'id');

        $subtypes = $osiris->adminTypes->find()->toArray();
        $subtypes = array_column($subtypes, lang('name', 'name_de'), 'id');


        $adminCategories = $osiris->adminCategories->find()->toArray();
        $typeModules = [];
        foreach ($adminCategories as $m) {
            $modules = $osiris->adminTypes->distinct('modules', ['parent' => $m['id']]);
            // merge all 'modules' keys
            $modules = array_map(fn($m) => str_replace('*', '', $m), $modules);
            $modules = array_unique($modules);
            foreach ($modules as $module) {
                if (!isset($typeModules[$module])) $typeModules[$module] = [];
                if (!in_array($m['id'], $typeModules[$module])) $typeModules[$module][] = $m['id'];
            }
        }

        $typeModules = array_merge($typeModules, [
            'print' => ['general'],
            'web' => ['general'],
            'icon' => ['general'],
            'type' => ['general'],
            'subtype' => ['general'],
            'title' => ['general'],
            'authors' => ['general'],
            'year' => ['general'],
            'month' => ['general'],
            'start_date' => ['general'],
            'end_date' => ['general'],
            'created' => ['general'],
            'created_by' => ['general'],
            'updated' => ['general'],
            'updated_by' => ['general'],
            'imported' => ['general'],
            'imported_by' => ['general'],
            'topics' => ['general'],
            'affiliated' => ['general'],
            'affiliated_positions' => ['general'],
            'cooperative' => ['general'],
        ]);

        $FIELDS = [
            [
                'id' => 'id',
                'module_of' => ['general'],
                'label' => lang('ID', 'ID'),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
            ],
            [
                'id' => 'print',
                'module_of' => $typeModules['print'] ?? [],
                'usage' => [
                    'columns',
                    'filter'
                ],
                'label' => lang('Print version', 'Printdarstellung'),
                'type' => 'string'
            ],
            [
                'id' => 'web',
                'module_of' => $typeModules['web'] ?? [],
                'usage' => [
                    'columns'
                ],
                'label' => lang('Web version', 'Webdarstellung'),
                'type' => 'string'
            ],
            [
                'id' => 'icon',
                'module_of' => $typeModules['icon'] ?? [],
                'usage' => [
                    'columns'
                ],
                'label' => lang('Icon', 'Icon'),
                'type' => 'string'
            ],
            [
                'id' => 'type',
                'module_of' => $typeModules['type'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Category', 'Kategorie'),
                'type' => 'string',
                'input' => 'select',
                'values' => $types
            ],
            [
                'id' => 'subtype',
                'module_of' => $typeModules['subtype'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Type', 'Typ'),
                'type' => 'string',
                'input' => 'select',
                'values' => $subtypes
            ],
            [
                'id' => 'title',
                'module_of' => $typeModules['title'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Title', 'Titel'),
                'type' => 'string'
            ],
            [
                'id' => 'start_date',
                'module_of' => $typeModules['start_date'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Start date', 'Startdatum'),
                'type' => 'datetime',
                'input' => 'date',
            ],
            [
                'id' => 'end_date',
                'module_of' => $typeModules['end_date'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('End date', 'Enddatum'),
                'type' => 'datetime',
                'input' => 'date',
            ],
            [
                'id' => 'abstract',
                'module_of' => $typeModules['abstract'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Abstract', 'Abstract'),
                'type' => 'string'
            ],
            [
                'id' => 'authors',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'columns'
                ],
                'label' => lang('Authors', 'Autoren'),
                'type' => 'list',
            ],
            [
                'id' => 'authors.first',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Author (first name)', 'Autor (Vorname)'),
                'type' => 'string'
            ],
            [
                'id' => 'authors.last',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Author (last name)', 'Autor (Nachname)'),
                'type' => 'string'
            ],
            [
                'id' => 'authors.user',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Author (username)', 'Autor (Username)'),
                'type' => 'string'
            ],
            [
                'id' => 'authors.position',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Author (position)', 'Autor (Position)'),
                'type' => 'string',
                'input' => 'select',
                'values' => ['first', 'middle', 'last', 'corresponding']
            ],
            [
                'id' => 'authors.approved',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [],
                'label' => lang('Author (approved)', 'Autor (Bestätigt)'),
                'type' => 'boolean',
            ],
            [
                'id' => 'authors.aoi',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Author (affiliated)', 'Autor (Affiliated)'),
                'type' => 'boolean',
            ],
            [
                'id' => 'authors.units',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Author (unit)', 'Autor (Einheit)'),
                'type' => 'string'
            ],
            [
                'id' => 'supervisors',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'columns'
                ],
                'label' => lang('Supervisors', 'Betreuende'),
                'type' => 'list',
            ],
            [
                'id' => 'supervisors.first',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Supervisor (first name)', 'Betreuende (Vorname)'),
                'type' => 'string'
            ],
            [
                'id' => 'supervisors.last',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Supervisor (last name)', 'Betreuende (Nachname)'),
                'type' => 'string'
            ],
            [
                'id' => 'supervisors.user',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Supervisor (username)', 'Betreuende (Username)'),
                'type' => 'string'
            ],
            [
                'id' => 'supervisors.approved',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [],
                'label' => lang('Supervisor (approved)', 'Betreuende (Bestätigt)'),
                'type' => 'boolean',
            ],
            [
                'id' => 'supervisors.aoi',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Supervisor (affiliated)', 'Betreuende (Affiliated)'),
                'type' => 'boolean',
            ],
            [
                'id' => 'supervisors.units',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'filter',
                ],
                'label' => lang('Supervisor (unit)', 'Betreuende (Einheit)'),
                'type' => 'string'
            ],
            [
                'id' => 'supervisors.sws',
                'module_of' => $typeModules['supervisor'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Supervisor (SWS)', 'Betreuende (SWS)'),
                'type' => 'integer'
            ],
            [
                'id' => 'editors',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'columns'
                ],
                'label' => lang('Editor', 'Herausgeber'),
                'type' => 'list',
            ],
            [
                'id' => 'editors.first',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Editor (first name)', 'Herausgeber (Vorname)'),
                'type' => 'string'
            ],
            [
                'id' => 'editors.last',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Editor (last name)', 'Herausgeber (Nachname)'),
                'type' => 'string'
            ],
            [
                'id' => 'editors.user',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Editor (username)', 'Herausgeber (Username)'),
                'type' => 'string'
            ],
            [
                'id' => 'editors.aoi',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Editor (affiliated)', 'Herausgeber (Affiliated)'),
                'type' => 'boolean',
            ],
            [
                'id' => 'editors.units',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Editor (unit)', 'Herausgeber (Einheit)'),
                'type' => 'string'
            ],
            [
                'id' => 'affiliated',
                'module_of' => $typeModules['affiliated'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Affiliated', 'Affiliiert'),
                'type' => 'boolean',
            ],
            [
                'id' => 'affiliated_positions',
                'module_of' => $typeModules['affiliated_positions'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Affiliated positions', 'Affiliierte Positionen'),
                'type' => 'list',
                'input' => 'select',
                'values' => [
                    'first' => lang('First author', 'Erstautor:in'),
                    'last' => lang('Last author', 'Letztautor:in'),
                    'first_and_last' => lang('First and last author', 'Erst- und Letztautor:in'),
                    'first_or_last' => lang('First or last author', 'Erst- oder Letztautor:in'),
                    'middle' => lang('Middle author', 'Mittelautor:in'),
                    'single' => lang('One single affiliated author', 'Ein einzelner affiliierter Autor'),
                    'none' => lang('No author affiliated', 'Kein:e Autor:in affiliiert'),
                    'all' => lang('All authors affiliated', 'Alle Autoren affiliiert'),
                    'corresponding' => lang('Corresponding author', 'Korrespondierender Autor:in'),
                    'not_first' => lang('Not first author', 'Nicht Erstautor:in'),
                    'not_last' => lang('Not last author', 'Nicht letzter Autor:in'),
                    'not_middle' => lang('Not middle author', 'Nicht Mittelautor:in'),
                    'not_corresponding' => lang('Not corresponding author', 'Nicht korrespondierender Autor:in'),
                    'not_first_or_last' => lang('Not first or last author', 'Nicht Erst- oder Letztautor:in')
                ]
            ],
            [
                'id' => 'cooperative',
                'module_of' => $typeModules['cooperative'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Cooperation type', 'Kooperationsform'),
                'type' => 'string',
                'values' => [
                    'individual' => lang('Individual (only one affiliated author)', 'Individuell (nur ein affiliierter Autor)'),
                    'departmental' => lang('Departmental (cooperation within one department)', 'Abteilungsintern (Kooperation innerhalb einer Abteilung)'),
                    'institutional' => lang('Institutional (cooperation between departments of the same institute)', 'Institutionell (Kooperation zwischen Abteilungen des gleichen Instituts)'),
                    'contributing' => lang('Contributing (cooperation with other institutes with middle authorships)', 'Beitragend (Kooperation mit anderen Instituten mit Mittelautorenschaft)'),
                    'leading' => lang('Leading (cooperation with other institutes with a corresponding role, first or last authorship)', 'Führend (Kooperation mit anderen Instituten mit einer korrespondierenden Rolle, Erst- oder Letztautorenschaft)'),
                    'none' => lang('None (no author affiliated)', 'Kein:e Autor:in affiliiert')
                ],
                'input' => 'select'
            ],
            [
                'id' => 'journal',
                'module_of' => $typeModules['journal'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => $Settings->journalLabel(),
                'type' => 'string'
            ],
            [
                'id' => 'issn',
                'module_of' => $typeModules['issn'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('ISSN'),
                'type' => 'list'
            ],
            [
                'id' => 'magazine',
                'module_of' => $typeModules['magazine'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Magazine', 'Magazin'),
                'type' => 'string'
            ],
            [
                'id' => 'year',
                'module_of' => $typeModules['year'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Year', 'Jahr'),
                'type' => 'integer',
                'default_value' => CURRENTYEAR
            ],
            [
                'id' => 'history',
                'module_of' => [],
                'usage' => [],
                'label' => lang('History', 'Verlauf'),
                'type' => 'list'
            ],
            [
                'id' => 'workflow',
                'module_of' => [],
                'usage' => [],
                'label' => lang('Workflow', 'Workflow'),
                'type' => 'list'
            ],
            [
                'id' => 'rendered',
                'module_of' => [],
                'usage' => [],
                'label' => lang('Rendered', 'Gerendert'),
                'type' => 'list'
            ],
            [
                'id' => 'license',
                'module_of' => $typeModules['license'] ?? [],
                'usage' => [
                    'filter',
                    'aggregate',
                    'columns'
                ],
                'label' => lang('License', 'Lizenz'),
                'type' => 'string'
            ],
            [
                'id' => 'month',
                'module_of' => $typeModules['month'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Month', 'Monat'),
                'type' => 'integer'
            ],
            [
                'id' => 'lecture_type',
                'module_of' => $typeModules['lecture_type'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'label' => lang('Lecture type', 'Vortragstyp'),
                'input' => 'select',
                'values' => ['short', 'long', 'repetition']
            ],
            [
                'id' => 'editorial',
                'module_of' => $typeModules['editorial'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Editor type', 'Editortyp'),
                'type' => 'string'
            ],
            [
                'id' => 'doi',
                'module_of' => $typeModules['doi'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('DOI'),
                'type' => 'string'
            ],
            [
                'id' => 'link',
                'module_of' => $typeModules['link'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Link'),
                'type' => 'string'
            ],
            [
                'id' => 'pubmed',
                'module_of' => $typeModules['pubmed'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Pubmed-ID'),
                'type' => 'integer'
            ],
            [
                'id' => 'pubtype',
                'module_of' => $typeModules['pubtype'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Publication type', 'Publikationstyp'),
                'type' => 'string',
                'input' => 'select',
                'values' => ['article', 'book', 'chapter', 'preprint', 'magazine', 'dissertation', 'others']
            ],
            [
                'id' => 'gender',
                'module_of' => $typeModules['gender'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Gender', 'Geschlecht'),
                'type' => 'string',
                'input' => 'select',
                'values' => ['f', 'm', 'd']
            ],
            [
                'id' => 'issue',
                'module_of' => $typeModules['issue'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Issue'),
                'type' => 'string'
            ],
            [
                'id' => 'volume',
                'module_of' => $typeModules['volume'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Volume'),
                'type' => 'string'
            ],
            [
                'id' => 'pages',
                'module_of' => $typeModules['pages'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Pages', 'Seiten'),
                'type' => 'string'
            ],
            [
                'id' => 'impact',
                'module_of' => $typeModules['journal'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Impact factor'),
                'type' => 'double'
            ],
            [
                'id' => 'quartile',
                'module_of' => $typeModules['journal'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Quartile'),
                'type' => 'string',
                'input' => 'select',
                'values' => ['Q1', 'Q2', 'Q3', 'Q4']
            ],
            [
                'id' => 'book-title',
                'module_of' => $typeModules['book-title'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Book title', 'Buchtitel'),
                'type' => 'string'
            ],
            [
                'id' => 'publisher',
                'module_of' => $typeModules['publisher'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Publisher', 'Verlag'),
                'type' => 'string'
            ],
            [
                'id' => 'city',
                'module_of' => $typeModules['city'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Location (Publisher)', 'Ort (Verlag)'),
                'type' => 'string'
            ],
            [
                'id' => 'edition',
                'module_of' => $typeModules['edition'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Edition'),
                'type' => 'string'
            ],
            [
                'id' => 'isbn',
                'module_of' => $typeModules['isbn'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('ISBN'),
                'type' => 'string'
            ],
            [
                'id' => 'doctype',
                'module_of' => $typeModules['doctype'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Document type', 'Dokumententyp'),
                'type' => 'string'
            ],
            [
                'id' => 'iteration',
                'module_of' => $typeModules['iteration'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Iteration (Misc)', 'Wiederholung (misc)'),
                'type' => 'string',
                'input' => 'select',
                'values' => ['once', 'annual']
            ],
            [
                'id' => 'software_type',
                'module_of' => $typeModules['software_type'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Type of software', 'Art der Software'),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'software' => lang('Software', 'Software'),
                    'database' => lang('Database', 'Datenbank'),
                    'dataset' => lang('Dataset', 'Datensatz'),
                    'webtool' => lang('Webtool', 'Webtool'),
                    'report' => lang('Report', 'Bericht')
                ]
            ],
            [
                'id' => 'software_venue',
                'module_of' => $typeModules['software_venue'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Publication venue (Software)', 'Ort der Veröffentlichung (Software)'),
                'type' => 'string'
            ],
            [
                'id' => 'version',
                'module_of' => $typeModules['version'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Version'),
                'type' => 'string'
            ],
            [
                'id' => 'category',
                'module_of' => $typeModules['category'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Category (students/guests)', 'Kategorie (Studenten/Gäste)'),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'guest scientist' => lang('Guest Scientist', 'Gastwissenschaftler:in'),
                    'lecture internship' => lang('Lecture Internship', 'Pflichtpraktikum im Rahmen des Studium'),
                    'student internship' => lang('Student Internship', 'Schülerpraktikum'),
                    'other' => lang('Other', 'Sonstiges'),
                    'doctoral thesis' => lang('Doctoral Thesis', 'Doktorand:in'),
                    'master thesis' => lang('Master Thesis', 'Master-Thesis'),
                    'bachelor thesis' => lang('Bachelor Thesis', 'Bachelor-Thesis')
                ]
            ],
            [
                'id' => 'thesis',
                'module_of' => $typeModules['thesis'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Thesis type', 'Art der Abschlussarbeit'),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('thesis')
            ],
            [
                'id' => 'pub-language',
                'module_of' => $typeModules['pub-language'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Publication language', 'Publikationssprache'),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('pub-language')
            ],
            [
                'id' => 'status',
                'module_of' => $typeModules['status'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Status (Thesis)'),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'in progress' => lang('In Progress', 'In Bearbeitung'),
                    'completed' => lang('Completed', 'Abgeschlossen'),
                    'aborted' => lang('Aborted', 'Abgebrochen')
                ]
            ],
            [
                'id' => 'name',
                'module_of' => $typeModules['name'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Name of guest', 'Name des Gastes'),
                'type' => 'string'
            ],
            [
                'id' => 'academic_title',
                'module_of' => $typeModules['academic_title'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Academic title of guest', 'Akad. Titel des Gastes'),
                'type' => 'string'
            ],
            [
                'id' => 'details',
                'module_of' => $typeModules['details'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Details', 'Details'),
                'type' => 'string'
            ],
            [
                'id' => 'conference',
                'module_of' => $typeModules['conference'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Event'),
                'type' => 'string'
            ],
            [
                'id' => 'location',
                'module_of' => $typeModules['location'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Location', 'Ort'),
                'type' => 'string'
            ],
            [
                'id' => 'country',
                'module_of' => $typeModules['country'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Country', 'Land'),
                'type' => 'string'
            ],
            [
                'id' => 'peer_reviewed',
                'module_of' => $typeModules['peer-reviewed'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Peer Reviewed', 'Peer Reviewed'),
                'type' => 'boolean',
            ],
            [
                'id' => 'open_access',
                'module_of' => $typeModules['openaccess'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Open Access'),
                'type' => 'boolean',
            ],
            [
                'id' => 'oa_status',
                'module_of' => $typeModules['openaccess-status'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Open Access Status'),
                'type' => 'string',
                'values' => ['gold', 'diamond', 'green', 'bronze', 'hybrid', 'open', 'closed'],
                'input' => 'select'
            ],
            [
                'id' => 'epub',
                'module_of' => $typeModules['epub'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Online ahead of print'),
                'type' => 'boolean',
            ],
            [
                'id' => 'correction',
                'module_of' => $typeModules['correction'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Correction'),
                'type' => 'boolean',
            ],
            [
                'id' => 'political_consultation',
                'module_of' => $typeModules['political_consultation'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Contribution to political and social consulting', 'Beitrag zur Politik- und Gesellschaftsberatung'),
                'type' => 'string',
                'values' => ['Gutachten', 'Positionspapier', 'Studie', 'Sonstiges', ''],
                'input' => 'select'
            ],
            [
                'id' => 'lecture-invited',
                'module_of' => $typeModules['lecture-invited'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Invited lecture'),
                'type' => 'boolean',
            ],
            [
                'id' => 'created_by',
                'module_of' => $typeModules['created_by'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Created by (Abbreviation)', 'Erstellt von (Kürzel)'),
                'type' => 'string'
            ],
            [
                'id' => 'created',
                'module_of' => $typeModules['created'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Created at', 'Erstellt am'),
                'type' => 'datetime',
                'input' => 'date'
            ],
            [
                'id' => 'imported',
                'module_of' => $typeModules['imported'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('Imported at', 'Importiert am'),
                'type' => 'datetime',
                'input' => 'date'
            ],
            [
                'id' => 'updated_by',
                'module_of' => $typeModules['updated_by'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Updated by (Abbreviation)', 'Aktualisiert von (Kürzel)'),
                'type' => 'string'
            ]
        ];

        $units = $osiris->groups->find(['inactive' => ['$ne' => true]], ['sort' => [lang('name', 'name_de') => 1]])->toArray();
        $units = array_column($units, lang('name', 'name_de'), 'id');
        $FIELDS[] = [
            'id' => 'units',
            'module_of' => ['general'],
            'usage' => [
                'aggregate',
                'filter',
                'columns'
            ],
            'label' => lang('Organizational unit', 'Organisationseinheit'),
            'type' => 'list',
            'input' => 'select',
            'values' => $units
        ];

        if ($Settings->featureEnabled('topics')) {
            $topics = $osiris->topics->find()->toArray();
            $topics = array_column($topics, 'name', 'id');
            $FIELDS[] = [
                'id' => 'topics',
                'module_of' => $typeModules['topics'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => $Settings->topicLabel(),
                'type' => 'list',
                'input' => 'select',
                'values' => $topics
            ];
        }

        if ($Settings->featureEnabled('tags')) {
            $tags = $Settings->get('tags', []);
            $FIELDS[] = [
                'id' => 'tags',
                'module_of' => ['general'],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => $Settings->tagLabel(),
                'type' => 'list',
                'input' => 'select',
                'values' => $tags
            ];
        }

        if ($Settings->featureEnabled('quality-workflow')) {
            $workflowTypes = $osiris->adminCategories->find(['workflow' => ['$ne' => null]])->toArray();
            $FIELDS[] = [
                'id' => 'workflow.status',
                'module_of' => $workflowTypes ? array_column($workflowTypes, 'id') : [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('Workflow Status', 'Workflow-Status'),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'verified' => lang('Verified', 'Verifiziert'),
                    'rejected' => lang('Rejected', 'Abgelehnt'),
                    'in_progress' => lang('In Process', 'In Bearbeitung'),
                ]
            ];
        }

        $FIELDS = parent::addCustomFields($FIELDS, $osiris, $typeModules);
        // remove 'filter' from all fields where module_of is empty
        // foreach ($FIELDS as &$f) {
        //     if (empty($f['module_of'])) {
        //         $f['usage'] = array_filter($f['usage'], function ($u) {
        //             return $u != 'filter';
        //         });
        //     }
        // }
        $this->fields = array_values($FIELDS);
        // Sort fields by name
        usort($this->fields, function ($a, $b) {
            if (isset($a['label']) && !isset($b['label'])) return -1;
            if (!isset($a['label']) && isset($b['label'])) return 1;
            if (!isset($a['label']) && !isset($b['label'])) return 0;
            return strnatcmp($a['label'], $b['label']);
        });
    }
}

// dump($FIELDS);
