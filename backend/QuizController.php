<?php

class QuizController {
    private $db;

    // Inicializácia kontroléra s pripojením na databázu
    public function __construct($db) {
        $this->db = $db;
    }

    // Získanie všetkých verejných kvízov vrátane mena autora a počtu otázok
    public function getAllPublic() {
        $stmt = $this->db->query("
            SELECT q.*, u.username as author, 
                   (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as questions
            FROM quizzes q JOIN users u ON q.user_id = u.id
            WHERE q.is_public = 1 ORDER BY q.created_at DESC
        ");
        return ['success' => true, 'quizzes' => $stmt->fetchAll()];
    }

    // Získanie kvízov konkrétneho používateľa a jeho celkových štatistík
    public function getUserQuizzes($userId) {
        $stmt = $this->db->prepare("
            SELECT q.*, (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
            FROM quizzes q WHERE q.user_id = :uid ORDER BY q.created_at DESC
        ");
        $stmt->execute(['uid' => $userId]);
        $quizzes = $stmt->fetchAll();

        // Načítanie základných údajov o autorovi profilu
        $uStmt = $this->db->prepare("SELECT username, avatar_url FROM users WHERE id = :uid");
        $uStmt->execute(['uid' => $userId]);
        $user = $uStmt->fetch();

        // Výpočet celkového počtu odohraní všetkých kvízov používateľa
        $totalPlays = 0;
        foreach ($quizzes as $quiz) {
            $totalPlays += (int)($quiz['plays_count'] ?? 0);
        }

        return [
            'success' => true,
            'quizzes' => $quizzes,
            'total_plays' => $totalPlays,
            'username' => $user ? $user['username'] : 'Unknown',
            'avatar_url' => $user ? $user['avatar_url'] : null
        ];
    }

    // Zvýšenie počítadla odohraní kvízu o jedna
    public function incrementPlays($quizId) {
        $stmt = $this->db->prepare("UPDATE quizzes SET plays_count = plays_count + 1 WHERE id = :id");
        $stmt->execute(['id' => $quizId]);
        return ['success' => true];
    }

    // Načítanie kompletných detailov kvízu vrátane všetkých otázok a ich možností
    public function getDetails($id) {
        $stmt = $this->db->prepare("SELECT id, title, description, image_url FROM quizzes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $quiz = $stmt->fetch();
        if (!$quiz) return ['success' => false, 'message' => 'Quiz not found'];

        // Načítanie otázok prislúchajúcich ku kvízu
        $qStmt = $this->db->prepare("SELECT id, question_text, correct_option_index FROM questions WHERE quiz_id = :id");
        $qStmt->execute(['id' => $id]);
        $questions = $qStmt->fetchAll();

        // Pre každú otázku načítame jej textové možnosti
        foreach ($questions as &$q) {
            $oStmt = $this->db->prepare("SELECT option_text FROM question_options WHERE question_id = :qid");
            $oStmt->execute(['qid' => $q['id']]);
            $q['options'] = $oStmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return ['success' => true, 'quiz' => $quiz, 'questions' => $questions];
    }

    // Uloženie nového kvízu alebo aktualizácia existujúceho pomocou transakcie
    public function saveQuiz($data, $quizId = null) {
        try {
            $this->db->beginTransaction();
            if ($quizId) {
                // Aktualizácia základných informácií existujúceho kvízu
                $stmt = $this->db->prepare("UPDATE quizzes SET title = :t, description = :d, is_public = :p, image_url = :img WHERE id = :id AND user_id = :uid");
                $stmt->execute([
                    't' => $data['title'],
                    'd' => $data['description'],
                    'p' => $data['is_public'] ? 1 : 0,
                    'img' => $data['image_url'] ?? null,
                    'id' => $quizId,
                    'uid' => $data['user_id']
                ]);
                // Vymazanie starých otázok (možnosti sa zmažú vďaka ON DELETE CASCADE v DB)
                $stmtDel = $this->db->prepare("DELETE FROM questions WHERE quiz_id = :id");
                $stmtDel->execute(['id' => $quizId]);
            } else {
                // Vytvorenie nového záznamu v tabuľke quizzes
                $stmt = $this->db->prepare("INSERT INTO quizzes (user_id, title, description, is_public, image_url) VALUES (:uid, :t, :d, :p, :img)");
                $stmt->execute([
                    'uid' => $data['user_id'],
                    't' => $data['title'],
                    'd' => $data['description'],
                    'p' => $data['is_public'] ? 1 : 0,
                    'img' => $data['image_url'] ?? null
                ]);
                $quizId = $this->db->lastInsertId();
            }

            // Hromadné vkladanie nových otázok a ich možností
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
            // V prípade chyby vrátime zmeny v DB späť
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Odstránenie konkrétneho kvízu (povolené len pre autora)
    public function delete($quizId, $userId) {
        $stmt = $this->db->prepare("DELETE FROM quizzes WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $quizId, 'uid' => $userId]);
        return ['success' => $stmt->rowCount() > 0];
    }

    // Odstránenie všetkých kvízov používateľa
    public function resetAccount($userId) {
        $stmt = $this->db->prepare("DELETE FROM quizzes WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        return ['success' => true];
    }

    // Získanie rebríčka používateľov podľa úspešnosti a aktivity
    public function getLeaderboard() {
        $sql = "
            SELECT 
                u.id as user_id,
                u.username,
                u.avatar_url,
                COUNT(DISTINCT q.id) as quizzes_created,
                IFNULL(SUM(q.plays_count), 0) as total_plays_received,
                (SELECT COUNT(*) FROM questions qst JOIN quizzes q2 ON qst.quiz_id = q2.id WHERE q2.user_id = u.id) as total_questions
            FROM users u
            LEFT JOIN quizzes q ON u.id = q.user_id
            GROUP BY u.id
            ORDER BY total_plays_received DESC
        ";
        $stmt = $this->db->query($sql);
        return ['success' => true, 'leaderboard' => $stmt->fetchAll()];
    }
}