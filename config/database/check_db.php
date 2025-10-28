<?php

require_once __DIR__ . '/db.php';

try {
    echo "========== DATABASE TABLES ==========\n";
    $result = $conn->query('SHOW TABLES');
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\n========== CHECKING REQUIREMENTS TABLE ==========\n";
    $check = $conn->query('SELECT COUNT(*) as count FROM requirements');
    $count = $check->fetch(PDO::FETCH_ASSOC);
    echo "Requirements records: " . $count['count'] . "\n";
    
    echo "\n========== CHECKING REQUIREMENTS_COMPLIANCE TABLE ==========\n";
    $check = $conn->query('SELECT COUNT(*) as count FROM requirements_compliance');
    $count = $check->fetch(PDO::FETCH_ASSOC);
    echo "Compliance records: " . $count['count'] . "\n";
    
    echo "\n========== CHECKING MIGRATIONS TABLE ==========\n";
    try {
        $result = $conn->query('DESCRIBE migrations');
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        echo "Migrations table structure:\n";
        foreach ($columns as $col) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
        
        $result = $conn->query('SELECT * FROM migrations LIMIT 10');
        $migrations = $result->fetchAll(PDO::FETCH_ASSOC);
        echo "\nMigrations records: " . count($migrations) . "\n";
        foreach ($migrations as $m) {
            echo "  - Batch: " . $m['batch'] . ", Migration: " . $m['migration'] . "\n";
        }
    } catch (Exception $e) {
        echo "Migrations table query failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
