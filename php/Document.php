<?php
require_once "Settings.php";
require_once "DB.php";
require_once "Schema.php";
require_once "Country.php";

class Document extends Settings
{

    public $doc = array();
    public $type = "";
    public $subtype = "";
    public $typeArr = array();
    public $subtypeArr = array();

    private $highlight = true;
    private $appendix = '';

    private $modules = array();

    public $title = "";
    public $subtitle = "";
    public $usecase = "web";
    public $full = false;

    private $schemaType = null;
    public $schema = [];
    private $DB = null;
    private $id = null;

    public $custom_fields = [];
    public $custom_field_values = [];

    public $templates = [
        "affiliation" => ["affiliation"],
        "authors" => ["authors"],
        "supervisor" => ["authors"],
        "scientist" => ["authors"],
        "authors-last-f" => ["authors"],
        "authors-last-first" => ["authors"],
        "authors-f-last" => ["authors"],
        "authors-f.-last" => ["authors"],
        "authors-first-last" => ["authors"],
        "book-series" => ["series"],
        "book-title" => ["book"],
        "category" => ["category"],
        "city" => ["city"],
        "conference" => ["conference"],
        "correction" => ["correction"],
        "start" => ["year", "month", "day"],
        "end" => ["year", "month", "day"],
        "date" => ["year", "month", "day"],
        "date-range" => ["start", "end"],
        "date-range-ongoing" => ["start", "end"],
        "year" => ["year", "month", "day"],
        "month" => ["year", "month", "day"],
        "details" => ["details"],
        "doctype" => ["doc_type"],
        "doi" => ["doi"],
        "edition" => ["edition"],
        "editor" => ["editors"],
        "editorial" => ["editor_type"],
        "file-icons" => ["file-icons"],
        "guest" => ["category"],
        "isbn" => ["isbn"],
        "issn" => ["issn"],
        "issue" => ["issue"],
        "iteration" => ["iteration"],
        "journal" => ["journal"],
        "journal-abbr" => ["journal"],
        "lecture-invited" => ["invited_lecture"],
        "lecture-type" => ["lecture_type"],
        "link" => ["link"],
        "software-link" => ["link"],
        "location" => ["location"],
        "magazine" => ["magazine"],
        "online-ahead-of-print" => ["epub"],
        "openaccess" => ["open_access"],
        "openaccess-text" => ["open_access"],
        "openaccess-status" => ["oa_status"],
        "pages" => ["pages"],
        "person" => ["name", "affiliation", "academic_title"],
        "publisher" => ["publisher"],
        "pubmed" => ["pubmed"],
        "pubtype" => ["pubtype"],
        "review-description" => ["title"],
        "review-type" => ["title"],
        "semester-select" => [],
        "subtype" => ["subtype"],
        "software-type" => ["software_type"],
        "software-venue" => ["software_venue"],
        "status" => ["status"],
        "student-category" => ["category"],
        "thesis" => ["category"],
        "teaching-category" => ["category"],
        "teaching-course" => ["title", "module", "module_id"],
        "teaching-course-short" => ["title", "module", "module_id"],
        "title" => ["title"],
        "university" => ["publisher"],
        "version" => ["version"],
        "volume" => ["volume"],
        "country" => ["country"],
        "nationality" => ["nationality"],
        "gender" => ["gender"],
        "volume-issue-pages" => ["volume"],
        "political_consultation" => ["political_consultation"],
    ];


    function __construct($highlight = true, $usecase = 'web')
    {
        parent::__construct();
        $this->highlight = $highlight;
        $this->usecase = $usecase;
        $this->DB = new DB;

        $fields = $this->DB->db->adminFields->find()->toArray();
        $this->custom_fields = array_column($fields, 'format', 'id');
        $this->custom_field_values = array_column($fields, 'values', 'id');
    }

    public function setDocument($doc)
    {
        if (!is_array($doc)) {
            $doc = DB::doc2Arr($doc);
        }
        $this->doc = $doc;
        $this->getActivityType();
        $this->initSchema();

        $this->modules = [];
        foreach (($this->subtypeArr['modules'] ?? array()) as $m) {
            $this->modules[] = str_replace('*', '', $m);
        }
    }

