// Recherche instantanée des cartes par titre et ingrédients.
(() => {
    const searchInput = document.querySelector("#navbarSearchInput");
    const cards = [...document.querySelectorAll(".recipes-grid .recipe-card[data-search]")];
    const status = document.querySelector("[data-live-search-status]");

    if (!searchInput || cards.length === 0 || !status) {
        return;
    }

    const normalize = (value) => value
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/œ/g, "oe")
        .replace(/æ/g, "ae")
        .trim();

    const searchableCards = cards.map((card) => ({
        card,
        text: normalize(card.dataset.search ?? ""),
    }));

    const filterCards = () => {
        const query = normalize(searchInput.value);
        const terms = query.split(/\s+/).filter(Boolean);
        let visibleCount = 0;

        searchableCards.forEach(({ card, text }) => {
            const matches = terms.every((term) => text.includes(term));
            card.hidden = !matches;

            if (matches) {
                visibleCount += 1;
            }
        });

        status.textContent = query === ""
            ? `${visibleCount} recette${visibleCount > 1 ? "s" : ""} affichée${visibleCount > 1 ? "s" : ""}.`
            : `${visibleCount} recette${visibleCount > 1 ? "s" : ""} trouvée${visibleCount > 1 ? "s" : ""} pendant la recherche.`;
    };

    searchInput.addEventListener("input", filterCards);
    filterCards();
})();
