<?php
require 'config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['id'])) {
    die("Ilmoittautumista ei valittu.");
}

$id = (int)$_GET['id'];

// Hae ilmoittautumisen tiedot
$stmt = $pdo->prepare("SELECT i.*, k.nimi as kilpailu_nimi 
                       FROM ilmoittautumiset i 
                       LEFT JOIN kilpailut k ON i.kilpailu_id = k.id 
                       WHERE i.id = ?");
$stmt->execute([$id]);
$ilmo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ilmo) {
    die("Ilmoittautumista ei löytynyt.");
}

// Päivitä tiedot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kilpailunumero = $_POST['kilpailunumero'] !== '' ? (int)$_POST['kilpailunumero'] : null;

    $stmt = $pdo->prepare("UPDATE ilmoittautumiset 
                           SET nimi=?, syntymaaika=?, seura=?, sahkoposti=?, kilpailunumero=? 
                           WHERE id=?");
    $stmt->execute([
        $_POST['nimi'],
        $_POST['syntymaaika'],
        $_POST['seura'],
        $_POST['sahkoposti'],
        $kilpailunumero,
        $id
    ]);

    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Muokkaa ilmoittautumista</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    label { display: block; margin-top: 10px; }
    input { padding: 5px; width: 300px; }
    button { margin-top: 15px; padding: 8px 12px; }
  </style>
</head>
<body>
  <h1>Muokkaa ilmoittautumista</h1>
  <h2><?= htmlspecialchars($ilmo['nimi']) ?> (<?= htmlspecialchars($ilmo['kilpailu_nimi']) ?>)</h2>

  <form method="post">
    <label>Nimi:
      <input type="text" name="nimi" value="<?= htmlspecialchars($ilmo['nimi']) ?>" required>
    </label>
    <label>Syntymäaika:
      <input type="date" name="syntymaaika" value="<?= htmlspecialchars($ilmo['syntymaaika']) ?>" required>
    </label>
    <label>Seura:
      <input type="text" name="seura" value="<?= htmlspecialchars($ilmo['seura']) ?>">
    </label>
    <label>Sähköposti:
      <input type="email" name="sahkoposti" value="<?= htmlspecialchars($ilmo['sahkoposti']) ?>" required>
    </label>
    <label>Kilpailunumero:
      <input type="number" name="kilpailunumero" value="<?= htmlspecialchars($ilmo['kilpailunumero'] ?? '') ?>" min="1" max="1000">
    </label>

    <button type="submit">Tallenna</button>
  </form>

  <p><a href="admin.php">← Takaisin</a></p>
</body>
</html>
