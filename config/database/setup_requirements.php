<?php

require_once __DIR__ . '/db.php';

try {
    // Create requirements table
    $createRequirementsSQL = "
    CREATE TABLE IF NOT EXISTS `requirements` (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `name` varchar(255) NOT NULL,
      `description` text DEFAULT NULL,
      `status` enum('open','closed','archived') NOT NULL DEFAULT 'open',
      `requirement_date` date NOT NULL,
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conn->exec($createRequirementsSQL);
    echo "✓ Requirements table created successfully\n";
    
    // Create requirements_compliance table
    $createComplianceSQL = "
    CREATE TABLE IF NOT EXISTS `requirements_compliance` (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `requirement_id` bigint(20) UNSIGNED NOT NULL,
      `member_id` bigint(20) UNSIGNED NOT NULL,
      `compliance_status` enum('complied','not_complied','pending') NOT NULL DEFAULT 'pending',
      `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      KEY `idx_requirement_id` (`requirement_id`),
      KEY `idx_member_id` (`member_id`),
      FOREIGN KEY (`requirement_id`) REFERENCES `requirements`(`id`) ON DELETE CASCADE,
      FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conn->exec($createComplianceSQL);
    echo "✓ Requirements compliance table created successfully\n";
    
    // Check if data already exists
    $checkReq = $conn->query("SELECT COUNT(*) FROM requirements");
    $count = $checkReq->fetchColumn();
    
    if ($count == 0) {
        // Insert sample requirements
        $insertRequirementsSQL = "
        INSERT INTO `requirements` (`name`, `description`, `status`, `requirement_date`, `created_at`, `updated_at`) VALUES
        ('Good Moral Certificate', 'Character clearance from previous institution or employer', 'open', '2025-10-28', NOW(), NOW()),
        ('Transcript of Records', 'Official transcript showing academic performance and grades', 'open', '2025-10-28', NOW(), NOW()),
        ('Birth Certificate', 'Certified copy of birth certificate (original or photocopy)', 'open', '2025-10-28', NOW(), NOW()),
        ('Medical Certificate', 'Health examination clearance from a licensed physician', 'open', '2025-10-30', NOW(), NOW()),
        ('Valid ID Submission', 'Any valid government-issued identification document', 'closed', '2025-09-30', NOW(), NOW()),
        ('Proof of Residency', 'Recent utility bill or lease agreement showing current address', 'open', '2025-11-15', NOW(), NOW()),
        ('NBI Clearance', 'National Bureau of Investigation clearance certificate', 'open', '2025-11-20', NOW(), NOW());
        ";
        
        $conn->exec($insertRequirementsSQL);
        echo "✓ Sample requirements data inserted successfully (7 records)\n";
        
        // Insert sample compliance records
        $insertComplianceSQL = "
        INSERT INTO `requirements_compliance` (`requirement_id`, `member_id`, `compliance_status`, `submitted_at`, `created_at`, `updated_at`) VALUES
        -- Good Moral Certificate (requirement 1)
        (1, 1, 'complied', '2025-09-15 10:00:00', NOW(), NOW()),
        (1, 2, 'complied', '2025-09-18 14:30:00', NOW(), NOW()),
        (1, 3, 'not_complied', '2025-09-20 09:00:00', NOW(), NOW()),
        (1, 4, 'pending', NOW(), NOW(), NOW()),
        (1, 5, 'complied', '2025-09-22 11:15:00', NOW(), NOW()),
        -- Transcript of Records (requirement 2)
        (2, 1, 'complied', '2025-09-16 08:45:00', NOW(), NOW()),
        (2, 2, 'pending', NOW(), NOW(), NOW()),
        (2, 3, 'complied', '2025-09-19 15:20:00', NOW(), NOW()),
        (2, 4, 'complied', '2025-09-21 10:00:00', NOW(), NOW()),
        (2, 5, 'not_complied', '2025-09-23 13:45:00', NOW(), NOW()),
        -- Birth Certificate (requirement 3)
        (3, 1, 'pending', NOW(), NOW(), NOW()),
        (3, 2, 'complied', '2025-09-17 12:00:00', NOW(), NOW()),
        (3, 3, 'complied', '2025-09-18 16:30:00', NOW(), NOW()),
        (3, 4, 'not_complied', '2025-09-24 09:15:00', NOW(), NOW()),
        (3, 5, 'complied', '2025-09-20 14:00:00', NOW(), NOW()),
        -- Medical Certificate (requirement 4)
        (4, 1, 'complied', '2025-09-25 10:30:00', NOW(), NOW()),
        (4, 2, 'complied', '2025-09-26 11:00:00', NOW(), NOW()),
        (4, 3, 'pending', NOW(), NOW(), NOW()),
        (4, 4, 'complied', '2025-09-27 15:45:00', NOW(), NOW()),
        (4, 5, 'pending', NOW(), NOW(), NOW()),
        -- Valid ID Submission (requirement 5)
        (5, 1, 'complied', '2025-09-10 09:00:00', NOW(), NOW()),
        (5, 2, 'complied', '2025-09-11 10:30:00', NOW(), NOW()),
        (5, 3, 'complied', '2025-09-12 14:00:00', NOW(), NOW()),
        (5, 4, 'complied', '2025-09-13 16:45:00', NOW(), NOW()),
        (5, 5, 'complied', '2025-09-14 11:20:00', NOW(), NOW());
        ";
        
        $conn->exec($insertComplianceSQL);
        echo "✓ Sample compliance data inserted successfully (25 records)\n";
    } else {
        echo "⚠ Requirements table already has data, skipping sample insert\n";
    }
    
    echo "\n✓ All tables created and data loaded successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
