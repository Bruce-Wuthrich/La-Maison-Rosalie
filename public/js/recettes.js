// path: public/js/recettes.js
// Page recettes : les listes Difficulté et Temps appliquent le filtre dès qu'on les change.
// Sans js, le bouton fait la même chose.

document.querySelectorAll("[data-auto-submit]").forEach((select) => {
    select.addEventListener("change", () => select.form.requestSubmit());
});
const commentForm = document.querySelector(".comment-form");
if (commentForm !== null) {
  const ratingInputs = commentForm.querySelectorAll('input[name="rating"]');
  const averageStars = document.querySelectorAll(
    ".recipe-rating-stars img, .comments-average-stars img",
  );
  const ratingStats = document.querySelector(".recipe-rating-stats");
  const ratingContainer = document.querySelector(".recipe-rating");
  ratingInputs.forEach((ratingInput) => {
    ratingInput.addEventListener("change", async () => {
      const ratingData = new FormData(commentForm);
      ratingData.delete("message");
      // étoiles bloquées pendant l'envoi, pas de double note
      ratingInputs.forEach((input) => {
        input.disabled = true;
      });

      let result;
      try {
        const response = await fetch("?pg=noter", {
          method: "POST",
          headers: { Accept: "application/json" },
          body: ratingData,
        });
        result = await response.json();
      } catch (error) {
        showFlash("error", "La note n'a pas pu être enregistrée. Vérifiez votre connexion.");
        return;
      } finally {
        ratingInputs.forEach((input) => {
          input.disabled = false;
        });
      }

        if (result.success === true) {
          showFlash("success", result.message);
          const filledStars = Math.round(result.data.average);

        averageStars.forEach((starImage, index) => {
          const starNumber = (index % 5) + 1;

          if (starNumber <= filledStars) {
            starImage.src = "assets/img/recettes/icons/star-filled.svg";
          } else {
            starImage.src = "assets/img/recettes/icons/star-outline.svg";
          }
        });
        if (ratingStats !== null) {
          const formattedAverage = result.data.average
            .toFixed(1)
            .replace(".", ",");

          ratingStats.textContent = `${formattedAverage}/5 — ${result.data.count} avis`;
          if (ratingContainer !== null) {
            ratingContainer.setAttribute(
              "aria-label",
              `Note moyenne : ${formattedAverage} sur 5`,
            );
          }
        }
      } else {
        showFlash("error", result.message);
      }
    });
  });
}

// Bonus : recherche en temps réel par titre, catégorie ou ingrédient (accents et majuscules ignorés)
const liveSearch = document.querySelector("[data-live-search]");
if (liveSearch !== null) {
  const normalize = (text) =>
    text.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
  const cards = document.querySelectorAll("[data-search-text]");
  const emptyMessage = document.querySelector("[data-live-empty]");

  liveSearch.addEventListener("input", () => {
    const term = normalize(liveSearch.value);
    let visibleCount = 0;

    cards.forEach((card) => {
      const isMatch = term === "" || normalize(card.dataset.searchText).includes(term);
      card.hidden = !isMatch;
      if (isMatch) {
        visibleCount++;
      }
    });

    if (emptyMessage !== null) {
      emptyMessage.hidden = visibleCount > 0;
    }
  });
}
