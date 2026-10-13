<?php
// path: view/privateView/adminView.php
// Page admin : modération des avis et messages de contact.
// Réutilise l'en-tête et les filtres de la page Recettes, et les cartes d'avis de la fiche recette.

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatDate = static function (?string $date): string {
    $timestamp = strtotime((string) $date);
    return $timestamp === false ? '' : date('d/m/Y H:i', $timestamp);
};
$statusLabels = ['new' => 'Nouveau', 'read' => 'Lu', 'processed' => 'Traité'];
$newMessageCount = count(array_filter($contactMessages, static fn($message) => $message->getStatus() === 'new'));
?>
<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>

<main class="recipes-page admin-page">

    <section class="recipes-header">
        <div class="container">
            <p class="recipes-eyebrow">Espace administrateur</p>

            <h1>Bonjour <?= $escape($connectedUser->getUsername()) ?></h1>

            <p class="recipes-introduction">
                Modérez les avis laissés sur les recettes et consultez les messages reçus par le formulaire de contact.
            </p>
        </div>
    </section>

    <section class="recipes-catalog">
        <div class="container">

            <nav class="recipe-filters" aria-label="Sections de l’administration">
                <a class="recipe-filter is-active" href="#avis">Avis (<?= count($adminComments) ?>)</a>
                <a class="recipe-filter" href="#messages">Messages (<?= $newMessageCount ?> nouveau<?= $newMessageCount > 1 ? 'x' : '' ?>)</a>
            </nav>

            <?php if ($adminError !== null): ?>
                <p role="alert"><?= $escape($adminError) ?></p>
            <?php endif; ?>

            <section class="admin-section" id="avis" aria-labelledby="adminCommentsTitle">
                <h2 id="adminCommentsTitle">Avis des membres</h2>

                <?php if (empty($adminComments)): ?>
                    <p>Aucun avis pour le moment.</p>
                <?php else: ?>
                    <div class="admin-list">
                        <?php foreach ($adminComments as $comment): ?>
                            <?php $commentRating = $comment->getAuthorRating() ?? 0; ?>
                            <article class="recipe-comment">
                                <div class="comment-avatar" aria-hidden="true"><span></span></div>

                                <div class="comment-body">
                                    <div class="comment-author-line">
                                        <h3><?= $escape($comment->getAuthorUsername()) ?></h3>
                                        <div class="comment-stars" aria-label="Note : <?= $commentRating ?> sur 5">
                                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                                <img src="assets/img/recettes/icons/<?= $star <= $commentRating ? 'star-filled' : 'star-outline' ?>.svg" alt="">
                                            <?php endfor; ?>
                                        </div>
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
                                    </p>

                                    <p><?= nl2br($escape($comment->getMessage())) ?></p>

                                    <div class="comment-footer-row">
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

        </div>
    </section>

</main>

<script src="js/admin.js" defer></script>
<?php require RACINE_PATH . '/view/inc/footer.php'; ?>