<?php

require_once __DIR__ . '/../config/database/db.php';

/**
 * Check if a member exists by school ID number
 * 
 * Query Parameters:
 * - idNumber (required): School ID number to check
 * 
 * Returns JSON with:
 * - exists (boolean): Whether the member exists
 * - member (object): Member data if exists
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$idNumber = isset($_GET['idNumber']) ? trim($_GET['idNumber']) : '';

if (empty($idNumber)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "ID number is required"
    ]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, id_school_number, first_name, last_name, email, birth_date, program, year, is_paid FROM members WHERE id_school_number = :idNumber LIMIT 1");
    $stmt->bindParam(":idNumber", $idNumber);
    $stmt->execute();
    
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($member) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "exists" => true,
            "member" => $member
        ]);
    } else {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "exists" => false,
            "member" => null
        ]);
    }
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "An error occurred while checking member status"
    ]);
    exit();
} finally {
    $conn = null;
}
?>
