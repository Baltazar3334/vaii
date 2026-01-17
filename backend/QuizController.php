<?php
class QuizController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllPublic() {
        $stmt = $this->db->query("
            SELECT q.*, u.username as author, (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as questions
            FROM quizzes q JOIN users u ON q.user_id = u.id
            WHERE q.is_public = 1 ORDER BY q.created_at DESC
        ");
        return ['success' => true, 'quizzes' => $stmt->fetchAll()];
    }

    public function getUserQuizzes($userId) {
        $stmt = $this->db->prepare("
            SELECT q.*, (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
            FROM quizzes q WHERE q.user_id = :uid ORDER BY q.created_at DESC
        ");
        $stmt->execute(['uid' => $userId]);
        return ['success' => true, 'quizzes' => $stmt->fetchAll()];
    }

    public function getDetails($id) {
        $stmt = $this->db->prepare("SELECT id, title, description FROM quizzes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $quiz = $stmt->fetch();
        if (!$quiz) return ['success' => false, 'message' => 'Quiz not found'];

        $qStmt = $this->db->prepare("SELECT id, question_text, correct_option_index FROM questions WHERE quiz_id = :id");
        $qStmt->execute(['id' => $id]);
        $questions = $qStmt->fetchAll();

        foreach ($questions as &$q) {
            $oStmt = $this->db->prepare("SELECT option_text FROM question_options WHERE question_id = :qid");
            $oStmt->execute(['qid' => $q['id']]);
            $q['options'] = $oStmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return ['success' => true, 'quiz' => $quiz, 'questions' => $questions];
    }

    public function saveQuiz($data, $quizId = null) {
        try {
            $this->db->beginTransaction();
            if ($quizId) {
                $stmt = $this->db->prepare("UPDATE quizzes SET title = :t, description = :d, is_public = :p WHERE id = :id AND user_id = :uid");
                $stmt->execute(['t' => $data['title'], 'd' => $data['description'], 'p' => $data['is_public'] ? 1 : 0, 'id' => $quizId, 'uid' => $data['user_id']]);
                $stmtDel = $this->db->prepare("DELETE FROM questions WHERE quiz_id = :id");
                $stmtDel->execute(['id' => $quizId]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO quizzes (user_id, title, description, is_public) VALUES (:uid, :t, :d, :p)");
                $stmt->execute(['uid' => $data['user_id'], 't' => $data['title'], 'd' => $data['description'], 'p' => $data['is_public'] ? 1 : 0]);
                $quizId = $this->db->lastInsertId();
            }

            foreach ($data['questions'] as $q) {
                $qStmt = $this->db->prepare("INSERT INTO questions (quiz_id, question_text, correct_option_index) VALUES (:qid, :txt, :corr)");
                $qStmt->execute(['qid' => $quizId, 'txt' => $q['text'], 'corr' => $q['correctAnswer']]);
                $questionId = $this->db->lastInsertId();
                foreach ($q['options'] as $opt) {
                    $oStmt = $this->db->prepare("INSERT INTO question_options (question_id, option_text) VALUES (:qid, :txt)");
                    $oStmt->execute(['qid' => $questionId, 'txt' => $opt]);
                }
            }
            $this->db->commit();
            return ['success' => true, 'quiz_id' => $quizId];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function delete($quizId, $userId) {
        $stmt = $this->db->prepare("DELETE FROM quizzes WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $quizId, 'uid' => $userId]);
        return ['success' => $stmt->rowCount() > 0];
    }

    public function resetAccount($userId) {
        $stmt = $this->db->prepare("DELETE FROM quizzes WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        return ['success' => true];
    }
}