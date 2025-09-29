<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require 'config.php';

$today = date('Y-m-d');

// Hae kilpailut, joiden ilmoittautuminen on auki
$stmt = $pdo->prepare("
    SELECT id, nimi, ajankohta
    FROM kilpailut 
    WHERE ilmoittautuminen_alku <= ? 
      AND ilmoittautuminen_loppu >= ?
");
$stmt->execute([$today, $today]);

$kilpailut = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($kilpailut);
