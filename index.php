<?php
session_start();
include 'config.php';
if (isset($_GET['reset_cart'])) {
    unset($_SESSION['cart']);
	unset($_SESSION['meja']);
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
								<li><a href="index.php" style="color:#F28123">Home</a></li>
								<li><a href="menu.php">Menu</a></li>
								<li>
									<div class="header-icons">
										<a class="shopping-cart" href="user_menu.php"><i class="fas fa-user"></i></a>
										<a class="mobile-hide search-bar-icon" href="cart.php"><i class="fas fa-shopping-cart"></i></a>
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
	


	<!-- hero area -->
	<div class="hero-area" style="background-image: url(assets/img/hero-page.jpg); background-size: cover;background-position: center; background-attachment: fixed;">
		<div class="container">
			<div class="row">
				<div class="col-lg-9 offset-lg-2 text-center">
					<div class="hero-text">
						<div class="hero-text-tablecell">
							<p class="subtitle">Omah Kopi</p>
							<h1>Rumahnya Kopi Sejati</h1>
							<div class="hero-btns">
								<a href="menu.php" class="bordered-btn">Pesan Sekarang</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end hero area -->

	<!-- features list section -->
	<div class="list-section pt-80 pb-80">
		<div class="container">

			<div class="row text-center">
				<div class="col-lg-3 col-md-6 mb-4">
					<div class="list-box d-flex align-items-center">
						<div class="list-icon"><i class="fas fa-user"></i></div>
						<div class="content">
							<h3>Daftar/Login</h3>
							<p>Ingat akunmu ya</p>
						</div>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 mb-4">
					<div class="list-box d-flex align-items-center">
						<div class="list-icon"><i class="fas fa-shopping-cart"></i></div>
						<div class="content">
							<h3>Pesan</h3>
							<p>Pilih menu favorit mu</p>
						</div>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 mb-4">
					<div class="list-box d-flex align-items-center">
						<div class="list-icon"><i class="fas fa-chair"></i></div>
						<div class="content">
							<h3>Tunggu</h3>
							<p>Santai dulu gak sih</p>
						</div>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 mb-4">
					<div class="list-box d-flex align-items-center">
						<div class="list-icon"><i class="fas fa-coffee"></i></div>
						<div class="content">
							<h3>Nikmati</h3>
							<p>Tambah Enjoy nih</p>
						</div>
					</div>
				</div>
			</div>


		</div>
	</div>
	<!-- end features list section -->

	<!-- product section -->
	<div class="product-section mt-150 mb-150">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="section-title">	
						<h3><span class="orange-text">Menu</span> Omah Kopi</h3>
						<p>Setiap menu kami buat dengan sepenuh hati untuk menjaga rasa yang asri.</p>
					</div>
				</div>
			</div>

			<?php
// Ambil 9 menu terbaru
$sqlMenu = "SELECT id_menu, nama_menu, foto_menu, harga_menu 
            FROM tb_menu 
            ORDER BY id_menu DESC 
            LIMIT 9";
$resMenu = mysqli_query($conn, $sqlMenu);
?>

<div class="row product-lists">
    <?php if ($resMenu && mysqli_num_rows($resMenu) > 0) { ?>
        <?php while ($row = mysqli_fetch_assoc($resMenu)) { ?>
            
            <div class="col-lg-4 col-md-6 text-center">
                <div class="single-product-item">
                    <div class="product-image">
                        <img src="assets/img/products/<?= htmlspecialchars($row['foto_menu']); ?>"
                             alt="<?= htmlspecialchars($row['nama_menu']); ?>">
                    </div>

                    <h3><?= htmlspecialchars($row['nama_menu']); ?></h3>

                    <p class="product-price">
                        Rp <?= number_format($row['harga_menu'], 0, ',', '.'); ?>
                    </p>

                    <!-- Tidak ada Add to Cart di index -->
                </div>
            </div>

        <?php } ?>
    <?php } else { ?>
        <div class="col-12 text-center">
            <p>Belum ada menu yang tersedia.</p>
        </div>
    <?php } ?>
</div>

		</div>
	</div>
	<!-- end product section -->


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