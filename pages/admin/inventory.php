<?php
$conn = new mysqli("localhost", "root", "", "otonow_db");
$conn->set_charset("utf8");

// =====================================================================
// TỰ ĐỘNG NÂNG CẤP DATABASE
// =====================================================================
@$conn->query("ALTER TABLE kho_xe ADD COLUMN model VARCHAR(100) AFTER ten_xe");
@$conn->query("ALTER TABLE kho_xe ADD COLUMN hinh_anh VARCHAR(255) AFTER so_luong");
@$conn->query("ALTER TABLE kho_xe ADD COLUMN mo_hinh_3d VARCHAR(255) AFTER hinh_anh"); 
@$conn->query("ALTER TABLE kho_xe ADD COLUMN phan_loai VARCHAR(255) AFTER mo_hinh_3d");

// Xuất Excel
if (isset($_GET['action']) && $_GET['action'] == 'export') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Danh_Sach_Kho_Xe.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    $sql = "SELECT * FROM kho_xe";
    $result = $conn->query($sql);

    echo '<meta charset="utf-8">';
    echo '<table border="1">';
    echo '<tr>
            <th style="background-color: #f2f2f2;">ID</th>
            <th style="background-color: #f2f2f2;">Tên xe</th>
            <th style="background-color: #f2f2f2;">Model/Hãng</th>
            <th style="background-color: #f2f2f2;">Năm SX</th>
            <th style="background-color: #f2f2f2;">Giá bán (VND)</th>
            <th style="background-color: #f2f2f2;">Màu sắc</th>
            <th style="background-color: #f2f2f2;">Số lượng</th>
            <th style="background-color: #f2f2f2;">Phân loại</th>
          </tr>';

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>CAR-' . sprintf('%03d', $row["id"]) . '</td>';
            echo '<td>' . htmlspecialchars($row["ten_xe"]) . '</td>';
            echo '<td>' . htmlspecialchars($row["model"] ?? '') . '</td>';
            echo '<td>' . $row["nam_sx"] . '</td>';
            echo '<td>' . number_format($row["gia_ban"], 0, ',', '.') . '</td>';
            echo '<td>' . htmlspecialchars($row["mau_sac"]) . '</td>';
            echo '<td>' . $row["so_luong"] . '</td>';
            echo '<td>' . htmlspecialchars($row["phan_loai"]) . '</td>';
            echo '</tr>';
        }
    }
    echo '</table>';
    exit();
}

// Cập nhật số lượng (+ / -)
if (isset($_GET['action']) && $_GET['action'] == 'update_qty' && isset($_GET['id']) && isset($_GET['type'])) {
    $id = (int)$_GET['id'];
    $type = $_GET['type'];
    if ($type == 'increase') {
        $conn->query("UPDATE kho_xe SET so_luong = so_luong + 1 WHERE id = $id");
    } elseif ($type == 'decrease') {
        $conn->query("UPDATE kho_xe SET so_luong = so_luong - 1 WHERE id = $id AND so_luong > 0");
    }
    header("Location: inventory.php");
    exit();
}

// Xóa xe
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM kho_xe WHERE id = $id");
    header("Location: inventory.php");
    exit();
}

