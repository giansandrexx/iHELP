<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php';

$user_id = $_GET['user_id'] ?? '';

if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$stmt = $conn->prepare(
    'SELECT id, user_id, label, full_address, street, barangay, city, province,
            latitude, longitude, is_default, created_at
     FROM user_addresses
     WHERE user_id = ?
     ORDER BY is_default DESC, id DESC'
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$addresses = [];
while ($row = $result->fetch_assoc()) {
    $addresses[] = [
        'id'           => (int)$row['id'],
        'user_id'      => (int)$row['user_id'],
        'label'        => $row['label'],
        'full_address' => $row['full_address'],
        'street'       => $row['street'],
        'barangay'     => $row['barangay'],
        'city'         => $row['city'],
        'province'     => $row['province'],
        'latitude'     => (float)$row['latitude'],
        'longitude'    => (float)$row['longitude'],
        'is_default'   => (int)$row['is_default'],
        'created_at'   => $row['created_at'],
    ];
}

echo json_encode([
    'success'   => true,
    'addresses' => $addresses,
]);

$conn->close();
?>