    public function schema()
    {
        if (!$this->hasSchema()) return "";
        $this->schema = [
            "@context" => "https://schema.org",
            "@graph" => []
        ];

        // shorten the expressions
        $d = $this->doc;

        $main = [
            "@id" => "#record",
            "@type" => $this->schemaType,
            "name" => $d['title'],
            "author" => Schema::authors($d['authors']),
            "datePublished" => Schema::date($d),
            "identifier" => [],
        ];
        if (isset($d['doi']))
            $main['identifier'][] = Schema::identifier("DOI", $d['doi']);
        if (isset($d['pubmed']))
            $main['identifier'][] = Schema::identifier("Pubmed-ID", $d['pubmed']);
        if (isset($d['isbn']))
            $main['identifier'][] = Schema::identifier("ISBN", $d['isbn']);


        switch ($this->schemaType) {
            case 'ScholarlyArticle':
                if (isset($d['pages']))
                    $main['pagination'] = $d['pages'];

                $main["isPartOf"] = [];
                if (isset($d['issue'])) {
                    $main['isPartOf'][] = ['@id' => '#issue'];
                    $issue = Schema::issue($d['issue']);
                    if (isset($d['volume'])) {
                        $issue['isPartOf'] = ['@id' => '#volume'];
                    }
                    $this->schema['@graph'][] = $issue;
                }

                if (isset($d['volume'])) {
                    $main['isPartOf'][] = ['@id' => '#volume'];
                    $volume = [
                        "@id" => "#volume",
                        "@type" => "PublicationVolume",
                        "volumeNumber" => $d['volume'],
                        "datePublished" => Schema::date($d),
                    ];
                    $this->schema['@graph'][] = $volume;
                }

                if (isset($d['journal_id'])) {
                    $j = $this->DB->getConnected('journal', $d['journal_id']);
                    if (!empty($j)) {
                        $journal = Schema::journal($j);
                        if (!empty($d['volume'])) {
                            $journal['hasPart'] = ['@id' => "#volume"];
                        }
                        $this->schema['@graph'][] = $journal;
                        $main['isPartOf'][] = ['@id' => '#journal'];
                    }
                }
                break;

            case "Thesis":
                $main['sourceOrganization'] = Schema::organisation($d['publisher'], $d['city'] ?? null);
                break;

            case "Book":
                if (isset($d['isbn']))
                    $main['isbn'] = $d['isbn'];
                if (isset($d['pages']))
                    $main['numberOfPages'] = $d['pages'];
                if (isset($d['publisher']))
                    $main['publisher'] = Schema::organisation($d['publisher'], $d['city'] ?? null);
                break;

            case "Chapter":
                $book = [
                    "@id" => "#book",
                    "@type" => 'Book',
                    "name" => $d['book'] ?? null
                ];
                if (isset($d['editors'])) {
                    $book['editor'] = Schema::authors($d['editors']);
                }

                if (isset($d['isbn']))
                    $book['isbn'] = $d['isbn'];
                if (isset($d['pages']))
                    $book['numberOfPages'] = $d['pages'];
                if (isset($d['publisher']))
                    $book['publisher'] = Schema::organisation($d['publisher'], $d['city'] ?? null);
                $this->schema['@graph'][] = $book;

                $main['isPartOf'] = "#book";
                if (isset($d['pages']))
                    $main['pagination'] = $d['pages'];
                break;

            case "Poster":
            case "PresentationDigitalDocument":
                $event = Schema::event($d);
                $event['@id'] = '#conference';
                $this->schema['@graph'][] = $event;
                $main['releasedEvent'] = ['@id' => '#conference'];
                break;

            default:
                break;
        }
        $this->schema['@graph'][] = $main;

        $s = '<script type="application/ld+json">';
        $s .= json_encode($this->schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $s .= '</script>';
        return $s;
    }

    public function hasSchema()
    {
        return $this->schemaType !== null;
    }

    private function initSchema()
    {
        $this->schemaType = null;
        switch ($this->type) {
            case 'publication':
                switch ($this->subtype) {
                    case 'article':
                    case 'magazine':
                    case 'preprint':
                        $this->schemaType = "ScholarlyArticle";
                        return;
                    case 'thesis':
                        $this->schemaType = "Thesis";
                        return;
                    case 'book':
                        $this->schemaType = "Book";
                        return;
                    case 'chapter':
                        $this->schemaType = "Chapter";
                        return;
                    default:
                        return;
                }
            case 'poster':
                $this->schemaType = "Poster";
                return;
            case 'lecture':
                $this->schemaType = "PresentationDigitalDocument";
                return;
            default:
                break;
        }
    }

    public function print_schema()
    {
        echo '<script type="application/ld+json">';
        echo json_encode($this->schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        echo '</script>';
    }

    function activity_icon($tooltip = true)
    {
        $icon = 'placeholder';

        if (!empty($this->subtypeArr) && isset($this->subtypeArr['icon'])) {
            $icon = $this->subtypeArr['icon'];
        } elseif (!empty($this->typeArr) && isset($this->typeArr['icon'])) {
            $icon = $this->typeArr['icon'];
        }
        if (empty($this->typeArr)) {
            return "<i class='ph text-danger ph-warning'></i>";
        }
        $type = $this->typeArr['id'];
        $icon = "<i class='ph text-$type ph-$icon'></i>";
        if ($tooltip) {
            $name = $this->activity_subtype();
            return "<span data-toggle='tooltip' data-title='$name'>$icon</span>";
        }

        return $icon;
    }


    function activity_subtype()
    {
        $name = lang("Other", "Sonstiges");
        if (!empty($this->subtypeArr) && isset($this->subtypeArr['name'])) {
            $name = lang(
                $this->subtypeArr['name'],
                $this->subtypeArr['name_de'] ?? $this->subtypeArr['name']
            );
        } elseif (!empty($this->typeArr) && isset($this->typeArr['name'])) {
            $name = lang(
                $this->typeArr['name'],
                $this->typeArr['name_de'] ?? $this->typeArr['name']
            );
        } else {
            return "ERROR: doc is not defined!";
        }
        return $name;
    }
    function activity_type()
    {
        $name = lang("Other", "Sonstiges");
        if (!empty($this->typeArr) && isset($this->typeArr['name'])) {
            $name = lang(
                $this->typeArr['name'],
                $this->typeArr['name_de'] ?? $this->typeArr['name']
            );
        } else {
            return "ERROR: doc is not defined!";
        }
        return $name;
    }

    private function getActivityType()
    {
        if (is_string($this->doc)) {
            $type = strtolower(trim($this->doc));
        } else {
            $type = strtolower(trim($this->doc['type'] ?? $this->doc['subtype'] ?? ''));
        }
        $this->type = $type;
        $this->typeArr = $this->getActivity($type);
        $this->subtype = $this->doc['subtype'];
        $this->subtypeArr = $this->getActivity($this->type, $this->subtype);
    }

    function activity_badge()
    {
        $name = $this->activity_subtype();
        $icon = $this->activity_icon(false);
        $type = $this->doc['type'];
        return "<span class='badge badge-$type'>$icon $name</span>";
    }


    private static function commalist($array, $sep = "and")
    {
        if (empty($array)) return "";
        if (count($array) < 3) return implode(" $sep ", $array);
        $str = implode(", ", array_slice($array, 0, -1));
        return $str . " $sep " . end($array);
    }

    public static function abbreviateAuthorFormat($last, $first, $format = 'last, f.')
    {
        // 'last, f'
        // 'f last'
        // 'last, first'
        // 'first last'
        switch ($format) {
            case 'last, f':
                return Document::abbreviateAuthor($last, $first, true, '&nbsp;', '');
            case 'last, first':
                return $last . ',&nbsp;' . $first;
            case 'first last':
                return $first . '&nbsp;' . $last;
            case 'f. last':
                return Document::abbreviateAuthor($last, $first, false, '&nbsp;', '.');
            case 'f last':
                return Document::abbreviateAuthor($last, $first, false, '&nbsp;', '');
            default:
                return Document::abbreviateAuthor($last, $first, true, '&nbsp;', '.');
        }
    }

    public static function abbreviateAuthor($last, $first, $reverse = true, $space = '&nbsp;', $abbr_symbol = '.')
    {
        $fn = "";
        if ($first) :
            foreach (preg_split("/(\s+| |-|\.)/u", $first, -1, PREG_SPLIT_DELIM_CAPTURE) as $name) {
                if (empty(trim($name)) || $name == '.' || $name == ' ') continue;
                if ($name == '-')
                    $fn .= '-';
                else
                    $fn .= "" . mb_substr($name, 0, 1) . $abbr_symbol;
            }
        endif;
        if (empty(trim($fn))) return $last;
        if ($reverse) return $last . "," . $space . $fn;
        return $fn . $space . $last;
    }

    function formatAuthors($raw_authors, $format = 'last, f.')
    {
        $this->appendix = '';
        if (empty($raw_authors)) return '';
        $n = 0;
        $authors = array();

        $first = 1;
        $last = 1;
        $corresponding = false;

        $raw_authors = DB::doc2Arr($raw_authors);
        if (!empty($raw_authors) && is_array($raw_authors)) {
            $pos = array_count_values(array_column($raw_authors, 'position'));
            // dump($pos);
            $first = $pos['first'] ?? 1;
            $last = $pos['last'] ?? 1;
            $corresponding = array_key_exists('corresponding', $pos);
        }
        foreach ($raw_authors as $n => $a) {
            if (!$this->full) {
                if ($n > 9) break;
                $author = Document::abbreviateAuthorFormat($a['last'], $a['first'], $format);
                if ($this->highlight === true) {
                    //if (($a['aoi'] ?? 0) == 1) $author = "<b>$author</b>";
                } else if ($this->highlight && $a['user'] == $this->highlight) {
                    $author = "<u>$author</u>";
                }
                if (isset($a['user']) && !empty($a['user'])) {
                    if ($this->usecase == 'portal') {
                        $person = $this->DB->getPerson($a['user']);
                        if ($person && ($person['hide'] ?? false) === false && ($person['is_active'] ?? true) !== false)
                            $author = "<a href='/person/" . strval($person['_id']) . "'>$author</a>";
                    } else if (!$this->full)
                        $author = "<a href='" . ROOTPATH . "/profile/" . $a['user'] . "'>$author</a>";
                }
                $authors[] = $author;
            } else {
                $author = Document::abbreviateAuthorFormat($a['last'], $a['first'], $format);
                if ($this->usecase == 'portal') {
                    if (isset($a['user']) && !empty($a['user'])) {
                        $person = $this->DB->getPerson($a['user']);
                        if ($person)
                            $author = "<a href='/person/" . strval($person['_id']) . "'>$author</a>";
                    }
                } else if (!$this->full) {
                    $author = "<a href='" . ROOTPATH . "/profile/" . $a['user'] . "'>$author</a>";
                } else if ($this->highlight === true) {
                    if (($a['aoi'] ?? 0) == 1) $author = "<b>$author</b>";
                } else if ($this->highlight && $a['user'] == $this->highlight) {
                    $author = "<b>$author</b>";
                }
                if ($first > 1 && $a['position'] == 'first') {
                    $author .= "<sup>#</sup>";
                }
                if ($last > 1 && $a['position'] == 'last') {
                    $author .= "<sup>*</sup>";
                }
                if (isset($a['position']) && $a['position'] == 'corresponding') {
                    $author .= "<sup>§</sup>";
                    $corresponding = true;
                }

                $authors[] = $author;
            }
        }

        if ($first > 1) {
            if ($this->typeArr['id'] == 'poster' || $this->typeArr['id'] == 'lecture')
                $this->appendix .= " <sup>#</sup> Presenting authors";
            else
                $this->appendix .= " <sup>#</sup> Shared first authors";
        }
        if ($last > 1) {
            $this->appendix .= " <sup>*</sup> Shared last authors";
        }
        if ($corresponding) {
            $this->appendix .= " <sup>§</sup> Corresponding author";
        }
        $append = "";
        $separator = 'and';
        if (!$this->full && $n > 9) {
            $append = " et al.";
            $separator = ", ";
        }
        return Document::commalist($authors, $separator) . $append;
    }

    public function getAffiliationTypes()
    {
        $authors = DB::doc2Arr($this->doc['authors']);
        $aoi_authors = array_filter($authors, function ($a) {
            return $a['aoi'] ?? false;
        });
        $pos = array_unique(array_column($aoi_authors, 'position'));
        $aff = [];
        if (empty($authors) || empty($pos)) {
            $aff[] = 'unspecified';
            return $aff;
        }
        if (count($authors) == 1 && count($aoi_authors) == 1)
            $aff[] = 'single';
        if (count($authors) == count($aoi_authors))
            $aff[] = 'all';
        if (in_array('first', $pos))
            $aff[] = 'first';
        if (in_array('last', $pos))
            $aff[] = 'last';
        if (in_array('first', $pos) && in_array('last', $pos))
            $aff[] = 'first_and_last';
        if (in_array('first', $pos) || in_array('last', $pos))
            $aff[] = 'first_or_last';
        if (in_array('middle', $pos))
            $aff[] = 'middle';
        if (empty($aoi_authors))
            $aff[] = 'none';
        if (in_array('corresponding', $pos))
            $aff[] = 'corresponding';
        if (!in_array('first', $pos))
            $aff[] = 'not_first';
        if (!in_array('last', $pos))
            $aff[] = 'not_last';
        if (!in_array('middle', $pos))
            $aff[] = 'not_middle';
        if (!in_array('corresponding', $pos))
            $aff[] = 'not_corresponding';
        if (!in_array('first', $pos) && !in_array('last', $pos))
            $aff[] = 'not_first_or_last';
        return $aff;
    }

    public function getCooperationType($affPos = null, $depts = [])
    {
        global $Departments;
        if (!empty($depts)) {
            $departments = array_keys($Departments);
            $depts = array_intersect(DB::doc2Arr($depts), $departments);
        }
        if (is_null($affPos)) $affPos = $this->getAffiliationTypes();
        if (in_array('none', $affPos)) return 'none';
        if (in_array('single', $affPos)) return 'individual';
        if (in_array('all', $affPos)) {
            if (count($depts) == 1) return 'departmental';
            else return 'institutional';
        }
        if (in_array('first_or_last', $affPos) || in_array('corresponding', $affPos)) return 'leading';
        else return 'contributing';
    }

    public function getAuthors()
    {
        if (empty($this->doc['authors'])) return '';
        $full = $this->full;
        $this->full = true;
        return $this->formatAuthors($this->doc['authors']);
        $this->full = $full;
    }


    private static function getDateTime($date)
    {
        if (isset($date['year'])) {
            //date instanceof MongoDB\Model\BSONDocument
            $d = new DateTime();
            $d->setDate(
                $date['year'],
                $date['month'] ?? 1,
                $date['day'] ?? 1
            );
        } else {
            try {
                $d = date_create($date);
            } catch (TypeError $th) {
                $d = null;
            }
        }
        return $d;
    }

    public static function format_date($date)
    {
        if (empty($date)) return '';
        $d = Document::getDateTime($date);
        if (empty($d)) return '';
        return date_format($d, "d.m.Y");
    }

    private static function fromToDate($from, $to)
    {
        if (empty($to) || $from == $to) {
            return Document::format_date($from);
        }
        // $to = date_create($to);
        $from = Document::format_date($from);
        $to = Document::format_date($to);

        $f = explode('.', $from, 3);
        $t = explode('.', $to, 3);

        $from = $f[0] . ".";
        if ($f[1] != $t[1] || $f[2] != $t[2]) {
            $from .= $f[1] . ".";
        }
        if ($f[2] != $t[2]) {
            $from .= $f[2];
        }

        return $from . '-' . $to;
    }


    public static function format_month($month)
    {
        if (empty($month)) return '';
        $month = intval($month);
        $array = [
            1 => lang("January", "Januar"),
            2 => lang("February", "Februar"),
            3 => lang("March", "März"),
            4 => lang("April"),
            5 => lang("May", "Mai"),
            6 => lang("June", "Juni"),
            7 => lang("July", "Juli"),
            8 => lang("August"),
            9 => lang("September"),
            10 => lang("October", "Oktober"),
            11 => lang("November"),
            12 => lang("December", "Dezember")
        ];
        return $array[$month];
    }


    function getUserAuthor($authors, $user)
    {
        $authors = DB::doc2Arr($authors);
        $author = array_filter($authors, function ($author) use ($user) {
            return $author['user'] == $user;
        });
        if (empty($author)) return array();
        return reset($author);
    }

    function is_approved($user)
    {
        if (!isset($this->doc['authors'])) return true;
        $authors = $this->doc['authors'];
        $authors = DB::doc2Arr($authors);
        if (isset($this->doc['editors'])) {
            $editors = $this->doc['editors'];
            $editors = DB::doc2Arr($editors);
            $authors = array_merge($authors, $editors);
        }
        return $this->getUserAuthor($authors, $user)['approved'] ?? false;
    }


    function has_issues($user = null)
    {
        if ($user === null) $user = $_SESSION['username'];
        $issues = array();
        $type = $this->typeArr['id'];
        $subtype = $this->subtypeArr['id'];

        if (!$this->is_approved($user)) $issues[] = "approval";

        // check EPUB issue
        $epub = ($this->doc['epub'] ?? false);
        // set epub to false if user has delayed the warning
        if ($epub && isset($this->doc['epub-delay'])) {
            if (new DateTime() < new DateTime($this->doc['epub-delay'])) {
                $epub = false;
            }
        }
        if ($epub) $issues[] = "epub";

        // CHECK status issue
        if (in_array('status', $this->modules)) {
            $status = $this->doc['status'] ?? '';
            $today = date('Y-m-d');
            if ($status == 'in progress' && $this->doc['end_date'] < $today) $issues[] = "status";
            if ($status == 'preparation' && $this->doc['start_date'] < $today) $issues[] = "status";
        }

        // check ongoing reminder
        if (in_array('date-range-ongoing', $this->modules) && is_null($this->doc['end'])) {
            if (!isset($this->doc['end-delay']) || (new DateTime() > new DateTime($this->doc['end-delay']))) {
                $issues[] = "openend";
            }
        }

        return $issues;
    }

    public static function translateCategory($cat)
    {
        switch ($cat) {
            case 'doctoral thesis':
                return "Doktorand:in";
            case 'master thesis':
                return "Master-Thesis";
            case 'bachelor thesis':
                return "Bachelor-Thesis";
            case 'guest scientist':
                return "Gastwissenschaftler:in";
            case 'lecture internship':
                return "Pflichtpraktikum im Rahmen des Studium";
            case 'student internship':
                return "Schülerpraktikum";
            case 'lecture':
                return lang('Lecture', 'Vorlesung');
            case 'practical':
                return lang('Practical course', 'Praktikum');
            case 'practical-lecture':
                return lang('Lecture and practical course', 'Vorlesung und Praktikum');
            case 'lecture-seminar':
                return lang('Lecture and seminar', 'Vorlesung und Seminar');
            case 'practical-seminar':
                return lang('Practical course and seminar', 'Praktikum und Seminar');
            case 'lecture-practical-seminar':
                return lang('Lecture, seminar, practical course', 'Vorlesung, Seminar und Praktikum');
            case 'seminar':
                return lang('Seminar');
            case 'other':
                return lang('Other', 'Sonstiges');
            default:
                return $cat;
        }
    }

    private function getVal($field, $default = '')
    {
        if ($default === '' && $this->usecase == 'list') $default = '-';
        return $this->doc[$field] ?? $default;
    }

    public function get_field($module, $default = '')
    {
        if ($this->usecase == 'list') $default = '-';
        switch ($module) {
            case "affiliation": // ["book"],
                return $this->getVal('affiliation');
            case "authors": // ["authors"],
            case "supervisor": // ["authors"],
            case "scientist": // ["authors"],
                return $this->formatAuthors($this->getVal('authors', []));
            case "authors-last-f":
                return $this->formatAuthors($this->getVal('authors', []), $format = 'last, f');
            case "authors-f-last":
                return $this->formatAuthors($this->getVal('authors', []), $format = 'f last');
            case "authors-f.-last":
                return $this->formatAuthors($this->getVal('authors', []), $format = 'f. last');
            case "authors-last-first":
                return $this->formatAuthors($this->getVal('authors', []), $format = 'last, first');
            case "authors-first-last":
                return $this->formatAuthors($this->getVal('authors', []), $format = 'first last');
            case "book-series": // ["series"],
                return $this->getVal('series');
            case "book-title": // ["book"],
                return $this->getVal('book');
            case "category": // ["category"],
                return $this->translateCategory($this->getVal('category'));
            case "city": // ["city"],
                return $this->getVal('city');
            case "conference": // ["conference"],
                return $this->getVal('conference');
            case "correction": // ["correction"],
                $val = $this->getVal('correction', false);
                if ($this->usecase == 'list')
                    return bool_icon($val);
                if ($val)
                    return "<span style='color:#B61F29;'>[Correction]</span>";
                else return '';
            case "start": // ["year", "month", "day"],
            case "end": // ["year", "month", "day"],
                return Document::format_date($this->getVal($module, null));
            case "date": // ["year", "month", "day"],
            case "date-range": // ["start", "end"],
                // return $this->fromToDate($this->getVal('start'), $this->getVal('end') ?? null);
                return $this->fromToDate($this->getVal('start', $this->doc), $this->getVal('end', null));
            case "date-range-ongoing":
                if (!empty($this->doc['start'])) {
                    if (!empty($this->doc['end'])) {
                        $start = Document::format_month($this->doc['start']['month']) . ' ' . $this->doc['start']['year'];
                        $end = Document::format_month($this->doc['end']['month']) . ' ' . $this->doc['end']['year'];
                        if ($start == $end) return $start;
                        $date = lang("from ", "von ") . $start . lang(" until ", " bis ") . $end;
                    } else {
                        $date = lang("since ", "seit ");
                        $date .= Document::format_month($this->doc['start']['month']) . ' ' . $this->doc['start']['year'];
                    }
                    return $date;
                }
                return $this->fromToDate($this->doc, null);
            case "year": // ["year", "month", "day"],
                return $this->getVal('year');
            case "month": // ["year", "month", "day"],
                return Document::format_month($this->getVal('month'));
            case "details": // ["details"],
                return $this->getVal('details');
            case "doctype": // ["doc_type"],
                return $this->getVal('doc_type');
            case "doi": // ["doi"],
                $val = $this->getVal('doi');
                if ($val == $default || empty($val)) return $default;
                return "DOI: <a target='_blank' href='https://doi.org/$val'>$val</a>";
            case "edition": // ["edition"],
                $val = $this->getVal('edition');
                if ($val != $default) {
                    if ($val == 1) $val .= "st";
                    elseif ($val == 2) $val .= "nd";
                    elseif ($val == 3) $val .= "rd";
                    else $val .= "th";
                }
                return $val;
            case "editor": // ["editors"],
                return $this->formatAuthors($this->getVal('editors', []));
            case "editorial": // ["editor_type"],
                return $this->getVal('editor_type');
            case "file-icons":
                if ($this->usecase == 'portal') return '';
                $files = '';
                foreach ($this->getVal('files', array()) as $file) {
                    $icon = getFileIcon($file['filetype']);
                    $files .= " <a href='$file[filepath]' target='_blank' data-toggle='tooltip' data-title='$file[filetype]: $file[filename]' class='file-link'><i class='ph ph-file ph-$icon'></i></a>";
                }
                return $files;
            case "guest": // ["category"],
                return $this->translateCategory($this->getVal('category'));
            case "isbn": // ["isbn"],
                return $this->getVal('isbn');
            case "issn": // ["issn"],
                $issn = $this->getVal('issn');
                if (empty($issn)) return '';
                return implode(', ', DB::doc2Arr($issn));
            case "issue": // ["issue"],
                return $this->getVal('issue');
            case "iteration": // ["iteration"],
                return $this->getVal('iteration');
            case "journal": // ["journal", "journal_id"],
                $val = $this->doc['journal_id'] ?? $default;
                if ($val != $default) {
                    $j = $this->DB->getConnected('journal', $this->getVal('journal_id'));
                    return $j['journal'];
                }
                return $this->getVal('journal');
            case "journal-abbr":
                $val = $this->doc['journal_id'] ?? $default;
                if ($val != $default) {
                    $j = $this->DB->getConnected('journal', $this->getVal('journal_id'));
                    if (isset($j['abbr']) && !empty($j['abbr'])) return $j['abbr'];
                    return $j['journal'];
                }
                return $this->getVal('journal');
            case "lecture-invited": // ["invited_lecture"],
                $val = $this->getVal('invited_lecture', false);
                if ($this->usecase == 'list')
                    return bool_icon($val);
                if ($val != $default)
                    return "Invited lecture";
                else return '';
            case "lecture-type": // ["lecture_type"],
                return $this->getVal('lecture_type');
            case "link": // ["link"],
            case "software-link": // ["link"],
                $val = $this->getVal('link');
                return "<a target='_blank' href='$val'>$val</a>";
            case "location": // ["location"],
                return $this->getVal('location');
            case "magazine": // ["magazine"],
                return $this->getVal('magazine');
            case "online-ahead-of-print": // ["epub"],
                if ($this->usecase == 'list')
                    return bool_icon($this->getVal('epub', false));
                if ($this->getVal('epub', false))
                    return "<span style='color:#B61F29;'>[Online ahead of print]</span>";
                else return '';
            case "openaccess": // ["open_access"],
                case "open_access": // ["open_access"],
                $status = $this->getVal('oa_status', 'Unknown Status');
                if (!empty($this->getVal('open_access', false))) {
                    $oa = '<i class="icon-open-access text-success" title="Open Access (' . $status . ')"></i>';
                } else {
                    $oa = '<i class="icon-closed-access text-danger" title="Closed Access"></i>';
                }
                if ($this->usecase == 'list') return $oa . " (" . $status . ")";
                return $oa;
            case "openaccess-text": // ["open_access"],
                if ($this->getVal('open_access', false)) {
                    return 'Open Access';
                } 
                return '';
            case "oa_status": // ["oa_status"],
            case "openaccess-status": // ["oa_status"],
                return $this->getVal('oa_status', 'Unknown Status');

            case "pages": // ["pages"],
                return $this->getVal('pages');
            case "person": // ["name", "affiliation", "academic_title"],
                return $this->getVal('name');
            case "publisher":; // ["publisher"],
                return $this->getVal('publisher');
            case "pubmed": // ["pubmed"],
                $val = $this->getVal('pubmed');
                if ($val == $default) return $val;
                return "<a target='_blank' href='https://pubmed.ncbi.nlm.nih.gov/$val'>$val</a>";
            case "pubtype": // ["pubtype"],
                return $this->getVal('pubtype');
            case "review-description": // ["title"],
                return $this->getVal('title');
            case "review-type": // ["title"],
                return $this->getVal('review-type');
            case "semester-select": // [],
                return '';
            case "subtype":
                return $this->activity_subtype();
            case "software-type": // ["software_type"],
                $val = $this->getVal('software_type');
                switch ($val) {
                    case 'software':
                        return "Computer software";
                    case 'database':
                        return "Database";
                    case 'webtool':
                        return "Webpage";
                    case 'dataset':
                        return "Dataset";
                    default:
                        return "Computer software";
                }
            case "software-venue": // ["software_venue"],
                return $this->getVal('software_venue');
            case "status": // ["status"],
                return $this->getVal('status');
            case "student-category": // ["category"],
                return $this->translateCategory($this->getVal('category'));
            case "thesis": // ["category"],
                switch ($this->getVal('thesis')) {
                    case 'doctor':
                        return lang('Doctoral Thesis', 'Doktorarbeit');
                    case 'master':
                        return lang('Master Thesis', 'Masterarbeit');
                    case 'bachelor':
                        return lang('Bachelor Thesis', 'Bachelorarbeit');
                    default:
                        return lang('Thesis', 'Abschlussarbeit');
                }
            case "teaching-category": // ["category"],
                return $this->translateCategory($this->getVal('category'));
            case "teaching-course": // ["title", "module", "module_id"],
                if (isset($this->doc['module_id'])) {
                    $m = $this->DB->getConnected('teaching', $this->getVal('module_id'));
                    if (empty($m)) return $this->getVal('module') ?? '';
                    return $m['module'] . ': ' . $m['title'];
                }
                return $this->getVal('title');
            case "teaching-course-short": // ["title", "module", "module_id"],
                if (isset($this->doc['module_id'])) {
                    $m = $this->DB->getConnected('teaching', $this->getVal('module_id'));
                    if (empty($m)) return $this->getVal('module') ?? '';
                    return $m['module'];
                }
                return 'Unknown';
            case "title": // ["title"],
                return $this->getVal('title');
            case "university": // ["publisher"],
                return $this->getVal('publisher');
            case "version": // ["version"],
                $val = $this->getVal('version');
                if ($val == $default) return $default;
                return "Version " . $val;
            case "volume": // ["volume"],
                return $this->getVal('volume');
            case "country":
            case "nationality":
                $code = $this->getVal('country');
                return $this->DB->getCountry($code, lang('name', 'name_de'));
            case "gender":
                switch ($this->getVal('gender')) {
                    case 'f':
                        return lang('female', 'weiblich');
                    case 'm':
                        return lang('male', 'männlich');
                    case 'd':
                        return lang('non-binary', 'divers');
                    case '-':
                        return lang('not specified', 'keine Angabe');
                    default:
                        return '';
                }
            case "volume-issue-pages": // ["volume"],
                $val = '';
                if (!empty($this->getVal('volume'))) {
                    $val .= " " . $this->getVal('volume');
                }
                if (!empty($this->getVal('issue'))) {
                    $val .= "(" . $this->getVal('issue') . ')';
                }
                if (!empty($this->getVal('pages'))) {
                    $val .= ": " . $this->getVal('pages');
                }
                return $val;
            case "political_consultation":
                return $this->getVal('political_consultation', false);
            default:
                $val = $this->getVal($module, '-');
                // only in german because standard is always english
                if (lang('en', 'de') == 'de' && isset($this->custom_field_values[$module])) {
                    foreach ($this->custom_field_values[$module] as $field) {
                        if ($val == $field[0] ?? '') return lang(...$field);
                    }
                }
                if (isset($this->custom_fields[$module])){
                    $val = $this->customVal($val, $this->custom_fields[$module]);
                }
                if (is_array($val)) return implode(", ", $val);
                return $val;
        }
    }

    public function customVal($val, $format){
        if ($format == 'date') {
            return Document::format_date($val);
        }
        if ($format == 'url') {
            return "<a href='$val' target='_blank' class='link'>$val</a>";
        }
        return $val;
    }

    public function format()
    {
        $this->full = true;
        $template = $this->subtypeArr['template']['print'] ?? '{title}';

        $line = $this->template($template);
        if (!empty($this->appendix)) {
            $line .= "<br><small style='color:#878787;'>" . $this->appendix . "</small>";
        }
        return $line;
    }

    public function formatShort($link = true)
    {
        $this->full = false;
        $line = "";
        $title = $this->getTitle();

        $id = $this->doc['_id'];
        if (is_array($id)) {
            $id = $id['$oid'];
        } else {
            $id = strval($id);
        }

        if ($this->usecase == 'portal') {
            $line = "<a class='colorless' href='" . PORTALPATH . "/activity/$id'>$title</a>";
        } else if ($link) {
            $line = "<a class='colorless' href='" . ROOTPATH . "/activities/view/$id'>$title</a>";
        } else {
            $line = $title;
        }

        $line .= "<br><small class='text-muted d-block'>";
        $line .= $this->getSubtitle();
        $line .= $this->get_field('file-icons');
        $line .= "</small>";

        return $line;
    }

    public function formatPortfolio()
    {
        $this->full = false;
        $this->usecase = 'portal';
        $line = "";
        $title = $this->getTitle();

        $id = $this->doc['_id'];
        if (is_array($id)) {
            $id = $id['$oid'];
        } else {
            $id = strval($id);
        }

        $line = "<a class='colorless' href='/activity/$id'>$title</a>";

        $line .= "<br><small class='text-muted d-block'>";
        $line .= $this->getSubtitle();
        $line .= "</small>";

        return $line;
    }

    public function bibtex()
    {
        $bibtex = '';
        $bibentries = [
            'journal-article' => "article",
            'article' => "article",
            'Journal Article' => "article",
            'book' => "book",
            'chapter' => "inbook",
            "misc" => "misc"
        ];
        $ids = [];
        $doc = $this->doc;
        // generate a unique ID 
        $id = $doc['authors'][0]['last'] . $doc['year'];
        $oid = $id;
        $i = 'a';
        while (in_array($id, $ids)) {
            // append letter if not unique
            $id = $oid . $i++;
        }
        $ids[] = $id;

        $bibtex .= '@' . ($bibentries[trim($doc['subtype'] ?? $doc['pubtype'] ?? 'misc')] ?? 'misc') . '{' . $id . ',' . PHP_EOL;


        if (isset($doc['title']) and ($doc['title'] != '')) {
            $bibtex .= '  Title = {' . strip_tags($doc['title']) . '},' . PHP_EOL;
        }
        if (isset($doc['authors']) and ($doc['authors'] != '')) {
            $authors = [];
            foreach ($doc['authors'] as $a) {
                $author = $a['last'];
                if (!empty($a['first'])) {
                    $author .= ", " . $a['first'];
                }
                $authors[] = $author;
            }
            $bibtex .= '  Author = {' . implode(' and ', $authors) . '},' . PHP_EOL;
        }
        if (isset($doc['editors']) and ($doc['editors'] != '')) {
            $editors = [];
            foreach ($doc['editors'] as $a) {
                $editors[] = Document::abbreviateAuthor($a['last'], $a['first']);
            }
            $bibtex .= '  Editor = {' . implode(' and ', $editors) . '},' . PHP_EOL;
        }
        if (isset($doc['journal']) and ($doc['journal'] != '')) $bibtex .= '  Journal = {' . $doc['journal'] . '},' . PHP_EOL;
        if (isset($doc['year']) and ($doc['year'] != '')) $bibtex .= '  Year = {' . $doc['year'] . '},' . PHP_EOL;
        if (isset($doc['number']) and ($doc['number'] != '')) $bibtex .= '  Number = {' . $doc['number'] . '},' . PHP_EOL;
        if (isset($doc['pages']) and ($doc['pages'] != '')) $bibtex .= '  Pages = {' . $doc['pages'] . '},' . PHP_EOL;
        if (isset($doc['volume']) and ($doc['volume'] != '')) $bibtex .= '  Volume = {' . $doc['volume'] . '},' . PHP_EOL;
        if (isset($doc['doi']) and ($doc['doi'] != '')) $bibtex .= '  Doi = {' . $doc['doi'] . '},' . PHP_EOL;
        if (isset($doc['isbn']) and ($doc['isbn'] != '')) $bibtex .= '  Isbn = {' . $doc['isbn'] . '},' . PHP_EOL;
        if (isset($doc['publisher']) and ($doc['publisher'] != '')) $bibtex .= '  Publisher = {' . $doc['publisher'] . '},' . PHP_EOL;
        if (isset($doc['book']) and ($doc['book'] != '')) $bibtex .= '  Booktitle = {' . $doc['book'] . '},' . PHP_EOL;
        // if (isset($doc['chapter']) and ($doc['chapter'] != '')) $bibtex .= '  Chapter = {' . $doc['chapter'] . '},' . PHP_EOL;
        if (isset($doc['abstract']) and ($doc['abstract'] != '')) $bibtex .= '  Abstract = {' . $doc['abstract'] . '},' . PHP_EOL;
        if (isset($doc['keywords']) and ($doc['keywords'] != '')) {
            $bibtex .= '  Keywords = {';
            foreach ($doc['keywords'] as $keyword) $bibtex .= $keyword . PHP_EOL;
            $bibtex .= '},' . PHP_EOL;
        }

        $bibtex .= '}' . PHP_EOL;
        return $bibtex;
    }

    public function RIS()
    {
        $ris = '';
        $doc = $this->doc;
        $types = [
            'article' => 'JOUR',
            'book' => 'BOOK',
            'chapter' => 'CHAP',
            'data' => 'DATA',
            'database' => 'DBASE',
            'dissertation' => 'THES',
            'lecture' => 'SLIDE',
            'magazine' => 'MGZN',
            'others' => 'RPRT',
            'preprint' => 'INPR',
            'software' => 'COMP',
            'patent' => 'PAT',
            'press' => 'PRESS'
        ];
        $ris .= "TY  - " . ($types[$doc['subtype']] ?? 'GEN') . PHP_EOL;
        if (isset($doc['authors']) and ($doc['authors'] != '')) {
            foreach ($doc['authors'] as $a) {
                $author = $a['last'];
                if (!empty($a['first'])) {
                    $author .= ", " . $a['first'];
                }
                $ris .= "AU  - " . $author . PHP_EOL;
            }
        }
        if (isset($doc['editors']) and ($doc['editors'] != '')) {
            foreach ($doc['editors'] as $a) {
                $author = $a['last'];
                if (!empty($a['first'])) {
                    $author .= ", " . $a['first'];
                }
                $ris .= "ED  - " . $author . PHP_EOL;
            }
        }
        if (isset($doc['title']) and ($doc['title'] != '')) $ris .= "TI  - " . $doc['title'] . PHP_EOL;
        if (isset($doc['journal']) and ($doc['journal'] != '')) $ris .= "T2  - " . $doc['journal'] . PHP_EOL;
        if (isset($doc['year']) and ($doc['year'] != '')) $ris .= "PY  - " . $doc['year'] . PHP_EOL;
        if (isset($doc['number']) and ($doc['number'] != '')) $ris .= "IS  - " . $doc['number'] . PHP_EOL;
        if (isset($doc['pages']) and ($doc['pages'] != '')) $ris .= "SP  - " . $doc['pages'] . PHP_EOL;
        if (isset($doc['volume']) and ($doc['volume'] != '')) $ris .= "VL  - " . $doc['volume'] . PHP_EOL;
        if (isset($doc['doi']) and ($doc['doi'] != '')) $ris .= "DO  - " . $doc['doi'] . PHP_EOL;
        if (isset($doc['isbn']) and ($doc['isbn'] != '')) $ris .= "SN  - " . $doc['isbn'] . PHP_EOL;
        if (isset($doc['publisher']) and ($doc['publisher'] != '')) $ris .= "PB  - " . $doc['publisher'] . PHP_EOL;
        if (isset($doc['book']) and ($doc['book'] != '')) $ris .= "BT  - " . $doc['book'] . PHP_EOL;
        if (isset($doc['chapter']) and ($doc['chapter'] != '')) $ris .= "BT  - " . $doc['chapter'] . PHP_EOL;
        if (isset($doc['abstract']) and ($doc['abstract'] != '')) $ris .= "AB  - " . $doc['abstract'] . PHP_EOL;
        if (isset($doc['keywords']) and ($doc['keywords'] != '')) {
            foreach ($doc['keywords'] as $keyword) $ris .= "KW  - " . $keyword . PHP_EOL;
        }
        if (isset($doc['location']) && !empty($doc['location'])) $ris .= "CY  - " . $doc['location'] . PHP_EOL;
        $ris .= "ER  - " . PHP_EOL;
        return $ris;
    }

    public function getTitle()
    {
        $template = $this->subtypeArr['template']['title'] ?? '{title}';
        return $this->template($template);
    }

    public function getSubtitle()
    {
        $template = $this->subtypeArr['template']['subtitle'] ?? '{authors}';
        return $this->template($template);
    }

    public function formatPortal()
    {
        $this->full = true;
        $this->subtitle = "";
        $template = $this->subtypeArr['template']['title'] ?? '{title}';
        $this->title = $this->template($template);


        $template = $this->subtypeArr['template']['subtitle'] ?? '{authors}';
        $this->subtitle .= "<p class='text-muted'>";
        $this->subtitle .= $this->template($template);
        // $this->subtitle .= $this->get_field('file-icons');
        if (!empty($this->appendix)) {
            $this->subtitle .= "<br><small style='color:#878787;'>" . $this->appendix . "</small>";
        }
        $this->subtitle .= "</p>";

        return "<h2 class='title'>$this->title</h2>$this->subtitle";
    }

    private function template($template)
    {

        $vars = array();

        $pattern = "/{([^}]*)}/";
        preg_match_all($pattern, $template, $matches);
        // dump($matches[1], true);

        foreach ($matches[1] as $module) {
            $m = explode('|', $module, 2);
            $value = $this->get_field($m[0]);

            if (empty($value) && count($m) == 2) {
                $value = $m[1];
            }
            $vars['{' . $module . '}'] = ($value);
        }

        $line = strtr($template, $vars);

        $line = preg_replace('/\(\s*\)/', '', $line);
        $line = preg_replace('/\[\s*\]/', '', $line);
        $line = preg_replace('/\s+[,]+/', ',', $line);
        $line = preg_replace('/([?!,])\.+/', '$1', $line);
        $line = preg_replace('/,\s\./', '.', $line);
        $line = preg_replace('/,\s:/', ',', $line);
        $line = preg_replace('/\s\./', '.', $line);
        $line = preg_replace('/\.+/', '.', $line);
        $line = preg_replace('/\(:\s*/', '(', $line);
        $line = preg_replace('/\s+/', ' ', $line);
        $line = preg_replace('/,\s*$/', '', $line);
        $line = preg_replace('/<br *\/?>,\s*/', '<br />', $line);

        return $line;
    }
}
