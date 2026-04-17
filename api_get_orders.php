<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$conn = new mysqli("127.0.0.1", "root", "", "otonow_db");
$conn->set_charset("utf8");

// Nếu bảng chưa được tạo, trả về mảng rỗng để không bị lỗi
$check_table = $conn->query("SHOW TABLES LIKE 'don_hang'");
if ($check_table->num_rows == 0) {
    echo json_encode(["orders" => [], "total_revenue" => 0, "total_deposits" => 0]);
    exit;
}

$sql = "SELECT * FROM don_hang ORDER BY ngay_tao DESC";
$result = $conn->query($sql);

$orders = [];
$total_revenue = 0; // Tổng giá trị các xe đã bán
$total_deposits = 0; // Tổng tiền mặt thực thu (tiền cọc)

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
        $total_revenue += (int)$row['gia_tri'];
        $total_deposits += (int)$row['tien_coc'];
    }
}

echo json_encode([
    "orders" => $orders,
    "total_revenue" => $total_revenue,
    "total_deposits" => $total_deposits
]);

$conn->close();
?>