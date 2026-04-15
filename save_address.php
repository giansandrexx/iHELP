<?php
header('Content-Type: application/json');
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

$user_id      = $data['user_id'] ?? '';
$address_id   = $data['address_id'] ?? null;
$label        = $data['label'] ?? '';
$full_address = $data['full_address'] ?? '';
$street       = $data['street'] ?? '';
$barangay     = $data['barangay'] ?? '';
$city         = $data['city'] ?? '';
$province     = $data['province'] ?? '';
$latitude     = $data['latitude'] ?? '';
$longitude    = $data['longitude'] ?? '';
$is_default   = $data['is_default'] ?? 0;

if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

if ($is_default == 1) {
    $reset = $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
    $reset->bind_param("i", $user_id);
    $reset->execute();
}

if (!empty($address_id)) {

    $stmt = $conn->prepare("
        UPDATE user_addresses 
        SET is_default = ? 
        WHERE id = ? AND user_id = ?
    ");

    $stmt->bind_param("iii", $is_default, $address_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Address updated']);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $conn->close();
    exit;
}

if (empty($full_address) || empty($latitude) || empty($longitude)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO user_addresses 
    (user_id, label, full_address, street, barangay, city, province, latitude, longitude, is_default)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "issssssddi",
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
    echo json_encode([
        'success' => true,
        'message' => 'Address saved',
        'address_id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$conn->close();
?>
