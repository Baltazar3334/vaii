
<?php

class Database {
    // Statická inštancia pre implementáciu vzoru Singleton
    private static $instance = null;
    private $conn;

    // Súkromný konštruktor na zabránenie vytváraniu viacerých inštancií
    private function __construct() {
        // Konfiguračné údaje pre pripojenie k MySQL (zhodné s docker-compose)
        $host = 'mysql_db';
        $db   = 'quiz_app';
        $user = 'root';
        $pass = 'root';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        // Nastavenia pre bezpečnejšie a pohodlnejšie spracovanie chýb a dát
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Vyhadzovanie výnimiek pri chybách
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Výsledky dotazov ako asociatívne polia
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Použitie natívnych prepared statements
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // V prípade chyby pripojenia vráti kód 500
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit;
        }
    }

    // Verejná metóda na získanie jedinej inštancie databázového pripojenia
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->getConnection();
    }

    // Metóda na prístup k samotnému PDO objektu
    public function getConnection() {
        return $this->conn;
    }
}