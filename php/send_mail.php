<?php
// send_mail.php — keskitetty postitus PHPMailerilla + .eml-tallennus
declare(strict_types=1);

// Lataa PHPMailer turvallisesti
$pmBase = __DIR__ . '/PHPMailer/src';
foreach (['Exception.php','PHPMailer.php','SMTP.php'] as $f) {
    $p = $pmBase . '/' . $f;
    if (!is_file($p)) {
        error_log("PHPMailer-tiedosto puuttuu: $p");
        // Palautetaan "stub" lähetystä varten
        function sendEmail(...$args) { return ['success'=>false,'error'=>'PHPMailer missing']; }
        return;
    }
    require_once $p;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// --- ASETUKSET (pidä yhdessä paikassa) ---
const SMTP_HOST  = 'mail.verkkotie.net';
const SMTP_USER  = 'noreply@verkkotie.net';
const SMTP_PASS  = 'Ultra1970!';          // siirrä .env:iin tuotannossa

const FROM_EMAIL = 'noreply@verkkotie.net';
const FROM_NAME  = 'Ilmoittautumiset';
const DEFAULT_REPLY_TO = 'jaris1970@gmail.com';

// Kehitysaput: löysää SSL-tarkistusta (vain deviin!)
const INSECURE_DEV = false;

// --- .EML tallennus (devissä erittäin kätevä) ---
const SAVE_EML = true;                                  // laita false tuotannossa
const EML_DIR  = __DIR__ . '/outbox';                   // .eml-tiedostojen hakemisto

// --- RIVITYS: älä katko URL:eja, käytä CRLF ---
function wrap_plain(string $text, int $width = 72): string {
    $text = preg_replace("/\r\n|\r|\n/", "\n", trim($text));
    $out = '';
    foreach (explode("\n", $text) as $line) {
        if (preg_match('~https?://\S+~', $line)) {
            $out .= $line . "\r\n";
        } else {
            $out .= wordwrap($line, $width, "\r\n", false) . "\r\n";
        }
    }
    return $out;
}

/**
 * Lähetä sähköposti.
 * $textBody = pakollinen tekstiversio
 * $htmlBody = jos annettu, lähetetään multipart (HTML + plain)
 * $replyTo  = jos null, käytetään DEFAULT_REPLY_TOa
 * $debug    = true => SMTP-loki error_logiin
 *
 * Palauttaa ['success'=>bool,'error'=>?string,'id'=>?string,'eml'=>?string]
 */
function sendEmail(string $to, string $subject, string $textBody, ?string $htmlBody = null, ?string $replyTo = null, bool $debug = false): array {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return ['success'=>false,'error'=>'Invalid recipient address'];
    }

    // SMTP-yritykset: ensin SMTPS 465, sitten STARTTLS 587
    $attempts = [
        ['port'=>465, 'secure'=>PHPMailer::ENCRYPTION_SMTPS,    'autotls'=>false, 'host'=>SMTP_HOST, 'auth'=>true],
        ['port'=>587, 'secure'=>PHPMailer::ENCRYPTION_STARTTLS, 'autotls'=>true,  'host'=>SMTP_HOST, 'auth'=>true],
    ];

    $lastErr = null;
    $testId  = bin2hex(random_bytes(6));
    $emlPath = null;
    $emlSaved = false;

    foreach ($attempts as $a) {
        $m = new PHPMailer(true);
        try {
            $m->isSMTP();
            $m->Host        = $a['host'];
            $m->SMTPAuth    = $a['auth'];
            if ($a['auth']) { $m->Username = SMTP_USER; $m->Password = SMTP_PASS; }
            $m->SMTPSecure  = $a['secure'];
            $m->Port        = $a['port'];
            $m->SMTPAutoTLS = $a['autotls'];
            $m->CharSet     = 'UTF-8';

            if (INSECURE_DEV) {
                $m->SMTPOptions = ['ssl'=>[
                    'verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true
                ]];
            }

            if ($debug) {
                $m->SMTPDebug   = SMTP::DEBUG_SERVER;
                $m->Debugoutput = static function($str,$lvl){ error_log("SMTP[$lvl] $str"); };
            }

            $m->setFrom(FROM_EMAIL, FROM_NAME);
            $m->addAddress($to);
            $m->addReplyTo($replyTo ?? DEFAULT_REPLY_TO);
            $m->addCustomHeader('X-Test-ID', $testId);

            if ($htmlBody !== null) {
                // multipart: HTML + plain
                $m->isHTML(true);
                $m->Subject = $subject;
                $m->Body    = $htmlBody;
                $m->AltBody = wrap_plain($textBody);
            } else {
                // pelkkä teksti
                $m->isHTML(false);
                $m->ContentType = 'text/plain; charset=UTF-8; format=flowed; delsp=yes';
                $m->Encoding    = 'quoted-printable';
                $m->Subject     = $subject;
                $m->Body        = wrap_plain($textBody);
            }

            // --- Tallenna .EML ensimmäisestä yrityksestä ---
            if (SAVE_EML && !$emlSaved) {
                if (!is_dir(EML_DIR)) { @mkdir(EML_DIR, 0777, true); }
                $m->preSend(); // rakenna MIME
                $mime = $m->getSentMIMEMessage();
                $emlPath = EML_DIR . '/' . date('Ymd_His') . "_{$testId}.eml";
                file_put_contents($emlPath, $mime);
                $emlSaved = true;
                error_log("MAIL EML saved: {$emlPath}");
            }

            error_log("MAIL TRY id={$testId} to={$to} via {$a['port']}");
            $m->send();
            error_log("MAIL OK  id={$testId} to={$to}");

            return ['success'=>true,'error'=>null,'id'=>$testId,'eml'=>$emlPath];

        } catch (Exception $e) {
            $lastErr = $m->ErrorInfo ?: $e->getMessage();
            error_log("MAIL FAIL id={$testId} to={$to} via {$a['port']} – {$lastErr}");
            // kokeillaan seuraavaa asetusta
        }
    }

    return ['success'=>false,'error'=>$lastErr,'id'=>$testId,'eml'=>$emlPath];
}
