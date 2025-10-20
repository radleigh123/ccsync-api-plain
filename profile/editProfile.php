<?php

require_once __DIR__ . '/../config/database/db.php';
require __DIR__ . '/../vendor/autoload.php';

/**
 * Edit user account information
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$displayName = $data['display_name'] ?? '';
$bio = $data['bio'] ?? '';

$user_id = '';
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $user_id = intval($user_id);
} else {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "User ID is required"
    ]);
    exit();
}

if (empty($displayName) || empty($bio)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE profiles SET display_name = ?, bio = ? WHERE id_user = ?");
    $stmt->bindParam(1, $displayName);
    $stmt->bindParam(2, $bio);
    $stmt->bindParam(3, $user_id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "User profile information updated successfully"
    ]);
} catch (PDOException $e) {
    error_log("Insert failed: " . $e->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Insert failed: " . $e->getMessage() . "\n", FILE_APPEND);

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database UPDATE failed",
        "message" => "Internal server error"
    ]);
    exit();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred',
        'error' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
