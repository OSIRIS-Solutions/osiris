<?php
// Standalone regression checks: php tests/conditional-fields.php
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

define('OSIRIS_VERSION', 'test');
require_once __DIR__ . '/../php/_config.php';

class DB
{
    public $db;
    public static function doc2Arr($value) { return is_object($value) ? (array)$value : ($value ?? []); }
}
class Settings {}
function lang($en, $de = null) { return $de ?? $en; }

// Load the actual renderers without opening a database/session in init.php.
foreach (['Modules', 'CustomFields', 'Document', 'Fields'] as $class) {
    $source = file_get_contents(__DIR__ . '/../php/' . $class . '.php');
    eval(substr($source, strpos($source, 'class ' . $class)));
}

$field = ['id' => 'archive', 'name' => 'Abbreviation', 'format' => 'string',
    'conditional' => 1, 'condition_label' => 'Archived?', 'default' => 'DEFAULT'];
$db = new DB;
$db->db = (object)['adminFields' => new class($field) {
    public $field;
    function __construct($field) { $this->field = $field; }
    function find() { return [$this->field]; }
    function findOne($filter) { return $filter['id'] === $this->field['id'] ? $this->field : null; }
}];
$checks = 0;
function check($label, $actual, $expected)
{
    global $checks;
    if ($actual !== $expected) throw new RuntimeException($label . ': ' . json_encode($actual));
    $checks++;
}

foreach ([0, '0', false, ['value']] as $value) {
    check('Existing nonempty value implies Yes', ConditionalFields::answer($field, ['archive' => $value]), true);
}
foreach ([null, '', []] as $value) {
    check('Missing old answer remains unanswered', ConditionalFields::answer($field, ['archive' => $value]), null);
}
foreach ([true, false, null] as $answer) {
    check('Explicit answer wins over legacy value', ConditionalFields::answer($field,
        ['archive' => 'ABC', 'archive--confirmed' => $answer]), $answer);
}
foreach (['true' => true, 'false' => false, '' => null] as $raw => $answer) {
    $values = validateValues(['archive' => 'ABC', 'archive--confirmed' => $raw], $db);
    check('Posted answer normalized', $values['archive--confirmed'], $answer);
    check('Only Yes retains value', $values['archive'], $answer === true ? 'ABC' : null);
    $update = ConditionalFields::update($values);
    check('Partial updates address one answer', $update['$set']['archive--confirmed'], $answer);
    check('Inactive value explicitly removed', isset($update['$unset']['archive']), $answer !== true);
}
check('Value without an answer becomes searchable as Yes', validateValues(['archive' => 'ABC'], $db), ['archive' => 'ABC', 'archive--confirmed' => true]);
check('Ordinary fields keep previous normalization', validateValues(['title' => 'ABC'], $db), ['title' => 'ABC']);
foreach ([0, '0', false, ['ABC', '']] as $value) {
    check('Implicit Yes includes zero, false and lists', validateValues(['archive' => $value], $db)['archive--confirmed'], true);
}
foreach ([true, false, null] as $answer) {
    $old = ['archive' => 'OLD', 'archive--confirmed' => $answer];
    $values = validateValues(['archive' => 'NEW'], $db, $old);
    check('An omitted answer preserves the stored state', $values['archive--confirmed'], $answer);
    check('Only an active field accepts the new value', $values['archive'], $answer === true ? 'NEW' : null);
    $values = validateValues(['archive' => 'NEW', 'archive--confirmed' => 'true'], $db, $old);
    check('A submitted answer overrides the stored state', $values['archive--confirmed'], true);
}
check('A partial edit does not touch omitted custom fields', validateValues(['title' => 'ABC'], $db,
    ['archive' => 'OLD', 'archive--confirmed' => false]), ['title' => 'ABC']);
check('Nested values do not get independent answer flags', validateValues(['details' => ['archive' => 'ABC']], $db),
    ['details' => ['archive' => 'ABC']]);
check('Cleared list does not retain an old value', validateValues(
    ['archive--confirmed' => 'true'], $db)['archive'], null);
check('Zero survives confirmed input', validateValues(
    ['archive' => '0', 'archive--confirmed' => 'true'], $db)['archive'], '0');
check('False remains a value separate from the answer', validateValues(
    ['archive' => 'false', 'archive--confirmed' => 'true'], $db)['archive'], false);
check('Forged control of built-in field ignored', validateValues(
    ['title' => 'Keep me', 'title--confirmed' => 'false', '$bad.key--confirmed' => 'false'], $db), ['title' => 'Keep me']);
$db->db->adminFields->field['conditional'] = 0;
check('Disabled feature ignores posted answers', validateValues(
    ['archive' => 'ABC', 'archive--confirmed' => 'false'], $db), ['archive' => 'ABC']);
$db->db->adminFields->field = $field;

