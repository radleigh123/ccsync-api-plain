<?php

require_once __DIR__ . '/../config/database/db.php';

/**
 * Fetch all requirements with pagination and filtering
 * 
 * Query Parameters:
 * - page: Page number (default: 1)
 * - limit: Items per page (default: 20, max: 100)
 * - status: Filter by status (open|closed|archived, optional)
 * 
 * Response includes:
 * - Paginated requirements array
 * - Compliance statistics for each requirement (complied, notComplied, pending, total)
 * - Pagination metadata
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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
    
    // Get optional status filter
    $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : null;
    $allowedStatuses = ['open', 'closed', 'archived'];

    // Build base query
    $countQuery = "SELECT COUNT(*) as total FROM requirements WHERE 1=1";
    $dataQuery = "SELECT * FROM requirements WHERE 1=1";
    $params = [];

    // Add status filter if provided
    if ($statusFilter && in_array($statusFilter, $allowedStatuses, true)) {
        $countQuery .= " AND status = ?";
        $dataQuery .= " AND status = ?";
        $params[] = $statusFilter;
    }

    // Get total count
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($params);
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalRequirements = $countResult['total'];
    $totalPages = ceil($totalRequirements / $limit);

    // Get paginated requirements
    $dataQuery .= " ORDER BY requirement_date ASC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($dataQuery);
    
    // Bind parameters
    $paramIndex = 1;
    if ($statusFilter && in_array($statusFilter, $allowedStatuses, true)) {
        $stmt->bindValue($paramIndex++, $statusFilter);
    }
    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enrich each requirement with compliance statistics
    $requirementsWithStats = [];
    foreach ($requirements as $req) {
        $reqId = $req['id'];
        
        // Get compliance statistics for this requirement
        $complianceStmt = $conn->prepare("
            SELECT 
                compliance_status,
                COUNT(*) as count
            FROM requirements_compliance
            WHERE requirement_id = ?
            GROUP BY compliance_status
        ");
        $complianceStmt->execute([$reqId]);
        $complianceStats = $complianceStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Build compliance stats object
        $stats = [
            'complied' => 0,
            'notComplied' => 0,
            'pending' => 0,
            'total' => 0
        ];
        
        foreach ($complianceStats as $stat) {
            $status = $stat['compliance_status'];
            $count = intval($stat['count']);
            
            if ($status === 'complied') {
                $stats['complied'] = $count;
            } elseif ($status === 'not_complied') {
                $stats['notComplied'] = $count;
            } elseif ($status === 'pending') {
                $stats['pending'] = $count;
            }
            
            $stats['total'] += $count;
        }
        
        // Add stats to requirement
        $req['complianceStats'] = $stats;
        $requirementsWithStats[] = $req;
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "requirements" => $requirementsWithStats,
        "pagination" => [
            "page" => $page,
            "limit" => $limit,
            "total" => $totalRequirements,
            "pages" => $totalPages,
            "hasNext" => $page < $totalPages,
            "hasPrev" => $page > 1
        ]
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
