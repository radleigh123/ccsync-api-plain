<?php

require_once __DIR__ . '/../config/database/db.php';

/**
 * Fetch events for the current month
 * 
 * Returns only events where event_date falls within the current month and year
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
    // Get current month and year
    $currentMonth = date('m');
    $currentYear = date('Y');
    
    // Build the query to get events for the current month
    $stmt = $conn->prepare("
        SELECT * FROM events
        WHERE YEAR(event_date) = :year
        AND MONTH(event_date) = :month
        ORDER BY event_date ASC
    ");
    
    $stmt->bindValue(':year', $currentYear, PDO::PARAM_INT);
    $stmt->bindValue(':month', $currentMonth, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "This month's events fetched successfully",
        "events" => $events,
        "month" => intval($currentMonth),
        "year" => intval($currentYear),
        "count" => count($events)
    ]);
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Query failed: " . $e->getMessage() . "\n", FILE_APPEND);

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database query failed",
        "message" => "Internal server error"
    ]);
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
?>
