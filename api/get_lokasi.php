<?php
header('Content-Type: application/json');
require_once("../config/db.php");

// Get filter parameter
$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

// Build query
$query = "SELECT * FROM lokasi";
if(!empty($kategori)) {
    $query .= " WHERE kategori = '$kategori'";
}
$query .= " ORDER BY id ASC";

$result = mysqli_query($conn, $query);

$locations = [];
if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $locations[] = [
            'id' => $row['id'],
            'nama_tempat' => $row['nama_tempat'],
            'latitude' => floatval($row['latitude']),
            'longitude' => floatval($row['longitude']),
            'keterangan' => $row['keterangan'],
            'kategori' => $row['kategori'],
            'url_foto' => $row['url_foto'],
            'jam_operasional' => $row['jam_operasional']
        ];
    }
}

echo json_encode([
    'success' => true,
    'count' => count($locations),
    'data' => $locations
]);
?>