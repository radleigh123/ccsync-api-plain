<?php
/**
 * Database Migration Runner
 * Executes the migration script to add name_first and name_last columns to users table
 */

require_once __DIR__ . '/../../config/database/db.php';

echo "================================\n";
echo "Database Migration Runner\n";
echo "================================\n\n";

try {
    echo "[1/5] Adding new columns (name_first, name_last)...\n";
    $sql1 = "ALTER TABLE users 
             ADD COLUMN name_first VARCHAR(255) NULL AFTER id,
             ADD COLUMN name_last VARCHAR(255) NULL AFTER name_first";
    $conn->exec($sql1);
    echo "✓ Columns added successfully\n\n";

    echo "[2/5] Splitting existing names into first_name and last_name...\n";
    $sql2 = "UPDATE users 
             SET 
               name_first = TRIM(SUBSTRING_INDEX(name, ' ', 1)),
               name_last = TRIM(SUBSTRING_INDEX(name, ' ', -1))";
    $result = $conn->exec($sql2);
    echo "✓ Updated $result records\n\n";

    echo "[3/5] Handling single-word names...\n";
    $sql3 = "UPDATE users 
             SET name_last = ''
             WHERE name_last = name_first AND name NOT LIKE '% %'";
    $result = $conn->exec($sql3);
    echo "✓ Handled $result edge cases\n\n";

    echo "[4/5] Making columns NOT NULL...\n";
    $sql4 = "ALTER TABLE users 
             MODIFY COLUMN name_first VARCHAR(255) NOT NULL,
             MODIFY COLUMN name_last VARCHAR(255) NOT NULL";
    $conn->exec($sql4);
    echo "✓ Columns set to NOT NULL\n\n";

    echo "[5/5] Converting name column to VIRTUAL computed column...\n";
    // Drop the old name column
    $sql5a = "ALTER TABLE users DROP COLUMN name";
    $conn->exec($sql5a);
    
    // Add it back as a virtual computed column
    $sql5b = "ALTER TABLE users ADD COLUMN name VARCHAR(255) GENERATED AS (CONCAT(name_first, ' ', name_last)) STORED";
    $conn->exec($sql5b);
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
    echo "✅ Migration completed successfully!\n";
    echo "================================\n";

} catch (PDOException $e) {
    echo "\n❌ Migration failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    $conn = null;
}
?>
