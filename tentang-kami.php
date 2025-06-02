<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Laundry</title>
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

        .about-section {
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

        .about-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,.05);
            padding: 30px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            height: 100%;
        }

        .about-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,.1);
        }

        .about-icon {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }

        .about-card:hover .about-icon {
            transform: scale(1.1);
        }

        .about-card h3 {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .about-description {
            color: #666;
            margin-bottom: 0;
            line-height: 1.6;
            font-size: 1.1rem;
        }

        .team-section {
            padding: 60px 0;
            background-color: white;
        }

        .team-member {
            text-align: center;
            padding: 30px;
        }

        .team-member img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-bottom: 20px;
            object-fit: cover;
        }

        .team-member h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .team-member p {
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

            .about-card {
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
                        <a class="nav-link active" aria-current="page" href="tentang-kami.php">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pelayanan.php">Pelayanan</a>
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
            <h1 data-aos="fade-up">Tentang Kami</h1>
            <p data-aos="fade-up" data-aos-delay="100">Mengenal lebih dekat dengan layanan laundry terpercaya kami</p>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Visi & Misi Kami</h2>
                <p>Memberikan layanan terbaik untuk kepuasan pelanggan</p>
            </div>
            <div class="row">
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="about-card">
                        <div class="about-icon">
                            <i class="bi bi-eye"></i>
                        </div>
                        <h3>Visi</h3>
                        <p class="about-description">
                            Menjadi layanan laundry terpercaya dan terbaik di wilayah kami dengan mengutamakan kualitas, 
                            ketepatan waktu, dan kepuasan pelanggan.
                        </p>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="about-card">
                        <div class="about-icon">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <h3>Misi</h3>
                        <p class="about-description">
                            1. Memberikan pelayanan laundry yang bersih, cepat, dan profesional.<br>
                            2. Menggunakan peralatan dan bahan ramah lingkungan untuk menjaga kualitas dan keberlanjutan.<br>
                            3. Menjaga kepercayaan pelanggan melalui pelayanan yang jujur dan tepat waktu.<br>
                            4. Terus meningkatkan kualitas layanan melalui pelatihan karyawan dan inovasi teknologi.<br>
                            5. Membangun hubungan jangka panjang dengan pelanggan melalui pelayanan yang ramah dan responsif.
                        </p>
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