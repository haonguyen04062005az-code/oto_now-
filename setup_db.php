<?php
// Script khởi tạo database và thêm dữ liệu mẫu
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli("localhost", "root", "");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Tạo database (nếu chưa tồn tại)
$sql = "CREATE DATABASE IF NOT EXISTS otonow_db";
if (!$conn->query($sql)) {
    die(json_encode(["error" => "Create DB failed: " . $conn->error]));
}

// Kết nối vào database mới
$conn->select_db("otonow_db");

// Tạo bảng kho_xe
$sql = "CREATE TABLE IF NOT EXISTS kho_xe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_xe VARCHAR(255) NOT NULL,
    model VARCHAR(100),
    gia_ban BIGINT NOT NULL,
    nam_sx INT,
    mau_sac VARCHAR(100),
    so_luong INT DEFAULT 0,
    loai_xe VARCHAR(50),
    hinh_anh VARCHAR(255),
    mo_hinh_3d VARCHAR(255),
    phan_loai VARCHAR(255),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die(json_encode(["error" => "Create table failed: " . $conn->error]));
}

// Kiểm tra xem bảng có dữ liệu không
$result = $conn->query("SELECT COUNT(*) as count FROM kho_xe");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Thêm dữ liệu mẫu
    $cars = [
        ["ARG Quadrifoglio", 4800000000, 2024, "Đỏ", "noi_bat", 10],
        ["Lamborghini AVD SuperVeloce", 35000000000, 2017, "Đen", "noi_bat", 8],
        ["Ferrari Laferrari (Hybrid)", 90000000000, 2016, "Đỏ", "noi_bat", 5],
        ["Nissan Skyline GT-R (R34)", 20800000000, 1999, "Xám", "noi_bat", 6],
        ["Audi A6 S-Line", 890000000, 2024, "Bạc", "ban_chay", 12],
        ["Land Rover Range Rover", 1250000000, 2024, "Trắng", "ban_chay", 9],
        ["BMW M4 Coupe", 2890000000, 2024, "Đen", "ban_chay", 7],
        ["Ford Mustang Shelby", 3650000000, 2024, "Xanh", "ban_chay", 5],
        ["Tesla Roadster", 5243000000, 2024, "Trắng", "dien", 11],
        ["VinFast VF3", 240000000, 2024, "Đen", "dien", 20],
        ["VinFast VF5 Plus", 468000000, 2024, "Bạc", "dien", 15],
        ["VinFast VF9", 1589000000, 2024, "Trắng", "dien", 13],
        ["Lexus LS 500H Hybrid", 7990000000, 2024, "Xám", "dien", 4]
    ];

    foreach ($cars as $car) {
        $sql = "INSERT INTO kho_xe (ten_xe, gia_ban, nam_sx, mau_sac, loai_xe, so_luong) 
                VALUES ('{$car[0]}', {$car[1]}, {$car[2]}, '{$car[3]}', '{$car[4]}', {$car[5]})";
        
        if (!$conn->query($sql)) {
            echo "Insert error: " . $conn->error . "\n";
        }
    }
    
    echo json_encode([
        "status" => "success",
        "message" => "Database and data created successfully",
        "rows_inserted" => count($cars)
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => "Database already has data",
        "existing_rows" => $row['count']
    ]);
}

$conn->close();
?>
