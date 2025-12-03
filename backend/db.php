<?php

$host = 'mysql_db';
$db   = 'quiz_app';
$user = 'root';
$pass = 'root';
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