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
    $pdo->beginTransaction();

    // Delete options → questions → quiz
    $stmt = $pdo->prepare("SELECT id FROM questions WHERE quiz_id = :id");
    $stmt->execute(['id' => $id]);
    $questions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($questions as $qId) {
        $pdo->prepare("DELETE FROM options WHERE question_id = :id")->execute(['id' => $qId]);
    }

    $pdo->prepare("DELETE FROM questions WHERE quiz_id = :id")->execute(['id' => $id]);
    $pdo->prepare("DELETE FROM quizzes WHERE id = :id")->execute(['id' => $id]);

    $pdo->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
