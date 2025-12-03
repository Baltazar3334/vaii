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

// Basic Validation
if (!isset($data['user_id']) || !isset($data['title']) || !isset($data['questions'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Start Transaction (All or Nothing)
    $pdo->beginTransaction();

    // 1. Insert Quiz
    $stmt = $pdo->prepare("INSERT INTO quizzes (user_id, title, description) VALUES (:user_id, :title, :description)");
    $stmt->execute([
        'user_id' => $data['user_id'],
        'title' => $data['title'],
        'description' => $data['description'] ?? ''
    ]);

    $quizId = $pdo->lastInsertId();

    // 2. Loop through Questions
    foreach ($data['questions'] as $q) {
        // Insert Question
        $qStmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, correct_option_index) VALUES (:quiz_id, :text, :correct_index)");
        $qStmt->execute([
            'quiz_id' => $quizId,
            'text' => $q['text'],
            'correct_index' => $q['correctAnswer']
        ]);

        $questionId = $pdo->lastInsertId();

        // 3. Loop through Options for this Question
        foreach ($q['options'] as $optionText) {
            $optStmt = $pdo->prepare("INSERT INTO question_options (question_id, option_text) VALUES (:qid, :text)");
            $optStmt->execute([
                'qid' => $questionId,
                'text' => $optionText
            ]);
        }
    }

    // Commit the transaction (Save everything)
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Quiz created successfully', 'quiz_id' => $quizId]);

} catch (Exception $e) {
    // Something went wrong, undo changes
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Failed to save quiz: ' . $e->getMessage()]);
}
?>