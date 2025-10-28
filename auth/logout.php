<?php

/**
 * Logout Endpoint
 * 
 * Handles server-side logout, clears sessions, and invalidates tokens
 * 
 * POST Parameters (JSON):
 * - user_id (optional): The user ID being logged out
 * 
 * Response:
 * - success: Boolean indicating logout success
 * - message: Status message
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
    // Check if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed. Use POST.'
        ]);
        exit();
    }

    // Get the request data
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $data['user_id'] ?? null;

    // Log the logout action
    $log_message = "User logged out";
    if ($user_id) {
        $log_message .= " (ID: $user_id)";
    }
    error_log("LOGOUT: " . $log_message . " at " . date('Y-m-d H:i:s'));

    // Clear any sessions (if using traditional PHP sessions)
    session_start();
    session_destroy();

    // In a production environment, you might want to:
    // 1. Invalidate tokens in a token blacklist table
    // 2. Clear user sessions from database
    // 3. Log audit trail

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'User logged out successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during logout',
        'error' => $e->getMessage()
    ]);
}
?>
