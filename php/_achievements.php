<?php
include_once BASEPATH . "/php/Coins.php";
include_once BASEPATH . "/php/init.php";

class Achievement
{
    private $DB;
    private $osiris;
    public $userac = [];
    public $achievements = [];
    private $username = null;
    private $userdata = [];
    public $new = [];
    public $lang = 'en';
    public $lang_g = 'en';
    private $self = false;
    private $showcoins = false;


    function __construct($DB = null)
    {
        $this->DB = new DB;
        $this->osiris = $this->DB->db;
        // $achievements = $this->DB->achievements->find()->toArray();

        $json = file_get_contents(BASEPATH . "/achievements.json");
        $achievements = json_decode($json, true, 512, JSON_NUMERIC_CHECK);

        foreach ($achievements as $ac) {
            $this->achievements[$ac['id']] = $ac;
        }
        $this->lang = lang('en', 'de');
        $this->lang_g = $this->lang;
    }

    function initUser($username)
    {
        $this->username = $username;

        // check if the user is the current user
        if ($username == ($_SESSION['username'] ?? '')) {
            $this->self = true;
        }

        // find user in the database
        $this->userdata = $this->DB->getPerson($username);
        if (empty($this->userdata)) return false;

        // get all user achievements

        $achieved = $this->osiris->achieved->find(['username' => $username]);

        // check if user disabled coins
        // $this->showcoins = !($this->userdata['hide_coins'] ?? true);
        global $Settings;
        if (!$Settings->featureEnabled('coins')) {
            $showcoins = false;
        } else {
            $showcoins = ($this->userdata['show_coins'] ?? 'no');
            if ($showcoins == 'all') {
                $this->showcoins = true;
            } elseif ($showcoins == 'myself' && $this->self) {
                $this->showcoins = true;
            } else {
                $this->showcoins = false;
            }
        }
        if (!$this->showcoins) {
            // do not show coin-associated achievements if the user has disabled coins
            unset($this->achievements['coins']);
            unset($this->achievements['pub-coins']);
            unset($this->achievements['year-coins']);
        }
        foreach ($achieved as $ac) {
            // ignore falsely formatted achievement entries
            if (!isset($ac['id']) || !array_key_exists($ac['id'], $this->achievements)) continue;

            // add metainformation
            $ac['achievement'] = $this->achievements[$ac['id']];

            // add to list of user achievements
            $this->userac[$ac['id']] = $ac;
        }

        // save gender for correct title formatting
        if (($this->userdata['gender'] ?? 'n') == 'f') {
            $this->lang_g .= "_f";
        }
    }

