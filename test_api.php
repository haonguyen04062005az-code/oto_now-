<?php
header('Content-Type: application/json; charset=utf-8');

// Test kết nối database
$conn = new mysqli("localhost", "root", "", "otonow_db");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Test: Lấy tất cả xe
$sql = "SELECT * FROM kho_xe LIMIT 5";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => "Query error: " . $conn->error
    ]);
    exit();
}

$cars = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "total_rows" => $result->num_rows,
    "cars" => $cars
]);

$conn->close();
?>
