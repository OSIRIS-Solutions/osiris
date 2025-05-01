<?php

/**
 * MongoDB connection
 *
 * This file is part of the OSIRIS package 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @link		https://github.com/JKoblitz/osiris
 * @version		1.2
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

require_once BASEPATH . '/vendor/autoload.php';

use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\BSON\Regex;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
use MongoDB\Driver\Cursor;

require_once 'Document.php';

if (!defined('DB_STRING')) {
    die("DB settings are missing in the CONFIG.php file. Add the DB_STRING constant as defined in the config documentation.");
}


/**
 * Class for MongoDB connection
 *
 * The basic connection to the database is established. 
 * The class provides a number of helper methods to interact 
 * with the data.
 */
class DB
{

    public $db = null;

    public function __construct()
    {
        $dbname = 'osiris';
        if (defined('DB_NAME') && !empty(DB_NAME)) $dbname = DB_NAME;

        $mongoDB = new Client(DB_STRING);

        $this->db = $mongoDB->$dbname;
    }

    /**
     * Initialize current user
     *
     * @return array Complete User Information.
     */
    public function initUser()
    {
        $USER = array();
        if (!empty($_SESSION['username'])) {
            $USER = $this->getPerson($_SESSION['username']);
            // set standard values
            if ($_SESSION['username'] === ADMIN) {
                $USER['roles'][] = 'admin';
            }
            if (!isset($USER['display_activities'])) $USER['display_activities'] = 'web';
        }
        return $this->doc2Arr($USER);
    }

    /**
     * Converts string to MongoDB ObjectId
     *
     * @param string $id MongoDB ID string.
     * @return ObjectId Converted ObjectId.
     */
    public static function to_ObjectID($id)
    {
        if (empty($id)) return null;
        if ($id instanceof ObjectId) {
            return $id;
        }
        if (DB::is_ObjectID($id)) {
            return new ObjectId($id);
        }
        return intval($id);
    }

    /**
     * Checks if var is MongoDB ObjectId using regex
     *
     * @param string $id MongoDB ID string.
     * @return bool true if valid ObjectID.
     */
    public static function is_ObjectID($id)
    {
        if (empty($id)) return false;
        if (preg_match("/^[0-9a-fA-F]{24}$/", $id)) {
            return true;
        }
        return false;
    }

    public static function getDate($doc)
    {
        $date = $doc['year'] ?? '';
        if (isset($doc['month'])) $date .= '-' . $doc['month'];
        if (isset($doc['day'])) $date .= '-' . $doc['day'];
        return $date;
    }

    /**
     * Convert MongoDB document to array.
     *
     * @param $doc MongoDB Document.
     * @return array Document array.
     */
    public static function doc2Arr($doc)
    {
        if (empty($doc)) return array();
        if (is_array($doc)) return $doc;
        if ($doc instanceof BSONArray) {
            return $doc->bsonSerialize();
        }
        if ($doc instanceof BSONDocument) {
            return iterator_to_array($doc);
        }
        if ($doc instanceof Cursor) {
            return DB::doc2Arr($doc->toArray());
        }
        return $doc;
    }

