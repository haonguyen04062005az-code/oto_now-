<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

$car_id = isset($_POST['car_id']) ? str_replace('CAR-', '', $_POST['car_id']) : 0;
$file_name = isset($_POST['file_name']) ? trim($_POST['file_name']) : '';

if (!$car_id || !$file_name) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin nhận dạng ảnh.']);
    exit;
}

try {
    $conn = new mysqli("localhost", "root", "", "otonow_db");
    if ($conn->connect_error) throw new Exception("Lỗi kết nối CSDL");

    // Lấy tất cả cột chứa ảnh của xe này
    $sql = "SELECT anh_noi_that, anh_chi_tiet, thu_vien_anh FROM kho_xe WHERE id = $car_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $update_fields = [];

        // 1. Quét xem ảnh có nằm ở cột cố định không
        if ($row['anh_noi_that'] === $file_name) $update_fields[] = "anh_noi_that = NULL";
        if ($row['anh_chi_tiet'] === $file_name) $update_fields[] = "anh_chi_tiet = NULL";

        // 2. Quét xem ảnh có nằm trong chuỗi Album (thu_vien_anh) không
        if (!empty($row['thu_vien_anh'])) {
            $images = explode(',', $row['thu_vien_anh']);
            $images = array_map('trim', $images);
            
            // Lọc bỏ file name cần xóa ra khỏi mảng
            $filtered_images = array_filter($images, function($img) use ($file_name) {
                return $img !== $file_name;
            });

            // Nếu mảng bị ngắn đi (tức là đã tìm thấy và xóa)
            if (count($filtered_images) < count($images)) {
                $new_thu_vien = implode(',', $filtered_images);
                $update_fields[] = "thu_vien_anh = '$new_thu_vien'";
            }
        }

        // 3. Tiến hành cập nhật Database
        if (count($update_fields) > 0) {
            $update_sql = "UPDATE kho_xe SET " . implode(', ', $update_fields) . " WHERE id = $car_id";
            $conn->query($update_sql);

            // 4. Xóa luôn file vật lý trong thư mục máy tính cho nhẹ máy
            $file_path = 'assets/image/' . $file_name; // Thay đổi đường dẫn nếu cấu trúc thư mục của bạn khác
            if (file_exists($file_path)) {
                @unlink($file_path);
            }

            echo json_encode(['status' => 'success', 'message' => 'Xóa ảnh thành công']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy ảnh này trong Database.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy thông tin xe.']);
    }
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>