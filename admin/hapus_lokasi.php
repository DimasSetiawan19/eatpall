<?php
session_start();
include("../config/auth.php");
include("../config/db.php"); // koneksi database

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Ambil nama tempat untuk konfirmasi
    $query = "SELECT nama_tempat FROM lokasi WHERE id = $id";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nama_tempat = $row['nama_tempat'];

        // Hapus data
        $del = mysqli_query($conn, "DELETE FROM lokasi WHERE id = $id");

        if($del) {
            $_SESSION['success'] = "Lokasi \"$nama_tempat\" berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus lokasi!";
        }
    } else {
        $_SESSION['error'] = "Data lokasi tidak ditemukan!";
    }
} else {
    $_SESSION['error'] = "ID lokasi tidak valid!";
}

// Redirect kembali ke kelola lokasi
header("Location: manage_lokasi.php");
exit();
?>
