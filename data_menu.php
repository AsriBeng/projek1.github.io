<?php
include 'config.php';
// proses hapus menu (kalau ada parameter ?hapus=)
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    // ambil data menu untuk tahu nama file gambarnya
    $sqlFoto = "SELECT foto_menu FROM tb_menu WHERE id_menu = $id";
    $resFoto = mysqli_query($conn, $sqlFoto);

    if ($resFoto && mysqli_num_rows($resFoto) > 0) {
        $rowFoto  = mysqli_fetch_assoc($resFoto);
        $fileName = trim($rowFoto['foto_menu']);
        $filePath = __DIR__ . '/assets/img/products/' . $fileName;

        // hapus dari database
        $delete = mysqli_query($conn, "DELETE FROM tb_menu WHERE id_menu = $id");

        if ($delete) {
            if (!empty($fileName) && file_exists($filePath)) {
                unlink($filePath);
            }

            header("Location: data_menu.php?msg=deleted");
            exit;
        } else {
            $errorDelete = "Gagal menghapus data: " . mysqli_error($conn);
        }
    } else {
        $errorDelete = "Data menu tidak ditemukan.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Responsive Bootstrap4 Shop Template, Created by Imran Hossain from https://imransdesign.com/">

	<!-- title -->
	<title>Omah Kopi</title>

	<!-- favicon -->
	<link rel="shortcut icon" type="image/png" href="assets/img/logoomahkopi.png">
	<!-- google font -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
	<!-- fontawesome -->
	<link rel="stylesheet" href="assets/css/all.min.css">
	<!-- bootstrap -->
	<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
	<!-- owl carousel -->
	<link rel="stylesheet" href="assets/css/owl.carousel.css">
	<!-- magnific popup -->
	<link rel="stylesheet" href="assets/css/magnific-popup.css">
	<!-- animate css -->
	<link rel="stylesheet" href="assets/css/animate.css">
	<!-- mean menu css -->
	<link rel="stylesheet" href="assets/css/meanmenu.min.css">
	<!-- main style -->
	<link rel="stylesheet" href="assets/css/main.css">
	<!-- responsive -->
	<link rel="stylesheet" href="assets/css/responsive.css">
<style>
        body {
            background: url('wooden_texture.jpg') repeat;
            color: #3C2F2F;
        }
        .menu-container {
            background-color: rgba(245, 230, 204, 0.9);
            
            border-radius: 10px;
            margin-top: 30px;
            margin-bottom: 30px;
            margin-left: 60px;
            margin-right: 60px;
        }
        .table thead {
            background-color: #4A3728;
            color: #FFF8E7;
        }
        .btn-pesan {
            background-color: #8B5E3C;
            color: #FFF8E7;
        }
    </style>
</head>
<body>
	
	<!--PreLoader-->
    <div class="loader">
        <div class="loader-inner">
            <div class="circle"></div>
        </div>
    </div>
    <!--PreLoader Ends-->
	
	<!-- header -->
	<div class="top-header-area" id="sticker">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 col-sm-12 text-center">
					<div class="main-menu-wrap">
						<!-- logo -->
						<div class="site-logo">
							<a href="index.html">
								<img src="assets/img/omahkopiicon.png" alt="">
							</a>
						</div>
						<!-- logo -->

						<!-- menu start -->
						<nav class="main-menu">
							<ul>
								<li><a href="index.php">Home</a></li>
								<li><a href="menu.php" style="color:#F28123">Menu</a></li>
								<li>
									<div class="header-icons">
										<a class="shopping-cart" href="login.php"><i class="fas fa-user"></i></a>
									</div>
								</li>
							</ul>
						</nav>
						<a class="mobile-show search-bar-icon" href="#"><i class="fas fa-search"></i></a>
						<div class="mobile-menu"></div>
						<!-- menu end -->
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end header -->

	<!-- breadcrumb section -->
	<div class="breadcrumb-section" style="background-image: url(assets/img/hero-page.jpg);">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="breadcrumb-text">
						<p>Omah Kopi</p>
						<h1>Data Menu</h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end breadcrumb section -->

	<!-- data menu -->
     <div class="menu-container">
        <a href="tambah_menu.php" style="margin:10px;"class="btn btn-pesan">Tambah Menu</a>
        <form method="post">
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Menu berhasil dihapus.</div>
        <?php endif; ?>

        <?php if (!empty($errorDelete)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorDelete); ?></div>
        <?php endif; ?>

            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // query ambil data menu + nama kategori
                    $no = 1;
                    $sql = "SELECT m.id_menu, m.nama_menu, m.foto_menu, m.harga_menu, k.kategori 
                            FROM tb_menu m
                            JOIN tb_kategori_menu k ON m.kategori_menu = k.id_kategori
                            ORDER BY k.kategori, m.nama_menu ASC";

                    $result = mysqli_query($conn, $sql);

                    // cek kalau hasil query ada datanya
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <img src="assets/img/products/<?= htmlspecialchars($row['foto_menu']); ?>" 
                                        alt="<?= htmlspecialchars($row['nama_menu']); ?>" 
                                        width="80">
                                </td>
                                <td><?= htmlspecialchars($row['nama_menu']); ?></td>
                                <td><?= htmlspecialchars($row['kategori']); ?></td>
                                <td>Rp <?= number_format($row['harga_menu'], 0, ',', '.'); ?></td>
                                <td>
                                    <a href="data_menu.php?hapus=<?= $row['id_menu']; ?>" 
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin hapus menu ini?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data menu</td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>

            </table>
        </form>
    </div>
     <!-- and data menu -->

	<!-- copyright -->
	<div class="copyright">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-12">
					<p>Copyrights &copy; 2025 - <a href="#">Omah Kopi</a>
					</p>
				</div>
				<div class="col-lg-6 text-right col-md-12">
					<div class="social-icons">
						<ul>
							<li><a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
							<li><a href="#" target="_blank"><i class="fab fa-twitter"></i></a></li>
							<li><a href="#" target="_blank"><i class="fab fa-instagram"></i></a></li>
							<li><a href="#" target="_blank"><i class="fab fa-linkedin"></i></a></li>
							<li><a href="#" target="_blank"><i class="fab fa-dribbble"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end copyright -->
	
	<!-- jquery -->
	<script src="assets/js/jquery-1.11.3.min.js"></script>
	<!-- bootstrap -->
	<script src="assets/bootstrap/js/bootstrap.min.js"></script>
	<!-- count down -->
	<script src="assets/js/jquery.countdown.js"></script>
	<!-- isotope -->
	<script src="assets/js/jquery.isotope-3.0.6.min.js"></script>
	<!-- waypoints -->
	<script src="assets/js/waypoints.js"></script>
	<!-- owl carousel -->
	<script src="assets/js/owl.carousel.min.js"></script>
	<!-- magnific popup -->
	<script src="assets/js/jquery.magnific-popup.min.js"></script>
	<!-- mean menu -->
	<script src="assets/js/jquery.meanmenu.min.js"></script>
	<!-- sticker js -->
	<script src="assets/js/sticker.js"></script>
	<!-- main js -->
	<script src="assets/js/main.js"></script>

</body>
</html>