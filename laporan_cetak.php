<?php
session_start();
require 'config.php';

// ================== AMBIL RANGE TANGGAL ==================
$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

// validasi sederhana format date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari))   $dari = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) $sampai = date('Y-m-d');

// WHERE range inklusif (00:00:00 - 23:59:59)
$where = "t.tgl_transaksi >= '{$dari} 00:00:00' AND t.tgl_transaksi <= '{$sampai} 23:59:59'";

$keterangan = "Laporan Transaksi<br>Dari Tanggal " . date('d/m/Y', strtotime($dari)) .
              " sampai " . date('d/m/Y', strtotime($sampai));

// ================== QUERY DATA (TABEL) ==================
$sqlTrans = "
    SELECT 
        t.id_transaksi,
        t.tgl_transaksi,
        t.total_transaksi,
        t.status,
        p.nama
    FROM tb_transaksi t
    LEFT JOIN tb_pelanggan p ON t.id_user = p.id_user
    WHERE $where
    ORDER BY t.tgl_transaksi ASC
";
$result = mysqli_query($conn, $sqlTrans);

// ================== HITUNG TOTAL ==================
$hitungSemua = false;

// status yang dihitung (samakan dengan status di database kamu)
$statusDihitung = ["selesai", "konfirmasi"]; // <-- sesuaikan

$grandTotal = 0;
$rows = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;

        if ($hitungSemua) {
            $grandTotal += (int)$r['total_transaksi'];
        } else {
            if (in_array($r['status'], $statusDihitung, true)) {
                $grandTotal += (int)$r['total_transaksi'];
            }
        }
    }
}

// ================== DATA GRAFIK: TOTAL PENDAPATAN PER TANGGAL ==================
// grafik mengikuti aturan total (jika $hitungSemua=false, hanya status tertentu)
$chartLabels = [];
$chartValues = [];

$chartStatusWhere = "";
if (!$hitungSemua && !empty($statusDihitung)) {
    $statusEsc = array_map(function($s) use ($conn){
        return "'" . mysqli_real_escape_string($conn, $s) . "'";
    }, $statusDihitung);
    $chartStatusWhere = " AND t.status IN (" . implode(',', $statusEsc) . ")";
}

$sqlChart = "
    SELECT 
        DATE(t.tgl_transaksi) AS tgl,
        IFNULL(SUM(t.total_transaksi),0) AS total
    FROM tb_transaksi t
    WHERE $where $chartStatusWhere
    GROUP BY DATE(t.tgl_transaksi)
    ORDER BY DATE(t.tgl_transaksi) ASC
";

