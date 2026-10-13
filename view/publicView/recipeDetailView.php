<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>
<?php
$escape = static fn (?string $value): string => htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
$primaryCategory = $categories[0] ?? null;
$averageRating = $recipe->getAverageRating();
$filledStars = $averageRating === null ? 0 : (int) round($averageRating);
$recipeImage = (string) $recipe->getMainImage();

// Garde l'image de la BDD et utilise l'asset local si son ancien chemin n'existe plus.
if (!is_file(RACINE_PATH . '/public/' . $recipeImage)) {
    $recipeImage = 'assets/img/recettes/' . $recipe->getSlug() . '/principale.webp';
}
$commentSortLabels = [
    'date' => 'Date',
    'mieux' => 'Les mieux notés',
    'pire' => 'Les moins bien notés',
];

// Tags visuels de la maquette : aucune nouvelle table ni requête SQL.
$recipeTags = [
    'chocolat-chaud-a-l-ancienne' => [
        ['name' => 'chocolat', 'count' => 5],
        ['name' => 'rapide', 'count' => 3],
        ['name' => 'famille', 'count' => 4],
        ['name' => 'chocolat noir', 'count' => 4],
        ['name' => 'populaire', 'count' => 4],
        ['name' => 'enfants', 'count' => 4],
    ],
    'mousse-au-chocolat-noir' => [
        ['name' => 'chocolat', 'count' => 5],
        ['name' => 'rapide', 'count' => 3],
        ['name' => 'famille', 'count' => 4],
        ['name' => 'chocolat noir', 'count' => 4],
        ['name' => 'enfants', 'count' => 4],
        ['name' => 'crémeux', 'count' => 2],
    ],
    'brownies-aux-noix' => [
        ['name' => 'chocolat', 'count' => 5],
        ['name' => 'famille', 'count' => 4],
        ['name' => 'four', 'count' => 2],
        ['name' => 'chocolat noir', 'count' => 4],
        ['name' => 'populaire', 'count' => 4],
        ['name' => 'brownies', 'count' => 1],
        ['name' => 'fête', 'count' => 2],
        ['name' => 'enfants', 'count' => 4],
    ],
    'fondant-au-coeur-coulant' => [
        ['name' => 'chocolat', 'count' => 5],
        ['name' => 'four', 'count' => 2],
        ['name' => 'chocolat noir', 'count' => 4],
        ['name' => 'populaire', 'count' => 4],
        ['name' => 'fête', 'count' => 2],
    ],
    'glace-au-chocolat-sans-sorbetiere' => [
        ['name' => 'chocolat', 'count' => 5],
        ['name' => 'rapide', 'count' => 3],
        ['name' => 'famille', 'count' => 4],
        ['name' => 'populaire', 'count' => 4],
        ['name' => 'enfants', 'count' => 4],
        ['name' => 'sans cuisson', 'count' => 1],
        ['name' => 'crémeux', 'count' => 2],
    ],
];
$visibleTags = $recipeTags[(string) $recipe->getSlug()] ?? [];
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
                        <button class="recipe-favorite" type="button"
                            data-recipe-slug="<?php echo $escape($recipe->getSlug()); ?>"
                            aria-label="Ajouter aux favoris" aria-pressed="false">
                            <svg viewBox="0 0 512 512" aria-hidden="true">
                                <path d="M352.92 80C288 80 256 144 256 144s-32-64-96.92-64C106.32 80 64.54 124.14 64 176.81c-1.1 109.33 86.73 187.08 183 252.42a16 16 0 0 0 18 0c96.26-65.34 184.09-143.09 183-252.42C447.46 124.14 405.68 80 352.92 80Z" />
                            </svg>
                            <span class="recipe-favorite-tooltip" aria-hidden="true">Enregistrez</span>
                        </button>

                        <img class="recipe-image"
                            src="<?php echo $escape($recipeImage); ?>"
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
                                    <img
                                        src="assets/img/recettes/icons/<?php echo $star <= $filledStars ? 'star-filled' : 'star-outline'; ?>.svg"
                                        alt=""
                                    >
                                <?php } ?>
                            </div>

                            <p class="recipe-rating-stats" aria-live="polite">
                                <?php if ($averageRating === null) { ?>
                                    Aucune note pour le moment
                                <?php } else { ?>
                                    <?php echo $escape($recipe->getFormattedAverage()); ?>/5 —
                                    <?php echo (int) $recipe->getRatingCount(); ?> avis
                                <?php } ?>
                            </p>

                            <a href="#comments" data-comment-count>
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
                                <span>Préparation <?php echo (int) $recipe->getPrepTimeMinutes(); ?> min</span>
                            </li>

                            <li>
                                <img src="assets/img/recettes/icons/time.svg" alt="" aria-hidden="true">
                                <span><?php echo (int) $recipe->getCookTimeMinutes() > 0
                                    ? 'Cuisson ' . (int) $recipe->getCookTimeMinutes() . ' min'
                                    : 'Sans cuisson'; ?></span>
                            </li>

                            <li>
                                <img src="assets/img/recettes/icons/time.svg" alt="" aria-hidden="true">
                                <span>Total <?php echo $recipe->getTotalTime(); ?> min</span>
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

                            <p class="recipe-action-status" data-recipe-action-status aria-live="polite"></p>
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
                    <h2>Method</h2>

                    <ol class="recipe-steps">
                        <?php foreach ($steps as $step) { ?>
                            <li>
                                <h3>
                                    Étape <?php echo (int) $step->getStepNumber(); ?> —
                                    <?php echo $escape($step->getTitle()); ?>
                                </h3>
                                <?php if ($step->getImage() !== null && $step->getImage() !== '') { ?>
                                    <img class="recipe-step-image"
                                        src="<?php echo $escape($step->getImage()); ?>"
                                        alt="Étape <?php echo (int) $step->getStepNumber(); ?> : <?php echo $escape($step->getTitle()); ?>"
                                        width="1200" height="800" loading="lazy">
                                <?php } ?>                            </li>
                        <?php } ?>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="recipe-comments" id="comments" data-comments-section
        data-slug="<?php echo $escape($recipe->getSlug()); ?>"
        data-comment-page="<?php echo (int) $commentPage; ?>"
        data-comment-count="<?php echo (int) $commentCount; ?>"
        data-comment-sort="<?php echo $escape($commentSort); ?>"
        data-user-id="<?php echo (int) ($connectedUser?->getId() ?? 0); ?>"
        data-author-id="<?php echo (int) $recipe->getAuthorId(); ?>">
        <div class="container">
            <div class="comments-heading">
                <img src="assets/img/recettes/icons/comment.svg" alt="" aria-hidden="true">
                <h2>Commentaires</h2>
            </div>

            <?php if (is_array($flashMessage) && isset($flashMessage['message'])) { ?>
                <p class="comment-flash comment-flash-<?php echo ($flashMessage['type'] ?? '') === 'success' ? 'success' : 'error'; ?>" role="status">
                    <?php echo $escape((string) $flashMessage['message']); ?>
                </p>
            <?php } ?>

            <p class="comment-action-status" data-comment-status role="status" aria-live="polite"></p>

            <div class="comments-layout">
                <aside class="comment-form-panel" aria-labelledby="commentFormTitle">
                    <h3 id="commentFormTitle">Partagez votre expérience</h3>

                        <?php
                        $selectedRating = (int) ($commentFormData['rating'] ?? $recipe->getUserRating() ?? 0);
                        $oldMessage = (string) ($commentFormData['message'] ?? '');
                        ?>
                        <form class="comment-form" action="?pg=commentaire&amp;action=add" method="post" data-auth-member<?php echo $connectedUser === null ? ' hidden' : ''; ?>>
                            <input type="hidden" name="csrf_token" value="<?php echo $escape($csrfToken); ?>">
                            <input type="hidden" name="recipe_id" value="<?php echo (int) $recipe->getId(); ?>">
                            <input type="hidden" name="slug" value="<?php echo $escape($recipe->getSlug()); ?>">

                            <div class="comment-identity">
                                <label>
                                    <span class="visually-hidden">Votre nom</span>
                                    <input type="text" data-comment-username value="<?php echo $escape($connectedUser?->getUsername()); ?>" readonly>
                                </label>
                                <label>
                                    <span class="visually-hidden">Votre e-mail</span>
                                    <input type="email" data-comment-email value="<?php echo $escape($connectedUser?->getEmail()); ?>" readonly>
                                </label>
                            </div>

                            <fieldset class="comment-rating-fieldset">
                                <legend>Votre note (facultative)</legend>
                                <div class="comment-rating-input" aria-label="Votre note sur 5">
                                    <?php for ($rating = 5; $rating >= 1; $rating--) { ?>
                                        <input id="commentRating<?php echo $rating; ?>" type="radio" name="rating"
                                            value="<?php echo $rating; ?>" <?php echo $selectedRating === $rating ? 'checked' : ''; ?>>
                                        <label for="commentRating<?php echo $rating; ?>" title="<?php echo $rating; ?> étoile<?php echo $rating > 1 ? 's' : ''; ?>">
                                            <span aria-hidden="true">★</span>
                                            <span class="visually-hidden"><?php echo $rating; ?> étoile<?php echo $rating > 1 ? 's' : ''; ?></span>
                                        </label>
                                    <?php } ?>
                                </div>
                            </fieldset>

                            <label class="comment-message-label" for="commentSubject">Sujet (facultatif)</label>
                            <input class="comment-subject-input" id="commentSubject" name="subject" type="text" maxlength="120"
                                value="<?php echo $escape($commentFormData['subject'] ?? ''); ?>">

                            <label class="comment-message-label" for="commentMessage">Votre commentaire</label>
                            <textarea id="commentMessage" name="message" minlength="3" maxlength="500" required
                                placeholder="Dites-nous ce que vous pensez de cette recette."><?php echo $escape($oldMessage); ?></textarea>
                            <small class="comment-character-count" data-comment-counter>0/500 caractères</small>

                            <button class="comment-submit" type="submit">Publier le commentaire</button>
                            <p class="comment-moderation-note">Votre avis est publié après validation des champs. Un avis existant est mis à jour.</p>
                        </form>
                        <div class="comment-login-panel" data-auth-guest<?php echo $connectedUser !== null ? ' hidden' : ''; ?>>
                            <div class="comment-identity" aria-hidden="true">
                                <input type="text" placeholder="Votre nom" disabled>
                                <input type="email" placeholder="Votre e-mail" disabled>
                            </div>
                            <p>Connectez-vous pour noter cette recette et partager votre commentaire.</p>
                            <button class="comment-submit" type="button" data-auth-open>Se connecter pour commenter</button>
                        </div>
                </aside>

                <div class="comments-feed">
                    <header class="comments-summary">
                        <div class="comments-average-stars" aria-hidden="true">
                            <?php for ($star = 1; $star <= 5; $star++) { ?>
                                <img src="assets/img/recettes/icons/<?php echo $star <= $filledStars ? 'star-filled' : 'star-outline'; ?>.svg" alt="">
                            <?php } ?>
                        </div>
                        <p data-comment-count><?php echo $commentCount; ?> commentaire<?php echo $commentCount === 1 ? '' : 's'; ?></p>
                        <details class="comment-sort">
                            <summary aria-label="Trier les commentaires" title="Trier les commentaires">
                                <span></span><span></span><span></span>
                            </summary>
                            <div class="comment-sort-menu">
                                <p>Trier par</p>
                                <?php foreach ($commentSortLabels as $sortValue => $sortLabel) { ?>
                                    <a href="?pg=recette&amp;slug=<?php echo rawurlencode((string) $recipe->getSlug()); ?>&amp;comment_sort=<?php echo $sortValue; ?>#comments"
                                        <?php echo $commentSort === $sortValue ? 'aria-current="true"' : ''; ?>>
                                        <?php echo $sortLabel; ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </details>
                    </header>

                        <p class="comments-empty"<?php echo !empty($comments) ? ' hidden' : ''; ?>>Soyez la première personne à partager son expérience.</p>
                        <div class="comments-list-panel<?php echo $commentPage > 1 ? ' is-expanded' : ''; ?>">
                            <?php foreach ($comments as $index => $comment) { ?>
                                <?php
                                $commentRating = $comment->getAuthorRating() ?? 0;
                                $commentDate = strtotime((string) $comment->getCreatedAt());
                                $isOwnComment = $connectedUser !== null && $comment->getAuthorId() === $connectedUser->getId();
                                ?>
                                <article class="recipe-comment<?php echo $index % 2 === 1 ? ' recipe-comment-reversed' : ''; ?>" data-comment-id="<?php echo (int) $comment->getId(); ?>">
                                    <div class="comment-avatar" aria-hidden="true"><span></span></div>

                                    <div class="comment-body">
                                        <div class="comment-author-line">
                                            <h3><?php echo $escape($comment->getAuthorUsername()); ?></h3>
                                       <?php if ($commentRating > 0) { // pas d'étoiles si l'auteur n'a pas noté ?>
                                                <div class="comment-stars" aria-label="Note : <?php echo $commentRating; ?> sur 5">
                                                    <?php for ($star = 1; $star <= 5; $star++) { ?>
                                                        <img src="assets/img/recettes/icons/<?php echo $star <= $commentRating ? 'star-filled' : 'star-outline'; ?>.svg" alt="">
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                            <time datetime="<?php echo $escape($comment->getCreatedAt()); ?>">
                                                <?php echo $commentDate === false ? '' : date('d/m/Y H:i', $commentDate); ?>
                                            </time>
                                        </div>

                                        <?php if ($comment->getSubject() !== null) { ?>
                                            <h4 class="comment-subject"><?php echo $escape($comment->getSubject()); ?></h4>
                                        <?php } ?>
                                        <p class="comment-message"><?php echo $escape($comment->getMessage()); ?></p>

                                        <div class="comment-footer-row">
                                            <?php if ($isOwnComment || ($connectedUser !== null && $connectedUser->isAdmin())) { ?>
                                                <form action="?pg=commentaire&amp;action=delete" method="post">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $escape($csrfToken); ?>">
                                                    <input type="hidden" name="comment_id" value="<?php echo (int) $comment->getId(); ?>">
                                                    <input type="hidden" name="slug" value="<?php echo $escape($recipe->getSlug()); ?>">
                                                    <button class="comment-delete" type="submit">Supprimer</button>
                                                </form>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </article>
                            <?php } ?>
                        </div>

                        <nav class="comment-pagination" aria-label="Pagination des commentaires"<?php echo !$commentHasNext ? ' hidden' : ''; ?>>
                            <a class="comment-more" href="?pg=recette&amp;slug=<?php echo rawurlencode((string) $recipe->getSlug()); ?>&amp;comment_sort=<?php echo $commentSort; ?>&amp;comment_page=<?php echo $commentPage + 1; ?>#comments">
                                Voir plus
                            </a>
                        </nav>
                </div>
            </div>
        </div>
    </section>

    <?php if ($visibleTags !== []) { ?>
        <section class="recipe-tags" aria-labelledby="recipeTagsTitle">
            <h2 id="recipeTagsTitle">Tags</h2>
            <div class="recipe-tags-band">
                <div class="recipe-tags-list" role="list">
                    <?php foreach ($visibleTags as $tag) { ?>
                        <a class="recipe-tag" role="listitem"
                            href="?pg=recettes&amp;recherche=<?php echo rawurlencode((string) $tag['name']); ?>">
                            <?php echo $escape($tag['name']); ?>(<?php echo (int) $tag['count']; ?>)
                        </a>
                    <?php } ?>
                </div>
            </div>
        </section>
    <?php } ?>
</main>

<?php require RACINE_PATH . '/view/inc/footer.php'; ?>
