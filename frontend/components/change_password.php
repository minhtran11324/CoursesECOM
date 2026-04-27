<?php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /PetsAccessories/frontend/components/login.php');
    exit;
}

$error = '';
$success = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            $error = 'Vui lòng nhập đầy đủ thông tin.';
        } elseif ($newPassword !== $confirmNewPassword) {
            $error = 'Mật khẩu mới và xác nhận mật khẩu không khớp.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        } elseif ($newPassword === $currentPassword) {
            $error = 'Mật khẩu mới phải khác mật khẩu hiện tại.';
        } else {
            try {
                $stmt = $db->prepare('SELECT id, password FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $error = 'Không tìm thấy tài khoản của bạn.';
                } elseif (!password_verify($currentPassword, $user['password'])) {
                    $error = 'Mật khẩu hiện tại không chính xác.';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateStmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $updated = $updateStmt->execute([$hashedPassword, (int) $_SESSION['user_id']]);

                    if ($updated) {
                        $success = 'Đổi mật khẩu thành công!';
                    } else {
                        $error = 'Không thể cập nhật mật khẩu. Vui lòng thử lại.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Có lỗi hệ thống, vui lòng thử lại sau.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../layout/Header.php'; ?>

<main class="auth-container">
    <div class="auth-box">
        <h2>Đổi mật khẩu</h2>

        <?php if (!empty($error)): ?>
            <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="change_password.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="current_password">Mật khẩu hiện tại</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">Mật khẩu mới</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>

            <div class="form-group">
                <label for="confirm_new_password">Xác nhận mật khẩu mới</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" required>
            </div>

            <button type="submit" class="btn-auth">Cập nhật mật khẩu</button>

            <p class="index-link">
                <a href="/PetsAccessories/public/index.php">Quay về trang chủ</a>
            </p>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/Footer.php'; ?>

</body>
</html>
