<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require 'db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing quiz ID']);
    exit;
}

$id = $_GET['id'];

try {
    // Get quiz
    $stmt = $pdo->prepare("SELECT id, title, description FROM quizzes WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found']);
        exit;
    }

    // Get questions
    $stmtQ = $pdo->prepare("SELECT id, text, correct_answer FROM questions WHERE quiz_id = :quiz_id");
    $stmtQ->execute(['quiz_id' => $id]);
    $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    // Get options for each question
    foreach ($questions as &$q) {
        $stmtO = $pdo->prepare("SELECT option_text FROM options WHERE question_id = :question_id");
        $stmtO->execute(['question_id' => $q['id']]);
        $q['options'] = $stmtO->fetchAll(PDO::FETCH_COLUMN);
    }

    echo json_encode(['success' => true, 'quiz' => $quiz, 'questions' => $questions]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
