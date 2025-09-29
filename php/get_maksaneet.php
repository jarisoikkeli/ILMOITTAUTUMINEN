<?php
declare(strict_types=1);

// Piilota virheet selaimessa, mutta lokitetaan ne silti
ini_set('display_errors', '0');
error_reporting(E_ALL);

require __DIR__ . '/config.php';

$kilpailuId = isset($_GET['kilpailu_id']) ? (int)$_GET['kilpailu_id'] : 0;
if ($kilpailuId <= 0) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'Puuttuva tai virheellinen kilpailu_id']);
  exit;
}

$k = $pdo->prepare("SELECT nimi, ajankohta FROM kilpailut WHERE id = ?");
$k->execute([$kilpailuId]);
$kilpailu = $k->fetch(PDO::FETCH_ASSOC);
if (!$kilpailu) {
  http_response_code(404);
  echo json_encode(['status'=>'error','message'=>'Kilpailua ei löytynyt']);
  exit;
}

$s = $pdo->prepare("
  SELECT i.kilpailunumero, i.nimi, i.seura
  FROM ilmoittautumiset i
  WHERE i.kilpailu_id = ? AND i.maksanut = 1
  ORDER BY i.kilpailunumero ASC
");
$s->execute([$kilpailuId]);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'status' => 'ok',
  'kilpailu' => [
    'id' => $kilpailuId,
    'nimi' => $kilpailu['nimi'] ?? '',
    'ajankohta' => $kilpailu['ajankohta'] ?? ''
  ],
  'count' => count($rows),
  'rows' => $rows
], JSON_UNESCAPED_UNICODE);
