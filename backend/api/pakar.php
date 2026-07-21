<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$liveFile = __DIR__ . '/live_data.json';

if (!file_exists($liveFile)) {
    echo json_encode(["error" => "No live data"]);
    exit;
}

$live = json_decode(file_get_contents($liveFile), true);
$suhu      = (float)$live['suhu'];
$kelembaban = (float)$live['kelembaban'];
$rain      = (int)$live['rain'];
$status    = $live['status_hujan'];

$skor = 0;
$warning = [];

// === RULE SUHU ===
if ($suhu >= 28 && $suhu <= 35) {
    $skor += 35;
    $kondisi_suhu = "Ideal";
} elseif ($suhu > 35) {
    $skor += 20;
    $kondisi_suhu = "Terlalu Panas";
    $warning[] = "Suhu terlalu tinggi, dapat merusak beberapa jenis kain";
} elseif ($suhu >= 25 && $suhu < 28) {
    $skor += 25;
    $kondisi_suhu = "Cukup";
} else {
    $skor += 5;
    $kondisi_suhu = "Kurang";
    $warning[] = "Suhu rendah, proses pengeringan akan lambat";
}

// === RULE KELEMBABAN (makin rendah = makin baik untuk jemur) ===
if ($kelembaban < 50) {
    $skor += 35;
    $kondisi_kl = "Kering (Ideal)";
} elseif ($kelembaban < 65) {
    $skor += 25;
    $kondisi_kl = "Normal";
} elseif ($kelembaban < 80) {
    $skor += 10;
    $kondisi_kl = "Lembab";
    $warning[] = "Kelembaban tinggi, pakaian akan lebih lama kering";
} else {
    $skor += 0;
    $kondisi_kl = "Sangat Lembab";
    $warning[] = "Kelembaban sangat tinggi, tidak disarankan menjemur";
}

// === RULE SENSOR HUJAN ===
if ($status === "TIDAK HUJAN") {
    if ($rain > 3500) {
        $skor += 30;
        $kondisi_rain = "Cerah";
    } else {
        $skor += 15;
        $kondisi_rain = "Mendung / Gerimis";
        $warning[] = "Sensor mendeteksi kelembaban di udara, waspadai hujan";
    }
} else {
    $skor += 0;
    $kondisi_rain = "Hujan";
    $warning[] = "Sedang hujan, jemuran harus masuk";
}

// === FINAL REKOMENDASI ===
if ($skor >= 75) {
    $rekomendasi = "Sangat Disarankan Menjemur";
    $level = "excellent";
} elseif ($skor >= 55) {
    $rekomendasi = "Aman untuk Menjemur";
    $level = "good";
} elseif ($skor >= 35) {
    $rekomendasi = "Kurang Ideal, Pertimbangkan Ulang";
    $level = "warning";
} else {
    $rekomendasi = "Tidak Disarankan Menjemur";
    $level = "danger";
}

echo json_encode([
    "skor"        => $skor,
    "level"       => $level,
    "rekomendasi" => $rekomendasi,
    "detail" => [
        "suhu"       => $kondisi_suhu,
        "kelembaban" => $kondisi_kl,
        "hujan"      => $kondisi_rain
    ],
    "warning" => $warning
]);
?>
