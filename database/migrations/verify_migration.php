<?php
/**
 * Verify Migration State - Check what's in the database now
 */

require_once __DIR__ . '/../../config/database/db.php';

echo "================================\n";
echo "Migration Verification\n";
echo "================================\n\n";

try {
    echo "Checking users table structure...\n";
    $checkStmt = $conn->prepare("DESCRIBE users");
    $checkStmt->execute();
    $columns = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nUsers table columns:\n";
    echo str_pad("Column Name", 20) . str_pad("Type", 30) . "Null\n";
    echo str_repeat("-", 60) . "\n";
    
    $has_name_first = false;
    $has_name_last = false;
    $has_name = false;
    
    foreach ($columns as $col) {
        echo str_pad($col['Field'], 20) . str_pad($col['Type'], 30) . $col['Null'] . "\n";
        if ($col['Field'] === 'name_first') $has_name_first = true;
        if ($col['Field'] === 'name_last') $has_name_last = true;
        if ($col['Field'] === 'name') $has_name = true;
    }
    
    echo "\n================================\n";
    echo "Summary:\n";
    echo "- name_first column: " . ($has_name_first ? "✓ EXISTS" : "✗ MISSING") . "\n";
    echo "- name_last column: " . ($has_name_last ? "✓ EXISTS" : "✗ MISSING") . "\n";
    echo "- name column: " . ($has_name ? "✓ EXISTS" : "✗ MISSING") . "\n";
    
    if ($has_name_first && $has_name_last) {
        echo "\n✅ Migration requirements satisfied!\n";
        echo "   Both name_first and name_last columns exist.\n";
        
        // Show sample data
        echo "\nSample data from users table:\n";
        $dataStmt = $conn->prepare("SELECT id, name_first, name_last FROM users LIMIT 5");
        $dataStmt->execute();
        $users = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($users as $user) {
            echo "- ID {$user['id']}: {$user['name_first']} {$user['name_last']}\n";
        }
    } else {
        echo "\n❌ Migration incomplete!\n";
        if (!$has_name_first) echo "   Missing: name_first column\n";
        if (!$has_name_last) echo "   Missing: name_last column\n";
    }
    
    echo "\n================================\n";

} catch (PDOException $e) {
    echo "\n❌ Database error!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    $conn = null;
}
?>
