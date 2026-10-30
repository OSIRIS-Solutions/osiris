<?php

// Run with: php tests/profile-contact.php (no database required).
require __DIR__ . '/../php/_config.php';

function checkContact($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

$all = array_fill_keys(['mail', 'slack', 'teams', 'matrix', 'other'], true);
$person = [
    'mail' => 'person@example.org',
    'socials' => [
        'matrix' => 'https://matrix.to/#/@person:example.org',
        'slack' => 'https://example.slack.com/team/U12345678',
        'teams' => 'https://teams.microsoft.com/l/chat/0/0?users=person%40example.org',
        'github' => 'https://github.com/person'
    ],
    'contact-link' => 'https://example.org/book?person=1&duration=30'
];
$methods = getProfileContactMethods($person, $all);
checkContact(array_keys($methods) === array_keys($all), 'Use a stable fallback order with email first.');
checkContact($methods['mail'] === 'mailto:person@example.org', 'Use the profile email.');
checkContact($methods['matrix'] === $person['socials']['matrix'], 'Keep existing Matrix URLs intact.');
checkContact(getProfileContactMethods($person, []) === [], 'Missing organisation settings expose no contact methods.');
checkContact(getProfileContactMethods([], $all) === [], 'Missing profile details expose no contact methods.');
checkContact(array_keys(getProfileContactMethods($person, ['mail' => '0', 'matrix' => '1', 'slack' => 1, 'teams' => false])) === ['slack', 'matrix'], 'Intersect organisation permissions and profile data across saved boolean formats.');
checkContact(array_keys(getProfileContactMethods(['mail' => $person['mail']], $all)) === ['mail'], 'An email address alone does not imply a Teams account.');

foreach (['javascript:alert(1)', 'data:text/html,test', 'https://matrix.to.evil.example/#/@person:example.org', 'https://evil.example'] as $url) {
    checkContact(getProfileContactMethods(['socials' => ['matrix' => $url]], $all) === [], 'Reject unsafe schemes and incorrect service hosts.');
}
checkContact(getProfileContactMethods(['mail' => 'invalid', 'socials' => ['slack' => 'https://evilslack.com/user']], $all) === [], 'Reject invalid emails and misleading Slack hostnames.');
checkContact(getProfileContactMethods(['socials' => ['teams' => 'https://teams.cloud.microsoft/l/chat/0/0?users=person%40example.org']], $all) !== [], 'Accept Teams links on the alternate host.');
checkContact(getProfileContactMethods(['socials' => ['matrix' => [], 'slack' => null]], $all) === [], 'Ignore malformed or empty contact values.');

$removed = $person;
unset($removed['socials']['matrix']);
checkContact(!isset(getProfileContactMethods($removed, $all)['matrix']), 'Removing a profile contact removes that method.');

echo "Profile contact checks passed.\n";
