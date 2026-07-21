<!DOCTYPE html>
<html>
<head>
  <title>Riwayat Data - ESP32 Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
 
<body>
 
<div class="navbar-wrap">
  <div class="navbar">
    <a href="index.php">🏠 Dashboard</a>
    <a href="prediksi.php">🤖 Sistem Pakar & Prediksi</a>
    <a href="riwayat.php" class="active">📋 Riwayat & CSV</a>
  </div>
</div>
 
<h2>📋 Riwayat Data Hari Ini</h2>
<div class="subtitle">Data historis sensor sepanjang hari</div>
 
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
 
async function fetchRiwayat() {
  try {
    const res = await fetch('api/stats.php');
    const d = await res.json();
 
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
    console.error('Riwayat error:', err);
  }
}
 
// Jalanin (interval sama kayak sebelumnya, 60 detik)
setInterval(fetchRiwayat, 60000);
fetchRiwayat();
 
</script>
 
</body>
</html>
