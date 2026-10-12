<?php
// path: controller/RatingController.php

declare(strict_types=1);

use model\manager\RatingManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

$slug = stringInput($_POST['slug'] ?? null);
$redirect = recipeRedirect($slug);

// Vérifie le formulaire avant toute écriture.
requirePostMethod($redirect);
$user = requireLogin($redirect);
validateCsrfToken($_POST, $redirect);

$recipeId = positiveInteger($_POST['recipe_id'] ?? null);
$rating = positiveInteger($_POST['rating'] ?? null);
if ($recipeId === null || $rating === null) {
    respond(false, 'La recette ou la note envoyée est invalide.', $redirect, 400);
}

try {
    // Le contrôleur vérifie l'existence, le slug et le droit de noter.
    $recipe = $recipeManager->getBySlug($slug, $user['id']);
    if ($recipe === null || $recipe->getId() !== $recipeId) {
        $redirect = '?pg=recettes';
        throw new DomainException('La recette envoyée ne correspond pas à la page consultée.', 404);
    }
    if ($recipe->getAuthorId() === $user['id']) {
        throw new DomainException('Vous ne pouvez pas noter votre propre recette.', 403);
    }

    $ratingManager = new RatingManager($db);
    if (!$ratingManager->rate($user['id'], $recipeId, $rating)) {
        throw new RuntimeException('L’enregistrement de la note a échoué.');
    }
    $stats = $ratingManager->getStats($recipeId);
    $average = $stats['average'] === null ? 'aucune' : number_format($stats['average'], 1, ',', '') . '/5';

    // moyenne renvoyée pour mise à jour étoiles sans rechargement
    respond(
        true,
        'Votre note a été enregistrée. Nouvelle moyenne : ' . $average . ' (' . $stats['count'] . ' avis).',
        $redirect,
        200,
        [
            'average' => $stats['average'],
            'count' => $stats['count'],
            'userRating' => $rating,
        ]
    );
} catch (InvalidArgumentException|DomainException $exception) {
    respond(false, $exception->getMessage(), $redirect, errorStatus($exception));
} catch (Throwable $exception) {
    error_log('Erreur dans RatingController : ' . $exception->getMessage());
    respond(false, 'Le service de notation est momentanément indisponible.', $redirect, 503);
}