const heroWheel = document.querySelector(".hero-wheel");

if (heroWheel) {
  let rotation = 0;

  const categories = [
    { name: "Tartes", slug: "tartes" },
    { name: "Gâteaux", slug: "gateaux" },
    { name: "Glaces", slug: "glaces" },
    { name: "Mousses", slug: "mousses" },
    { name: "Pâtisseries", slug: "patisseries" },
  ];
  const categoryLink = document.querySelector(".hero-category-current");
  let categoryIndex = 0;

  heroWheel.addEventListener("click", () => {
    heroWheel.disabled = true;
    categoryLink.classList.remove("is-active");
    rotation += 15;
    heroWheel.style.transform = `rotate(${rotation}deg)`;
  });

  heroWheel.addEventListener("transitionend", (event) => {
    if (event.propertyName === "transform") {
      categoryIndex = (categoryIndex + 1) % categories.length;
      const currentCategory = categories[categoryIndex];

      categoryLink.textContent = currentCategory.name;
      categoryLink.href = `?pg=recettes&categorie=${currentCategory.slug}`;

      void categoryLink.offsetWidth;
      categoryLink.classList.add("is-active");

      heroWheel.disabled = false;
    }
  });
}
