<?php

/**
 * Shared standalone layout for all public authentication pages.
 *
 * Page-specific state and validation are prepared by the route. The allowlisted
 * $page value only selects the content rendered inside the central card.
 */

$page = $page ?? 'login';
$authPages = [
    'login' => BASEPATH . '/pages/user/auth/login.php',
    'forgot-password' => BASEPATH . '/pages/user/auth/forgot-password.php',
    'register' => BASEPATH . '/pages/user/auth/register.php',
    'reset-password' => BASEPATH . '/pages/user/auth/reset-password.php',
];
if (!isset($authPages[$page])) {
    $page = 'login';
}

$authPageTitles = [
    'login' => lang('Login | OSIRIS', 'Anmelden | OSIRIS'),
    'forgot-password' => lang('Forgot password | OSIRIS', 'Passwort vergessen | OSIRIS'),
    'register' => lang('Create account | OSIRIS', 'Account erstellen | OSIRIS'),
    'reset-password' => lang('Reset password | OSIRIS', 'Passwort zurücksetzen | OSIRIS'),
];

$affiliation = (string) $Settings->get('affiliation', '');
$affiliationDetails = $Settings->get('affiliation_details', []);
$affiliationDetails = is_array($affiliationDetails) ? $affiliationDetails : [];
$affiliationName = (string) ($affiliationDetails['name'] ?? $affiliation ?: 'Institution');
$affiliationLink = (string) ($affiliationDetails['link'] ?? '');
$affiliationLogo = $Settings->printLogo('institute-image');
$authMessages = $authMessages ?? [];
if (!empty($_SESSION['msg'])) {
    $authMessages[] = [
        'text' => (string) $_SESSION['msg'],
        'type' => (string) ($_SESSION['msg_type'] ?? 'info'),
    ];
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}

$authMessages = array_values(array_filter($authMessages, static function ($message) {
    return !empty(is_array($message) ? ($message['text'] ?? '') : $message);
}));

$impressContent = $Settings->get('impress');
if (empty($impressContent)) {
    $impressContent = file_get_contents(BASEPATH . '/pages/impressum.html');
}
$privacyContent = $Settings->get('privacy');
if (empty($privacyContent)) {
    $privacyContent = file_get_contents(BASEPATH . '/pages/privacy.html');
}

