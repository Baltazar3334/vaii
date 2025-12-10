<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require 'db.php';

try {
    $stmt = $pdo->query("SELECT id, title, description FROM quizzes");
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'quizzes' => $quizzes]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}