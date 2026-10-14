// path: public/js/favoris.js
// Bonus favoris : le cœur enregistre la recette en base pour le membre connecté (POST ?pg=favori).
// Le visiteur ouvre la fenêtre de connexion (data-auth-open, géré par auth.js).
const favoriteForm = document.querySelector("[data-favorite-form]");

if (favoriteForm) {
  const favoriteButton = favoriteForm.querySelector(".recipe-favorite");
  const favoriteInput = favoriteForm.querySelector('input[name="favorite"]');

  const showFavoriteState = (isFavorite) => {
    favoriteButton.classList.toggle("is-active", isFavorite);
    favoriteButton.setAttribute("aria-pressed", String(isFavorite));
    favoriteButton.setAttribute(
      "aria-label",
      isFavorite ? "Retirer des favoris" : "Ajouter aux favoris",
    );
    // Prochain envoi : l'état inverse
    favoriteInput.value = isFavorite ? "0" : "1";

    const tooltip = favoriteButton.querySelector(".recipe-favorite-tooltip");
    if (tooltip) {
      tooltip.textContent = isFavorite ? "Enregistré" : "Enregistrez";
    }
  };

  favoriteForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    // sendJsonForm (ajax.js) désactive le bouton pendant l'envoi
    const result = await sendJsonForm(favoriteForm);
    if (result.success) {
      showFavoriteState(Boolean(result.data?.favorite));
    } else {
      showFavoriteState(favoriteInput.value === "0");
    }
    showFlash(result.success ? "success" : "error", result.message);
  });
}