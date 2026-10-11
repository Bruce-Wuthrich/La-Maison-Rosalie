// path: public/js/flash.js
// Notification flash

const flash = document.querySelector("[data-flash]");

if (flash) {
    const hideFlash = () => {
        flash.classList.add("is-hidden");
        setTimeout(() => flash.remove(), 300);
    };

    flash.querySelector("[data-flash-close]").addEventListener("click", hideFlash);

    if (flash.hasAttribute("data-flash-autohide")) {
        setTimeout(hideFlash, 5000);
    }
}