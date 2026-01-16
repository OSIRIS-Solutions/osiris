<?php


// implement newer functions in case they don't exist
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return $needle !== '' && strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle)
    {
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
    }
}

// helper functions for all CRUD methods
function validateValues($values, $DB)
{
    $first = max(intval($values['first_authors'] ?? 1), 1);
    unset($values['first_authors']);
    $last = max(intval($values['last_authors'] ?? 1), 1);
    unset($values['last_authors']);

    foreach ($values as $key => $value) {
        if ($key == 'id') {
            // do not validate id, it is set by the database
            continue;
        } else if ($key == 'doi') {
            if (!str_contains($value, '10.')) $value = null;
            elseif (!str_starts_with($value, '10.')) {
                $value = explode('10.', $value, 2);
                $values[$key] = "10." . $value[1];
            }
        } else if ($key == 'authors' || $key == "editors" || $key == 'supervisors') {
            $values[$key] = array();
            $i = 0;
            foreach ($value as $author) {
                if (is_array($author)) {
                    $author['approved'] = ($author['user'] ?? '') == $_SESSION['username'];
                    if (isset($author['aoi'])) {
                        $author['aoi'] = boolval($author['aoi']);
                    }
                    if (isset($author['sws'])) {
                        $author['sws'] = floatval($author['sws']);
                    }
                    $values[$key][] = $author;
                    continue;
                }
                $author = explode(';', $author, 3);
                if (count($author) == 1) {
                    $user = $author[0];
                    $temp = $DB->getPerson($user);
                    $author = [$temp['last'], $temp['first'], true];
                } else {
                    $user = $DB->getUserFromName($author[0], $author[1]);
                }
                $vals = [
                    'last' => $author[0],
                    'first' => $author[1],
                    'aoi' => boolval($author[2]),
                    'user' => $user,
                    'approved' => $user == $_SESSION['username']
                ];
                if ($key == "editors") {
                    $values[$key][] = $vals;
                } else {
                    if ($i < $first) {
                        $pos = 'first';
                    } elseif ($i + $last >= count($value)) {
                        $pos = 'last';
                    } else {
                        $pos = 'middle';
                    }
                    $vals['position'] = $pos;
                    $values[$key][] = $vals;
                }
                $i++;
            }
        } else if ($key == 'sws') {
            foreach ($value as $i => $v) {
                $values['authors'][$i]['sws'] = $v;
            }
            unset($values['sws']);
        } else if ($key == 'user') {
            $user = $DB->getPerson($value);
            $values["authors"] = [
                [
                    'last' => $user['last'],
                    'first' => $user['first'],
                    'aoi' => true,
                    'user' => $value,
                    'approved' => $value == $_SESSION['username']
                ]
            ];
        } else if ($key == 'kdsf-ffk') {
            $values[$key] = array_map('strval', $value);
            continue;
        } else if ($key == 'countries' && is_array($value) && strlen($value[0]) > 2) {
            $countries = [];
            foreach ($value as $country) {
                $country = explode(';', $country, 2);
                $countries[] = [
                    'country' => $country[0],
                    'role' => $country[1] ?? ''
                ];
            }
            $values[$key] = $countries;
        } else if (is_array($value)) {
            $values[$key] = validateValues($value, $DB);
        } else if ($key == 'issn') {
            if (empty($value)) {
                $values[$key] = array();
            } else {
                $values[$key] = explode(' ', $value);
                $values[$key] = array_unique($values[$key]);
            }
        } else if ($value === 'true') {
            $values[$key] = true;
        } else if ($value === 'false') {
            $values[$key] = false;
        } else if ($key == 'invited_lecture' || $key == 'open_access') {
            $values[$key] = boolval($value);
        } else if ($key == 'oa_status') {
            $values['open_access'] = $value != 'closed';
        } else if ($key == 'title' || $key == 'title_de') {
            // strip <p> tags
            $values[$key] = str_replace(['<p>', '</p>'], ' ', $value);
            $values[$key] = trim($values[$key]);
        } else if ($key === 'epub'){
            $values['epub-delay'] = endOfCurrentQuarter(true);
            // value is boolean
            $values[$key] = boolval($value);
        } else if (in_array($key, ['aoi', 'correction'])) {
            $values[$key] = boolval($value);
        } else if ($value === '') {
            $values[$key] = null;
        } else if ($key === 'epub-delay' || $key === 'end-delay') {
            // will be converted otherwise
            $values[$key] = endOfCurrentQuarter(true);
        } else if ($key == 'start' || $key == 'end') {
            if (DateTime::createFromFormat('Y-m-d', $value) !== FALSE) {
                $values[$key] = valiDate($value);
                $values[$key . '_date'] = $value;
                if ($key == 'start') {
                    if (!isset($values['year']) && isset($values[$key]['year'])) {
                        $values['year'] = $values[$key]['year'];
                    }
                    if (!isset($values['month']) && isset($values[$key]['month'])) {
                        $values['month'] = $values[$key]['month'];
                    }
                }
            } else {
                $values[$key] = null;
            }
        } else if ($key == 'month' || $key == 'year') {
            $values[$key] = intval($value);
        } else if ($key == 'room') {
            // do not connvert room numbers to integers
        } else if (is_numeric($value)) {
            if (str_starts_with($value, "0")) {
                $values[$key] = trim($value);
            } elseif (is_float($value + 0)) {
                $values[$key] = floatval($value);
            } else {
                $values[$key] = intval($value);
            }
        } else if (is_string($value)) {
            $values[$key] = trim($value);
        }
    }

    if (isset($values['journal']) && !isset($values['role']) && isset($values['year'])) {
        if (!isset($values['open_access']) || !$values['open_access']) {
            $values['open_access'] = $DB->get_oa($values);
        }
        if (!isset($values['correction'])) $values['correction'] = false;

        $values['impact'] = $DB->get_impact($values);
    }
    if (($values['type'] ?? '') == 'misc' && ($values['iteration'] ?? '') == 'annual') {
        $values['end-delay'] = endOfCurrentQuarter(true);
    }

    if (isset($values['year']) && ($values['year'] < 1900 || $values['year'] > (CURRENTYEAR ?? 2055) + 5)) {
        echo "The year $values[year] is not valid!";
        die();
    }
    // dump($values);
    // die;
    return $values;
}

