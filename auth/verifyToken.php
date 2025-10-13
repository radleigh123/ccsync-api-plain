<?php

require_once __DIR__ . '/../config/database/db.php';
require __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$firebaseSecret = getenv('FIREBASE_SECRET');
$factory = (new Factory)->withServiceAccount($firebaseSecret);
$auth = $factory->createAuth();

$data = json_decode(file_get_contents("php://input"), true);
$idToken = $data['id_token'] ?? '';

try {
    $verifiedIdToken = $auth->verifyIdToken($idToken);
    $firebaseUid = $verifiedIdToken->claims()->get('sub');
    $firebaseUser = $auth->getUser($firebaseUid);

    $stmt = $conn->prepare("SELECT * FROM users WHERE firebase_uid = :firebase_uid");
    $stmt->bindParam(":firebase_uid", $firebaseUid);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Token verified successfully",
        "user" => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'firebase_uid' => $user['firebase_uid'],
            'email_verified' => $firebaseUser->emailVerified,
            'role' => $user['role'],
            'id_school_number' => $user['id_school_number']
        ],
        "firebase_claims" => $verifiedIdToken->claims()->all()
    ]);
} catch (FailedToVerifyToken $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or expired ID token',
        'error' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Query failed: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        "success" => false,
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
