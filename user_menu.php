<?php
session_start();
require 'config.php';

// cek apakah sudah login
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

$id_user = $_SESSION['id_user'];

$pesan_sukses = '';
$pesan_error  = '';

// === PROSES UPDATE PROFIL ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    // amankan input
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $alamat   = mysqli_real_escape_string($conn, $_POST['alamat']);
    $nohp     = mysqli_real_escape_string($conn, $_POST['nohp']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // kalau password dikosongkan, jangan diupdate
    if ($password === '') {
        $sql_update = "
            UPDATE tb_pelanggan 
               SET nama = '$nama',
                   username = '$username',
                   alamat = '$alamat',
                   no_hp = '$nohp'
             WHERE id_user = '$id_user'
             LIMIT 1
        ";
    } else {
        // NOTE: kalau nanti pakai password hash, di sini diganti password_hash()
        $sql_update = "
            UPDATE tb_pelanggan 
               SET nama = '$nama',
                   username = '$username',
                   password = '$password',
                   alamat = '$alamat',
                   no_hp = '$nohp'
             WHERE id_user = '$id_user'
             LIMIT 1
        ";
    }

    if (mysqli_query($conn, $sql_update)) {
        $pesan_sukses = "Profil berhasil diperbarui.";
    } else {
        $pesan_error = "Gagal memperbarui profil: " . mysqli_error($conn);
    }
}

// === AMBIL DATA USER LOGIN ===
$sql_user = "
    SELECT * FROM tb_pelanggan 
    WHERE id_user = '$id_user'
    LIMIT 1
";
$result_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($result_user);

// kalau user tidak ditemukan (misal data hilang)
if (!$user) {
    die("Data user tidak ditemukan.");
}
// === AMBIL DATA RESERVASI USER LOGIN ===
$sql_reservasi = "
    SELECT 
        t.id_transaksi,
        t.tgl_transaksi,
        t.total_transaksi,
        t.status,
        m.meja,
        SUM(d.qty) AS total_qty
    FROM tb_transaksi t
    JOIN tb_detail_transaksi d ON t.id_transaksi = d.id_transaksi
    JOIN tb_meja m ON t.id_meja = m.id_meja
    WHERE t.id_user = '$id_user'
    GROUP BY 
        t.id_transaksi,
        t.tgl_transaksi,
        t.total_transaksi,
        t.status,
        m.meja
    ORDER BY t.tgl_transaksi DESC
";

