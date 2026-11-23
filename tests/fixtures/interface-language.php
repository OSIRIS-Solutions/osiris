<?php

// Resolve real interface keys while keeping these tests independent of application bootstrap.
require_once dirname(__DIR__, 2) . '/php/Language.php';

function t($key, array $replace = []): string
{
    $language = $GLOBALS['testLanguage'] ?? 'en';
    static $catalogue;
    $catalogue ??= new Language(dirname(__DIR__, 2) . '/lang');
    $text = $catalogue->resolve((string) $key, $language, 'en');
    foreach ($replace as $key => $value) $text = str_replace('{{' . $key . '}}', (string) $value, $text);
    return $text;
}

function lang($en, $de = null, array $replace = []): string
{
    if ($de !== null) return ($GLOBALS['testLanguage'] ?? 'en') === 'de' ? $de : $en;
    return t((string) $en, $replace);
}
