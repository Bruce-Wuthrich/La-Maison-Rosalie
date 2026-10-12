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
      const response = await fetch("?pg=noter", {
        method: "POST",
        headers: { Accept: "application/json" },
        body: ratingData,
      });
      const result = await response.json();

      if (result.success === true) {
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
        console.log(result.message);
      }
    });
  });
}
