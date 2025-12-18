<?php
session_start();
include 'config.php';


if (!isset($_SESSION['id_user'])) {
    // jika belum login, kembali ke login
    header("Location: login.php");
    exit;
}
// ambil semua kategori
$sqlKat = "SELECT id_kategori, kategori FROM tb_kategori_menu ORDER BY kategori ASC";
$resKat = mysqli_query($conn, $sqlKat);

// ambil semua menu, urut dari id_menu terkecil
$sqlMenu = "SELECT m.id_menu, m.nama_menu, m.foto_menu, m.harga_menu, k.id_kategori
            FROM tb_menu m
            JOIN tb_kategori_menu k ON m.kategori_menu = k.id_kategori
            ORDER BY m.id_menu ASC";
$resMenu = mysqli_query($conn, $sqlMenu);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Responsive Bootstrap4 Shop Template, Created by Imran Hossain from https://imransdesign.com/">

	<!-- title -->
	<title>Omah Kopi - Menu</title>

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
								<li><a href="index.php">Home</a></li>
								<li><a href="menu.php" style="color:#F28123">Menu</a></li>
								<li>
									<div class="header-icons">
										<a class="mobile-hide shopping-cart" href="user_menu.php"><i class="fas fa-user"></i></a>
                                        <a class="mobile-hide search-bar-icon" href="cart.php"><i class="fas fa-shopping-cart"></i></a>
									</div>
								</li>
							</ul>
						</nav>
						<a class="mobile-show search-bar-icon" href="#"><i class="fas fa-shopping-cart"></i></a>
						<div class="mobile-menu"></div>
						<!-- menu end -->
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end header -->

    <!-- cart area -->
	<div class="search-area">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<span class="btn close-btn btn-danger">Close</span>
                    <!-- cart -->
                    <div class="cart-section mt-150 mb-150">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-8 col-md-12">
                                    <div class="cart-table-wrap">
                                        <table class="cart-table">
                                            <thead class="cart-table-head">
                                                <tr class="table-head-row">
                                                    <th class="product-remove"></th>
                                                    <th class="product-image">Product Image</th>
                                                    <th class="product-name">Name</th>
                                                    <th class="product-price">Price</th>
                                                    <th class="product-quantity">Quantity</th>
                                                    <th class="product-total">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="table-body-row">
                                                    <td class="product-remove"><a href="#"><i class="far fa-window-close"></i></a></td>
                                                    <td class="product-image"><img src="assets/img/products/product-img-1.jpg" alt=""></td>
                                                    <td class="product-name">Strawberry</td>
                                                    <td class="product-price">$85</td>
                                                    <td class="product-quantity"><input type="number" placeholder="0"></td>
                                                    <td class="product-total">1</td>
                                                </tr>
                                                <tr class="table-body-row">
                                                    <td class="product-remove"><a href="#"><i class="far fa-window-close"></i></a></td>
                                                    <td class="product-image"><img src="assets/img/products/product-img-2.jpg" alt=""></td>
                                                    <td class="product-name">Berry</td>
                                                    <td class="product-price">$70</td>
                                                    <td class="product-quantity"><input type="number" placeholder="0"></td>
                                                    <td class="product-total">1</td>
                                                </tr>
                                                <tr class="table-body-row">
                                                    <td class="product-remove"><a href="#"><i class="far fa-window-close"></i></a></td>
                                                    <td class="product-image"><img src="assets/img/products/product-img-3.jpg" alt=""></td>
                                                    <td class="product-name">Lemon</td>
                                                    <td class="product-price">$35</td>
                                                    <td class="product-quantity"><input type="number" placeholder="0"></td>
                                                    <td class="product-total">1</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="total-section">
                                        <table class="total-table">
                                            <thead class="total-table-head">
                                                <tr class="table-total-row">
                                                    <th>Total</th>
                                                    <th>Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="total-data">
                                                    <td><strong>Subtotal: </strong></td>
                                                    <td>$500</td>
                                                </tr>
                                                <tr class="total-data">
                                                    <td><strong>Shipping: </strong></td>
                                                    <td>$45</td>
                                                </tr>
                                                <tr class="total-data">
                                                    <td><strong>Total: </strong></td>
                                                    <td>$545</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="cart-buttons">
                                            <a href="cart.html" class="boxed-btn">Update Cart</a>
                                            <a href="checkout.html" class="boxed-btn black">Check Out</a>
                                        </div>
                                    </div>

                                    <div class="coupon-section">
                                        <h3>Apply Coupon</h3>
                                        <div class="coupon-form-wrap">
                                            <form action="index.html">
                                                <p><input type="text" placeholder="Coupon"></p>
                                                <p><input type="submit" value="Apply"></p>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end cart -->
				</div>
			</div>
		</div>
	</div>
	<!-- end cart area -->

	<!-- breadcrumb-section -->
	<div class="breadcrumb-section" style="background-image: url(assets/img/hero-page.jpg);">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="breadcrumb-text">
						<p>Omah Kopi</p>
						<h1>Menu</h1>
                        <div class="hero-btns">
                            <a href="user_menu.php#reservasi" class="bordered-btn">Reservasi</a>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end breadcrumb section -->

    <!-- products -->
    <div class="product-section mt-150 mb-150">
        <div class="container">

            <!-- FILTER KATEGORI -->
            <div class="row">
                <div class="col-md-12">
                    <div class="product-filters">
                        <ul>
                            <li class="active" data-filter="*">All</li>
                            <?php if ($resKat && mysqli_num_rows($resKat) > 0) { ?>
                                <?php while ($kat = mysqli_fetch_assoc($resKat)) { ?>
                                    <li data-filter=".kat-<?= $kat['id_kategori']; ?>">
                                        <?= htmlspecialchars($kat['kategori']); ?>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- LIST PRODUK -->
            <div class="row product-lists">
                <?php
                if ($resMenu && mysqli_num_rows($resMenu) > 0) {
                    while ($row = mysqli_fetch_assoc($resMenu)) {
                ?>
                    <div class="col-lg-4 col-md-6 text-center kat-<?= $row['id_kategori']; ?>">
                        <div class="single-product-item">
                            <div class="product-image">
                                <img src="assets/img/products/<?= htmlspecialchars($row['foto_menu']); ?>" 
                                    alt="<?= htmlspecialchars($row['nama_menu']); ?>">
                            </div>
                            <h3><?= htmlspecialchars($row['nama_menu']); ?></h3>
                            <p class="product-price">
                                Rp <?= number_format($row['harga_menu'], 0, ',', '.'); ?>
                            </p>
                            <a href="#" class="cart-btn add-to-cart" data-id="<?= $row['id_menu']; ?>">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </a>



                        </div>
                    </div>
                <?php
                    }
                } else {
                ?>
                    <div class="col-12 text-center">
                        <p>Belum ada menu yang tersedia.</p>
                    </div>
                <?php } ?>
            </div>

            <!-- pagination dihapus, kalau mau bisa ditambah lagi nanti -->
        </div>
    </div>
    <!-- end products -->


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
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.add-to-cart');

        buttons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                const id = this.dataset.id;
                const formData = new FormData();
                formData.append('id', id);

                fetch('add_to_cart.php', {   // ganti path kalau perlu
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    console.log('RAW RESPONSE:', text); // cek di console

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        alert('Respon server tidak valid:\n\n' + text);
                        return;
                    }

                    if (data.success) {
                        alert('Produk berhasil dimasukkan ke keranjang');

                        const badge = document.querySelector('.cart-count');
                        if (badge && typeof data.total_items !== 'undefined') {
                            badge.textContent = data.total_items;
                        }
                    } else {
                        alert(data.message || 'Gagal menambahkan ke keranjang');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Fetch gagal:\n' + err);
                });
            });
        });
    });
    </script>
</body>
</html>