function shortenName($name, $maxLength = 30)
{
    return get_preview($name, $maxLength);
}

function get_preview($html, $length = 150)
{
    // 1. Entferne HTML-Tags
    if (empty($html)) return '';
    $text = strip_tags($html);

    if (empty($text)) return '';

    // 2. Kürze den Text auf die gewünschte Länge
    if (mb_strlen($text) > $length) {
        $preview = mb_substr($text, 0, $length);
        // 3. Stelle sicher, dass das letzte Wort nicht abgeschnitten wird
        $preview = mb_substr($preview, 0, strrpos($preview, ' ')) . '...';
    } else {
        $preview = $text;
    }

    return $preview;
}

function valiDate($date)
{
    if (empty($date)) return null;
    $t = explode('-', $date, 3);
    return array(
        'year' => intval($t[0]),
        'month' => intval($t[1] ?? 1),
        'day' => intval($t[2] ?? 1),
    );
}

function printMsg($msg = null, $type = 'info', $header = "default")
{
    if ($msg === null && isset($_SESSION['msg'])) {
        $msg = $_SESSION['msg'];
        unset($_SESSION["msg"]);
    }
    if (isset($_SESSION['msg_type'])) {
        $type = $_SESSION['msg_type'];
        unset($_SESSION["msg_type"]);
    }
    if ($msg === null && isset($_GET['error'])) {
        $msg = $_GET['error'];
        $type = 'error';
        $header = lang('Error', 'Fehler');
    }
    if ($msg === null && !isset($_GET["msg"])) return;
    $msg = $msg ?? $_GET["msg"];
    $text = "";
    $header = $header;
    $class = "";
    if ($type == 'success') {
        $class = "success";
        if ($header == "default") {
            $header = lang("Success!", "Erfolg!");
        }
    } elseif ($type == 'error') {
        $class = "danger";
        if ($header == "default") {
            $header = lang("Error", "Fehler");
        }
    } elseif ($type == 'info') {
        $class = "primary";
        if ($header == "default") {
            $header = "";
        }
    } elseif ($type == 'warning') {
        $class = "signal";
        if ($header == "default") {
            $header = lang("Warning", "Warnung");
        }
    }
    switch ($msg) {

        case 'welcome':
            $header = lang("Welcome,", "Willkommen,") . " " . ($_SESSION["name"] ?? '') . ".";
            $text = lang("You are now logged in.", "Du bist jetzt eingeloggt.");
            if (isset($_GET['new'])) {
                $text = lang(
                    '',
                    'Du bist zum ersten Mal hier? Ich habe dir einen neuen Account angelegt. 
                    Bitte überprüfe <a class="link" href="' . ROOTPATH . '/user/edit/' . $_SESSION['username'] . '">dein Profil</a> und ergänze bzw. korrigiere die Angaben.'
                );
                if (!empty($_GET['new'])) {
                    $text .=  '<br/>' . lang('Ich habe außerdem <b>' . $_GET['new'] . ' Aktivitäten</b> gefunden, die vielleicht zu dir gehören. Du kannst sie <a class="link" href="' . ROOTPATH . '/issues">hier</a> überprüfen.');
                }
            }


            $class = "success";
            break;
        case 'approved':
            $header = lang("Quarter approved.", "Quartal freigegeben.");
            $text = lang("Thank you.", "Vielen Dank.");
            $class = "success";
            break;

        case 'account-created':
            $text = lang("Account has been created. Please log in.", "Der Account wurde erstellt. Bitte logge dich ein.");
            // $text = lang("Thank you.", "Vielen Dank.");
            $class = "success";
            break;

        case 'settings-saved':
            $text = lang("Settings saved", "Einstellungen gespeichert.");
            // $text = lang("Thank you.", "Vielen Dank.");
            $class = "success";
            break;
        case 'settings-resetted':
            $text = lang("Settings resetted to the default values.", "Einstellungen wurden auf den Standard zurückgesetzt.");
            // $text = lang("Thank you.", "Vielen Dank.");
            $class = "success";
            break;
        case 'settings-replaced':
            $text = lang("Settings replaced by uploaded file.", "Einstellungen wurden durch den Upload ersetzt.");
            // $text = lang("Thank you.", "Vielen Dank.");
            $class = "success";
            break;
        case 'success':
            $text = lang("Success", "Erfolg");
            // $text = lang("Dataset was added successfully.", "Der Datensatz wurde erfolgreich hinzufügt.");
            // $text .= '<br/><a class="btn mt-10" href="' . ROOTPATH . '/add-activity">' . lang('Add another activity', 'Weitere Aktivität hinzufügen') . '</a>';
            $class = "success";
            break;

        case 'add-success':
            $header = lang("Success", "Erfolg");
            $text = lang("Dataset was added successfully.", "Der Datensatz wurde erfolgreich hinzufügt.");
            // $text .= '<br/><a class="btn mt-10" href="' . ROOTPATH . '/add-activity">' . lang('Add another activity', 'Weitere Aktivität hinzufügen') . '</a>';
            $class = "success";
            break;

        case 'update-success':
            $header = lang("Success", "Erfolg");
            $text = lang("Dataset was updated successfully.", "Der Datensatz wurde erfolgreich bearbeitet.");
            $class = "success";
            break;

        case 'deleted':
        case 'deleted-1':
            $header = lang("Deleted", "Gelöscht");
            $text = lang("You have deleted a dataset.", "Du hast einen Datensatz gelöscht.");
            $class = "danger";
            break;

        case 'locked':
            $header = lang("This activity is locked.", "Diese Aktivität ist gesperrt.");
            $text = lang(
                "You cannot edit or delete this activity because of our reporting rules. Contact the OSIRIS editors if there are any issues.",
                "Du kannst diese Aktivität aufgrund unserer Report-Richtlinien nicht bearbeiten oder löschen. Kontaktiere die OSIRIS-Editoren, falls dadurch irgendwelche Probleme entstehen."
            );
            $class = "danger";
            break;

        case 'ali':
            $header = '';
            $text = lang("You are already logged in.", "Du bist bereits eingeloggt");
            $class = "signal";
            break;

        default:
            $text = str_replace("-", " ", $msg);
            break;
    }
    $get = currentGET(['msg']) ?? "";
    echo "<div class='alert $class block show my-10' role='alert'>
          <a class='close' href='$get' aria-label='Close'>
          <span aria-hidden='true'>&times;</span>
        </a> ";
    if (!empty($header)) {
        echo " <h4 class='title'>$header</h4>";
    }
    echo "$text
      </div>";
}