// =====================================================================
// XỬ LÝ THÊM VÀ SỬA XE
// =====================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $ten = $conn->real_escape_string($_POST['ten_xe']);
    $nam = (int)$_POST['nam_sx'];
    $gia = (int)$_POST['gia_ban'];
    $mau = $conn->real_escape_string($_POST['mau_sac']);
    $sl  = (int)$_POST['so_luong'];

    // Lấy Model và Tags phân loại
    $model = isset($_POST['model']) ? $conn->real_escape_string($_POST['model']) : '';
    $phan_loai = isset($_POST['tags']) ? $conn->real_escape_string(implode(', ', $_POST['tags'])) : 'Xe mới nhất';

    // 1. Xử lý lưu File Ảnh 2D
    $hinh_anh = '';
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $upload_dir = '../../assets/image/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);
        $hinh_anh = time() . '_' . $_FILES['hinh_anh']['name'];
        move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $upload_dir . $hinh_anh);
    }

    // 2. Xử lý lưu File Mô hình 3D (.glb, .gltf, .obj)
    $mo_hinh_3d = '';
    if (isset($_FILES['mo_hinh_3d']) && $_FILES['mo_hinh_3d']['error'] == 0) {
        $upload_dir_3d = '../../assets/3d_models/';
        if (!is_dir($upload_dir_3d)) @mkdir($upload_dir_3d, 0777, true);
        $mo_hinh_3d = time() . '_' . $_FILES['mo_hinh_3d']['name'];
        move_uploaded_file($_FILES['mo_hinh_3d']['tmp_name'], $upload_dir_3d . $mo_hinh_3d);
    }

    if ($_POST['action'] == 'add') {
        $sql = "INSERT INTO kho_xe (ten_xe, model, nam_sx, gia_ban, mau_sac, so_luong, hinh_anh, mo_hinh_3d, phan_loai) 
                VALUES ('$ten', '$model', '$nam', '$gia', '$mau', '$sl', '$hinh_anh', '$mo_hinh_3d', '$phan_loai')";
        $conn->query($sql);
    } elseif ($_POST['action'] == 'edit') {
        $id = (int)$_POST['id'];
        
        // Cập nhật các trường text
        $update_fields = array(
            "ten_xe='$ten'", 
            "model='$model'", 
            "nam_sx='$nam'", 
            "gia_ban='$gia'", 
            "mau_sac='$mau'", 
            "so_luong='$sl'",
            "phan_loai='$phan_loai'"
        );
        
        // Chỉ cập nhật ảnh/3D nếu có file mới up lên
        if ($hinh_anh != '') { $update_fields[] = "hinh_anh='$hinh_anh'"; }
        if ($mo_hinh_3d != '') { $update_fields[] = "mo_hinh_3d='$mo_hinh_3d'"; }
        
        $sql = "UPDATE kho_xe SET " . implode(', ', $update_fields) . " WHERE id=$id";
        $conn->query($sql);
    }
    
    header("Location: inventory.php");
    exit();
}

// Lấy danh sách xe
$sql = "SELECT * FROM kho_xe ORDER BY id DESC";
$result = $conn->query($sql);

