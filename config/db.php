<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "eatpall";
$port = 3306; // default MySQL port

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
