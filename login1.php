<?php
session_start();
include 'config/db.php'; // Kết nối cơ sở dữ liệu

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Truy vấn người dùng
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        // Lưu session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Lưu lịch sử
        $stmt = $pdo->prepare("INSERT INTO history (user_id, action_type, action_description) VALUES (?, 'login', ?)");
        $stmt->execute([$user['id'], 'Đăng nhập thành công']);

        // Điều hướng theo vai trò
        if ($user['role'] === 'admin') {
            header('Location: quanly.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = "Tên tài khoản hoặc mật khẩu không đúng!";
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Màn Hình Đăng Nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-sm" style="width: 400px;">
            <h2 class="text-center mb-4">Đăng Nhập</h2>

            <!-- Hiển thị thông báo lỗi nếu đăng nhập thất bại -->
            <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

            <!-- Form đăng nhập -->
            <form method="POST">
                <!-- Username input -->
                <div class="mb-3">
                    <label for="username" class="form-label">Tên tài khoản</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Nhập tên tài khoản" required>
                </div>

                <!-- Password input -->
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>

                <!-- Remember me checkbox -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                </div>

                <!-- Login button -->
                <button type="submit" class="btn btn-primary w-100">Đăng Nhập</button>

                <div class="text-center mt-3">
                    <a href="index.php">Quay lại trang chủ</a>
                </div>
                <!-- Đường dẫn đến trang đăng ký -->
                <div class="text-center mt-3">
                    <a href="register.php">Đăng ký tài khoản mới</a>
                </div>
              
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>
