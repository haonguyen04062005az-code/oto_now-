<?php
session_start();
// Xóa toàn bộ dữ liệu session trên Server
session_unset();
session_destroy();

echo json_encode(["status" => "success", "message" => "Đã đăng xuất"]);
?>