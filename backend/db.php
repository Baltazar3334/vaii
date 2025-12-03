<?php
// Configuration for your Docker database
$host = 'mysql_db'; // UPDATED: Use the service name from docker-compose
$db   = 'quiz_app';  // The name defined in docker-compose
$user = 'root';      // The user defined in docker-compose
$pass = 'root';      // The password defined in docker-compose
$port = "3306";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fixed typo here
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // create the connection
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Connection successful!
} catch (\PDOException $e) {
    // If something goes wrong, stop and show error
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>