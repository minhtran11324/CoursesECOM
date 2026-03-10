<?php
session_start();
require_once '../config/db.php';

// Kiểm tra thông báo
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success" role="alert">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);  // Xóa thông báo sau khi hiển thị
}

// Thêm tài khoản mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_account'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    try {
        // Chèn tài khoản mới vào cơ sở dữ liệu 
        $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$username, $password, $role]);

        // Thêm thông báo và chuyển hướng về trang danh sách tài khoản
        $_SESSION['message'] = 'Tài khoản đã được thêm thành công!';
        header('Location: list_account.php');
        exit;
    } catch (PDOException $e) {
        echo 'Lỗi khi thêm tài khoản: ' . $e->getMessage();
        exit;
    }
}

// Truy vấn tất cả tài khoản
try {
    $stmt = $pdo->query('SELECT id, username, password, role, created_at FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Lỗi truy vấn: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Tài Khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Form Thêm Tài Khoản -->
<div class="container my-5">
    <h2 class="text-center mb-4">Thêm Tài Khoản Mới</h2>

    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Tên Người Dùng</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Vai Trò</label>
            <select id="role" name="role" class="form-select" required>
                <option value="user">Người dùng</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" name="add_account" class="btn btn-success">Thêm Tài Khoản</button>
        <a href="http://localhost:8080/Demo/index.php" class="btn btn-secondary">Quay Lại Trang Chủ</a>
    </form>
</div>

<!-- Danh sách tài khoản -->
<div class="container my-5">
    <h2 class="text-center mb-4">Danh Sách Tài Khoản</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Người Dùng</th>
                <th>Mật khẩu</th>
                <th>Vai trò</th>
                <th>Ngày Tạo</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="text-center">Không có tài khoản nào.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <!-- Hiển thị mật khẩu không mã hóa -->
                        <span id="password-<?php echo $user['id']; ?>" class="password-text" data-password="<?php echo htmlspecialchars($user['password']); ?>">******</span>
                            <button type="button" class="btn btn-info btn-sm" onclick="togglePassword(<?php echo $user['id']; ?>)">
                                <i class="bi bi-eye"></i> Xem
                            </button>
                        </td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="../admin/sua_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm" onclick="return confirm('Bạn có chắc muốn Sửa tài khoản này không?')">Chỉnh sửa</a>
                            <a href="xoa_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn Xóa tài khoản này không?')">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Thêm Bootstrap JS và Popper -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<!-- Thêm icon Bootstrap (Bi-eye) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>

<!-- JavaScript để hiển thị mật khẩu -->
<script>
function togglePassword(userId) {
    const passwordSpan = document.getElementById('password-' + userId);
    const realPassword = passwordSpan.getAttribute('data-password');
    
    if (passwordSpan.textContent === '******') {
        passwordSpan.textContent = realPassword;
    } else {
        passwordSpan.textContent = '******';
    }
}
</script>


</body>
</html>