    function notifications($force = false)
    {
        $notifications = [
            'approval' => lang('Approval of activities', 'Freigabe von Aktivitäten'),
            'epub' => '<em>Online ahead of print</em>-' . lang('Publications', 'Publikationen'),
            'status' => lang('Expired status', 'Abgelaufener Status'),
            'openend' => lang('Ongoing activities', 'Laufende Aktivitäten'),
            'project-open' => lang('Open project applications', 'Offene Projektanträge'),
            'project-end' => lang('Expired projects', 'Abgelaufene Projekte'),
            'infrastructure' => lang('Updating Infrastructures', 'Infrastrukturen aktualisieren'),
        ];
        $now = time();
        $user = $_SESSION['username'] ?? null;
        $last = $_SESSION['last_notification_check'] ?? 0;
        $hasNotification = $_SESSION['has_notifications'] ?? false;
        $issues = array();
        if (empty($user)) return $issues;

        // check every minute if user has notifications
        if ($now - $last > 60 || $force) {
            // check if new issues were added for user
            $hasNotification = 0;

            $issues_raw = $this->getUserIssues($user);
            if (!empty($issues_raw)) {
                $issues['activity'] = [
                    'name' => lang('Activities', 'Aktivitäten'),
                    'count' => 0,
                    'key' => 'activity',
                    'values' => []
                ];
                foreach ($issues_raw as $key => $val) {
                    $val = count($val);
                    if ($val == 0) continue;
                    $issues['activity']['values'][] = [
                        'name' => $notifications[$key] ?? $key,
                        'count' => $val,
                        'key' => $key
                    ];
                    $issues['activity']['count'] += $val;
                }
                $hasNotification += $issues['activity']['count'];
            }

            // check for queue
            $queue = $this->db->queue->count(['authors.user' => $user, 'duplicate' => ['$exists' => false]]);
            if ($queue !== 0) {
                $issues['queue'] = [
                    'name' => lang('Queue', 'Warteschlange'),
                    'count' => $queue,
                    'key' => 'queue',
                ];
                $hasNotification += $queue;
            }

            // check for new version
            $scientist = $this->db->persons->findOne(['username' => $user], ['projection' => ['lastversion' => 1, 'approved' => 1, 'roles' => 1]]);
            if (lang('en', 'de') == 'de' && (empty($scientist['lastversion'] ?? '') || $scientist['lastversion'] !== OSIRIS_VERSION)) {
                $issues['version'] = [
                    'name' => lang('New version available', 'Neue Version verfügbar'),
                    'count' => 1,
                    'key' => 'version',
                ];
                $hasNotification += 1;
            }

            $reportingEnabled = $this->db->adminFeatures->findOne(['feature'=>'quarterly-reporting']);
            $reportingEnabled = $reportingEnabled['enabled'] ?? true;
            if ($reportingEnabled) {
                // check for approval of activities
                $approvedQ = DB::doc2Arr($scientist['approved'] ?? []);
                $roles = DB::doc2Arr($scientist['roles'] ?? []);
                $lastquarter = $this->getLastQuarter();
                if (in_array('scientist', $roles) && !in_array($lastquarter, $approvedQ)) {
                    $issues['approval'] = [
                        'name' => lang('Approval of the quarter', 'Freigabe des Quartals'),
                        'count' => 1,
                        'key' => $lastquarter,
                    ];
                    $hasNotification += 1;
                }
            }
            if ($hasNotification > 0) {
                // save in DB
                $this->db->notifications->updateOne(
                    ['user' => $user],
                    ['$set' => ['issues' => $issues]],
                    ['upsert' => true]
                );
            } else {
                $hasNotification = false;
                // delete from DB
                $this->db->notifications->deleteOne(['user' => $user]);
            }
            // Session aktualisieren
            $_SESSION['last_notification_check'] = $now;
            $_SESSION['has_notifications'] = $hasNotification;
        } else {
            // check if user has notifications in DB
            $doc = $this->db->notifications->findOne(['user' => $user]);
            if (!empty($doc)) {
                $issues = DB::doc2Arr($doc['issues']);
                // $hasNotification = array_sum(array_map("count", $issues));
            } else {
                // $hasNotification = false;
            }
        }
        return $issues;
    }

    function getLastQuarter()
    {
        $Q = CURRENTQUARTER - 1;
        $Y = CURRENTYEAR;
        if ($Q < 1) {
            $Q = 4;
            $Y -= 1;
        }
        return $Y . "Q" . $Q;
    }

    function printProfilePicture($user, $class = "")
    {
        $img = $this->db->userImages->findOne(['user' => $user]);
        if (empty($img)) return ' <img src="' . ROOTPATH . '/img/no-photo.png" alt="Profilbild" class="' . $class . '">';
        if ($img['ext'] == 'svg') {
            $img['ext'] = 'svg+xml';
        }
        return '<img src="data:image/' . $img['ext'] . ';base64,' . base64_encode($img['img']) . ' " class="' . $class . '" />';
    }

    // methods to query documents
    // function getAllPersons(bool $only_user=false){
    //     $filter = [];
    //     if ($only_user) $filter = ['username' => ['$ne'=>null]];
    //     return $this->db->persons->find($filter);
    // }

