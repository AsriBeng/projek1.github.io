<?php
session_start();
include 'config.php';

$error = '';
$success = '';

// biar form tetap keisi kalau ada error
$nama     = $_POST['nama']     ?? '';
$username = $_POST['username'] ?? '';
$no_hp    = $_POST['no_hp']    ?? '';
$alamat   = $_POST['alamat']   ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($nama);
    $username = trim($username);
    $no_hp    = trim($no_hp);
    $alamat   = trim($alamat);
    $password = $_POST['password']  ?? '';
    $konfirpw = $_POST['konfirpw'] ?? '';

    // validasi dasar
    if ($nama === '' || $username === '' || $no_hp === '' || $password === '' || $konfirpw === '') {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $konfirpw) {
        $error = 'Kata sandi dan konfirmasi tidak sama.';
    } else {
        // cek username sudah dipakai belum
        $usernameEsc = mysqli_real_escape_string($conn, $username);
        $cek = mysqli_query($conn, "SELECT id_user FROM tb_pelanggan WHERE username = '$usernameEsc' LIMIT 1");

        if ($cek && mysqli_num_rows($cek) > 0) {
            $error = 'Username sudah digunakan, silakan pilih username lain.';
        } else {
            // simpan ke database (password masih plaintext, sesuaikan dengan login kamu sekarang)
            $namaEsc   = mysqli_real_escape_string($conn, $nama);
            $nohpEsc   = mysqli_real_escape_string($conn, $no_hp);
            $alamatEsc = mysqli_real_escape_string($conn, $alamat);
            $passEsc   = mysqli_real_escape_string($conn, $password);

            $sql = "INSERT INTO tb_pelanggan (nama, username, password, alamat, no_hp)
                    VALUES ('$namaEsc', '$usernameEsc', '$passEsc', '$alamatEsc', '$nohpEsc')";

            if (mysqli_query($conn, $sql)) {
                echo "<script>
                        alert('Pendaftaran berhasil! Silakan login.');
                        window.location.href = 'login.php';
                      </script>";
                exit;
            } else {
                $error = 'Terjadi kesalahan saat menyimpan data: ' . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Omah Kopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('wooden_texture.jpg') repeat;
            color: #3C2F2F;
        }
        .register-container {
            background-color: rgba(245, 230, 204, 0.9);
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            margin: 50px auto;
        }
        .btn-register {
            background-color: #8B5E3C;
            color: #FFF8E7;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2 style="text-align:center;">Daftar Akun</h2>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama"
                       value="<?= htmlspecialchars($nama); ?>" required>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">UserName</label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= htmlspecialchars($username); ?>" required>
            </div>

            <div class="mb-3">
                <label for="no_hp" class="form-label">Nomor Telepon</label>
                <input type="text" class="form-control" id="no_hp" name="no_hp"
                       value="<?= htmlspecialchars($no_hp); ?>" required>
            </div>

            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control" id="alamat" name="alamat"><?= htmlspecialchars($alamat); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label for="konfirpw" class="form-label">Ulangi Kata Sandi</label>
                <input type="password" class="form-control" id="konfirpw" name="konfirpw" required>
            </div>

            <button type="submit" class="btn btn-register">Daftar</button>
            <a href="login.php" class="btn btn-link">Sudah punya akun? Login</a>
        </form>
    </div>
</body>
</html>
