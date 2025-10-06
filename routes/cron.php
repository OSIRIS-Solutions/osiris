<?php

/**
 * Routing file for cron jobs
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.0
 * 
 * @copyright	Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

// GET /cron/digest?key=YOUR_SECRET
Route::get('/cron/digest', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/MailSender.php";
    date_default_timezone_set('Europe/Berlin');

    // --- Admin Default ---
    $digestDefault = $Settings->get('mail-digest', 'none'); // none|daily|weekly|monthly
    if ($digestDefault === 'none') return JSON::error('Digest is disabled in settings', 400);

    // --- Auth ---
    if (!defined('CRON_SECRET') || CRON_SECRET === 'please-change-this-secret') {
        return JSON::error('CRON_SECRET is not set properly in CONFIG.php', 500);
    }
    $secret = $_GET['key'] ?? '';
    if ($secret !== CRON_SECRET) return JSON::error('Unauthorized', 401);

    // --- Time point ---
    $now   = new DateTimeImmutable('now');         // Europe/Berlin
    $today = $now->format('Y-m-d');
    $dow   = (int)$now->format('N');               // 1=Mon ... 7=Sun
    $dom   = (int)$now->format('j');               // 1..31

    // --- Get Users ---
    $users = $osiris->persons->find([
        'is_active' => ['$ne' => false],
        'mail'   => ['$ne' => null]
    ], ['projection' => ['username' => 1, 'mail' => 1, 'lang' => 1, 'displayname' => 1, 'mail_digest' => 1, 'digest_last_sent' => 1]]);

    $sent = 0;
    $skipped = 0;
    $errors = 0;

    foreach ($users as $doc) {
        $u = DB::doc2Arr($doc);

        // 1) Get User Preference
        $userPref = $u['mail_digest'] ?? 'default';           // 'default' | 'none' | 'daily' | 'weekly' | 'monthly'
        $freq     = ($userPref === 'default') ? $digestDefault : $userPref;
        if ($freq === 'none') {
            $skipped++;
            continue;
        }

        // 2) Check if today is the right day
        $shouldToday = (
            ($freq === 'daily') ||
            ($freq === 'weekly'  && $dow === 1) ||
            ($freq === 'monthly' && $dom === 1)
        );
        if (!$shouldToday) {
            $skipped++;
            continue;
        }

        // 3) Prevent double sending on the same day
        $last = $u['digest_last_sent'] ?? null; // Y-m-d
        if ($last === $today) {
            $skipped++;
            continue;
        }

        // 4) Get Notifications
        $notifications = $DB->notifications(true, $u['username'] ?? null);

        // 5) Check if there is anything relevant to send
        $hasAny =
            (!empty($notifications['activity']['count'])) ||
            (!empty($notifications['queue']['count']))    ||
            (!empty($notifications['approval'])) ||
            (!empty($notifications['messages']));
        if (!$hasAny) {
            $skipped++;
            continue;
        }

        // 6) Build + send mail
        [$subject, $html, $text] = build_digest_email($u, $notifications, $freq);

        $sendRes = sendMail($u['mail'], $subject, $html, $text);
        // if ($sendRes !== null) {
        //     // Error sending mail
        //     $errors++;
        //     continue;
        // }

        // 7) set last sent
        $osiris->persons->updateOne(['_id' => $doc['_id']], [
            '$set' => ['digest_last_sent' => $today]
        ]);

        $sent++;
    }

    return JSON::ok(['status' => 'ok', 'sent' => $sent, 'skipped' => $skipped, 'errors' => $errors]);
});
