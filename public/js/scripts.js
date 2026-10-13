const heroWheel = document.querySelector(".hero-wheel");

if (heroWheel) {
  let rotation = 0;

  const categories = [
    {
      name: "Tartes",
      href: "?pg=recettes",
      description:
        "Découvrez des tartes chocolatées généreuses, croustillantes et fondantes, pensées pour toutes les occasions.",
      image: "assets/img/recettes/fondant-au-coeur-coulant/principale.webp",
      imageAlt: "Fondant au chocolat au cœur coulant",
    },
    {
      name: "Gâteaux",
      href: "?pg=recettes&categorie=gateaux",
      description:
        "Moelleux, brownies et fondants : retrouvez nos gâteaux au chocolat à partager en famille ou entre amis.",
      image: "assets/img/recettes/brownies-aux-noix/principale.webp",
      imageAlt: "Brownies au chocolat et aux noix",
    },
    {
      name: "Glaces",
      href: "?pg=recettes&categorie=glace",
      description:
        "Savourez des desserts glacés au chocolat, onctueux et simples à préparer, même sans sorbetière.",
      image:
        "assets/img/recettes/glace-au-chocolat-sans-sorbetiere/principale.webp",
      imageAlt: "Glace au chocolat sans sorbetière",
    },
    {
      name: "Mousses",
      href: "?pg=recettes&categorie=mousses",
      description:
        "Légères et intenses, nos mousses au chocolat jouent avec les textures pour terminer le repas en douceur.",
      image: "assets/img/recettes/mousse-au-chocolat-noir/principale.webp",
      imageAlt: "Mousse au chocolat noir",
    },
    {
      name: "Pâtisserie",
      href: "?pg=recettes",
      description:
        "Plongez dans l’univers gourmand des pâtisseries au chocolat, où il y en a pour tous les goûts. Explorez différentes textures et saveurs et laissez-vous inspirer par nos recettes.",
      image: "assets/img/recettes/fondant-au-coeur-coulant/principale.webp",
      imageAlt: "Fondant au chocolat au cœur coulant",
    },
  ];
  const heroHome = document.querySelector(".hero-home");
  const categoryLink = document.querySelector(".hero-category-current");
  const categoryButtons = document.querySelectorAll(".hero-pagination button");
  const categoryBackdrop = document.querySelector(".hero-category-backdrop");
  const categoryPanel = document.querySelector(".hero-category-panel");
  const categoryPanelClose = document.querySelector(".hero-category-close");
  const categoryPanelTitle = document.querySelector(
    ".hero-category-panel-title",
  );
  const categoryPanelCopy = document.querySelector(".hero-category-panel-copy");
  const categoryPanelImage = document.querySelector(
    ".hero-category-panel-image",
  );
  const categoryPanelLink = document.querySelector(".hero-category-panel-link");
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

  const updateCategoryPanel = (category) => {
    if (!categoryPanel) {
      return;
    }

    categoryPanelTitle.textContent = category.name;
    categoryPanelCopy.textContent = category.description;
    categoryPanelImage.src = category.image;
    categoryPanelImage.alt = category.imageAlt;
    categoryPanelLink.href = category.href;
  };

  const setCategoryPanelOpen = (isOpen, { returnFocus = false } = {}) => {
    if (!categoryPanel || !heroHome) {
      return;
    }

    heroHome.classList.toggle("is-detail-open", isOpen);
    document.body.classList.toggle("hero-detail-open", isOpen);
    categoryPanel.setAttribute("aria-hidden", String(!isOpen));
    categoryPanel.inert = !isOpen;
    categoryLink.setAttribute("aria-expanded", String(isOpen));

    if (isOpen) {
      updateCategoryPanel(categories[categoryIndex]);
      requestAnimationFrame(() =>
        categoryPanelClose.focus({ preventScroll: true }),
      );
    } else {
      if (returnFocus) {
        categoryLink.focus({ preventScroll: true });
      }

      // Le focus ne doit pas laisser le héros décalé horizontalement.
      heroHome.scrollLeft = 0;
    }
  };

  const showCategory = (index) => {
    categoryIndex = index;
    const currentCategory = categories[categoryIndex];

    categoryLink.textContent = currentCategory.name;
    categoryLink.href = currentCategory.href;
    updateCategoryPanel(currentCategory);

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

    const steps =
      (index - categoryIndex + categories.length) % categories.length;
    rotation += 30 * (steps || 1);
    heroWheel.style.transform = `rotate(${rotation}deg)`;
  };

  categoryLink.addEventListener("click", (event) => {
    if (!categoryPanel) {
      return;
    }

    event.preventDefault();
    const isOpen = heroHome.classList.contains("is-detail-open");
    setCategoryPanelOpen(!isOpen, { returnFocus: isOpen });
  });

  if (categoryPanelClose) {
    categoryPanelClose.addEventListener("click", () => {
      setCategoryPanelOpen(false, { returnFocus: true });
    });

    categoryBackdrop.addEventListener("click", () => {
      setCategoryPanelOpen(false);
    });

    heroHome.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        setCategoryPanelOpen(false, { returnFocus: true });
      }
    });

    // Réinitialise l'accueil lorsqu'on y revient avec le bouton précédent.
    if (typeof window.addEventListener === "function") {
      window.addEventListener("pageshow", () => setCategoryPanelOpen(false));
    }
  }

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
  const supportsHover = window.matchMedia(
    "(hover: hover) and (pointer: fine)",
  ).matches;

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
      navbarSearch.classList.contains("is-open") &&
      !navbarSearch.contains(event.target)
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
    if (
      event.key !== "Escape" ||
      !navigationCollapse.classList.contains("show")
    ) {
      return;
    }

    bootstrap.Collapse.getOrCreateInstance(navigationCollapse).hide();
  });
}

