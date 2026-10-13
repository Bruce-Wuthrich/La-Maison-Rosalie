from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]


def text(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


footer = text("view/inc/footer.php")
for page in ("accueil", "recettes", "a-propos", "contact", "mentions-legales"):
    assert f"?pg={page}" in footer, f"Lien de footer manquant : {page}"
assert 'aria-label="Navigation du pied de page"' in footer

index = text("public/index.php")
home_controller = text("controller/HomeController.php")
header = text("view/inc/header.php")
assert "'mentions-legales'" in index, "Route Mentions légales absente"
assert "legalView.php" in home_controller, "Vue légale non distribuée par HomeController"
assert (ROOT / "view/publicView/legalView.php").is_file(), "Vue Mentions légales absente"
assert (ROOT / "public/css/legal.css").is_file(), "Styles Mentions légales absents"
assert "$pageDescriptions" in header, "Descriptions SEO non centralisées"
assert '<meta name="description"' in header, "Balise meta description absente"
assert "$recipe->getDescription()" in header, "Description SEO des recettes non dynamique"

about = text("view/publicView/aboutView.php")
images = re.findall(r"<img\b[^>]*>", about, flags=re.I | re.S)
content_images = [image for image in images if "title-spark.svg" not in image]
assert content_images, "Aucune image de contenu trouvée sur À propos"
assert all('loading="lazy"' in image for image in content_images), "Lazy-loading incomplet sur À propos"

contrast_files = [
    "public/css/base.css",
    "public/css/contact.css",
    "public/css/error.css",
    "public/css/recipes.css",
    "public/css/recipe.css",
]
for path in contrast_files:
    css = text(path)
    assert "color: var(--color-gold);" not in css, f"Texte jaune insuffisamment contrasté dans {path}"
for weak_background in ("#ad7800", "#a87400", "#a97200"):
    assert all(weak_background not in text(path) for path in contrast_files), (
        f"Fond trop clair avec texte blanc : {weak_background}"
    )

about_css = text("public/css/about.css")
assert "color: rgba(151, 103, 0, 0.6);" not in about_css, "Rôle de la fondatrice trop peu contrasté"
assert "background: rgba(21, 13, 9, 0.65);" in about_css, "Voile de la bannière À propos insuffisant"

contact_view = text("view/publicView/contactView.php")
assert 'class="row align-items-center g-4"' in contact_view, "La grille Contact déborde à 360 px"

view_files = list((ROOT / "view").rglob("*.php"))
assert all('action=""' not in path.read_text(encoding="utf-8") for path in view_files), "Formulaire avec action vide"
assert '<nav class="footer-social" aria-label="Réseaux sociaux de Maison Rosalie">' in footer, "Le groupe social doit être une navigation"
auth_modal = text("view/inc/authModal.php")
auth_js = text("public/js/auth.js")
assert 'role="group" aria-label="Type de connexion"' in auth_modal, "Rôles de connexion mal définis"
assert 'role="tab"' not in auth_modal, "Onglets sans panneaux associés"
assert 'aria-pressed' in auth_modal and 'aria-pressed' in auth_js, "État des boutons de rôle non exposé"

homepage = text("view/publicView/homepageView.php")
assert 'class="hero-pagination" role="group"' in homepage, "Pagination des catégories sans rôle accessible"
assert 'class="star-card-rating" role="img"' in homepage, "Notes du Top 3 sans rôle accessible"
recipe_view = text("view/publicView/recipeDetailView.php")
for accessible_role in ('class="recipe-rating" role="group"', 'class="comment-rating-input" role="radiogroup"', 'class="comment-stars" role="img"'):
    assert accessible_role in recipe_view, f"Rôle accessible manquant : {accessible_role}"
assert '<ul class="recipe-tags-list">' in recipe_view and 'role="listitem"' not in recipe_view, "Liste des tags invalide"
recipes_js = text("public/js/recettes.js")
assert "stars.setAttribute('role', 'img');" in recipes_js, "Rôle accessible absent des notes ajoutées dynamiquement"
assert '<div class="recipes-catalog">' in text("view/publicView/recipesView.php"), "Section catalogue sans titre"
assert '<div class="about-recipes" data-about-reveal>' in text("view/publicView/aboutView.php"), "Section À propos sans titre"
assert '<div class="contact-section">' in contact_view and '<div class="contact-presentation">' in contact_view, "Sections Contact sans niveau de titre cohérent"
legal_css = text("public/css/legal.css")
assert "var(--color-cream)" not in legal_css, "La page Mentions légales doit rester entièrement blanche"

print("PRIORITY2_STATIC_OK")
