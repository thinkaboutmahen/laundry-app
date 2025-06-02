<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Fetch services from database
try {
    $stmt = $pdo->query("SELECT * FROM paket_laundry ORDER BY jenis_laundry");
    $services = $stmt->fetchAll();
    
    // Debug: Check if we got any services
    if (empty($services)) {
        echo "<!-- No services found in database -->";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelayanan - Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4169e1;
            --secondary-color: #0056b3;
            --accent-color: #28a745;
            --text-color: #333;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            background-color: var(--light-bg);
        }

        .navbar-custom {
            background-color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: var(--primary-color) !important;
            font-weight: 500;
        }

        .navbar-custom .nav-link:hover {
            color: var(--secondary-color) !important;
        }

        .navbar-custom .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .navbar-custom .navbar-brand img {
            margin-right: 10px;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/Laundryroom.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            padding: 120px 0 60px;
            margin-bottom: 60px;
            text-align: center;
            position: relative;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .hero-section .container {
            position: relative;
            z-index: 2;
        }

        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-section p {
            font-size: 1.3rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .services-section {
            padding: 60px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            color: var(--primary-color);
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .section-title p {
            color: #666;
            font-size: 1.1rem;
        }

        .service-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,.05);
            padding: 30px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,.1);
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .service-icon {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.1);
        }

        .service-card h3 {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .service-price {
            font-size: 1.8rem;
            color: var(--accent-color);
            font-weight: 700;
            margin: 20px 0;
            padding: 10px;
            background-color: rgba(40, 167, 69, 0.1);
            border-radius: 8px;
            display: inline-block;
        }

        .service-description {
            color: #666;
            margin-bottom: 0;
            line-height: 1.6;
            font-size: 1.1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .features-section {
            padding: 60px 0;
            background-color: white;
        }

        .feature-item {
            text-align: center;
            padding: 30px;
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .feature-description {
            color: #666;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 100px 0 40px;
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .service-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Custom Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/logoinvoice.png" alt="Laundry Logo" width="50" height="50" class="d-inline-block align-text-top me-2">
                Laundry
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tentang-kami.php">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="pelayanan.php">Pelayanan</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="login.php" class="btn btn-primary">Masuk Akun</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 data-aos="fade-up">Layanan Laundry Terbaik</h1>
            <p data-aos="fade-up" data-aos-delay="100">Kami menyediakan berbagai layanan laundry berkualitas dengan harga terjangkau untuk memenuhi kebutuhan Anda</p>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Layanan Kami</h2>
                <p>Pilih layanan yang sesuai dengan kebutuhan Anda</p>
            </div>
            <div class="row">
                <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $loop * 100; ?>">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-tshirt"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($service['jenis_laundry']); ?></h3>
                        <div class="service-price">
                            Rp <?php echo number_format($service['harga'], 0, ',', '.'); ?>/kg
                        </div>
                        <p class="service-description">
                            <?php 
                            $descriptions = [
                                'Laundry + Setrika' => 'Cuci bersih dengan hasil setrika rapi dan wangi',
                                'Fast Laundry' => 'Layanan cepat untuk kebutuhan mendesak',
                                'Regular' => 'Layanan standar dengan hasil maksimal',
                                'Cuci Karpet' => 'Cuci khusus karpet dengan hasil bersih dan wangi',
                                'Fast Laundry + Setrika' => 'Layanan cepat dengan hasil setrika rapi'
                            ];
                            echo htmlspecialchars($service['estimasi_waktu'] ? "Estimasi: " . $service['estimasi_waktu'] : ($descriptions[$service['jenis_laundry']] ?? "Layanan laundry berkualitas dengan hasil maksimal")); 
                            ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Mengapa Memilih Kami?</h2>
                <p>Kami berkomitmen memberikan layanan terbaik untuk Anda</p>
            </div>
            <div class="row">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h3 class="feature-title">Cepat & Tepat Waktu</h3>
                        <p class="feature-description">Proses cepat dengan estimasi waktu yang akurat</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-star"></i>
                        </div>
                        <h3 class="feature-title">Kualitas Terjamin</h3>
                        <p class="feature-description">Hasil cucian bersih, wangi, dan rapi</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="feature-title">Aman & Terpercaya</h3>
                        <p class="feature-description">Barang Anda aman dalam penanganan kami</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html> 