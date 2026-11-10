<?php
// Run with: php tests/form-builder.php (no database required).
define('BASEPATH', dirname(__DIR__));
define('ROOTPATH', '/osiris');
define('OSIRIS_BUILD', 'test');
require BASEPATH . '/php/FormBuilder.php';

function check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}
function rejects(array $fields, array $rules, string $message): void
{
    try {
        FormBuilder::validateSystemFields($fields, $rules);
    } catch (InvalidArgumentException $e) {
        return;
    }
    throw new RuntimeException($message);
}

$rules = ['title' => ['required' => true], 'date-range-ongoing' => ['required' => false]];
$fields = FormBuilder::prepareFields([], $rules);
check(array_column($fields, 'id') === ['title', 'date-range-ongoing'], 'Missing fields must be added.');
check($fields[0]['props']['required'] === true, 'Title must require a value.');
check($fields[1]['props']['required'] === false, 'Presence must not imply input is required.');
FormBuilder::validateSystemFields($fields, $rules);

$moved = [$fields[1], ['type' => 'heading', 'props' => ['text' => 'Details']], $fields[0]];
$moved[2]['props'] = ['required' => false, 'recommended' => true, 'roles' => 'admin', 'width' => '6', 'label' => 'Mission'];
$prepared = FormBuilder::prepareFields($moved, $rules);
check(array_column($prepared, 'id') === ['date-range-ongoing', 'title'], 'Existing positions must be preserved.');
check($prepared[1] === $moved[1], 'Layout elements must be preserved.');
check($prepared[2]['props']['width'] === '6' && $prepared[2]['props']['label'] === 'Mission', 'Presentation must remain editable.');
check(!isset($prepared[2]['props']['roles']) && $prepared[2]['props']['required'] === true, 'System rules must override old settings.');
check($prepared[2]['props']['recommended'] === false, 'Required fields cannot also be recommended.');
check(FormBuilder::prepareFields($prepared, $rules) === $prepared, 'Reloading must not duplicate fields.');
FormBuilder::validateSystemFields($prepared, $rules);

$optionalRequired = $fields;
$optionalRequired[1]['props']['required'] = true;
check(FormBuilder::prepareFields($optionalRequired, $rules)[1]['props']['required'] === true, 'Admins may make an optional system field required.');
FormBuilder::validateSystemFields($optionalRequired, $rules);

rejects([$fields[1]], $rules, 'Removing a system field must fail.');
rejects([$fields[0], $fields[0], $fields[1]], $rules, 'Duplicate system fields must fail.');
foreach ([
    ['required' => false],
    ['required' => 'true'],
    ['required' => true, 'recommended' => true],
    ['required' => true, 'roles' => 'admin'],
] as $props) {
    $invalid = $fields;
    $invalid[0]['props'] = $props;
    rejects($invalid, $rules, 'Manipulated system properties must fail.');
}
foreach (['heading', 'custom'] as $type) {
    $invalid = $fields;
    $invalid[0]['type'] = $type;
    rejects($invalid, $rules, 'A different element cannot impersonate a system field.');
}
check(FormBuilder::prepareFields($moved, []) === $moved, 'Activities without system rules must remain unchanged.');
FormBuilder::validateSystemFields([], []);

// Render both variants with small stand-ins for unrelated application services.
function lang($en, $de = null) { return $en; }
function localized($value) { return is_array($value) ? $value['en'] : $value; }
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
$Settings = new class { public function getRoles() { return ['user', 'admin']; } };
$osiris = (object) ['adminTypes' => new class { public function find() { return []; } }];
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});
foreach (['initiatives', 'activities'] as $entity) {
    $config = (object) [
        'entity' => $entity, 'entityLabel' => 'Test', 'redirectUrl' => '/save', 'backUrl' => '/back',
        'canBeCopied' => $entity === 'activities', 'tags' => [],
        'systemFields' => $entity === 'initiatives' ? FormBuilder::INITIATIVE_SYSTEM_FIELDS : [],
        'modules' => ['title' => ['id' => 'title', 'name' => 'Title', 'type' => 'field', 'width' => 12]],
    ];
    $fields = [['id' => 'custom-example', 'type' => 'custom', 'props' => []]];
    $type = ['id' => 'example', 'name' => 'Example'];
    $copy = false;
    ob_start();
    include BASEPATH . '/pages/admin/form-builder.php';
    $html = ob_get_clean();
    check(str_contains($html, 'const ALL = {'), 'The module catalog must be available to JavaScript.');
    check(str_contains($html, 'data-type="custom"'), 'Existing custom fields must retain their type.');
    check(str_contains($html, 'let authorFields') === ($entity === 'activities'), 'Activity-specific checks must not block other entities.');
    if ($entity === 'initiatives') {
        check(str_contains($html, 'ph-lock-simple'), 'System fields must have a visible lock.');
        check(str_contains($html, '"title":{"required":true}'), 'Trusted system rules must reach the UI.');
    }
    // Optional output for checking the rendered JavaScript syntax.
    if (isset($argv[1])) file_put_contents($argv[1] . '/' . $entity . '.html', $html);
}
restore_error_handler();
echo "Form builder regression tests passed.\n";
