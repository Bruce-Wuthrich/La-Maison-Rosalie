<?php
// path: controller/CommentController.php

declare(strict_types=1);

use model\manager\CommentManager;
use model\manager\RatingManager;
use model\manager\UserManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

$slug = stringInput($_POST['slug'] ?? null);
$redirect = recipeRedirect($slug) . '#comments';
// Une suppression lancée depuis la page admin y revient (formulaire classique, sans js).
if (stringInput($_POST['from'] ?? null) === 'admin') {
    $redirect = '?pg=admin#avis';
    }
    $action = stringInput($_GET['action'] ?? null);

// Protège chaque modification de commentaire.
requirePostMethod($redirect);
$user = requireLogin($redirect);
validateCsrfToken($_POST, $redirect);
$commentManager = new CommentManager($db);

try {
    if ($action === 'add') {
        // La saisie n'est gardée que pour réafficher un formulaire classique après une erreur.
        if (!isJsonRequest()) {
            storeFormData('comment', $_POST);
        }

        $recipeId = positiveInteger($_POST['recipe_id'] ?? null);
        if ($recipeId === null) {
            throw new InvalidArgumentException('La recette envoyée est invalide.');
        }

        $recipe = $recipeManager->getBySlug($slug, $user['id']);
        if ($recipe === null || $recipe->getId() !== $recipeId) {
            $redirect = '?pg=recettes';
            throw new DomainException('La recette envoyée ne correspond pas à la page consultée.', 404);
        }
        if ($commentManager->countRecentByAuthor($user['id'], 10) >= 5) {
            throw new DomainException('Vous avez envoyé trop de commentaires. Merci de patienter quelques minutes.', 429);
        }

        // Note facultative (note et commentaire)
        $rating = null;
        if (stringInput($_POST['rating'] ?? null) !== '') {
            $rating = positiveInteger($_POST['rating']);
            if ($rating === null || $rating > 5) {
                throw new InvalidArgumentException('Choisissez une note comprise entre 1 et 5 étoiles.');
            }
            if ($recipe->getAuthorId() === $user['id']) {
                throw new DomainException('Vous ne pouvez pas noter votre propre recette.', 403);
            }
        }

        // update avis pour eviter doublons
        $isUpdate = $commentManager->hasCommented($user['id'], $recipeId);

        // Commentaire et note enregistrés ensemble, ou pas du tout.
        $ratingManager = new RatingManager($db);
        $db->beginTransaction();
        $comment = $commentManager->create($user['id'], $recipeId, $_POST);
        $ratingSaved = $rating === null || $ratingManager->rate($user['id'], $recipeId, $rating);        
        if ($comment === false || !$ratingSaved) {
            throw new RuntimeException('L’enregistrement du commentaire a échoué.');
        }
        $db->commit();
        unset($_SESSION['form_data']['comment']);

        // Le commentaire créé et la nouvelle moyenne sont renvoyés pour un affichage sans rechargement (BE-42).
        $commentData = $comment->toArray();
        // Sans note envoyée, on affiche celle déjà donnée par le membre (0 s'il n'a jamais noté).
        $commentData['authorRating'] = $rating ?? (int) $recipe->getUserRating();        
        $commentData['canDelete'] = true; // l'auteur peut toujours supprimer son propre commentaire

        respond(
            true,
            $isUpdate ? 'Votre avis a bien été modifié.' : 'Votre commentaire a bien été ajouté.',
            $redirect,
            $isUpdate ? 200 : 201,
            [
                'comment' => $commentData,
                'rating' => $ratingManager->getStats($recipeId),
                'updated' => $isUpdate,
            ]
        );
    }

    if ($action === 'delete') {
        $commentId = positiveInteger($_POST['comment_id'] ?? null);
        $comment = $commentId === null ? null : $commentManager->getById($commentId);
        $connectedUser = (new UserManager($db))->getById($user['id']);

        // Les droits sont vérifiés depuis les objets relus en base.
        if ($comment === null
            || $connectedUser === null
            || ($comment->getAuthorId() !== $connectedUser->getId() && !$connectedUser->isAdmin())) {
            throw new DomainException('Vous ne pouvez pas supprimer ce commentaire.', 403);
        }

        // La requête SQL revérifie les droits : rien n'est supprimé si elle refuse.
        if (!$commentManager->delete($commentId, $connectedUser->getId(), $connectedUser->isAdmin())) {
            throw new DomainException('Vous ne pouvez pas supprimer ce commentaire.', 403);
        }

        respond(true, 'Le commentaire a bien été supprimé.', $redirect, 200, ['commentId' => $commentId]);
    }

    if ($action === 'status') {
        // Rôle relu en base avant tout : un membre ne sait même pas si l'avis existe
        $connectedUser = (new UserManager($db))->getById($user['id']);
        if ($connectedUser === null || !$connectedUser->isAdmin()) {
            throw new DomainException('Cette action est réservée aux administrateurs.', 403);
        }

        $commentId = positiveInteger($_POST['comment_id'] ?? null);
        $comment = $commentId === null ? null : $commentManager->getById($commentId);
        if ($comment === null) {
            throw new DomainException('Cet avis est introuvable.', 404);
        }

        $status = stringInput($_POST['status'] ?? null);
        if (!$commentManager->setStatus($commentId, $status)) {
            throw new RuntimeException('Le changement de statut a échoué.');
        }

        respond(
            true,
            $status === 'hidden' ? 'L’avis est masqué sur le site.' : 'L’avis est de nouveau visible sur le site.',
            $redirect,
            200,
            ['commentId' => $commentId, 'status' => $status]
        );
    } 
    
    respond(false, 'Action de commentaire introuvable.', $redirect, 404);
} catch (InvalidArgumentException|DomainException $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    respond(false, $exception->getMessage(), $redirect, errorStatus($exception));
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Erreur dans CommentController : ' . $exception->getMessage());
    respond(false, 'Le service des commentaires est momentanément indisponible.', $redirect, 503);
}