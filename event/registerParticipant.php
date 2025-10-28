<?php

require_once __DIR__ . '/../config/database/db.php';
// require __DIR__ . '/../vendor/autoload.php';  // Not needed - using native PHP/PDO only

/**
 * Register a participant for an event
 * 
 * POST Parameters (JSON):
 * - event_id (required): The event ID
 * - member_id (required): The member ID to register
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
    $data = json_decode(file_get_contents("php://input"), true);
    
    $event_id = $data['event_id'] ?? null;
    $member_id = $data['member_id'] ?? null;

    if (!$event_id || !$member_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'event_id and member_id parameters are required'
        ]);
        exit();
    }

    // Check if participant is already registered
    $checkQuery = "SELECT id FROM event_registrations WHERE event_id = :event_id AND member_id = :member_id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $checkStmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'This member is already registered for this event'
        ]);
        exit();
    }

    // Check if member exists
    $memberCheckQuery = "SELECT id FROM members WHERE id = :member_id";
    $memberCheckStmt = $conn->prepare($memberCheckQuery);
    $memberCheckStmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $memberCheckStmt->execute();
    
    if ($memberCheckStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Member not found'
        ]);
        exit();
    }

    // Check if event exists
    $eventCheckQuery = "SELECT id FROM events WHERE id = :event_id";
    $eventCheckStmt = $conn->prepare($eventCheckQuery);
    $eventCheckStmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $eventCheckStmt->execute();
    
    if ($eventCheckStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Event not found'
        ]);
        exit();
    }

    // Register the participant
    $insertQuery = "
        INSERT INTO event_registrations (event_id, member_id, registered_at, created_at, updated_at)
        VALUES (:event_id, :member_id, NOW(), NOW(), NOW())
    ";
    
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->execute();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Participant registered successfully',
        'registration' => [
            'id' => $conn->lastInsertId(),
            'event_id' => $event_id,
            'member_id' => $member_id,
            'registered_at' => date('Y-m-d H:i:s')
        ]
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
