<?php
// backend/config/database.php

$host = 'localhost';
$candidateDbNames = ['petsaccessories', 'pets_accessories'];
$username = 'root';
$password = '';

$pdo = null;
$lastError = null;

foreach ($candidateDbNames as $dbname) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        break;
    } catch (PDOException $e) {
        $lastError = $e;
    }
}

if ($pdo === null) {
    die('Lỗi kết nối cơ sở dữ liệu: ' . ($lastError ? $lastError->getMessage() : 'Không xác định'));
}
?>