<?php
session_start();
require_once("config/db.php");

// Ambil ID dari parameter URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query untuk mendapatkan detail lokasi
$query = "SELECT * FROM lokasi WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$location = mysqli_fetch_assoc($result);

if (!$location) {
    die("Lokasi tidak ditemukan");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail - <?php echo $location['nama_tempat']; ?> | EatPall</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .badge {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-section h3 {
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-section p {
            color: #666;
            line-height: 1.6;
        }
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .btn-back {
            background: #6c757d;
            color: white;
        }
        .btn-direction {
            background: #4caf50;
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .map-container {
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        #detailMap {
            width: 100%;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $location['nama_tempat']; ?></h1>
            <span class="badge"><?php echo $location['kategori']; ?></span>
        </div>
        
        <div class="content">
            <div class="info-section">
                <h3><i class="fas fa-map-marker-alt"></i> Alamat</h3>
                <p><?php echo $location['keterangan']; ?></p>
            </div>
            
            <div class="info-section">
                <h3><i class="fas fa-clock"></i> Jam Operasional</h3>
                <p><?php echo $location['jam_operasional']; ?></p>
            </div>
            
            <div class="info-section">
                <h3><i class="fas fa-info-circle"></i> Deskripsi</h3>
                <p><?php echo $location['keterangan']; ?></p>
            </div>
            
            <div class="info-section">
                <h3><i class="fas fa-map"></i> Lokasi di Peta</h3>
                <div class="map-container">
                    <div id="detailMap"></div>
                </div>
            </div>
            
            <div class="actions">
                <a href="index.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button class="btn btn-direction" onclick="getDirection(<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>)">
                    <i class="fas fa-directions"></i> Dapatkan Rute
                </button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize map for detail page
        const map = L.map('detailMap').setView([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add marker for this location
        L.marker([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>])
            .addTo(map)
            .bindPopup('<?php echo $location['nama_tempat']; ?>')
            .openPopup();
        
        // Get direction function
        function getDirection(lat, lng) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    window.open(`https://www.google.com/maps/dir/${userLat},${userLng}/${lat},${lng}`, '_blank');
                }, () => {
                    window.open(`https://www.google.com/maps/dir//${lat},${lng}`, '_blank');
                });
            } else {
                window.open(`https://www.google.com/maps/dir//${lat},${lng}`, '_blank');
            }
        }
    </script>
</body>
</html>