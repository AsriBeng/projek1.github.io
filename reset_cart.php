<?php
session_start();

// Hapus seluruh keranjang
unset($_SESSION['cart']);

// redirect balik ke cart.php
header("Location: cart.php");
exit;
?>
