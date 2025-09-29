<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// --- keskitetty maileri ---
require_once __DIR__ . '/send_mail.php';

// --- CORS (säädä domainit tarpeen mukaan) ---
$allowedOrigins = ['http://localhost:5173', 'http://localhost'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: http://localhost');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require 'config.php'; // tuo $pdo (PDO)
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Luo Reactin muokkauslinkki (hash-reitti)
function react_edit_link(string $token): string {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (str_contains($host, 'localhost')) {
        $base = 'http://localhost/ilmo';
    } else {
        // päivitä oma tuotantopolkusi tähän
        $base = 'https://www.joensuu12h.com/ilmo';
    }
    return $base . '/#/muokkaa?token=' . urlencode($token);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Väärä HTTP-metodi']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Virheellinen JSON']);
        exit;
    }

    $token       = trim((string)($data['token'] ?? ''));
    $nimi        = trim((string)($data['nimi'] ?? ''));
    $syntymaaika = trim((string)($data['syntymaaika'] ?? ''));
    $seura       = trim((string)($data['seura'] ?? ''));
    $sahkoposti  = trim((string)($data['sahkoposti'] ?? ''));

    if ($token === '' || $nimi === '' || $syntymaaika === '' || $sahkoposti === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Pakollisia tietoja puuttuu']);
        exit;
    }
    if (!filter_var($sahkoposti, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Virheellinen sähköposti']);
        exit;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $syntymaaika)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Virheellinen syntymäaika']);
        exit;
    }

    // Hae nykyinen rivi vanhalla tokenilla (hae myös kilpailu_id)
    $stmt = $pdo->prepare("SELECT id, sahkoposti, kilpailu_id FROM ilmoittautumiset WHERE muokkaus_token = ?");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Ilmoittautumista ei löytynyt']);
        exit;
    }

    // Haetaan kilpailun nimi ja info (maksuohjeet)
    $kilpailu = null;
    if (!empty($row['kilpailu_id'])) {
        $k = $pdo->prepare("SELECT nimi, info FROM kilpailut WHERE id = ?");
        $k->execute([$row['kilpailu_id']]);
        $kilpailu = $k->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    $oldEmail = (string)$row['sahkoposti'];
    $emailChanged = (strcasecmp($oldEmail, $sahkoposti) !== 0);

    $pdo->beginTransaction();

    if ($emailChanged) {
        // Sähköposti muuttui → generoi uusi token ja mitätöi vanha
        $newToken = bin2hex(random_bytes(32));

        $sql = "UPDATE ilmoittautumiset
                SET nimi = ?, syntymaaika = ?, seura = ?, sahkoposti = ?,
                    muokkaus_token = ?, muokkaus_token_luotu = NOW()
                WHERE muokkaus_token = ?";
        $ok = $pdo->prepare($sql)->execute([
            $nimi, $syntymaaika, $seura, $sahkoposti,
            $newToken, $token
        ]);

        if (!$ok) {
            throw new RuntimeException('Päivitys epäonnistui (sähköposti vaihtui).');
        }

        $pdo->commit();

        // Uusi linkki ja viesti (pelkkä teksti, siisti rivitys send_mail.php:ssä)
        $linkki = react_edit_link($newToken);
        $kisanNimi = $kilpailu['nimi'] ?? '';
        $subject = 'Uusi muokkauslinkki' . ($kisanNimi ? " ({$kisanNimi})" : '');

        $plain = "Hei {$nimi},\n\n"
               . "Ilmoittautumisen tiedot päivitettiin. Uusi muokkauslinkki:\n"
               . "{$linkki}\n\n"
               . "Vanha muokkauslinkki on mitätöity.\n";
        if ($kilpailu && !empty($kilpailu['info'])) {
            $plain .= "\nMaksuohjeet:\n" . $kilpailu['info'] . "\n";
        }

        // Lähetä keskitetysti
$res = sendEmail($sahkoposti, $subject, $plain, null, null, true); // <- debug=true
$emailSent  = $res['success'];
$emailError  = $res['error'] ?? null;

        // Loki varalle
        file_put_contents(
            __DIR__ . "/ilmoitukset.log",
            "UUSI MUOKKAUSLINKKI -> {$sahkoposti}\nLinkki: {$linkki}\nLahetys: " . ($emailSent ? 'OK' : 'VIRHE')
            . "\nVirhe: " . ($emailError ?? '—') . "\n\n",
            FILE_APPEND
        );

        echo json_encode([
            'status'      => $emailSent ? 'ok' : 'error',
            'code'        => 'email_changed',
            'message'     => $emailSent
                              ? 'Sähköposti päivitetty. Uusi muokkauslinkki lähetettiin.'
                              : 'Sähköposti päivitetty, mutta lähetys epäonnistui.',
            'emailSent'   => $emailSent,
            'email_error' => $emailError
        ]);
        exit;

    } else {
        // Sähköposti ennallaan → pidä token, päivitä kentät
        $sql = "UPDATE ilmoittautumiset
                SET nimi = ?, syntymaaika = ?, seura = ?, sahkoposti = ?
                WHERE muokkaus_token = ?";
        $ok = $pdo->prepare($sql)->execute([
            $nimi, $syntymaaika, $seura, $sahkoposti, $token
        ]);

        if (!$ok) {
            throw new RuntimeException('Päivitys epäonnistui.');
        }

        $pdo->commit();
        echo json_encode(['status' => 'ok', 'code' => 'updated', 'message' => 'Tiedot päivitetty.']);
        exit;
    }

} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    // Kirjaa virhe itsellesi debuggaukseen
    file_put_contents(
        __DIR__ . '/php_error.log',
        '[' . date('c') . "] update_ilmoitus.php: " . $e->getMessage() . "\n",
        FILE_APPEND
    );

    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Palvelinvirhe']);
}
