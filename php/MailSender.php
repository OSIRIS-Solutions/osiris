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
    $mail = DB::doc2Arr($mail);

    $msg = 'mail-sent';

    $Mailer = new PHPMailer\PHPMailer\PHPMailer(true);

    $Mailer->isSMTP();
    $Mailer->Host = $mail['smtp_server'] ?? 'localhost';
    if (isset($mail['user']) && isset($mail['smtp_password'])) {
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

    $Mailer->setFrom($mail['email'] ?? 'no-reply@osiris-app.de', 'OSIRIS');
    $Mailer->addAddress($to);
    $Mailer->isHTML(true);

    $Mailer->Subject = $subject;
    $Mailer->Body = $message;

    try {
        $Mailer->send();
    } catch (PHPMailer\PHPMailer\Exception $e) {
        $msg = "mail-error: " . $Mailer->ErrorInfo;
    }
}
