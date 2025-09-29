<?php
// list_maksaneet.php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/config.php'; // $pdo (PDO)

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// ---- pakollinen parametri ----
$kilpailuId = isset($_GET['kilpailu_id']) ? (int)$_GET['kilpailu_id'] : 0;
if ($kilpailuId <= 0) {
  http_response_code(400);
  echo "Puuttuva tai virheellinen parametri: kilpailu_id";
  exit;
}

// ---- hae kilpailun nimi ----
$kstmt = $pdo->prepare("SELECT id, nimi, ajankohta FROM kilpailut WHERE id = ?");
$kstmt->execute([$kilpailuId]);
$kilpailu = $kstmt->fetch(PDO::FETCH_ASSOC);

if (!$kilpailu) {
  http_response_code(404);
  echo "Kilpailua ei löytynyt (id: " . (int)$kilpailuId . ").";
  exit;
}

// ---- hae data (vain maksetut) ----
$stmt = $pdo->prepare("
  SELECT i.kilpailunumero, i.nimi, i.seura
  FROM ilmoittautumiset i
  WHERE i.maksanut = 1 AND i.kilpailu_id = ?
  ORDER BY i.kilpailunumero ASC
");
$stmt->execute([$kilpailuId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fi">
<head>
  <meta charset="utf-8">
  <title><?= h($kilpailu['nimi']) ?> – Maksaneet ilmoittautuneet</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --blue:#0d6efd; --bg:#f7f7fb; --border:#e8e8ef; --text:#222; }
    * { box-sizing: border-box; }
    body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:var(--bg); color:var(--text); }
    .wrap { max-width: 900px; margin: 28px auto; padding: 0 16px; }
    h1 { font-size: 28px; margin: 0 0 6px; }
    .sub { color:#555; margin:0 0 16px; }
    .table {
      width:100%; border-collapse: collapse; background:#fff; border:1px solid var(--border); border-radius:10px; overflow:hidden;
      box-shadow:0 4px 14px rgba(0,0,0,.04);
    }
    .table th, .table td { padding:10px 12px; border-bottom:1px solid var(--border); font-size:14px; }
    .table th { background:#f2f6ff; text-align:left; font-weight:600; }
    .table tr:last-child td { border-bottom:none; }
    .pill { background:#eef4ff; color:#1246d6; padding:4px 8px; border-radius: 999px; font-size:12px; }
  </style>
</head>
<body>
<div class="wrap">
  <h1><?= h($kilpailu['nimi']) ?></h1>
  <?php if (!empty($kilpailu['ajankohta'])): ?>
    <p class="sub"><?= h($kilpailu['ajankohta']) ?></p>
  <?php endif; ?>
  <h3>Ilmoittautuneet</h3>
  <table class="table" role="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Nimi</th>
        <th>Seura</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="3">Ei maksaneita löytynyt.</td></tr>
    <?php else: foreach ($rows as $r): ?>
      <tr>
        <td><span class="pill"><?= h((string)$r['kilpailunumero']) ?></span></td>
        <td><?= h($r['nimi']) ?></td>
        <td><?= h($r['seura'] ?? '') ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
