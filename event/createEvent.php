<?php

require_once __DIR__ . '/../config/database/db.php';
require __DIR__ . '/../vendor/autoload.php';

/**
 * Create a new event
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
    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';
    $venue = $data['venue'] ?? '';
    $event_date = $data['event_date'] ?? '';
    $time_from = $data['time_from'] ?? '';
    $time_to = $data['time_to'] ?? '';
    $registration_start = $data['registration_start'] ?? '';
    $registration_end = $data['registration_end'] ?? '';
    $max_participants = $data['max_participants'] ?? null;
    $status = $data['status'] ?? 'upcoming';

    if (empty($name) || empty($event_date) || empty($time_from) || empty($time_to)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Missing required fields: name, event_date, time_from, time_to"
        ]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO events (name, description, venue, event_date, time_from, time_to, registration_start, registration_end, max_participants, status) VALUES (:name, :description, :venue, :event_date, :time_from, :time_to, :registration_start, :registration_end, :max_participants, :status)");
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":venue", $venue);
    $stmt->bindParam(":event_date", $event_date);
    $stmt->bindParam(":time_from", $time_from);
    $stmt->bindParam(":time_to", $time_to);
    $stmt->bindParam(":registration_start", $registration_start);
    $stmt->bindParam(":registration_end", $registration_end);
    $stmt->bindParam(":max_participants", $max_participants);
    $stmt->bindParam(":status", $status);
    $stmt->execute();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Event created successfully",
        "event" => [
            "id" => $conn->lastInsertId(),
            "name" => $name,
            "description" => $description,
            "venue" => $venue,
            "event_date" => $event_date,
            "time_from" => $time_from,
            "time_to" => $time_to,
            "registration_start" => $registration_start,
            "registration_end" => $registration_end,
            "max_participants" => $max_participants,
            "status" => $status
        ]
    ]);
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Internal server error"
    ]);
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
