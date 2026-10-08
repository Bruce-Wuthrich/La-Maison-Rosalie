<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>
<?php
$escape = static fn (?string $value): string => htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
$primaryCategory = $categories[0] ?? null;
$averageRating = $recipe->getAverageRating();
$filledStars = $averageRating === null ? 0 : (int) round($averageRating);
?>

<main class="recipe-page">
    <section class="recipe-summary">
        <div class="container">
            <nav class="recipe-breadcrumb" aria-label="Fil d’Ariane">
                <a href="?pg=accueil">Accueil</a>
                <span aria-hidden="true">›</span>

                <a href="?pg=recettes">Recettes</a>

                <?php if ($primaryCategory !== null) { ?>
                    <span aria-hidden="true">›</span>
                    <a href="?pg=recettes&amp;categorie=<?php echo rawurlencode((string) $primaryCategory->getSlug()); ?>">
                        <?php echo $escape($primaryCategory->getTitle()); ?>
                    </a>
                <?php } ?>

                <span aria-hidden="true">›</span>
                <span aria-current="page"><?php echo $escape($recipe->getTitle()); ?></span>
            </nav>

            <div class="row align-items-center g-5">
                <div class="col-12 col-lg-5">
                    <figure class="recipe-figure">
                        <button class="recipe-favorite" type="button" aria-label="Ajouter aux favoris">
                            <img src="assets/img/recettes/icons/favorite-outline.svg" alt="" aria-hidden="true">
                        </button>

                        <img class="recipe-image"
                            src="<?php echo $escape($recipe->getMainImage()); ?>"
                            alt="<?php echo $escape($recipe->getTitle()); ?>"
                            width="1200" height="800">
                    </figure>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="recipe-introduction">
                        <?php if (!empty($categories)) { ?>
                            <p class="recipe-category">
                                <?php
                                echo $escape(implode(', ', array_map(
                                    static fn ($category): string => (string) $category->getTitle(),
                                    $categories
                                )));
                                ?>
                            </p>
                        <?php } ?>

                        <h1><?php echo $escape($recipe->getTitle()); ?></h1>

                        <p class="recipe-description"><?php echo $escape($recipe->getDescription()); ?></p>

                        <div class="recipe-rating"
                            aria-label="<?php echo $averageRating === null
                                ? 'Cette recette n’a pas encore de note'
                                : 'Note moyenne : ' . $escape($recipe->getFormattedAverage()) . ' sur 5'; ?>">
                            <div class="recipe-rating-stars" aria-hidden="true">
                                <?php for ($star = 1; $star <= 5; $star++) { ?>
                                    <img src="assets/img/recettes/icons/<?php echo $star <= $filledStars ? 'star-filled' : 'star-outline'; ?>.svg" alt="">
                                <?php } ?>
                            </div>

                            <a href="#comments">
                                <?php echo $commentCount; ?> commentaire<?php echo $commentCount === 1 ? '' : 's'; ?>
                            </a>
                        </div>

                        <ul class="recipe-meta">
                            <li>
                                <img src="assets/img/recettes/icons/servings.svg" alt="" aria-hidden="true">
                                <span><?php echo (int) $recipe->getServings(); ?> pers.</span>
                            </li>

                            <li>
                                <img src="assets/img/recettes/icons/difficulty.svg" alt="" aria-hidden="true">
                                <span><?php echo $escape($recipe->getDifficultyLabel()); ?></span>
                            </li>

                            <li>
                                <img src="assets/img/recettes/icons/time.svg" alt="" aria-hidden="true">
                                <span><?php echo $recipe->getTotalTime(); ?> min</span>
                            </li>
                        </ul>

                        <div class="recipe-actions">
                            <button class="recipe-action" type="button" id="printRecipe">
                                <img src="assets/img/recettes/icons/print.svg" alt="" aria-hidden="true">
                                <span>Imprimer</span>
                            </button>

                            <button class="recipe-action" type="button" id="shareRecipe">
                                <img src="assets/img/recettes/icons/share.svg" alt="" aria-hidden="true">
                                <span>Partager</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="recipe-content">
        <div class="container">
            <div class="row g-5">
                <div class="col-12 col-lg-5">
                    <h2>Ingrédients</h2>

                    <ul class="ingredient-list">
                        <?php foreach ($ingredients as $ingredient) { ?>
                            <?php
                            $ingredientParts = array_filter([
                                $ingredient->getFormattedQuantity(),
                                $ingredient->getUnit() ?? '',
                                $ingredient->getIngredientName() ?? '',
                            ], static fn (string $part): bool => $part !== '');
                            $ingredientLabel = implode(' ', $ingredientParts);
                            if ($ingredient->getDetails() !== null && $ingredient->getDetails() !== '') {
                                $ingredientLabel .= ' (' . $ingredient->getDetails() . ')';
                            }
                            ?>
                            <li><?php echo $escape($ingredientLabel); ?></li>
                        <?php } ?>
                    </ul>
                </div>

                <div class="col-12 col-lg-7">
                    <h2>Préparation</h2>

                    <ol class="recipe-steps">
                        <?php foreach ($steps as $step) { ?>
                            <li>
                                <h3>
                                    Étape <?php echo (int) $step->getStepNumber(); ?> —
                                    <?php echo $escape($step->getTitle()); ?>
                                </h3>
                                <p><?php echo $escape($step->getInstructions()); ?></p>
                            </li>
                        <?php } ?>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="recipe-comments" id="comments">
        <div class="container">
            <div class="comments-heading">
                <img src="assets/img/recettes/icons/comment.svg" alt="" aria-hidden="true">
                <h2>Commentaires (<?php echo $commentCount; ?>)</h2>
            </div>

            <?php if (empty($comments)) { ?>
                <p>Aucun commentaire pour cette recette.</p>
            <?php } else { ?>
                <div class="comments-list-panel">
                    <?php foreach ($comments as $comment) { ?>
                        <article class="recipe-comment">
                            <h3><?php echo $escape($comment->getSubject() ?: 'Commentaire de ' . $comment->getAuthorUsername()); ?></h3>
                            <p><?php echo $escape($comment->getMessage()); ?></p>
                            <small>Par <?php echo $escape($comment->getAuthorUsername()); ?></small>
                        </article>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($commentPage > 1 || $commentHasNext) { ?>
                <nav class="comment-pagination" aria-label="Pagination des commentaires">
                    <?php if ($commentPage > 1) { ?>
                        <a href="?pg=recette&amp;slug=<?php echo rawurlencode((string) $recipe->getSlug()); ?>&amp;comment_page=<?php echo $commentPage - 1; ?>#comments">
                            Commentaires précédents
                        </a>
                    <?php } ?>

                    <span>Page <?php echo $commentPage; ?></span>

                    <?php if ($commentHasNext) { ?>
                        <a href="?pg=recette&amp;slug=<?php echo rawurlencode((string) $recipe->getSlug()); ?>&amp;comment_page=<?php echo $commentPage + 1; ?>#comments">
                            Commentaires suivants
                        </a>
                    <?php } ?>
                </nav>
            <?php } ?>
        </div>
    </section>
</main>

<?php require RACINE_PATH . '/view/inc/footer.php'; ?>
