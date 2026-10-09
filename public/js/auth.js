const authModal = document.querySelector("#authModal");
const authOpenButton = document.querySelector("[data-auth-open]");

if (authModal && authOpenButton) {
  const authPanels = authModal.querySelectorAll("[data-auth-panel]");
  const authRoleButtons = authModal.querySelectorAll("[data-auth-role]");
  const loginModeInput = authModal.querySelector("[data-auth-login-mode]");
  const loginEmailInput = authModal.querySelector('input[name="email"]');
  const rememberEmailInput = authModal.querySelector("[data-auth-remember-email]");
  const loginForm = authModal.querySelector('.auth-login-panel form');
  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  let previousFocus = null;
  let isSwitchingPanel = false;

  const focusFirstPanelInput = (panel) => {
    const firstInput = panel?.querySelector("input:not([type='hidden'])");
    window.setTimeout(() => firstInput?.focus(), 0);
  };

  const showPanel = (panelName, animate = true) => {
    const nextPanel = authModal.querySelector(`[data-auth-panel="${panelName}"]`);
    const currentPanel = [...authPanels].find((panel) => !panel.hidden);

    if (!nextPanel || isSwitchingPanel) {
      return;
    }

    if (currentPanel === nextPanel) {
      focusFirstPanelInput(nextPanel);
      return;
    }

    if (!animate || prefersReducedMotion.matches || typeof nextPanel.animate !== "function") {
      authPanels.forEach((panel) => {
        panel.hidden = panel !== nextPanel;
      });
      focusFirstPanelInput(nextPanel);
      return;
    }

    isSwitchingPanel = true;
    nextPanel.hidden = false;
    currentPanel.classList.add("is-switching");
    nextPanel.classList.add("is-switching");

    const direction = panelName === "register" ? 1 : -1;
    const animationOptions = {
      duration: 430,
      easing: "cubic-bezier(0.22, 1, 0.36, 1)",
      fill: "both",
    };

    const outgoingAnimation = currentPanel.animate(
      [
        { opacity: 1, transform: "translateX(0)" },
        { opacity: 0, transform: `translateX(${-42 * direction}px)` },
      ],
      animationOptions,
    );
    const incomingAnimation = nextPanel.animate(
      [
        { opacity: 0, transform: `translateX(${42 * direction}px)` },
        { opacity: 1, transform: "translateX(0)" },
      ],
      animationOptions,
    );

    Promise.allSettled([outgoingAnimation.finished, incomingAnimation.finished]).then(() => {
      currentPanel.hidden = true;
      currentPanel.classList.remove("is-switching");
      nextPanel.classList.remove("is-switching");
      outgoingAnimation.cancel();
      incomingAnimation.cancel();
      isSwitchingPanel = false;
      focusFirstPanelInput(nextPanel);
    });
  };

  const openAuthModal = (panelName = "login") => {
    previousFocus = document.activeElement;
    authModal.hidden = false;
    document.body.classList.add("auth-open");
    showPanel(panelName, false);
  };

  const closeAuthModal = () => {
    authModal.hidden = true;
    document.body.classList.remove("auth-open");

    if (window.location.hash === "#authModal") {
      history.replaceState(null, "", `${window.location.pathname}${window.location.search}`);
    }

    previousFocus?.focus();
  };

  authOpenButton.addEventListener("click", () => openAuthModal("login"));

  authModal.querySelectorAll("[data-auth-close]").forEach((button) => {
    button.addEventListener("click", closeAuthModal);
  });

  authModal.querySelectorAll("[data-auth-show]").forEach((button) => {
    button.addEventListener("click", () => showPanel(button.dataset.authShow));
  });

  authRoleButtons.forEach((button) => {
    button.addEventListener("click", () => {
      authRoleButtons.forEach((roleButton) => {
        const isActive = roleButton === button;
        roleButton.classList.toggle("is-active", isActive);
        roleButton.setAttribute("aria-selected", String(isActive));
      });

      loginModeInput.value = button.dataset.authRole;
    });
  });

  try {
    const rememberedEmail = localStorage.getItem("maisonRosalieLoginEmail");

    if (rememberedEmail && !loginEmailInput.value) {
      loginEmailInput.value = rememberedEmail;
      rememberEmailInput.checked = true;
    }
  } catch (error) {
    // Le formulaire reste utilisable si le stockage du navigateur est indisponible.
  }

  loginForm.addEventListener("submit", () => {
    try {
      if (rememberEmailInput.checked) {
        localStorage.setItem("maisonRosalieLoginEmail", loginEmailInput.value);
      } else {
        localStorage.removeItem("maisonRosalieLoginEmail");
      }
    } catch (error) {
      // La connexion ne dépend pas du stockage facultatif de l’e-mail.
    }
  });

  authModal.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeAuthModal();
      return;
    }

    if (event.key !== "Tab") {
      return;
    }

    const focusableElements = [...authModal.querySelectorAll(
      'button:not([disabled]):not([hidden]), input:not([disabled]):not([type="hidden"]), a[href]',
    )].filter((element) => element.offsetParent !== null);

    if (focusableElements.length === 0) {
      return;
    }

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (event.shiftKey && document.activeElement === firstElement) {
      event.preventDefault();
      lastElement.focus();
    } else if (!event.shiftKey && document.activeElement === lastElement) {
      event.preventDefault();
      firstElement.focus();
    }
  });

  if (window.location.hash === "#authModal") {
    openAuthModal(authModal.dataset.initialPanel || "login");
  }
}
