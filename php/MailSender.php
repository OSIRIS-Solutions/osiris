<?php
include_once 'init.php';

function sendMail(
    $to,
    $subject,
    $message
) {
    global $osiris;
    // get mail settings:
    $mail = $osiris->adminGeneral->findOne(['key' => 'mail']);
    $mail = DB::doc2Arr($mail['value'] ?? []);

    $msg = 'mail-sent';

    $Mailer = new PHPMailer\PHPMailer\PHPMailer(true);

    if (!empty($mail['smtp_server'])) {
        $Mailer->isSMTP();
        $Mailer->Host = $mail['smtp_server'] ?? 'localhost';
        if (isset($mail['smtp_user']) && isset($mail['smtp_password'])) {
            $Mailer->SMTPAuth = true;
            $Mailer->Username = $mail['smtp_user'];
            $Mailer->Password = $mail['smtp_password'];
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
