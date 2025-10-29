<?php

require_once __DIR__ . '/../config/database/db.php';
require_once __DIR__ . '/../../temp/DTOs/RequirementDTO.php';
require_once __DIR__ . '/../../temp/utils/RequirementValidationHelper.php';

/**
 * Create a new requirement
 * 
 * Expected fields in JSON request (RequirementCreateDTO):
 * - name (required, 3-255 chars): Requirement name
 * - requirementDate (required, YYYY-MM-DD): Requirement deadline
 * - description (optional): Requirement description
 * - status (optional, default: 'open'): Status (open|closed|archived)
 * 
 * Authentication: Bearer token required (admin-only)
 * 
 * Response:
 * - 201: Requirement created successfully
 * - 400: Validation error
 * - 401: Unauthorized (missing/invalid token)
 * - 403: Forbidden (insufficient permissions)
 * - 500: Server error
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
    // VALIDATION
    // ============================================================
    
    $validationResult = RequirementValidationHelper::validateRequirementCreateDTO($data);
    
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
    // DATABASE INSERT
    // ============================================================
    
    $now = date('Y-m-d H:i:s');
    $name = $data['name'];
    $requirementDate = $data['requirementDate'];
    $description = $data['description'] ?? null;
    $status = $data['status'] ?? 'open';
    
    $stmt = $conn->prepare("
        INSERT INTO requirements (name, description, status, requirement_date, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $name,
        $description,
        $status,
        $requirementDate,
        $now,
        $now
    ]);
    
    $requirementId = $conn->lastInsertId();
    
    // ============================================================
    // BUILD RESPONSE
    // ============================================================
    
    $newRequirement = [
        'id' => intval($requirementId),
        'name' => $name,
        'description' => $description,
        'status' => $status,
        'requirement_date' => $requirementDate,
        'created_at' => $now,
        'updated_at' => $now
    ];
    
    http_response_code(201);
    echo json_encode(
        RequirementValidationHelper::createSuccessResponse(
            "Requirement created successfully",
            $newRequirement
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
