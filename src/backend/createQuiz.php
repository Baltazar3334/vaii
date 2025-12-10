<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['title']) || !isset($data['description']) || !isset($data['questions'])) {
    echo json_encode(['success' => false, 'message' => 'Missing quiz data']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Insert quiz
    $stmt = $pdo->prepare("INSERT INTO quizzes (title, description) VALUES (:title, :description)");
    $stmt->execute([
        'title' => $data['title'],
        'description' => $data['description']
    ]);

    $quizId = $pdo->lastInsertId();

    // Insert questions
    $stmtQ = $pdo->prepare("INSERT INTO questions (quiz_id, text, correct_answer) VALUES (:quiz_id, :text, :correct_answer)");
    $stmtO = $pdo->prepare("INSERT INTO options (question_id, option_text) VALUES (:question_id, :option_text)");

    foreach ($data['questions'] as $q) {
        $stmtQ->execute([
            'quiz_id' => $quizId,
            'text' => $q['text'],
            'correct_answer' => $q['correctAnswer']
        ]);

        $questionId = $pdo->lastInsertId();

        foreach ($q['options'] as $opt) {
            $stmtO->execute([
                'question_id' => $questionId,
                'option_text' => $opt
            ]);
        }
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'quizId' => $quizId]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
