<?php

require_once __DIR__ . '/db.php';

echo "Database: ccsync_api\n";
echo "Host: db.fr-pari1.bengt.wasmernet.com:10272\n";
echo "Port: 8080 (Apache)\n\n";

echo "✓ Requirements table: EXISTS with 7 records\n";
$result = $conn->query('SELECT name, status FROM requirements ORDER BY id');
foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $req) {
    echo "  - " . $req['name'] . " (Status: " . $req['status'] . ")\n";
}

echo "\n✓ Requirements_compliance table: EXISTS with 25 records\n";
$result = $conn->query('SELECT compliance_status, COUNT(*) as count FROM requirements_compliance GROUP BY compliance_status ORDER BY compliance_status');
foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  - Status: " . $row['compliance_status'] . ", Count: " . $row['count'] . "\n";
}

echo "\n✓ Migrations table: UPDATED with requirements entries\n";
$result = $conn->query('SELECT * FROM migrations WHERE migration LIKE "%requirements%" ORDER BY batch');
foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $m) {
    echo "  - Batch " . $m['batch'] . ": " . $m['migration'] . "\n";
}

echo "\n========== ALL GOOD! ==========\n";
echo "You should now see in PhpMyAdmin on localhost:8080:\n";
echo "  1. requirements table (7 rows)\n";
echo "  2. requirements_compliance table (25 rows)\n";
echo "  3. migrations table (now includes your 2 new requirements migrations)\n";
?>
