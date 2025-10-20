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
$currentPass = $data['current_password'] ?? '';
$newPass = $data['new_password'] ?? '';

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

if (empty($currentPass) || empty($newPass)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit();
}

// Verify user's password
try {
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bindParam(1, $user_id);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $hashedPassword = $user['password'];

    if (!password_verify($currentPass, $hashedPassword)) {
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'message' => 'Wrong password',
        ]);
        exit();
    }
} catch (PDOException $e) {
    error_log("Get password failed: " . $e->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Get password failed: " . $e->getMessage() . "\n", FILE_APPEND);

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database SELECT failed",
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
    exit();
}

// TODO: Update Firebase as well
try {
    $newPass = password_hash($newPass, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bindParam(1, $newPass);
    $stmt->bindParam(2, $user_id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "User password updated successfully"
    ]);
} catch (PDOException $e) {
    error_log("UPDATE failed: " . $e->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - UPDATE failed: " . $e->getMessage() . "\n", FILE_APPEND);

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
