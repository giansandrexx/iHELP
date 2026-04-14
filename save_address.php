<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php';

$data        = json_decode(file_get_contents('php://input'), true);
$user_id     = $data['user_id']     ?? '';
$label       = $data['label']       ?? '';
$full_address= $data['full_address']?? '';
$street      = $data['street']      ?? '';
$barangay    = $data['barangay']    ?? '';
$city        = $data['city']        ?? '';
$province    = $data['province']    ?? '';
$latitude    = $data['latitude']    ?? '';
$longitude   = $data['longitude']   ?? '';
$is_default  = $data['is_default']  ?? 0;

if (empty($user_id) || empty($full_address) || empty($latitude) || empty($longitude)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if ($is_default == 1) {
    $reset = $conn->prepare('UPDATE user_addresses SET is_default = 0 WHERE user_id = ?');
    if ($reset) {
        $reset->bind_param('i', $user_id);
        $reset->execute();
    }
}

$stmt = $conn->prepare(
    'INSERT INTO user_addresses 
        (user_id, label, full_address, street, barangay, city, province, latitude, longitude, is_default) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$stmt->bind_param(
    'issssssddi',
    $user_id,
    $label,
    $full_address,
    $street,
    $barangay,
    $city,
    $province,
    $latitude,
    $longitude,
    $is_default
);

if ($stmt->execute()) {
    $new_id = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Address saved successfully',
        'address' => [
            'id'           => $new_id,
            'user_id'      => $user_id,
            'label'        => $label,
            'full_address' => $full_address,
            'street'       => $street,
            'barangay'     => $barangay,
            'city'         => $city,
            'province'     => $province,
            'latitude'     => $latitude,
            'longitude'    => $longitude,
            'is_default'   => $is_default,
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$conn->close();
?>