    function userOrder()
    {
        uasort(
            $this->achievements,
            function ($a, $b) {
                $lvl1 = $this->userac[$a['id']]['level'] ?? '0';
                if ($a['maxlvl'] == $lvl1) $lvl1 = 'max';
                $lvl2 = $this->userac[$b['id']]['level'] ?? '0';
                if ($b['maxlvl'] == $lvl2) $lvl2 = 'max';
                if ($lvl1 > $lvl2) return -1;
                if ($lvl2 > $lvl1) return 1;
                return 0;
            }
        );
    }
    function checkAchievements()
    {
        if ($this->username === null) return false;
        $activities = $this->osiris->activities->find(
            ['authors' => ['$elemMatch' => ['user' => $this->username, 'approved' => ['$in' => [true, 1, '1']]]]]
        )->toArray();

        if (empty($activities)) $types = [];
        else {
            $types = array_column($activities, 'type');
            $types = array_count_values($types);
        }

        $Coins = new Coins();
        foreach ($activities as $i => $doc) {
            $l =  $Coins->activityCoins($doc, $this->username);
            $activities[$i]['coins'] = $l['coins'] ?? 0;
        }

        foreach ($this->achievements as $key => $achievement) {
            $user_ac = $this->userac[$key] ?? [];
            $user_lvl = $user_ac['level'] ?? 0;
            $new_lvl = $user_lvl;
            $max_lvl = $achievement['maxlvl'];

            $value = 0;
            if ($user_lvl < $max_lvl) {
                switch ($key) {
                    case 'create':
                        $value = $this->osiris->activities->count(['created_by' => $this->username]);
                        break;
                    case 'versatile':
                        $created = $this->osiris->activities->find(['created_by' => $this->username])->toArray();
                        $created_types = array_column($created, 'type');
                        $value = count(array_unique($created_types));
                        break;
                    case 'publications':
                        $value = $types['publication'] ?? 0;
                        break;
                    case 'lectures':
                        $value = $types['lecture'] ?? 0;
                        break;
                    case 'posters':
                        $value = $types['poster'] ?? 0;
                        break;
                    case 'reviews':
                        $value = $types['review'] ?? 0;
                        break;
                    case 'software':
                        $value = $types['software'] ?? 0;
                        break;
                    case 'profile-edit':
                        if (isset($this->userdata['updated_by']) && $this->userdata['updated_by'] == $this->username)
                            $value = 1;
                        break;
                    case 'coauthors':
                        $value = $this->osiris->activities->count([
                            'authors' => [
                                '$elemMatch' => [
                                    'user' => $this->username,
                                    'approved' => ['$in' => [true, 1, "1"]]
                                ]
                            ],
                            'created_by' => ['$ne' => $this->username]
                        ]);
                        break;

                    case 'theses':
                        foreach ($activities as $a) {
                            if ($a['type'] == "students" && isset($a['status']) && $a['status'] == 'completed') $value++;
                        }
                        break;
                    case 'network':
                        $authors = [];
                        foreach ($activities as $a) {
                            if (isset($a['authors'])) {
                                foreach ($a['authors'] as $au) {
                                    if (!isset($au['first']) || !isset($au['last']) || ($au['user'] ?? '') == $this->username) continue;
                                    $name = strtolower($au['first'] . " " . $au['last']);
                                    if (!in_array($name, $authors)) {
                                        $authors[] = $name;
                                        $value++;
                                    }
                                }
                            }
                        }
                        break;
                    case 'pub-impact':
                        $arr = array_column($activities, 'impact');
                        if (!empty($arr)) $value = max($arr);
                        break;

                    case 'coins':
                        if (!$this->showcoins) break;
                        $value = array_sum(array_column($activities, 'coins'));
                        break;
                    case 'year-coins':
                        if (!$this->showcoins) break;
                        $lom_years = [];
                        foreach ($activities as $a) {
                            if (!isset($lom_years[$a['year']])) $lom_years[$a['year']] = 0;
                            $lom_years[$a['year']] += $a['coins'];
                        }

                        if (!empty($lom_years)) $value = max($lom_years);
                        break;
                    case 'pub-coins':
                        if (!$this->showcoins) break;
                        $array = array_filter($activities, function ($a) {
                            return $a['type'] == 'publication';
                        });
                        if (count(array_column($array, 'coins')) > 0)
                            $value = max(array_column($array, 'coins'));
                        break;

                    case 'approved':
                        $approved = [];
                        if (!isset($this->userdata['approved'])) {
                            break;
                        }
                        foreach ($this->userdata['approved'] ?? array() as $a) {
                            $a = explode('Q', $a);
                            $y = $a[0];
                            if (!isset($approved[$y])) $approved[$y] = 0;
                            $approved[$y] += 1;
                        }
                        $value = max($approved);
                        break;
                    default:
                        $value = 0;
                        break;
                }
                // echo "<!--";
                // dump([$key, $value], true);
                // echo "-->";

                // calculate new level
                $new_lvl = $user_lvl;
                foreach ($achievement['levels'] as $lvl) {
                    if ($lvl['level'] <= $new_lvl) continue;
                    if ($value < $lvl['step']) break;
                    $new_lvl = $lvl['level'];
                }
            }
            if ($new_lvl > $user_lvl || ($user_ac['new'] ?? false)) {
                $this->userac[$key] = [
                    'id' => $key, "level" => $new_lvl, "achieved" => date("d.m.Y"), "username" => $this->username
                ];
                $this->new[] = $this->userac[$key];
            }
        }

        return true;
    }

    function icon($uac)
    {
        $ac = $this->achievements[$uac['id']] ?? array();

        $title = $ac['title'][$this->lang_g];

        $lvl = $uac['level'] ?? 1;
        if ($lvl == $ac['maxlvl']) $lvl = "max";
        $title .= " (Level&nbsp;$lvl)";

?>
        <a class="achievement lvl<?= $uac['level'] ?> max-<?= $ac['maxlvl'] ?? 4 ?>" href="<?= ROOTPATH ?>/achievements/<?= $this->username ?>#<?= $uac['id'] ?>">

            <span class="thumb-sm" data-toggle="tooltip" data-title="<?= $title ?>">
                <?php
                echo file_get_contents(BASEPATH . "/img/achievements/ac_{$ac['icon']}.svg");
                ?>
            </span>

        </a>
    <?php
    }


