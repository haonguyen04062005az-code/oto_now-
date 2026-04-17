// --- 1. TẠO HTML THANH MENU VÀ POPUP KHUYẾN MÃI ---
const headerHTML = `
    <div class="fixed-header-container">
        <div class="header-inner">
            <a href="/OTO_NOW/index.html" class="header-logo">
                <img src="/OTO_NOW/assets/image/logo.png" alt="OTONOW" class="logo-img-fixed"> 
            </a>
            <div class="header-right">
                <div id="auth-section">
                    <i class="fas fa-spinner fa-spin"></i> Đang tải...
                </div>
                <div class="lang-switch">EN / DE</div>
                
                <div class="main-menu-wrapper">
                    <div class="menu-btn" id="menuToggleBtn">
                        <span></span><span></span><span></span>
                    </div>
                    
                    <ul class="main-dropdown-menu" id="mainDropdownMenu">
                        <li><a href="/OTO_NOW/index.html"><i class="fas fa-home"></i> Trang chủ</a></li>
                        <li><a href="/OTO_NOW/ve_chung_toi.html"><i class="fas fa-info-circle"></i> Về chúng tôi</a></li>
                        <li><a href="#" onclick="handlePromotion(event)"><i class="fas fa-tags"></i> Khuyến mãi</a></li>
                        <li><a href="/OTO_NOW/index.html#news-section"><i class="fas fa-newspaper"></i> Tin tức</a></li>
                        <li><a href="/OTO_NOW/lien_he.html"><i class="fas fa-headset"></i> Hỗ trợ / Liên hệ</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div id="promotion-modal-bg" class="modal-backdrop" onclick="closePromotionModal()">
        <div class="modal-container" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2>🎉 Khuyến Mãi & Ưu Đãi</h2>
                <button class="modal-close" onclick="closePromotionModal()">✕</button>
            </div>
            <div class="modal-body">
                <div class="promotion-item"><div class="promo-badge">Mới</div><h3>Giảm giá 10% cho tất cả xe Lamborghini</h3><p>Nhân dịp kỷ niệm 60 năm thành lập hãng Lamborghini.</p><div class="promo-date">Từ 01/03/2026 - 30/04/2026</div></div>
                <div class="promotion-item"><div class="promo-badge">Hot</div><h3>Tặng 1 năm bảo hành vàng</h3><p>Mua xe bất kỳ sẽ được tặng gói bảo hành toàn diện 1 năm.</p><div class="promo-date">Từ 01/01/2026 - 31/12/2026</div></div>
                <div class="promotion-item"><div class="promo-badge">Deal</div><h3>Hỗ trợ vay lên tới 80% giá trị xe</h3><p>Lãi suất ưu đãi từ 3.5%/năm qua các ngân hàng đối tác.</p><div class="promo-date">Liên tục trong năm</div></div>
                <div class="promotion-item"><div class="promo-badge">Bonus</div><h3>Miễn phí vận chuyển xe đến nhà</h3><p>Giao xe miễn phí trong khu vực Hà Nội - TP.HCM.</p><div class="promo-date">Từ 01/02/2026 - 30/06/2026</div></div>
            </div>
        </div>
    </div>
`;

document.body.insertAdjacentHTML('afterbegin', headerHTML);

// --- 2. XỬ LÝ MENU 3 GẠCH TRUNG TÂM ---
const menuBtn = document.getElementById("menuToggleBtn");
const dropdownMenu = document.getElementById("mainDropdownMenu");

if (menuBtn && dropdownMenu) {
    menuBtn.onclick = function(event) {
        event.stopPropagation();
        dropdownMenu.classList.toggle("show");
    };
    
    document.onclick = function(event) {
        if (!dropdownMenu.contains(event.target) && !menuBtn.contains(event.target)) {
            dropdownMenu.classList.remove("show");
        }
    };
}

// --- 3. CÁC HÀM XỬ LÝ TOÀN CỤC ---
window.handlePromotion = function(event) {
    event.preventDefault();
    const modalBg = document.getElementById('promotion-modal-bg');
    if (modalBg) { modalBg.classList.add('active'); document.body.style.overflow = 'hidden'; }
    if (dropdownMenu) dropdownMenu.classList.remove("show");
};

