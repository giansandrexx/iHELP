<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php';

$data       = json_decode(file_get_contents("php://input"), true);
$email      = $data['email']      ?? '';
$first_name = $data['first_name'] ?? '';
$last_name  = $data['last_name']  ?? '';
$phone      = $data['phone']      ?? '';

if (empty($email) || empty($first_name) || empty($phone)) {
    echo json_encode(["success" => false, "message" => "Missing data"]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE email = ?");

if (!$stmt) {
    echo json_encode(["success" => false, "message" => $conn->error]);
    exit;
}

$stmt->bind_param("ssss", $first_name, $last_name, $phone, $email);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Profile saved"]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$conn->close();
?>