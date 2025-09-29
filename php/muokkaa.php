<?php
// muokkaa.php — ilmoittautujan muokkaus linkistä (token)

ini_set('display_errors', 1);
error_reporting(E_ALL);

// HUOM: käytä samaa configia kuin admin & register
require __DIR__ . '/config.php'; // luo $pdo

// Pieni apufunktio
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$token = $_GET['token'] ?? '';

if (!$token || strlen($token) < 10) {
    http_response_code(400);
    echo "Virheellinen linkki.";
    exit;
}

// Hae ilmoittautuminen tokenilla
$stmt = $pdo->prepare("SELECT i.*, k.nimi AS kilpailu_nimi, k.ajankohta 
                       FROM ilmoittautumiset i
                       LEFT JOIN kilpailut k ON i.kilpailu_id = k.id
                       WHERE i.muokkaus_token = ?");
$stmt->execute([$token]);
$ilmo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ilmo) {
    http_response_code(404);
    echo "Linkki on virheellinen tai vanhentunut.";
    exit;
}

$ok = null;
$err = null;

// Päivitys
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nimi        = trim($_POST['nimi'] ?? '');
    $syntymaaika = trim($_POST['syntymaaika'] ?? '');
    $seura       = trim($_POST['seura'] ?? '');
    $sahkoposti  = trim($_POST['sahkoposti'] ?? '');

    if ($nimi === '' || $syntymaaika === '' || $sahkoposti === '') {
        $err = "Täytä vähintään nimi, syntymäaika ja sähköposti.";
    } else {
        try {
            $upd = $pdo->prepare("UPDATE ilmoittautumiset 
                                  SET nimi=?, syntymaaika=?, seura=?, sahkoposti=? 
                                  WHERE muokkaus_token=?");
            $upd->execute([$nimi, $syntymaaika, $seura, $sahkoposti, $token]);

            // Päivitä näkyviin tuore data
            $stmt->execute([$token]);
            $ilmo = $stmt->fetch(PDO::FETCH_ASSOC);

            $ok = "Tiedot päivitetty.";
        } catch (Exception $e) {
            $err = "Päivitys epäonnistui. Yritä uudelleen.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Ilmoittautumisen muokkaus</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; background:#f5f5f5; margin:0; }
    .wrap { max-width: 720px; margin: 40px auto; background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.1); overflow:hidden; }
    .header { background:#007bff; color:#fff; padding:18px 22px; }
    .header h1 { margin:0; font-size:20px; }
    .content { padding:22px; }
    .meta { font-size:14px; color:#555; margin-bottom:14px; }
    label { display:block; font-weight:600; margin-top:10px; margin-bottom:4px; }
    input[type="text"], input[type="date"], input[type="email"] {
      width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:14px;
    }
    .row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .btnbar { margin-top:16px; display:flex; gap:10px; }
    .btn { padding:10px 14px; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
    .btn-primary { background: linear-gradient(90deg,#007bff,#0056b3); color:#fff; }
    .btn-secondary { background:#eee; color:#333; }
    .note { margin-top:8px; font-size:13px; color:#666; }
    .alert-ok { background:#e6fff0; border:1px solid #9be0bb; color:#145c2e; padding:10px; border-radius:6px; margin-bottom:12px; }
    .alert-err { background:#ffecec; border:1px solid #f5b5b5; color:#7a1f1f; padding:10px; border-radius:6px; margin-bottom:12px; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1>Muokkaa ilmoittautumistasi</h1>
    </div>
    <div class="content">
      <div class="meta">
        <strong>Kilpailu:</strong> <?= h($ilmo['kilpailu_nimi']) ?> (<?= h($ilmo['ajankohta']) ?>)
      </div>

      <?php if ($ok): ?><div class="alert-ok"><?= h($ok) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert-err"><?= h($err) ?></div><?php endif; ?>

      <form method="post">
        <label>Nimi</label>
        <input type="text" name="nimi" value="<?= h($ilmo['nimi']) ?>" required>

        <div class="row">
          <div>
            <label>Syntymäaika</label>
            <input type="date" name="syntymaaika" value="<?= h($ilmo['syntymaaika']) ?>" required>
          </div>
          <div>
            <label>Seura</label>
            <input type="text" name="seura" value="<?= h($ilmo['seura']) ?>">
          </div>
        </div>

        <label>Sähköposti</label>
        <input type="email" name="sahkoposti" value="<?= h($ilmo['sahkoposti']) ?>" required>

        <div class="btnbar">
          <button class="btn btn-primary" type="submit">Tallenna muutokset</button>
          <a class="btn btn-secondary" href="javascript:history.back()">Peruuta</a>
        </div>
        <div class="note">Huom: kilpailua, kilpailunumeroa tai maksutilaa ei voi muokata tällä sivulla.</div>
      </form>
    </div>
  </div>
</body>
</html>
