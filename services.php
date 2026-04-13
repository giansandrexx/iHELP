<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once 'config.php';

function jsonResponse(bool $success, $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $success, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? 'services';

try {
    if ($action === 'categories') {
        $result = $conn->query("
            SELECT id, label, icon_name
            FROM categories
            WHERE is_active = 1
            ORDER BY sort_order
        ");

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        jsonResponse(true, $rows);
    }

    if ($action === 'services') {
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

        if ($categoryId) {
            $stmt = $conn->prepare("
                SELECT s.id, s.name, s.rating, s.image_path,
                       c.label AS category, c.id AS category_id
                FROM services s
                JOIN categories c ON c.id = s.category_id
                WHERE s.is_active = 1 AND s.category_id = ?
                ORDER BY s.name
            ");
            $stmt->bind_param("i", $categoryId);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("
                SELECT s.id, s.name, s.rating, s.image_path,
                       c.label AS category, c.id AS category_id
                FROM services s
                JOIN categories c ON c.id = s.category_id
                WHERE s.is_active = 1
                ORDER BY c.sort_order, s.name
            ");
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $row['id'] = (int)$row['id'];
            $row['category_id'] = (int)$row['category_id'];
            $row['rating'] = (float)$row['rating'];
            $rows[] = $row;
        }

        jsonResponse(true, $rows);
    }

    jsonResponse(false, 'Unknown action', 400);

} catch (Throwable $e) {
    jsonResponse(false, 'Server error', 500);
}