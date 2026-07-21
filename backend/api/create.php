<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../class/nodemcu_log.php';
include_once '../config/database.php';

$database = new database();
$db = $database->getConnection();

$item = new nodemcu_log($db);

// METHOD POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"));

    if (
        isset($data->suhu) &&
        isset($data->kelembaban) &&
        isset($data->rain) &&
        isset($data->status_hujan)
    ) {

        $item->suhu = $data->suhu;
        $item->kelembaban = $data->kelembaban;
        $item->rain = $data->rain;
        $item->status_hujan = $data->status_hujan;

    } else {

        die(json_encode([
            "message" => "Wrong structure!"
        ]));

    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $item->suhu = isset($_GET['suhu'])
        ? $_GET['suhu']
        : die(json_encode(["message" => "Wrong structure!"]));

    $item->kelembaban = isset($_GET['kelembaban'])
        ? $_GET['kelembaban']
        : die(json_encode(["message" => "Wrong structure!"]));

} else {

    die(json_encode([
        "message" => "Wrong request method!"
    ]));

}

if ($item->createLogData()) {

    echo json_encode([
        "message" => "Data created successfully"
    ]);

} else {

    echo json_encode([
        "message" => "Data could not be created"
    ]);

}
?>