    /**
     * Get connected document from other collection
     *
     * @param string $type type of collection to connect to.
     * @param string $id MongoDB ID string.
     * @return array connected document.
     */
    public function getConnected(string $type, $id)
    {
        $con = [];
        $id = $this->to_ObjectID($id);
        if ($type == 'journal') {
            $con = $this->db->journals->findOne(['_id' => $id]);
        } elseif ($type == 'teaching') {
            $con = $this->db->teaching->findOne(['_id' => $id]);
        } elseif ($type == 'project') {
            $con = $this->db->projects->findOne(['_id' => $id]);
        } elseif ($type == 'person') {
            $con = $this->db->persons->findOne(['_id' => $id]);
        } elseif ($type == 'activity') {
            $con = $this->db->activities->findOne(['_id' => $id]);
        } elseif ($type == 'conference') {
            $con = $this->db->conferences->findOne(['_id' => $id]);
        }
        return $this->doc2Arr($con);
    }



    /**
     * Get username from first and last name
     *
     * @param string $last last name of user.
     * @param string $first first name of user.
     * @return string|null username or null if user not found.
     */
    public function getUserFromName($last, $first)
    {
        $last = trim($last);
        $first = trim($first);
        if (strlen($first) == 1) $first .= ".";

        // make sure that collation does not mingle here
        $options = ['collation' => ['locale' => 'en', 'strength' => 1]];

        try {
            // $veryfirst = explode(' ', $first)[0];
            $abbr = $this->abbreviateName($first);
            // dump($abbr);
            // $regex = new Regex('^' . $veryfirst);
            $user = $this->db->persons->findOne([
                '$or' => [
                    // if user has not set alternative names yet, 'names'=>['$exist'=>false]
                    ['last' => $last, 'first' => $first, 'names' => ['$exist' => false]],
                    // otherwise, we respect the names that have been set
                    ['names' => "$last, $first"],
                    ['names' => "$last, $abbr"],
                ]
            ], $options);
        } catch (\Throwable $th) {
            $user = $this->db->persons->findOne([
                '$or' => [
                    ['last' => $last, 'first' => $first],
                    ['names' => "$last, $first"]
                ]
            ], $options);
        }

        if (empty($user) || empty($user['username'] ?? null)) return null;
        return strval($user['username']);
    }

    /**
     * Get all personal information from username
     *
     * @param string $user Username.
     * @return array Person array.
     */
    public function getPerson($user = null)
    {
        if ($user === null) $user = $_SESSION['username'];
        $person = $this->db->persons->findOne(['username' => $user]);
        if (empty($person)) return array();
        $person['name'] = $person['first'] . " " . $person['last'];
        $person['first_abbr'] = $this->abbreviateName($person['first']);
        return $this->doc2Arr($person);
    }

    /**
     * Get all personal information from username
     *
     * @param string $user Username.
     * @return array Person array.
     */
    public function getPersonByUniqueID($uniqueid = null)
    {
        if ($uniqueid === null) return array();
        $person = $this->db->persons->findOne(['uniqueid' => $uniqueid]);
        if (empty($person)) return array();
        $person['name'] = $person['first'] . " " . $person['last'];
        $person['first_abbr'] = $this->abbreviateName($person['first']);
        return $this->doc2Arr($person);
    }

    /**
     * Get project
     *
     * @param string $project Project name or ID.
     * @return array Project array.
     */
    public function getProject($project)
    {
        if ($this->is_ObjectID($project)) {
            $project = $this->db->projects->findOne(['$or' => [['name' => $project], ['_id' => $this->to_ObjectID($project)]]]);
        } else {
            $project = $this->db->projects->findOne(['name' => $project]);
        }
        return $this->doc2Arr($project);
    }

    /**
     * Abbreviate all first names including hyphens.
     *
     * @param string $first Full first name.
     * @return string Abbreviated first name.
     */
    public function abbreviateName($first)
    {
        $fn = "";
        if (empty($first)) return $fn;
        foreach (preg_split("/(\s+| |-|\.)/u", $first, -1, PREG_SPLIT_DELIM_CAPTURE) as $name) {
            if (empty($name) || $name == '.' || $name == ' ') continue;
            if ($name == '-')
                $fn .= '-';
            else
                $fn .= "" . mb_substr($name, 0, 1) . ".";
        }
        return $fn;
    }


