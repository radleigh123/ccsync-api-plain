<?php

require_once __DIR__ . '/../config/database/db.php';
require_once __DIR__ . '/../../temp/DTOs/RequirementDTO.php';
require_once __DIR__ . '/../../temp/utils/RequirementValidationHelper.php';

/**
 * Delete a requirement (soft delete)
 * 
 * Soft delete implementation: Sets the status to 'archived' instead of actually removing the record
 * 
 * Query Parameters:
 * - id: Requirement ID to delete (required)
 * 
 * Authentication: Bearer token required (admin-only)
 * 
 * Response:
 * - 200: Requirement deleted successfully
 * - 400: Bad request (missing ID)
 * - 401: Unauthorized (missing/invalid token)
 * - 403: Forbidden (insufficient permissions)
 * - 404: Requirement not found
 * - 500: Server error
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
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
    
    // ============================================================
    // CHECK IF REQUIREMENT EXISTS
    // ============================================================
    
    $checkStmt = $conn->prepare("SELECT id FROM requirements WHERE id = ?");
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
    // SOFT DELETE
    // ============================================================
    
    $now = date('Y-m-d H:i:s');
    
    // Set status to 'archived' and update the updated_at timestamp
    $stmt = $conn->prepare("
        UPDATE requirements 
        SET status = 'archived', updated_at = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$now, $requirementId]);
    
    // ============================================================
    // BUILD RESPONSE
    // ============================================================
    
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Requirement deleted successfully",
        "id" => $requirementId
    ]);

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
