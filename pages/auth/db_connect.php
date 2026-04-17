<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "otonow_db";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Cố gắng tạo database
    $conn_temp = new mysqli($servername, $username, $password);
    $conn_temp->set_charset("utf8");
    
    // Tạo database
    $conn_temp->query("CREATE DATABASE IF NOT EXISTS otonow_db");
    
    // Chọn database
    $conn_temp->select_db("otonow_db");
    
    // Tạo bảng users
    $conn_temp->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Thêm user test
    $conn_temp->query("INSERT IGNORE INTO users (fullname, email, password, role) VALUES 
        ('Admin User', 'admin@otonow.com', '123456', 'admin')
    ");
    
    // Thêm dữ liệu xe mẫu
    $conn_temp->query("INSERT IGNORE INTO kho_xe (ten_xe, gia_ban, nam_sx, mau_sac, loai_xe) VALUES 
        ('ARG Quadrifoglio', 4800000000, 2024, 'Đỏ', 'noi_bat'),
        ('Lamborghini AVD SuperVeloce', 35000000000, 2017, 'Đen', 'noi_bat'),
        ('Ferrari Laferrari (Hybrid)', 90000000000, 2016, 'Đỏ', 'noi_bat'),
        ('Nissan Skyline GT-R (R34)', 20800000000, 1999, 'Xám', 'noi_bat'),
        ('Audi A6 S-Line', 890000000, 2024, 'Bạc', 'ban_chay'),
        ('Land Rover Range Rover', 1250000000, 2024, 'Trắng', 'ban_chay'),
        ('BMW M4 Coupe', 2890000000, 2024, 'Đen', 'ban_chay'),
        ('Ford Mustang Shelby', 3650000000, 2024, 'Xanh', 'ban_chay'),
        ('Tesla Roadster', 5243000000, 2024, 'Trắng', 'dien'),
        ('VinFast VF3', 240000000, 2024, 'Đen', 'dien'),
        ('VinFast VF5 Plus', 468000000, 2024, 'Bạc', 'dien'),
        ('VinFast VF9', 1589000000, 2024, 'Trắng', 'dien'),
        ('Lexus LS 500H Hybrid', 7990000000, 2024, 'Xám', 'dien')
    ");
    
    $conn_temp->close();
    
    // Kết nối lại
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8");
}

// Tạo bảng nếu chưa tồn tại (lần nữa để chắc chắn)
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Tạo bảng kho_xe nếu chưa tồn tại
$conn->query("CREATE TABLE IF NOT EXISTS kho_xe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_xe VARCHAR(255) NOT NULL,
    gia_ban BIGINT NOT NULL,
    nam_sx INT,
    mau_sac VARCHAR(100),
    loai_xe VARCHAR(50),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Kiểm tra xem dữ liệu xe có tồn tại không, nếu không thì thêm
$checkCars = $conn->query("SELECT COUNT(*) as count FROM kho_xe");
$carRow = $checkCars->fetch_assoc();

if ($carRow['count'] == 0) {
    // Thêm dữ liệu xe mẫu
    $conn->query("INSERT INTO kho_xe (ten_xe, gia_ban, nam_sx, mau_sac, loai_xe) VALUES 
        ('ARG Quadrifoglio', 4800000000, 2024, 'Đỏ', 'noi_bat'),
        ('Lamborghini AVD SuperVeloce', 35000000000, 2017, 'Đen', 'noi_bat'),
        ('Ferrari Laferrari (Hybrid)', 90000000000, 2016, 'Đỏ', 'noi_bat'),
        ('Nissan Skyline GT-R (R34)', 20800000000, 1999, 'Xám', 'noi_bat'),
        ('Audi A6 S-Line', 890000000, 2024, 'Bạc', 'ban_chay'),
        ('Land Rover Range Rover', 1250000000, 2024, 'Trắng', 'ban_chay'),
        ('BMW M4 Coupe', 2890000000, 2024, 'Đen', 'ban_chay'),
        ('Ford Mustang Shelby', 3650000000, 2024, 'Xanh', 'ban_chay'),
        ('Tesla Roadster', 5243000000, 2024, 'Trắng', 'dien'),
        ('VinFast VF3', 240000000, 2024, 'Đen', 'dien'),
        ('VinFast VF5 Plus', 468000000, 2024, 'Bạc', 'dien'),
        ('VinFast VF9', 1589000000, 2024, 'Trắng', 'dien'),
        ('Lexus LS 500H Hybrid', 7990000000, 2024, 'Xám', 'dien')
    ");
}
?>