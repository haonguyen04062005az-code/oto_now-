<?php
// Cho phép điện thoại và máy tính giao tiếp chéo với nhau
header('Access-Control-Allow-Origin: *');

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($order_id == '') die('error');

// Tạo một file text tạm thời để lưu trạng thái thanh toán
$file = "payment_" . $order_id . ".txt";

if ($action == 'create') {
    file_put_contents($file, "pending"); // Máy tính báo: Đang chờ điện thoại
    echo "pending";
}
else if ($action == 'confirm') {
    file_put_contents($file, "paid"); // Điện thoại báo: Đã trả tiền
    echo "paid";
}
else if ($action == 'check') {
    if (file_exists($file)) {
        echo file_get_contents($file); // Máy tính liên tục đọc file này để kiểm tra
    } else {
        echo "pending";
    }
}
?>