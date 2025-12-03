<?php
// Allow requests from your Vue app (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // This brings in the $pdo variable ready to use
    require 'db.php';
    /** @var PDO $pdo */ // <-- Add this line to silence the warning
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing credentials']);
    exit;
}

$email = $data['email'];
$password = $data['password'];

try {
    // Use $pdo directly (it comes from db.php)
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>