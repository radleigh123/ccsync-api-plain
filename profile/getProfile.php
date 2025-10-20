<?php

require_once __DIR__ . '/../config/database/db.php';
require __DIR__ . '/../vendor/autoload.php';

/**
 * Fetch user profile by ID
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// $data = json_decode(file_get_contents("php://input"), true);
// $user_id = $data['id'] ?? '';
// Get user ID from query parameters
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

try {
    $stmt = $conn->prepare("SELECT p.*, u.email AS user_email, u.id_school_number AS user_id_school_number FROM profiles p LEFT JOIN users u ON p.id_user = u.id WHERE p.id_user = :user_id");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "User profile successfully recieved",
        "userProfile" => [
            'id_profile' => $userProfile['id'],
            'first_name' => $userProfile['first_name'],
            'last_name' => $userProfile['last_name'],
            'email' => $userProfile['user_email'],
            'id_school_number' => $userProfile['user_id_school_number'],
            'phone_number' => $userProfile['phone_number'],
            'bio' => $userProfile['bio'],
            'gender' => $userProfile['gender']
        ]
    ]);
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Query failed: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Internal server error"
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "An unexpected error occurred",
        "error" => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
