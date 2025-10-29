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

use Kreait\Firebase\Exception\AuthException;
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

// Get JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($input)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "No input data provided"
        ]);
        exit();
    }

    if (
        !isset($data['first_name']) ||
        !isset($data['last_name']) ||
        !isset($data['email']) ||
        !isset($data['password']) ||
        !isset($data['password_confirmation']) ||
        !isset($data['id_school_number'])
    ) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Missing required fields: name, email, password, and id_number are required"
        ]);
        exit();
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email format"
        ]);
        exit();
    }

    // Validate password length
    if (strlen($data['password']) < 6) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Password must be at least 6 characters long"
        ]);
        exit();
    }

    // Validate password and confirmation
    if ($data['password'] !== $data['password_confirmation']) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Password does not match"
        ]);
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

try {
    // Start transaction
    $conn->beginTransaction();

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(":email", $data['email']);
    $stmt->execute();
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email already registered"
        ]);
        exit();
    }

    // Check if ID number already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE id_school_number = :id_school_number");
    $stmt->bindParam(":id_school_number", $data['id_number']);
    $stmt->execute();
    $existingIdNumber = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingIdNumber) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "ID number already registered"
        ]);
        exit();
    }

    // Create Firebase user
    $firebaseUid = null;
    try {
        $fullName = $data['first_name'] . " " . $data['last_name'];
        // source read: https://firebase-php.readthedocs.io/en/7.23.0/user-management.html#create-a-user
        $userProperties = [
            'email' => $data['email'],
            'emailVerified' => false,
            'password' => $data['password'],
            'displayName' => $fullName,
        ];

        $firebaseUser = $auth->createUser($userProperties);
        $firebaseUid = $firebaseUser->uid;
    } catch (AuthException $e) {
        $conn->rollBack();
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Firebase user creation failed",
            "error" => $e->getMessage()
        ]);
        exit();
    }

    // Insert new user in local database
    $stmt = $conn->prepare("
        INSERT INTO users (name, email, firebase_uid, id_school_number, password, role, created_at, updated_at) 
        VALUES (:name, :email, :firebase_uid, :id_school_number, :password, :role, NOW(), NOW())
    ");

    $role = 'user'; // Default role
    $options = ['cost' => 12];
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT, $options);

    $stmt->bindParam(":name", $fullName);
    $stmt->bindParam(":email", $data['email']);
    $stmt->bindParam(":firebase_uid", $firebaseUid);
    $stmt->bindParam(":id_school_number", $data['id_school_number']);
    $stmt->bindParam(":password", $hashedPassword);
    $stmt->bindParam(":role", $role);

    $stmt->execute();
    $userId = $conn->lastInsertId();

    // Create custom token for the new user
    $customToken = $auth->createCustomToken($firebaseUid, [
        'user_id' => $userId,
        'email' => $data['email'],
        'name' => $fullName,
    ]);

    // Fetch the newly created user
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(":id", $userId);
    $stmt->execute();
    $newUser = $stmt->fetch(PDO::FETCH_ASSOC);

    // Commit transaction
    $conn->commit();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "User registered successfully",
        "user" => [
            'id' => $newUser['id'],
            'name' => $newUser['name'],
            'email' => $newUser['email'],
            'firebase_uid' => $newUser['firebase_uid'],
            'email_verified' => false,
            'role' => $newUser['role'],
            'id_school_number' => $newUser['id_school_number']
        ],
        'firebase_token' => $customToken->toString(),
        'firebase_uid' => $firebaseUser->uid,
    ]);
} catch (AuthException $e) {
    if (isset($firebaseUser) && isset($firebaseUser->uid)) {
        try {
            $auth->deleteUser($firebaseUser->uid);
        } catch (Exception $deleteException) {
            error_log("Failed to delete Firebase user after registration failure: " . $deleteException->getMessage());
        }
    }
    $conn->rollBack();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Firebase authentication failed',
        'error' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    if (isset($firebaseUser) && isset($firebaseUser->uid)) {
        try {
            $auth->deleteUser($firebaseUser->uid);
        } catch (Exception $deleteException) {
            error_log("Failed to delete Firebase user after registration failure: " . $deleteException->getMessage());
        }
    }
    $conn->rollBack();
    error_log("Registration failed: " . $e->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Registration failed: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Internal server error during registration"
    ]);
} catch (Exception $e) {
    if (isset($firebaseUser) && isset($firebaseUser->uid)) {
        try {
            $auth->deleteUser($firebaseUser->uid);
        } catch (Exception $deleteException) {
            error_log("Failed to delete Firebase user after registration failure: " . $deleteException->getMessage());
        }
    }
    $conn->rollBack();
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred during registration',
        'error' => $e->getMessage()
    ]);
} finally {
    if (!isset($conn)) {
        $conn = null;
    }
}
