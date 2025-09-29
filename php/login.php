<?php
session_start();

// Jos jo kirjautunut, ohjaa admin.php:lle
if (isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$virhe = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kayttaja = $_POST['kayttaja'] ?? '';
    $salasana = $_POST['salasana'] ?? '';

    // Yksinkertainen tarkistus – tuotannossa hae tiedot tietokannasta ja käytä password_hash
    if ($kayttaja === 'admin' && $salasana === 'salasana123') {
        $_SESSION['admin'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $virhe = "Virheellinen käyttäjätunnus tai salasana.";
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
</head>
<body>
  <h2>Admin Login</h2>
  <?php if ($virhe) echo "<p style='color:red;'>$virhe</p>"; ?>
  <form method="post">
    <input type="text" name="kayttaja" placeholder="Käyttäjätunnus" required><br>
    <input type="password" name="salasana" placeholder="Salasana" required><br>
    <button type="submit">Kirjaudu</button>
  </form>
</body>
</html>
