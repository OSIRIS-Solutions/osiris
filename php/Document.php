<?php

/**
 * Document class
 * 
 * This class is responsible for formatting of activities
 */

require_once "Settings.php";
require_once "DB.php";
require_once "Schema.php";
require_once "Country.php";
require_once "Organization.php";
require_once "Vocabulary.php";

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
    public $usecase = "";
    public $full = false;

    private $schemaType = null;
    public $schema = [];
    private $DB = null;

    public $custom_fields = [];
    public $custom_field_values = [];

    private $lang = 'en';

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
        "editors-first-last-amp-ed"  => ["editors"],
        "authors-last-f-etal6" => ["authors"],
        "authors-last-first-amp+comma" => ["authors"],
        "editors-f.-last-semicolon-Eds" => ["authors"],
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
        "doi-link" => ["doi"],
        "doi-text" => ["doi"],
        "edition" => ["edition"],
        "edition-ed" => ["edition"],
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
        "link-full" => ["link"],
        "software-link" => ["link"],
        "location" => ["location"],
        "magazine" => ["magazine"],
        "online-ahead-of-print" => ["epub"],
        "openaccess" => ["open_access"],
        "openaccess-text" => ["open_access"],
        "openaccess-status" => ["oa_status"],
        "organization" => ["organization"],
        "organization-location" => ["organization"],
        "organizations" => ["organizations"],
        "pages" => ["pages"],
        "pages-pp" => ["pages"],
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


    function __construct($highlight = true, $usecase = '')
    {
        parent::__construct();
        $this->highlight = $highlight;
        $this->usecase = $usecase;
        $this->DB = new DB;

        $fields = $this->DB->db->adminFields->find()->toArray();
        $this->custom_fields = array_column($fields, null, 'id');
        $this->custom_field_values = array_column($fields, 'values', 'id');

        $this->lang = lang('en', 'de');
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

    private function lang($en, $de = null)
    {
        if ($de === null) return $en;
        if ($this->lang == "de") return $de;
        return $en;
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
        $icon = 'folder-open';

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
        $name = $this->lang("Other", "Sonstiges");
        if (!empty($this->subtypeArr) && isset($this->subtypeArr['name'])) {
            $name = $this->lang(
                $this->subtypeArr['name'],
                $this->subtypeArr['name_de'] ?? $this->subtypeArr['name']
            );
        } elseif (!empty($this->typeArr) && isset($this->typeArr['name'])) {
            $name = $this->lang(
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
        $name = $this->lang("Other", "Sonstiges");
        if (!empty($this->typeArr) && isset($this->typeArr['name'])) {
            $name = $this->lang(
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

    function formatAuthors($raw_authors, $format = 'last, f.', $separator = 'and')
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
                    if (isset($a['user']) && !empty($a['user']))
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
        if (!$this->full && $n > 9) {
            $append = " et al.";
            $separator = ", ";
        }
        return Document::commalist($authors, $separator) . $append;
    }

    public function getAffiliationTypes($key = 'authors')
    {
        if (!isset($this->doc[$key]) || empty($this->doc[$key])) {
            return ['unspecified'];
        }
        $authors = DB::doc2Arr($this->doc[$key]);
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

        if (empty($from)) {
            return Document::format_date($to);
        }
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

    public function format_month($month)
    {
        if (empty($month)) return '';
        $month = intval($month);
        $array = [
            1 => $this->lang("January", "Januar"),
            2 => $this->lang("February", "Februar"),
            3 => $this->lang("March", "März"),
            4 => $this->lang("April"),
            5 => $this->lang("May", "Mai"),
            6 => $this->lang("June", "Juni"),
            7 => $this->lang("July", "Juli"),
            8 => $this->lang("August"),
            9 => $this->lang("September"),
            10 => $this->lang("October", "Oktober"),
            11 => $this->lang("November"),
            12 => $this->lang("December", "Dezember")
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

    public function translateCategory($cat)
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
                return $this->lang('Lecture', 'Vorlesung');
            case 'practical':
                return $this->lang('Practical course', 'Praktikum');
            case 'practical-lecture':
                return $this->lang('Lecture and practical course', 'Vorlesung und Praktikum');
            case 'lecture-seminar':
                return $this->lang('Lecture and seminar', 'Vorlesung und Seminar');
            case 'practical-seminar':
                return $this->lang('Practical course and seminar', 'Praktikum und Seminar');
            case 'lecture-practical-seminar':
                return $this->lang('Lecture, seminar, practical course', 'Vorlesung, Seminar und Praktikum');
            case 'seminar':
                return $this->lang('Seminar');
            case 'other':
                return $this->lang('Other', 'Sonstiges');
            case "doctoral student":
                return $this->lang('Doctoral Student', 'Doktorand:in');
            case "master student":
                return $this->lang('Master Student', 'Masterstudent');
            case "bachelor student":
                return $this->lang('Bachelor Student', 'Bachelorstudent');
            case "intern":
                return $this->lang('Intern', 'Praktikant');
            default:
                return $cat;
        }
    }

    public function getSupervisorRole($role)
    {
        $roles = [
            'supervisor' => $this->lang('Supervisor', 'Betreuer'),
            'first-reviewer' => $this->lang('First reviewer', 'Erster Gutachter'),
            'second-reviewer' => $this->lang('Second reviewer', 'Zweiter Gutachter'),
            'third-reviewer' => $this->lang('Third reviewer', 'Dritter Gutachter'),
            'committee-member' => $this->lang('Committee member', 'Ausschussmitglied'),
            'chair' => $this->lang('Chair', 'Vorsitzender'),
            'mentor' => $this->lang('Mentor', 'Mentor'),
            'other' => $this->lang('Other', 'Sonstiges')
        ];
        if (isset($roles[$role])) {
            return $roles[$role];
        }
        return $role;
    }

    private function getVal($field, $default = '')
    {
        if ($default === '' && $this->usecase == 'list') $default = '-';
        if (!array_key_exists($field, $this->doc)) return $default;
        return ($this->doc[$field] ?? '');
    }

    private function formatAuthorsNew($module)
    {
        $isEditors = str_starts_with($module, 'editors-');
        $authorKey = $isEditors ? 'editors' : 'authors';

        $authors = DB::doc2Arr($this->getVal($authorKey, []));
        if (empty($authors)) return '';
        $N = count($authors);

        $formatParts = explode('-', str_replace(['authors-', 'editors-'], '', $module));

        // Default-Werte
        $nameFormat = 'last f.'; // z. B. last-f, f.-last, etc.
        $delimiter = ', ';
        $lastSeparator = ' and ';
        $etalLimit = null;
        $ellipsesLimit = null;
        $suffix = "";
        $aoi_format = $this->get('affiliation_format', 'bold');
        if ($this->highlight !== true) {
            $aoi_format = 'none';
        }

        $nameparts = ['last f.', 'last f', 'f last', 'f. last', 'last first', 'first last', 'last, f.', 'last, f', 'last, first'];
        foreach ($formatParts as $part) {
            if (in_array($part, $nameparts)) {
                $nameFormat = $part;
                break;
            }
        }

        // format the parts according to the module name
        foreach ($formatParts as $part) {
            if ($part === 'amp') {
                $lastSeparator = ' & ';
            } elseif ($part === 'amp+comma') {
                $lastSeparator = ', & ';
            } elseif ($part === 'semicolon') {
                $delimiter = '; ';
            } elseif (str_starts_with($part, 'etal')) {
                $etalLimit = (int) str_replace('etal', '', $part);
            } elseif (str_starts_with($part, 'ellipses')) {
                $ellipsesLimit = (int) str_replace('ellipses', '', $part);
            } else if (in_array($part, ['eds', 'ed', 'Eds', 'Ed'])) {
                if ($part === 'Eds' || $part === 'eds') {
                    $suffix = ' (' . $part . '.)';
                } elseif ($N == 1) {
                    $suffix = ' (' . $part . '.)';
                } else {
                    $suffix = ' (' . $part . 's.)';
                }
            }
        }

        // format the authors
        $formatted = [];
        foreach ($authors as $person) {
            $first = $person['first'] ?? '';
            $last = $person['last'] ?? '';
            // $initial = $first ? mb_substr($first, 0, 1) : '';
            $initial = '';
            $initialDot = '';
            if ($first) :
                foreach (preg_split("/(\s+| |-|\.)/u", $first, -1, PREG_SPLIT_DELIM_CAPTURE) as $name) {
                    if (empty(trim($name)) || $name == '.' || $name == ' ') continue;
                    if ($name == '-') {
                        $initial .= '-';
                        $initialDot .= '-';
                    } else {
                        $name = mb_substr($name, 0, 1);
                        $initial .= "" . $name;
                        $initialDot .= "" . $name . '.';
                    }
                }
            endif;
            $author = '';
            switch ($nameFormat) {
                case 'last f':
                    $author = "$last $initial";
                    break;
                case 'f last':
                    $author = "$initial $last";
                    break;
                case 'f. last':
                    $author = "$initialDot $last";
                    break;
                case 'last first':
                    $author = "$last, $first";
                    break;
                case 'first last':
                    $author = "$first $last";
                    break;
                case 'last, f.':
                    $author = "$last, $initialDot";
                    break;
                case 'last, f':
                    $author = "$last, $initial";
                    break;
                case 'last, first':
                    $author = "$last, $first";
                    break;
                default:
                    $author = "$last $initialDot";
                    break;
            }

            // markup of affiliated authors
            if ($aoi_format == 'none') {
                // do nothing
            } elseif (($this->highlight === true && ($person['aoi'] ?? false)) || ($person['user'] === $this->highlight)) {
                if ($this->usecase == 'web') {
                    if (isset($person['user']) && !empty($person['user'])) {
                        $author = "<a href='" . ROOTPATH . "/profile/" . $person['user'] . "'>$author</a>";
                    }
                } else if ($aoi_format == 'bold') {
                    $author = "<b>$author</b>";
                } else if ($aoi_format == 'italic') {
                    $author = "<i>$author</i>";
                } else if ($aoi_format == 'bold-underline') {
                    $author = "<b><u>$author</u></b>";
                } else if ($aoi_format == 'italic-underline') {
                    $author = "<i><u>$author</u></i>";
                } else if ($aoi_format == 'underline') {
                    $author = "<u>$author</u>";
                } else if ($aoi_format == 'bold-italic') {
                    $author = "<b><i>$author</i></b>";
                }
            }

            $formatted[] = $author;
        }

        if ($etalLimit === null && $this->usecase == 'web') {
            $etalLimit = 12; // default limit for web use case
        }

        if ($etalLimit !== null  && $N > $etalLimit) {
            $formatted = array_slice($formatted, 0, $etalLimit);
            $result = implode($delimiter, $formatted);
            $result .=  ' et al.';
            return $result . $suffix;
        } else if ($ellipsesLimit !== null && $N > $ellipsesLimit) {
            $lastAuthor = array_pop($formatted);
            $formatted = array_slice($formatted, 0, $ellipsesLimit - 1);
            $result = implode($delimiter, $formatted);
            $result .= '&period;&period;&period;' . $lastAuthor;
            return $result . $suffix;
        }
        $last = array_pop($formatted);
        $result = $formatted ? implode($delimiter, $formatted) . $lastSeparator . $last : $last;
        return $result . $suffix;
    }

    public function get_field($module, $default = '')
    {
        // dump($module);
        // dump($this->getVal($module, $default));
        if ($this->usecase == 'list') $default = '-';
        if (str_starts_with($module, 'authors-') || str_starts_with($module, 'editors-')) {
            return $this->formatAuthorsNew($module);
        }
        switch ($module) {
            case "affiliation": // ["book"],
                return $this->getVal('affiliation');
            case "authors": // ["authors"],
            case "supervisor": // ["authors"],
            case "scientist": // ["authors"],
                return $this->formatAuthorsNew('authors-last-f.');
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
                        $start = $this->format_month($this->doc['start']['month']) . ' ' . $this->doc['start']['year'];
                        $end = $this->format_month($this->doc['end']['month']) . ' ' . $this->doc['end']['year'];
                        if ($start == $end) return $start;
                        $date = $this->lang("from ", "von ") . $start . $this->lang(" until ", " bis ") . $end;
                    } else {
                        $date = $this->lang("since ", "seit ");
                        $date .= $this->format_month($this->doc['start']['month']) . ' ' . $this->doc['start']['year'];
                    }
                    return $date;
                }
                return $this->fromToDate($this->doc, null);
            case "year": // ["year", "month", "day"],
                return $this->getVal('year');
            case "month": // ["year", "month", "day"],
                return $this->format_month($this->getVal('month'));
            case "details": // ["details"],
                return $this->getVal('details');
            case "doctype": // ["doc_type"],
                return $this->getVal('doc_type');
            case "doi": // ["doi"],
            case "doi-link": // ["doi"],
            case "doi-text": // ["doi"],
                $val = $this->getVal('doi');
                if ($val == $default || empty($val)) return $default;
                if ($module == 'doi-link') {
                    return "<a target='_blank' href='https://doi.org/$val'>https://doi.org/$val</a>";
                } elseif ($module == 'doi-text') {
                    return $val;
                }
                return "DOI: <a target='_blank' href='https://doi.org/$val'>$val</a>";
            case "edition": // ["edition"],
            case "edition-ed": // ["edition"],
                $val = $this->getVal('edition', $default);
                if ($val != $default) {
                    if ($val == 1) $val .= "st";
                    elseif ($val == 2) $val .= "nd";
                    elseif ($val == 3) $val .= "rd";
                    else $val .= "th";
                    if ($module == "edition-ed") {
                        $val .= " ed.";
                    }
                }
                return $val;
            case "editor": // ["editors"],
            case "editors": // ["editors"],
                return $this->formatAuthorsNew('editors-last-f.');
            case "editorial": // ["editor_type"],
                return $this->getVal('editor_type');
            case "file-icons":
                if (!($this->typeArr['upload'] ?? true)) return '';
                if ($this->usecase == 'portal') return '';
                $files = '';
                foreach ($this->getVal('files', array()) as $file) {
                    $icon = getFileIcon($file['filetype']);
                    $files .= " <a href='$file[filepath]' target='_blank' data-toggle='tooltip' data-title='$file[filetype]: $file[filename]' class='file-link'><i class='ph ph-file ph-$icon'></i></a>";
                }
                return $files;
            case 'funding_type':
                $Vocabulary = new Vocabulary();
                $funder = $this->getVal('funding_type', null);
                return $Vocabulary->getValue('funding-type', $funder);
            case "guest": // ["category"],
                return $this->translateCategory($this->getVal('category'));
            case "isbn": // ["isbn"],
                return $this->getVal('isbn');
            case "issn": // ["issn"],
                $issn = $this->getVal('issn', null);
                if (empty($issn)) return $default;
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
            case "link-full":
            case "link-short":
            case "software-link": // ["link"],
                $val = $this->getVal('link');
                if (empty($val) || $val == $default) return $default;
                if ($module != 'link-short' || $module == 'link-full' || $this->usecase != 'list') {
                    return "<a target='_blank' href='$val'>$val</a>";
                }
                $short_url = str_replace(['https://', 'http://'], '', $val);
                if (strlen($short_url) > 50) {
                    $short_url = substr($short_url, 0, 50) . '...';
                }
                return "<a target='_blank' href='$val'>$short_url</a>";
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

            case "organization": // ["organization"],
                $value = $this->getVal('organization');
                if (empty($value)) return $default;
                $org = $this->DB->db->organizations->findOne(['_id' => DB::to_ObjectID($value)]);
                if (empty($org)) return $value;
                if ($this->usecase == 'web') {
                    return '<a href="' . ROOTPATH . '/organizations/view/' . $org['_id'] . '">' . $org['name'] . '</a>';
                }
                if ($this->usecase == 'list') {
                    return '
                        <a href="' . ROOTPATH . '/organizations/view/' . $org['_id'] . '" class="module ">
                            <h6 class="m-0">' . htmlspecialchars($org['name']) . '</h6>
                            <ul class="horizontal mb-0">
                                <li> <i class="ph ph-map-pin-area"></i> ' . htmlspecialchars($org['location']) . '</li>
                                <li>' . Organization::getIcon($org['type'] ?? '') .  ' ' . ($org['type'] ?? '') . '</li>
                            </ul>
                        </a>';
                }
                return $org['name'];
            case "organization-location":
                $value = $this->getVal('organization');
                if (empty($value)) return $default;
                $org = $this->DB->db->organizations->findOne(['_id' => DB::to_ObjectID($value)]);
                if (empty($org)) return $value;
                return $org['location'] ?? $default;
            case 'organizations':
                $value = $this->getVal('organizations', []);
                if (empty($value)) return $default;
                $orgs = [];
                foreach ($value as $org_id) {
                    $org = $this->DB->db->organizations->findOne(['_id' => DB::to_ObjectID($org_id)]);
                    if (empty($org)) continue;
                    if ($this->usecase == 'list') {
                        $orgs[] = '
                            <a href="' . ROOTPATH . '/organizations/view/' . $org['_id'] . '" class="module ">
                                <h6 class="m-0">' . htmlspecialchars($org['name']) . '</h6>
                                <ul class="horizontal mb-0">
                                    <li> <i class="ph ph-map-pin-area"></i> ' . htmlspecialchars($org['location']) . '</li>
                                    <li>' . Organization::getIcon($org['type'] ?? '') .  ' ' . ($org['type'] ?? '') . '</li>
                                </ul>
                            </a>';
                    } elseif ($this->usecase == 'web') {
                        $orgs[] = '<a href="' . ROOTPATH . '/organizations/view/' . $org['_id'] . '">' . htmlspecialchars($org['name']) . '</a>';
                    } else {
                        $orgs[] = htmlspecialchars($org['name']);
                    }
                }
                if (empty($orgs)) return $default;
                if ($this->usecase == 'list') {
                    return implode('', $orgs);
                }
                return implode(', ', $orgs);
            case "peer-reviewed":
                $val = $this->getVal('peer_reviewed', false);
                if ($this->usecase == 'list')
                    return bool_icon($val);
                if ($val != $default)
                    return "<span style='color:#63a308;'>[Peer-reviewed]</span>";
                else return '';
            case "pages": // ["pages"],
                return $this->getVal('pages');
            case "pages-pp": // ["pages"],
                $val = $this->getVal('pages', $default);
                if ($val == $default) return $val;
                if (str_contains($val, '-')) {
                    return  "pp. " . $val;
                }
                return "p. " . $val;
            case "person": // ["name", "affiliation", "academic_title"],
                return $this->getVal('name');
            case "publisher":; // ["publisher"],
                return $this->getVal('publisher');
            case "pubmed": // ["pubmed"],
                $val = $this->getVal('pubmed');
                if ($val == $default) return $val;
                return "<a target='_blank' href='https://pubmed.ncbi.nlm.nih.gov/$val'>$val</a>";
            case "pubtype": // ["pubtype"],
                switch ($this->getVal('pubtype')) {
                    case 'article':
                        return "Journal article (refereed)";
                    case 'book':
                        return $this->lang('Book', 'Buch');
                    case 'chapter':
                        return $this->lang('Book chapter', 'Buchkapitel');
                    case 'preprint':
                        return "Preprint (non refereed)";
                    case 'conference':
                        return $this->lang('Conference preceedings', 'Konferenzbeitrag');
                    case 'magazine':
                        return $this->lang('Magazine article (non refereed)', 'Magazin-Artikel (non-refereed)');
                    case 'dissertation':
                        return $this->lang('Thesis');
                    case 'others':
                        return $this->lang('Others', 'Weiteres');
                    default:
                        return $this->getVal('pubtype');
                }
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
                        return $this->lang('Doctoral Thesis', 'Doktorarbeit');
                    case 'master':
                        return $this->lang('Master Thesis', 'Masterarbeit');
                    case 'bachelor':
                        return $this->lang('Bachelor Thesis', 'Bachelorarbeit');
                    case 'thesis': // ["category"],
                        return $this->lang('Thesis', 'Abschlussarbeit');
                    case 'diploma':
                        return $this->lang('Diploma Thesis', 'Diplomarbeit');
                    case 'habilitation':
                        return $this->lang('Habilitation Thesis', 'Habilitationsschrift');
                    default:
                        return $this->lang('Thesis', 'Abschlussarbeit');
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
                return $this->DB->getCountry($code, $this->lang('name', 'name_de'));
            case 'countries':
                $countries = DB::doc2Arr($this->getVal('countries', []));
                if (empty($countries)) return '';
                $country_names = array_map(function ($code) {
                    return $this->DB->getCountry($code, $this->lang('name', 'name_de'));
                }, $countries);
                return implode(', ', $country_names);
            case "gender":
                switch ($this->getVal('gender')) {
                    case 'f':
                        return $this->lang('female', 'weiblich');
                    case 'm':
                        return $this->lang('male', 'männlich');
                    case 'd':
                        return $this->lang('non-binary', 'divers');
                    case '-':
                        return $this->lang('not specified', 'keine Angabe');
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
                if (isset($this->custom_fields[$module])) {
                    $val = $this->customVal($this->custom_fields[$module]);
                } else {
                    $val = $this->getVal($module, $default);
                }
                // only in german because standard is always english
                if ($this->lang('en', 'de') == 'de' && isset($this->custom_field_values[$module])) {
                    if (is_array($val)) {
                        $values = [];
                        foreach ($val as $v) {
                            // check if the value is in the custom field values
                            foreach ($this->custom_field_values[$module] as $field) {
                                if ($v == $field[0] ?? '') {
                                    $values[] = $this->lang(...$field);
                                    continue 2;
                                }
                            }
                            $values[] = $v;
                        }
                        return implode(", ", $values);
                    } else {
                        foreach ($this->custom_field_values[$module] as $field) {
                            if ($val == $field[0] ?? '') return $this->lang(...$field);
                        }
                    }
                }

                if ($val === true || $val === false) {
                    if ($this->usecase == 'list') return bool_icon($val);
                    $field = $this->custom_fields[$module];
                    if (!isset($field['name'])) {
                        $field['name'] = $module;
                    }
                    $label = '[' . $this->lang($field['name'], $field['name_de'] ?? null) . ']';
                    return $val ? $label : '';
                }
                if (is_array($val)) return implode(", ", $val);
                return $val;
        }
    }

    public function customVal($field)
    {
        $format = $field['format'] ?? '';
        $default = $field['default'] ?? '';
        if (!array_key_exists($field['id'], $this->doc)) return $default;

        $val = ($this->doc[$field['id']] ?? '');
        if ($format == 'date') {
            return Document::format_date($val);
        }
        if ($format == 'url' && !empty($val) && $val != '-') {
            return "<a href='$val' target='_blank' class='link'>$val</a>";
        }
        return $val;
    }

    public function format($lang = null)
    {
        $this->full = true;
        if (empty($this->usecase)) {
            $this->usecase = 'print';
        }
        $template = $this->subtypeArr['template']['print'] ?? '{title}';

        if ($lang !== null) {
            $this->lang = $lang;
        }
        $line = $this->template($template);
        if (!empty($this->appendix)) {
            $line .= "<br><small style='color:#878787;'>" . $this->appendix . "</small>";
        }
        if ($lang !== null) {
            $this->lang = lang('en', 'de');
        }
        return $line;
    }

    public function formatShort($link = true, $lang = null)
    {
        $this->full = false;
        if (empty($this->usecase)) {
            $this->usecase = 'web';
        }
        if ($lang !== null) {
            $this->lang = $lang;
        }
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
        if ($lang !== null) {
            $this->lang = lang('en', 'de');
        }
        return $line;
    }

    public function formatPortfolio($lang = null)
    {
        $this->full = false;
        $this->usecase = 'portal';
        if ($lang !== null) {
            $this->lang = $lang;
        }
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
        if ($lang !== null) {
            $this->lang = lang('en', 'de');
        }
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

    public function getTitle($temporary_usecase = null)
    {
        $usecase = $this->usecase;
        if (!empty($temporary_usecase)) {
            $this->usecase = $temporary_usecase;
        }
        $template = $this->subtypeArr['template']['title'] ?? '{title}';
        $result = $this->template($template);
        $this->usecase = $usecase;
        return $result;
    }

    public function getSubtitle($temporary_usecase = null)
    {
        $usecase = $this->usecase;
        if (!empty($temporary_usecase)) {
            $this->usecase = $temporary_usecase;
        }
        $template = $this->subtypeArr['template']['subtitle'] ?? '{authors}';
        $result = $this->template($template);

        $this->usecase = $usecase;
        return $result;
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

        foreach ($matches[1] as $match) {
            $m = explode('|', $match, 2);
            $value = $this->get_field($m[0]);

            if (empty($value) && count($m) == 2) {
                $value = $m[1];
            }
            $vars['{' . $match . '}'] = ($value);
        }

        $pattern = "/%([^%]*)%/";
        preg_match_all($pattern, $template, $matches);

        foreach ($matches[1] as $match) {
            $m = explode(' ', $match, 2);
            $fields = $m[0];          // e.g. field1&field2 or field1|field2
            $text = $m[1] ?? '';

            // Check if multiple fields are used
            if (strpos($fields, '&') !== false) {
                $allFilled = true;
                foreach (explode('&', $fields) as $f) {
                    $value = trim($this->get_field($f));
                    if (empty($value) || $value == '-') {
                        $allFilled = false;
                        break;
                    }
                }
                if (!$allFilled) $text = '';
            } elseif (strpos($fields, '|') !== false) {
                $anyFilled = false;
                foreach (explode('|', $fields) as $f) {
                    $value = trim($this->get_field($f));
                    if (!empty($value) && $value != '-') {
                        $anyFilled = true;
                        break;
                    }
                }
                if (!$anyFilled) $text = '';
            } else {
                // single field as before
                $value = trim($this->get_field($fields));
                if (empty($value) || $value == '-') $text = '';
            }

            $vars['%' . $match . '%'] = $text;
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
        $line = preg_replace('/\s*\(,?\s*\)/', '', $line);
        $line = preg_replace('/\s+/', ' ', $line);
        $line = preg_replace('/,\s*$/', '', $line);
        $line = preg_replace('/<br *\/?>,\s*/', '<br />', $line);

        return $line;
    }
}
