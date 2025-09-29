<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// --- CORS (salli vain kehitys ja/tai tuotanto) ---
$allowedOrigins = [
  'http://localhost:5173',
  'http://localhost',       // jos haet samasta hostista ilman porttia
  // 'https://www.joensuu12h.com',  // lisää tuotanto tähän kun julkaiset
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: http://localhost'); // fallback
}

require 'config.php'; // tuo $pdo

// Lisää: varmista että PDO heittää poikkeukset
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $token = $_GET['token'] ?? '';
    if ($token === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Token puuttuu']);
        exit;
    }

    // Voit halutessasi palauttaa myös kilpailun nimen:
    $sql = "SELECT i.id, i.kilpailu_id, i.nimi, i.syntymaaika, i.seura, i.sahkoposti,
                   k.nimi AS kilpailu_nimi
            FROM ilmoittautumiset i
            LEFT JOIN kilpailut k ON k.id = i.kilpailu_id
            WHERE i.muokkaus_token = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Ilmoittautumista ei löytynyt']);
        exit;
    }

    echo json_encode(['status' => 'ok', 'ilmoittautuja' => $row], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Palvelinvirhe']);
}
