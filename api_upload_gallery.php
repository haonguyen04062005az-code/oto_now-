<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$conn = new mysqli("127.0.0.1", "root", "", "otonow_db");
$conn->set_charset("utf8");

// Tự động tạo cột thu_vien_anh (định dạng TEXT để chứa được rất nhiều ảnh)
@$conn->query("ALTER TABLE kho_xe ADD COLUMN thu_vien_anh TEXT AFTER hinh_anh");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    $car_id = (int)str_replace('CAR-', '', $_POST['car_id']);
    $uploaded_files = [];

    // Kiểm tra nếu có mảng file được gửi lên
    if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        $upload_dir = 'assets/image/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);

        // Lặp qua từng file để lưu
        foreach ($_FILES['gallery_images']['name'] as $key => $name) {
            if ($_FILES['gallery_images']['error'][$key] == 0) {
                $new_filename = time() . '_' . $key . '_' . basename($name);
                $target_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$key], $target_path)) {
                    $uploaded_files[] = $new_filename;
                }
            }
        }
    }

    if (count($uploaded_files) > 0) {
        // Lấy bộ sưu tập cũ (nếu có)
        $sql_get = "SELECT thu_vien_anh FROM kho_xe WHERE id = $car_id";
        $res = $conn->query($sql_get);
        $row = $res->fetch_assoc();
        $current_gallery = $row['thu_vien_anh'] ? $row['thu_vien_anh'] : '';

        // Nối các ảnh mới vào
        $new_files_str = implode(',', $uploaded_files);
        $new_gallery = $current_gallery ? $current_gallery . ',' . $new_files_str : $new_files_str;

        // Lưu lại DB
        $sql_update = "UPDATE kho_xe SET thu_vien_anh = '$new_gallery' WHERE id = $car_id";
        if ($conn->query($sql_update)) {
            echo json_encode(["status" => "success", "message" => "Tải lên " . count($uploaded_files) . " ảnh thành công!"]);
            exit;
        }
    }
    echo json_encode(["status" => "error", "message" => "Chưa có ảnh nào được tải lên!"]);
}
$conn->close();
?>