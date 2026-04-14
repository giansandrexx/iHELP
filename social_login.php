<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php';

$data     = json_decode(file_get_contents('php://input'), true);
$provider = $data['provider'] ?? '';
$token    = $data['token']    ?? '';

if (empty($provider) || empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$email = '';

if ($provider === 'google') {
    $url        = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $token;
    $response   = file_get_contents($url);
    $googleData = json_decode($response, true);

    if (!$googleData || isset($googleData['error'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid Google token']);
        exit;
    }

    $email = $googleData['email'];
}

// ── Fetch existing user (now includes id) ─────────────────────
$stmt = $conn->prepare('SELECT id, first_name, last_name, email, phone FROM users WHERE email = ?');

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();

if ($user) {
    echo json_encode([
        'success'     => true,
        'is_new_user' => false,
        'user' => [
            'id'         => $user['id'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
            'phone'      => $user['phone'],
        ]
    ]);
} else {
    $stmt = $conn->prepare('INSERT INTO users (email, password, provider) VALUES (?, ?, ?)');

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => $conn->error]);
        exit;
    }

    $emptyPassword = '';
    $stmt->bind_param('sss', $email, $emptyPassword, $provider);
    $stmt->execute();
    $newId = $conn->insert_id;

    echo json_encode([
        'success'     => true,
        'is_new_user' => true,
        'user' => [
            'id'         => $newId,
            'first_name' => '',
            'last_name'  => '',
            'email'      => $email,
            'phone'      => null,
        ]
    ]);
}

$conn->close();
?>
