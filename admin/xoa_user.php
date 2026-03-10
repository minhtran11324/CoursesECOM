<?php
session_start();
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    try {
        // Kiểm tra tài khoản có tồn tại không
        $stmt = $pdo->prepare('SELECT id, username FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "Tài khoản không tồn tại!";
            exit;
        }
        // Sau đó mới xóa tài khoản
        $deleteStmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $deleteStmt->execute([$userId]);

        // Chuyển hướng
        header('Location: ../quanly.php');
        exit;

    } catch (PDOException $e) {
        echo 'Lỗi khi xóa tài khoản: ' . $e->getMessage();
        exit;
    }
} else {
    echo "ID tài khoản không hợp lệ!";
    exit;
}
?>
