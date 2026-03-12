<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "harst"; // Pastikan ini sama dengan nama database di phpMyAdmin kamu

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>