const aboutSection = document.querySelector(".about-home-section");

if (aboutSection) {
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  );

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

const contactEye = document.querySelector(".contact-eye");

if (contactEye) {
  const pupil = contactEye.querySelector(".contact-eye-pupil");
  const followsPointer = window.matchMedia(
    "(any-hover: hover) and (any-pointer: fine)",
  ).matches;
  const reducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  ).matches;

  if (pupil && followsPointer && !reducedMotion) {
    let currentX = 0;
    let currentY = 0;
    let targetX = 0;
    let targetY = 0;
    let animationFrame = null;

    // Approche progressivement la position demandée pour éviter un mouvement sec.
    const animatePupil = () => {
      currentX += (targetX - currentX) * 0.16;
      currentY += (targetY - currentY) * 0.16;

      pupil.style.setProperty("--pupil-x", `${currentX.toFixed(2)}px`);
      pupil.style.setProperty("--pupil-y", `${currentY.toFixed(2)}px`);

      if (
        Math.abs(targetX - currentX) > 0.05 ||
        Math.abs(targetY - currentY) > 0.05
      ) {
        animationFrame = requestAnimationFrame(animatePupil);
      } else {
        currentX = targetX;
        currentY = targetY;
        animationFrame = null;
      }
    };

    const requestPupilAnimation = () => {
      if (animationFrame === null) {
        animationFrame = requestAnimationFrame(animatePupil);
      }
    };

    const centerPupil = () => {
      targetX = 0;
      targetY = 0;
      requestPupilAnimation();
    };

    document.addEventListener("pointermove", (event) => {
      if (event.pointerType !== "mouse") {
        return;
      }

      const eyeBounds = contactEye.getBoundingClientRect();
      const eyeCenterX = eyeBounds.left + eyeBounds.width / 2;
      const eyeCenterY = eyeBounds.top + eyeBounds.height / 2;
      const deltaX = event.clientX - eyeCenterX;
      const deltaY = event.clientY - eyeCenterY;
      const distance = Math.hypot(deltaX, deltaY);

      if (distance === 0) {
        centerPupil();
        return;
      }

      const maximumX = eyeBounds.width * 0.18;
      const maximumY = eyeBounds.height * 0.12;
      const reach = Math.min(
        distance / Math.max(eyeBounds.width * 1.2, 240),
        1,
      );

      targetX = (deltaX / distance) * maximumX * reach;
      targetY = (deltaY / distance) * maximumY * reach;
      requestPupilAnimation();
    });

    document.addEventListener("pointerout", (event) => {
      if (event.relatedTarget === null) {
        centerPupil();
      }
    });

    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        centerPupil();
      }
    });
  }
}

const commentMessage = document.querySelector("#commentMessage");
const commentCounter = document.querySelector("[data-comment-counter]");
if (commentMessage && commentCounter) {
  const updateCommentCounter = () => {
    commentCounter.textContent = `${commentMessage.value.length}/500 caractères`;
  };
  updateCommentCounter();
  commentMessage.addEventListener("input", updateCommentCounter);
}

// Rend fonctionnels les boutons de la fiche recette sans requête serveur.
const recipeFavorite = document.querySelector(
  ".recipe-favorite[data-recipe-slug]",
);
const favoriteStorageKey = "maisonRosalieFavoriteRecipes";

if (recipeFavorite) {
  const recipeSlug = recipeFavorite.dataset.recipeSlug;
  let favoriteRecipes = [];

  try {
    const savedFavorites = JSON.parse(
      localStorage.getItem(favoriteStorageKey) ?? "[]",
    );
    favoriteRecipes = Array.isArray(savedFavorites)
      ? savedFavorites.filter((slug) => typeof slug === "string")
      : [];
  } catch {
    favoriteRecipes = [];
  }

  const showFavoriteState = (isFavorite) => {
    recipeFavorite.classList.toggle("is-active", isFavorite);
    recipeFavorite.setAttribute("aria-pressed", String(isFavorite));
    recipeFavorite.setAttribute(
      "aria-label",
      isFavorite ? "Retirer des favoris" : "Ajouter aux favoris",
    );

    const tooltip = recipeFavorite.querySelector(".recipe-favorite-tooltip");
    if (tooltip) {
      tooltip.textContent = isFavorite ? "Enregistré" : "Enregistrez";
    }
  };

  showFavoriteState(favoriteRecipes.includes(recipeSlug));

  recipeFavorite.addEventListener("click", () => {
    const isFavorite = favoriteRecipes.includes(recipeSlug);
    favoriteRecipes = isFavorite
      ? favoriteRecipes.filter((slug) => slug !== recipeSlug)
      : [...favoriteRecipes, recipeSlug];

    try {
      localStorage.setItem(favoriteStorageKey, JSON.stringify(favoriteRecipes));
    } catch {
      // L'état visuel reste utilisable même si le stockage est bloqué.
    }

    showFavoriteState(!isFavorite);
  });
}

