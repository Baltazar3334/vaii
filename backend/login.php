<?php
session_start(); // <--- ADD THIS at the very top

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

// Check required fields
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing credentials']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];
$action = isset($data['action']) ? $data['action'] : 'login'; // Default to login if not specified

try {
    if ($action === 'register') {
        // --- REGISTRATION LOGIC ---
        if (!isset($data['username'])) {
            echo json_encode(['success' => false, 'message' => 'Username is required for registration']);
            exit;
        }
        $username = trim($data['username']);

        // 1. Check if user already exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
        $checkStmt->execute(['email' => $email, 'username' => $username]);
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email or Username already exists']);
            exit;
        }

        // 2. Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert new user
        $insertStmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $result = $insertStmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ]);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful',
                'user' => [
                    'id' => $pdo->lastInsertId(),
                    'username' => $username
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed']);
        }

    } else {
        // --- LOGIN LOGIC ---
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // SAVE TO SESSION
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

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
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>