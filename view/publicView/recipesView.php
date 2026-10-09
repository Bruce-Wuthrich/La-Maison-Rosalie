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

            <nav class="recipe-filters" aria-label="Filtrer les recettes">
                <?php foreach ($recipeFilters as $filterSlug => $filterLabel) { ?>
                    <?php $isActive = $categorySlug === $filterSlug; ?>
                    <a class="recipe-filter<?php echo $isActive ? ' is-active' : ''; ?>"
                        href="?pg=recettes<?php echo $filterSlug === '' ? '' : '&amp;categorie=' . rawurlencode($filterSlug); ?><?php echo $searchTerm === '' ? '' : '&amp;recherche=' . rawurlencode($searchTerm); ?>"
                        <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                        <?php echo htmlspecialchars($filterLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php } ?>
            </nav>

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
                                    src="<?php echo htmlspecialchars((string) $recipe->getMainImage(), ENT_QUOTES, 'UTF-8'); ?>"
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

                                <div class="recipe-card-footer">
                                    <span>
                                        <?php
                                        echo $recipe->getAverageRating() === null
                                            ? 'Pas encore notée'
                                            : htmlspecialchars($recipe->getFormattedAverage(), ENT_QUOTES, 'UTF-8') . '/5';
                                        ?>
                                    </span>
                                    <span><?php echo $recipe->getTotalTime(); ?> min</span>
                                </div>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </section>

</main>

<?php require RACINE_PATH . '/view/inc/footer.php'; ?>
