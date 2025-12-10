<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['title']) || !isset($data['description']) || !isset($data['questions'])) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Update quiz
    $stmt = $pdo->prepare("UPDATE quizzes SET title = :title, description = :description WHERE id = :id");
    $stmt->execute([
        'title' => $data['title'],
        'description' => $data['description'],
        'id' => $data['id']
    ]);

    // Delete old questions & options
    $stmt = $pdo->prepare("SELECT id FROM questions WHERE quiz_id = :id");
    $stmt->execute(['id' => $data['id']]);
    $oldQuestions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $pdo->prepare("DELETE FROM options WHERE question_id = :id")->execute(['id' => $oldQuestions]);
    $pdo->prepare("DELETE FROM questions WHERE quiz_id = :id")->execute(['id' => $data['id']]);

    // Insert updated questions
    $stmtQ = $pdo->prepare("INSERT INTO questions (quiz_id, text, correct_answer) VALUES (:quiz_id, :text, :correct_answer)");
    $stmtO = $pdo->prepare("INSERT INTO options (question_id, option_text) VALUES (:question_id, :option_text)");

    foreach ($data['questions'] as $q) {
        $stmtQ->execute([
            'quiz_id' => $data['id'],
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

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error']);
}