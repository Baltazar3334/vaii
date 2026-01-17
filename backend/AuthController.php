<?php
class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, username, password FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return ['success' => true, 'user' => ['id' => $user['id'], 'username' => $user['username']]];
        }
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    public function register($username, $email, $password) {
        $check = $this->db->prepare("SELECT id FROM users WHERE email = :e OR username = :u");
        $check->execute(['e' => $email, 'u' => $username]);
        if ($check->rowCount() > 0) return ['success' => false, 'message' => 'User already exists'];

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (:u, :e, :p)");
        $stmt->execute(['u' => $username, 'e' => $email, 'p' => $hash]);
        return ['success' => true, 'user' => ['id' => $this->db->lastInsertId(), 'username' => $username]];
    }
}
