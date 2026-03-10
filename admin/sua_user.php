<?php
session_start();
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Truy vấn thông tin tài khoản cần chỉnh sửa
    $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Tài khoản không tồn tại!";
        exit;
    }

// ... phần xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $passwordToStore = !empty($password) ? $password : $user['password'];

    $updateStmt = $pdo->prepare('UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?');
    $updateStmt->execute([$username, $passwordToStore, $role, $userId]);

    // Lưu lịch sử chỉnh sửa
    $action_description = "Chỉnh sửa tài khoản (ID: $userId)";
    $historyStmt = $pdo->prepare('INSERT INTO history (user_id, action_type, action_description) VALUES (?, ?, ?)');
    $historyStmt->execute([$userId, 'edit', $action_description]);

    // ✅ Không có HTML nào in ra trước đó → chuyển hướng an toàn
    header("Location: ../quanly.php");
    exit();
    }
} else {
    echo "ID tài khoản không hợp lệ!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Tài Khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
    <h2 class="text-center mb-4">Chỉnh Sửa Tài Khoản</h2>

    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Tên Người Dùng</label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Nhập mật khẩu mới nếu muốn thay đổi">
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Vai Trò</label>
            <select id="role" name="role" class="form-select">
                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Người dùng</option>
                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Cập Nhật</button>
    </form>
</div>

</body>
</html>