function readCart()
{
    $cart = $_COOKIE['osiris-cart'] ?? '';
    if (empty($cart)) return array();
    $cart = explode(',', $cart);
    return $cart;
}

function emptyCart()
{
    setcookie('osiris-cart', '', time() - 3600, "/");
}

function currentGET(array $exclude = [], array $include = [])
{
    if (empty($_GET) && empty($include)) return '?';

    $get = "?";
    foreach (array_merge($_GET, $include) as $name => $value) {
        if (in_array($name, $exclude) || $name == 'msg') continue;
        if (is_array($value)) {
            foreach ($value as $v) {
                // if (empty($v)) continue;
                if ($get !== "?") $get .= "&";
                $get .= $name . "[]=" . $v;
            }
        } elseif (!empty($value)) {
            if ($get !== "?") $get .= "&";
            $get .= $name . "=" . $value;
        }
    }
    return $get;
}

function CallAPI($method, $url, $data = [])
{
    $curl = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "JSON":
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    if ($result === false) {
        throw new Exception(curl_error($curl), curl_errno($curl));
    }
    curl_close($curl);

    return $result;
}

function redirect($url)
{
    header("Location: " . ROOTPATH . $url);
}

function endOfCurrentQuarter($as_string = false)
{
    $q = CURRENTYEAR . '-' . (3 * CURRENTQUARTER) . '-' . (CURRENTQUARTER == 1 || CURRENTQUARTER == 4 ? 31 : 30) . ' 23:59:59';
    if ($as_string) {
        return $q;
    }
    return new DateTime($q);
}

