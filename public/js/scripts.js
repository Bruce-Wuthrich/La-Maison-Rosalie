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

const navbarSearch = document.querySelector("[data-navbar-search]");

if (navbarSearch) {
  const searchToggle = navbarSearch.querySelector("[data-search-toggle]");
  const searchInput = navbarSearch.querySelector(".navbar-search-input");
  const supportsHover = window.matchMedia("(hover: hover) and (pointer: fine)").matches;

  const setSearchOpen = (
    isOpen,
    { returnFocus = false, focusInput = false } = {},
  ) => {
    navbarSearch.classList.toggle("is-open", isOpen);
    document.body.classList.toggle("search-open", isOpen);
    searchToggle.setAttribute("aria-expanded", String(isOpen));
    searchToggle.setAttribute(
      "aria-label",
      isOpen ? "Rechercher une recette" : "Ouvrir la recherche",
    );

    if (isOpen && focusInput) {
      requestAnimationFrame(() => searchInput.focus());
    } else if (!isOpen) {
      searchInput.blur();
      if (returnFocus) {
        searchToggle.focus();
      }
    }
  };

  document.body.classList.toggle(
    "search-open",
    navbarSearch.classList.contains("is-open"),
  );

  if (supportsHover) {
    navbarSearch.addEventListener("pointerenter", () => {
      setSearchOpen(true);
    });

    navbarSearch.addEventListener("pointerleave", () => {
      if (!navbarSearch.contains(document.activeElement)) {
        setSearchOpen(false);
      }
    });
  }

  searchToggle.addEventListener("click", () => {
    if (searchInput.value.trim() !== "") {
      navbarSearch.requestSubmit();
      return;
    }

    setSearchOpen(true, { focusInput: true });
  });

  navbarSearch.addEventListener("focusin", () => {
    setSearchOpen(true);
  });

  navbarSearch.addEventListener("focusout", () => {
    requestAnimationFrame(() => {
      if (!navbarSearch.contains(document.activeElement)) {
        setSearchOpen(false);
      }
    });
  });

  navbarSearch.addEventListener("submit", (event) => {
    if (searchInput.value.trim() === "") {
      event.preventDefault();
      setSearchOpen(false, { returnFocus: true });
    }
  });

  document.addEventListener("pointerdown", (event) => {
    if (
      navbarSearch.classList.contains("is-open")
      && !navbarSearch.contains(event.target)
    ) {
      setSearchOpen(false);
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && navbarSearch.classList.contains("is-open")) {
      setSearchOpen(false, { returnFocus: true });
    }
  });
}

const navigationCollapse = document.querySelector("#navigationPrincipale");

if (navigationCollapse) {
  navigationCollapse.addEventListener("show.bs.collapse", () => {
    document.body.classList.add("menu-open");
  });

  navigationCollapse.addEventListener("hidden.bs.collapse", () => {
    document.body.classList.remove("menu-open");
  });

  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape" || !navigationCollapse.classList.contains("show")) {
      return;
    }

    bootstrap.Collapse.getOrCreateInstance(navigationCollapse).hide();
  });
}

const aboutSection = document.querySelector(".about-home-section");

if (aboutSection) {
  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  // Prépare les éléments avant leur apparition dans la fenêtre.
  aboutSection.classList.add("is-scroll-ready");

  if (prefersReducedMotion.matches || !("IntersectionObserver" in window)) {
    aboutSection.classList.add("is-visible");
  } else {
    const aboutObserver = new IntersectionObserver(
      ([entry], observer) => {
        if (!entry.isIntersecting) {
          return;
        }

        aboutSection.classList.add("is-visible");
        observer.unobserve(aboutSection);
      },
      {
        threshold: 0.18,
        rootMargin: "0px 0px -8%",
      },
    );

    aboutObserver.observe(aboutSection);
  }
}
