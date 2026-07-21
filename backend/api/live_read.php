<?php

header("Content-Type: application/json");

$liveFile = __DIR__ . '/live_data.json';

if (file_exists($liveFile)) {

    echo file_get_contents($liveFile);

} else {

    echo json_encode([
        "message" => "No live data yet"
    ]);

}
?>
