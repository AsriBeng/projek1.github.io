<?php
session_start();
require 'config.php'; // ganti jika nama file koneksi berbeda

header('Content-Type: application/json');

// Validasi ID terkirim
if (!isset($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID produk tidak dikirim.'
    ]);
    exit;
}

$id = (int) $_POST['id'];
if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID produk tidak valid.'
    ]);
    exit;
}

// Ambil data produk dari database
$query = "SELECT id_menu, nama_menu, harga_menu, foto_menu 
          FROM tb_menu 
          WHERE id_menu = $id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Produk tidak ditemukan.'
    ]);
    exit;
}

$menu = mysqli_fetch_assoc($result);

// Inisialisasi cart jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$price = (int) $menu['harga_menu'];

// Jika produk sudah ada → tambah qty
if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['qty'] += 1;
} else {
    // Jika produk belum ada → tambahkan baru
    $_SESSION['cart'][$id] = [
        'id'    => $menu['id_menu'],
        'name'  => $menu['nama_menu'],
        'price' => $price,
        'image' => $menu['foto_menu'],
        'qty'   => 1
    ];
}



// Kirim response JSON
echo json_encode([
    'success' => true,
    'message' => 'Produk berhasil dimasukkan ke keranjang.',
]);
?>
