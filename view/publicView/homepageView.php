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
        <img src="assets/img/logo/picto.svg?v=<?= (int) $iconVersion ?>" alt="">
    </div>

    <section class="hero-home">
        <h1 class="visually-hidden">Maison Rosalie</h1>

        <?php
        $heroCount = count($heroCategories);
        $firstCategory = $heroCategories[0] ?? null;
        $lastCategory = $heroCategories[$heroCount - 1] ?? null;
        $nextCategory = $heroCategories[1] ?? $firstCategory;
        ?>
        <script type="application/json" id="heroCategoriesData"><?= json_encode(
            $heroCategories,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        ) ?></script>

        <div class="hero-emblem">
            <button class="hero-wheel" type="button" aria-label="Faire tourner les catégories" <?= $heroCount < 2 ? 'disabled' : '' ?>>
                <img class="hero-sunburst" src="assets/img/logo/sunburst-home.png" alt="" aria-hidden="true">
            </button>

            <img class="hero-logo" src="assets/img/logo/logo.svg?v=<?= (int) $iconVersion ?>" alt="Maison Rosalie">

            <?php if ($firstCategory !== null): ?>
                <div class="hero-category-stack" aria-label="Catégories de recettes">
                    <button class="hero-category-preview" type="button" data-category-neighbor="previous"
                        aria-label="Afficher la catégorie <?= htmlspecialchars($lastCategory['name'], ENT_QUOTES, 'UTF-8') ?>"
                        <?= $heroCount < 2 ? 'hidden disabled' : '' ?>><?= htmlspecialchars($lastCategory['name'], ENT_QUOTES, 'UTF-8') ?></button>
                    <a class="hero-category-current is-active" href="<?= htmlspecialchars($firstCategory['href'], ENT_QUOTES, 'UTF-8') ?>" aria-live="off"
                        aria-expanded="false" aria-controls="heroCategoryPanel"><?= htmlspecialchars($firstCategory['name'], ENT_QUOTES, 'UTF-8') ?></a>
                    <button class="hero-category-preview" type="button" data-category-neighbor="next"
                        aria-label="Afficher la catégorie <?= htmlspecialchars($nextCategory['name'], ENT_QUOTES, 'UTF-8') ?>"
                        <?= $heroCount < 2 ? 'hidden disabled' : '' ?>><?= htmlspecialchars($nextCategory['name'], ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            <?php else: ?>
                <div class="hero-category-stack" aria-label="Catégories de recettes">
                    <span class="hero-category-current">Catégories bientôt disponibles</span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($firstCategory !== null): ?>
            <div class="hero-pagination" role="group" aria-label="Choisir une catégorie">
                <?php foreach ($heroCategories as $index => $category): ?>
                    <button type="button" data-category-index="<?= $index ?>"
                        aria-label="Afficher la catégorie <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>"
                        <?= $index === 0 ? 'class="is-active" aria-current="true"' : '' ?>></button>
                <?php endforeach; ?>
            </div>

            <button class="hero-rotation-toggle" type="button" hidden
                aria-label="Mettre en pause la rotation automatique" aria-pressed="false">Pause</button>

            <button class="hero-category-backdrop" type="button"
                aria-label="Fermer la présentation de la catégorie"></button>

            <aside id="heroCategoryPanel" class="hero-category-panel" aria-hidden="true" inert>
                <button class="hero-category-close" type="button" aria-label="Fermer la présentation de la catégorie">
                    <span aria-hidden="true">×</span>
                </button>

                <div class="hero-category-panel-content">
                    <h2 class="hero-category-panel-title"><?= htmlspecialchars($firstCategory['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="hero-category-panel-copy"><?= htmlspecialchars($firstCategory['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="hero-category-panel-link" href="<?= htmlspecialchars($firstCategory['href'], ENT_QUOTES, 'UTF-8') ?>">
                        Découvrir les recettes <span aria-hidden="true">⟶</span>
                    </a>
                    <img class="hero-category-panel-image"
                        src="<?= htmlspecialchars($firstCategory['image'], ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($firstCategory['imageAlt'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </aside>
        <?php endif; ?>

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
                    <?php foreach ($topRecipes as $index => $recipe): ?>
                        <?php $roundedRating = (int) round($recipe->getAverageRating() ?? 0); ?>
                        <article class="star-card">
                            <p class="star-card-rank" aria-label="Rang <?= $index + 1 ?>">
                                N° <?= $index + 1 ?>
                            </p>
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
                                    class="star-card-image"
                                    src="<?= htmlspecialchars($recipeImagePath($recipe), ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars((string) $recipe->getTitle(), ENT_QUOTES, 'UTF-8') ?>"
                                    loading="lazy">
                                <h3><?= htmlspecialchars((string) $recipe->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3>
                            </a>
                            <div class="star-card-details">
                                <span><?= $recipe->getFormattedAverage() !== '' ? htmlspecialchars($recipe->getFormattedAverage(), ENT_QUOTES, 'UTF-8') . '/5' : 'Pas encore notée' ?></span>
                                <span><?= (int) $recipe->getRatingCount() ?> avis</span>
                                <span><?= $recipe->getTotalTime() ?> min</span>
                                <span><?= htmlspecialchars($recipe->getDifficultyLabel(), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <a class="star-card-button" href="?pg=recette&amp;slug=<?= htmlspecialchars((string) $recipe->getSlug(), ENT_QUOTES, 'UTF-8') ?>">
                                Voir la recette
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require RACINE_PATH . '/view/inc/footer.php'; ?>
