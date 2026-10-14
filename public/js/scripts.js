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
  const previousCategory = document.querySelector(
    '[data-category-neighbor="previous"]',
  );
  const nextCategory = document.querySelector(
    '[data-category-neighbor="next"]',
  );
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
  const rotationToggle = document.querySelector(".hero-rotation-toggle");
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  let autoRotationTimer;
  let rotationFallback;
  let userPaused = false;
  let heroVisible = false;

  // Un délai neuf après chaque interaction évite une rotation inattendue.
  const scheduleAutoRotation = () => {
    clearTimeout(autoRotationTimer);
    const activeElement = document.activeElement;
    // Un clic souris ne doit pas laisser l'automatisme bloqué par le focus.
    // La navigation au clavier garde une pause pour utiliser les commandes.
    const controlsFocused =
      heroHome.contains(activeElement) &&
      activeElement !== rotationToggle &&
      activeElement.matches(":focus-visible");
    if (
      userPaused ||
      reducedMotion.matches ||
      document.hidden ||
      !heroVisible ||
      heroWheel.disabled ||
      controlsFocused ||
      heroHome.classList.contains("is-detail-open") ||
      document.body.matches(".menu-open, .auth-open, .search-open")
    ) {
      return;
    }
    autoRotationTimer = setTimeout(() => {
      rotateToCategory((categoryIndex + 1) % categories.length, {
        automatic: true,
      });
    }, 4000);
  };

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
    scheduleAutoRotation();

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
    clearTimeout(rotationFallback);
    heroHome.classList.remove("is-rotating");
    categoryIndex = index;
    const currentCategory = categories[categoryIndex];

    categoryLink.textContent = currentCategory.name;
    categoryLink.href = currentCategory.href;
    const previous =
      categories[(categoryIndex - 1 + categories.length) % categories.length];
    const next = categories[(categoryIndex + 1) % categories.length];
    previousCategory.textContent = previous.name;
    previousCategory.setAttribute(
      "aria-label",
      `Afficher la catégorie ${previous.name}`,
    );
    nextCategory.textContent = next.name;
    nextCategory.setAttribute(
      "aria-label",
      `Afficher la catégorie ${next.name}`,
    );
    previousCategory.disabled = false;
    nextCategory.disabled = false;
    updateCategoryPanel(currentCategory);

    showActiveIndicator(categoryIndex);

    void categoryLink.offsetWidth;
    categoryLink.classList.add("is-active");
    heroWheel.disabled = false;
    categoryButtons.forEach((button) => {
      button.disabled = false;
    });
    scheduleAutoRotation();
  };

  const rotateToCategory = (index, { automatic = false } = {}) => {
    if (heroWheel.disabled || index === categoryIndex) {
      return;
    }

    clearTimeout(autoRotationTimer);
    heroHome.classList.add("is-rotating");
    categoryLink.setAttribute("aria-live", automatic ? "off" : "polite");
    categoryButtons.forEach((button) => {
      button.disabled = true;
    });
    heroWheel.disabled = true;
    previousCategory.disabled = true;
    nextCategory.disabled = true;
    categoryLink.classList.remove("is-active");
    nextCategoryIndex = index;

    // Le voisin du bas remonte vers le rayon horizontal ; celui du haut descend.
    let steps = (index - categoryIndex + categories.length) % categories.length;
    if (steps > categories.length / 2) steps -= categories.length;
    rotation -= 30 * steps;
    heroWheel.style.transform = `rotate(${rotation}deg)`;
    if (reducedMotion.matches) {
      showCategory(nextCategoryIndex);
    } else {
      // Réactive aussi les commandes si le navigateur interrompt la transition.
      rotationFallback = setTimeout(
        () => showCategory(nextCategoryIndex),
        2600,
      );
    }
  };

  categoryLink.addEventListener("click", (event) => {
    if (!categoryPanel) {
      return;
    }

    event.preventDefault();
    if (heroWheel.disabled) return;
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

  previousCategory.addEventListener("click", () => {
    rotateToCategory(
      (categoryIndex - 1 + categories.length) % categories.length,
    );
  });
  nextCategory.addEventListener("click", () => {
    rotateToCategory((categoryIndex + 1) % categories.length);
  });

  showCategory(categoryIndex);

  heroWheel.addEventListener("click", () => {
    rotateToCategory((categoryIndex + 1) % categories.length);
  });

  categoryButtons.forEach((button) => {
    button.addEventListener("click", () => {
      rotateToCategory(Number(button.dataset.categoryIndex));
    });
  });

  heroWheel.addEventListener("transitionend", (event) => {
    if (
      event.target === heroWheel &&
      event.propertyName === "transform" &&
      heroWheel.disabled
    ) {
      showCategory(nextCategoryIndex);
    }
  });

  rotationToggle.hidden = reducedMotion.matches;
  rotationToggle.addEventListener("click", () => {
    userPaused = !userPaused;
    rotationToggle.textContent = userPaused ? "Reprendre" : "Pause";
    rotationToggle.setAttribute("aria-pressed", String(userPaused));
    rotationToggle.setAttribute(
      "aria-label",
      userPaused
        ? "Reprendre la rotation automatique"
        : "Mettre en pause la rotation automatique",
    );
    scheduleAutoRotation();
  });

  heroHome.addEventListener("focusin", scheduleAutoRotation);
  heroHome.addEventListener("focusout", () =>
    setTimeout(scheduleAutoRotation, 0),
  );
  document.addEventListener("visibilitychange", scheduleAutoRotation);
  reducedMotion.addEventListener("change", () => {
    rotationToggle.hidden = reducedMotion.matches;
    if (reducedMotion.matches && heroWheel.disabled)
      showCategory(nextCategoryIndex);
    scheduleAutoRotation();
  });
  new IntersectionObserver(
    ([entry]) => {
      heroVisible = entry.isIntersecting && entry.intersectionRatio >= 0.2;
      scheduleAutoRotation();
    },
    { threshold: [0, 0.2] },
  ).observe(heroHome);
  new MutationObserver(scheduleAutoRotation).observe(document.body, {
    attributes: true,
    attributeFilter: ["class"],
  });
  window.addEventListener("pagehide", () => {
    clearTimeout(autoRotationTimer);
    clearTimeout(rotationFallback);
  });
  window.addEventListener("pageshow", () => {
    if (heroWheel.disabled) showCategory(nextCategoryIndex);
    scheduleAutoRotation();
  });
}

// Conserve un seul formulaire de recherche et un seul ensemble de contrôles
// de compte : leurs écouteurs JS restent actifs lorsqu'on les déplace.
const mobileNavActions = document.querySelector("#navbarMobileActions");
const desktopNavLeft = document.querySelector(".navbar-actions-left");
const desktopNavRight = document.querySelector(".navbar-actions-right");
const mobileNavBreakpoint = window.matchMedia("(max-width: 767.98px)");

if (mobileNavActions && desktopNavLeft && desktopNavRight) {
  const searchElement = desktopNavLeft.querySelector("[data-navbar-search]");
  const accountElements = [
    desktopNavRight.querySelector("[data-auth-member]"),
    desktopNavRight.querySelector("[data-auth-logout]"),
    desktopNavRight.querySelector("[data-auth-open]"),
  ].filter(Boolean);
  const burgerToggle = desktopNavRight.querySelector(".navbar-toggler");

  const syncNavbarActions = () => {
    if (mobileNavBreakpoint.matches) {
      if (searchElement) mobileNavActions.append(searchElement);
      accountElements.forEach((element) => mobileNavActions.append(element));
    } else {
      if (searchElement) desktopNavLeft.append(searchElement);
      accountElements.forEach((element) =>
        desktopNavRight.insertBefore(element, burgerToggle),
      );
    }
  };

  syncNavbarActions();
  mobileNavBreakpoint.addEventListener("change", syncNavbarActions);
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
    // Sur mobile, la recherche est déjà dans le menu burger : ne pas
    // conserver search-open après sa fermeture, car cela suspend la hero.
    document.body.classList.toggle(
      "search-open",
      isOpen && !navbarSearch.closest("#navbarMobileActions"),
    );
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
    navbarSearch.classList.contains("is-open") &&
      !navbarSearch.closest("#navbarMobileActions"),
  );

  // Nettoie l'état de la recherche après fermeture du burger sur mobile.
  // Sinon la pause de rotation du soleil peut rester active.
  document
    .querySelector("#navigationPrincipale")
    ?.addEventListener("hidden.bs.collapse", () => {
      if (navbarSearch.closest("#navbarMobileActions")) {
        setSearchOpen(false);
      }
    });

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