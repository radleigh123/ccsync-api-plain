<?php

require_once __DIR__ . '/../config/database/db.php';
// require __DIR__ . '/../vendor/autoload.php';  // Not needed - using native PHP/PDO only

/**
 * Check if a member is registered for a specific event
 * 
 * Query Parameters:
 * - event_id (required): The event ID
 * - member_id (required): The member ID
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
    $event_id = $_GET['event_id'] ?? null;
    $member_id = $_GET['member_id'] ?? null;

    if (!$event_id || !$member_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'event_id and member_id parameters are required'
        ]);
        exit();
    }

    // Check if member is registered for this specific event
    $query = "
        SELECT id FROM event_registrations 
        WHERE event_id = :event_id AND member_id = :member_id
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $isRegistered = $stmt->rowCount() > 0;

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'is_registered' => $isRegistered,
        'event_id' => $event_id,
        'member_id' => $member_id
    ]);

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