    /**
     * Returns full name from username.
     * Format: Last, First
     *
     * @param string $user Username.
     * @return string Full name.
     */
    public function getNameFromId($user, $reverse = false, $abbr = false)
    {
        $USER = $this->getPerson($user, true);
        $first = $USER['first'] ?? '';

        if ($abbr && !empty($first)) {
            $fn = "";
            foreach (preg_split("/(\s+| |-|\.)/u", $first, -1, PREG_SPLIT_DELIM_CAPTURE) as $name) {
                if (empty(trim($name)) || $name == '.' || $name == ' ') continue;
                if ($name == '-')
                    $fn .= '-';
                else
                    $fn .= "" . mb_substr($name, 0, 1) . ".";
            }
            $first = $fn;
        }
        if (empty($first)) return $USER['last'] ?? '';
        if ($reverse) {
            return $USER['last'] . ', ' . $first;
        } else {
            return $first . ' ' . $USER['last'];
        }
    }

    /**
     * Get the professional name from username.
     * Format: Title Last
     *
     * @param string $user Username.
     * @return array User array.
     */
    public function getTitleLastname($user)
    {
        $u = $this->getPerson($user);
        if (empty($u)) return "!!$user!!";
        $n = "";
        if (!empty($u['academic_title'])) $n = $u['academic_title'] . " ";
        $n .= $u['last'];
        return $n;
    }


    /**
     * Get Activity from ID.
     *
     * @param string $id Activity ID.
     * @return array Activity document.
     */
    public function getActivity($id)
    {
        $id = $this->to_ObjectID($id);
        $doc = $this->db->activities->findOne(['_id' => $id]);
        return $this->doc2Arr($doc);
    }

    /**
     * Get name of the journal from activity doc.
     *
     * @param array $doc Activity document.
     * @return string Journal name.
     */
    public function getJournalName($doc)
    {
        $journal = $this->getJournal($doc);
        return $this->ucname($journal['journal'] ?? '');
    }

    /**
     * Get journal information from activity document.
     *
     * @param array $doc Activity document.
     * @return array Journal document.
     */
    public function getJournal($doc)
    {
        if (isset($doc['journal_id']) && !empty($doc['journal_id'])) {
            return $this->getConnected('journal', $doc['journal_id']);
        }

        if (isset($doc['issn'])) {
            $issn = $doc['issn'];
            if (is_string($issn)) {
                $issn = explode(' ', $issn);
            }
            $journal = $this->db->journals->findOne(['issn' => ['$in' => $issn]]);
            if (!empty($journal)) return $journal;
        }

        if (isset($doc['journal'])) {
            $j = new Regex('^' . trim($doc['journal']) . '$', 'i');
            return $this->db->journals->findOne(['journal' => ['$regex' => $j]]);
        }
        return [];
    }

    /**
     * Get journal impact factor for a specific year (minus one)
     *
     * @param array $journal Journal document.
     * @param int $year Optional. year, defaults to current year.
     * @return float impact factor.
     */
    public function impact_from_year($journal, $year = null)
    {
        if (empty($year)) $year = CURRENTYEAR;
        $if = 0;
        if (!isset($journal['impact']) || empty($journal['impact'])) return 0;

        // get impact factors from journal
        $impact = DB::doc2Arr($journal['impact']);
        // sort ascending by year
        usort($impact, function ($a, $b) {
            return $a['year'] - $b['year'];
        });

        foreach ($impact as $i) {
            if ($i['year'] >= $year) break;
            $if = $i['impact'];
        }
        return $if;
    }

    /**
     * Get latest journal impact factor
     *
     * @param array $journal Journal document.
     * @return float impact factor.
     */
    public function latest_impact($journal)
    {
        $if = 0.0;
        if (!isset($journal['impact'])) return $if;
        $impact = DB::doc2Arr($journal['impact']);
        if (empty($impact)) return $if;
        $if = end($impact)['impact'] ?? $if;
        return $if;
    }

