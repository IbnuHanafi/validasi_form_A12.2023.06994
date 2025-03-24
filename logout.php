<?php
// Nama : Ibnu Hanafi Assalam
// NIM   : A12.2023.06994

session_start();

// Menghancurkan session untuk logout
session_unset();
session_destroy();

// Mengarahkan kembali ke halaman login setelah logout
header("Location: login.php");
exit();
