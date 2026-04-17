<?php
// Bật Session
session_start();
header('Content-Type: application/json; charset=utf-8');

// Tắt cảnh báo lỗi PHP bừa bãi hiển thị ra HTML làm hỏng chuỗi JSON
error_reporting(0);

try {
    // Thử kết nối Database (Đổi thông tin nếu XAMPP của bạn dùng pass khác)
    $conn = new mysqli("localhost", "root", "", "otonow_db");
    
    // Nếu kết nối lỗi, ném ra ngoại lệ để khối catch bắt lấy
    if ($conn->connect_error) {
        throw new Exception("Lỗi kết nối DB");
    }

    // Giả lập: Kiểm tra session (Bạn tự thay bằng logic session thực tế của bạn)
    if (isset($_SESSION['user_id']) || isset($_COOKIE['user_login'])) {
        echo json_encode([
            "status" => "loggedin", 
            "name" => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Khách hàng"
        ]);
    } else {
        echo json_encode(["status" => "loggedout"]);
    }
    
    $conn->close();

} catch (Exception $e) {
    // Nếu có bất kỳ lỗi nào (sập DB, sai tên DB...), báo trạng thái chưa đăng nhập một cách an toàn
    echo json_encode(["status" => "error", "message" => "Hệ thống đang bảo trì"]);
}
?>