<?php
session_start();
include 'config.php'; // ini sudah membuat variabel $conn

// gunakan $conn sebagai koneksi
$koneksi = $conn;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // hindari warning undefined index
    $username = mysqli_real_escape_string($koneksi, $_POST['username'] ?? '');
    $password = mysqli_real_escape_string($koneksi, $_POST['password'] ?? '');

    if ($username !== '' && $password !== '') {

        $sql = "SELECT * FROM tb_pelanggan 
                WHERE username = '$username' AND password = '$password'
                LIMIT 1";

        $result = mysqli_query($koneksi, $sql);

        if ($result && mysqli_num_rows($result) === 1) {

            $user = mysqli_fetch_assoc($result);

            // simpan session
            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama'];

            header('Location: menu.php');
            exit;

        } else {
            $error = "Username atau password salah!";
        }

    } else {
        $error = "Username dan password wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Omah Kopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('wooden_texture.jpg') repeat;
            color: #3C2F2F;
        }
        .login-container {
            background-color: rgba(245, 230, 204, 0.9);
            padding: 20px;
            border-radius: 10px;
            max-width: 450px;
            margin: 50px auto;
        }
        .btn-login {
            background-color: #8B5E3C;
            color: #FFF8E7;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 style="text-align:center;">Login</h2>
        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <button type="submit" class="btn btn-login">Login</button>
                    <a href="sigin.php" class="btn btn-link">Daftar</a>
                </div>

                <a href="loginstaf.php" class="btn btn-link">Admin/Staf</a>
            </div>
        </form>
    </div>
    
</body>
</html>
