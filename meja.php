<?php
session_start();
require 'config.php'; // pastikan ada $conn

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$error = '';

// HANDLE HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];

    if ($id_hapus > 0) {
        $sqlDel = "DELETE FROM tb_meja WHERE id_meja = $id_hapus";
        if (mysqli_query($conn, $sqlDel)) {
            header("Location: meja.php?msg=deleted");
            exit;
        } else {
            $error = "Gagal menghapus meja: " . mysqli_error($conn);
        }
    }
}

// HANDLE TAMBAH / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi    = $_POST['aksi'] ?? '';
    $meja    = trim($_POST['meja'] ?? '');
    $id_edit = (int) ($_POST['id_meja'] ?? 0);

    if ($meja === '') {
        $error = "Nama/nomor meja wajib diisi.";
    } else {
        if ($aksi === 'tambah') {
            $stmt = mysqli_prepare($conn, "INSERT INTO tb_meja (meja) VALUES (?)");
            mysqli_stmt_bind_param($stmt, "s", $meja);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: meja.php?msg=added");
                exit;
            } else {
                $error = "Gagal menambah meja: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);

        } elseif ($aksi === 'update' && $id_edit > 0) {

            $stmt = mysqli_prepare($conn, "UPDATE tb_meja SET meja = ? WHERE id_meja = ?");
            mysqli_stmt_bind_param($stmt, "si", $meja, $id_edit);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: meja.php?msg=updated");
                exit;
            } else {
                $error = "Gagal mengupdate meja: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        }
    }
}

// CEK MODE EDIT UNTUK FORM KANAN
$isEdit    = false;
$editMeja  = null;

if (isset($_GET['edit'])) {
    $id_edit_get = (int) $_GET['edit'];

    if ($id_edit_get > 0) {
        $sqlEdit = "SELECT * FROM tb_meja WHERE id_meja = $id_edit_get LIMIT 1";
        $resEdit = mysqli_query($conn, $sqlEdit);

        if ($resEdit && mysqli_num_rows($resEdit) === 1) {
            $editMeja = mysqli_fetch_assoc($resEdit);
            $isEdit   = true;
        }
    }
}

// AMBIL DATA MEJA UNTUK CARD KIRI
$sqlList = "SELECT * FROM tb_meja ORDER BY id_meja ASC";
$result  = mysqli_query($conn, $sqlList);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Meja - Omah Kopi</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background-color: #f5f5f5;
        }
        .wrapper-admin {
            display: flex;
            min-height: 100vh;
        }
        /* SIDEBAR */
        .sidebar {
            width: 220px;
            background-color: #231c17;
            color: #fff;
            padding: 20px 0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            transition: transform 0.3s ease;
        }
        .sidebar .brand {
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar .brand span {
            color: #f2a154;
        }
        .sidebar .menu {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .sidebar .menu li {
            margin-bottom: 5px;
        }
        .sidebar .menu a {
            display: block;
            padding: 10px 20px;
            color: #f1f1f1;
            text-decoration: none;
            font-size: 15px;
        }
        .sidebar .menu a i {
            margin-right: 8px;
        }
        .sidebar .menu a:hover,
        .sidebar .menu a.active {
            background-color: #3a2f26;
        }

        /* KONTEN UTAMA */
        .main-content {
            margin-left: 220px;
            padding: 20px;
            width: 100%;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .topbar h2 {
            margin: 0;
        }
        .topbar .user-info {
            font-size: 14px;
        }
        .card {
            border-radius: 10px;
        }

        /* tombol toggle sidebar (mobile) */
        .btn-toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            margin-right: 10px;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .wrapper-admin {
                flex-direction: column;
            }
            .sidebar {
                position: fixed;
                z-index: 999;
                height: 100%;
                transform: translateX(-220px); /* default: tertutup */
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .topbar {
                justify-content: space-between;
            }
            .btn-toggle-sidebar {
                display: block;
            }
            .topbar h2 {
                font-size: 18px;
            }
            .topbar .user-info {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

<div class="wrapper-admin">
    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="brand">
            Omah <span>Kopi</span>
        </div>
        <ul class="menu">
            <li><a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
            <li><a href="menuadmin.php" class="active">
                <i class="fas fa-calendar-check"></i> Reservasi
            </a></li>
            <li><a href="menues.php">
                <i class="fas fa-coffee"></i> Menu
            </a></li>
            <li><a href="laporan.php">
                <i class="fas fa-file-alt"></i> Laporan
            </a></li>
            <li><a href="users.php">
                <i class="fas fa-users"></i> Users
            </a></li>
            <li><a href="staf_user.php"><i class="fas fa-user-shield"></i> Staf / Admin</a></li>
        </ul>
    </nav>

    <!-- KONTEN -->
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center">
                <button class="btn-toggle-sidebar" id="btnToggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>Daftar Meja</h2>
            </div>
            <div class="user-info">
                Admin Panel
            </div>
        </div>

        <!-- Notifikasi -->
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'added'): ?>
                <div class="alert alert-success">Meja berhasil ditambahkan.</div>
            <?php elseif ($_GET['msg'] === 'updated'): ?>
                <div class="alert alert-success">Meja berhasil diupdate.</div>
            <?php elseif ($_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-success">Meja berhasil dihapus.</div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- CARD KIRI: TABEL MEJA -->
            <div class="col-md-7 mb-3">
                <div class="card">
                    <div class="card-header">
                        <strong>Daftar Meja</strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm align-middle">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width:60px;">No</th>
                                        <th>Meja</th>
                                        <th style="width:160px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($result && mysqli_num_rows($result) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['meja']); ?></td>
                                            <td>
                                                <a href="meja.php?edit=<?= $row['id_meja']; ?>"
                                                   class="btn btn-sm btn-warning mb-1">
                                                    <i class="fas fa-edit"></i> Update
                                                </a>

                                                <a href="meja.php?hapus=<?= $row['id_meja']; ?>"
                                                   class="btn btn-sm btn-danger mb-1"
                                                   onclick="return confirm('Yakin hapus meja ini?');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Belum ada data meja.</td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD KANAN: FORM TAMBAH / UPDATE -->
            <div class="col-md-5 mb-3">
                <div class="card">
                    <div class="card-header">
                        <strong><?= $isEdit ? 'Update Meja' : 'Tambah Meja'; ?></strong>
                    </div>
                    <div class="card-body">
                        <form action="meja.php<?= $isEdit && $editMeja ? '?edit='.(int)$editMeja['id_meja'] : ''; ?>" method="post">
                            <input type="hidden" name="aksi" value="<?= $isEdit ? 'update' : 'tambah'; ?>">

                            <?php if ($isEdit && $editMeja): ?>
                                <input type="hidden" name="id_meja" value="<?= (int)$editMeja['id_meja']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="meja" class="form-label">Nama / Nomor Meja</label>
                                <input type="text"
                                       name="meja"
                                       id="meja"
                                       class="form-control"
                                       placeholder="Contoh: Meja 01, Meja Lesehan, dll"
                                       value="<?= $isEdit && $editMeja ? htmlspecialchars($editMeja['meja']) : ''; ?>"
                                       required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <?= $isEdit ? 'Update Meja' : 'Simpan Meja'; ?>
                            </button>

                            <?php if ($isEdit): ?>
                                <a href="meja.php" class="btn btn-secondary ms-2">Batal</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- end konten -->
</div>

<!-- JS -->
<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script>
    // toggle sidebar mobile
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar   = document.querySelector('.sidebar');

    if (btnToggle) {
        btnToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });
    }
</script>

</body>
</html>