$resChart = mysqli_query($conn, $sqlChart);
if ($resChart && mysqli_num_rows($resChart) > 0) {
    while ($c = mysqli_fetch_assoc($resChart)) {
        // label tanggal (contoh: 20/12)
        $chartLabels[] = date('d/m', strtotime($c['tgl']));
        $chartValues[] = (int)$c['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - Omah Kopi</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ====== Tampilan lebih besar ====== */
        body { font-family: "Poppins", sans-serif; font-size: 14px; color: #111; }
        .kop { text-align: center; margin-bottom: 12px; }
        .kop h3 { margin: 0; font-size: 28px; font-weight: 800; }
        .muted { color: #666; font-size: 12px; }
        .table th, .table td { padding: 10px; vertical-align: middle; font-size: 13px; }
        .table thead th { background: #f7f7f7; font-weight: 800; }

        /* blok grafik */
        .box { border: 1px solid #ddd; border-radius: 10px; overflow: hidden; margin-bottom: 12px; }
        .box-h { background: #f7f7f7; padding: 10px 12px; font-weight: 800; }
        .box-b { padding: 12px; }
        .chart-wrap { height: 260px; }

        /* canvas->img untuk print */
        #chartImage { display: none; }

        @page { size: A4; margin: 10mm; }
        @media print {
            .no-print { display: none !important; }
            #salesChart { display: none !important; }
            #chartImage { display: block !important; width: 100%; }

            tr, td, th { page-break-inside: avoid !important; }
            thead { display: table-header-group; }
        }
    </style>
</head>

<!-- NOTE: jangan auto print di body onload, karena chart butuh waktu render -->
<body>

<div class="container-fluid">

    <div class="no-print mt-2 mb-2 text-right">
        <button onclick="doPrint()" class="btn btn-sm btn-secondary">Print / Simpan PDF</button>
    </div>

    <div class="kop">
        <h3>Omah Kopi</h3>
        <div><?php echo $keterangan; ?></div>
        <div class="muted">Dicetak: <?php echo date('d-m-Y H:i'); ?></div>
        <hr>
    </div>

    <!-- GRAFIK -->
    <div class="box">
        <div class="box-h">Grafik Pendapatan (Harian)</div>
        <div class="box-b">
            <div class="chart-wrap">
                <canvas id="salesChart"></canvas>
                <img id="chartImage" alt="Grafik Pendapatan">
            </div>
            <div class="muted mt-2">
                <?php if (!$hitungSemua): ?>
                    * Grafik dihitung dari status: <?php echo htmlspecialchars(implode(', ', $statusDihitung)); ?>
                <?php else: ?>
                    * Grafik dihitung dari semua transaksi.
                <?php endif; ?>
            </div>
            <?php if (empty($chartLabels)): ?>
                <div class="mt-2 text-danger" style="font-size:12px;">
                    Tidak ada data grafik pada range ini (atau status tidak cocok).
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex justify-content-between mb-2">
        <div class="muted">
            <?php if (!$hitungSemua): ?>
                * Total dihitung dari status: <?php echo htmlspecialchars(implode(', ', $statusDihitung)); ?>
            <?php else: ?>
                * Total dihitung dari semua transaksi.
            <?php endif; ?>
        </div>
        <div>
            <strong>Total Pendapatan: Rp <?php echo number_format($grandTotal, 0, ',', '.'); ?></strong>
        </div>
    </div>

    <table class="table table-bordered table-sm">
        <thead class="thead-light">
            <tr>
                <th style="width:50px;">No</th>
                <th style="width:170px;">ID Pesanan</th>
                <th style="width:170px;">Tanggal</th>
                <th>Pelanggan</th>
                <th style="width:140px;">Total</th>
                <th style="width:140px;">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($rows)): ?>
            <?php $no = 1; foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['id_transaksi']); ?></td>
                    <td><?php echo date('d-m-Y H:i', strtotime($row['tgl_transaksi'])); ?></td>
                    <td><?php echo htmlspecialchars($row['nama'] ?? '-'); ?></td>
                    <td>Rp <?php echo number_format((int)$row['total_transaksi'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">Tidak ada data.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>

<script>
  // data dari PHP
  const labels = <?php echo json_encode($chartLabels, JSON_UNESCAPED_UNICODE); ?>;
  const values = <?php echo json_encode($chartValues, JSON_UNESCAPED_UNICODE); ?>;

  // bikin chart kalau ada data
  let chartInstance = null;

  function renderChart() {
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;

    // kalau data kosong, jangan error
    const hasData = Array.isArray(labels) && labels.length > 0;

    const ctx = canvas.getContext('2d');
    chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: hasData ? labels : ['-'],
        datasets: [{
          label: 'Pendapatan (Rp)',
          data: hasData ? values : [0],
          tension: 0.35,
          fill: true,
          pointRadius: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID')
            }
          }
        }
      }
    });
  }

  function canvasToImage() {
    const canvas = document.getElementById('salesChart');
    const img = document.getElementById('chartImage');
    if (!canvas || !img) return;
    try {
      img.src = canvas.toDataURL('image/png', 1.0);
    } catch (e) {}
  }

  // tombol print: pastikan chart selesai render + image siap
  function doPrint() {
    // pastikan chart sudah ada
    if (!chartInstance) renderChart();

    // kasih waktu sebentar untuk render, lalu convert dan print
    setTimeout(() => {
      canvasToImage();
      setTimeout(() => window.print(), 200);
    }, 300);
  }

  // render chart saat load
  window.addEventListener('load', () => {
    renderChart();

    // AUTO PRINT (seperti file lamamu), tapi ditunda supaya grafik jadi dulu
    setTimeout(() => {
      canvasToImage();
      window.print();
    }, 900);
  });

  // kalau browser memanggil print dari menu, tetap convert dulu
  window.addEventListener('beforeprint', canvasToImage);
</script>

</body>
</html>
