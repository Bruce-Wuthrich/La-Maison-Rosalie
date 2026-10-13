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

// Masquer / republier un avis sans rechargement
document.querySelectorAll("[data-admin-status]").forEach((form) => {
    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const result = await sendJsonForm(form);
        showFlash(result.success ? "success" : "error", result.message);

        if (!result.success) {
            return;
        }

        // Le formulaire propose ensuite l'action inverse
        const isHidden = result.data.status === "hidden";
        const article = form.closest("article");
        const label = article?.querySelector("[data-admin-status-label]");

        article?.classList.toggle("admin-hidden", isHidden);
        if (label) {
            label.textContent = isHidden ? "Masqué" : "Publié";
        }
        form.querySelector('input[name="status"]').value = isHidden ? "published" : "hidden";
        form.querySelector('[type="submit"]').textContent = isHidden ? "Republier" : "Masquer";
    });
});

// Suppression d'une recette après confirmation (bonus gestion des recettes)
document.querySelectorAll("[data-admin-recipe-delete]").forEach((form) => {
    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (!window.confirm("Supprimer définitivement cette recette, ses notes et ses avis ?")) {
            return;
        }

        const result = await sendJsonForm(form);
        showFlash(result.success ? "success" : "error", result.message);

        if (result.success) {
            form.closest("[data-recipe-row]")?.remove();
        }
    });
});