foreach (['Modules', 'CustomFields'] as $class) {
    $reflection = new ReflectionClass($class);
    $renderer = $reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('DB')->setValue($renderer, $db);
    $renderer->form = ['archive' => 'ABC'];
    foreach (['string', 'text', 'int', 'float', 'list', 'date', 'bool', 'bool-check', 'url', 'text-format', 'str-list'] as $format) {
        $db->db->adminFields->field = $field + ['values' => ['ABC', 'DEF']];
        $db->db->adminFields->field['format'] = $format;
        ob_start();
        $renderer->custom_field('archive', true);
        $html = ob_get_clean();
        check($class . ': one question for ' . $format, substr_count($html, 'class="conditional-answer"'), 2);
        check($class . ': original value key for ' . $format, str_contains($html, 'name="values[archive]'), true);
        check($class . ': legacy value selects Yes for ' . $format, preg_match('/value="true"\s+checked required/', $html), 1);
    }
    $db->db->adminFields->field = $field;
    $renderer->form = ['archive' => 'ABC', 'archive--confirmed' => false];
    ob_start();
    $renderer->custom_field('archive', true);
    $html = ob_get_clean();
    check($class . ': No disables input', str_contains($html, 'disabled style="display: none;"'), true);
    check($class . ': mandatory question', preg_match('/value="false"\s+checked required/', $html), 1);
}

$document = (new ReflectionClass('Document'))->newInstanceWithoutConstructor();
$document->doc = ['archive--confirmed' => false];
check('No suppresses display default', $document->customVal($field), '');
$document->doc = ['archive' => 'ABC'];
check('Existing display unchanged', $document->customVal($field), 'ABC');
$custom = (new ReflectionClass('CustomFields'))->newInstanceWithoutConstructor();
$custom->fields = ['archive' => $field];
$custom->form = ['archive' => 'ABC', 'archive--confirmed' => false];
check('Other entities hide inactive values', $custom->value('archive'), '');

$fields = (new ReflectionClass('Fields'))->newInstanceWithoutConstructor();
$db->db->adminFields->field = $field;
$search = $fields->addCustomFields([], $db->db);
check('Value filter keeps its ID', $search[0]['id'], 'archive');
check('Value filter keeps its type', $search[0]['type'], 'string');
check('Answer filter has a flat ID', $search[1]['id'], 'archive--confirmed');
check('Answer filter is boolean', $search[1]['type'], 'boolean');
check('Answer filter uses the question label', $search[1]['label'], 'Archived? (archive--confirmed)');
check('Answer filter is available in search', $search[1]['usage'], ['filter']);
$db->db->adminFields->field['conditional'] = 0;
check('Ordinary custom fields have no answer filter', count($fields->addCustomFields([], $db->db)), 1);

// Backfill answers only when an existing field first gets its Yes/No question.
$records = new class {
    public $calls = 0;
    public $docs = [
        ['archive' => 'ABC'],
        ['archive' => 0],
        ['archive' => false],
        ['archive' => ['ABC', '']],
        ['archive' => ''],
        ['archive' => null],
        ['archive' => []],
        [],
        ['archive' => 'DEF', 'archive--confirmed' => false],
        ['archive--confirmed' => false],
    ];
    function updateMany($query, $update) {
        $this->calls++;
        $id = array_key_first($query);
        check('Only records with the original field are considered', $query[$id], ['$exists' => true]);
        check('Backfill never removes values', array_keys($update), ['$set']);
        $expression = $query['$expr']['$not'][0]['$in'];
        foreach ($this->docs as &$doc) {
            if (!array_key_exists($id, $doc)) continue;
            $value = $doc[substr($expression[0], 1)];
            if (in_array($value, $expression[1], true)) continue;
            $doc = array_replace($doc, $update['$set']);
        }
    }
};
$osiris = new stdClass();
$collections = ['activities', 'activitiesDrafts', 'projects', 'proposals', 'persons', 'organizations', 'infrastructures', 'journals'];
foreach ($collections as $name) $osiris->$name = clone $records;
$old = array_replace($field, ['conditional' => 0]);
ConditionalFields::initializeAnswers($field, $old, $osiris);
foreach ($collections as $name) {
    $docs = $osiris->$name->docs;
    check($name . ': text is confirmed', $docs[0]['archive--confirmed'], true);
    check($name . ': zero is confirmed', $docs[1]['archive--confirmed'], true);
    check($name . ': false is a valid value', $docs[2]['archive--confirmed'], true);
    check($name . ': a nonempty list is confirmed', $docs[3]['archive--confirmed'], true);
    foreach ([4, 5, 6, 7] as $i) {
        check($name . ': empty value stays unanswered', array_key_exists('archive--confirmed', $docs[$i]), false);
    }
    check($name . ': value added while disabled becomes Yes on reactivation', $docs[8]['archive--confirmed'], true);
    check($name . ': No without a value stays No', $docs[9]['archive--confirmed'], false);
    foreach ($records->docs as $i => $doc) {
        check($name . ': original value stays unchanged', $docs[$i]['archive'] ?? null, $doc['archive'] ?? null);
    }
}
$before = $osiris->activities->docs;
ConditionalFields::initializeAnswers($field, $field, $osiris);
$disabled = array_replace($field, ['conditional' => 0]);
ConditionalFields::initializeAnswers($disabled, $field, $osiris);
ConditionalFields::initializeAnswers($field, [], $osiris);
foreach ($collections as $name) {
    check($name . ': edits, disabling and creation need no backfill', $osiris->$name->calls, 1);
}
check('Disabling leaves all stored data untouched', $osiris->activities->docs, $before);
$db->db->adminFields->field = $disabled;
$values = validateValues(['archive--confirmed' => 'false'], $db);
check('Stale No after disabling cannot delete a value', ConditionalFields::update($values), ['$set' => []]);
$values = validateValues(['archive' => 'ABC', 'archive--confirmed' => 'false'], $db);
check('Normal edits after disabling retain their value', ConditionalFields::update($values), ['$set' => ['archive' => 'ABC']]);

echo "$checks checks passed.\n";
