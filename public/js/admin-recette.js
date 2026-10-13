// path: public/js/admin-recette.js
// Admin recette : ajout et retrait de lignes (ingrédients, étapes), envoi sans rechargement.
// Sans JS, le formulaire s'envoie normalement et le serveur réaffiche les erreurs.

const recipeForm = document.querySelector("[data-admin-recipe-form]");

if (recipeForm !== null) {
    // Numéro libre pour les nouvelles lignes : jamais deux fois le même nom de champ
    let nextIndex = recipeForm.querySelectorAll("[data-row]").length + 100;

    document.querySelectorAll("[data-add-row]").forEach((button) => {
        button.addEventListener("click", () => {
            const type = button.dataset.addRow;
            const template = document.querySelector(`[data-template="${type}"]`);
            const rows = recipeForm.querySelector(`[data-rows="${type}"]`);

            const wrapper = document.createElement("div");
            wrapper.innerHTML = template.innerHTML.replaceAll("__INDEX__", String(nextIndex++));
            const newRow = wrapper.querySelector("[data-row]");
            rows.append(newRow);
            newRow.querySelector("input, textarea")?.focus();
        });
    });

    recipeForm.addEventListener("click", (event) => {
        const removeButton = event.target.closest("[data-remove-row]");
        if (removeButton === null) {
            return;
        }

        const rows = removeButton.closest("[data-rows]");
        const minRows = Number(rows.dataset.minRows ?? 1);
        if (rows.querySelectorAll("[data-row]").length <= minRows) {
            showFlash("error", `Il faut garder au moins ${minRows} ligne${minRows > 1 ? "s" : ""} ici.`);
            return;
        }
        removeButton.closest("[data-row]").remove();
    });

    recipeForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        // FE-61 : contrôles rapides avant envoi, le serveur revérifie tout
        const filledSteps = [...recipeForm.querySelectorAll('[data-rows="steps"] [data-row]')].filter((row) =>
            row.querySelector("input[type='text']").value.trim() !== "",
        );
        const minSteps = Number(recipeForm.querySelector('[data-rows="steps"]').dataset.minRows);
        if (filledSteps.length < minSteps) {
            showFlash("error", `Une recette compte au moins ${minSteps} étapes.`);
            return;
        }

        const tooBig = [...recipeForm.querySelectorAll("input[type='file']")].some((input) =>
            [...input.files].some((file) => file.size > 300 * 1024),
        );
        if (tooBig) {
            showFlash("error", "Chaque image doit peser 300 Ko maximum.");
            return;
        }

        const result = await sendJsonForm(recipeForm);
        showFlash(result.success ? "success" : "error", result.message);

        if (result.success) {
            window.location.href = result.data.url;
        }
    });
}