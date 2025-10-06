<?php
include_once 'init.php';

function sendMail(
    $to,
    $subject,
    $message,
    $altMessage = null
) {
    global $osiris;
    // get mail settings:
    $mail = $osiris->adminGeneral->findOne(['key' => 'mail']);
    $mail = DB::doc2Arr($mail['value'] ?? []);

    $msg = 'mail-sent';

    $Mailer = new PHPMailer\PHPMailer\PHPMailer(true);

    $Mailer->CharSet  = 'UTF-8';
    $Mailer->Encoding = 'quoted-printable'; // or 'base64'

    if (!empty($mail['smtp_server'])) {
        $Mailer->isSMTP();
        $Mailer->Host = $mail['smtp_server'] ?? 'localhost';
        if (isset($mail['smtp_user']) && isset($mail['smtp_password'])) {
            $Mailer->SMTPAuth = true;
            $Mailer->Username = $mail['smtp_user'];
            $Mailer->Password = $mail['smtp_password'];
        } else {
            $Mailer->SMTPAuth = false;
        }

        if (isset($mail['smtp_security'])) {
            if ($mail['smtp_security'] == 'ssl')
                $Mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            elseif ($mail['smtp_security'] == 'tls')
                $Mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }

        $Mailer->Port = $mail['smtp_port'] ?? 25;
    }

    // $Mailer->SMTPDebug = 2; // oder 3
    // $Mailer->Debugoutput = 'html';

    $Mailer->setFrom($mail['email'] ?? 'no-reply@osiris-app.de', 'OSIRIS');
    $Mailer->addAddress($to);
    $Mailer->isHTML(true);

    $Mailer->Subject = $subject;
    $Mailer->Body = $message;
    if ($altMessage !== null) $Mailer->AltBody = $altMessage;

    try {
        $Mailer->send();
    } catch (PHPMailer\PHPMailer\Exception $e) {
        $msg = $Mailer->ErrorInfo;
    }
    $_SESSION['msg'] = $msg;
    return $msg;
}


function buildNotificationMail($title, $html, $linkText, $linkUrl)
{
    $linkUrl = $_SERVER['HTTP_HOST'] . ROOTPATH . $linkUrl;
    return '
        <div style="font-family: Arial, sans-serif; color: #333;">
            <h2 style="color: #008083;">' . htmlspecialchars($title) . '</h2>
            ' . $html . '
            <p style="margin-top:20px;">
                <a href="' . htmlspecialchars($linkUrl) . '" style="background-color: #f78104; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    ' . htmlspecialchars($linkText) . '
                </a>
            </p>
            <p style="font-size: 12px; color: #777; margin-top:40px;">Dies ist eine automatische Nachricht von OSIRIS. Bitte antworte nicht darauf.</p>
        </div>
    ';
}

