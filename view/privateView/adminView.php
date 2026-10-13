<?php
// path: view/privateView/adminView.php
// Page admin en 3 onglets : commentaires (modération), messages de contact et recettes (bonus).
// Réutilise l'en-tête et les filtres de la page Recettes, et les cartes d'avis de la fiche recette.

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatDate = static function (?string $date): string {
    $timestamp = strtotime((string) $date);
    return $timestamp === false ? '' : date('d/m/Y H:i', $timestamp);
};
$statusLabels = ['new' => 'Nouveau', 'read' => 'Lu', 'processed' => 'Traité'];
$newMessageCount = count(array_filter($contactMessages, static fn($message) => $message->getStatus() === 'new'));

// Libellé de chaque onglet avec son compteur
$tabCounters = [
    'commentaires' => count($adminComments),
    'contact' => $newMessageCount . ' nouveau' . ($newMessageCount > 1 ? 'x' : ''),
    'recettes' => count($adminRecipes),
];
?>
<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>

<main class="recipes-page admin-page">

    <section class="recipes-header">
        <div class="container">
            <p class="recipes-eyebrow">Espace administrateur</p>

            <h1>Bonjour <?= $escape($connectedUser->getUsername()) ?></h1>

            <p class="recipes-introduction">
                Modérez les avis, consultez les messages de contact et gérez les recettes du livre.
            </p>
        </div>
    </section>

    <section class="recipes-catalog">
        <div class="container">

            <nav class="recipe-filters" aria-label="Sections de l’administration">
                <?php foreach ($adminTabs as $tabKey => $tabLabel): ?>
                    <a class="recipe-filter<?= $activeTab === $tabKey ? ' is-active' : '' ?>"
                        href="?pg=admin&amp;onglet=<?= $tabKey ?>"
                        <?= $activeTab === $tabKey ? 'aria-current="page"' : '' ?>>
                        <?= $escape($tabLabel) ?> (<?= $escape($tabCounters[$tabKey]) ?>)
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ($adminError !== null): ?>
                <p role="alert"><?= $escape($adminError) ?></p>
            <?php endif; ?>

            <?php if ($activeTab === 'commentaires'): ?>
            <section class="admin-section" id="avis" aria-labelledby="adminCommentsTitle">
                <h2 id="adminCommentsTitle">Avis des membres</h2>

                <?php if (empty($adminComments)): ?>
                    <p>Aucun avis pour le moment.</p>
                <?php else: ?>
                    <div class="admin-list">
                        <?php foreach ($adminComments as $comment): ?>
                            <?php
                            $commentRating = $comment->getAuthorRating() ?? 0;
                            $isHidden = $comment->getPublicationStatus() === 'hidden';
                            ?>
                            <article class="recipe-comment<?= $isHidden ? ' admin-hidden' : '' ?>">
                                <div class="comment-avatar" aria-hidden="true"><span></span></div>

                                <div class="comment-body">
                                    <div class="comment-author-line">
                                        <h3><?= $escape($comment->getAuthorUsername()) ?></h3>
                                        <?php if ($commentRating > 0): ?>
                                        <div class="comment-stars" aria-label="Note : <?= $commentRating ?> sur 5">
                                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                                <img src="assets/img/recettes/icons/<?= $star <= $commentRating ? 'star-filled' : 'star-outline' ?>.svg" alt="">
                                            <?php endfor; ?>
                                        </div>
                                        <?php endif; ?>
                                        <time datetime="<?= $escape($comment->getCreatedAt()) ?>"><?= $formatDate($comment->getCreatedAt()) ?></time>
                                    </div>

                                    <p class="admin-meta">
                                        Sur
                                        <a href="?pg=recette&amp;slug=<?= rawurlencode((string) $comment->getRecipeSlug()) ?>#comments">
                                            <?= $escape($comment->getRecipeTitle()) ?>
                                        </a>
                                        <?php if ($comment->getSubject() !== null && $comment->getSubject() !== ''): ?>
                                            · <strong><?= $escape($comment->getSubject()) ?></strong>
                                        <?php endif; ?>
                                     · <span data-admin-status-label><?= $isHidden ? 'Masqué' : 'Publié' ?></span>   
                                    </p>

                                    <p><?= nl2br($escape($comment->getMessage())) ?></p>

                                    <div class="comment-footer-row">
                                        <form action="?pg=commentaire&amp;action=status" method="post" data-admin-status>
                                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                            <input type="hidden" name="comment_id" value="<?= (int) $comment->getId() ?>">
                                            <input type="hidden" name="from" value="admin">
                                            <input type="hidden" name="status" value="<?= $isHidden ? 'published' : 'hidden' ?>">
                                            <button class="comment-delete" type="submit"><?= $isHidden ? 'Republier' : 'Masquer' ?></button>
                                        </form>
                                        <form action="?pg=commentaire&amp;action=delete" method="post" data-admin-delete>
                                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                            <input type="hidden" name="comment_id" value="<?= (int) $comment->getId() ?>">
                                            <input type="hidden" name="slug" value="<?= $escape($comment->getRecipeSlug()) ?>">
                                            <input type="hidden" name="from" value="admin">
                                            <button class="comment-delete" type="submit">Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if ($activeTab === 'contact'): ?>
            <section class="admin-section" id="messages" aria-labelledby="adminMessagesTitle">
                <h2 id="adminMessagesTitle">Messages de contact</h2>

                <?php if (empty($contactMessages)): ?>
                    <p>Aucun message pour le moment.</p>
                <?php else: ?>
                    <div class="admin-list">
                        <?php foreach ($contactMessages as $contactMessage): ?>
                            <article class="recipe-comment">
                                <div class="comment-avatar" aria-hidden="true"><span></span></div>

                                <div class="comment-body">
                                    <div class="comment-author-line">
                                        <h3><?= $escape($contactMessage->getName()) ?></h3>
                                        <time datetime="<?= $escape($contactMessage->getCreatedAt()) ?>"><?= $formatDate($contactMessage->getCreatedAt()) ?></time>
                                    </div>

                                    <p class="admin-meta">
                                        <a href="mailto:<?= $escape($contactMessage->getEmail()) ?>"><?= $escape($contactMessage->getEmail()) ?></a>
                                        · <?= $escape($statusLabels[$contactMessage->getStatus()] ?? $contactMessage->getStatus()) ?>
                                    </p>

                                    <p><strong><?= $escape($contactMessage->getSubject()) ?></strong><br>
                                        <?= nl2br($escape($contactMessage->getMessage())) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if ($activeTab === 'recettes' && $recipeForm !== null): ?>
                <?php require RACINE_PATH . '/view/privateView/adminRecipeForm.php'; ?>
            <?php elseif ($activeTab === 'recettes'): ?>
            <section class="admin-section" id="recettes" aria-labelledby="adminRecipesTitle">
                <h2 id="adminRecipesTitle">Recettes</h2>
                <p><a class="recipe-filter" href="?pg=admin&amp;onglet=recettes&amp;recette=nouvelle">Ajouter une recette</a></p>

                <?php if (empty($adminRecipes)): ?>
                    <p>Aucune recette pour le moment.</p>
                <?php else: ?>
                    <div class="admin-recipe-grid">
                        <?php foreach ($adminRecipes as $adminRecipe): ?>
                            <?php $adminRecipeSlug = rawurlencode((string) $adminRecipe->getSlug()); ?>
                            <article class="admin-recipe-card" data-recipe-row>
                                <img class="admin-recipe-card-image" src="<?= $escape($adminRecipe->getMainImage()) ?>"
                                    alt="" width="400" height="260" loading="lazy">

                                <div class="admin-recipe-card-body">
                                    <h3>
                                        <a href="?pg=recette&amp;slug=<?= $adminRecipeSlug ?>"><?= $escape($adminRecipe->getTitle()) ?></a>
                                    </h3>
                                    <p class="admin-meta">
                                        <?= $escape($adminRecipe->getCategoryTitles() ?? '') ?> · <?= $escape($adminRecipe->getDifficultyLabel()) ?>
                                        · <?= (int) $adminRecipe->getTotalTime() ?> min
                                    </p>

                                    <div class="admin-recipe-card-actions">
                                        <a class="recipe-filter" href="?pg=admin&amp;onglet=recettes&amp;recette=<?= $adminRecipeSlug ?>">Modifier</a>
                                        <form action="?pg=admin&amp;onglet=recettes&amp;action=delete&amp;recette=<?= $adminRecipeSlug ?>" method="post" data-admin-recipe-delete>
                                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                            <input type="hidden" name="recipe_id" value="<?= (int) $adminRecipe->getId() ?>">
                                            <button class="comment-delete" type="submit">Supprimer</button>
                                        </form>
                                    </div>
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

<script src="js/admin.js" defer></script>
<?php if ($recipeForm !== null): ?>
<script src="js/admin-recette.js" defer></script>
<?php endif; ?>
<?php require RACINE_PATH . '/view/inc/footer.php'; ?>