<?php
// Nama : Ibnu Hanafi Assalam
// NIM   : A12.2023.06994

$host = 'localhost';
$user = 'root';      // Sesuaikan dengan username MySQL
$pass = '';          // Sesuaikan dengan password MySQL
$dbname = 'users_validasi_form'; // Nama database yang digunakan

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
