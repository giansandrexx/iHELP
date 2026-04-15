<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

$address_id = $data['address_id'] ?? '';

if (empty($address_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing address_id']);
    exit;
}

$stmt = $conn->prepare('DELETE FROM user_addresses WHERE id = ?');

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$stmt->bind_param('i', $address_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Address deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Address not found']);
}

$stmt->close();
$conn->close();
?>