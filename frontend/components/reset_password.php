<?php
// frontend/components/reset_password.php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

// Kiểm tra xem người dùng có đi qua form "Quên mật khẩu" hay không
if (!isset($_SESSION['reset_email'])) {
    // Không có email tạm thì quay về file quên mật khẩu
    header("Location: /PetsAccessories/frontend/components/forgot_password.php");
    exit;
}

$email = $_SESSION['reset_email'];
$error = '';
$success = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password) || empty($confirm_password)) {
            $error = "Vui lòng điền đủ mật khẩu mới.";
        } elseif ($password !== $confirm_password) {
            $error = "Mật khẩu xác nhận không khớp.";
        } elseif (strlen($password) <3) {
            $error = "Mật khẩu phải dài hơn 3 ký tự.";
        } else {
            // Cập nhật Database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$hashed_password, $email])) {
                $success = "Cập nhật mật khẩu thành công!";
                // Xóa Session reset mật khẩu để bảo mật sau khi đổi xong
                unset($_SESSION['reset_email']);
            } else {
                $error = "Có lỗi máy chủ, không thể cập nhật lúc này.";
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
    <title>Đặt lại mật khẩu - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../layout/Header.php'; ?>

<main class="auth-container">
    <div class="auth-box">
        <h2>Đặt lại mật khẩu mới</h2>
        
        <?php if (!empty($success)): ?>
            <div style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <p style="text-align: center; color: #555; margin-bottom: 20px;">
                Đang chuyển hướng về lại trang đăng nhập...
            </p>
            <meta http-equiv="refresh" content="2;url=/PetsAccessories/frontend/components/login.php">
        <?php else: ?>
            <p style="text-align: center; color: #ff6f61; margin-bottom: 20px; font-weight: bold; font-size: 15px;">
                Tài khoản: <?php echo htmlspecialchars($email); ?>
            </p>

            <?php if (!empty($error)): ?>
                <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="reset_password.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="password">Mật khẩu mới</label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu mới" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu mới</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                </div>
                <button type="submit" class="btn-auth">Cập nhật mật khẩu</button>
                <p class="index-link" style="margin-top: 20px;">
                    <a href="/PetsAccessories/frontend/components/login.php">Hủy bỏ</a>
                </p>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/Footer.php'; ?>

</body>
</html>
