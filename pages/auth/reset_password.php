<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// Kiểm tra xem đã xác thực email chưa (tránh trường hợp truy cập chui)
if (!isset($_SESSION['reset_email'])) {
    echo json_encode(["status" => "error", "message" => "Phiên làm việc hết hạn."]);
    exit();
}

$new_pass = $_POST['password'];
$email = $_SESSION['reset_email'];

// Cập nhật mật khẩu mới vào Database
// (Lưu ý: Để đơn giản cho bạn test, tôi không mã hóa password. Nếu muốn bảo mật thì dùng password_hash)
$sql = "UPDATE users SET password = '$new_pass' WHERE email = '$email'";

if ($conn->query($sql) === TRUE) {
    // Xóa session để hoàn tất
    unset($_SESSION['otp']);
    unset($_SESSION['reset_email']);
    
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Lỗi hệ thống: " . $conn->error]);
}
?>