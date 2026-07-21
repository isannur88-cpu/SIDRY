<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$liveFile = __DIR__ . '/live_data.json';

// METHOD POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"));

    if (
        isset($data->suhu) &&
        isset($data->kelembaban) &&
        isset($data->rain) &&
        isset($data->status_hujan)
    ) {

        $payload = [
            "suhu" => $data->suhu,
            "kelembaban" => $data->kelembaban,
            "rain" => $data->rain,
            "status_hujan" => $data->status_hujan,
            "updated_at" => date('Y-m-d H:i:s')
        ];

    } else {

        die(json_encode([
            "message" => "Wrong structure!"
        ]));

    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (
        isset($_GET['suhu']) &&
        isset($_GET['kelembaban']) &&
        isset($_GET['rain']) &&
        isset($_GET['status_hujan'])
    ) {

        $payload = [
            "suhu" => $_GET['suhu'],
            "kelembaban" => $_GET['kelembaban'],
            "rain" => $_GET['rain'],
            "status_hujan" => $_GET['status_hujan'],
            "updated_at" => date('Y-m-d H:i:s')
        ];

    } else {

        die(json_encode([
            "message" => "Wrong structure!"
        ]));

    }

} else {

    die(json_encode([
        "message" => "Wrong request method!"
    ]));

}

if (file_put_contents($liveFile, json_encode($payload))) {

    echo json_encode([
        "message" => "Live data updated"
    ]);

} else {

    echo json_encode([
        "message" => "Failed to write live data"
    ]);

}
?>