$result_reservasi = mysqli_query($conn, $sql_reservasi);

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Responsive Bootstrap4 Shop Template, Created by Imran Hossain from https://imransdesign.com/">

	<!-- title -->
	<title>Omah Kopi - User</title>

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
										<a class="shopping-cart" href="login.php"><i class="fas fa-user"  style="color:#F28123"></i></a>
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

	<!-- breadcrumb-section -->
	<div class="breadcrumb-section" style="background-image: url(assets/img/hero-page.jpg);">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 text-center">
					<div class="breadcrumb-text">
						<p>Omah Kopi</p>
						<h1>User Menu</h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end breadcrumb section -->


    <!-- fitur user -->
    <section class="mt-150 mb-150">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">

                    <!-- Nav tab Profil & Reservasi -->
                    <ul class="nav nav-tabs" id="userTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="profil-tab" data-toggle="tab" href="#profil" role="tab" aria-controls="profil" aria-selected="true">
                                Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="reservasi-tab" data-toggle="tab" href="#reservasi" role="tab" aria-controls="reservasi" aria-selected="false">
                                Reservasi
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="userTabContent" style="padding: 30px 20px; border: 1px solid #eee; border-top: none;">
                        <!-- TAB PROFIL -->
						<div class="tab-pane fade show active" id="profil" role="tabpanel" aria-labelledby="profil-tab">
							<div class="row">
								<div class="col-lg-8 col-md-10">
									<h3 class="mb-4">Profil Saya</h3>

									<!-- tampilkan pesan sukses / error -->
									<?php if (!empty($pesan_sukses)): ?>
										<div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
									<?php endif; ?>

									<?php if (!empty($pesan_error)): ?>
										<div class="alert alert-danger"><?php echo $pesan_error; ?></div>
									<?php endif; ?>

									<form method="post" id="formProfil">
										<div class="form-group">
											<label for="nama">Nama</label>
											<input type="text" 
												class="form-control" 
												id="nama" 
												name="nama"
												value="<?php echo htmlspecialchars($user['nama']); ?>" 
												disabled>
										</div>

										<div class="form-group">
											<label for="username">Username</label>
											<input type="text" 
												class="form-control" 
												id="username" 
												name="username"
												value="<?php echo htmlspecialchars($user['username']); ?>" 
												disabled>
										</div>

										<div class="form-group">
											<label for="password">Password</label>
											<div class="input-group">
												<input type="password" 
													class="form-control" 
													id="password" 
													name="password"
													value="<?php echo htmlspecialchars($user['password']); ?>"
													disabled>

												<div class="input-group-append">
													<span class="input-group-text" id="togglePassword" style="cursor:pointer;">
														<i class="fas fa-eye"></i>
													</span>
												</div>
											</div>
											<small class="form-text text-muted">
												Kosongkan jika tidak ingin mengubah password.
											</small>
										</div>


										<div class="form-group">
											<label for="alamat">Alamat</label>
											<textarea class="form-control" 
													id="alamat" 
													name="alamat"
													rows="3" 
													disabled><?php echo htmlspecialchars($user['alamat']); ?></textarea>
										</div>

										<div class="form-group">
											<label for="nohp">Nomor HP</label>
											<input type="text" 
												class="form-control" 
												id="nohp" 
												name="nohp"
												value="<?php echo htmlspecialchars($user['no_hp']); ?>" 
												disabled>
										</div>

										<div class="d-flex justify-content-between align-items-center mt-4">
											<!-- Tombol Ubah Profil (aktifkan edit) -->
											<button type="button" 
													class="btn btn-secondary" 
													id="btnEditProfil">
												Ubah Profil
											</button>

											<!-- Tombol Simpan (submit form) -->
											<button type="submit" 
													class="btn" 
													id="btnSimpanProfil"
													name="update_profil"
													style="background-color:#F28123; color:#fff; display:none;">
												Simpan Perubahan
											</button>

											<a href="logout.php" class="btn" style="background-color:#dc3545; color:#fff;">Log Out</a>
										</div>
									</form>
								</div>
							</div>
						</div>


                        <!-- TAB RESERVASI -->
                        <div class="tab-pane fade" id="reservasi" role="tabpanel" aria-labelledby="reservasi-tab">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h3 class="mb-4">Daftar Reservasi Saya</h3>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Meja</th>
                                                    <th>Tanggal</th>
                                                    <th>Total</th>
													<th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
											<?php
											$no = 1;
											if ($result_reservasi && mysqli_num_rows($result_reservasi) > 0) {
												while ($row = mysqli_fetch_assoc($result_reservasi)) {
													?>
													<tr>
														<td><?= $no++; ?></td>
														<td><?= htmlspecialchars($row['meja']); ?></td>
														<td><?= htmlspecialchars($row['tgl_transaksi']); ?></td>
														<td>Rp <?= number_format($row['total_transaksi'], 0, ',', '.'); ?></td>
														<td><?= htmlspecialchars($row['status']); ?></td>
														<td>
															<button type="button" class="btn btn-sm btn-primary btn-detail-reservasi" data-id="<?= $row['id_transaksi']; ?>" data-toggle="modal" data-target="#detailReservasiModal">
																Detail
															</button>
														</td>
													</tr>
													<?php
												}
											} else {
												?>
												<tr>
													<td colspan="6" class="text-center">Belum ada reservasi.</td>
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

                    </div> <!-- end tab-content -->
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Detail Reservasi -->
    <!-- Modal Detail Reservasi -->
<div class="modal fade" id="detailReservasiModal" tabindex="-1" role="dialog" aria-labelledby="detailReservasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailReservasiLabel">Detail Reservasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Info singkat reservasi -->
                <div class="mb-3" id="detail-info-reservasi">
                    <!-- akan diganti via AJAX -->
                    <p class="mb-1"><strong>Meja:</strong> -</p>
                    <p class="mb-1"><strong>Tanggal:</strong> -</p>
                    <p class="mb-1"><strong>Total:</strong> -</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Menu</th>
                                <th>Harga</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody id="detail-tbody-reservasi">
                            <!-- akan diganti via AJAX -->
                            <tr>
                                <td colspan="3" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="printReservasi()">Cetak Bukti</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

    <!-- end fitur user -->

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
	// tabel transaksi
	// bisa kamu sesuaikan dengan data dari PHP / session
	var namaPelanggan = "<?php echo isset($_SESSION['nama_pelanggan']) ? $_SESSION['nama_pelanggan'] : 'Nama Pelanggan'; ?>";
	var noHpPelanggan = "<?php echo isset($_SESSION['no_hp']) ? $_SESSION['no_hp'] : '-'; ?>";

	function printReservasi() {
		var modal = document.getElementById('detailReservasiModal');

		// ambil info singkat dari <p> di modal-body
		var infoPs = modal.querySelectorAll('.modal-body .mb-3 p');
		var meja = '';
		var tanggal = '';
		var total = '';

		infoPs.forEach(function(p) {
			var text = p.innerText || '';
			if (text.indexOf('Meja:') !== -1) {
				meja = text.split(':')[1].trim();
			} else if (text.indexOf('Tanggal:') !== -1) {
				tanggal = text.split(':')[1].trim();
			} else if (text.indexOf('Total:') !== -1) {
				total = text.split(':')[1].trim();
			}
		});

		// ambil data tabel menu dari modal
		var rows = modal.querySelectorAll('.modal-body table tbody tr');
		var rowsHtml = '';

		rows.forEach(function(tr) {
			var tds = tr.querySelectorAll('td');
			var menu  = tds[0] ? tds[0].innerText : '';
			var harga = tds[1] ? tds[1].innerText : '';
			var qty   = tds[2] ? tds[2].innerText : '';

			// di sini subtotal = harga (kalau mau bisa dihitung sendiri)
			var subtotal = harga;

			rowsHtml += `
				<tr>
					<td>${menu}</td>
					<td>${harga}</td>
					<td>${qty}</td>
					<td>${subtotal}</td>
				</tr>
			`;
		});

		var newWindow = window.open('', '', 'width=800,height=600');

		newWindow.document.write(`
			<html>
			<head>
				<title>Bukti Reservasi</title>
				<style>
					body {
						font-family: Arial, sans-serif;
						padding: 20px;
						font-size: 13px;
						color: #333;
					}
					h2 {
						text-align: center;
						margin: 0;
						font-size: 22px;
					}
					h4 {
						text-align: center;
						margin: 5px 0 25px 0;
						font-weight: normal;
						font-size: 14px;
					}
					hr {
						border: 0;
						border-top: 1px solid #ccc;
						margin: 15px 0;
					}
					table {
						width: 100%;
						border-collapse: collapse;
						margin-top: 10px;
					}
					table, th, td {
						border: 1px solid #ccc;
					}
					th, td {
						padding: 6px 8px;
						text-align: left;
						font-size: 13px;
					}
					.text-right { text-align: right; }
					.font-weight-bold { font-weight: bold; }
					.mt-20 { margin-top: 20px; }
				</style>
			</head>
			<body>
				<h2>Omah Kopi</h2>
				<h4>Struk Reservasi &amp; Pemesanan Menu</h4>

				<p><strong>Nama Pelanggan:</strong> ${namaPelanggan}</p>
				<p><strong>No HP:</strong> ${noHpPelanggan}</p>
				<p><strong>Meja:</strong> ${meja}</p>
				<p><strong>Tanggal:</strong> ${tanggal}</p>

				<hr>

				<table>
					<thead>
						<tr>
							<th>Menu</th>
							<th>Harga</th>
							<th>Qty</th>
							<th>Subtotal</th>
						</tr>
					</thead>
					<tbody>
						${rowsHtml}
					</tbody>
				</table>

				<p class="text-right font-weight-bold mt-20">Total: ${total}</p>

				<p class="mt-20">Terima kasih telah berkunjung ke Omah Kopi.</p>
			</body>
			</html>
		`);

		newWindow.document.close();
		newWindow.focus();
		newWindow.print();
		newWindow.close();
	}
	</script>
	<script>
	// profil
	document.addEventListener('DOMContentLoaded', function () {
		var btnEdit   = document.getElementById('btnEditProfil');
		var btnSimpan = document.getElementById('btnSimpanProfil');
		var form      = document.getElementById('formProfil');

		if (btnEdit && btnSimpan && form) {
			btnEdit.addEventListener('click', function () {
				// enable semua input & textarea di form
				var fields = form.querySelectorAll('input, textarea');
				fields.forEach(function (el) {
					el.disabled = false;
				});

				// password tetap kosong saat mulai edit
				var passwordField = document.getElementById('password');
				if (passwordField) {
					passwordField.value = '';
				}

				// ganti tombol
				btnEdit.style.display   = 'none';
				btnSimpan.style.display = 'inline-block';
			});
		}
	});
	</script>
	<script>
	// hide/sow pw
	document.addEventListener('DOMContentLoaded', function () {

		// === SHOW / HIDE PASSWORD ===
		const passwordInput = document.getElementById('password');
		const togglePassword = document.getElementById('togglePassword');

		togglePassword.addEventListener('click', function () {
			const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
			passwordInput.setAttribute('type', type);

			// ganti icon
			this.querySelector('i').classList.toggle('fa-eye');
			this.querySelector('i').classList.toggle('fa-eye-slash');
		});

		// === AKTIFKAN MODE EDIT ===
		var btnEdit   = document.getElementById('btnEditProfil');
		var btnSimpan = document.getElementById('btnSimpanProfil');
		var form      = document.getElementById('formProfil');

		btnEdit.addEventListener('click', function () {
			const fields = form.querySelectorAll('input, textarea');
			fields.forEach(el => el.disabled = false);

			// password saat mulai edit harus kosong (supaya user isi baru)
			passwordInput.value = "";

			btnEdit.style.display = "none";
			btnSimpan.style.display = "inline-block";
		});

	});
	</script>
	<script>
	// json detail reservasi
	$(document).ready(function() {
		$('.btn-detail-reservasi').on('click', function () {
			var id = $(this).data('id');

			// placeholder saat loading
			$('#detail-info-reservasi').html('<p class="mb-1">Memuat...</p>');
			$('#detail-tbody-reservasi').html(
				'<tr><td colspan="3" class="text-center">Memuat data...</td></tr>'
			);

			$.get('get_detail_reservasi.php', { id_transaksi: id }, function (res) {
				try {
					var data = JSON.parse(res);

					if (data.error) {
						$('#detail-info-reservasi').html('<p class="mb-1 text-danger">'+data.error+'</p>');
						$('#detail-tbody-reservasi').html(
							'<tr><td colspan="3" class="text-center">-</td></tr>'
						);
						return;
					}

					$('#detail-info-reservasi').html(data.info);
					$('#detail-tbody-reservasi').html(data.rows);
				} catch (e) {
					$('#detail-info-reservasi').html('<p class="mb-1 text-danger">Gagal memuat data.</p>');
					$('#detail-tbody-reservasi').html(
						'<tr><td colspan="3" class="text-center">Terjadi kesalahan.</td></tr>'
					);
				}
			});
		});
	});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const hash = window.location.hash;

    if (hash === "#reservasi") {
        // Aktifkan Tab
        const tabTrigger = document.querySelector('#reservasi-tab');
        if (tabTrigger) {
            new bootstrap.Tab(tabTrigger).show();
        }

        // Scroll otomatis setelah tab benar-benar aktif
        setTimeout(() => {
            const reservasiSection = document.querySelector('#reservasi');
            if (reservasiSection) {
                reservasiSection.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }
        }, 300); // delay agar tab benar-benar sudah tampil
    }
});
</script>



</body>
</html>