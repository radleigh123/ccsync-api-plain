<?php
require_once __DIR__ . '/../config/database/db.php';

header("Content-Type: application/json");

try {
    $result = $conn->query("SELECT id, id_school_number, first_name, last_name, email FROM members ORDER BY id DESC");
    $members = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "count" => count($members),
        "members" => $members
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
