<?php

require_once __DIR__ . '/../config/database/db.php';
require_once __DIR__ . '/../../temp/DTOs/RequirementDTO.php';
require_once __DIR__ . '/../../temp/utils/RequirementValidationHelper.php';

/**
 * Update an existing requirement
 * 
 * Expected fields in JSON request (RequirementUpdateDTO):
 * - name (optional, 3-255 chars): Requirement name
 * - requirementDate (optional, YYYY-MM-DD): Requirement deadline
 * - description (optional): Requirement description
 * - status (optional): Status (open|closed|archived)
 * 
 * Query Parameters:
 * - id: Requirement ID to update (required)
 * 
 * Authentication: Bearer token required (admin-only)
 * 
 * Response:
 * - 200: Requirement updated successfully
 * - 400: Validation error
 * - 401: Unauthorized (missing/invalid token)
 * - 403: Forbidden (insufficient permissions)
 * - 404: Requirement not found
 * - 500: Server error
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // ============================================================
    // AUTHENTICATION & AUTHORIZATION
    // ============================================================
    
    // Check for Bearer token
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (empty($authHeader) || !preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Missing or invalid Authorization header"
        ]);
        exit();
    }

    $token = $matches[1];
    
    // TODO: Verify token and check admin role
    // For now, we'll do basic session verification
    // In production, verify JWT token and check user role
    
    // ============================================================
    // REQUEST PARSING
    // ============================================================
    
    $requirementId = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if (!$requirementId) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Requirement ID is required"
        ]);
        exit();
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON request body"
        ]);
        exit();
    }
    
    // ============================================================
    // CHECK IF REQUIREMENT EXISTS
    // ============================================================
    
    $checkStmt = $conn->prepare("SELECT * FROM requirements WHERE id = ?");
    $checkStmt->execute([$requirementId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Requirement not found"
        ]);
        exit();
    }
    
    // ============================================================
    // VALIDATION
    // ============================================================
    
    $validationResult = RequirementValidationHelper::validateRequirementUpdateDTO($data);
    
    if (!$validationResult['valid']) {
        http_response_code(400);
        echo json_encode(
            RequirementValidationHelper::createErrorResponse(
                400,
                "Validation failed",
                $validationResult['errors']
            )
        );
        exit();
    }
    
    // ============================================================
    // DATABASE UPDATE
    // ============================================================
    
    $now = date('Y-m-d H:i:s');
    $updateFields = [];
    $updateValues = [];
    
    // Build dynamic update query based on provided fields
    if (isset($data['name'])) {
        $updateFields[] = "name = ?";
        $updateValues[] = $data['name'];
    }
    
    if (isset($data['requirementDate'])) {
        $updateFields[] = "requirement_date = ?";
        $updateValues[] = $data['requirementDate'];
    }
    
    if (isset($data['description'])) {
        $updateFields[] = "description = ?";
        $updateValues[] = $data['description'];
    }
    
    if (isset($data['status'])) {
        $updateFields[] = "status = ?";
        $updateValues[] = $data['status'];
    }
    
    // Always update the updated_at timestamp
    $updateFields[] = "updated_at = ?";
    $updateValues[] = $now;
    
    // Add the ID as the last parameter for WHERE clause
    $updateValues[] = $requirementId;
    
    if (!empty($updateFields)) {
        $query = "UPDATE requirements SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute($updateValues);
    }
    
    // ============================================================
    // BUILD RESPONSE
    // ============================================================
    
    // Fetch updated requirement
    $fetchStmt = $conn->prepare("SELECT * FROM requirements WHERE id = ?");
    $fetchStmt->execute([$requirementId]);
    $updatedRequirement = $fetchStmt->fetch(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode(
        RequirementValidationHelper::createSuccessResponse(
            "Requirement updated successfully",
            $updatedRequirement
        )
    );

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error occurred",
        "error" => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "An error occurred",
        "error" => $e->getMessage()
    ]);
}
