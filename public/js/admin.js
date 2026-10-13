// path: public/js/admin.js
// Page admin : suppression d'un avis sans rechargement, après confirmation.

document.querySelectorAll("[data-admin-delete]").forEach((form) => {
    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (!window.confirm("Supprimer définitivement cet avis ?")) {
            return;
        }

        const result = await sendJsonForm(form);
        showFlash(result.success ? "success" : "error", result.message);

        if (result.success) {
            form.closest("article")?.remove();
        }
    });
});