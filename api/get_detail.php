<?php
session_start();
require_once("config/db.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT * FROM lokasi WHERE id = $id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$data = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['nama_tempat']; ?> - EatPall</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
            text-decoration: none;
            font-size: 28px;
            font-weight: 700;
        }

        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .detail-header {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .title-section h1 {
            font-size: 36px;
            color: #333;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .info-content h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .info-content p {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .main-content, .sidebar {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #detailMap {
            width: 100%;
            height: 400px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-action {
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-success {
            background: #4caf50;
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .coordinates {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            font-family: monospace;
        }

        .share-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .share-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .btn-share {
            flex: 1;
            min-width: 120px;
            padding: 12px;
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-share:hover {
            transform: translateY(-2px);
        }

        .btn-whatsapp { background: #25d366; }
        .btn-facebook { background: #1877f2; }
        .btn-twitter { background: #1da1f2; }
        .btn-copy { background: #6c757d; }

        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-map-marked-alt"></i>
                <span>EatPall</span>
            </a>
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Beranda
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="detail-header">
            <div class="header-top">
                <div class="title-section">
                    <h1><?php echo $data['nama_tempat']; ?></h1>
                    <?php
                    $badge_bg = '#e3f2fd';
                    $badge_color = '#1976d2';
                    
                    if(stripos($data['kategori'], 'cafe') !== false) {
                        $badge_bg = '#fff3e0';
                        $badge_color = '#f57c00';
                    } elseif($data['kategori'] == 'Warung Makan') {
                        $badge_bg = '#f3e5f5';
                        $badge_color = '#7b1fa2';
                    } elseif($data['kategori'] == 'Restoran') {
                        $badge_bg = '#e8f5e9';
                        $badge_color = '#388e3c';
                    }
                    ?>
                    <span class="badge" style="background: <?php echo $badge_bg; ?>; color: <?php echo $badge_color; ?>;">
                        <?php echo $data['kategori']; ?>
                    </span>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h3>Jam Operasional</h3>
                        <p><?php echo $data['jam_operasional']; ?></p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Alamat</h3>
                        <p><?php echo $data['keterangan']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div class="main-content">
                <h2 class="section-title">
                    <i class="fas fa-map"></i>
                    Lokasi di Peta
                </h2>
                <div id="detailMap"></div>
                
                <div class="coordinates">
                    <strong>Koordinat:</strong><br>
                    Latitude: <?php echo $data['latitude']; ?><br>
                    Longitude: <?php echo $data['longitude']; ?>
                </div>
            </div>

            <div class="sidebar">
                <h2 class="section-title">
                    <i class="fas fa-route"></i>
                    Navigasi
                </h2>
                <div class="action-buttons">
                    <button class="btn-action btn-success" onclick="getDirection()">
                        <i class="fas fa-directions"></i>
                        Petunjuk Arah
                    </button>
                    <a href="https://www.google.com/maps?q=<?php echo $data['latitude']; ?>,<?php echo $data['longitude']; ?>" 
                       target="_blank" class="btn-action btn-primary">
                        <i class="fas fa-map-marked-alt"></i>
                        Buka di Google Maps
                    </a>
                </div>

                <div class="share-section">
                    <h2 class="section-title">
                        <i class="fas fa-share-alt"></i>
                        Bagikan
                    </h2>
                    <div class="share-buttons">
                        <button class="btn-share btn-whatsapp" onclick="shareWhatsApp()">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </button>
                        <button class="btn-share btn-facebook" onclick="shareFacebook()">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </button>
                        <button class="btn-share btn-twitter" onclick="shareTwitter()">
                            <i class="fab fa-twitter"></i> Twitter
                        </button>
                        <button class="btn-share btn-copy" onclick="copyLink()">
                            <i class="fas fa-copy"></i> Salin Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const lat = <?php echo $data['latitude']; ?>;
        const lng = <?php echo $data['longitude']; ?>;
        const nama = "<?php echo addslashes($data['nama_tempat']); ?>";
        const kategori = "<?php echo addslashes($data['kategori']); ?>";
        
        // Initialize map
        const map = L.map('detailMap').setView([lat, lng], 16);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Custom marker icon
        const customIcon = L.divIcon({
            html: '<div style="background: #667eea; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; border: 4px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.3);"><i class="fas fa-map-marker-alt"></i></div>',
            className: 'custom-icon',
            iconSize: [40, 40]
        });
        
        L.marker([lat, lng], { icon: customIcon }).addTo(map)
            .bindPopup(`<strong>${nama}</strong><br>${kategori}`).openPopup();
        
        // Get direction function
        function getDirection() {
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
        
        // Share functions
        function shareWhatsApp() {
            const text = `Check out ${nama} di EatPall: ${window.location.href}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }
        
        function shareFacebook() {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`, '_blank');
        }
        
        function shareTwitter() {
            const text = `Check out ${nama} di EatPall`;
            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(window.location.href)}`, '_blank');
        }
        
        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Link berhasil disalin!');
            });
        }
    </script>
</body>
</html>