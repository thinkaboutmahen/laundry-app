<?php
require_once 'config/database.php';

try {
    // Create table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS layanan (
        id_layanan INT AUTO_INCREMENT PRIMARY KEY,
        nama_layanan VARCHAR(100) NOT NULL,
        deskripsi TEXT,
        harga DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Table 'layanan' created successfully or already exists.<br>";

    // Check if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM layanan");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Insert sample data
        $sql = "INSERT INTO layanan (nama_layanan, deskripsi, harga) VALUES 
            ('Cuci Kering', 'Layanan cuci kering untuk pakaian sehari-hari', 15000.00),
            ('Cuci Setrika', 'Layanan cuci dan setrika untuk pakaian formal', 25000.00),
            ('Setrika Saja', 'Layanan setrika untuk pakaian yang sudah dicuci', 10000.00),
            ('Cuci Selimut', 'Layanan cuci khusus untuk selimut dan bed cover', 35000.00),
            ('Cuci Sepatu', 'Layanan cuci khusus untuk sepatu', 30000.00),
            ('Cuci Boneka', 'Layanan cuci khusus untuk boneka dan mainan', 20000.00)";
        
        $pdo->exec($sql);
        echo "Sample data inserted successfully.";
    } else {
        echo "Table already has data.";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 