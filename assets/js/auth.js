/* File: assets/js/auth.js */

function handleLogin(event) {
    event.preventDefault(); // Chặn load lại trang

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // --- TRƯỜNG HỢP 1: ADMIN ---
    if (email === 'admin@otonow.com' && password === '123456') {
        // Lưu thông tin Admin vào bộ nhớ
        const adminData = { name: "Admin User", role: "admin", avatar: "A" };
        localStorage.setItem("currentUser", JSON.stringify(adminData));
        
        alert("Xin chào Admin! Đang chuyển đến Dashboard...");
        window.location.href = '../admin/dashboard.html'; 
    } 
    
    // --- TRƯỜNG HỢP 2: USER (KHÁCH HÀNG) ---
    else if (password === '123456') {
        // Lấy tên từ email (ví dụ: khachhang@gmail.com -> khachhang)
        // Hoặc bạn có thể fix cứng tên hiển thị
        let userName = email.split('@')[0]; 
        
        // Lưu thông tin User vào bộ nhớ (QUAN TRỌNG)
        const userData = { name: userName, role: "user", avatar: userName.charAt(0).toUpperCase() };
        localStorage.setItem("currentUser", JSON.stringify(userData));

        alert("Đăng nhập thành công!");
        
        // Chuyển về trang chủ
        window.location.href = '../../'; 
    } 
    
    // --- TRƯỜNG HỢP 3: SAI MẬT KHẨU ---
    else {
        alert("Thông tin đăng nhập không đúng! (Mật khẩu test: 123456)");
    }
}