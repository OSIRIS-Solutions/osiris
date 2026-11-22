<?php

// Resolve real interface keys while keeping these tests independent of application bootstrap.
require_once dirname(__DIR__, 2) . '/php/Language.php';

function lang($en, $de = null, array $replace = []): string
{
    $language = $GLOBALS['testLanguage'] ?? 'en';
    if ($de !== null) return $language === 'de' ? $de : $en;
    static $catalogue;
    $catalogue ??= new Language(dirname(__DIR__, 2) . '/lang');
    $text = $catalogue->resolve((string) $en, $language, 'en');
    foreach ($replace as $key => $value) $text = str_replace('{{' . $key . '}}', (string) $value, $text);
    return $text;
}
