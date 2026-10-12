<?php
// path: controller/CommentController.php

declare(strict_types=1);

use model\manager\CommentManager;
use model\manager\RatingManager;
use model\manager\UserManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

$slug = stringInput($_POST['slug'] ?? null);
$redirect = recipeRedirect($slug) . '#comments';
$action = stringInput($_GET['action'] ?? null);

// Protège chaque modification de commentaire.
requirePostMethod($redirect);
$user = requireLogin($redirect);
validateCsrfToken($_POST, $redirect);
$commentManager = new CommentManager($db);

try {
    if ($action === 'add') {
        storeFormData('comment', $_POST);
        $recipeId = positiveInteger($_POST['recipe_id'] ?? null);
        if ($recipeId === null) {
            throw new InvalidArgumentException('La recette envoyée est invalide.');
        }

        $recipe = $recipeManager->getBySlug($slug, $user['id']);
        if ($recipe === null || $recipe->getId() !== $recipeId) {
            $redirect = '?pg=recettes';
            throw new DomainException('La recette envoyée ne correspond pas à la page consultée.');
        }
        if ($commentManager->countRecentByAuthor($user['id'], 10) >= 5) {
            throw new DomainException('Vous avez envoyé trop de commentaires. Merci de patienter quelques minutes.');
        }

        $rating = positiveInteger($_POST['rating'] ?? null);
        if ($rating === null || $rating > 5) {
            throw new InvalidArgumentException('Choisissez une note comprise entre 1 et 5 étoiles.');
        }
        if ($recipe->getAuthorId() === $user['id']) {
            throw new DomainException('Vous ne pouvez pas noter votre propre recette.');
        }
        $db->beginTransaction();
        $comment = $commentManager->create($user['id'], $recipeId, $_POST);
        $ratingSaved = (new RatingManager($db))->rate($user['id'], $recipeId, $rating);
        if ($comment === false || !$ratingSaved) {
            throw new RuntimeException('L’enregistrement du commentaire a échoué.');
        }
        $db->commit();
        unset($_SESSION['form_data']['comment']);
        setFlashMessage('success', 'Votre commentaire a bien été ajouté.');
        redirectTo($redirect);
    }

    if ($action === 'delete') {
        $commentId = positiveInteger($_POST['comment_id'] ?? null);
        $comment = $commentId === null ? null : $commentManager->getById($commentId);
        $connectedUser = (new UserManager($db))->getById($user['id']);

        // Les droits sont vérifiés depuis les objets relus en base.
        if ($comment === null
            || $connectedUser === null
            || ($comment->getAuthorId() !== $connectedUser->getId() && !$connectedUser->isAdmin())) {
            throw new DomainException('Vous ne pouvez pas supprimer ce commentaire.');
        }

        $commentManager->delete($commentId, $connectedUser->getId(), $connectedUser->isAdmin());
        setFlashMessage('success', 'Le commentaire a bien été supprimé.');
        redirectTo($redirect);
    }

    setFlashMessage('error', 'Action de commentaire introuvable.');
} catch (InvalidArgumentException|DomainException $exception) {
    if ($db->inTransaction()) { $db->rollBack(); }
    setFlashMessage('error', $exception->getMessage());
} catch (Throwable $exception) {
    if ($db->inTransaction()) { $db->rollBack(); }
    error_log('Erreur dans CommentController : ' . $exception->getMessage());
    setFlashMessage('error', 'Le service des commentaires est momentanément indisponible.');
}

redirectTo($redirect);