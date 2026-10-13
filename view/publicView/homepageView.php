<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>
<?php
// Utilise le chemin de la BDD s'il existe, sinon l'image locale de la recette.
$recipeImagePath = static function ($recipe): string {
    $image = (string) $recipe->getMainImage();

    if (!is_file(RACINE_PATH . '/public/' . $image)) {
        $image = 'assets/img/recettes/' . $recipe->getSlug() . '/principale.webp';
    }

    return $image;
};
?>
<main class="home-main">
    <div class="hero-top-marker" aria-hidden="true">
        <img src="assets/img/logo/picto.svg" alt="">
    </div>

    <section class="hero-home">
        <h1 class="visually-hidden">Maison Rosalie</h1>

        <div class="hero-emblem">
            <button class="hero-wheel" type="button" aria-label="Faire tourner les catégories">
                <img class="hero-sunburst" src="assets/img/logo/sunburst-home.png" alt="" aria-hidden="true">
            </button>

            <img class="hero-logo" src="assets/img/logo/logo.svg" alt="Maison Rosalie">

            <a class="hero-category-current is-active" href="?pg=recettes" aria-live="polite"
                aria-expanded="false" aria-controls="heroCategoryPanel">
                Pâtisserie
            </a>
        </div>

        <div class="hero-pagination" role="group" aria-label="Choisir une catégorie">
            <button type="button" data-category-index="0" aria-label="Afficher la catégorie Tartes"></button>
            <button type="button" data-category-index="1" aria-label="Afficher la catégorie Gâteaux"></button>
            <button type="button" data-category-index="2" aria-label="Afficher la catégorie Glaces"></button>
            <button type="button" data-category-index="3" aria-label="Afficher la catégorie Mousses"></button>
            <button class="is-active" type="button" data-category-index="4" aria-label="Afficher la catégorie Pâtisserie" aria-current="true"></button>
        </div>

        <button class="hero-category-backdrop" type="button"
            aria-label="Fermer la présentation de la catégorie"></button>

        <aside id="heroCategoryPanel" class="hero-category-panel" aria-hidden="true" inert>
            <button class="hero-category-close" type="button" aria-label="Fermer la présentation de la catégorie">
                <span aria-hidden="true">×</span>
            </button>

            <div class="hero-category-panel-content">
                <h2 class="hero-category-panel-title">Pâtisserie</h2>
                <p class="hero-category-panel-copy">
                    Plongez dans l’univers gourmand des pâtisseries au chocolat, où il y en a pour tous les goûts.
                    Explorez différentes textures et saveurs et laissez-vous inspirer par nos recettes.
                </p>
                <a class="hero-category-panel-link" href="?pg=recettes">
                    Découvrir les recettes <span aria-hidden="true">⟶</span>
                </a>
                <img class="hero-category-panel-image"
                    src="assets/img/recettes/fondant-au-coeur-coulant/principale.webp"
                    alt="Fondant au chocolat au cœur coulant">
            </div>
        </aside>

        <p class="hero-tagline">
            <span>Des créations d’exception</span>
            <span>pour des moments</span>
            <span>précieux</span>
        </p>
    </section>

    <section class="about-home-section" aria-labelledby="about-home-title">
        <div class="container">
            <h2 id="about-home-title" class="about-home-title">Qui sommes nous</h2>

            <div class="about-home-layout">
                <img
                    class="about-home-chocolate"
                    src="assets/img/home/about-chocolate.gif"
                    alt="Préparation d’un dessert au chocolat"
                    loading="lazy">

                <p class="about-home-copy">
                    Depuis 1987, la famille Moreau perpétue la tradition des chocolatiers artisanaux belges.
                    Chaque recette est transmise de génération en génération avec amour et savoir-faire.
                </p>

                <img
                    class="about-home-women"
                    src="assets/img/home/about-women.webp"
                    alt="Deux femmes dégustant une pâtisserie en terrasse"
                    loading="lazy">

                <div class="about-home-more">
                    <a class="about-home-link" href="?pg=a-propos">
                        <span>Notre histoire</span>
                        <span aria-hidden="true">⟶</span>
                    </a>

                    <img
                        class="about-home-books"
                        src="assets/img/home/about-books.webp"
                        alt="Livres anciens dans une bibliothèque"
                        loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <section class="home-recipes-section time-section" aria-labelledby="time-recipes-title">
        <div class="container">
            <h2 id="time-recipes-title" class="home-section-title">Quand le temps compte</h2>

            <?php if ($timeRecipes === []): ?>
                <p class="home-empty-state">Les recettes seront bientôt disponibles.</p>
            <?php else: ?>
                <div class="time-grid">
                    <?php foreach ($timeRecipes as $recipe): ?>
                        <a class="time-card" href="?pg=recette&amp;slug=<?= htmlspecialchars((string) $recipe->getSlug(), ENT_QUOTES, 'UTF-8') ?>">
                            <span class="time-card-duration"><?= $recipe->getTotalTime() ?> min</span>
                            <img
                                src="<?= htmlspecialchars($recipeImagePath($recipe), ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars((string) $recipe->getTitle(), ENT_QUOTES, 'UTF-8') ?>"
                                loading="lazy">
                            <span class="time-card-title"><?= htmlspecialchars((string) $recipe->getTitle(), ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="home-recipes-section stars-section" aria-labelledby="star-recipes-title">
        <div class="container">
            <h2 id="star-recipes-title" class="home-section-title">Les recettes star</h2>

            <?php if ($topRecipes === []): ?>
                <p class="home-empty-state">Le classement sera disponible après les premières notes.</p>
            <?php else: ?>
                <div class="star-grid">
                    <?php foreach ($topRecipes as $recipe): ?>
                        <?php $roundedRating = (int) round($recipe->getAverageRating() ?? 0); ?>
                        <article class="star-card">
                            <p class="star-card-rating" role="img" aria-label="Note : <?= $recipe->getFormattedAverage() !== '' ? $recipe->getFormattedAverage() . ' sur 5' : 'pas encore notée' ?>">
                                <?php for ($star = 1; $star <= 5; $star++): ?>
                                    <svg
                                        class="star-icon <?= $star <= $roundedRating ? 'is-filled' : 'is-empty' ?>"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                        focusable="false">
                                        <path d="M12 2.25 14.92 8.17 21.45 9.12 16.72 13.73 17.84 20.24 12 17.17 6.16 20.24 7.28 13.73 2.55 9.12 9.08 8.17 12 2.25Z" />
                                    </svg>
                                <?php endfor; ?>
                            </p>
                            <a href="?pg=recette&amp;slug=<?= htmlspecialchars((string) $recipe->getSlug(), ENT_QUOTES, 'UTF-8') ?>">
                                <img
                                    src="<?= htmlspecialchars($recipeImagePath($recipe), ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars((string) $recipe->getTitle(), ENT_QUOTES, 'UTF-8') ?>"
                                    loading="lazy">
                                <h3><?= htmlspecialchars((string) $recipe->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require RACINE_PATH . '/view/inc/footer.php'; ?>