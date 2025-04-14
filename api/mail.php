<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "ekurzusok2024@gmail.com";
    $mail->Password = getenv("EMAIL_PASSWD");
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->CharSet = "UTF-8";

    $mail->setFrom("ekurzusok2024@gmail.com", "eKurzusok");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Elfelejtett jelszó";
    $mail->Body = "Kedves {$firstname},
        <br><p>Küldjük új ideiglenes belépési jelszavát! Ezzel a jelszóval be tud lépni és megváltoztatni régi jelszavát.
        Más műveletet nem tud végezni vele. Amint megváltoztatta régi jelszavát vagy belépett régi jelszavával,
        ez az ideiglenes jelszó törlődni fog. Ha valaki más kért új jelszót az Ön e-mail címére, ne aggódjon! Régi jelszava továbbra is működni fog.
        <br><p>Az ön új ideiglenes jelszava:</p>
        <p><b>{$new_passwd}</b></p>
        <br><br><p>eKurzusok</p>";

    $mail->send();
    $mail_success = true;
} catch (Exception $e) {
    var_dump($e);
    $mail_success = false;
}

?>