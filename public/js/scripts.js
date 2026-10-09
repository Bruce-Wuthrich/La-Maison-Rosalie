const heroWheel = document.querySelector(".hero-wheel");

if (heroWheel) {
  let rotation = 0;

  const categories = [
    { name: "Tartes", slug: "tartes" },
    { name: "Gâteaux", slug: "gateaux" },
    { name: "Glaces", slug: "glaces" },
    { name: "Mousses", slug: "mousses" },
    { name: "Pâtisserie", slug: "patisseries" },
  ];
  const categoryLink = document.querySelector(".hero-category-current");
  const categoryButtons = document.querySelectorAll(".hero-pagination button");
  let categoryIndex = 4;
  let nextCategoryIndex = categoryIndex;

  const showActiveIndicator = (index) => {
    categoryButtons.forEach((button, buttonIndex) => {
      const isCurrent = buttonIndex === index;
      button.classList.toggle("is-active", isCurrent);

      if (isCurrent) {
        button.setAttribute("aria-current", "true");
      } else {
        button.removeAttribute("aria-current");
      }
    });
  };

  const showCategory = (index) => {
    categoryIndex = index;
    const currentCategory = categories[categoryIndex];

    categoryLink.textContent = currentCategory.name;
    categoryLink.href = `?pg=recettes&categorie=${currentCategory.slug}`;

    showActiveIndicator(categoryIndex);

    void categoryLink.offsetWidth;
    categoryLink.classList.add("is-active");
    heroWheel.disabled = false;
  };

  const rotateToCategory = (index) => {
    if (heroWheel.disabled || index === categoryIndex) {
      return;
    }

    heroWheel.disabled = true;
    categoryLink.classList.remove("is-active");
    nextCategoryIndex = index;
    showActiveIndicator(nextCategoryIndex);

    const steps = (index - categoryIndex + categories.length) % categories.length;
    rotation += 15 * (steps || 1);
    heroWheel.style.transform = `rotate(${rotation}deg)`;
  };

  heroWheel.addEventListener("click", () => {
    rotateToCategory((categoryIndex + 1) % categories.length);
  });

  categoryButtons.forEach((button) => {
    button.addEventListener("click", () => {
      rotateToCategory(Number(button.dataset.categoryIndex));
    });
  });

  heroWheel.addEventListener("transitionend", (event) => {
    if (event.propertyName === "transform") {
      showCategory(nextCategoryIndex);
    }
  });
}
