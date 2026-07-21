<!DOCTYPE html>
<html>
<head>
  <title>Sistem Pakar & Prediksi - ESP32 Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
 
<body>
 
<div class="navbar-wrap">
  <div class="navbar">
    <a href="index.php">🏠 Dashboard</a>
    <a href="prediksi.php" class="active">🤖 Sistem Pakar & Prediksi</a>
    <a href="riwayat.php">📋 Riwayat & CSV</a>
  </div>
</div>
 
<h2>🤖 Sistem Pakar & Prediksi AI</h2>
<div class="subtitle">Analisis kondisi saat ini dan prediksi cuaca</div>
 
<!-- SISTEM PAKAR + AI PREDICTION -->
<div class="section-title">🧠 Sistem Pakar & Prediksi AI</div>
 
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
 
<br>
 
<script>
 
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
 
// Jalanin — pakar tiap 10 detik, prediksi tiap 60 detik
setInterval(fetchPakar, 10000);
fetchPakar();
 
setInterval(fetchPrediksi, 60000);
fetchPrediksi();
 
</script>
 
</body>
</html>
 
