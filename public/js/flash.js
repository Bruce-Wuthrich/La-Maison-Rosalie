// path: public/js/flash.js
// Notification flash

const hideFlash = (flash) => {
    flash.classList.add("is-hidden");
    setTimeout(() => flash.remove(), 300);
};

const initFlash = (flash) => {
    flash.querySelector("[data-flash-close]")?.addEventListener("click", () => hideFlash(flash));

    if (flash.hasAttribute("data-flash-autohide")) {
        setTimeout(() => hideFlash(flash), 5000);
    }
};

// affiche notif depuis JS + texte insert textContent pas interprété comme code 
const showFlash = (type, message) => {
    document.querySelectorAll("[data-flash]").forEach(hideFlash);

    const isSuccess = type === "success";
    const flash = document.createElement("div");
    flash.className = `flash-message flash-toast flash-message-${isSuccess ? "success" : "error"}`;
    flash.setAttribute("role", isSuccess ? "status" : "alert");
    flash.dataset.flash = "";
    if (isSuccess) {
        flash.dataset.flashAutohide = "";
    }

    const text = document.createElement("p");
    text.className = "m-0";
    text.textContent = message;

    const closeButton = document.createElement("button");
    closeButton.type = "button";
    closeButton.className = "flash-toast-close";
    closeButton.dataset.flashClose = "";
    closeButton.setAttribute("aria-label", "Fermer le message");
    closeButton.textContent = "×";

    flash.append(text, closeButton);
    document.body.append(flash);
    initFlash(flash);
};

document.querySelectorAll("[data-flash]").forEach(initFlash);