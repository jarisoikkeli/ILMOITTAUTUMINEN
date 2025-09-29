<?php
// Tietokanta-asetukset (vain tuotanto)
$DB_HOST = "localhost";
$DB_NAME = "verkkoti_ilmoittautuminen";
$DB_USER = "joensuu12h_user";
$DB_PASS = "PitkäSalasana";

try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS
  );
} catch (PDOException $e) {
  die("Tietokantavirhe: " . $e->getMessage());
}
