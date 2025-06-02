<?php
require_once 'config/database.php';

$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$name = 'Administrator';
$jenis_kelamin = 'Laki-laki';
$alamat = 'Admin Office';
$no_telepon = '08123456789';
$level = 'Admin';

try {
    // Delete existing admin user
    $stmt = $pdo->prepare("DELETE FROM user WHERE username = ?");
    $stmt->execute([$username]);
    
    // Insert new admin user
    $stmt = $pdo->prepare("INSERT INTO user (username, password, name, jenis_kelamin, alamat, no_telepon, level) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $name, $jenis_kelamin, $alamat, $no_telepon, $level]);
    
    echo "Admin user created successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 