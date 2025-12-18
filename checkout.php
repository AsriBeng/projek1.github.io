<?php
session_start();
require 'config.php';

// kalau belum ada cart, balikin ke cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'];

// data user dari session login
$namaPelanggan = $_SESSION['nama'] ?? '';
$idUser        = $_SESSION['id_user'] ?? null;

// ambil no hp dari tb_pelanggan (sesuaikan nama kolom)
$noHp = '';
if ($idUser) {
    $sqlUser = "SELECT no_hp FROM tb_pelanggan WHERE id_user = $idUser LIMIT 1";
    $resUser = mysqli_query($conn, $sqlUser);
    if ($resUser && mysqli_num_rows($resUser) === 1) {
        $rowUser = mysqli_fetch_assoc($resUser);
        $noHp    = $rowUser['no_hp'] ?? '';
    }
}

// meja
$mejaId   = $_SESSION['meja'] ?? null;
$namaMeja = '';
if ($mejaId) {
    $sqlMeja = "SELECT meja FROM tb_meja WHERE id_meja = $mejaId LIMIT 1";
    $resMeja = mysqli_query($conn, $sqlMeja);
    if ($resMeja && mysqli_num_rows($resMeja) === 1) {
        $rowMeja = mysqli_fetch_assoc($resMeja);
        $namaMeja = $rowMeja['meja'] ?? '';
    }
}

// hitung total (untuk disimpan & ditampilkan)
$grandTotal = 0;
$totalMenu  = 0; // total qty semua item

foreach ($cart as $item) {
    $price = (int) $item['price'];
    $qty   = (int) $item['qty'];

    $grandTotal += $price * $qty;
    $totalMenu  += $qty;
}

// === JIKA KLIK "Kembali ke Beranda" → SIMPAN TRANSAKSI ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_transaksi'])) {

    // tanggal untuk disimpan (sesuaikan dengan type tgl_transaksi di DB)
    $tglSql = date('Y-m-d H:i:s'); // pakai DATETIME di DB
    $status = 'pending';

    // 1. INSERT ke tb_transaksi
    $sqlTrans = "INSERT INTO tb_transaksi (id_user, id_meja, id_admin, tgl_transaksi, total_menu, total_transaksi, status) VALUES ($idUser, $mejaId, '1', '$tglSql', $totalMenu, $grandTotal, '$status')";

    $simpanTrans = mysqli_query($conn, $sqlTrans);

    if ($simpanTrans) {
    $idTransaksiBaru = mysqli_insert_id($conn);

        // 2. INSERT detail ke tb_detail_transaksi
        foreach ($cart as $idKey => $item) {
            $idMenu = (int)$idKey; // atau sesuaikan dengan struktur cart-mu
            $qty    = (int)$item['qty'];

            $sqlDetail = "INSERT INTO tb_detail_transaksi (id_transaksi, id_menu, qty)
                        VALUES ($idTransaksiBaru, $idMenu, $qty)";
            mysqli_query($conn, $sqlDetail);
        }

        // 3. kosongkan cart & meja
        unset($_SESSION['cart']);
        unset($_SESSION['meja']);

        // 4. Tampilkan notifikasi lalu pindah ke index.php
        echo "<script>
                alert('Pesanan berhasil dibuat. Silakan tunggu di meja yang telah dipesan.');
                window.location.href = 'index.php';
            </script>";
        exit;
    } else {
        die("Gagal menyimpan transaksi: " . mysqli_error($conn));
    }
}

// tanggal untuk tampil di struk (boleh beda format dari DB)
$tanggal = date('d-m-Y H:i');
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan - Omah Kopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f2e2c4;
        }
        .struk-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            margin: 30px auto;
            border: 1px solid #d1b79b;
        }
        .judul {
            text-align: center;
            margin-bottom: 15px;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
        }
        .btn-print {
            background-color: #8B5E3C;
            color: #FFF8E7;
        }

        @media print {
            body {
                background: #ffffff !important;
            }
            .struk-container {
                box-shadow: none;
                border: none;
                max-width: 100%;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="struk-container" id="struk">
        <div class="judul">
            <h3>Omah Kopi</h3>
            <p>Struk Reservasi &amp; Pemesanan Menu</p>
        </div>

        <p><strong>Nama Pelanggan:</strong> <?= htmlspecialchars($namaPelanggan); ?></p>
        <p><strong>No HP:</strong> <?= htmlspecialchars($noHp); ?></p>
        <p><strong>Meja:</strong> <?= htmlspecialchars($namaMeja); ?></p>
        <p><strong>Tanggal:</strong> <?= $tanggal; ?></p>

        <hr>

        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $item): 
                    $price = (int) $item['price'];
                    $qty   = (int) $item['qty'];
                    $sub   = $price * $qty;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td>Rp <?= number_format($price, 0, ',', '.'); ?></td>
                        <td><?= $qty; ?></td>
                        <td>Rp <?= number_format($sub, 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="total">Total: Rp <?= number_format($grandTotal, 0, ',', '.'); ?></p>

        <p>Terima kasih telah berkunjung ke Omah Kopi.</p>

        <div class="no-print mt-3">
            <button class="btn btn-print btn-sm" onclick="window.print()">Cetak Struk</button>

            <!-- tombol simpan + kembali -->
            <form method="post" class="d-inline">
                <button type="submit" name="simpan_transaksi" class="btn btn-secondary btn-sm">
                    Kembali ke Beranda
                </button>
            </form>
        </div>
    </div>
</body>
</html>
