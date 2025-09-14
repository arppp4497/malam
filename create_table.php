<?php
require_once 'config.php';

try {
    // Create photos table
    $sql = "CREATE TABLE IF NOT EXISTS photos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        date DATE NOT NULL,
        description TEXT,
        image_path VARCHAR(500) NOT NULL,
        type ENUM('my', 'partner') NOT NULL,
        uploaded_by VARCHAR(100) DEFAULT 'anonymous',
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "Table 'photos' created successfully!";

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
