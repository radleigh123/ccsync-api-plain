<?php

require_once __DIR__ . '/../config/database/db.php';

/**
 * Create a new member
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$firstName = $data['first_name'] ?? '';
$lastName = $data['last_name'] ?? '';
$suffix = $data['suffix'] ?? null;
$idSchoolNumber = $data['id_school_number'] ?? null;
$email = $data['email'] ?? null;
$birthDate = $data['birth_date'] ?? null;
$enrollmentDate = $data['enrollment_date'] ?? null;
$program = $data['program'] ?? '';
$year = $data['year'] ?? null;
$isPaid = isset($data['is_paid']) ? (int)$data['is_paid'] : 0;

if (empty($firstName) || empty($lastName) || empty($idSchoolNumber) || empty($birthDate) || empty($enrollmentDate) || empty($program) || empty($year)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO members (first_name, last_name, suffix, id_school_number, email, birth_date, enrollment_date, program, year, is_paid) VALUES (:first_name, :last_name, :suffix, :id_school_number, :email, :birth_date, :enrollment_date, :program, :year, :is_paid)");
    $stmt->bindParam(":first_name", $firstName);
    $stmt->bindParam(":last_name", $lastName);
    $stmt->bindParam(":suffix", $suffix);
    $stmt->bindParam(":id_school_number", $idSchoolNumber);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":birth_date", $birthDate);
    $stmt->bindParam(":enrollment_date", $enrollmentDate);
    $stmt->bindParam(":program", $program);
    $stmt->bindParam(":year", $year);
    $stmt->bindParam(":is_paid", $isPaid);

    $stmt->execute();
    // $newMemberId = $conn->lastInsertId();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Member created successfully",
        "member" => [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'suffix' => $suffix,
            'id_school_number' => $idSchoolNumber,
            'email' => $email,
            'birth_date' => $birthDate,
            'enrollment_date' => $enrollmentDate,
            'program' => $program,
            'year' => $year,
            'is_paid' => $isPaid
        ]
    ]);
} catch (PDOException $e) {
    error_log("Insert failed: " . $e->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Insert failed: " . $e->getMessage() . "\n", FILE_APPEND);

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database insert failed",
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
