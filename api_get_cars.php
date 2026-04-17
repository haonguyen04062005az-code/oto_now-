<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$conn = new mysqli("127.0.0.1", "root", "", "otonow_db");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die(json_encode(["error" => "Kết nối thất bại"]));
}

// Lấy xe mới nhất lên đầu
$sql = "SELECT * FROM kho_xe ORDER BY id DESC";
$result = $conn->query($sql);

$cars = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
}

echo json_encode($cars);
$conn->close();
?>