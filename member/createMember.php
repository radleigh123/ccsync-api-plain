<?php

require_once __DIR__ . '/../config/database/db.php';

/**
 * Create a new member
 * 
 * IMPORTANT: userId is intentionally NOT stored in the members table
 * Members are uniquely identified by id_school_number (school ID), which is the canonical identifier
 * This allows members to be tracked independently of user accounts and supports use cases where:
 * - One user can represent multiple member profiles
 * - Members can exist without an active user account
 * - Member data lifecycle is independent of user account lifecycle
 * 
 * Note: If user_id is sent in the request, it is safely ignored by this endpoint
 * 
 * Expected fields in JSON request:
 * - first_name (required)
 * - last_name (required)
 * - id_school_number (required)
 * - email (optional)
 * - birth_date (required)
 * - enrollment_date (required)
 * - program (required)
 * - year (required)
 * - suffix (optional)
 * - is_paid (optional, default: 0)
 * 
 * Note: user_id field is ignored if provided (for frontend compatibility)
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

// ============================================================
// VALIDATION LAYER
// ============================================================

$errors = [];

// 1. Required field validation
if (empty($firstName)) {
    $errors[] = "First name is required";
}
if (empty($lastName)) {
    $errors[] = "Last name is required";
}
if (empty($idSchoolNumber)) {
    $errors[] = "School ID number is required";
}
if (empty($birthDate)) {
    $errors[] = "Birth date is required";
}
if (empty($enrollmentDate)) {
    $errors[] = "Enrollment date is required";
}
if (empty($program)) {
    $errors[] = "Program is required";
}
if ($year === null || $year === '') {
    $errors[] = "Year level is required";
}

// Return early if critical fields are missing
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields",
        "errors" => $errors
    ]);
    exit();
}

// 2. Date format validation (YYYY-MM-DD)
$dateRegex = '/^\d{4}-\d{2}-\d{2}$/';

if (!preg_match($dateRegex, $birthDate)) {
    $errors[] = "Birth date must be in YYYY-MM-DD format";
}
if (!preg_match($dateRegex, $enrollmentDate)) {
    $errors[] = "Enrollment date must be in YYYY-MM-DD format";
}

// 3. Birth date validation
if (preg_match($dateRegex, $birthDate)) {
    $birthTimestamp = strtotime($birthDate);
    if ($birthTimestamp === false) {
        $errors[] = "Birth date is not a valid date";
    } else if ($birthTimestamp > time()) {
        $errors[] = "Birth date cannot be in the future";
    }
}

// 4. Enrollment date validation
if (preg_match($dateRegex, $enrollmentDate)) {
    $enrollmentTimestamp = strtotime($enrollmentDate);
    if ($enrollmentTimestamp === false) {
        $errors[] = "Enrollment date is not a valid date";
    }
}

// 5. Program enum validation (must be one of: BSIT, BSCS, BSIS)
$allowedPrograms = ['BSIT', 'BSCS', 'BSIS'];
if (!in_array($program, $allowedPrograms, true)) {
    $errors[] = "Program must be one of: " . implode(', ', $allowedPrograms);
}

// 6. Year level range validation (must be 1-4)
$yearInt = (int)$year;
if ($yearInt < 1 || $yearInt > 4) {
    $errors[] = "Year level must be between 1 and 4";
}

// 7. Email format validation (if provided)
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email format is invalid";
}

// Return validation errors if any exist
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Validation failed",
        "errors" => $errors
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

    // Handle specific constraint violations
    if (strpos($e->getMessage(), '1062') !== false) {
        // Duplicate entry
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "This student ID is already registered as a member",
            "error" => "Duplicate member entry"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "error" => "Database insert failed",
            "message" => "Internal server error"
        ]);
    }
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
