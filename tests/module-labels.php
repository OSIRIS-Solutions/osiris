<?php
// Standard label keys, interface languages and explicit label overrides.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
$modules = new Modules();
check(array_filter($modules->all_modules, fn($module) => !isset($module['label']) || isset($module['label_de'])) === [], 'Standard module definitions store one label key.');
foreach (['en', 'de'] as $locale) {
    check(array_filter($modules->all_modules, fn($module) => $language->resolve($module['label'], $locale, 'en') === $module['label']) === [], "All module label keys resolve in $locale.");
}
$testInterfaceLanguage = 'fr';
check($modules->get_name('name') === $language->resolve('common.name', 'fr', 'en'), 'Default labels follow the interface language.');
$config = new FormBuilder('Initiative', '', entity: 'initiatives');
check($config->modules['name']['label'] === $modules->get_name('name') && $config->modules['name']['label_en'] === 'Name' && $config->modules['name']['label_de'] === 'Name', 'Builder defaults expose the current label and explicit property placeholders.');
ob_start(); $modules->print_module('name'); $html = ob_get_clean();
check(str_contains($html, $modules->get_name('name')) && !str_contains($html, 'common.name'), 'Forms display the translated standard rather than its key.');
$modules->setFields([['id' => 'name', 'type' => 'field', 'props' => ['label' => 'Our name', 'label_de' => 'Unser Name']]]);
check($modules->get_name('name') === 'Our name', 'Explicit institutional labels remain literal text.');
$modules->setFields([['id' => 'name', 'type' => 'field', 'props' => ['label' => '']]]);
check($modules->get_name('name') === $language->resolve('common.name', 'fr', 'en'), 'An empty override restores the translated default.');
$testInterfaceLanguage = 'en';
ob_end_clean(); echo $baselineOutput; echo 'Module label checks passed (' . ($checks - $baselineChecks) . " checks).\n";
