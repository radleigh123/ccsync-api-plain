<?php

require_once __DIR__ . '/../config/database/db.php';
// require __DIR__ . '/../vendor/autoload.php';  // Not needed - using native PHP/PDO only

/**
 * Fetch all members with their associated programs
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5137");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get pagination parameters from query string
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20; // Max 100, default 20
    $offset = ($page - 1) * $limit;

    // Get total count
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM members");
    $countStmt->execute();
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalMembers = $countResult['total'];
    $totalPages = ceil($totalMembers / $limit);

    // Get paginated members
    $stmt = $conn->prepare("SELECT m.*, p.name AS program_name FROM members m LEFT JOIN programs p ON m.program COLLATE utf8mb4_unicode_ci = p.code COLLATE utf8mb4_unicode_ci LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "members" => $members,
        "pagination" => [
            "page" => $page,
            "limit" => $limit,
            "total" => $totalMembers,
            "pages" => $totalPages,
            "hasNext" => $page < $totalPages,
            "hasPrev" => $page > 1
        ]
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
