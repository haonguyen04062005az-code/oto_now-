<?php
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli("localhost", "root", "", "otonow_db");
$conn->set_charset("utf8");

// Kiểm tra số lượng xe thuộc mỗi loại
$sql = "SELECT loai_xe, COUNT(*) as count FROM kho_xe WHERE so_luong > 0 GROUP BY loai_xe";
$result = $conn->query($sql);

$stats = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $stats[] = $row;
    }
}

// Cũng lấy danh sách chi tiết xe theo loại
$details = [];
$categories = ['noi_bat', 'ban_chay', 'gia_dinh', 'dien'];

foreach ($categories as $cat) {
    $sql = "SELECT id, ten_xe, loai_xe FROM kho_xe WHERE loai_xe = '$cat' AND so_luong > 0 ORDER BY gia_ban DESC";
    $result = $conn->query($sql);
    $details[$cat] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $details[$cat][] = $row;
        }
    }
}

echo "=== THỐNG KÊ SỐ LƯỢNG XE THEO LOẠI ===\n";
echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== CHI TIẾT DANH SÁCH XE THEO LOẠI ===\n";
echo json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

$conn->close();
?>
