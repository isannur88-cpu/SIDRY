<?php

header("Content-Type: application/json");

include_once '../config/database.php';

$database = new database();
$db = $database->getConnection();

$query = "SELECT suhu,
       kelembaban,
       rain,
       status_hujan,
       created_at
          FROM station 
          ORDER BY id DESC 
          LIMIT 60";

$stmt = $db->prepare($query);
$stmt->execute();

$data = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = $row;
}

echo json_encode(array_reverse($data));
