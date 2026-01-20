<?php

class AuthController {
    private $db;

    // Inicializácia kontroléra s inštanciou databázy
    public function __construct($db) {
        $this->db = $db;
    }

    // Overenie prihlasovacích údajov používateľa
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, username, password, avatar_url FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Kontrola existencie používateľa a správnosti hashovaného hesla
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'avatar_url' => $user['avatar_url']
                ]
            ];
        }
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    // Registrácia nového používateľa s kontrolou unikátnosti údajov
    public function register($username, $email, $password) {
        $check = $this->db->prepare("SELECT id FROM users WHERE email = :e OR username = :u");
        $check->execute(['e' => $email, 'u' => $username]);

        if ($check->rowCount() > 0) {
            return ['success' => false, 'message' => 'User already exists'];
        }

        // Bezpečné hashovanie hesla pred uložením
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (:u, :e, :p)");
        $stmt->execute(['u' => $username, 'e' => $email, 'p' => $hash]);

        return ['success' => true, 'user' => ['id' => $this->db->lastInsertId(), 'username' => $username]];
    }

    // Aktualizácia URL adresy profilového obrázka
    public function updateAvatar($userId, $url) {
        $stmt = $this->db->prepare("UPDATE users SET avatar_url = :url WHERE id = :id");
        $stmt->execute(['url' => $url, 'id' => $userId]);
        return ['success' => true, 'avatar_url' => $url];
    }

    // Zmena používateľského mena s overením dostupnosti
    public function updateUsername($userId, $newUsername) {
        $check = $this->db->prepare("SELECT id FROM users WHERE username = :u AND id != :id");
        $check->execute(['u' => $newUsername, 'id' => $userId]);

        if ($check->rowCount() > 0) {
            return ['success' => false, 'message' => 'Username already taken'];
        }

        $stmt = $this->db->prepare("UPDATE users SET username = :u WHERE id = :id");
        $stmt->execute(['u' => $newUsername, 'id' => $userId]);

        // Aktualizácia mena aj v aktuálnej relácii
        $_SESSION['username'] = $newUsername;
        return ['success' => true, 'username' => $newUsername];
    }

    // Zmena hesla používateľa (opätovné hashovanie)
    public function updatePassword($userId, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = :p WHERE id = :id");
        $stmt->execute(['p' => $hash, 'id' => $userId]);
        return ['success' => true, 'message' => 'Password updated'];
    }
}