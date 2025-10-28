<?php

require_once __DIR__ . '/../config/database/db.php';

/**
 * Fetch all users (CCS Students)
 * 
 * Returns a count of all users with role='user' to represent total CCS students
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
    // Get pagination parameters from query string
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20; // Max 100, default 20
    $offset = ($page - 1) * $limit;

    // Get total count of users with role='user'
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $countStmt->execute();
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $countResult['total'];
    $totalPages = ceil($totalUsers / $limit);

    // Get paginated users
    $stmt = $conn->prepare("
        SELECT 
            id,
            name,
            email,
            email_verified_at,
            id_school_number,
            role,
            created_at,
            updated_at
        FROM users 
        WHERE role = 'user'
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Users fetched successfully",
        "users" => $users,
        "totalCount" => $totalUsers,
        "pagination" => [
            "page" => $page,
            "limit" => $limit,
            "total" => $totalUsers,
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
?>
