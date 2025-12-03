<?php
// Allow requests from your Vue app (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Include the database connection
require 'db.php';

// Get the JSON input from Vue
$data = json_decode(file_get_contents("php://input"), true);

// Check if we have the required fields
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing credentials']);
    exit;
}

$email = $data['email'];
$password = $data['password'];

try {
    // 1. Prepare the SQL statement (Prevents SQL Injection)
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = :email");

    // 2. Execute with the user's email
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // 3. Verify the user exists AND the password is correct
    if ($user && password_verify($password, $user['password'])) {
        // Success!
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ]);
    } else {
        // Invalid login
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>