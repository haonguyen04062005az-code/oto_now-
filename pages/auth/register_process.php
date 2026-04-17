<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// Lấy dữ liệu từ form gửi sang
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$fullname || !$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Vui lòng điền đầy đủ thông tin."]);
    exit();
}

$checkEmail = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($checkEmail);

if ($result && $result->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "field" => "email",
        "message" => "Email này đã được sử dụng!"
    ]);
} else {
    $sql = "INSERT INTO users (fullname, email, password, role) VALUES ('$fullname', '$email', '$password', 'customer')";
    if ($conn->query($sql) === TRUE) {
        $userId = $conn->insert_id;
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $fullname;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'customer';
        echo json_encode(["status" => "success", "role" => "customer", "name" => $fullname]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Lỗi hệ thống: " . $conn->error
        ]);
    }
}
?>