function build_digest_email(array $user, array $n, string $frequency = "weekly"): array
{
    global $Settings;
    $language = $user['lang'] ?? 'de';
    $lang = function ($en, $de) use ($language) {
        return $language === 'de' ? $de : $en;
    };

    $username     = htmlspecialchars($user['displayname'] ?? $user['username'] ?? '');
    $issuesCount  = (int)($n['activity']['count'] ?? 0);
    $issuesList   = $n['activity']['values'] ?? [];
    $queueCount   = (int)($n['queue']['count'] ?? 0);
    $hasVersion   = !empty($n['version']);
    $messagesCount = !empty($n['messages']) ? count(DB::doc2Arr($n['messages'] ?? [])) : 0;
    $approval = $n['approval'] ?? null;
    $quarter = $approval['key'] ?? null;


    $subject = $lang('Your OSIRIS digest', 'Dein OSIRIS-Digest');
    $baseUrl = ($_SERVER['HTTPS'] ?? 'off') === 'on' ? 'https' : 'http';
    $baseUrl .= '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ROOTPATH;

    $colors = $Settings->get('colors');
    $primary = $colors['primary'] ?? '#008083';
    $secondary = $colors['secondary'] ?? '#f78104';

    ob_start(); ?>
    <div style="font-family:Arial,Helvetica,sans-serif;max-width:640px;margin:0 auto;background:#fff;border:1px solid <?= $primary ?>;border-radius:.5rem;overflow:hidden">
        <div style="background:<?= $primary ?>;color:#fff;padding:14px 18px">
            <h2 style="margin:0;font-size:18px;">OSIRIS <?= ucfirst($frequency) ?></h2>
            <div style="opacity:.85;font-size:12px;"><?= $lang('Date', 'Datum') ?>: <?= date('d.m.Y') ?></div>
        </div>

        <div style="padding:18px">
            <p style="margin:0 0 12px 0;"><?= $lang('Hello', 'Hallo') ?> <strong><?= $username ?></strong>,</p>
            <p style="margin:0 0 18px 0;"><?= $lang('Here is a summary of your notifications in OSIRIS.', 'Hier ist eine Zusammenfassung deiner Benachrichtigungen in OSIRIS.') ?></p>

            <?php if ($approval): ?>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 14px 0">
                    <tr>
                        <td style="padding:12px;background:#63a30820;border-radius:.5rem">
                            <div style="font-weight:bold;color:#63a308;margin-bottom:6px;"><?= $lang('Quarterly approval pending', 'Quartalsfreigabe ausstehend') ?></div>
                            <p>
                                <?= $lang('The past quarter (' . ($quarter) . ') has not been approved yet. Please review your activities and approve the quarter for the quarterly controlling.', 'Das vergangene Quartal (' . ($quarter) . ') wurde von dir noch nicht freigegeben. Bitte überprüfe deine Aktivitäten und gib das Quartal für das Quartalscontrolling frei.') ?>
                            </p>
                            <a href="<?= $baseUrl ?>/my-year/<?= $user['username'] ?? '' ?>?quarter=<?= $quarter ?>" style="display:inline-block;padding:8px 12px;background:#63a308;color:#fff;text-decoration:none;border-radius:.5rem;"><?= $lang('Review & Approve', 'Überprüfen & Freigeben') ?></a>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>

            <?php if ($issuesCount > 0): ?>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 14px 0">
                    <tr>
                        <td style="padding:12px;background:#fff5f5;border-radius:.5rem">
                            <div style="font-weight:bold;color:#B61F29;margin-bottom:6px;"><?= $lang('Activity issues', 'Aktivitäts-Hinweise') ?> (<?= $issuesCount ?>)</div>
                            <ul style="margin:0 0 8px 18px;padding:0;color:#B61F29">
                                <?php foreach ($issuesList as $it): ?>
                                    <li><?= htmlspecialchars($it['name']) ?>: <strong><?= (int)$it['count'] ?></strong></li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="<?= $baseUrl ?>/issues" style="display:inline-block;padding:8px 12px;background:#B61F29;color:#fff;text-decoration:none;border-radius:.5rem;"><?= $lang('View all', 'Alle anzeigen') ?></a>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>

            <?php if ($queueCount > 0): ?>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 14px 0">
                    <tr>
                        <td style="padding:12px;background:#63a30820;border-radius:.5rem">
                            <div style="font-weight:bold;color:#63a308;margin-bottom:6px;"><?= $lang('New activities to review', 'Neue Aktivitäten zur Prüfung') ?> (<?= $queueCount ?>)</div>
                            <a href="<?= $baseUrl ?>/queue/user" style="display:inline-block;padding:8px 12px;background:#63a308;color:#fff;text-decoration:none;border-radius:.5rem;"><?= $lang('Review now', 'Jetzt prüfen') ?></a>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>


            <?php if ($messagesCount > 0): ?>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 14px 0">
                    <tr>
                        <td style="padding:12px;background:#00808320;border-radius:.5rem">
                            <div style="font-weight:bold;color:#008083;margin-bottom:6px;"><?= $lang('Unread messages', 'Ungelesene Nachrichten') ?> (<?= $messagesCount ?>)</div>
                            <a href="<?= $baseUrl ?>/messages" style="display:inline-block;padding:8px 12px;background:#008083;color:#fff;text-decoration:none;border-radius:.5rem;"><?= $lang('Open inbox', 'Posteingang öffnen') ?></a>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>

            <?php if ($hasVersion): ?>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 14px 0">
                    <tr>
                        <td style="padding:12px;background:#87878720;border-radius:.5rem">
                            <div style="font-weight:bold;margin-bottom:6px;color:#878787;"><?= $lang('OSIRIS has been updated', 'OSIRIS wurde aktualisiert') ?></div>
                            <a href="<?= $baseUrl ?>/new-stuff#version-<?= OSIRIS_VERSION ?>" style="display:inline-block;padding:8px 12px;background:#878787;color:#fff;text-decoration:none;border-radius:.5rem;"><?= $lang('See what’s new', 'Neuigkeiten ansehen') ?></a>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>

            <p style="font-size:12px;color:#6b7280;margin-top:18px;">
                <?php
                $settingsLink = $baseUrl . '/user/edit/' . ($user['username'] ?? '') . '#section-contact';
                ?>

                <?= $lang('You can change your digest frequency in <a href="' . ($settingsLink) . '">settings</a>.', 'Du kannst die Häufigkeit des Digests in <a href="' . ($settingsLink) . '">deinen Einstellungen</a> ändern.') ?>
            </p>
        </div>

        <div style="background:#f3f4f6;color:#6b7280;font-size:12px;padding:10px 14px;text-align:center;">
            OSIRIS • <?= htmlspecialchars($baseUrl ?? 'localhost') ?>
        </div>
    </div>
<?php
    $html = ob_get_clean();

    // Plaintext (optional)
    $lines = [];
    $lines[] = ($language === 'de' ? 'Dein OSIRIS Digest' : 'Your OSIRIS Digest');
    if ($issuesCount > 0)
        $lines[] = ($language === 'de' ? "Aktivitäts-Hinweise: $issuesCount" : "Activity issues: $issuesCount") . " → " . ROOTPATH . "/issues";
    if ($queueCount > 0)
        $lines[] = ($language === 'de' ? "Neue Aktivitäten zur Prüfung: $queueCount" : "New activities to review: $queueCount") . " → " . ROOTPATH . "/queue/user";
    if ($hasVersion)
        $lines[] = ($language === 'de' ? "OSIRIS wurde aktualisiert" : "OSIRIS has been updated") . " → " . ROOTPATH . "/new-stuff#version-" . OSIRIS_VERSION;
    if ($messagesCount > 0)
        $lines[] = ($language === 'de' ? "Ungelesene Nachrichten: $messagesCount" : "Unread messages: $messagesCount") . " → " . ROOTPATH . "/messages";
    $text = implode("\n", $lines);

    return [$subject, $html, $text];
}
