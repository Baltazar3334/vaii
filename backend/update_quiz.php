<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['quiz_id']) || !isset($data['user_id']) || !isset($data['title'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$quizId = $data['quiz_id'];
$userId = $data['user_id'];
$title = trim($data['title']);
$description = isset($data['description']) ? trim($data['description']) : '';

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title cannot be empty']);
    exit;
}

try {
    // 1. Verify ownership (Security check)
    $checkStmt = $pdo->prepare("SELECT id FROM quizzes WHERE id = :quiz_id AND user_id = :user_id");
    $checkStmt->execute(['quiz_id' => $quizId, 'user_id' => $userId]);

    if ($checkStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found or unauthorized']);
        exit;
    }

    // 2. Update the quiz
    $updateStmt = $pdo->prepare("UPDATE quizzes SET title = :title, description = :description WHERE id = :quiz_id");
    $result = $updateStmt->execute([
        'title' => $title,
        'description' => $description,
        'quiz_id' => $quizId
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Quiz updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update quiz']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>