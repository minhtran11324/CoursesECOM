<?php
// frontend/components/login.php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

$error = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login_id = trim($_POST['login_id'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login_id) || empty($password)) {
            $error = "Vui lòng nhập đầy đủ thông tin.";
        } else {
            // Truy vấn user từ DB
            $sql = "SELECT id, username, email, password, fullname FROM users WHERE username = ? OR email = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$login_id, $login_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Kiểm tra mật khẩu
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = !empty($user['fullname']) ? $user['fullname'] : $user['username'];
                
                // Đăng nhập thành công -> Về trang chủ
                header("Location: /PetsAccessories/public/index.php");
                exit;
            } else {
                $error = "Tài khoản hoặc mật khẩu không chính xác!";
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
    <title>Đăng nhập - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body>

<main class="auth-container">
    <div class="auth-box">
        <h2>Đăng nhập</h2>
        
        <?php if (!empty($error)): ?>
            <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="login_id">Tên đăng nhập hoặc Email</label>
                <input type="text" id="login_id" name="login_id" placeholder="Nhập tên đăng nhập hoặc email" value="<?php echo htmlspecialchars($login_id ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            <div style="text-align: right; margin-bottom: 15px;">
                <a href="/PetsAccessories/frontend/components/forgot_password.php" style="color: #ff6f61; font-size: 14px; text-decoration: none; font-weight: bold;">Quên mật khẩu?</a>
            </div>
            <button type="submit" class="btn-auth">Đăng nhập</button>
            <p class="auth-links">
                Chưa có tài khoản? <a href="/PetsAccessories/frontend/components/register.php">Đăng ký ngay</a>
            </p>
            <p class="index-link">
                <a href="/PetsAccessories/public/index.php">Quay về trang chủ</a>
            </p>
        </form>
    </div>
</main>

</body>
</html>
