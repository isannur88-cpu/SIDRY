<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include_once '../config/database.php';
$database = new database();
$db = $database->getConnection();

// ============================================
// BAGIAN 1: TREND DETECTION (2 jam terakhir)
// ============================================

$q = $db->prepare("SELECT suhu, kelembaban, rain, status_hujan
    FROM station
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
    ORDER BY id ASC");
$q->execute();
$trend_data = $q->fetchAll(PDO::FETCH_ASSOC);

$trend_suhu = "stabil";
$trend_kl   = "stabil";
$trend_rain = "stabil";
$prediksi_3jam = "Data tidak cukup (butuh minimal 3 record)";
$cukup_data_trend = count($trend_data) >= 3;

if ($cukup_data_trend) {

    $mid = floor(count($trend_data) / 2);
    $first = array_slice($trend_data, 0, $mid);
    $second = array_slice($trend_data, $mid);

    $avg = function($arr, $key) {
        return array_sum(array_column($arr, $key)) / count($arr);
    };

    $d_suhu = $avg($second, 'suhu') - $avg($first, 'suhu');
    $d_kl   = $avg($second, 'kelembaban') - $avg($first, 'kelembaban');
    $d_rain = $avg($second, 'rain') - $avg($first, 'rain');

    if ($d_suhu > 1)       $trend_suhu = "naik";
    elseif ($d_suhu < -1)  $trend_suhu = "turun";

    if ($d_kl > 3)         $trend_kl = "naik";
    elseif ($d_kl < -3)    $trend_kl = "turun";

    // rain value turun = makin basah
    if ($d_rain < -200)    $trend_rain = "memburuk";
    elseif ($d_rain > 200) $trend_rain = "membaik";

    // Proyeksi 3 jam ke depan (linear extrapolation)
    $last = end($trend_data);
    $proj_kl   = (float)$last['kelembaban'] + ($d_kl * 3);
    $proj_rain = (float)$last['rain'] + ($d_rain * 3);
    $proj_rain = max(0, min(4095, $proj_rain));

    if ($proj_rain < 2500 || $proj_kl > 85) {
        $prediksi_3jam = "Kemungkinan Hujan";
    } elseif ($proj_rain < 3200 || $proj_kl > 75) {
        $prediksi_3jam = "Kemungkinan Mendung";
    } else {
        $prediksi_3jam = "Kemungkinan Cerah";
    }
}

// ============================================
// BAGIAN 2: NAIVE BAYES
// (train dari seluruh data DB, prediksi kondisi saat ini)
// ============================================

$q2 = $db->query("SELECT suhu, kelembaban, rain, status_hujan FROM station");
$all_data = $q2->fetchAll(PDO::FETCH_ASSOC);

$nb_prediksi   = "--";
$nb_confidence = 0;
$nb_total_data = count($all_data);
$cukup_data_nb = $nb_total_data >= 10;

if ($cukup_data_nb) {

    function binSuhu($v) {
        if ($v < 25) return 'dingin';
        if ($v < 30) return 'normal';
        return 'panas';
    }
    function binKl($v) {
        if ($v < 50) return 'kering';
        if ($v < 70) return 'normal';
        return 'lembab';
    }
    function binRain($v) {
        if ($v < 2000) return 'lebat';
        if ($v < 3000) return 'hujan';
        if ($v < 3500) return 'mendung';
        return 'kering';
    }

    $class_count   = ['HUJAN' => 0, 'TIDAK HUJAN' => 0];
    $feat_count    = [
        'HUJAN'       => ['suhu' => [], 'kl' => [], 'rain' => []],
        'TIDAK HUJAN' => ['suhu' => [], 'kl' => [], 'rain' => []]
    ];

    foreach ($all_data as $row) {
        $c = $row['status_hujan'];
        $class_count[$c]++;
        $bs = binSuhu((float)$row['suhu']);
        $bk = binKl((float)$row['kelembaban']);
        $br = binRain((int)$row['rain']);
        $feat_count[$c]['suhu'][$bs] = ($feat_count[$c]['suhu'][$bs] ?? 0) + 1;
        $feat_count[$c]['kl'][$bk]   = ($feat_count[$c]['kl'][$bk] ?? 0) + 1;
        $feat_count[$c]['rain'][$br] = ($feat_count[$c]['rain'][$br] ?? 0) + 1;
    }

    $liveFile = __DIR__ . '/live_data.json';
    if (file_exists($liveFile)) {
        $live = json_decode(file_get_contents($liveFile), true);
        $cs = binSuhu((float)$live['suhu']);
        $ck = binKl((float)$live['kelembaban']);
        $cr = binRain((int)$live['rain']);

        $scores = [];
        foreach (['HUJAN', 'TIDAK HUJAN'] as $c) {
            $n = $class_count[$c];
            if ($n == 0) continue;
            $prior  = $n / $nb_total_data;
            // Laplace smoothing (vocab size = 4 per feature)
            $p_s = (($feat_count[$c]['suhu'][$cs] ?? 0) + 1) / ($n + 4);
            $p_k = (($feat_count[$c]['kl'][$ck] ?? 0) + 1)   / ($n + 4);
            $p_r = (($feat_count[$c]['rain'][$cr] ?? 0) + 1)  / ($n + 4);
            $scores[$c] = $prior * $p_s * $p_k * $p_r;
        }

        $total_score = array_sum($scores);
        arsort($scores);
        $winner = array_key_first($scores);
        $nb_prediksi   = $winner;
        $nb_confidence = $total_score > 0
            ? round(($scores[$winner] / $total_score) * 100, 1)
            : 0;
    }
}

// ============================================
// BAGIAN 3: PREDIKSI BESOK
// (pola per jam dari seluruh data historis)
// ============================================

$q3 = $db->query("SELECT
    HOUR(created_at) as jam,
    AVG(suhu) as avg_suhu,
    AVG(kelembaban) as avg_kl,
    SUM(CASE WHEN status_hujan = 'HUJAN' THEN 1 ELSE 0 END) as jml_hujan,
    COUNT(*) as total
    FROM station
    GROUP BY HOUR(created_at)
    ORDER BY jam ASC");
$pola = $q3->fetchAll(PDO::FETCH_ASSOC);

$prediksi_besok = [];
foreach ($pola as $row) {
    $prob = $row['total'] > 0
        ? round(($row['jml_hujan'] / $row['total']) * 100, 1)
        : 0;
    $prediksi_besok[] = [
        "jam"          => sprintf("%02d:00", $row['jam']),
        "avg_suhu"     => round($row['avg_suhu'], 1),
        "avg_kl"       => round($row['avg_kl'], 1),
        "prob_hujan"   => $prob,
        "prediksi"     => $prob >= 60 ? "Kemungkinan Hujan"
                         : ($prob >= 35 ? "Tidak Pasti" : "Kemungkinan Cerah"),
        "data_points"  => (int)$row['total']
    ];
}

echo json_encode([
    "trend" => [
        "suhu"          => $trend_suhu,
        "kelembaban"    => $trend_kl,
        "sensor_hujan"  => $trend_rain,
        "prediksi_3jam" => $prediksi_3jam,
        "cukup_data"    => $cukup_data_trend,
        "data_points"   => count($trend_data)
    ],
    "naive_bayes" => [
        "prediksi"     => $nb_prediksi,
        "confidence"   => $nb_confidence,
        "total_data"   => $nb_total_data,
        "cukup_data"   => $cukup_data_nb
    ],
    "prediksi_besok" => $prediksi_besok
]);
?>
