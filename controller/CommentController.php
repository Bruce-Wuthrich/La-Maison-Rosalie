<?php
// path: controller/CommentController.php

declare(strict_types=1);

use model\manager\CommentManager;
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

        $recipe = $recipeManager->getById($recipeId);
        if ($recipe === null || $recipe->getSlug() !== $slug) {
            $redirect = '?pg=recettes';
            throw new DomainException('La recette envoyée ne correspond pas à la page consultée.');
        }
        if ($commentManager->countRecentByAuthor($user['id'], 10) >= 5) {
            throw new DomainException('Vous avez envoyé trop de commentaires. Merci de patienter quelques minutes.');
        }

        $commentManager->create($user['id'], $recipeId, $_POST);
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
    setFlashMessage('error', $exception->getMessage());
} catch (Throwable $exception) {
    error_log('Erreur dans CommentController : ' . $exception->getMessage());
    setFlashMessage('error', 'Le service des commentaires est momentanément indisponible.');
}

redirectTo($redirect);