const printRecipeButton = document.querySelector("#printRecipe");
if (printRecipeButton) {
  printRecipeButton.addEventListener("click", () => window.print());
}

const shareRecipeButton = document.querySelector("#shareRecipe");
const recipeActionStatus = document.querySelector(
  "[data-recipe-action-status]",
);

if (shareRecipeButton) {
  const showShareStatus = (message) => {
    if (recipeActionStatus) {
      recipeActionStatus.textContent = message;
    }
  };

  shareRecipeButton.addEventListener("click", async () => {
    const shareData = {
      title: document.title,
      url: window.location.href,
    };

    try {
      if (typeof navigator.share === "function") {
        await navigator.share(shareData);
        showShareStatus("Recette partagée.");
        return;
      }

      await navigator.clipboard.writeText(shareData.url);
      showShareStatus("Lien copié dans le presse-papiers.");
    } catch (error) {
      if (error?.name !== "AbortError") {
        showShareStatus("Le partage n’est pas disponible sur ce navigateur.");
      }
    }
  });
}

const aboutPage = document.querySelector(".about-page");

if (aboutPage) {
  const revealElements = aboutPage.querySelectorAll("[data-about-reveal]");
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  ).matches;

  if (!prefersReducedMotion && "IntersectionObserver" in window) {
    aboutPage.classList.add("is-reveal-ready");

    const revealObserver = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        });
      },
      {
        threshold: 0.14,
        rootMargin: "0px 0px -7%",
      },
    );

    revealElements.forEach((element) => revealObserver.observe(element));
  } else {
    revealElements.forEach((element) => element.classList.add("is-visible"));
  }

  const teamGallery = aboutPage.querySelector(".about-team-gallery");
  const teamCopy = aboutPage.querySelector(".about-founder-copy");
  const nextMemberButton = aboutPage.querySelector(".about-round-link");

  if (teamGallery && teamCopy && nextMemberButton) {
    const title = teamCopy.querySelector("h2");
    const role = teamCopy.querySelector(".about-founder-role");
    const biography = teamCopy.querySelector(".about-founder-biography");
    const reducedMotion = window.matchMedia(
      "(prefers-reduced-motion: reduce)",
    ).matches;
    let changing = false;

    // Le portrait sélectionné glisse à droite et devient le portrait principal.
    const showTeamMember = (selectedMember) => {
      if (
        !selectedMember ||
        selectedMember.classList.contains("is-active") ||
        changing
      ) {
        return;
      }

      changing = true;
      const membersBefore = [
        ...teamGallery.querySelectorAll(".about-team-member"),
      ];
      const oldPositions = new Map(
        membersBefore.map((member) => [member, member.getBoundingClientRect()]),
      );

      membersBefore.forEach((member) => {
        const isSelected = member === selectedMember;
        member.classList.toggle("is-active", isSelected);
        member
          .querySelector(".about-team-select")
          ?.toggleAttribute("aria-current", isSelected);
      });

      teamGallery.append(selectedMember);

      if (!reducedMotion) {
        [...teamGallery.querySelectorAll(".about-team-member")].forEach(
          (member) => {
            const previous = oldPositions.get(member);
            const current = member.getBoundingClientRect();

            member.animate(
              [
                {
                  transform: `translate(${previous.left - current.left}px, ${previous.top - current.top}px)`,
                },
                { transform: "translate(0, 0)" },
              ],
              {
                duration: 700,
                easing: "cubic-bezier(0.22, 1, 0.36, 1)",
              },
            );
          },
        );

        teamCopy.classList.add("is-changing");
      }

      window.setTimeout(
        () => {
          title.textContent = selectedMember.dataset.name;
          role.textContent = selectedMember.dataset.role;
          biography.textContent = selectedMember.dataset.biography;
          teamCopy.classList.remove("is-changing");
          changing = false;
        },
        reducedMotion ? 0 : 210,
      );
    };

    teamGallery.addEventListener("click", (event) => {
      showTeamMember(event.target.closest(".about-team-member"));
    });

    nextMemberButton.addEventListener("click", () => {
      const activeMember = teamGallery.querySelector(
        ".about-team-member.is-active",
      );
      const members = [...teamGallery.querySelectorAll(".about-team-member")];
      const nextMember =
        members[(members.indexOf(activeMember) + 1) % members.length];
      showTeamMember(nextMember);
    });
  }
}
