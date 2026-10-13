<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>
<?php
$recipeFilters = [
    '' => 'Toutes',
    'gateaux' => 'Gâteaux',
    'mousses' => 'Mousses',
    'boissons' => 'Boissons',
    'glace' => 'Glacé',
];

// Utilise le chemin de la BDD s'il existe, sinon l'image locale de la recette.
$recipeImagePath = static function ($recipe): string {
    $image = (string) $recipe->getMainImage();

    if (!is_file(RACINE_PATH . '/public/' . $image)) {
        $image = 'assets/img/recettes/' . $recipe->getSlug() . '/principale.webp';
    }

    return $image;
};
?>

<main class="recipes-page">

    <section class="recipes-header">
        <div class="container">
            <p class="recipes-eyebrow">Maison Rosalie</p>

            <h1>Nos recettes</h1>

            <p class="recipes-introduction">
                Découvrez nos créations chocolatées et trouvez la recette qui vous fera plaisir.
            </p>

            <?php if ($searchTerm !== '') { ?>
                <p class="recipes-search-summary">
                    <?php echo count($recipes); ?> résultat<?php echo count($recipes) > 1 ? 's' : ''; ?> pour
                    « <?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?> »
                </p>
            <?php } ?>
        </div>
    </section>

    <section class="recipes-catalog">
        <div class="container">
        <?php
            // filtre qui garde les autres critères actifs (catégorie, recherche, difficulté, temps).
            $filterUrl = static function (array $changes) use ($categorySlug, $searchTerm, $difficulty, $maxTotalTime): string {
                $params = array_merge([
                    'pg' => 'recettes',
                    'categorie' => $categorySlug,
                    'recherche' => $searchTerm,
                    'difficulte' => $difficulty,
                    'temps' => $maxTotalTime,
                ], $changes);
                $params = array_filter($params, static fn($value): bool => $value !== '' && $value !== null);

                return '?' . htmlspecialchars(http_build_query($params), ENT_QUOTES, 'UTF-8');
            };
            ?>

            <nav class="recipe-filters" aria-label="Filtrer les recettes par catégorie">
                <?php foreach ($recipeFilters as $filterSlug => $filterLabel) { ?>
                    <?php $isActive = $categorySlug === $filterSlug; ?>
                    <a class="recipe-filter<?php echo $isActive ? ' is-active' : ''; ?>"
                        href="<?php echo $filterUrl(['categorie' => $filterSlug]); ?>"
                        <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                        <?php echo htmlspecialchars($filterLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php } ?>
            </nav>

            <form class="recipe-extra-filters" action="" method="get" aria-label="Filtrer par difficulté et par temps">
                <input type="hidden" name="pg" value="recettes">
                <?php if ($categorySlug !== '') { ?>
                    <input type="hidden" name="categorie" value="<?php echo htmlspecialchars($categorySlug, ENT_QUOTES, 'UTF-8'); ?>">
                <?php } ?>
                <?php if ($searchTerm !== '') { ?>
                    <input type="hidden" name="recherche" value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
                <?php } ?>

                <label>
                    <span>Difficulté</span>
                    <select name="difficulte" data-auto-submit>
                        <option value="">Toutes</option>
                        <?php foreach ($difficultyOptions as $value => $label) { ?>
                            <option value="<?php echo $value; ?>"<?php echo $difficulty === $value ? ' selected' : ''; ?>><?php echo $label; ?></option>
                        <?php } ?>
                    </select>
                </label>

                <label>
                    <span>Temps total</span>
                    <select name="temps" data-auto-submit>
                        <option value="">Peu importe</option>
                        <?php foreach ($timeOptions as $value => $label) { ?>
                            <option value="<?php echo $value; ?>"<?php echo $maxTotalTime === $value ? ' selected' : ''; ?>><?php echo $label; ?></option>
                        <?php } ?>
                    </select>
                </label>

                <button class="recipe-filter" type="submit">Filtrer</button>
            </form>

            <?php if ($error !== null) { ?>
                <p role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } elseif (empty($recipes)) { ?>
                <p>
                    <?php echo $searchTerm === ''
                        ? 'Aucune recette n’est disponible pour le moment.'
                        : 'Aucune recette ne correspond à votre recherche.'; ?>
                </p>
            <?php } else { ?>
                <div class="recipes-grid">
                    <?php foreach ($recipes as $recipe) { ?>
                        <article class="recipe-card">
                            <a class="recipe-card-image-link"
                                href="?pg=recette&amp;slug=<?php echo rawurlencode((string) $recipe->getSlug()); ?>">
                                <img class="recipe-card-image"
                                    src="<?php echo htmlspecialchars($recipeImagePath($recipe), ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars((string) $recipe->getTitle(), ENT_QUOTES, 'UTF-8'); ?>"
                                    width="1200" height="800" loading="lazy">
                            </a>

                            <div class="recipe-card-content">
                                <p class="recipe-card-category">
                                    <?php echo htmlspecialchars($recipe->getCategoryTitles() ?? 'Recette', ENT_QUOTES, 'UTF-8'); ?>
                                </p>

                                <h2>
                                    <a href="?pg=recette&amp;slug=<?php echo rawurlencode((string) $recipe->getSlug()); ?>">
                                        <?php echo htmlspecialchars((string) $recipe->getTitle(), ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </h2>

                                <p class="recipe-card-description">
                                    <?php echo htmlspecialchars((string) $recipe->getDescription(), ENT_QUOTES, 'UTF-8'); ?>
                                </p>

                                <div class="recipe-card-footer flex-wrap">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <img src="assets/img/recettes/icons/<?php echo $recipe->getAverageRating() === null ? 'star-outline' : 'star-filled'; ?>.svg" width="18" height="18" alt="" aria-hidden="true">
                                        <?php
                                        echo $recipe->getAverageRating() === null
                                            ? 'Pas encore notée'
                                            : htmlspecialchars($recipe->getFormattedAverage(), ENT_QUOTES, 'UTF-8') . '/5';
                                        ?>
                                    </span>
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <img src="assets/img/recettes/icons/time.svg" width="18" height="18" alt="" aria-hidden="true">
                                        <?php echo $recipe->getTotalTime(); ?> min
                                    </span>
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <?php
                                        $difficultyLevel = match ($recipe->getDifficulty()) {
                                            'easy' => 1,
                                            'medium' => 2,
                                            'hard' => 3,
                                            default => 0,
                                        };
                                        ?>
                                        <svg width="18" height="18" viewBox="0 0 512 512" aria-hidden="true" focusable="false">
                                            <g stroke="#F1B300" stroke-width="32" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M32,32V464a16,16,0,0,0,16,16H480" fill="none" />
                                                <rect x="96" y="224" width="80" height="192" rx="20"
                                                    fill="<?php echo $difficultyLevel >= 1 ? '#F1B300' : 'none'; ?>" opacity="<?php echo $difficultyLevel >= 1 ? '1' : '0.25'; ?>" />
                                                <rect x="240" y="176" width="80" height="240" rx="20"
                                                    fill="<?php echo $difficultyLevel >= 2 ? '#F1B300' : 'none'; ?>" opacity="<?php echo $difficultyLevel >= 2 ? '1' : '0.25'; ?>" />
                                                <rect x="383.64" y="112" width="80" height="304" rx="20"
                                                    fill="<?php echo $difficultyLevel >= 3 ? '#F1B300' : 'none'; ?>" opacity="<?php echo $difficultyLevel >= 3 ? '1' : '0.25'; ?>" />
                                            </g>
                                        </svg>
                                        <?php echo htmlspecialchars($recipe->getDifficultyLabel(), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <span>
                                        <?php $ratingCount = (int) $recipe->getRatingCount(); ?>
                                        <?php echo $ratingCount; ?> avis
                                    </span>
                                </div>
                                <a class="recipe-filter d-inline-block mt-3"
                                    href="?pg=recette&amp;slug=<?php echo rawurlencode((string) $recipe->getSlug()); ?>">
                                    Voir la recette
                                </a>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </section>
</main>
<?php require RACINE_PATH . '/view/inc/footer.php'; ?>
