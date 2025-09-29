<?php
require 'config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['id'])) {
    die("Kilpailua ei valittu.");
}

$id = (int)$_GET['id'];

// Hae kilpailun tiedot
$stmt = $pdo->prepare("SELECT * FROM kilpailut WHERE id = ?");
$stmt->execute([$id]);
$kisa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kisa) {
    die("Kilpailua ei löytynyt.");
}

// Päivitä tiedot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE kilpailut 
                           SET nimi=?, ajankohta=?, ilmoittautuminen_alku=?, ilmoittautuminen_loppu=?, maksimi_osallistujat=?, info=? 
                           WHERE id=?");
    $stmt->execute([
        $_POST['nimi'],
        $_POST['ajankohta'],
        $_POST['ilmoittautuminen_alku'],
        $_POST['ilmoittautuminen_loppu'],
        $_POST['maksimi_osallistujat'],
        $_POST['info'],
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
  <title>Muokkaa kilpailua</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    label { display: block; margin-top: 10px; }
    input, textarea { padding: 5px; width: 400px; }
    button { margin-top: 15px; padding: 8px 12px; }
  </style>
</head>
<body>
  <h1>Muokkaa kilpailua</h1>
  <h2><?= htmlspecialchars($kisa['nimi']) ?></h2>

  <form method="post">
    <label>Nimi:
      <input type="text" name="nimi" value="<?= htmlspecialchars($kisa['nimi']) ?>" required>
    </label>
    <label>Päivämäärä:
      <input type="date" name="ajankohta" value="<?= htmlspecialchars($kisa['ajankohta']) ?>" required>
    </label>
    <label>Ilmoittautuminen alkaa:
      <input type="date" name="ilmoittautuminen_alku" value="<?= htmlspecialchars($kisa['ilmoittautuminen_alku']) ?>" required>
    </label>
    <label>Ilmoittautuminen loppuu:
      <input type="date" name="ilmoittautuminen_loppu" value="<?= htmlspecialchars($kisa['ilmoittautuminen_loppu']) ?>" required>
    </label>
    <label>Maksimi osallistujat:
      <input type="number" name="maksimi_osallistujat" value="<?= htmlspecialchars($kisa['maksimi_osallistujat']) ?>" required>
    </label>
    <label>Info:
      <textarea name="info" rows="4"><?= htmlspecialchars($kisa['info']) ?></textarea>
    </label>

    <button type="submit">Tallenna</button>
  </form>

  <p><a href="admin.php">← Takaisin</a></p>
</body>
</html>