    function widget($size = "")
    {
        $levels = [
            "max" => 0,
            "3" => 0,
            "2" => 0,
            "1" => 0
        ];
        foreach ($this->userac as $id => $uac) {
            if (!isset($this->achievements[$uac['id']])) continue;
            $ac = $this->achievements[$uac['id']];
            $lvl = $uac['level'] ?? 1;
            if ($lvl == $ac['maxlvl']) $lvl = "max";
            if (!isset($levels[$lvl])) continue;
            $levels[$lvl]++;
        }
        echo "<div class='achievement-widget achievement-widget-$size'>";
        foreach ($levels as $lvl => $value) {
            if ($value == 0) continue;
            echo "<a class='achievement lvl-$lvl' href='" . ROOTPATH . "/achievements/$this->username'>";
            echo "<img src='" . ROOTPATH . "/img/tophies/trophy_$lvl-simple.svg' alt='Level $lvl'>";
            echo "<span>$value</span>";
            echo "</a>";
        }
        echo "</div>";

        // $levels = [
        //     "4" => 0,
        //     "3" => 0,
        //     "2" => 0,
        //     "1" => 0
        // ];
        // foreach ($this->userac as $id => $uac) {
        //     $ac = $this->achievements[$uac['id']] ?? array();
        //     $lvl = $uac['level'] ?? 1;
        //     if ($lvl == $ac['maxlvl']) $lvl = "4";
        //     if (!isset($levels[$lvl])) continue;
        //     $levels[$lvl]++;
        // }
        // echo "<div class=''>";
        // foreach ($levels as $lvl => $value) {
        //     if ($value == 0) continue;
        //     echo "<a class='achievement colorless font-size-16 max-4 lvl$lvl mr-10' href='".ROOTPATH."/achievements/$this->username'>";
        //     echo "<i class='ph ph-fill ph-lg ph-trophy'></i> $value";
        //     echo "</a>";
        // }
        // echo "</div>";
    }

    function snack($uac)
    {
        $ac = $this->achievements[$uac['id']] ?? array();
    ?>
        <div class="alert achievement mb-10 lvl<?= $uac['level'] ?> max-<?= $ac['maxlvl'] ?? 4 ?>">
            <button class="close" data-dismiss="alert" type="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="row">
                <div class="col flex-grow-0 thumb">
                    <?php
                    echo file_get_contents(BASEPATH . "/img/achievements/ac_{$ac['icon']}.svg");
                    ?>
                </div>
                <div class="col">
                    <h3 class="">
                        <a class="link colorless" href="<?= ROOTPATH ?>/achievements/<?= $this->username ?>#<?= $uac['id'] ?>">
                            <?= $ac['title'][$this->lang_g] ?>
                        </a>
                    </h3>
                    <h5 class="d-inline-block">Level
                        <?php
                        $lvl = $uac['level'] ?? 1;
                        if ($lvl == $ac['maxlvl']) $lvl = "max";
                        echo $lvl;
                        ?>
                    </h5>
                    <small class="text-muted"><?= lang('achieved at', 'erlangt am') ?> <?= $uac['achieved'] ?? '-' ?></small>
                    <p class="m-0">
                        <?php
                        $descr = $ac['levels'][$uac['level'] - 1][$this->lang];
                        if ($this->self) {
                            $descr = str_replace(lang('have', 'hat'), lang('has', 'hast'), $descr);
                            echo str_replace('*', 'Du', $descr);
                        } else {
                            echo str_replace('*', $this->userdata['first'], $descr);
                        }
                        ?>
                    </p>

                </div>
            </div>
        </div>
<?php
    }

    function save()
    {
        // check if there is something to save
        if (!$this->self || empty($this->new) || empty($this->userac)) return;
        $values = array_values($this->userac);

        $this->osiris->achieved->deleteMany(['username' => $this->username]);
        $this->osiris->achieved->insertMany($values);
    }
}

