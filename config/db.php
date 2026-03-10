<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "shopthucung"; 

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập bộ ký tự
$conn->query("SET NAMES utf8"); 

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Thiết lập chế độ lỗi PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage();
}
?>