window.closePromotionModal = function() {
    const modalBg = document.getElementById('promotion-modal-bg');
    if (modalBg) { modalBg.classList.remove('active'); document.body.style.overflow = ''; }
};

// FIX LỖI ĐĂNG XUẤT (Xóa sạch bộ nhớ và ép chuyển về Trang Chủ)
// FIX LỖI ĐĂNG XUẤT (Chặn load trang sai và xóa sạch session)
window.handleLogout = async function(event) {
    if (event) event.preventDefault(); // Chặn việc trình duyệt bị giật lên đầu trang
    
    localStorage.removeItem("currentUser"); // 1. Xóa trí nhớ trình duyệt
    
    try { 
        await fetch('/OTO_NOW/logout.php'); // 2. Báo cho Server hủy đăng nhập
    } catch(e) {
        console.log("Lỗi gọi file logout.php");
    } 
    
    window.location.href = '/OTO_NOW/index.html'; // 3. Ép văng về trang chủ
};

// --- 4. XỬ LÝ ĐĂNG NHẬP & PHÂN QUYỀN ADMIN KỶ LUẬT THÉP ---
const authSection = document.getElementById('auth-section');
if (authSection) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 1500);

    fetch('/OTO_NOW/check_session.php', { signal: controller.signal })
        .then(res => {
            if (!res.ok) throw new Error('Lỗi server');
            return res.json();
        })
        .then(data => {
            clearTimeout(timeoutId);
            if (data.status === 'loggedin') {
                
                // Đồng bộ Email từ Database PHP (nếu có) hoặc từ LocalStorage
                // Đồng bộ thông tin từ Server
                const storedUser = JSON.parse(localStorage.getItem("currentUser") || "{}");
                const userName = data.name || storedUser.name || "";
                const userEmail = data.email || storedUser.email || "";
                
                localStorage.setItem("currentUser", JSON.stringify({name: userName, email: userEmail}));

                // KỶ LUẬT THÉP: KIỂM TRA ĐÚNG 1 TÊN TÀI KHOẢN DUY NHẤT
                const STRICT_ADMIN_NAME = "Admin OTONOW"; // Bắt buộc khớp từng chữ cái với ảnh bạn chụp
                const STRICT_ADMIN_EMAIL = "admin@otonow.com"; 
                
                let adminMenuHtml = '';
                if (userName === STRICT_ADMIN_NAME || userEmail === STRICT_ADMIN_EMAIL) {
                    adminMenuHtml = `
                        <a href="/OTO_NOW/pages/admin/dashboard.html" class="dropdown-item" style="color: #b45309; font-weight: 700; background: #fffbeb; border-bottom: 1px solid #fde68a;">
                            <i class="fas fa-crown" style="color: #f59e0b; width: 20px;"></i> Quản trị hệ thống
                        </a>`;
                }

                authSection.innerHTML = `
                    <div class="user-account-box">
                        <div class="user-info-text"><span class="user-name-text">${userName || 'User'}</span></div>
                        <div class="user-avatar-circle"><i class="fas fa-user"></i></div>
                        <div class="dropdown-menu">
                            ${adminMenuHtml}
                            <a href="#" class="dropdown-item"><i class="fas fa-id-card" style="width: 20px;"></i> Hồ sơ cá nhân</a>
                            <a href="#" class="dropdown-item logout-btn" onclick="handleLogout(event)"><i class="fas fa-sign-out-alt" style="width: 20px;"></i> Đăng xuất</a>
                        </div>
                    </div>`;
            } else {
                localStorage.removeItem("currentUser");
                authSection.innerHTML = `<a href="/OTO_NOW/pages/auth/GD_dangnhap.html" class="btn-login-header">Đăng nhập</a>`;
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            authSection.innerHTML = `<a href="/OTO_NOW/pages/auth/GD_dangnhap.html" class="btn-login-header">Đăng nhập</a>`;
        });
}