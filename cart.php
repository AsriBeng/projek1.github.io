<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // simpan / hapus pilihan meja di session
    if (isset($_POST['id_meja'])) {
        if ($_POST['id_meja'] !== '') {
            $_SESSION['meja'] = (int) $_POST['id_meja'];
        } else {
            // kalau user balik ke "-- Pilih Meja --"
            unset($_SESSION['meja']);
        }
    }

    // kalau tombol Reset ditekan
    if (isset($_POST['action']) && $_POST['action'] === 'reset') {
        unset($_SESSION['cart']);
        unset($_SESSION['meja']);
        header('Location: cart.php');
        exit;
    }

    // update qty (baik untuk update biasa maupun checkout)
    if (isset($_POST['qty'])) {
        foreach ($_POST['qty'] as $id => $qty) {
            $id  = (int) $id;
            $qty = (int) $qty;

            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                if (isset($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id]['qty'] = $qty;
                }
            }
        }
    }

    // kalau action = checkout ➜ cek dulu meja
    if (isset($_POST['action']) && $_POST['action'] === 'checkout') {

        $mejaId = isset($_SESSION['meja']) ? (int)$_SESSION['meja'] : 0;

        // kalau belum pilih meja ➜ kasih error & balik ke cart
        if ($mejaId === 0) {
            $_SESSION['error_meja'] = 'Silakan pilih meja terlebih dahulu sebelum checkout.';
            header('Location: cart.php');
            exit;
        }

        // kalau sudah ada meja ➜ lanjut ke checkout
        header('Location: checkout.php');
        exit;
    }

    // selain itu (update) ➜ balik ke cart.php
    header('Location: cart.php');
    exit;
}

// ambil cart & meja terpilih
$cart         = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$mejaSelected = isset($_SESSION['meja']) ? $_SESSION['meja'] : '';

// ambil pesan error meja (kalau ada)
$errorMeja = isset($_SESSION['error_meja']) ? $_SESSION['error_meja'] : '';
unset($_SESSION['error_meja']);

// ambil daftar meja
$listMeja = [];
$sqlMeja  = "SELECT id_meja, meja FROM tb_meja ORDER BY meja ASC";
$resMeja  = mysqli_query($conn, $sqlMeja);
if ($resMeja) {
    while ($row = mysqli_fetch_assoc($resMeja)) {
        $listMeja[] = $row;
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
	<title>Omah Kopi - Pesan</title>

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
								<li><a href="menu.php">Menu</a></li>
								<li>
									<div class="header-icons">
										<a class="shopping-cart" href="user_menu.php"><i class="fas fa-user"></i></a>
                                        <a class="mobile-hide search-bar-icon" href="cart.php"><i class="fas fa-shopping-cart" style="color:#F28123"></i></a>
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

	<!-- breadcrumb-section -->
	<div class="breadcrumb-section" style="background-image: url(assets/img/hero-page.jpg);">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="breadcrumb-text">
						<p>Omah Kopi</p>
						<h1>Pesan</h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end breadcrumb section -->

	<!-- cart -->
    <form action="cart.php" method="post">
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
                                <?php
                                $grandTotal = 0;

                                if (empty($cart)) {
                                    echo '<tr><td colspan="6" class="text-center">Keranjang masih kosong.</td></tr>';
                                } else {
                                    foreach ($cart as $item) {
                                        // pastikan dihitung sebagai integer
                                        $price = (int) $item['price'];
                                        $qty   = (int) $item['qty'];

                                        $rowTotal   = $price * $qty;   // total per baris
                                        $grandTotal += $rowTotal;      // akumulasi total semua produk
                                ?>
                                    <tr class="table-body-row">
                                        <td class="product-remove">
                                            <a href="remove_from_cart.php?id=<?= $item['id']; ?>">
                                                <i class="far fa-window-close"></i>
                                            </a>
                                        </td>
                                        <td class="product-image">
                                            <img src="assets/img/products/<?= htmlspecialchars($item['image']); ?>" alt="">
                                        </td>
                                        <td class="product-name">
                                            <?= htmlspecialchars($item['name']); ?>
                                        </td>
                                        <td class="product-price">
                                            Rp <?= number_format($price, 0, ',', '.'); ?>
                                        </td>
                                        <td class="product-quantity">
                                            <input
                                                type="number"
                                                name="qty[<?= $item['id']; ?>]"
                                                min="1"
                                                value="<?= $qty; ?>"
                                                style="width:70px; text-align:center;">
                                        </td>
                                        <td class="product-total">
                                            Rp <?= number_format($rowTotal, 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                }
                                ?>
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
                                        <td><strong>Total: </strong></td>
                                        <td>
                                            Rp <?= number_format($grandTotal, 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="meja-select" style="margin:15px 0;">
                                <label for="id_meja"><strong>Pilih Meja</strong></label>
                                <select name="id_meja" id="id_meja" class="form-control" style="width:100%; max-width:250px;">
                                    <option value="">-- Pilih Meja --</option>
                                    <?php foreach ($listMeja as $m): ?>
                                        <option value="<?= $m['id_meja']; ?>"
                                            <?= ($mejaSelected == $m['id_meja']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($m['meja']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <?php if (!empty($errorMeja)): ?>
                                    <small style="color:red;"><?= htmlspecialchars($errorMeja); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="cart-buttons">
                                <!-- submit untuk update qty -->
                                <button type="submit" name="action" value="update" class="boxed-btn2">
                                    Update
                                </button>

                                <!-- submit untuk reset keranjang -->
                                <button class="boxed-btn2" type="submit" name="action" value="reset">Reset</button>

                                <!-- link biasa -->
                                <a href="menu.php" class="boxed-btn">Tambah</a>
                                <button type="submit" name="action" value="checkout" class="boxed-btn2 black">Check Out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
	<!-- end cart -->


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