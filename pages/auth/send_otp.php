<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

$email = $_POST['email'];

$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $otp = rand(100000, 999999);
    
    // --- SỬA ĐỔI TẠI ĐÂY ---
    $_SESSION['otp'] = $otp;
    $_SESSION['reset_email'] = $email;
    $_SESSION['otp_expire'] = time() + 300; // Cộng thêm 300 giây (5 phút) tồn tại
    // -----------------------

    echo json_encode([
        "status" => "success",
        "message" => "Mã OTP đã gửi!",
        "test_otp" => $otp 
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Email chưa đăng ký!"]);
}
?>