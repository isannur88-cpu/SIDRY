<?php

include_once '../config/database.php';

$database = new database();
$db = $database->getConnection();

$today = date('Y-m-d');

$stmt = $db->prepare("SELECT suhu, kelembaban, rain, status_hujan, created_at 
    FROM station 
    WHERE DATE(created_at) = :today 
    ORDER BY id ASC");
$stmt->execute([':today' => $today]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "data_jemuran_" . $today . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');

// Header CSV
fputcsv($out, ['Suhu (°C)', 'Kelembaban (%)', 'Rain Value', 'Status Hujan', 'Waktu']);

// Isi data
foreach ($rows as $row) {
    fputcsv($out, [
        $row['suhu'],
        $row['kelembaban'],
        $row['rain'],
        $row['status_hujan'],
        $row['created_at']
    ]);
}

fclose($out);
exit;
?>
