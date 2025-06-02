<?php
// Get counts for badges
$userCount = $pdo->query("SELECT COUNT(*) as total FROM user")->fetch()['total'];
$customerCount = $pdo->query("SELECT COUNT(*) as total FROM pelanggan")->fetch()['total'];
$packageCount = $pdo->query("SELECT COUNT(*) as total FROM paket_laundry")->fetch()['total'];
$transactionCount = $pdo->query("SELECT COUNT(*) as total FROM transaksi WHERE status_laundry != 'Diambil'")->fetch()['total'];
$expenseCount = $pdo->query("SELECT COUNT(*) as total FROM pengeluaran")->fetch()['total'];

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>



<!-- Mobile Menu Toggle Button -->
<button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Menu Overlay -->
<div class="menu-overlay" id="menuOverlay"></div>

<!-- Sidebar -->
<div class="bg-white border-end" id="sidebar">
    <div class="p-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="text-center flex-grow-1">
                <img src="assets/logo.png" alt="Logo" style="max-width: 80px; max-height: 80px;" class="mb-2">
                <h4 class="mb-0">Laundry</h4>
                <small class="text-muted"><?php echo $_SESSION['level'] === 'Admin' ? 'Beranda Admin' : 'Beranda Kasir'; ?></small>
            </div>
        </div>

        <div class="nav-section">
            <h6 class="text-muted mb-3">Menu Utama</h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between text-dark <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-house-door me-2"></i> Beranda
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between text-dark <?php echo $currentPage === 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person me-2"></i> Data Pelanggan
                        </div>
                        <span class="badge bg-primary"><?php echo $customerCount; ?></span>
                    </a>
                </li>
                <?php if ($_SESSION['level'] === 'Admin'): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between text-dark <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>" href="users.php">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-badge me-2"></i> Data Pengguna
                        </div>
                        <span class="badge bg-primary"><?php echo $userCount; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between text-dark <?php echo $currentPage === 'packages.php' ? 'active' : ''; ?>" href="packages.php">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-box-seam me-2"></i> Jenis Layanan
                        </div>
                        <span class="badge bg-primary"><?php echo $packageCount; ?></span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between text-dark <?php echo $currentPage === 'transactions.php' ? 'active' : ''; ?>" href="transactions.php">
                        <div class="d-flex align-items-center me-auto me-3">
                            <i class="bi bi-cart me-2"></i> Transaksi Laundry
                        </div>
                        <span class="badge bg-primary"><?php echo $transactionCount; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between text-dark <?php echo $currentPage === 'expenses.php' ? 'active' : ''; ?>" href="expenses.php">
                        <div class="d-flex align-items-center me-auto me-3">
                            <i class="bi bi-cash-stack me-2"></i> Data Pengeluaran
                        </div>
                        <span class="badge bg-primary"><?php echo $expenseCount; ?></span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Logout -->
        <div class="mt-4 pt-3 border-top">
            <a class="nav-link d-flex align-items-center text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const menuOverlay = document.getElementById('menuOverlay');
    const mainContent = document.querySelector('.container');

    function toggleMenu() {
        sidebar.classList.toggle('show');
        menuOverlay.classList.toggle('show');
        menuToggle.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
    }

    menuToggle.addEventListener('click', toggleMenu);
    menuOverlay.addEventListener('click', toggleMenu);

    // Close menu when clicking a link on mobile
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                toggleMenu();
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
            menuOverlay.classList.remove('show');
            menuToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});
</script>