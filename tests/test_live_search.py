from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def text(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


manager = text("model/manager/RecipeManager.php")
mapping = text("model/mapping/RecipeMapping.php")
view = text("view/publicView/recipesView.php")
footer = text("view/inc/footer.php")
script_path = ROOT / "public/js/recherche.js"

assert "AS ingredient_names" in manager, "Les ingrédients ne sont pas fournis avec les cartes"
assert "getIngredientNames" in mapping and "setIngredientNames" in mapping, "Le mapping ne porte pas les ingrédients recherchables"
assert "data-search=" in view and "getIngredientNames()" in view, "Les cartes n’exposent pas le titre et les ingrédients"
assert "data-live-search-status" in view and 'aria-live="polite"' in view, "Le résultat du filtrage n’est pas annoncé"
assert script_path.is_file(), "Le script recherche.js est absent"

script = script_path.read_text(encoding="utf-8")
assert 'addEventListener("input"' in script, "La recherche ne réagit pas pendant la frappe"
assert '.normalize("NFD")' in script, "La recherche ne normalise pas les accents"
assert '.replace(/œ/g, "oe")' in script, "La recherche ne normalise pas la ligature œ"
assert ".hidden =" in script, "Les cartes ne sont pas masquées avec hidden"
assert "recherche.js" in footer and "filemtime" in footer, "Le script de recherche n’est pas chargé avec versionnement"

print("LIVE_SEARCH_STATIC_OK")
