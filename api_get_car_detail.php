<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Tắt các cảnh báo lặt vặt của PHP để không làm hỏng định dạng JSON
error_reporting(0); 

// DÙNG 127.0.0.1 THAY VÌ LOCALHOST ĐỂ CHỐNG TREO WEB TRÊN WINDOWS
$conn = new mysqli("127.0.0.1", "root", "", "otonow_db");
$conn->set_charset("utf8");

// Nếu chưa bật MySQL, báo lỗi ngay lập tức thay vì treo 30 giây
if ($conn->connect_error) {
    echo json_encode(["error" => "Lỗi MySQL: Hãy chắc chắn XAMPP đã bật MySQL!"]);
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM kho_xe WHERE id = $id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["error" => "Không tìm thấy xe trong Database"]);
    }
} elseif (isset($_GET['name'])) {
    $name = $conn->real_escape_string($_GET['name']);
    $sql = "SELECT * FROM kho_xe WHERE ten_xe LIKE '%$name%' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["error" => "Không tìm thấy xe trong Database"]);
    }
} else {
    echo json_encode(["error" => "Thiếu mã xe (ID)"]);
}

$conn->close();
?>