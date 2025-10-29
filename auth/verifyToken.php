<?php
require_once __DIR__ . '/../config/database/db.php';
require __DIR__ . '/../vendor/autoload.php';

try {
    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (\Exception $e) {
    error_log("Error loading .env file: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error'
    ]);
    exit();
}

use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;

header("Access-Control-Allow-Origin: http://localhost:5137");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Read input data ONCE
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No input data provided'
        ]);
        exit();
    }

    if (!isset($data['id_token'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID token is required'
        ]);
        exit();
    }
}

$firebaseSecret = $_ENV['FIREBASE_SECRET'] ?? null;
if (!$firebaseSecret || !file_exists($firebaseSecret)) {
    error_log("Firebase credentials file not found at: " . $firebaseSecret);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Firebase configuration error'
    ]);
    exit();
}

$factory = (new Factory)->withServiceAccount($firebaseSecret);
$auth = $factory->createAuth();

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
    error_log("Unexpected error: " . $e->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Unexpected error: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred',
        'error' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
