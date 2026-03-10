<?php
include 'config/db.php'; // Kết nối cơ sở dữ liệu

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $username = trim($_POST['username']);  // Cắt bỏ khoảng trắng dư thừa trong tên tài khoản
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];  // Lấy giá trị mật khẩu xác nhận
    $role = 'user';  // Mặc định là user, nếu là admin bạn sẽ thay đổi sau

    // Kiểm tra mật khẩu và mật khẩu xác nhận có khớp nhau không
    if ($password !== $confirm_password) {
        $error = "Mật khẩu và xác nhận mật khẩu không khớp!";
    } elseif (strlen($password) < 6) {  // Đảm bảo mật khẩu có ít nhất 6 ký tự
        $error = "Mật khẩu phải có ít nhất 6 ký tự!";
    } else {
        // Kiểm tra nếu người dùng đã tồn tại
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $error = "Tên tài khoản đã tồn tại!";
        } else {
            // Lưu mật khẩu dưới dạng plain text (không mã hóa)
            // Lưu người dùng vào cơ sở dữ liệu
            $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $password, $role]);

            $success = "Tạo tài khoản thành công! Vui lòng đăng nhập.";
            header("Location: login1.php");  // Chuyển hướng đến trang đăng nhập sau khi đăng ký thành công
            exit();  // Ngừng thực thi mã PHP
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-sm" style="width: 400px;">
        <h2 class="text-center mb-4">Đăng Ký Tài Khoản</h2>

        <!-- Hiển thị thông báo lỗi hoặc thành công -->
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <?php if (isset($success)) { echo "<div class='alert alert-success'>$success</div>"; } ?>

        <!-- Form đăng ký -->
        <form action="register.php" method="POST">
            <!-- Tên tài khoản -->
            <div class="mb-3">
                <label for="username" class="form-label">Tên tài khoản</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <!-- Mật khẩu -->
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <!-- Xác nhận mật khẩu -->
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <!-- Nút đăng ký -->
            <button type="submit" class="btn btn-primary w-100">Đăng Ký</button>
        </form>

        <!-- Liên kết đến đăng nhập -->
        <div class="text-center mt-3">
            <p>Bạn đã có tài khoản? <a href="login1.php">Đăng nhập ngay!</a></p>
        </div>
    </div>
</div>

<!-- Thêm Bootstrap JS và Popper -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
