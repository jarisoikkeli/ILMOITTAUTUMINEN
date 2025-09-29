<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/send_mail.php';

// Salli CORS
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- LUE INPUT ---
$data = json_decode(file_get_contents("php://input"), true);
if (
    !$data ||
    !isset($data['nimi'], $data['syntymaaika'], $data['sahkoposti'], $data['kilpailu_id'])
) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["status" => "error", "message" => "Virheellinen syöte."]);
    exit;
}

// --- DB-YHTEYS ---
$pdo = new PDO("mysql:host=localhost;dbname=ilmoittautuminen;charset=utf8mb4", "root", "");

// --- TOKEN & KILPAILUNUMERO ---
$token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare("SELECT MAX(kilpailunumero) FROM ilmoittautumiset WHERE kilpailu_id = ?");
$stmt->execute([$data['kilpailu_id']]);
$nextNumber = (int)$stmt->fetchColumn() + 1;

// --- TALLENNUS ---
$stmt = $pdo->prepare("
    INSERT INTO ilmoittautumiset 
    (kilpailu_id, nimi, syntymaaika, seura, sahkoposti, muokkaus_token, muokkaus_token_luotu, kilpailunumero, maksanut)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, 0)
");
$stmt->execute([
    $data['kilpailu_id'],
    $data['nimi'],
    $data['syntymaaika'],
    $data['seura'],
    $data['sahkoposti'],
    $token,
    $nextNumber
]);

// --- KILPAILUN INFO ---
$stmt = $pdo->prepare("SELECT nimi, info FROM kilpailut WHERE id = ?");
$stmt->execute([$data['kilpailu_id']]);
$kilpailu = $stmt->fetch(PDO::FETCH_ASSOC);

// --- APUTOIMINTO: BASE URL ---
function base_url(): string {
    $https = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
    );
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    if ($path !== '') { $path .= '/'; }
    return "{$scheme}://{$host}{$path}";
}

// --- MUOKKAUSLINKKI ---
$base  = base_url();
$linkki = $base . "#/muokkaa?token=" . urlencode($token);

// --- POSTI: PELKKÄ TEKSTI, SIJOITA LINKKI OMALLE RIVILLE ---
$nimi = $data['nimi'];
$maksuohjeet = (string)($kilpailu['info'] ?? '');
$kisanNimi = $kilpailu['nimi'] ?? 'Tapahtuma';

$subject = "Ilmoittautumisesi ({$kisanNimi})";

$viesti_plain = <<<TXT
Kiitos ilmoittautumisesta, {$nimi}!

Muokkauslinkki:
{$linkki}

Maksuohjeet:
{$maksuohjeet}

Jos et tehnyt ilmoittautumista, vastaa tähän viestiin.
TXT;

$result = sendEmail($data['sahkoposti'], $subject, $viesti_plain, null, null, true); // <- debug=true
$emailSent  = $result['success'];
$emailError = $result['error'] ?? null;

// --- LOKI ---
file_put_contents(
    __DIR__ . "/ilmoitukset.log",
    "REGISTER -> {$data['sahkoposti']}\nLahetys: " . ($emailSent ? 'OK' : 'VIRHE') .
    "\nVirhe: " . ($emailError ?? '—') . "\n{$viesti_plain}\n\n",
    FILE_APPEND
);

// --- VASTAUS FRONTILLE ---
// Rekisteröinti onnistui jo DB:hen; ei rikota sitä vaikka maili epäonnistuu.
// Palautetaan kuitenkin mailin tila näkyviin (emailSent + email_error).
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
  "status"          => "ok",
  "message"         => "Kiitos ilmoittautumisesta,<br>" . htmlspecialchars($nimi) . "!",
  "kilpailu_info"   => $maksuohjeet,
  "kilpailu_id"     => (int)$data['kilpailu_id'], // <-- lisätty
  "kilpailunumero"  => (int)$nextNumber,          // <-- hyödyllinen lisä (vapaaehtoinen käyttää)
  "emailSent"       => $emailSent,
  "email_error"     => $emailError
], JSON_UNESCAPED_UNICODE);

