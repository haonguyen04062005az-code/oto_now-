<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$google_login = $_POST['google_login'] ?? false;

if (!$email || !$password) {
    echo json_encode([
        "success" => false, 
        "message" => "Vui lòng điền email và mật khẩu.",
        "status" => "error"
    ]);
    exit();
}

$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($password === $row['password']) {
        // Thành công: tạo session
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['fullname'];
        $_SESSION['user_email'] = $row['email'];
        $_SESSION['user_role'] = $row['role'];

        echo json_encode([
            "success" => true,
            "status" => "success",
            "role" => $row['role'], 
            "username" => $row['fullname'],
            "user_id" => $row['id'],
            "message" => $google_login ? "Đăng nhập Google thành công!" : "Đăng nhập thành công!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "status" => "error",
            "field" => "password",
            "message" => "Mật khẩu không đúng!"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "status" => "error",
        "field" => "email",
        "message" => "Email này chưa đăng ký!"
    ]);
}
?>