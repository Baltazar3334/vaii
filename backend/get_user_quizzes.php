<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require 'db.php';
    /** @var PDO $pdo */
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check for user_id in query parameters (e.g., ?user_id=1)
if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

$userId = $_GET['user_id'];

try {
    // Fetch quizzes for this user
    $stmt = $pdo->prepare("
        SELECT 
            q.id, 
            q.title, 
            q.description, 
            q.created_at,
            (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
        FROM quizzes q 
        WHERE q.user_id = :user_id 
        ORDER BY q.created_at DESC
    ");
    
    $stmt->execute(['user_id' => $userId]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'quizzes' => $quizzes
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>