<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['car_id'])) {
    echo json_encode(["status" => "error", "message" => "Thiếu mã xe!"]);
    exit;
}

$car_id = (int)str_replace('CAR-', '', $data['car_id']);
$customer_name = isset($data['customer_name']) ? $data['customer_name'] : 'Khách hàng';
$customer_phone = isset($data['customer_phone']) ? $data['customer_phone'] : '09xx';
$delivery_method = isset($data['delivery_method']) ? $data['delivery_method'] : 'Nhận tại Showroom';
$delivery_address = isset($data['delivery_address']) ? $data['delivery_address'] : 'Showroom OTO NOW';

$conn = new mysqli("127.0.0.1", "root", "", "otonow_db");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Lỗi CSDL"]);
    exit;
}

// 1. Kiểm tra và lấy giá xe để tính 20%
$check_sql = "SELECT so_luong, ten_xe, gia_ban FROM kho_xe WHERE id = $car_id";
$result = $conn->query($check_sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    if ((int)$row['so_luong'] > 0) {
        $update_sql = "UPDATE kho_xe SET so_luong = so_luong - 1 WHERE id = $car_id";
        if ($conn->query($update_sql) === TRUE) {
            
            // ---------------------------------------------------------
            // CÔNG THỨC MỚI: TIỀN CỌC = 20% GIÁ TRỊ XE
            // ---------------------------------------------------------
            $gia_tri = (int)$row['gia_ban'];
            $tien_coc = $gia_tri * 0.2; 
            
            $ma_don = "ORD" . time();
            $ten_xe_safe = $conn->real_escape_string($row['ten_xe']);
            $khach_safe = $conn->real_escape_string($customer_name);
            $sdt_safe = $conn->real_escape_string($customer_phone);
            $hinh_thuc_safe = $conn->real_escape_string($delivery_method);
            $dia_chi_safe = $conn->real_escape_string($delivery_address);
            
            $insert_order = "INSERT INTO don_hang (ma_don, ten_khach, sdt, ten_xe, gia_tri, tien_coc, trang_thai, hinh_thuc_giao, dia_chi_giao) 
                             VALUES ('$ma_don', '$khach_safe', '$sdt_safe', '$ten_xe_safe', $gia_tri, $tien_coc, 'Đã đặt cọc', '$hinh_thuc_safe', '$dia_chi_safe')";
            
            if($conn->query($insert_order) === TRUE) {
                echo json_encode([
                    "status" => "success", 
                    "message" => "Đặt cọc 20% thành công! Số tiền ghi nhận: " . number_format($tien_coc) . " VNĐ"
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi tạo đơn: " . $conn->error]);
            }
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Xe đã hết hàng!"]);
    }
}
$conn->close();
?>