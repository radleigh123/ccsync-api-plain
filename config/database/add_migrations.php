<?php

require_once __DIR__ . '/db.php';

try {
    echo "========== ADDING MIGRATION RECORDS ==========\n";
    
    // Get the current batch number
    $batchResult = $conn->query('SELECT MAX(batch) as max_batch FROM migrations');
    $batchData = $batchResult->fetch(PDO::FETCH_ASSOC);
    $nextBatch = ($batchData['max_batch'] ?? 0) + 1;
    
    echo "Current batch: " . $batchData['max_batch'] . ", Next batch: $nextBatch\n\n";
    
    // Check if migrations already exist
    $checkReq = $conn->query("SELECT COUNT(*) as count FROM migrations WHERE migration LIKE '%requirements%'");
    $existingCount = $checkReq->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($existingCount > 0) {
        echo "⚠️  Requirements migrations already exist in tracking table\n";
    } else {
        // Add requirements table migration
        $stmt = $conn->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute(['2025_10_28_000001_create_requirements_table', $nextBatch]);
        echo "✓ Added migration: 2025_10_28_000001_create_requirements_table\n";
        
        // Add requirements_compliance table migration
        $stmt = $conn->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute(['2025_10_28_000002_create_requirements_compliance_table', $nextBatch]);
        echo "✓ Added migration: 2025_10_28_000002_create_requirements_compliance_table\n";
        
        echo "\n✓ Migration records added successfully!\n";
    }
    
    // Show all migrations
    echo "\n========== CURRENT MIGRATIONS ==========\n";
    $result = $conn->query('SELECT * FROM migrations ORDER BY batch, migration');
    $migrations = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($migrations as $m) {
        echo "  Batch " . $m['batch'] . ": " . $m['migration'] . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
