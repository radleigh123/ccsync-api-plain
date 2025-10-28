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
$idSchoolNumber = $data['id_school_number'] ?? null;
$name = $data['name'] ?? '';

try {
    // Verify the Firebase token
    $verifiedIdToken = $auth->verifyIdToken($idToken);
    $firebaseUid = $verifiedIdToken->claims()->get('sub');
    $firebaseUser = $auth->getUser($firebaseUid);

    // Check if user already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE firebase_uid = :firebase_uid");
    $stmt->bindParam(":firebase_uid", $firebaseUid);
    $stmt->execute();
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "User already registered"
        ]);
        exit();
    }

    // Insert new user
    $stmt = $conn->prepare("
        INSERT INTO users (name, email, firebase_uid, id_school_number, role) 
        VALUES (:name, :email, :firebase_uid, :id_school_number, :role)
    ");

    $role = 'user'; // Default role
    $email = $firebaseUser->email;

    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":firebase_uid", $firebaseUid);
    $stmt->bindParam(":id_school_number", $idSchoolNumber);
    $stmt->bindParam(":role", $role);

    $stmt->execute();
    $userId = $conn->lastInsertId();

    // Fetch the newly created user
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(":id", $userId);
    $stmt->execute();
    $newUser = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "User registered successfully",
        "user" => [
            'id' => $newUser['id'],
            'name' => $newUser['name'],
            'email' => $newUser['email'],
            'firebase_uid' => $newUser['firebase_uid'],
            'email_verified' => $firebaseUser->emailVerified,
            'role' => $newUser['role'],
            'id_school_number' => $newUser['id_school_number']
        ]
    ]);
} catch (FailedToVerifyToken $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or expired ID token',
        'error' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log("Registration failed: " . $e->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Registration failed: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Internal server error during registration"
    ]);
    exit();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred during registration',
        'error' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
