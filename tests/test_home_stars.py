from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]
CSS = (ROOT / "public/css/home.css").read_text(encoding="utf-8")
VIEW = (ROOT / "view/publicView/homepageView.php").read_text(encoding="utf-8")


def css_rule(selector: str) -> str:
    match = re.search(rf"{re.escape(selector)}\s*\{{([^}}]*)\}}", CSS, flags=re.S)
    assert match, f"Règle CSS absente : {selector}"
    return match.group(1)


assert 'class="star-icon <?= $star <= $roundedRating ? \'is-filled\' : \'is-empty\' ?>"' in VIEW
assert "fill: currentColor" in css_rule(".star-icon.is-filled"), "Les étoiles notées doivent être remplies"
assert "fill: none" in css_rule(".star-icon.is-empty"), "Les étoiles restantes doivent rester vides"

print("HOME_STARS_OK")
