<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require "../../vendor/autoload.php";
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "ekurzusok2024@gmail.com";
    $mail->Password = getenv("EMAIL_PASSWD");
    $mail->Port = 25;

    $mail->setFrom("ekurzusok2024@gmail.com", "eKurzusok'");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Jelszó visszállítás";
    $mail->Body = "Kedves {$firstname},
        <br><p>Jelszavát sikeresen visszaállítottuk a következőre:<b>{$new_passwd}</b></p>
        <br><p>Kérjük, bejelentkezés után változtassa meg jelszavát! Köszönjük!</p>
        <br><p>eKurzusok</p>";

    $mail->send();
    $mail_success = true;
} catch (Exception $e) {
    $mail_success = false;
}

?>