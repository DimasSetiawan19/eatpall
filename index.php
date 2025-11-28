<?php
session_start();
require_once("config/db.php");

// Ambil semua lokasi untuk peta
$query = "SELECT * FROM lokasi ORDER BY id ASC";
$result = mysqli_query($conn, $query);
$locations = [];
while($row = mysqli_fetch_assoc($result)) {
    $locations[] = $row;
}

// Hitung total langsung dari query count
$count_query = "SELECT COUNT(*) as total FROM lokasi";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_locations = $count_row['total'];

// Hitung statistik per kategori
$stats_query = "SELECT kategori, COUNT(*) as jumlah FROM lokasi GROUP BY kategori";
$stats_result = mysqli_query($conn, $stats_query);
$stats = [];
while($row = mysqli_fetch_assoc($stats_result)) {
    $stats[$row['kategori']] = $row['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EatPall - Sistem Informasi Geografis Tempat Makan Kota Palu</title>
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

        /* Navbar */
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

        .logo-img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .nav-menu {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.2);
        }

        .nav-link.active {
            background: rgba(255,255,255,0.3);
        }

        .btn-login {
            background: white;
            color: #667eea;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,255,255,0.3);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            padding: 80px 30px;
            text-align: center;
            color: white;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .search-box-hero {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .search-box-hero input {
            width: 100%;
            padding: 18px 60px 18px 25px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .search-box-hero button {
            position: absolute;
            right: 8px;
            top: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            color: white;
            cursor: pointer;
            font-weight: 600;
        }

        /* Stats Section */
        .stats-section {
            max-width: 1400px;
            margin: -50px auto 50px;
            padding: 0 30px;
            position: relative;
            z-index: 10;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .stat-info h3 {
            font-size: 32px;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 14px;
            color: #666;
        }

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px 50px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-header h2 {
            font-size: 36px;
            color: #333;
            margin-bottom: 10px;
        }

        .section-header p {
            font-size: 16px;
            color: #666;
        }

        /* Map Container */
        .map-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 50px;
        }

        .map-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .map-header h3 {
            font-size: 24px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .map-controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 16px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
        }

        #map {
            width: 100%;
            height: 600px;
            border-radius: 15px;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Location List */
        .location-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 50px;
        }

        .location-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
        }

        .location-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .location-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            color: white;
            overflow: hidden;
            position: relative;
        }

        .location-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .location-card:hover .location-image img {
            transform: scale(1.1);
        }

        .location-content {
            padding: 25px;
        }

        .location-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .location-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .location-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .location-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 14px;
            color: #666;
        }

        .location-info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .location-info-item i {
            width: 20px;
            color: #667eea;
        }

        .location-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .btn-action {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-detail {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-direction {
            background: #4caf50;
            color: white;
        }

        .btn-direction:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 30px 20px;
            margin-top: 50px;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }

        .footer-section h3 {
            margin-bottom: 20px;
            font-size: 20px;
        }

        .footer-section p {
            color: #bdc3c7;
            line-height: 1.6;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            max-width: 1400px;
            margin: 0 auto;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #bdc3c7;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 32px;
            }

            .nav-menu {
                gap: 0;
            }

            .nav-link span {
                display: none;
            }

            #map {
                height: 400px;
            }

            .location-list {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .map-controls {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <!-- Ganti dengan logo custom Anda -->
                <img src="assets/img/logo.png" alt="EatPall Logo" class="logo-img">
                <i class="fas fa-map-marked-alt" style="display: none;"></i>
                <span>EatPall</span>
            </a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="#map" class="nav-link">
                    <i class="fas fa-map"></i>
                    <span>Peta</span>
                </a>
                <a href="#lokasi" class="nav-link">
                    <i class="fas fa-list"></i>
                    <span>Daftar Lokasi</span>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <a href="admin/dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-link btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Keluar</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Temukan Tempat Makan Terbaik di Palu</h1>
            <p>Jelajahi <?php echo $total_locations; ?> lokasi rumah makan, cafe, dan restoran terpilih di Kota Palu dengan sistem peta interaktif</p>
            <div class="search-box-hero">
                <input type="text" id="heroSearch" placeholder="Cari rumah makan, cafe, atau restoran...">
                <button onclick="searchLocation()">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_locations; ?></h3>
                    <p>Total Lokasi</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo isset($stats['Rumah Makan']) ? $stats['Rumah Makan'] : 0; ?></h3>
                    <p>Rumah Makan</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #f3e5f5; color: #7b1fa2;">
                    <i class="fas fa-store"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo isset($stats['Warung Makan']) ? $stats['Warung Makan'] : 0; ?></h3>
                    <p>Warung Makan</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0; color: #f57c00;">
                    <i class="fas fa-coffee"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo isset($stats['Cafe']) ? $stats['Cafe'] : 0; ?></h3>
                    <p>Cafe</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9; color: #388e3c;">
                    <i class="fas fa-pizza-slice"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo isset($stats['Restoran']) ? $stats['Restoran'] : 0; ?></h3>
                    <p>Restoran</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Map Section -->
        <div class="map-container" id="map-section">
            <div class="map-header">
                <h3>
                    <i class="fas fa-map-marked-alt"></i>
                    Peta Interaktif Lokasi
                </h3>
                <div class="map-controls">
                    <button class="filter-btn active" onclick="filterMap('all')">
                        <i class="fas fa-globe"></i> Semua
                    </button>
                    <button class="filter-btn" onclick="filterMap('Rumah Makan')">
                        <i class="fas fa-utensils"></i> Rumah Makan
                    </button>
                    <button class="filter-btn" onclick="filterMap('Warung Makan')">
                        <i class="fas fa-store"></i> Warung Makan
                    </button>
                    <button class="filter-btn" onclick="filterMap('Cafe')">
                        <i class="fas fa-coffee"></i> Cafe
                    </button>
                    <button class="filter-btn" onclick="filterMap('Restoran')">
                        <i class="fas fa-pizza-slice"></i> Restoran
                    </button>
                </div>
            </div>
            <div id="map"></div>
        </div>

        <!-- Location List -->
        <div class="section-header" id="lokasi">
            <h2>Daftar Semua Lokasi</h2>
            <p>Temukan informasi lengkap tentang tempat makan favorit Anda</p>
        </div>

        <div class="location-list" id="locationList">
            <?php foreach($locations as $loc): 
                // Tentukan warna badge berdasarkan kategori
                $badge_bg = '#e3f2fd';
                $badge_color = '#1976d2';
                $icon = 'fa-utensils';
                
                switch($loc['kategori']) {
                    case 'Rumah Makan':
                        $badge_bg = '#e3f2fd';
                        $badge_color = '#1976d2';
                        $icon = 'fa-utensils';
                        break;
                    case 'Warung Makan':
                        $badge_bg = '#f3e5f5';
                        $badge_color = '#7b1fa2';
                        $icon = 'fa-store';
                        break;
                    case 'Cafe':
                    case 'Cafe & Resto':
                        $badge_bg = '#fff3e0';
                        $badge_color = '#f57c00';
                        $icon = 'fa-coffee';
                        break;
                    case 'Restoran':
                        $badge_bg = '#e8f5e9';
                        $badge_color = '#388e3c';
                        $icon = 'fa-pizza-slice';
                        break;
                    default:
                        $badge_bg = '#e3f2fd';
                        $badge_color = '#1976d2';
                        $icon = 'fa-utensils';
                }
                
                // Path foto dari database (foto1.jpg, foto2.jpg, dll)
                $foto_path = 'foto/' . $loc['url_foto'];
            ?>
            <div class="location-card" data-kategori="<?php echo $loc['kategori']; ?>">
                <div class="location-image">
                    <?php if(!empty($loc['url_foto']) && file_exists($foto_path)): ?>
                        <img src="<?php echo $foto_path; ?>" alt="<?php echo htmlspecialchars($loc['nama_tempat']); ?>">
                    <?php else: ?>
                        <i class="fas <?php echo $icon; ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="location-content">
                    <div class="location-header">
                        <div>
                            <div class="location-title"><?php echo htmlspecialchars($loc['nama_tempat']); ?></div>
                            <span class="location-badge" style="background: <?php echo $badge_bg; ?>; color: <?php echo $badge_color; ?>;">
                                <?php echo htmlspecialchars($loc['kategori']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="location-info">
                        <div class="location-info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo substr(htmlspecialchars($loc['keterangan']), 0, 60); ?>...</span>
                        </div>
                        <div class="location-info-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo htmlspecialchars($loc['jam_operasional']); ?></span>
                        </div>
                    </div>
                    <div class="location-actions">
                        <button class="btn-action btn-detail" onclick="showDetail(<?php echo $loc['id']; ?>)">
                            <i class="fas fa-info-circle"></i> Detail
                        </button>
                        <button class="btn-action btn-direction" onclick="getDirection(<?php echo $loc['latitude']; ?>, <?php echo $loc['longitude']; ?>)">
                            <i class="fas fa-directions"></i> Rute
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Tentang EatPall</h3>
                <p>Sistem Informasi Geografis untuk membantu Anda menemukan tempat makan terbaik di Kota Palu dengan mudah dan cepat.</p>
            </div>
            <div class="footer-section">
                <h3>Link Cepat</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-home"></i> Beranda</a></li>
                    <li><a href="#map"><i class="fas fa-map"></i> Peta</a></li>
                    <li><a href="#lokasi"><i class="fas fa-list"></i> Daftar Lokasi</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Kontak</h3>
                <ul class="footer-links">
                    <li><i class="fas fa-envelope"></i> dimassetiawan2154@gmail.com</li>
                    <li><i class="fas fa-phone"></i> +62 857-5432-4560</li>
                    <li><i class="fas fa-map-marker-alt"></i> Palu, Sulawesi Tengah</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 EatPall. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Data lokasi dari PHP
        const locations = <?php echo json_encode($locations); ?>;
        
        // Initialize map
        const map = L.map('map').setView([-0.8978, 119.8707], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        let markers = [];
        
        // Custom icons
        const icons = {
            'Rumah Makan': L.divIcon({
                html: '<div style="background: #1976d2; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);"><i class="fas fa-utensils"></i></div>',
                className: 'custom-icon',
                iconSize: [30, 30]
            }),
            'Warung Makan': L.divIcon({
                html: '<div style="background: #7b1fa2; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);"><i class="fas fa-store"></i></div>',
                className: 'custom-icon',
                iconSize: [30, 30]
            }),
            'Cafe': L.divIcon({
                html: '<div style="background: #f57c00; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);"><i class="fas fa-coffee"></i></div>',
                className: 'custom-icon',
                iconSize: [30, 30]
            }),
            'Cafe & Resto': L.divIcon({
                html: '<div style="background: #c2185b; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);"><i class="fas fa-mug-hot"></i></div>',
                className: 'custom-icon',
                iconSize: [30, 30]
            }),
            'Restoran': L.divIcon({
                html: '<div style="background: #388e3c; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);"><i class="fas fa-pizza-slice"></i></div>',
                className: 'custom-icon',
                iconSize: [30, 30]
            })
        };
        
        // Add markers
        function addMarkers(filter = 'all') {
            // Clear existing markers
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            
            locations.forEach(loc => {
                if(filter === 'all' || loc.kategori === filter) {
                    const icon = icons[loc.kategori] || icons['Rumah Makan'];
                    
                    // Buat konten popup dengan foto dari folder foto/
                    let popupContent = '<div style="padding: 10px; min-width: 200px;">';
                    
                    // Tambahkan foto jika ada
                    if(loc.url_foto && loc.url_foto.trim() !== '') {
                        const fotoPath = 'foto/' + loc.url_foto;
                        popupContent += `<img src="${fotoPath}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;" alt="${loc.nama_tempat}" onerror="this.style.display='none'">`;
                    }
                    
                    popupContent += `
                        <h3 style="margin-bottom: 10px; color: #333;">${loc.nama_tempat}</h3>
                        <p style="margin-bottom: 8px; color: #666;"><i class="fas fa-tag"></i> ${loc.kategori}</p>
                        <p style="margin-bottom: 8px; color: #666;"><i class="fas fa-clock"></i> ${loc.jam_operasional}</p>
                        <p style="margin-bottom: 12px; color: #666;"><i class="fas fa-map-marker-alt"></i> ${loc.keterangan}</p>
                        <button onclick="showDetail(${loc.id})" style="width: 100%; padding: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Lihat Detail</button>
                    </div>`;
                    
                    const marker = L.marker([loc.latitude, loc.longitude], { icon: icon })
                        .addTo(map)
                        .bindPopup(popupContent);
                    
                    markers.push(marker);
                }
            });
        }
        
        // Initial markers
        addMarkers();
        
        // Filter map
        function filterMap(kategori) {
            // Update button states
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.filter-btn').classList.add('active');
            
            // Filter markers
            addMarkers(kategori);
            
            // Filter location cards
            const cards = document.querySelectorAll('.location-card');
            cards.forEach(card => {
                if(kategori === 'all' || card.dataset.kategori === kategori) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Search function
        function searchLocation() {
            const searchTerm = document.getElementById('heroSearch').value.toLowerCase();
            const cards = document.querySelectorAll('.location-card');
            let found = false;
            
            cards.forEach(card => {
                const title = card.querySelector('.location-title').textContent.toLowerCase();
                const kategori = card.dataset.kategori.toLowerCase();
                
                if(title.includes(searchTerm) || kategori.includes(searchTerm)) {
                    card.style.display = 'block';
                    if(!found) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        found = true;
                    }
                } else {
                    card.style.display = 'none';
                }
            });
            
            if(searchTerm === '') {
                cards.forEach(card => card.style.display = 'block');
            }
        }
        
        // Show detail
        function showDetail(id) {
            window.location.href = `detail.php?id=${id}`;
        }
        
        // Get direction
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
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
        
        // Search on enter
        document.getElementById('heroSearch').addEventListener('keypress', function(e) {
            if(e.key === 'Enter') {
                searchLocation();
            }
        });
    </script>
</body>
</html>