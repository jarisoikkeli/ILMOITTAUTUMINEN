<?php
// tiedosto: htdocs/ilmo/mailtest.php

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // === SMTP-asetukset (verkkotie.net) ===
    $mail->isSMTP();
    $mail->Host       = 'mail.verkkotie.net'; // vaihda oikeaan
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@verkkotie.net'; // vaihda
    $mail->Password   = 'Ultra1970!';             // vaihda
    $mail->SMTPSecure = 'ssl'; // vaihtoehto: 'tls'
    $mail->Port       = 465;   // tls: 587

    // (Valinnainen) Jos sertifikaatti aiheuttaa varoituksen testissä:
    // ÄLÄ jätä tuotantoon, käytä vain paikallisessa testissä tarvittaessa!
    // $mail->SMTPOptions = [
    //   'ssl' => [
    //     'verify_peer' => false,
    //     'verify_peer_name' => false,
    //     'allow_self_signed' => true
    //   ]
    // ];

    // Lähettäjä & vastaanottaja
    $mail->setFrom('noreply@verkkotie.net', 'Ilmoittautumiset');
    $mail->addAddress('jaris1970@gmail.com', 'Testi Vastaanottaja');

    // Sisältö
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'SMTP-testi XAMPP';
    $mail->Body    = '<p><strong>Hei!</strong> Tämä on testi XAMPP:ista.</p>';
    $mail->AltBody = 'Hei! Tämä on testi XAMPP:ista.';

    $mail->send();
    echo 'Sähköposti lähetetty ok.';
} catch (Exception $e) {
    echo 'Virhe: ' . $mail->ErrorInfo;
}
