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

const contactEye = document.querySelector(".contact-eye");

if (contactEye) {
  const pupil = contactEye.querySelector(".contact-eye-pupil");
  const followsPointer = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

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

      if (Math.abs(targetX - currentX) > 0.05 || Math.abs(targetY - currentY) > 0.05) {
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
      const reach = Math.min(distance / Math.max(eyeBounds.width * 1.2, 240), 1);

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

const aboutPage = document.querySelector(".about-page");

if (aboutPage) {
  const revealElements = aboutPage.querySelectorAll("[data-about-reveal]");
  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

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
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    let changing = false;

    // Le portrait sélectionné glisse à droite et devient le portrait principal.
    const showTeamMember = (selectedMember) => {
      if (!selectedMember || selectedMember.classList.contains("is-active") || changing) {
        return;
      }

      changing = true;
      const membersBefore = [...teamGallery.querySelectorAll(".about-team-member")];
      const oldPositions = new Map(
        membersBefore.map((member) => [member, member.getBoundingClientRect()]),
      );

      membersBefore.forEach((member) => {
        const isSelected = member === selectedMember;
        member.classList.toggle("is-active", isSelected);
        member.querySelector(".about-team-select")?.toggleAttribute("aria-current", isSelected);
      });

      teamGallery.append(selectedMember);

      if (!reducedMotion) {
        [...teamGallery.querySelectorAll(".about-team-member")].forEach((member) => {
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
        });

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
      const activeMember = teamGallery.querySelector(".about-team-member.is-active");
      const members = [...teamGallery.querySelectorAll(".about-team-member")];
      const nextMember = members[(members.indexOf(activeMember) + 1) % members.length];
      showTeamMember(nextMember);
    });
  }
}
