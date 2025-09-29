<?php
require 'config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ---- KILPAILUJEN KÄSITTELY ----
if (isset($_POST['add_competition'])) {
    $stmt = $pdo->prepare("INSERT INTO kilpailut (nimi, ajankohta, ilmoittautuminen_alku, ilmoittautuminen_loppu, maksimi_osallistujat, info) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nimi'],
        $_POST['ajankohta'],
        $_POST['ilmoittautuminen_alku'],
        $_POST['ilmoittautuminen_loppu'],
        $_POST['maksimi_osallistujat'],
        $_POST['info']
    ]);
}

if (isset($_GET['delete_competition'])) {
    $stmt = $pdo->prepare("DELETE FROM kilpailut WHERE id=?");
    $stmt->execute([$_GET['delete_competition']]);
}

if (isset($_GET['delete_ilmo'])) {
    $stmt = $pdo->prepare("DELETE FROM ilmoittautumiset WHERE id=?");
    $stmt->execute([$_GET['delete_ilmo']]);
}

// Kilpailunumeron päivitys
if (isset($_POST['update_numero']) && isset($_POST['id']) && isset($_POST['kilpailunumero'])) {
    $id = (int)$_POST['id'];
    $numero = (int)$_POST['kilpailunumero'];

    if ($numero >= 1 && $numero <= 1000) {
        $stmt = $pdo->prepare("UPDATE ilmoittautumiset SET kilpailunumero=? WHERE id=?");
        $stmt->execute([$numero, $id]);
        $msg = "Kilpailunumero päivitetty!";
    } else {
        $msg = "Virhe: kilpailunumero pitää olla väliltä 1–1000.";
    }
}