function print_list($list)
{
    if ($list instanceof MongoDB\Model\BSONArray) {
        $list = $list->bsonSerialize();
    }
    return implode(', ', $list);
}


function getDateTime($date)
{
    if (empty($date)) return null;
    if ($date instanceof DateTime) return $date;
    if ($date instanceof MongoDB\BSON\UTCDateTime) {
        // MongoDB\BSON\UTCDateTime 
        $d = $date->toDateTime();
    } else if (isset($date['year'])) {
        //date instanceof MongoDB\Model\BSONDocument
        $d = new DateTime();
        $d->setDate(
            $date['year'],
            $date['month'] ?? 1,
            $date['day'] ?? 1
        );
    } else {
        if (is_string($date)) {
            $d = date_create($date);
        } else {
            $d = null;
        }
    }
    return $d;
}



function valueFromDateArray($date)
{
    // this function is used to generate a input:date-like string from arrays
    if (is_string($date)) return $date;
    if (empty($date) || !isset($date['year'])) return '';
    $d = new DateTime();
    $d->setDate(
        $date['year'],
        $date['month'] ?? 1,
        $date['day'] ?? 1
    );
    return date_format($d, "Y-m-d");
}

function fromToDate($from, $to, $continuous = false)
{
    if ($from == $to) {
        return format_date($from);
    }
    if (empty($to)) {
        if (!$continuous) {
            return format_date($from);
        } else {
            return format_date($from) . ' - ' . lang('today', 'heute');
        }
    }
    // $to = date_create($to);
    $from = format_date($from);

    $to = format_date($to);

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

function fromToYear($from, $to, $continuous = false)
{
    $from = format_date($from, "Y");
    if (!empty($to))
        $to = format_date($to, "Y");

    if ($from == $to) {
        return $from;
    }
    if (empty($to)) {
        if (!$continuous) {
            return $from;
        } else {
            return $from . ' - ' . lang('today', 'heute');
        }
    }

    return $from . ' - ' . $to;
}

function getYear($doc)
{
    if (isset($doc['year'])) return $doc['year'];
    if (isset($doc['start'])) return $doc['start']['year'];
    if (isset($doc['dates'])) {
        if (isset($doc['dates'][0]['start'])) return $doc['dates'][0]['start']['year'];
        if (isset($doc['dates']['start'])) return $doc['dates']['start']['year'];
        // return $doc['start']['year'];
    }
}

function getQuarter($time)
{
    // this function takey either the month, a date string, 
    // or an date array and returns the quarter
    if (empty($time)) {
        return 0;
    }
    if (isset($time['month'])) {
        return ceil($time['month'] / 3);
    }
    if (isset($time['start'])) {
        $time = $time['start'];
    }
    if (isset($time['dates']) && !empty($time['dates'])) {
        $time = reset($time['dates']);
    }
    if (is_int($time)) {
        return ceil($time / 3);
    }

    try {
        $date = getDateTime($time);
        if ($date === null) return 0;
        $month = date_format($date, 'n');
    } catch (TypeError $th) {
        $month = 1;
    }

    return ceil($month / 3);
}

function inQuarter($start, $end = null, $qarter = CURRENTQUARTER, $year = CURRENTYEAR)
{
    // check if time period in selected quarter
    if (empty($end)) {
        $end = $start;
    }
    $qstart = new DateTime($year . '-' . (3 * $qarter - 2) . '-1 00:00:00');
    $qend = new DateTime($year . '-' . (3 * $qarter) . '-' . ($qarter == 1 || $qarter == 4 ? 31 : 30) . ' 23:59:59');

    $start = new DateTime($start);
    $end = new DateTime($end);
    if ($start <= $qstart && $qstart <= $end) {
        return true;
    } elseif ($qstart <= $start && $start <= $qend) {
        return true;
    }
    return false;
}

function inCurrentQuarter($year, $month)
{
    // check if time period in selected quarter
    $qstart = new DateTime(CURRENTYEAR . '-' . (3 * CURRENTQUARTER - 2) . '-1 00:00:00');
    $qend = new DateTime(CURRENTYEAR . '-' . (3 * CURRENTQUARTER) . '-' . (CURRENTQUARTER == 1 || CURRENTQUARTER == 4 ? 31 : 30) . ' 23:59:59');

    $time = new DateTime();
    $time->setDate($year, $month, 15);
    if ($time <= $qstart && $qstart <= $time) {
        return true;
    } elseif ($qstart <= $time && $time <= $qend) {
        return true;
    }
    return false;
}

function format_date($date, $format = "d.m.Y")
{
    // dump($date);
    $d = getDateTime($date);
    if ($d === null) return '';
    return date_format($d, $format);
}

function dump($element, $as_json = true)
{
    echo '<pre class="code">';
    if ($element instanceof MongoDB\Model\BSONArray) {
        $element = $element->bsonSerialize();
    }
    if ($as_json) {
        echo htmlspecialchars(json_encode($element, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        if (!empty(json_last_error())) {
            var_dump(json_last_error_msg()) . PHP_EOL;
            var_export($element);
        }
    } else {
        var_dump($element);
    }
    echo "</pre>";
}

function bool_icon($bool)
{
    if ($bool) {
        return '<i class="ph ph-check text-success"></i>';
    } else {
        return '<i class="ph ph-x text-danger"></i>';
    }
}

function flatten(array $array)
{
    $return = array();
    array_walk_recursive($array, function ($a) use (&$return) {
        $return[] = $a;
    });
    return $return;
}

function time_elapsed_string($datetime, $full = false, $type = 'str')
{
    $now = new DateTime;
    if ($type == 'str') {
        $ago = new DateTime($datetime);
    } else {
        $ago = new DateTime();
        $ago->setTimestamp($datetime);
    }
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => lang('year', 'Jahre'),
        'm' => lang('month', 'Monate'),
        'w' => lang('week', 'Woche'),
        'd' => lang('day', 'Tage'),
        'h' => lang('hour', 'Stunde'),
        'i' => lang('minute', 'Minute'),
        's' => lang('second', 'Sekunde'),
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? lang('s', 'n') : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? lang('', 'vor ') . implode(', ', $string) . lang(' ago', '') : lang('just now', 'gerade eben');
}


function adjustBrightness($hex, $steps)
{
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color   = hexdec($color); // Convert to decimal
        $color   = max(0, min(255, $color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}

function getFileIcon($type)
{
    switch ($type) {
        case 'pdf':
        case 'csv':
            return 'file-' . $type;
        case 'xlsx':
        case 'xls':
            return 'file-excel';
        case 'pptx':
        case 'ppt':
            return 'file-powerpoint';
        case 'docx':
        case 'doc':
            return 'file-word';
        case 'zip':
        case 'gz':
            return 'file-zipper';
        case 'png':
        case 'gif':
        case 'jpg':
        case 'jpeg':
            return 'file-image';
        case 'mp4':
        case 'mpeg':
            return 'file-video';
        case 'json':
            return 'file-code';
        default:
            return 'file-text';
    }
}

/**
 * Return the last day of the Week/Month/Quarter/Year that the
 * current/provided date falls within
 *
 * @param string   $period The period to find the last day of. ('year', 'quarter', 'month', 'week')
 * @param DateTime|null $date   The date to use instead of the current date
 *
 * @return DateTime
 * @throws InvalidArgumentException
 */
function lastDayOf($period, $date = null)
{
    $period = strtolower($period);
    $validPeriods = array('year', 'quarter', 'month', 'week');

    if (!in_array($period, $validPeriods))
        throw new InvalidArgumentException('Period must be one of: ' . implode(', ', $validPeriods));

    $newDate = ($date === null) ? new DateTime() : clone $date;

    switch ($period) {
        case 'year':
            $newDate->modify('last day of december ' . $newDate->format('Y'));
            break;
        case 'quarter':
            $month = $newDate->format('n');

            if ($month < 4) {
                $newDate->modify('last day of march ' . $newDate->format('Y'));
            } elseif ($month > 3 && $month < 7) {
                $newDate->modify('last day of june ' . $newDate->format('Y'));
            } elseif ($month > 6 && $month < 10) {
                $newDate->modify('last day of september ' . $newDate->format('Y'));
            } elseif ($month > 9) {
                $newDate->modify('last day of december ' . $newDate->format('Y'));
            }
            break;
        case 'month':
            $newDate->modify('last day of this month');
            break;
        case 'week':
            $newDate->modify(($newDate->format('w') === '0') ? 'now' : 'sunday this week');
            break;
    }

    return $newDate;
}

function socialLogo($type)
{
    switch ($type) {
        case 'researchgate':
            return 'ph-student';
        case 'youtube':
            return 'ph-youtube-logo';
        case 'github':
            return 'ph-github-logo';
        case 'linkedin':
            return 'ph-linkedin-logo';
        case 'xing':
            return 'ph-xing-logo';
        case 'mastodon':
            return 'ph-mastodon-logo';
        case 'bluesky':
            return 'ph-butterfly';
        case 'instagram':
            return 'ph-instagram-logo';
        case 'facebook':
            return 'ph-facebook-logo';
        case 'X':
            return 'ph-x-logo';
        case 'website':
            return 'ph-browser';
        case 'twitter':
            return 'ph-twitter-logo';
        case 'orcid':
            return 'ph-student';
        case 'email':
            return 'ph-email-logo';
        default:
            return 'ph-link';
    }
}

function getNextColor()
{
    static $palatte = [
        "#1f77b4",
        "#ff7f0f",
        "#2ba02b",
        "#d62727",
        "#9467bd",
        "#8c564b",
        "#e377c2",
        "#7f7f7f",
        "#bcbd22",
        "#17becf",
        "#ffbb78",
        "#aec7e8",
        "#98df8a",
        "#ff9896",
    ];
    static $index = 0;

    $color = $palatte[$index];
    $index = ($index + 1) % count($palatte); // Zurück zum Anfang, wenn Ende erreicht

    return $color;
}
function format_month($month)
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
