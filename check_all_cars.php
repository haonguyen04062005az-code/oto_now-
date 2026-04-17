<?php
$conn = new mysqli("localhost", "root", "", "otonow_db");
$conn->set_charset("utf8");

$sql = "SELECT id, ten_xe, loai_xe, so_luong FROM kho_xe ORDER BY id";
$result = $conn->query($sql);

echo "=== TẤT CẢ XE TRONG DATABASE ===\n";
while ($row = $result->fetch_assoc()) {
    $loai = $row['loai_xe'] ?: "(chưa phân loại)";
    echo $row['id'] . " | " . $row['ten_xe'] . " | loai_xe: " . $loai . " | so_luong: " . $row['so_luong'] . "\n";
}

$conn->close();
?>
