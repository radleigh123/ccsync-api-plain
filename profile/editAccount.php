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
$email = $data['email'] ?? '';
$phone = $data['phone_number'] ?? '';
$gender = $data['gender'] ?? '';

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

if (empty($email) || empty($phone) || empty($gender)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit();
}

// TODO: Update on Firebase email
try {
    $conn->beginTransaction();
    $stmt = $conn->prepare("UPDATE users SET email = :email WHERE id = :user_id");
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE profiles SET phone_number = ?, gender = ? WHERE id_user = ?");
    $stmt->bindParam(1, $phone);
    $stmt->bindParam(2, $gender);
    $stmt->bindParam(3, $user_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode([
        "success" => true,
        "message" => "User profile updated successfully"
    ]);
} catch (\Throwable $th) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to update user profile"
    ]);
    exit();
}