$pageLanguage = lang('en', 'de');
$accessibilityClasses = trim(implode(' ', [
    $_COOKIE['D3-accessibility-contrast'] ?? '',
    $_COOKIE['D3-accessibility-transitions'] ?? '',
    $_COOKIE['D3-accessibility-dyslexia'] ?? '',
]));
?>
<!DOCTYPE html>
<html lang="<?= e($pageLanguage) ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e(lang('Log in to OSIRIS.', 'Bei OSIRIS anmelden.')) ?>">
    <meta name="theme-color" content="<?= e($Settings->get('primary_color') ?? '#008083') ?>">
    <title><?= e($authPageTitles[$page]) ?></title>
    <link rel="icon" href="<?= ROOTPATH ?>/img/favicon.png">
    <link href="<?= ROOTPATH ?>/css/phosphoricons/regular/style.css?v=<?= OSIRIS_BUILD ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/main.css?v=<?= OSIRIS_BUILD ?>">
    <?= $Settings->renderAdditionalStylesheetLinks() ?>
    <link rel="stylesheet" href="<?= ROOTPATH ?>/custom_style.css?v=<?= OSIRIS_BUILD ?>">

    <style>
        .osiris-login {
            --login-surface: rgba(255, 255, 255, .92);
            --login-ink: var(--text-color);
            --login-muted: var(--muted-color);
            min-height: 100vh;
            min-height: 100svh;
            margin: 0;
            color: var(--login-ink);
            background:
                radial-gradient(circle at 12% 34%, var(--secondary-color-20) 0, transparent 27rem),
                radial-gradient(circle at 94% 85%, var(--primary-color-20) 0, transparent 34rem),
                #f5f8f7;
            font-family: var(--font-family);
            height: max-content;
        }

        .osiris-login *,
        .osiris-login *::before,
        .osiris-login *::after {
            box-sizing: border-box;
        }

        .osiris-login .page {
            position: relative;
            display: grid;
            grid-template-rows: auto 1fr auto;
            min-height: 100vh;
            min-height: 100svh;
            overflow: hidden;
            isolation: isolate;
        }

        .osiris-login .header,
        .osiris-login .main,
        .osiris-login .footer {
            width: min(100% - 4rem, 132rem);
            margin-inline: auto;
        }

        .osiris-login .header {
            display: flex;
            min-height: 10rem;
            align-items: center;
            justify-content: space-between;
            gap: 3rem;
            padding-bottom: 2rem;
        }

        .osiris-login .osiris-logo {
            display: block;
            width: clamp(13rem, 15vw, 18.5rem);
            height: auto;
        }

        .osiris-login .institute {
            display: flex;
            max-width: min(35vw, 34rem);
            min-height: 4.5rem;
            align-items: center;
            justify-content: flex-end;
            color: var(--login-ink);
            font-size: 1.45rem;
            font-weight: 600;
            line-height: 1.25;
            text-align: right;
            text-decoration: none;
        }

        .osiris-login .institute-image {
            display: block;
            width: auto;
            max-width: 100%;
            height: auto;
            max-height: 6.2rem;
            object-fit: contain;
        }

        .osiris-login .main {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 6rem;
        }

        .osiris-login .network {
            position: absolute;
            z-index: -1;
            top: 50%;
            left: 50%;
            width: min(112rem, 94vw);
            height: auto;
            color: var(--primary-color);
            opacity: .42;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .osiris-login .network-path {
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-width: 1.2;
        }

        .osiris-login .network-path--soft {
            stroke: var(--secondary-color);
            stroke-dasharray: 4 12;
            opacity: .75;
            fill: none;
        }

        .osiris-login .network-node {
            fill: #f5f8f7;
            stroke: currentColor;
            stroke-width: 1.5;
        }

        .osiris-login .network-node--accent {
            fill: var(--secondary-color);
            stroke: #f5f8f7;
            stroke-width: 3;
        }

        .osiris-login .main::before,
        .osiris-login .main::after {
            position: absolute;
            z-index: -1;
            border-radius: 50%;
            content: '';
        }

        .osiris-login .main::before {
            top: 14%;
            left: calc(50% - 33rem);
            width: 1.8rem;
            height: 1.8rem;
            background: var(--secondary-color);
            box-shadow: 61rem 34rem 0 -.35rem var(--primary-color);
        }

        .osiris-login .main::after {
            right: calc(50% - 36rem);
            bottom: 5%;
            width: 15rem;
            height: 15rem;
            border: 1px dashed var(--secondary-color-30);
        }

        .osiris-login .card {
            position: relative;
            width: 100%;
            max-width: 52rem;
            padding: 3rem;
            border: 1px solid rgba(255, 255, 255, .8);
            border-radius: 2.6rem;
            background: var(--login-surface);
            box-shadow: 0 2.4rem 7rem rgba(20, 55, 57, .13);
            backdrop-filter: blur(18px);
        }

        .osiris-login .card--wide {
            max-width: 76rem;
        }

        .osiris-login .card::before {
            position: absolute;
            top: 3rem;
            left: 0;
            width: .45rem;
            height: 7rem;
            border-radius: 0 1rem 1rem 0;
            background: linear-gradient(var(--secondary-color), var(--primary-color));
            content: '';
        }

        .osiris-login .card-title {
            margin: 0 !important;
            color: var(--login-ink);
            font-size: clamp(3rem, 3vw, 4.2rem);
            font-weight: 650;
            line-height: 1.12;
        }

        .osiris-login .card-copy {
            margin: 1.2rem 0 2.8rem;
            color: var(--login-muted);
            font-size: 1.55rem;
            line-height: 1.5;
        }

        .osiris-login .message {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin: 0 0 2.2rem;
            padding: 1.3rem 1.5rem;
            border: 1px solid var(--danger-color-20, #f1b7b7);
            border-radius: 1.2rem;
            color: var(--danger-color-dark, #8b1f1f);
            background: var(--danger-color-20, #fff0f0);
            font-size: 1.4rem;
            line-height: 1.45;
        }

        .osiris-login .message i {
            flex: 0 0 auto;
            margin-top: .15rem;
            font-size: 1.8rem;
        }

        .osiris-login .message-success {
            border-color: var(--success-color-20);
            color: var(--success-color-dark);
            background: var(--success-color-20);
        }

        .osiris-login .message-info {
            border-color: var(--primary-color-20);
            color: var(--primary-color-dark);
            background: var(--primary-color-20);
        }

        .osiris-login .form-group label {
            font-weight: 600;
        }

        .osiris-login .password-toggle {
            position: absolute;
            top: 50%;
            right: .8rem;
            display: grid;
            width: 4rem;
            height: 4rem;
            padding: 0;
            border: 0;
            border-radius: .8rem;
            color: var(--login-muted);
            background: transparent;
            font-size: 2rem;
            place-items: center;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .osiris-login .password-toggle:hover,
        .osiris-login .password-toggle:focus-visible {
            color: var(--primary-color);
            background: var(--primary-color-20);
            outline: 0;
        }

        .osiris-login .options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            margin: .2rem 0 2.4rem;
            font-size: 1.35rem;
        }

        .osiris-login .checkbox {
            display: inline-flex;
            align-items: center;
            gap: .8rem;
            cursor: pointer;
        }

        .osiris-login .checkbox input {
            width: 1.8rem;
            height: 1.8rem;
            margin: 0;
            accent-color: var(--primary-color);
        }

        .osiris-login .link {
            color: var(--primary-color-dark);
            font-weight: 600;
            text-decoration: none;
        }

        .osiris-login .link:hover {
            color: var(--secondary-color-dark);
            text-decoration: underline;
            text-underline-offset: .25em;
        }

        .osiris-login .submit {
            display: inline-flex;
            width: 100%;
            min-height: 5.5rem;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 1.2rem 2rem;
            border: 0;
            border-radius: 1.2rem;
            color: #fff;
            background: linear-gradient(115deg, var(--primary-color-dark), var(--primary-color));
            box-shadow: 0 1rem 2.4rem var(--primary-color-20);
            font: inherit;
            font-size: 1.55rem;
            font-weight: 700;
            text-decoration: none;
            transition: transform .2s, box-shadow .2s, filter .2s;
            cursor: pointer;
        }

        .osiris-login .submit:hover {
            color: #fff;
            box-shadow: 0 1.3rem 3rem var(--primary-color-30);
            filter: saturate(1.1);
            transform: translateY(-2px);
        }

        .osiris-login .submit:focus-visible,
        .osiris-login .link:focus-visible,
        .osiris-login .back-link:focus-visible,
        .osiris-login .portal-cta:focus-visible,
        .osiris-login .institute:focus-visible,
        .osiris-login .footer a:focus-visible,
        .osiris-login .language:focus-visible,
        .osiris-login .legal-trigger:focus-visible,
        .osiris-login .dialog-close:focus-visible {
            border-radius: .5rem;
            outline: .3rem solid var(--secondary-color);
            outline-offset: .3rem;
        }

        .osiris-login .secondary-action {
            margin: 2.1rem 0 0;
            color: var(--login-muted);
            font-size: 1.35rem;
            text-align: center;
        }

        .osiris-login .back-link {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 1.8rem;
            color: var(--login-muted);
            font-size: 1.3rem;
            font-weight: 600;
            text-decoration: none;
        }

        .osiris-login .back-link:hover {
            color: var(--primary-color-dark);
        }

        .osiris-login .form-grid {
            display: grid;
            gap: 0 1.5rem;
        }

        .osiris-login .form-grid--2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .osiris-login .form-grid--name {
            grid-template-columns: minmax(10rem, .55fr) repeat(2, minmax(0, 1fr));
        }

        .osiris-login .choice-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem 1.8rem;
            margin: 0 0 2rem;
            padding: 0;
            border: 0;
        }

        .osiris-login .choice-group legend {
            width: 100%;
            margin-bottom: .7rem;
            color: var(--login-ink);
            font-size: 1.4rem;
            font-weight: 600;
        }

        .osiris-login .choice,
        .osiris-login .registration-checkbox {
            display: inline-flex;
            align-items: center;
            gap: .7rem;
            color: var(--login-muted);
            font-size: 1.3rem;
            cursor: pointer;
        }

        .osiris-login .choice input,
        .osiris-login .registration-checkbox input {
            width: 1.7rem;
            height: 1.7rem;
            margin: 0;
            accent-color: var(--primary-color);
        }

        .osiris-login .register-submit {
            margin-top: 2.4rem;
        }

        .osiris-login .portal-divider {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            margin: 2.5rem 0 1.7rem;
            color: var(--login-muted);
            font-size: 1.2rem;
            text-transform: uppercase;
        }

        .osiris-login .portal-divider::before,
        .osiris-login .portal-divider::after {
            flex: 1;
            height: 1px;
            background: #dce4e3;
            content: '';
        }

        .osiris-login .portal-cta {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 1.3rem;
            width: 100%;
            min-height: 6.4rem;
            padding: 1rem 1.3rem;
            border: 1px solid var(--primary-color-30);
            border-radius: 1.2rem;
            color: var(--primary-color-dark);
            background: var(--primary-color-20);
            text-align: left;
            text-decoration: none;
            transition: border-color .2s, background .2s, transform .2s;
        }

        .osiris-login .portal-cta > i:first-child {
            display: grid;
            width: 4rem;
            height: 4rem;
            border-radius: 1rem;
            color: #fff;
            background: var(--primary-color);
            font-size: 2.2rem;
            place-items: center;
        }

        .osiris-login .portal-cta strong,
        .osiris-login .portal-cta small {
            display: block;
        }

        .osiris-login .portal-cta strong {
            font-size: 1.45rem;
            line-height: 1.3;
        }

        .osiris-login .portal-cta small {
            margin-top: .15rem;
            color: var(--login-muted);
            font-size: 1.2rem;
            font-weight: 400;
            line-height: 1.3;
        }

        .osiris-login .portal-cta > i:last-child {
            font-size: 1.9rem;
            transition: transform .2s;
        }

        .osiris-login .portal-cta:hover {
            border-color: var(--primary-color);
            color: var(--primary-color-dark);
            background: var(--primary-color-30);
            transform: translateY(-1px);
        }

        .osiris-login .portal-cta:hover > i:last-child {
            transform: translateX(.3rem);
        }

        .osiris-login .demo {
            margin-top: 2.2rem;
            padding: 1.2rem 1.5rem;
            border-left: .3rem solid var(--secondary-color);
            border-radius: 0 .8rem .8rem 0;
            color: var(--login-muted);
            background: var(--secondary-color-20);
            font-size: 1.3rem;
            line-height: 1.45;
        }

        .osiris-login .footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            padding-block: 2.4rem 3rem;
            color: var(--login-muted);
            font-size: 1.25rem;
        }

        .osiris-login .footer nav {
            display: flex;
            flex-wrap: wrap;
            gap: 1.2rem 2.4rem;
        }

        .osiris-login .footer a,
        .osiris-login .language,
        .osiris-login .legal-trigger {
            padding: 0;
            border: 0;
            color: var(--login-muted);
            background: transparent;
            font: inherit;
            text-decoration: none;
            cursor: pointer;
        }

        .osiris-login .footer a:hover,
        .osiris-login .language:hover,
        .osiris-login .legal-trigger:hover {
            color: var(--primary-color-dark);
        }

        .osiris-login .auth-dialog {
            width: min(80rem, calc(100% - 3rem));
            max-height: min(82vh, 85rem);
            padding: 0;
            border: 0;
            border-radius: 1.8rem;
            color: var(--login-ink);
            background: #fff;
            box-shadow: 0 2.4rem 8rem rgba(20, 55, 57, .24);
        }

        .osiris-login .auth-dialog::backdrop {
            background: rgba(10, 28, 31, .58);
            backdrop-filter: blur(5px);
        }

        .osiris-login .dialog-header {
            position: sticky;
            z-index: 1;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            padding: 2rem 2.4rem;
            border-bottom: 1px solid #e2e8e7;
            background: rgba(255, 255, 255, .96);
        }

        .osiris-login .dialog-header h2 {
            margin: 0;
            font-size: 2.2rem;
        }

        .osiris-login .dialog-close {
            display: grid;
            flex: 0 0 auto;
            width: 4rem;
            height: 4rem;
            padding: 0;
            border: 0;
            border-radius: 50%;
            color: var(--login-ink);
            background: var(--primary-color-20);
            font-size: 2rem;
            place-items: center;
            cursor: pointer;
        }

        .osiris-login .dialog-close:hover {
            color: #fff;
            background: var(--primary-color);
        }

        .osiris-login .dialog-content {
            max-height: calc(82vh - 8rem);
            padding: 2.4rem;
            overflow: auto;
            font-size: 1.45rem;
            line-height: 1.6;
        }

        .osiris-login .dialog-content > :first-child,
        .osiris-login .dialog-content .container > :first-child {
            margin-top: 0;
        }

        .osiris-login .accessibility-options {
            display: grid;
            gap: 1.2rem;
            margin: 2rem 0;
        }

        .osiris-login .accessibility-option {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: .2rem 1rem;
            padding: 1.3rem;
            border: 1px solid #dce4e3;
            border-radius: 1rem;
            cursor: pointer;
        }

        .osiris-login .accessibility-option input {
            grid-row: 1 / 3;
            width: 1.8rem;
            height: 1.8rem;
            margin-top: .25rem;
            accent-color: var(--primary-color);
        }

        .osiris-login .accessibility-option small {
            color: var(--login-muted);
        }

        .osiris-login .language i {
            margin-right: .4rem;
            vertical-align: -.15em;
        }

        @media (max-width: 900px) {
            .osiris-login .header,
            .osiris-login .main,
            .osiris-login .footer {
                width: min(100% - 3rem, 60rem);
            }

            .osiris-login .header {
                min-height: 8rem;
            }

            .osiris-login .main {
                padding-block: 1rem 4rem;
            }

            .osiris-login .main::before {
                left: 2rem;
                box-shadow: calc(100vw - 6rem) 38rem 0 -.35rem var(--primary-color);
            }

            .osiris-login .network {
                width: 110rem;
                opacity: .28;
            }

            .osiris-login .card {
                max-width: none;
            }
        }

        @media (max-width: 520px) {
            .osiris-login .header,
            .osiris-login .main,
            .osiris-login .footer {
                width: min(100% - 2.4rem, 60rem);
            }

            .osiris-login .header {
                gap: 1.5rem;
            }

            .osiris-login .institute {
                max-width: 42vw;
                font-size: 1.2rem;
            }

            .osiris-login .institute-image {
                max-height: 4.8rem;
            }

            .osiris-login .card {
                padding: 3rem 2.2rem;
                border-radius: 2rem;
            }

            .osiris-login .options {
                align-items: flex-start;
                flex-direction: column;
            }

            .osiris-login .form-grid--2,
            .osiris-login .form-grid--name {
                grid-template-columns: 1fr;
            }

            .osiris-login .footer {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .osiris-login *,
            .osiris-login *::before,
            .osiris-login *::after {
                scroll-behavior: auto !important;
                transition: none !important;
            }
        }

        .osiris-login.high-contrast {
            --login-surface: #fff;
            --login-ink: #000;
            --login-muted: #222;
            background: #fff;
        }

        .osiris-login.high-contrast .card {
            border: 2px solid #000;
            box-shadow: none;
        }
    </style>
</head>

<body class="osiris-login <?= e($accessibilityClasses) ?>">
    <div class="page">
        <header class="header">
            <a href="<?= ROOTPATH ?>/" aria-label="OSIRIS">
                <img class="osiris-logo" id="osiris-logo" src="<?= ROOTPATH ?>/img/logo.svg" alt="OSIRIS">
            </a>

            <?php if ($affiliationLogo !== '') { ?>
                <?php if ($affiliationLink !== '') { ?>
                    <a class="institute" href="<?= e($affiliationLink) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($affiliationName) ?>">
                        <?= $affiliationLogo ?>
                    </a>
                <?php } else { ?>
                    <div class="institute" aria-label="<?= e($affiliationName) ?>">
                        <?= $affiliationLogo ?>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="institute"><?= e($affiliationName) ?></div>
            <?php } ?>
        </header>

        <main class="main">
            <svg class="network" viewBox="0 0 1000 650" aria-hidden="true" focusable="false">
                <path class="network-path" d="M58 414C177 238 302 188 436 286S690 468 940 224" />
                <path class="network-path" d="M87 178C246 317 302 434 474 397S723 177 913 372" />
                <path class="network-path network-path--soft" d="M24 304C207 382 324 83 501 189S731 524 976 328" />
                <path class="network-path network-path--soft" d="M135 530C271 471 331 549 500 468S731 91 870 135" />
                <circle class="network-node" cx="87" cy="178" r="7" />
                <circle class="network-node--accent" cx="175" cy="301" r="9" />
                <circle class="network-node" cx="267" cy="227" r="6" />
                <circle class="network-node" cx="436" cy="286" r="8" />
                <circle class="network-node--accent" cx="500" cy="468" r="8" />
                <circle class="network-node" cx="654" cy="354" r="6" />
                <circle class="network-node" cx="758" cy="221" r="8" />
                <circle class="network-node--accent" cx="913" cy="372" r="9" />
                <circle class="network-node" cx="870" cy="135" r="6" />
            </svg>

            <section class="card <?= $page === 'register' && empty($registrationRequiresToken) ? 'card--wide' : '' ?>" aria-labelledby="auth-title">
                <?php foreach ($authMessages as $message) {
                    $message = is_array($message) ? $message : ['text' => $message, 'type' => 'error'];
                    $messageType = $message['type'] ?? 'error';
                    $messageClass = $messageType === 'success' ? 'message-success' : ($messageType === 'info' ? 'message-info' : '');
                    $messageIcon = $messageType === 'success' ? 'ph-check-circle' : ($messageType === 'info' ? 'ph-info' : 'ph-warning-circle');
                ?>
                    <div class="message <?= $messageClass ?>" role="message" aria-live="polite">
                        <i class="ph <?= $messageIcon ?>" aria-hidden="true"></i>
                        <div><?= e($message['text'] ?? '') ?></div>
                    </div>
                <?php } ?>

                <?php include $authPages[$page]; ?>

                <?php if ($Settings->featureEnabled('portal-public')) { ?>
                    <div class="portal-divider"><span><?= lang('or', 'oder') ?></span></div>
                    <a class="portal-cta" href="<?= ROOTPATH ?>/portal/info">
                        <i class="ph ph-globe-hemisphere-west" aria-hidden="true"></i>
                        <span>
                            <strong><?= lang('Open public portal', 'Öffentliches Portal öffnen') ?></strong>
                            <small><?= lang('Discover research without logging in', 'Forschung ohne Anmeldung entdecken') ?></small>
                        </span>
                        <i class="ph ph-arrow-right" aria-hidden="true"></i>
                    </a>
                <?php } ?>
            </section>
        </main>

        <footer class="footer">
            <nav aria-label="<?= e(lang('Legal information', 'Rechtliche Informationen')) ?>">
                <a href="https://osiris-app.de" target="_blank" rel="noopener noreferrer" class="link"><?= lang('About OSIRIS', 'Über OSIRIS') ?></a>
                <button class="legal-trigger" type="button" data-dialog-open="accessibility-dialog"><?= lang('Accessibility', 'Barrierefreiheit') ?></button>
                <button class="legal-trigger" type="button" data-dialog-open="impress-dialog"><?= lang('Legal notice', 'Impressum') ?></button>
                <button class="legal-trigger" type="button" data-dialog-open="privacy-dialog"><?= lang('Privacy', 'Datenschutz') ?></button>
            </nav>

            <form action="<?= ROOTPATH ?>/set-preferences" method="get">
                <input type="hidden" name="language" value="<?= lang('de', 'en') ?>">
                <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI']) ?>">
                <button class="language" type="submit">
                    <i class="ph ph-translate" aria-hidden="true"></i>
                    <?= lang('Deutsch', 'English') ?>
                </button>
            </form>
        </footer>

        <dialog class="auth-dialog" id="accessibility-dialog" aria-labelledby="accessibility-title">
            <div class="dialog-header">
                <h2 id="accessibility-title"><?= lang('Accessibility', 'Barrierefreiheit') ?></h2>
                <button class="dialog-close" type="button" data-dialog-close aria-label="<?= e(lang('Close', 'Schließen')) ?>">
                    <i class="ph ph-x" aria-hidden="true"></i>
                </button>
            </div>
            <div class="dialog-content">
                <p>
                    <?= lang(
                        'OSIRIS is committed to making the platform accessible to everyone. You can adjust the display here.',
                        'OSIRIS setzt sich dafür ein, die Plattform für alle Menschen zugänglich zu machen. Hier kannst du die Darstellung anpassen.'
                    ) ?>
                </p>
                <form action="<?= ROOTPATH ?>/set-preferences" method="get">
                    <input type="hidden" name="accessibility[check]">
                    <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI']) ?>">
                    <div class="accessibility-options">
                        <label class="accessibility-option" for="auth-set-contrast">
                            <input id="auth-set-contrast" type="checkbox" name="accessibility[contrast]" value="high-contrast" <?= !empty($_COOKIE['D3-accessibility-contrast'] ?? '') ? 'checked' : '' ?>>
                            <strong><?= lang('High contrast', 'Erhöhter Kontrast') ?></strong>
                            <small><?= lang('Enhances contrast for better readability.', 'Erhöht den Kontrast für eine bessere Lesbarkeit.') ?></small>
                        </label>
                        <label class="accessibility-option" for="auth-set-transitions">
                            <input id="auth-set-transitions" type="checkbox" name="accessibility[transitions]" value="without-transitions" <?= !empty($_COOKIE['D3-accessibility-transitions'] ?? '') ? 'checked' : '' ?>>
                            <strong><?= lang('Reduce motion', 'Verringerte Bewegung') ?></strong>
                            <small><?= lang('Reduces animations and motion effects.', 'Reduziert Animationen und Bewegungseffekte.') ?></small>
                        </label>
                        <label class="accessibility-option" for="auth-set-dyslexia">
                            <input id="auth-set-dyslexia" type="checkbox" name="accessibility[dyslexia]" value="dyslexia" <?= !empty($_COOKIE['D3-accessibility-dyslexia'] ?? '') ? 'checked' : '' ?>>
                            <strong><?= lang('Dyslexia mode', 'Dyslexie-Modus') ?></strong>
                            <small><?= lang('Uses a font designed for improved readability.', 'Verwendet eine Schriftart für eine verbesserte Lesbarkeit.') ?></small>
                        </label>
                    </div>
                    <button class="submit" type="submit"><?= lang('Apply settings', 'Einstellungen anwenden') ?></button>
                </form>
            </div>
        </dialog>

        <dialog class="auth-dialog" id="impress-dialog" aria-labelledby="impress-title">
            <div class="dialog-header">
                <h2 id="impress-title"><?= lang('Legal notice', 'Impressum') ?></h2>
                <button class="dialog-close" type="button" data-dialog-close aria-label="<?= e(lang('Close', 'Schließen')) ?>">
                    <i class="ph ph-x" aria-hidden="true"></i>
                </button>
            </div>
            <div class="dialog-content">
                <?= $impressContent ?: '<p>' . lang('No legal notice available.', 'Kein Impressum verfügbar.') . '</p>' ?>
            </div>
        </dialog>

        <dialog class="auth-dialog" id="privacy-dialog" aria-labelledby="privacy-title">
            <div class="dialog-header">
                <h2 id="privacy-title"><?= lang('Privacy', 'Datenschutz') ?></h2>
                <button class="dialog-close" type="button" data-dialog-close aria-label="<?= e(lang('Close', 'Schließen')) ?>">
                    <i class="ph ph-x" aria-hidden="true"></i>
                </button>
            </div>
            <div class="dialog-content">
                <?= $privacyContent ?: '<p>' . lang('No privacy statement available.', 'Keine Datenschutzerklärung verfügbar.') . '</p>' ?>
            </div>
        </dialog>
    </div>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const input = this.closest('.input-wrap').querySelector('input');
                const showPassword = input.type === 'password';
                input.type = showPassword ? 'text' : 'password';
                this.setAttribute('aria-pressed', String(showPassword));
                this.setAttribute('aria-label', showPassword
                    ? <?= json_encode(lang('Hide password', 'Passwort ausblenden')) ?>
                    : <?= json_encode(lang('Show password', 'Passwort anzeigen')) ?>
                );
                this.querySelector('i').className = showPassword ? 'ph ph-eye-slash' : 'ph ph-eye';
            });
        });

        document.querySelectorAll('[data-dialog-open]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                document.getElementById(this.dataset.dialogOpen)?.showModal();
            });
        });

        document.querySelectorAll('[data-dialog-close]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                this.closest('dialog').close();
            });
        });

        document.querySelectorAll('dialog.auth-dialog').forEach(function(dialog) {
            dialog.addEventListener('click', function(event) {
                if (event.target === dialog) dialog.close();
            });
        });
    </script>
</body>

</html>
