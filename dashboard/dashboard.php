<!DOCTYPE html>
<html>
<head>
  <title>ESP32 Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>

    * { box-sizing: border-box; }

    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      text-align: center;
      background: linear-gradient(135deg, #74ebd5, #ACB6E5);
      min-height: 100vh;
      padding: 30px 15px;
      margin: 0;
    }

    h2 { color: #2c3e50; margin-bottom: 5px; }

    .subtitle {
      color: #34495e;
      margin-bottom: 30px;
      font-size: 14px;
    }

    /* === SENSOR CARDS === */
    .container {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
      max-width: 950px;
      margin: 0 auto;
    }

    .card {
      width: 200px;
      padding: 25px;
      border-radius: 18px;
      color: white;
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
      transition: transform 0.2s ease;
    }

    .card:hover { transform: translateY(-6px); }

    .card h3 {
      margin: 0 0 10px 0;
      font-size: 16px;
      opacity: 0.9;
    }

    .temp   { background: linear-gradient(135deg, #ff6b6b, #e74c3c); }
    .hum    { background: linear-gradient(135deg, #4facfe, #3498db); }
    .rain   { background: linear-gradient(135deg, #1dd1a1, #16a085); }
    .status { background: linear-gradient(135deg, #a55eea, #8e44ad); }

    .value { font-size: 38px; font-weight: bold; margin-top: 5px; }
    .unit  { font-size: 18px; }

    /* === SECTION TITLE === */
    .section-title {
      margin: 40px 0 15px 0;
      color: #2c3e50;
      font-size: 18px;
      font-weight: bold;
    }

    /* === KONTROL PANEL === */
    .panel {
      background: rgba(255,255,255,0.85);
      border-radius: 18px;
      padding: 20px;
      max-width: 600px;
      margin: 0 auto;
      box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    }

    .jemuran-btn {
      width: 160px;
      height: 55px;
      font-size: 15px;
      margin: 8px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      color: white;
      font-weight: bold;
      transition: transform 0.15s ease, box-shadow 0.15s ease;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .jemuran-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 12px rgba(0,0,0,0.3);
    }

    .masuk  { background: #16a085; }
    .keluar { background: #2980b9; }
    .auto   { background: #f39c12; }

    .esp-status {
      margin-top: 10px;
      font-size: 13px;
      color: #555;
    }

    /* === STATISTIK === */
    .stats-grid {
      display: flex;
      justify-content: center;
      gap: 15px;
      flex-wrap: wrap;
      max-width: 950px;
      margin: 0 auto;
    }

    .stat-card {
      background: rgba(255,255,255,0.85);
      border-radius: 15px;
      padding: 18px 22px;
      min-width: 150px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    .stat-card .stat-label {
      font-size: 13px;
      color: #777;
      margin-bottom: 6px;
    }

    .stat-card .stat-value {
      font-size: 28px;
      font-weight: bold;
      color: #2c3e50;
    }

    .stat-card .stat-unit {
      font-size: 14px;
      color: #555;
    }

    /* === RIWAYAT TABLE === */
    .table-wrapper {
      max-width: 850px;
      margin: 0 auto;
      background: rgba(255,255,255,0.85);
      border-radius: 18px;
      padding: 20px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.15);
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    th {
      background: #2c3e50;
      color: white;
      padding: 10px 12px;
      text-align: center;
    }

    td {
      padding: 9px 12px;
      text-align: center;
      border-bottom: 1px solid #eee;
      color: #333;
    }

    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f0f8ff; }

    .badge-hujan {
      background: #3498db;
      color: white;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 12px;
    }

    .badge-cerah {
      background: #f39c12;
      color: white;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 12px;
    }

    /* === TOMBOL CSV === */
    .btn-csv {
      display: inline-block;
      margin-top: 20px;
      background: #27ae60;
      color: white;
      padding: 12px 28px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: bold;
      text-decoration: none;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      transition: transform 0.15s ease;
    }

    .btn-csv:hover { transform: translateY(-3px); }


    /* === AI / ML SECTION === */
.ai-grid {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
  max-width: 950px;
  margin: 0 auto;
}

.ai-card {
  background: rgba(255,255,255,0.85);
  border-radius: 18px;
  padding: 22px;
  flex: 1;
  min-width: 260px;
  max-width: 420px;
  box-shadow: 0 6px 15px rgba(0,0,0,0.15);
  text-align: left;
}

.ai-card h4 {
  margin: 0 0 12px 0;
  color: #2c3e50;
  font-size: 15px;
  border-bottom: 2px solid #eee;
  padding-bottom: 8px;
}

.skor-bar-wrap {
  background: #eee;
  border-radius: 20px;
  height: 14px;
  margin: 8px 0 12px 0;
  overflow: hidden;
}

.skor-bar {
  height: 100%;
  border-radius: 20px;
  transition: width 0.5s ease;
}

.level-excellent { background: #27ae60; }
.level-good      { background: #2ecc71; }
.level-warning   { background: #f39c12; }
.level-danger    { background: #e74c3c; }

.rekomen-text {
  font-weight: bold;
  font-size: 15px;
  margin-bottom: 8px;
}

.warning-list {
  font-size: 12px;
  color: #e74c3c;
  margin: 6px 0 0 0;
  padding-left: 16px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  padding: 4px 0;
  border-bottom: 1px solid #f0f0f0;
  color: #555;
}

.nb-result {
  font-size: 22px;
  font-weight: bold;
  color: #2c3e50;
  margin: 8px 0 4px 0;
}

.nb-conf {
  font-size: 13px;
  color: #777;
  margin-bottom: 10px;
}

.trend-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  padding: 4px 0;
  color: #555;
}

.arrow-up    { color: #e74c3c; }
.arrow-down  { color: #3498db; }
.arrow-same  { color: #27ae60; }

/* Tabel prediksi besok */
.pred-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  margin-top: 10px;
}

.pred-table th {
  background: #2c3e50;
  color: white;
  padding: 8px 10px;
  text-align: center;
}

.pred-table td {
  padding: 7px 10px;
  text-align: center;
  border-bottom: 1px solid #eee;
}

.pred-table tr:last-child td { border-bottom: none; }

.badge-cerah2 { background: #f39c12; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; }
.badge-hujan2 { background: #3498db; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; }
.badge-pasti  { background: #95a5a6; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; }

.note-data {
  font-size: 11px;
  color: #999;
  margin-top: 10px;
  font-style: italic;
}

  </style>
</head>

<body>

<h2>🌤️ Realtime Monitoring Jemuran Otomatis</h2>
<div class="subtitle" id="lastUpdate">Memuat data...</div>

<!-- SENSOR CARDS -->
<div class="container">

  <div class="card temp">
    <h3>Suhu</h3>
    <div class="value">
      <span id="suhu">--</span>
      <span class="unit">°C</span>
    </div>
  </div>

  <div class="card hum">
    <h3>Kelembaban</h3>
    <div class="value">
      <span id="kelembaban">--</span>
      <span class="unit">%</span>
    </div>
  </div>

  <div class="card rain">
    <h3>Rain Value</h3>
    <div class="value">
      <span id="rain">--</span>
    </div>
  </div>

  <div class="card status">
    <h3>Status Hujan</h3>
    <div class="value" style="font-size:24px;">
      <span id="status_hujan">--</span>
    </div>
  </div>

</div>

<!-- KONTROL JEMURAN -->
<div class="section-title">🎛️ Kontrol Jemuran</div>

<div class="panel">
  <button class="jemuran-btn masuk" onclick="sendCommand('masuk')">🏠 Jemuran Masuk</button>
  <button class="jemuran-btn keluar" onclick="sendCommand('keluar')">☀️ Jemuran Keluar</button>
  <button class="jemuran-btn auto" onclick="sendCommand('auto')">🤖 Auto Mode</button>
  <div class="esp-status" id="espStatus">Status ESP32: -</div>
</div>

<!-- STATISTIK HARI INI -->
<div class="section-title">📊 Statistik Hari Ini</div>

<div class="stats-grid">

  <div class="stat-card">
    <div class="stat-label">Data Masuk</div>
    <div class="stat-value"><span id="stat-masuk">--</span></div>
    <div class="stat-unit">record</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Suhu Maksimum</div>
    <div class="stat-value"><span id="stat-suhu-max">--</span></div>
    <div class="stat-unit">°C</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Suhu Minimum</div>
    <div class="stat-value"><span id="stat-suhu-min">--</span></div>
    <div class="stat-unit">°C</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Rata-rata Suhu</div>
    <div class="stat-value"><span id="stat-suhu-avg">--</span></div>
    <div class="stat-unit">°C</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Status Hujan Terakhir</div>
    <div class="stat-value" style="font-size:16px; margin-top:6px;">
      <span id="stat-last-status">--</span><br>
      <span style="font-size:12px; color:#777;" id="stat-last-time">--</span>
    </div>
  </div>

</div>

<!-- SISTEM PAKAR + AI PREDICTION -->
<div class="section-title">🤖 Sistem Pakar & Prediksi AI</div>

<div class="ai-grid">

  <!-- SISTEM PAKAR -->
  <div class="ai-card">
    <h4>🧠 Sistem Pakar — Kondisi Sekarang</h4>
    <div id="pakar-loading" style="color:#aaa; font-size:13px;">Memuat...</div>
    <div id="pakar-content" style="display:none;">
      <div class="rekomen-text" id="pakar-rekomen">--</div>
      <div class="skor-bar-wrap">
        <div class="skor-bar" id="pakar-bar" style="width:0%"></div>
      </div>
      <div style="font-size:12px; color:#777; margin-bottom:10px;">
        Skor Kelayakan Jemur: <strong id="pakar-skor">--</strong>/100
      </div>
      <div class="detail-row"><span>Kondisi Suhu</span><span id="p-suhu">--</span></div>
      <div class="detail-row"><span>Kondisi Kelembaban</span><span id="p-kl">--</span></div>
      <div class="detail-row"><span>Kondisi Hujan</span><span id="p-rain">--</span></div>
      <ul class="warning-list" id="pakar-warning"></ul>
    </div>
  </div>

  <!-- NAIVE BAYES + TREND -->
  <div class="ai-card">
    <h4>📈 Deteksi Tren (2 Jam Terakhir)</h4>
    <div id="trend-loading" style="color:#aaa; font-size:13px;">Memuat...</div>
    <div id="trend-content" style="display:none;">
      <div class="trend-row"><span>Tren Suhu</span><span id="t-suhu">--</span></div>
      <div class="trend-row"><span>Tren Kelembaban</span><span id="t-kl">--</span></div>
      <div class="trend-row"><span>Tren Sensor Hujan</span><span id="t-rain">--</span></div>
      <div style="margin-top:12px; font-size:13px; color:#777;">Prediksi 3 Jam ke Depan:</div>
      <div style="font-weight:bold; font-size:16px; margin: 4px 0 12px 0;" id="pred-3jam">--</div>

      <h4 style="margin-top:4px;">🤖 Naive Bayes — Prediksi Status Saat Ini</h4>
      <div id="nb-loading" style="color:#aaa; font-size:13px;">Memuat...</div>
      <div id="nb-content" style="display:none;">
        <div class="nb-result" id="nb-result">--</div>
        <div class="nb-conf">Confidence: <strong id="nb-conf">--%</strong>
          &nbsp;|&nbsp; Training data: <span id="nb-data">--</span> record
        </div>
      </div>
    </div>
  </div>

</div>

<!-- PREDIKSI BESOK -->
<div class="section-title">🌤️ Prediksi Besok (Berdasarkan Pola Historis)</div>

<div class="table-wrapper">
  <div id="besok-note" class="note-data" style="margin-bottom:10px;"></div>
  <table class="pred-table">
    <thead>
      <tr>
        <th>Jam</th>
        <th>Rata-rata Suhu (°C)</th>
        <th>Rata-rata Kelembaban (%)</th>
        <th>Peluang Hujan</th>
        <th>Prediksi</th>
      </tr>
    </thead>
    <tbody id="tabel-besok">
      <tr><td colspan="5">Memuat data...</td></tr>
    </tbody>
  </table>
  <div class="note-data">
    * Prediksi berdasarkan pola data historis per jam. Akurasi meningkat seiring bertambahnya data.
  </div>
</div>

<!-- RIWAYAT DATA -->
<div class="section-title">📋 Riwayat Data Hari Ini</div>

<div class="table-wrapper">
  <table>
    <thead>
      <tr>
        <th>Waktu</th>
        <th>Suhu (°C)</th>
        <th>Kelembaban (%)</th>
        <th>Rain Value</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="tabel-riwayat">
      <tr><td colspan="5">Memuat data...</td></tr>
    </tbody>
  </table>
</div>

<!-- TOMBOL DOWNLOAD CSV -->
<a href="api/download_csv.php" class="btn-csv">⬇️ Download CSV Hari Ini</a>

<br><br>

<script>

const esp32 = "10.199.126.213";
let lastRainStatus = null;

if ("Notification" in window) {
  if (Notification.permission !== "granted") {
    Notification.requestPermission();
  }
}

function showRainNotification(status) {
  if (Notification.permission !== "granted") return;
  if (status === "HUJAN") {
    new Notification("🌧️ Hujan Terdeteksi!", {
      body: "Jemuran masuk otomatis.",
      icon: "https://cdn-icons-png.flaticon.com/512/3351/3351979.png"
    });
  } else {
    new Notification("☀️ Hujan Berhenti", {
      body: "Jemuran kembali normal.",
      icon: "https://cdn-icons-png.flaticon.com/512/869/869869.png"
    });
  }
}

function sendCommand(cmd) {
  document.getElementById('espStatus').textContent = "Mengirim perintah...";
  fetch("http://" + esp32 + "/" + cmd, { mode: 'no-cors' })
  .then(() => {
    document.getElementById('espStatus').textContent = "✅ Berhasil: " + cmd;
  })
  .catch(() => {
    document.getElementById('espStatus').textContent = "❌ ESP32 Tidak Terhubung";
  });
}

// FETCH LIVE DATA (tiap 3 detik)
async function fetchData() {
  try {
    const res = await fetch('api/live_read.php');
    const last = await res.json();

    if (!last.suhu) return;

    document.getElementById('suhu').textContent = parseFloat(last.suhu).toFixed(1);
    document.getElementById('kelembaban').textContent = parseFloat(last.kelembaban).toFixed(1);
    document.getElementById('rain').textContent = last.rain;
    document.getElementById('status_hujan').textContent = last.status_hujan;

    if (lastRainStatus === null) {
      lastRainStatus = last.status_hujan;
    } else if (lastRainStatus !== last.status_hujan) {
      showRainNotification(last.status_hujan);
      lastRainStatus = last.status_hujan;
    }

    document.getElementById('lastUpdate').textContent =
      "Update terakhir: " + new Date().toLocaleTimeString();

  } catch (err) {
    console.error(err);
    document.getElementById('lastUpdate').textContent = "Gagal memuat data";
  }
}

// FETCH STATISTIK + RIWAYAT (tiap 60 detik, sama kayak interval DB)
async function fetchStats() {
  try {
    const res = await fetch('api/stats.php');
    const d = await res.json();

    document.getElementById('stat-masuk').textContent = d.data_masuk ?? '--';
    document.getElementById('stat-suhu-max').textContent = d.suhu_max ?? '--';
    document.getElementById('stat-suhu-min').textContent = d.suhu_min ?? '--';
    document.getElementById('stat-suhu-avg').textContent = d.suhu_avg ?? '--';
    document.getElementById('stat-last-status').textContent = d.last_status ?? '--';

    // Tampilin jamnya aja (bukan full datetime)
    if (d.last_status_time && d.last_status_time !== '--') {
      const jam = d.last_status_time.split(' ')[1];
      document.getElementById('stat-last-time').textContent = jam;
    }

    // Isi tabel riwayat
    const tbody = document.getElementById('tabel-riwayat');
    if (d.riwayat && d.riwayat.length > 0) {
      tbody.innerHTML = d.riwayat.map(row => {
        const jam = row.created_at.split(' ')[1];
        const badge = row.status_hujan === 'HUJAN'
          ? `<span class="badge-hujan">🌧️ HUJAN</span>`
          : `<span class="badge-cerah">☀️ TIDAK HUJAN</span>`;
        return `<tr>
          <td>${jam}</td>
          <td>${parseFloat(row.suhu).toFixed(1)}</td>
          <td>${parseFloat(row.kelembaban).toFixed(1)}</td>
          <td>${row.rain}</td>
          <td>${badge}</td>
        </tr>`;
      }).join('');
    } else {
      tbody.innerHTML = '<tr><td colspan="5">Belum ada data hari ini</td></tr>';
    }

  } catch (err) {
    console.error('Stats error:', err);
  }
}

// Helper tren → arrow + warna
function trendArrow(val) {
  if (val === "naik" || val === "memburuk")
    return `<span class="arrow-up">▲ ${val}</span>`;
  if (val === "turun" || val === "membaik")
    return `<span class="arrow-down">▼ ${val}</span>`;
  return `<span class="arrow-same">→ ${val}</span>`;
}

async function fetchPakar() {
  try {
    const res = await fetch('api/pakar.php');
    const d = await res.json();

    document.getElementById('pakar-loading').style.display = 'none';
    document.getElementById('pakar-content').style.display = 'block';

    document.getElementById('pakar-rekomen').textContent = d.rekomendasi;
    document.getElementById('pakar-skor').textContent = d.skor;
    document.getElementById('p-suhu').textContent  = d.detail.suhu;
    document.getElementById('p-kl').textContent    = d.detail.kelembaban;
    document.getElementById('p-rain').textContent  = d.detail.hujan;

    const bar = document.getElementById('pakar-bar');
    bar.style.width = d.skor + '%';
    bar.className = `skor-bar level-${d.level}`;

    const warnEl = document.getElementById('pakar-warning');
    warnEl.innerHTML = d.warning.length > 0
      ? d.warning.map(w => `<li>${w}</li>`).join('')
      : '<li style="color:#27ae60">Tidak ada peringatan</li>';

  } catch(e) { console.error('Pakar error:', e); }
}

async function fetchPrediksi() {
  try {
    const res = await fetch('api/prediksi.php');
    const d = await res.json();

    // TREND
    document.getElementById('trend-loading').style.display = 'none';
    document.getElementById('trend-content').style.display = 'block';
    document.getElementById('t-suhu').innerHTML  = trendArrow(d.trend.suhu);
    document.getElementById('t-kl').innerHTML    = trendArrow(d.trend.kelembaban);
    document.getElementById('t-rain').innerHTML  = trendArrow(d.trend.sensor_hujan);
    document.getElementById('pred-3jam').textContent = d.trend.cukup_data
      ? d.trend.prediksi_3jam
      : 'Data belum cukup (butuh minimal 3 record dalam 2 jam)';

    // NAIVE BAYES
    document.getElementById('nb-loading').style.display = 'none';
    document.getElementById('nb-content').style.display = 'block';
    if (d.naive_bayes.cukup_data) {
      document.getElementById('nb-result').textContent = d.naive_bayes.prediksi;
      document.getElementById('nb-conf').textContent   = d.naive_bayes.confidence + '%';
      document.getElementById('nb-data').textContent   = d.naive_bayes.total_data;
    } else {
      document.getElementById('nb-result').textContent = 'Data belum cukup';
      document.getElementById('nb-conf').textContent   = '--';
      document.getElementById('nb-data').textContent   = d.naive_bayes.total_data + ' (butuh min. 10)';
    }

    // PREDIKSI BESOK
    const tbody = document.getElementById('tabel-besok');
    if (d.prediksi_besok && d.prediksi_besok.length > 0) {
      tbody.innerHTML = d.prediksi_besok.map(row => {
        let badge = '';
        if (row.prediksi === 'Kemungkinan Hujan')
          badge = `<span class="badge-hujan2">🌧️ ${row.prediksi}</span>`;
        else if (row.prediksi === 'Tidak Pasti')
          badge = `<span class="badge-pasti">⛅ ${row.prediksi}</span>`;
        else
          badge = `<span class="badge-cerah2">☀️ ${row.prediksi}</span>`;

        return `<tr>
          <td><strong>${row.jam}</strong></td>
          <td>${row.avg_suhu}</td>
          <td>${row.avg_kl}</td>
          <td>${row.prob_hujan}% <span style="font-size:11px;color:#aaa;">(${row.data_points} data)</span></td>
          <td>${badge}</td>
        </tr>`;
      }).join('');
    } else {
      tbody.innerHTML = '<tr><td colspan="5">Belum ada data historis</td></tr>';
    }

  } catch(e) { console.error('Prediksi error:', e); }
}

// Jalanin — pakar + prediksi update tiap 10 detik
setInterval(fetchPakar, 10000);
fetchPakar();

setInterval(fetchPrediksi, 60000);
fetchPrediksi();

// Jalanin
setInterval(fetchData, 3000);
fetchData();

setInterval(fetchStats, 60000);
fetchStats();

</script>

</body>
</html>
