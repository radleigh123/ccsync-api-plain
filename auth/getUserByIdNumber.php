<?php
/**
 * Lookup User by ID Number
 *
 * Retrieves user information from the users table by their school ID number.
 * Used in the member registration flow to auto-fill user data.
 * Returns data in camelCase format matching MemberCreateDTO structure.
 *
 * @author CCSync Development Team
 * @version 1.0
 *
 * @endpoint GET /auth/getUserByIdNumber.php?idNumber=20023045
 * @return {
 *   "success": true,
 *   "data": {
 *     "id": 1,
 *     "firstName": "Juan",
 *     "lastName": "Dela Cruz",
 *     "email": "juan.delacruz@example.com",
 *     "idNumber": "20023045"
 *   }
 * }
 */

require_once __DIR__ . '/../config/database/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get ID number from query parameter
$idNumber = $_GET['idNumber'] ?? '';

if (!$idNumber) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "ID number is required"
    ]);
    exit();
}

try {
    // Query to find user by school ID number
    // Fetch from users table with snake_case field names from DB
    $query = "
        SELECT id, name_first, name_last, email, id_school_number
        FROM users
        WHERE id_school_number = :idNumber
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(":idNumber", $idNumber, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);
        exit();
    }

    // Return user data in camelCase format for consistency with MemberCreateDTO
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => [
            "id" => (int)$user['id'],
            "firstName" => $user['name_first'],
            "lastName" => $user['name_last'],
            "email" => $user['email'],
            "idNumber" => $user['id_school_number']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);
    exit();
} catch (Exception $e) {
    error_log("Error fetching user: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server error"
    ]);
    exit();
} finally {
    $conn = null;
}
?>
