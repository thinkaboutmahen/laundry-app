<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary border-bottom shadow-sm">
    <div class="custom-container d-flex justify-content-end align-items-center w-100">
        <!-- Right side: Profile -->
        <div class="d-flex align-items-center">
            <!-- Profile Picture with Online Indicator -->
            <div class="position-relative">
                <?php
                // Get user data from database
                $stmt = $pdo->prepare("SELECT * FROM user WHERE id_user = ?");
                $stmt->execute([$_SESSION['id_user']]);
                $user = $stmt->fetch();
                
                if (!empty($user['userFoto'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($user['userFoto']); ?>" 
                         alt="Profile" 
                         class="rounded-circle border border-white shadow-sm" 
                         width="40" 
                         height="40" 
                         style="object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center border border-white shadow-sm" 
                         style="width: 40px; height: 40px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
                <span class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle" 
                      style="width: 10px; height: 10px; right: 0; bottom: 0; box-shadow: 0 0 0 2px rgba(255,255,255,0.3);"></span>
            </div>
            <!-- User Info -->
            <div class="ms-2 text-white d-none d-sm-block">
                <div class="small fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="small opacity-75"><?php echo htmlspecialchars($user['level']); ?></div>
            </div>
        </div>
    </div>
</nav>

<style>
.navbar {
    height: auto;
    min-height: 60px;
    padding: 0.5rem 1rem;
}

@media (max-width: 768px) {
    .navbar {
        padding: 0.5rem;
    }
    
    .custom-container {
        padding: 0 0.5rem;
    }
}
</style>