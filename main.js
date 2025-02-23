// Change navbar styles on scroll

window.addEventListener('scroll', () => {
    document.querySelector('nav').classList.toggle('window-scroll', window.scrollY > 0)
});

// ===================== show/hide faq answer ======================

const faqs = document.querySelectorAll('.faq');

faqs.forEach(faq => {
    faq.addEventListener('click', () => {
        faq.classList.toggle('open');

        // change icon
        const icon = faq.querySelector('.faq__icon i');
        if(icon.className === 'uil uil-plus-circle') {
            icon.className="uil uil-minus-circle";
        } else {
            icon.className="uil uil-plus-circle";
        }
    });
});

// ==================== Our Patners Section code =====================

document.addEventListener("DOMContentLoaded", function () {
    const track = document.querySelector(".carousel__track");
    const slides = Array.from(track.children);
    const slideWidth = slides[0].getBoundingClientRect().width;

    // Clone the first few slides and append them to the end of the track
    const clonedSlides = slides.slice(0, 3).map(slide => slide.cloneNode(true));
    track.append(...clonedSlides);

    let currentIndex = 0;

    // Function to move to the next slide
    function moveToNextSlide() {
        currentIndex++;
        const offset = -slideWidth * currentIndex;
        track.style.transition = "transform 0.5s ease-in-out";
        track.style.transform = `translateX(${offset}px)`;

        // If we reach the end of the original slides, reset to the first slide without animation
        if (currentIndex >= slides.length) {
            setTimeout(() => {
                track.style.transition = "none";
                track.style.transform = `translateX(0)`;
                currentIndex = 0;
            }, 500); // Wait for the transition to complete
        }
    }

    // Auto-swipe every 5 seconds
    setInterval(moveToNextSlide, 5000);

    // Update slide width on window resize
    window.addEventListener("resize", function () {
        const slideWidth = slides[0].getBoundingClientRect().width;
        track.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
    });
});

// ========================= Show/hide nav menu ====================

const menu = document.querySelector(".nav__menu");
const menuBtn = document.querySelector("#open-menu-btn");
const closeBtn = document.querySelector("#close-menu-btn");

menuBtn.addEventListener('click', () => {
    menu.style.display = "flex";
    closeBtn.style.display = "inline-block";
    menuBtn.style.display = "none";
});

// close nav menu
const closeNav = () => {
    menu.style.display = "none";
    closeBtn.style.display = "none";
    menuBtn.style.display = "inline-block";
}

closeBtn.addEventListener('click',closeNav);

// ===================== Header Span Text Typing Animation ======================
const texts = ["Admission Form Filling", "Exam Form Filling", "Result Download"];
let count = 0;
let index = 0;
let currentText = "";
let letter = "";

(function type() {
    if (count === texts.length) {
        count = 0; // Reset to the first text
    }
    currentText = texts[count];
    letter = currentText.slice(0, ++index);

    document.getElementById("typing-text").textContent = letter;

    if (letter.length === currentText.length) {
        count++;
        index = 0;
        setTimeout(type, 2000); // Delay before starting the next text
    } else {
        setTimeout(type, 100); // Typing speed
    }
})();