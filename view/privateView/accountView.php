<?php
// path: view/privateView/accountView.php
// Page Mon compte en 3 onglets : recettes favorites, notes données et avis écrits.
// Réutilise l'en-tête et les filtres de la page Recettes, les cartes de recette et les cartes d'avis (comme la page admin).

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatDate = static function (?string $date): string {
    $timestamp = strtotime((string) $date);
    return $timestamp === false ? '' : date('d/m/Y H:i', $timestamp);
};

// Utilise le chemin de la BDD s'il existe, sinon l'image locale de la recette (même règle que la page Recettes).
$recipeImagePath = static function ($recipe): string {
    $image = (string) $recipe->getMainImage();

    if (!is_file(RACINE_PATH . '/public/' . $image)) {
        $image = 'assets/img/recettes/' . $recipe->getSlug() . '/principale.webp';
    }

    return $image;
};

// Libellé de chaque onglet avec son compteur
$tabCounters = [
    'favoris' => count($favoriteRecipes),
    'notes' => count($userRatings),
    'avis' => count($userComments),
];
?>
<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>

<main class="recipes-page admin-page">

    <section class="recipes-header">
        <div class="container">
            <p class="recipes-eyebrow">Mon compte</p>

            <h1>Bonjour <?= $escape($connectedUser['username']) ?></h1>

            <p class="recipes-introduction">
                Retrouvez vos recettes favorites, les notes que vous avez données et les avis que vous avez écrits.
            </p>
        </div>
    </section>

    <section class="recipes-catalog">
        <div class="container">

            <nav class="recipe-filters" aria-label="Sections de mon compte">
                <?php foreach ($accountTabs as $tabKey => $tabLabel): ?>
                    <a class="recipe-filter<?= $activeTab === $tabKey ? ' is-active' : '' ?>"
                        href="?pg=mon-compte&amp;onglet=<?= $tabKey ?>"
                        <?= $activeTab === $tabKey ? 'aria-current="page"' : '' ?>>
                        <?= $escape($tabLabel) ?> (<?= $escape($tabCounters[$tabKey]) ?>)
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ($accountError !== null): ?>
                <p role="alert"><?= $escape($accountError) ?></p>
            <?php endif; ?>

            <?php if ($activeTab === 'favoris'): ?>
            <section class="admin-section" aria-labelledby="accountFavoritesTitle">
                <h2 id="accountFavoritesTitle">Mes recettes favorites</h2>

                <?php if (empty($favoriteRecipes)): ?>
                    <p>Vous n’avez pas encore de recette favorite : touchez le cœur d’une fiche recette pour l’enregistrer.</p>
                    <a class="recipe-filter d-inline-block mt-2" href="?pg=recettes">Voir les recettes</a>
                <?php else: ?>
                    <div class="recipes-grid">
                        <?php foreach ($favoriteRecipes as $recipe): ?>
                            <?php require RACINE_PATH . '/view/inc/recipeCard.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if ($activeTab === 'notes'): ?>
            <section class="admin-section" aria-labelledby="accountRatingsTitle">
                <h2 id="accountRatingsTitle">Mes notes</h2>

                <?php if (empty($userRatings)): ?>
                    <p>Vous n’avez encore noté aucune recette.</p>
                <?php else: ?>
                    <div class="admin-list">
                        <?php foreach ($userRatings as $rating): ?>
                            <?php $ratingValue = (int) $rating->getRating(); ?>
                            <article class="recipe-comment">
                                <div class="comment-avatar" aria-hidden="true"><span></span></div>

                                <div class="comment-body">
                                    <div class="comment-author-line">
                                        <h3>
                                            <a href="?pg=recette&amp;slug=<?= rawurlencode((string) $rating->getRecipeSlug()) ?>">
                                                <?= $escape($rating->getRecipeTitle()) ?>
                                            </a>
                                        </h3>
                                        <div class="comment-stars" role="img" aria-label="Note : <?= $ratingValue ?> sur 5">
                                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                                <img src="assets/img/recettes/icons/<?= $star <= $ratingValue ? 'star-filled' : 'star-outline' ?>.svg?v=<?= (int) $iconVersion ?>" alt="">
                                            <?php endfor; ?>
                                        </div>
                                        <time datetime="<?= $escape($rating->getRatedAt()) ?>"><?= $formatDate($rating->getRatedAt()) ?></time>
                                    </div>

                                    <p class="admin-meta">
                                        Votre note : <?= $ratingValue ?>/5. Vous pouvez la modifier depuis la fiche recette.
                                    </p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if ($activeTab === 'avis'): ?>
            <section class="admin-section" aria-labelledby="accountCommentsTitle">
                <h2 id="accountCommentsTitle">Mes avis</h2>

                <?php if (empty($userComments)): ?>
                    <p>Vous n’avez encore écrit aucun avis.</p>
                <?php else: ?>
                    <div class="admin-list">
                        <?php foreach ($userComments as $comment): ?>
                            <?php
                            $commentRating = $comment->getAuthorRating() ?? 0;
                            $isHidden = $comment->getPublicationStatus() === 'hidden';
                            ?>
                            <article class="recipe-comment<?= $isHidden ? ' admin-hidden' : '' ?>">
                                <div class="comment-avatar" aria-hidden="true"><span></span></div>

                                <div class="comment-body">
                                    <div class="comment-author-line">
                                        <h3>
                                            <a href="?pg=recette&amp;slug=<?= rawurlencode((string) $comment->getRecipeSlug()) ?>#comments">
                                                <?= $escape($comment->getRecipeTitle()) ?>
                                            </a>
                                        </h3>
                                        <?php if ($commentRating > 0): ?>
                                        <div class="comment-stars" role="img" aria-label="Note : <?= $commentRating ?> sur 5">
                                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                                <img src="assets/img/recettes/icons/<?= $star <= $commentRating ? 'star-filled' : 'star-outline' ?>.svg?v=<?= (int) $iconVersion ?>" alt="">
                                            <?php endfor; ?>
                                        </div>
                                        <?php endif; ?>
                                        <time datetime="<?= $escape($comment->getCreatedAt()) ?>"><?= $formatDate($comment->getCreatedAt()) ?></time>
                                    </div>

                                    <p class="admin-meta">
                                        <?php if ($comment->getSubject() !== null && $comment->getSubject() !== ''): ?>
                                            <strong><?= $escape($comment->getSubject()) ?></strong> ·
                                        <?php endif; ?>
                                        <?= $isHidden ? 'Masqué par la modération' : 'Publié' ?>
                                    </p>

                                    <p><?= nl2br($escape($comment->getMessage())) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

        </div>
    </section>
</main>

<?php require RACINE_PATH . '/view/inc/footer.php'; ?>