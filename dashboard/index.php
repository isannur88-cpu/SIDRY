<!DOCTYPE html>
<html>
<head>
  <title>ESP32 Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
 
<body>
 
<div class="navbar-wrap">
  <div class="navbar">
    <a href="index.php" class="active">🏠 Dashboard</a>
    <a href="prediksi.php">🤖 Sistem Pakar & Prediksi</a>
    <a href="riwayat.php">📋 Riwayat & CSV</a>
  </div>
</div>
 
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
 
<br>
 
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
 
// FETCH STATISTIK (tiap 60 detik, sama kayak interval DB)
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
 
  } catch (err) {
    console.error('Stats error:', err);
  }
}
 
// Jalanin
setInterval(fetchData, 3000);
fetchData();
 
setInterval(fetchStats, 60000);
fetchStats();
 
</script>
 
</body>
</html>
