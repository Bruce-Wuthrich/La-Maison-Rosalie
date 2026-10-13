from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]


def text(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def css_rule(css: str, selector: str) -> str:
    match = re.search(rf"{re.escape(selector)}\s*\{{([^}}]*)\}}", css, flags=re.S)
    assert match, f"Règle CSS absente : {selector}"
    return match.group(1)


view = text("view/publicView/recipeDetailView.php")
css = text("public/css/recipe.css")

assert 'class="recipe-category"' in view, "La catégorie doit être affichée sur la fiche recette"
assert '$recipe->getDescription()' in view, "La description doit provenir de la recette"

category_rule = css_rule(css, ".recipe-category")
description_rule = css_rule(css, ".recipe-description")

assert "display: none" not in category_rule, "La catégorie est encore masquée"
assert "display: none" not in description_rule, "La description est encore masquée"
assert "var(--color-gold-text)" in category_rule, "La catégorie doit conserver un contraste lisible"
assert "var(--color-brown)" in description_rule, "La description doit conserver un contraste lisible"

print("FE30_STATIC_OK")
