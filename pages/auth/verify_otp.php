<?php
session_start();
header('Content-Type: application/json');

$user_otp = $_POST['otp'];

// 1. Kiểm tra xem có OTP trong session không (Nếu tắt trình duyệt là mất)
if (!isset($_SESSION['otp'])) {
    echo json_encode(["status" => "error", "message" => "Phiên làm việc đã hết hạn. Vui lòng gửi lại mã!"]);
    exit();
}

// 2. Kiểm tra thời gian (Quá 5 phút chưa?)
if (time() > $_SESSION['otp_expire']) {
    unset($_SESSION['otp']); // Xóa mã hết hạn
    echo json_encode(["status" => "error", "message" => "Mã OTP đã hết hạn (quá 5 phút)!"]);
    exit();
}

// 3. So sánh mã
if ($user_otp == $_SESSION['otp']) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Mã OTP không chính xác!"]);
}
?>