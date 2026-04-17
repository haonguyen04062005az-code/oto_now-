<?php
$conn = new mysqli("localhost", "root", "", "otonow_db");
if ($conn->connect_error) {
    die("❌ Thất bại: " . $conn->connect_error);
}
echo "✅ KẾT NỐI DATABASE THÀNH CÔNG!";
?>