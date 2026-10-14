from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]


def text(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def css_rule(css: str, selector: str) -> str:
    match = re.search(rf"{re.escape(selector)}\s*\{{([^}}]*)\}}", css, flags=re.S)
    assert match, f"Règle CSS absente : {selector}"
    return match.group(1)


filled_icon = text("public/assets/img/recettes/icons/star-filled.svg")
outline_icon = text("public/assets/img/recettes/icons/star-outline.svg")
home_css = text("public/css/home.css")
recipe_css = text("public/css/recipe.css")

# Accueil : les étoiles correspondant à la note sont remplies.
assert "fill: currentColor" in css_rule(home_css, ".star-icon.is-filled")
assert "fill: none" in css_rule(home_css, ".star-icon.is-empty")

# Catalogue, fiche, commentaires et administration utilisent ces deux SVG partagés.
assert 'fill="#f1b300"' in filled_icon, "L’icône star-filled est devenue une simple étoile vide"
assert 'fill="none"' in outline_icon, "L’icône star-outline doit rester vide"

# Formulaire de commentaire : une note sélectionnée doit remplir les étoiles.
selected_rating_rule = css_rule(
    recipe_css,
    ".comment-rating-input input:checked ~ label,\n.comment-rating-input label:hover,\n.comment-rating-input label:hover ~ label",
)
assert "color: transparent" not in selected_rating_rule, "Les étoiles sélectionnées restent transparentes"
assert re.search(r"color:\s*(?:#a87500|var\(--color-gold(?:-text)?\))", selected_rating_rule), (
    "Les étoiles sélectionnées doivent recevoir une couleur visible"
)

print("SITE_STARS_OK")