    /**
     * Check if activity is open access
     *
     * @param array $doc Activity document.
     * @return bool is open access.
     */
    public function get_oa($doc)
    {
        $journal = $this->getJournal($doc);
        if (!isset($journal['oa']) || $journal['oa'] === false) {
            return false;
        } elseif ($journal['oa'] > 0) {
            if (intval($doc['year']) > $journal['oa']) return true;
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get document impact factor
     *
     * @param array $doc Activity document.
     * @param int $year Optional. Year. Defaults to document year
     * @return float impact factor.
     */
    public function get_impact($doc, $year = null)
    {
        $journal = $this->getJournal($doc);

        if (empty($journal)) return null;

        if ($year == null) {
            $year = intval($doc['year'] ?? 1);
        }
        return $this->impact_from_year($journal, $year);
    }
    /**
     * Get document quartile
     *
     * @param array $doc Activity document.
     * @param int $year Optional. Year. Defaults to document year
     * @return int is open access.
     */
    public function get_metrics($doc, $year = null, $key = 'quartile')
    {
        $metrics = [];
        $journal = $this->getJournal($doc);

        if (empty($journal) || !isset($journal['metrics'])) return null;

        if ($year == null) {
            $year = intval($doc['year'] ?? 1);
        }
        foreach ($journal['metrics'] as $i) {
            if ($i['year'] >= $year) break;
            $metrics = $i;
        }
        if (empty($metrics)) return null;
        return $metrics[$key] ?? $metrics;
    }
    /**
     * Check if user is author of activity
     *
     * @param array $doc Activity document.
     * @param string $user Username of potential author.
     * @return bool is user activity.
     */
    public static function isUserActivity($doc, $user)
    {
        if (isset($doc['created_by']) && $doc['created_by'] == $user) return true;
        if (isset($doc['user']) && $doc['user'] == $user) return true;
        foreach (['authors', 'editors'] as $role) {
            if (!isset($doc[$role])) continue;
            foreach ($doc[$role] as $author) {
                if (isset($author['user']) && !empty($author['user'])) {
                    if ($user == $author['user']) return true;
                }
            }
        }
        return false;
    }

    /**
     * Convert title or journal name to capital case
     * Ignores stop words
     *
     * @param string $name Name to convert.
     * @return string capital case name.
     */
    public function ucname($name)
    {
        include BASEPATH . "/php/stopwords.php";
        $result = "";
        $words = explode(" ", $name);
        foreach ($words as $word) {
            if (!ctype_lower($word) || in_array($word, $stopwords))
                $result .= " " . $word;
            else
                $result .= " " . ucfirst($word);
        }
        return trim($result);
    }

    /**
     * Get all activities that are used for the reports
     * Filters by time period, epubs and affiliated authors
     *
     * @param string $start Start date in ISO Format.
     * @param string $end End date in ISO Format.
     * @return array All reportable activity documents.
     */
    public function get_reportable_activities($start, $end)
    {
        $result = [];

        $startyear = intval(explode('-', $start, 2)[0]);
        $endyear = intval(explode('-', $end, 2)[0]);

        $starttime = getDateTime($start . ' 00:00:00');
        $endtime = getDateTime($end . ' 23:59:59');

        $options = ['sort' => ["year" => 1, "month" => 1, "day" => 1, "start.day" => 1]];
        $filter = [];

        $filter['$or'] =   array(
            [
                "start.year" => array('$lte' => $startyear),
                '$and' => array(
                    ['$or' => array(
                        ['end.year' => array('$gte' => $endyear)],
                        ['end' => null]
                    )],
                    ['$or' => array(
                        ['type' => 'misc', 'subtype' => 'misc-annual'],
                        ['type' => 'review', 'subtype' =>  'editorial'],
                    )]
                )
            ],
            [
                'year' => ['$gte' => $startyear, '$lte' => $endyear],
            ]
        );
        $cursor = $this->db->activities->find($filter, $options);

        foreach ($cursor as $doc) {
            // dump($doc['title'] ?? '');
            // check if time of activity ist in the correct time range
            $ds = getDateTime($doc['start'] ?? $doc);
            if (isset($doc['end']) && !empty($doc['end'])) $de = getDateTime($doc['end'] ?? $doc);
            elseif (in_array($doc['subtype'], ['misc-annual', 'editorial']) && is_null($doc['end'])) {
                $de = $endtime;
            } else
                $de = $ds;

            if (($de  >= $starttime) && ($endtime >= $ds)) {
                //overlap
                // echo "overlap";
                // if (($ds <= $starttime && $starttime <= $de) || ($starttime <= $ds && $ds <= $endtime)) {
            } else {
                continue;
            }

            // the following is only relevant for publications
            if ($doc['type'] == 'publication') {
                // epubs are not reported
                if (isset($doc['epub']) && $doc['epub']) continue;
            }

            // check if any of the authors is affiliated
            $aoi_exists = false;
            foreach ($doc['authors'] as $a) {
                $aoi = boolval($a['aoi'] ?? false);
                $aoi_exists = $aoi_exists || $aoi;
            }
            if (!$aoi_exists) continue;

            $result[] = $doc;
        }

        return $result;
    }


    /**
     * Convert list of authors into unique list of departments
     *
     * @param array $authors List of activity authors.
     * @return array unique list of departments.
     * 
     * @deprecated 1.3.0
     */
    public function getDeptFromAuthors($authors)
    {
        $result = [];
        $authors = $this->doc2Arr($authors);
        $authors = array_filter($authors, function ($a) {
            return boolval($a['aoi'] ?? false);
        });
        if (empty($authors)) return [];
        $users = array_filter(array_column($authors, 'user'));
        foreach ($users as $user) {
            $user = $this->getPerson($user);
            if (empty($user) || empty($user['dept'])) continue;
            if (in_array($user['dept'], $result)) continue;
            $result[] = $user['dept'];
        }
        return $result;
    }

    public function getUserIssues($user = null)
    {
        if ($user === null) $user = $_SESSION['username'];
        $issues = array();
        $now = new DateTime();
        $today = date('Y-m-d');

        // check if new activity was added for user
        $docs = $this->db->activities->distinct(
            '_id',
            ['authors' => ['$elemMatch' => ['user' => $user, 'approved' => ['$nin' => [true, 1, '1']]]]]
        );
        if (!empty($docs)) $issues['approval'] = array_map('strval', $docs);

        // CHECK status issue
        $docs = $this->db->activities->find(
            [
                'authors.user' => $user,
                '$or' => [
                    ['status' => 'in progress', '$or' => [['end_date' => null], ['end_date' => ['$lt' => $today]]]],
                    ['status' => 'preparation', 'start_date' => ['$lt' => $today]],
                ]
            ],
            [
                'projection' => ['status' => 1]
            ]
        );
        foreach ($docs as $doc) {
            $issues['status'][] = strval($doc['_id']);
        }

        // check EPUB issue
        $docs = $this->db->activities->find(['authors.user' => $user, 'epub' => true], ['projection' => ['epub-delay' => 1]]);
        foreach ($docs as $doc) {
            if (isset($doc['epub-delay']) && $now < new DateTime($doc['epub-delay'])) continue;
            $issues['epub'][] = strval($doc['_id']);
        }

        // check ongoing reminder
        // but first get all open end subtypes
        // $Format = new Document();
        // $activities = $Format->getActivities();
        $openendtypes = [];
        $types = $this->db->adminTypes->find()->toArray();
        // foreach ($types as $typeArr) {
        foreach ($types as $typeArr) {
            $type = $typeArr['id'];
            $modules = DB::doc2Arr($typeArr['modules']);
            if (in_array('date-range-ongoing', $modules) || in_array('date-range-ongoing*', $modules))
                $openendtypes[] = $type;
        }
        // }
        // then find all documents that belong to this
        $docs = $this->db->activities->find(['authors.user' => $user, 'end' => null, 'subtype' => ['$in' => $openendtypes]], ['projection' => ['end-delay' => 1]]);
        foreach ($docs as $doc) {
            if (isset($doc['end-delay']) && $now < new DateTime($doc['end-delay'])) continue;
            $issues['openend'][] = strval($doc['_id']);
        }

        // find all projects that need attention
        // $projects = $this->db->projects->find([
        //     'persons.user' => $user,
        //     'status' => 'proposed'
        // ]);
        // foreach ($projects as $project) {
        //     if (isset($project['end-delay']) && $now < new DateTime($project['end-delay'])) continue;
        //     $issues['project-open'][] = strval($project['_id']);
        // }
        // $projects = $this->db->projects->find([
        //     'persons.user' => $user,
        //     'status' => 'approved',
        //     'end.year' => ['$lte' => CURRENTYEAR]
        // ]);
        // foreach ($projects as $project) {
        //     if ($now < getDateTime($project['end'])) continue;
        //     $issues['project-end'][] = strval($project['_id']);
        // }

        $y = CURRENTYEAR - 1;
        $infrastructures = $this->db->infrastructures->find([
            'persons' => ['$elemMatch' => ['user' => $user, 'reporter' => true]],
            'start_date' => ['$lte' => $y . '-12-31'],
            '$or' => [
                ['end_date' => null],
                ['end_date' => ['$gte' => $y . '-01-01']]
            ],
            'statistics.year' => ['$ne' => $y]
        ], ['projection' => ['id' => ['$toString' => '$_id']]]);

        foreach ($infrastructures as $infra) {
            $issues['infrastructure'][] = $infra['id'];
        }

        return $issues;
    }


    public static function arrayRecursiveDiff($aArray1, $aArray2)
    {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue) {
            if ($aArray2 instanceof BSONArray || $aArray2 instanceof BSONDocument) {
                $aArray2 = DB::doc2Arr($aArray2);
            }
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = DB::arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }

    /**
     * Function to convert array into human readable Module fields
     */
    public static function convert4humans($doc)
    {

        $omit_fields = ['_id', 'history', 'rendered', 'comment', 'editor-comment', 'journal_id', 'impact'];

        $result = [];

        $Format = new Document();
        $Format->usecase = "list";
        $Format->setDocument($doc);

        foreach ($doc as $key => $val) {
            if (in_array($key, $omit_fields)) continue;
            $val = $Format->get_field($key);
            if ($val instanceof BSONArray || $val instanceof BSONDocument) {
                $val = DB::doc2Arr($val);
            }
            if (is_array($val)) $val = json_encode($val);
            $val = strip_tags($val);
            $result[$key] = $val;
        }
        return $result;
    }


    /**
     * function to add update history in a document
     */
    public function updateHistory($new_doc, $id)
    {
        $old_doc = $this->getActivity($id);
        $hist = [
            'date' => date('Y-m-d'),
            'user' => $_SESSION['username'] ?? 'system',
            'type' => 'edited',
            // 'current' => 'unchanged'
            'changes' => [],
            'comment' => $new_doc['editor-comment'] ?? null
        ];
        // unset editor comment
        unset($new_doc['editor-comment']);

        $new_ = DB::convert4humans($new_doc);
        $old_ = DB::convert4humans($old_doc);
        $diff = array_diff_assoc($new_, $old_);

        if (!empty($diff)) {
            $changes = [];
            foreach ($diff as $key => $val) {
                $changes[$key] = ['before' => $old_[$key] ?? null, 'after' => $val];
            }
            $hist['changes'] = $changes;
        }
        // dump($hist, true);
        // die;
        $new_doc['history'] = $old_doc['history'] ?? [];
        $new_doc['history'][] = $hist;
        return $new_doc;
    }

    function getCountry($iso, $key = null)
    {
        if (empty($iso)) return null;
        $country = $this->db->countries->findOne(['iso' => $iso]);
        if (empty($country)) return null;
        if ($key !== null) {
            if (isset($country[$key])) return $country[$key];
            return null;
        }
        return $this->doc2Arr($country);
    }
    function getCountries($key = 'name')
    {
        $countries = $this->db->countries->find();
        $result = [];
        foreach ($countries as $country) {
            $result[$country['iso']] = $country[$key];
        }
        return $result;
    }


    public function canProjectsBeCreated()
    {
        $ability = $this->db->adminProjects->count(['disabled' => false, 'process' => 'project']);
        if ($ability > 0) return true;
        return false;
    }

    public function canProposalsBeCreated()
    {
        $ability = $this->db->adminProjects->count(['disabled' => false, 'process' => 'proposal']);
        if ($ability > 0) return true;
        return false;
    }
}
