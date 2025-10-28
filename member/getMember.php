<?php

require_once __DIR__ . '/../config/database/db.php';
// require __DIR__ . '/../vendor/autoload.php';  // Not needed - using native PHP/PDO only

/**
 * Get a single member by ID
 * 
 * Query Parameters:
 * - id (required): The member ID or school ID number to fetch
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

try {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'id parameter is required'
        ]);
        exit();
    }

    // Try to find by member ID first, then by school ID number
    $query = "
        SELECT 
            id,
            first_name,
            last_name,
            suffix,
            id_school_number,
            email,
            birth_date,
            enrollment_date,
            program,
            year,
            is_paid,
            created_at,
            updated_at
        FROM members
        WHERE id = :id OR id_school_number = :id_school
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':id_school', $id, PDO::PARAM_INT);
    
    $stmt->execute();
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Member retrieved successfully',
            'member' => $member
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Member not found'
        ]);
    }

} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database query failed',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
?>
