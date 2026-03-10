<?php
session_start(); // Bắt đầu session

// Hủy tất cả các session
session_unset();

// Hủy session
session_destroy();

// Chuyển hướng về trang đăng nhập (login1.php)
header("Location: login1.php");
exit();
?>
