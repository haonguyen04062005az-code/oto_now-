<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Dùng 127.0.0.1 cho tốc độ bàn thờ
$conn = new mysqli("127.0.0.1", "root", "", "otonow_db");
$conn->set_charset("utf8");

// Tự động thêm cột vào CSDL nếu chưa có
@$conn->query("ALTER TABLE kho_xe ADD COLUMN anh_noi_that VARCHAR(255) AFTER hinh_anh");
@$conn->query("ALTER TABLE kho_xe ADD COLUMN anh_chi_tiet VARCHAR(255) AFTER anh_noi_that");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id']) && isset($_POST['image_type'])) {
    $car_id = (int)str_replace('CAR-', '', $_POST['car_id']);
    $image_type = $_POST['image_type']; // Nhận diện xem đang up ảnh Ngoại thất, Nội thất hay Chi tiết
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = 'assets/image/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);
        
        $new_filename = time() . '_' . $_FILES['file']['name'];
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
            // Xác định cột cần lưu dựa vào nút Admin bấm
            $column = '';
            if ($image_type === 'ngoai_that') $column = 'hinh_anh'; // Nếu up ô to thì đè luôn ảnh đại diện
            elseif ($image_type === 'noi_that') $column = 'anh_noi_that';
            elseif ($image_type === 'chi_tiet') $column = 'anh_chi_tiet';
            
            if ($column) {
                $sql = "UPDATE kho_xe SET $column = '$new_filename' WHERE id = $car_id";
                if ($conn->query($sql)) {
                    echo json_encode(["status" => "success", "file" => $new_filename]);
                    exit();
                }
            }
        }
    }
    echo json_encode(["status" => "error", "message" => "Upload thất bại!"]);
}
$conn->close();
?>