// Lấy dữ liệu thống kê
$stat_sql = "SELECT COUNT(id) as total_models, SUM(so_luong) as total_cars, SUM(gia_ban * so_luong) as total_value, AVG(gia_ban) as avg_price FROM kho_xe";
$stat_result = $conn->query($stat_sql);
$stats = $stat_result->fetch_assoc();
$total_models = $stats['total_models'] ? $stats['total_models'] : 0;
$total_cars = $stats['total_cars'] ? $stats['total_cars'] : 0;
$total_value = $stats['total_value'] ? $stats['total_value'] : 0;
$avg_price = $stats['avg_price'] ? $stats['avg_price'] : 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Kho xe - AutoAdmin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/inventory.css">
</head>
<body>
    <div class="app-container">
        <div class="admin-sidebar">
            <div class="admin-logo">
                <img src="../../assets/image/logo.png" alt="Logo">
                <span>ADMIN</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.html"><i class="fas fa-th-large"></i> Tổng quan</a></li>
                <li class="active"><a href="#"><i class="fas fa-car"></i> Kho xe</a></li>
                <li><a href="orders.html"><i class="fas fa-shopping-cart"></i> Đơn hàng</a></li>
                <li><a href="danh_gia.html"><i class="fas fa-star"></i> Đánh giá</a></li>
                <li><a href="analytics.html"><i class="fas fa-chart-line"></i> Thống kê</a></li>
                <li><a href="users.html"><i class="fas fa-users"></i> Nhân viên</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="../../../OTO_NOW/"><i class="fas fa-sign-out-alt"></i> Quay về Web</a>
            </div>
        </div>

        <main class="main-content">
            <header class="top-header">
                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Tìm kiếm dữ liệu..." onkeyup="searchTable()">
                </div>
                <div class="header-right">
                    <div class="notification-bell"><i class="fas fa-bell"></i><span class="dot"></span></div>
                    <div class="user-profile">
                        <div class="user-text">
                            <span class="user-name">Admin User</span>
                            <span class="user-status">Đang hoạt động</span>
                        </div>
                        <div class="user-avatar"><i class="fas fa-user"></i></div>
                    </div>
                </div>
            </header>

            <div class="content-wrapper">
                <div class="page-header">
                    <div>
                        <h1>Kho xe</h1>
                        <p>Quản lý danh sách xe và các file 3D Model đăng bán.</p>
                    </div>
                    <div class="actions">
                        <button class="btn-secondary" onclick="window.location.href='inventory.php?action=export'"><i class="fas fa-file-export"></i> Xuất Excel</button>
                        <button class="btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm xe mới</button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Thông tin xe</th>
                                <th>Năm SX</th>
                                <th>Giá bán</th>
                                <th>Màu sắc</th>
                                <th>Số lượng</th>
                                <th class="text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $raw_color = strtolower(trim($row["mau_sac"]));
                                    $color_map = ['white'=>'#ffffff','trắng'=>'#ffffff','black'=>'#000000','đen'=>'#000000','green'=>'#28a745','xanh lá'=>'#28a745','yellow'=>'#ffc107','vàng'=>'#ffc107','red'=>'#dc3545','đỏ'=>'#dc3545','blue'=>'#007bff','xanh dương'=>'#007bff','orange'=>'#fd7e14','cam'=>'#fd7e14','pink'=>'#e83e8c','hồng'=>'#e83e8c','grey'=>'#6c757d','xám'=>'#6c757d'];
                                    $text_color_map = ['white'=>'#000000','trắng'=>'#000000','yellow'=>'#000000','vàng'=>'#000000','orange'=>'#000000','cam'=>'#000000'];
                                    
                                    $bg_color = array_key_exists($raw_color, $color_map) ? $color_map[$raw_color] : '#000000';
                                    $text_color = array_key_exists($raw_color, $text_color_map) ? $text_color_map[$raw_color] : '#ffffff';
                                    $border = ($raw_color == 'white' || $raw_color == 'trắng') ? 'border: 1px solid #ddd;' : '';
                                    
                                    $has_3d = !empty($row["mo_hinh_3d"]);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row["ten_xe"]); ?></strong><br>
                                            <span class="id-text">ID: CAR-<?php echo sprintf('%03d', $row["id"]); ?></span>
                                            
                                            <div style="margin-top: 5px; display: flex; gap: 5px; flex-wrap: wrap;">
                                                <?php if($has_3d): ?>
                                                    <span style="font-size: 11px; background: #E0E7FF; color: #1D4ED8; padding: 2px 6px; border-radius: 4px; font-weight: bold;"><i class="fas fa-cube"></i> 3D Model</span>
                                                <?php endif; ?>
                                                <?php if(!empty($row["phan_loai"]) && $row["phan_loai"] != 'Xe mới nhất'): ?>
                                                    <?php 
                                                        $tags = explode(',', $row["phan_loai"]);
                                                        foreach($tags as $tag) {
                                                            $tag = trim($tag);
                                                            if($tag == 'Xe mới nhất') continue;
                                                            echo '<span style="font-size: 11px; background: #DCFCE7; color: #166534; padding: 2px 6px; border-radius: 4px; font-weight: bold;"><i class="fas fa-tag"></i> '.$tag.'</span>';
                                                        }
                                                    ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo $row["nam_sx"]; ?></td>
                                        <td><?php echo number_format($row["gia_ban"], 0, ',', '.'); ?> VND</td>
                                        <td><span class="badge" style="background: <?php echo $bg_color; ?>; color: <?php echo $text_color; ?>; <?php echo $border; ?>"><?php echo htmlspecialchars($row["mau_sac"]); ?></span></td>
                                        <td>
                                            <div class="qty-group">
                                                <button onclick="window.location.href='inventory.php?action=update_qty&id=<?php echo $row['id']; ?>&type=decrease'">-</button>
                                                <span><?php echo $row["so_luong"]; ?></span>
                                                <button onclick="window.location.href='inventory.php?action=update_qty&id=<?php echo $row['id']; ?>&type=increase'">+</button>
                                            </div>
                                        </td>
                                        <td class="text-right action-btns">
                                            <button class="edit" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-ten="<?php echo htmlspecialchars($row['ten_xe']); ?>" 
                                                data-model="<?php echo htmlspecialchars($row['model'] ?? ''); ?>" 
                                                data-nam="<?php echo $row['nam_sx']; ?>" 
                                                data-gia="<?php echo $row['gia_ban']; ?>" 
                                                data-mau="<?php echo htmlspecialchars($row['mau_sac']); ?>" 
                                                data-sl="<?php echo $row['so_luong']; ?>" 
                                                data-tags="<?php echo htmlspecialchars($row['phan_loai'] ?? ''); ?>" 
                                                onclick="openEditModal(this)">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <button class="delete" onclick="if(confirm('Bạn có chắc chắn muốn xóa xe này?')) window.location.href='inventory.php?action=delete&id=<?php echo $row['id']; ?>'"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center; padding: 20px;'>Chưa có xe nào trong kho</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="stats-container">
                    <div class="stat-card blue"><span>Tổng số xe</span><h2><?php echo $total_cars; ?></h2><small>Trên <?php echo $total_models; ?> mẫu xe</small></div>
                    <div class="stat-card green"><span>Tổng giá trị</span><h2><?php echo number_format($total_value, 0, ',', '.'); ?> VND</h2><small>Dựa trên sl × giá</small></div>
                    <div class="stat-card purple"><span>Giá trung bình</span><h2><?php echo number_format($avg_price, 0, ',', '.'); ?> VND</h2><small>Trên tất cả các mẫu</small></div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="addModal" style="display: none;">
        <div class="modal-content-custom" style="width: 750px; max-width: 95%;">
            <div class="modal-header-custom">
                <h3>Thêm xe mới</h3>
                <button type="button" class="close-btn-custom" onclick="closeAddModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Tên xe (Hãng và Model) <span style="color:red;">*</span></label>
                            <input type="text" name="ten_xe" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;" placeholder="VD: Audi A6 S-Line">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Model / Hãng <span style="color:red;">*</span></label>
                            <input type="text" name="model" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;" placeholder="VD: Audi">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Năm SX <span style="color:red;">*</span></label>
                            <input type="number" name="nam_sx" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Màu sắc</label>
                            <input type="text" name="mau_sac" style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Giá bán (VND) <span style="color:red;">*</span></label>
                            <input type="number" name="gia_ban" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Số lượng <span style="color:red;">*</span></label>
                            <input type="number" name="so_luong" value="1" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px;">
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Hình ảnh xe (.jpg, .png)</label>
                            <div style="border: 2px dashed #94A3B8; border-radius: 12px; padding: 25px 15px; text-align: center; background: #F8FAFC; cursor: pointer; transition: 0.3s;" onclick="document.getElementById('inp-car-image').click()" onmouseover="this.style.borderColor='#A1FF14'" onmouseout="this.style.borderColor='#94A3B8'">
                                <input type="file" id="inp-car-image" name="hinh_anh" accept="image/*" style="display: none;" onchange="previewImage(event, 'car-image-preview', 'upload-placeholder-img')">
                                <div id="upload-placeholder-img">
                                    <i class="fas fa-image" style="font-size: 30px; color: #64748B; margin-bottom: 10px;"></i>
                                    <span style="display: block; font-size: 13px; color: #475569; font-weight: 500;">Tải lên ảnh xe</span>
                                </div>
                                <img id="car-image-preview" src="" alt="Preview" style="max-height: 80px; border-radius: 8px; display: none; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Mô hình 3D (.glb, .gltf, .obj)</label>
                            <div style="border: 2px dashed #94A3B8; border-radius: 12px; padding: 25px 15px; text-align: center; background: #EEF2FF; cursor: pointer; transition: 0.3s;" onclick="document.getElementById('inp-car-3d').click()" onmouseover="this.style.borderColor='#3B82F6'" onmouseout="this.style.borderColor='#94A3B8'">
                                <input type="file" id="inp-car-3d" name="mo_hinh_3d" accept=".glb,.gltf,.obj,.fbx" style="display: none;" onchange="show3DFileName(this, 'file-3d-name')">
                                <div id="upload-placeholder-3d">
                                    <i class="fas fa-cube" style="font-size: 30px; color: #3B82F6; margin-bottom: 10px;"></i>
                                    <span id="file-3d-name" style="display: block; font-size: 13px; color: #475569; font-weight: 500;">Tải lên file 3D Model</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Phân loại danh mục (Tags)</label>
                        <input type="hidden" name="tags[]" value="Xe mới nhất">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid #CBD5E1; border-radius: 8px; background: #F1F5F9; color: #94A3B8; cursor: not-allowed;">
                                <input type="checkbox" checked disabled style="width: 18px; height: 18px; filter: grayscale(1);">
                                <span style="font-size: 14px; font-weight: 500;">Xe mới nhất (Mặc định)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid #E2E8F0; border-radius: 8px; cursor: pointer; transition: 0.2s;">
                                <input type="checkbox" name="tags[]" value="Xe nổi bật" style="width: 18px; height: 18px; accent-color: #16A34A;">
                                <span style="font-size: 14px; font-weight: 500;"><i class="fas fa-fire" style="color:#EF4444; width: 20px;"></i> Xe nổi bật</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid #E2E8F0; border-radius: 8px; cursor: pointer; transition: 0.2s;">
                                <input type="checkbox" name="tags[]" value="Xe bán chạy" style="width: 18px; height: 18px; accent-color: #16A34A;">
                                <span style="font-size: 14px; font-weight: 500;"><i class="fas fa-crown" style="color:#F59E0B; width: 20px;"></i> Xe bán chạy</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid #E2E8F0; border-radius: 8px; cursor: pointer; transition: 0.2s;">
                                <input type="checkbox" name="tags[]" value="Xe điện" style="width: 18px; height: 18px; accent-color: #16A34A;">
                                <span style="font-size: 14px; font-weight: 500;"><i class="fas fa-bolt" style="color:#3B82F6; width: 20px;"></i> Xe điện (EV)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid #E2E8F0; border-radius: 8px; cursor: pointer; transition: 0.2s;">
                                <input type="checkbox" name="tags[]" value="Xe gia đình" style="width: 18px; height: 18px; accent-color: #16A34A;">
                                <span style="font-size: 14px; font-weight: 500;"><i class="fas fa-users" style="color:#10B981; width: 20px;"></i> Xe gia đình</span>
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer-custom" style="margin-top: 25px; border-top: 1px solid #E2E8F0; padding-top: 20px; display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" class="btn-cancel-custom" onclick="closeAddModal()" style="padding: 12px 24px; border: 1px solid #CBD5E1; background: #fff; color: #475569; border-radius: 8px; cursor: pointer; font-weight: 700; transition: 0.2s;">Hủy bỏ</button>
                        <button type="submit" class="btn-save-custom" style="padding: 12px 30px; background: #A1FF14; border: none; color: #000; border-radius: 8px; cursor: pointer; font-weight: 800; box-shadow: 0 4px 15px rgba(161,255,20,0.3); transition: 0.2s;">Lưu vào CSDL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="editModal" style="display: none;">
        <div class="modal-content-custom" style="width: 750px; max-width: 95%;">
            <div class="modal-header-custom">
                <h3>Chỉnh sửa xe</h3>
                <button type="button" class="close-btn-custom" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Tên xe (Hãng và Model) <span style="color:red;">*</span></label>
                            <input type="text" name="ten_xe" id="edit_ten" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Model / Hãng <span style="color:red;">*</span></label>
                            <input type="text" name="model" id="edit_model" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Năm SX <span style="color:red;">*</span></label>
                            <input type="number" name="nam_sx" id="edit_nam" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Màu sắc</label>
                            <input type="text" name="mau_sac" id="edit_mau" style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Giá bán (VND) <span style="color:red;">*</span></label>
                            <input type="number" name="gia_ban" id="edit_gia" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Số lượng <span style="color:red;">*</span></label>
                            <input type="number" name="so_luong" id="edit_sl" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px;">
                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Cập nhật Ảnh (Bỏ qua nếu giữ nguyên)</label>
                            <div style="border: 2px dashed #94A3B8; border-radius: 12px; padding: 25px 15px; text-align: center; background: #F8FAFC; cursor: pointer; transition: 0.3s;" onclick="document.getElementById('edit-inp-car-image').click()">
                                <input type="file" id="edit-inp-car-image" name="hinh_anh" accept="image/*" style="display: none;" onchange="previewImage(event, 'edit-car-image-preview', 'edit-upload-placeholder-img')">
                                <div id="edit-upload-placeholder-img">
                                    <i class="fas fa-image" style="font-size: 30px; color: #64748B; margin-bottom: 10px;"></i>
                                    <span style="display: block; font-size: 13px; color: #475569; font-weight: 500;">Tải ảnh mới để thay thế</span>
                                </div>
                                <img id="edit-car-image-preview" src="" alt="Preview" style="max-height: 80px; border-radius: 8px; display: none; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Cập nhật File 3D (Bỏ qua nếu giữ nguyên)</label>
                            <div style="border: 2px dashed #94A3B8; border-radius: 12px; padding: 25px 15px; text-align: center; background: #EEF2FF; cursor: pointer; transition: 0.3s;" onclick="document.getElementById('edit-inp-car-3d').click()">
                                <input type="file" id="edit-inp-car-3d" name="mo_hinh_3d" accept=".glb,.gltf,.obj,.fbx" style="display: none;" onchange="show3DFileName(this, 'edit-file-3d-name')">
                                <div id="edit-upload-placeholder-3d">
                                    <i class="fas fa-cube" style="font-size: 30px; color: #3B82F6; margin-bottom: 10px;"></i>
                                    <span id="edit-file-3d-name" style="display: block; font-size: 13px; color: #475569; font-weight: 500;">Tải file 3D mới để thay thế</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label style="font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block;">Phân loại danh mục (Tags)</label>
                        <input type="hidden" name="tags[]" value="Xe mới nhất">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid #CBD5E1; border-radius: 8px; background: #F1F5F9; color: #94A3B8; cursor: not-allowed;">
                                <input type="checkbox" checked disabled style="width: 18px; height: 18px; filter: grayscale(1);">
                                <span style="font-size: 14px; font-weight: 500;">Xe mới nhất (Mặc định)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid #E2E8F0; border-radius: 8px; cursor: pointer; transition: 0.2s;">
                                <input type="checkbox" name="tags[]" class="edit-tag-checkbox" value="Xe nổi bật" style="width: 18px; height: 18px; accent-color: #16A34A;">
                                <span style="font-size: 14px; font-weight: 500;"><i class="fas fa-fire" style="color:#EF4444; width: 20px;"></i> Xe nổi bật</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid #E2E8F0; border-radius: 8px; cursor: pointer; transition: 0.2s;">
                                <input type="checkbox" name="tags[]" class="edit-tag-checkbox" value="Xe bán chạy" style="width: 18px; height: 18px; accent-color: #16A34A;">
                                <span style="font-size: 14px; font-weight: 500;"><i class="fas fa-crown" style="color:#F59E0B; width: 20px;"></i> Xe bán chạy</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid #E2E8F0; border-radius: 8px; cursor: pointer; transition: 0.2s;">
                                <input type="checkbox" name="tags[]" class="edit-tag-checkbox" value="Xe điện" style="width: 18px; height: 18px; accent-color: #16A34A;">
                                <span style="font-size: 14px; font-weight: 500;"><i class="fas fa-bolt" style="color:#3B82F6; width: 20px;"></i> Xe điện (EV)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid #E2E8F0; border-radius: 8px; cursor: pointer; transition: 0.2s;">
                                <input type="checkbox" name="tags[]" class="edit-tag-checkbox" value="Xe gia đình" style="width: 18px; height: 18px; accent-color: #16A34A;">
                                <span style="font-size: 14px; font-weight: 500;"><i class="fas fa-users" style="color:#10B981; width: 20px;"></i> Xe gia đình</span>
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer-custom" style="margin-top: 25px; border-top: 1px solid #E2E8F0; padding-top: 20px; display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" class="btn-cancel-custom" onclick="closeEditModal()" style="padding: 12px 24px; border: 1px solid #CBD5E1; background: #fff; color: #475569; border-radius: 8px; cursor: pointer; font-weight: 700; transition: 0.2s;">Hủy bỏ</button>
                        <button type="submit" class="btn-save-custom" style="padding: 12px 30px; background: #A1FF14; border: none; color: #000; border-radius: 8px; cursor: pointer; font-weight: 800; box-shadow: 0 4px 15px rgba(161,255,20,0.3); transition: 0.2s;">Cập nhật Thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function searchTable() {
            let filter = document.getElementById('searchInput').value.toLowerCase();
            let rows = document.querySelectorAll('.inventory-table tbody tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        // Tái sử dụng hàm xem trước ảnh chung cho cả Add và Edit
        function previewImage(event, previewId, placeholderId) {
            const input = event.target;
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById(placeholderId);

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function show3DFileName(input, nameId) {
            const nameDisplay = document.getElementById(nameId);
            if (input.files && input.files[0]) {
                nameDisplay.innerText = input.files[0].name;
                nameDisplay.style.color = '#1D4ED8'; 
                nameDisplay.style.fontWeight = 'bold';
            }
        }

        function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
        function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }

        function openEditModal(btn) { 
            // Load các trường Text cơ bản
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_ten').value = btn.getAttribute('data-ten');
            document.getElementById('edit_model').value = btn.getAttribute('data-model');
            document.getElementById('edit_nam').value = btn.getAttribute('data-nam');
            document.getElementById('edit_gia').value = btn.getAttribute('data-gia');
            document.getElementById('edit_mau').value = btn.getAttribute('data-mau');
            document.getElementById('edit_sl').value = btn.getAttribute('data-sl');
            
            // Tự động Tick lại các Checkbox Tag của xe này
            const currentTags = btn.getAttribute('data-tags') || '';
            const tagCheckboxes = document.querySelectorAll('.edit-tag-checkbox');
            tagCheckboxes.forEach(cb => {
                if (currentTags.includes(cb.value)) {
                    cb.checked = true;
                } else {
                    cb.checked = false;
                }
            });

            // Reset UI của Ảnh và File 3D (Đưa về trạng thái ban đầu)
            document.getElementById('edit-car-image-preview').style.display = 'none';
            document.getElementById('edit-upload-placeholder-img').style.display = 'block';
            
            document.getElementById('edit-file-3d-name').innerText = 'Tải file 3D mới để thay thế';
            document.getElementById('edit-file-3d-name').style.color = '#475569';
            document.getElementById('edit-file-3d-name').style.fontWeight = '500';

            document.getElementById('editModal').style.display = 'flex'; 
        }
        function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
        
        window.onclick = function(e) { 
            if(e.target == document.getElementById('editModal')) closeEditModal(); 
            if(e.target == document.getElementById('addModal')) closeAddModal();
        }
    </script>
</body>
</html>