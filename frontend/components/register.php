<?php
// frontend/components/register.php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

$error = '';
$success = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm-password'] ?? '';

        // Kiểm tra dữ liệu hợp lệ cơ bản
        if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
            $error = "Vui lòng nhập đầy đủ thông tin.";
        } elseif ($password !== $confirm_password) {
            $error = "Mật khẩu nhập lại không khớp.";
        } else {
            // Kiểm tra xem Username hoặc Email đã tồn tại chưa
            $sqlCheck = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmtCheck = $db->prepare($sqlCheck);
            $stmtCheck->execute([$username, $email]);
            
            if ($stmtCheck->fetch()) {
                $error = "Tên đăng nhập hoặc Email này đã tồn tại trong hệ thống.";
            } else {
                // Mã hóa mật khẩu an toàn theo chuẩn bcrypt
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Chèn dữ liệu User mới vào bảng `users`
                $sqlInsert = "INSERT INTO users (fullname, username, email, password) VALUES (?, ?, ?, ?)";
                $stmtInsert = $db->prepare($sqlInsert);
                
                try {
                    if ($stmtInsert->execute([$fullname, $username, $email, $hashed_password])) {
                        $success = "Đăng ký thành công! Hãy đăng nhập để tiếp tục.";
                    } else {
                        $error = "Đã xảy ra lỗi khi tạo tài khoản, vui lòng thử lại sau.";
                    }
                } catch (PDOException $e) {
                    // Xử lý lỗi nếu database cấu hình ràng buộc chặt chẽ
                    $error = "Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage();
                }
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
    <title>Đăng ký - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>

    <main class="auth-container">
        <div class="auth-box">
            <h2>Đăng ký tài khoản</h2>
            
            <?php if (!empty($error)): ?>
                <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <!-- Chuyển hướng về trang đăng nhập sau 2 giây -->
                <meta http-equiv="refresh" content="2;url=login.php">
            <?php endif; ?>

            <!-- Form gọi POST dữ liệu vào chính file này thay vì index.php để kiểm tra code PHP -> DB -->
            <form action="register.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="fullname">Họ tên</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Họ tên của bạn" value="<?php echo htmlspecialchars($fullname ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" placeholder="Tên đăng nhập" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" placeholder="Tạo mật khẩu" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Nhập lại mật khẩu</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Nhập lại mật khẩu" required>
                </div>
                <div class="form-group">
                    <label for="email">Tài khoản Email</label>
                    <input type="email" id="email" name="email" placeholder="Nhập email của bạn" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn-auth btn-auth-register">Tạo tài khoản</button>
                <p class="auth-links">
                    Đã có tài khoản? <a href="/PetsAccessories/frontend/components/login.php">Đăng nhập</a>
                </p>
                <p class="index-link">
                    <a href="/PetsAccessories/public/index.php">Quay về trang chủ</a>
                </p>
            </form>
        </div>
    </main>

</body>

</html>