// path: public/js/portions.js
// Bonus portions : change le nombre de personnes et recalcule les quantités des ingrédients.
// Les quantités de base viennent de la base (data-quantity), la page n'est jamais rechargée.
const servingsBox = document.querySelector("[data-servings]");

if (servingsBox) {
  const baseServings = Number(servingsBox.dataset.servingsBase) || 1;
  const minServings = 1;
  const maxServings = 50; // même limite que la saisie admin
  const servingsLabels = document.querySelectorAll("[data-servings-value]");
  const quantities = document.querySelectorAll("[data-quantity]");
  const minusButton = servingsBox.querySelector('[data-servings-step="-1"]');
  const plusButton = servingsBox.querySelector('[data-servings-step="1"]');
  let servings = baseServings;

  // Arrondi lisible : demi-unité pour ce qui se compte (œufs), 1 décimale sous 10, entier au-delà
  const formatQuantity = (value, isCount) => {
    let rounded;
    if (isCount) {
      rounded = Math.max(0.5, Math.round(value * 2) / 2);
    } else if (value < 10) {
      rounded = Math.round(value * 10) / 10;
    } else {
      rounded = Math.round(value);
    }
    return String(rounded).replace(".", ",");
  };

  const showServings = () => {
    servingsLabels.forEach((label) => {
      label.textContent = String(servings);
    });
    quantities.forEach((quantity) => {
      const baseQuantity = Number(quantity.dataset.quantity);
      if (Number.isFinite(baseQuantity)) {
        quantity.textContent = formatQuantity(
          (baseQuantity * servings) / baseServings,
          quantity.hasAttribute("data-quantity-count"),
        );
      }
    });
    minusButton.disabled = servings <= minServings;
    plusButton.disabled = servings >= maxServings;
  };

  servingsBox.addEventListener("click", (event) => {
    const button = event.target.closest("[data-servings-step]");
    if (!button) {
      return;
    }
    servings = Math.min(
      maxServings,
      Math.max(minServings, servings + Number(button.dataset.servingsStep)),
    );
    showServings();
  });

  showServings();
}