// Maksutilan päivitys
if (isset($_POST['update_maksu']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    // Checkbox: jos kenttä puuttuu -> 0, jos mukana -> 1
    $paid = isset($_POST['maksanut']) ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE ilmoittautumiset SET maksanut=? WHERE id=?");
    $stmt->execute([$paid, $id]);
    $msg = "Maksutila päivitetty.";
}

// ---- HAE DATA ----
$kilpailut = $pdo->query("SELECT * FROM kilpailut ORDER BY ajankohta ASC")->fetchAll(PDO::FETCH_ASSOC);
$ilmoittautumiset = $pdo->query("SELECT i.*, k.nimi as kilpailu_nimi 
                                 FROM ilmoittautumiset i 
                                 LEFT JOIN kilpailut k ON i.kilpailu_id = k.id 
                                 ORDER BY k.ajankohta ASC, i.id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Ryhmittele kilpailun mukaan
$ilmo_per_kisa = [];
foreach ($ilmoittautumiset as $i) {
    $ilmo_per_kisa[$i['kilpailu_nimi']][] = $i;
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Admin - kilpailut & ilmoittautumiset</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .paid-badge{font-weight:600;}
    .paid-yes{color:green;}
    .paid-no{color:#b00;}
  </style>
</head>
<body>
  <h1>Admin - kilpailut ja ilmoittautumiset</h1>

  <?php if (isset($msg)): ?>
    <p class="msg"><?= htmlspecialchars($msg) ?></p>
  <?php endif; ?>

  <!-- --- KILPAILUJEN HALLINTA --- -->
  <h2>Kilpailut</h2>

  <table>
    <tr>
      <th>ID</th><th>Nimi</th><th>Päivämäärä</th><th>Ilmo alkoi</th><th>Ilmo loppui</th><th>Maksimi</th><th>Info</th><th>Toiminnot</th>
    </tr>
    <?php foreach ($kilpailut as $k): ?>
      <tr>
        <td><?= $k['id'] ?></td>
        <td><?= htmlspecialchars($k['nimi']) ?></td>
        <td><?= $k['ajankohta'] ?></td>
        <td><?= $k['ilmoittautuminen_alku'] ?></td>
        <td><?= $k['ilmoittautuminen_loppu'] ?></td>
        <td><?= $k['maksimi_osallistujat'] ?></td>
        <td><?= htmlspecialchars($k['info']) ?></td>
        <td>
          <a href="muokkaa_kilpailu.php?id=<?= $k['id'] ?>"><button class="btn">Muokkaa</button></a>
          <a href="admin.php?delete_competition=<?= $k['id'] ?>" onclick="return confirm('Poistetaanko kilpailu?')"><button class="btn">Poista</button></a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- Lisää kilpailu -nappi -->
  <div style="text-align:right;">
    <button class="btn" onclick="toggleForm()">Lisää kilpailu</button>
  </div>

  <!-- Piilotettu lomake -->
  <div id="addForm" style="display:none; margin-top:20px;">
    <form method="post" class="form-box">
      <h3>Lisää uusi kilpailu</h3>
      <label>Nimi:<br><input type="text" name="nimi" required></label>
      <label>Päivämäärä:<br><input type="date" name="ajankohta" required></label>
      <label>Ilmoittautuminen alkaa:<br><input type="date" name="ilmoittautuminen_alku" required></label>
      <label>Ilmoittautuminen loppuu:<br><input type="date" name="ilmoittautuminen_loppu" required></label>
      <label>Maksimi osallistujat:<br><input type="number" name="maksimi_osallistujat" required></label>
      <label>Info:<br><textarea name="info"></textarea></label>
      <button type="submit" name="add_competition">Tallenna kilpailu</button>
    </form>
  </div>

  <script>
    function toggleForm() {
      const f = document.getElementById("addForm");
      f.style.display = (f.style.display === "none" ? "block" : "none");
    }
  </script>

  <!-- --- ILMOITTAUTUMISET KISAN MUKAAN --- -->
  <h2>Ilmoittautumiset</h2>
  <?php foreach ($ilmo_per_kisa as $kisan_nimi => $lista): ?>
    <h3><?= htmlspecialchars($kisan_nimi) ?></h3>
    <table>
      <tr>
        <th>ID</th><th>Nimi</th><th>Syntymäaika</th><th>Seura</th><th>Sähköposti</th><th>Kilpailunumero</th><th>Maksu</th><th>Toiminnot</th>
      </tr>
      <?php foreach ($lista as $i): ?>
        <tr>
          <td><?= $i['id'] ?></td>
          <td><?= htmlspecialchars($i['nimi']) ?></td>
          <td><?= $i['syntymaaika'] ?></td>
          <td><?= htmlspecialchars($i['seura']) ?></td>
          <td><?= htmlspecialchars($i['sahkoposti']) ?></td>
          <td>
            <form method="post" style="display:inline;">
              <input type="hidden" name="id" value="<?= $i['id'] ?>">
              <input type="number" name="kilpailunumero" value="<?= htmlspecialchars($i['kilpailunumero']) ?>" min="1" max="1000">
              <button type="submit" name="update_numero">Tallenna</button>
            </form>
          </td>
          <td>
            <form method="post" style="display:inline;">
              <input type="hidden" name="id" value="<?= $i['id'] ?>">
              <!-- Checkbox toimii: rasti = 1, ei rastia = 0 -->
              <label class="paid-badge <?= ($i['maksanut'] ? 'paid-yes' : 'paid-no') ?>">
                <input type="checkbox" name="maksanut" <?= ($i['maksanut'] ? 'checked' : '') ?>>
                <?= $i['maksanut'] ? 'Maksettu' : 'Ei maksettu' ?>
              </label>
              <button type="submit" name="update_maksu">Tallenna</button>
            </form>
          </td>
          <td>
            <a href="muokkaa_ilmo.php?id=<?= $i['id'] ?>"><button class="btn">Muokkaa</button></a>
            <a href="admin.php?delete_ilmo=<?= $i['id'] ?>" onclick="return confirm('Poistetaanko ilmoittautuminen?')"><button class="btn">Poista</button></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endforeach; ?>
</body>
</html>
