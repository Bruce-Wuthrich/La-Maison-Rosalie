<?php
// path: view/inc/recipeCard.php
// Carte de recette commune à la page Recettes et à l'onglet favoris de Mon compte.
// Attend $recipe (RecipeMapping), $recipeImagePath (closure de la vue) et $iconVersion (header.php).
?>
<?php
$searchableRecipe = trim(
    (string) $recipe->getTitle() . ' ' . (string) $recipe->getIngredientNames()
);
?>
<article class="recipe-card"
    data-search="<?php echo htmlspecialchars($searchableRecipe, ENT_QUOTES, 'UTF-8'); ?>">
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
                <img src="assets/img/recettes/icons/<?php echo $recipe->getAverageRating() === null ? 'star-outline' : 'star-filled'; ?>.svg?v=<?= (int) $iconVersion ?>" width="18" height="18" alt="" aria-hidden="true">
                <?php
                echo $recipe->getAverageRating() === null
                    ? 'Pas encore notée'
                    : htmlspecialchars($recipe->getFormattedAverage(), ENT_QUOTES, 'UTF-8') . '/5';
                ?>
            </span>
            <span class="d-inline-flex align-items-center gap-1">
                <img src="assets/img/recettes/icons/time.svg?v=<?= (int) $iconVersion ?>" width="18" height="18" alt="" aria-hidden="true">
                <?php echo $recipe->getTotalTime(); ?> min
            </span>
            <span class="d-inline-flex align-items-center gap-1">
                <?php $difficultyIcon = match ($recipe->getDifficulty()) {
                    'easy' => 'difficulty-easy',
                    'medium' => 'difficulty-medium',
                    'hard' => 'difficulty-hard',
                    default => 'difficulty',
                }; ?>
                <img src="assets/img/recettes/icons/<?php echo $difficultyIcon; ?>.svg?v=<?= (int) $iconVersion ?>" width="18" height="18" alt="" aria-hidden="true">
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