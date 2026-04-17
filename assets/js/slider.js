document.addEventListener('DOMContentLoaded', () => {
    // 1. Tìm các thẻ HTML
    const quoteEl = document.getElementById('t-quote');
    const authorEl = document.getElementById('t-author');
    const locationEl = document.getElementById('t-location');
    const ratingEl = document.getElementById('t-rating');
    const btnNext = document.getElementById('btn-next');
    const btnPrev = document.getElementById('btn-prev');

    // 2. QUAN TRỌNG: Nếu trang này không có slider thì dừng lại ngay (Không báo lỗi)
    if (!quoteEl || !authorEl || !locationEl || !ratingEl) {
        return; 
    }

    // 3. Dữ liệu mẫu
    const testimonials = [
        {
            quote: "“In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content.”",
            author: "Sam Kolder",
            location: "Los Angeles, California, USA",
            stars: 5
        },
        {
            quote: "“It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus Maker versions.”",
            author: "Jim Bowden",
            location: "Kolkata, West Bengal, India",
            stars: 4
        },
        {
            quote: "“But they’re very difficult to detect and therefore their properties, for many years, were difficult to know, even the question of whether they have a mass that is greater than zero.”",
            author: "Arthur B. McDonald",
            location: "Sydney, Nova Scotia, Canada",
            stars: 5
        }
    ];

    let currentIndex = 0;
    let slideInterval;

    // 4. Hàm hiển thị
    function showTestimonial(index) {
        if (index >= testimonials.length) currentIndex = 0;
        else if (index < 0) currentIndex = testimonials.length - 1;
        else currentIndex = index;

        const item = testimonials[currentIndex];
        const box = document.querySelector('.testimonial-box');

        // Hiệu ứng Fade
        if(box) {
            box.classList.remove('fade-anim');
            void box.offsetWidth; 
            box.classList.add('fade-anim');
        }

        // Cập nhật nội dung
        quoteEl.innerText = item.quote;
        authorEl.innerText = item.author;
        locationEl.innerText = item.location;

        // Cập nhật sao
        ratingEl.innerHTML = '';
        for (let i = 0; i < 5; i++) {
            if (i < item.stars) {
                ratingEl.innerHTML += '<i class="fa-solid fa-star"></i>';
            } else {
                ratingEl.innerHTML += '<i class="fa-regular fa-star" style="opacity: 0.5"></i>';
            }
        }
    }

    // 5. Chuyển Slide
    function nextSlide() {
        showTestimonial(currentIndex + 1);
        resetTimer();
    }

    function prevSlide() {
        showTestimonial(currentIndex - 1);
        resetTimer();
    }

    // 6. Tự động chạy
    function startAutoPlay() {
        slideInterval = setInterval(() => {
            nextSlide();
        }, 5000);
    }

    function resetTimer() {
        clearInterval(slideInterval);
        startAutoPlay();
    }

    // Gắn sự kiện click
    if(btnNext) btnNext.addEventListener('click', nextSlide);
    if(btnPrev) btnPrev.addEventListener('click', prevSlide);

    // Bắt đầu chạy
    startAutoPlay();
});