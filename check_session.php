<?php
// Bật Session
session_start();
header('Content-Type: application/json; charset=utf-8');

// Tắt cảnh báo lỗi PHP bừa bãi
error_reporting(0);

try {
    $conn = new mysqli("localhost", "root", "", "otonow_db");
    if ($conn->connect_error) {
        throw new Exception("Lỗi kết nối DB");
    }

    // Đã thêm việc trả về email và role để hệ thống bảo mật có thể nhận diện Admin
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            "status" => "loggedin", 
            "name" => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Khách hàng",
            "email" => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : "",
            "role" => isset($_SESSION['user_role']) ? $_SESSION['user_role'] : "user"
        ]);
    } else {
        echo json_encode(["status" => "loggedout"]);
    }
    
    $conn->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Hệ thống đang bảo trì"]);
}
?>