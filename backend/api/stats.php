<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include_once '../config/database.php';

$database = new database();
$db = $database->getConnection();

$today = date('Y-m-d');

// DATA MASUK HARI INI
$q1 = $db->prepare("SELECT COUNT(*) as total FROM station WHERE DATE(created_at) = :today");
$q1->execute([':today' => $today]);
$datamasuk = $q1->fetch(PDO::FETCH_ASSOC)['total'];

// SUHU MAKS, MIN, RATA-RATA HARI INI
$q2 = $db->prepare("SELECT 
    MAX(suhu) as suhu_max,
    MIN(suhu) as suhu_min,
    AVG(suhu) as suhu_avg,
    AVG(kelembaban) as kelembaban_avg
    FROM station WHERE DATE(created_at) = :today");
$q2->execute([':today' => $today]);
$statsRow = $q2->fetch(PDO::FETCH_ASSOC);

// STATUS HUJAN TERAKHIR
$q3 = $db->prepare("SELECT status_hujan, created_at FROM station 
    WHERE DATE(created_at) = :today 
    ORDER BY id DESC LIMIT 1");
$q3->execute([':today' => $today]);
$lastStatus = $q3->fetch(PDO::FETCH_ASSOC);

// RIWAYAT 10 DATA TERBARU HARI INI
$q4 = $db->prepare("SELECT suhu, kelembaban, rain, status_hujan, created_at 
    FROM station 
    WHERE DATE(created_at) = :today 
    ORDER BY id DESC LIMIT 10");
$q4->execute([':today' => $today]);
$riwayat = $q4->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "data_masuk"      => (int)$datamasuk,
    "suhu_max"        => $statsRow['suhu_max'] ? round($statsRow['suhu_max'], 1) : null,
    "suhu_min"        => $statsRow['suhu_min'] ? round($statsRow['suhu_min'], 1) : null,
    "suhu_avg"        => $statsRow['suhu_avg'] ? round($statsRow['suhu_avg'], 1) : null,
    "kelembaban_avg"  => $statsRow['kelembaban_avg'] ? round($statsRow['kelembaban_avg'], 1) : null,
    "last_status"     => $lastStatus['status_hujan'] ?? '--',
    "last_status_time"=> $lastStatus['created_at'] ?? '--',
    "riwayat"         => $riwayat
]);
?>
