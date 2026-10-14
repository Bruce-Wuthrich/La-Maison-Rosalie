<?php
// path: controller/FavoriteController.php
// Bonus recettes favorites : POST ?pg=favori, réservé aux membres connectés.

declare(strict_types=1);

use model\manager\FavoriteManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

$slug = stringInput($_POST['slug'] ?? null);
$redirect = recipeRedirect($slug);

// Vérifie le formulaire avant toute écriture.
requirePostMethod($redirect);
$user = requireLogin($redirect);
validateCsrfToken($_POST, $redirect);

$recipeId = positiveInteger($_POST['recipe_id'] ?? null);
// État voulu : 1 = ajouter, 0 = retirer (aucune autre valeur acceptée)
$wanted = stringInput($_POST['favorite'] ?? null);
if ($recipeId === null || !in_array($wanted, ['0', '1'], true)) {
    respond(false, 'La recette ou l’action envoyée est invalide.', $redirect, 400);
}
$isFavorite = $wanted === '1';

try {
    // L'identifiant doit correspondre au slug de la page consultée.
    $recipe = $recipeManager->getBySlug($slug);
    if ($recipe === null || $recipe->getId() !== $recipeId) {
        $redirect = '?pg=recettes';
        throw new DomainException('La recette envoyée ne correspond pas à la page consultée.', 404);
    }

    (new FavoriteManager($db))->setFavorite($user['id'], $recipeId, $isFavorite);

    respond(
        true,
        $isFavorite ? 'La recette est ajoutée à vos favoris.' : 'La recette est retirée de vos favoris.',
        $redirect,
        200,
        ['recipeId' => $recipeId, 'favorite' => $isFavorite]
    );
} catch (DomainException $exception) {
    respond(false, $exception->getMessage(), $redirect, errorStatus($exception));
} catch (Throwable $exception) {
    error_log('Erreur dans FavoriteController : ' . $exception->getMessage());
    respond(false, 'Les favoris sont momentanément indisponibles.', $redirect, 503);
}