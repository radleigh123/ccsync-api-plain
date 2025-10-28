<?php
/**
 * Fix Virtual Column - Complete the migration
 */

require_once __DIR__ . '/../../config/database/db.php';

echo "================================\n";
echo "Fixing Virtual Column\n";
echo "================================\n\n";

try {
    echo "Checking if name column exists...\n";
    $checkStmt = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'name'");
    $checkStmt->execute();
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✓ name column exists\n";
        echo "Dropping name column...\n";
        $conn->exec("ALTER TABLE users DROP COLUMN name");
        echo "✓ name column dropped\n\n";
    } else {
        echo "✓ name column already dropped (or doesn't exist)\n\n";
    }

    echo "Creating virtual computed column...\n";
    $sql = "ALTER TABLE users ADD COLUMN name VARCHAR(255) GENERATED AS (CONCAT(name_first, ' ', name_last)) VIRTUAL";
    $conn->exec($sql);
    echo "✓ Virtual computed column created\n\n";

    // Verification
    echo "================================\n";
    echo "Verification\n";
    echo "================================\n\n";

    $verifyStmt = $conn->prepare("SELECT id, name_first, name_last, name FROM users");
    $verifyStmt->execute();
    $results = $verifyStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        echo "ID: {$row['id']} | First: {$row['name_first']} | Last: {$row['name_last']} | Computed Name: {$row['name']}\n";
    }

    echo "\n================================\n";
    echo "✅ Virtual column setup completed successfully!\n";
    echo "================================\n";

} catch (PDOException $e) {
    echo "\n❌ Operation failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    $conn = null;
}
?>
