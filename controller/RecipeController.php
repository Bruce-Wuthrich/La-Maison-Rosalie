<?php

declare(strict_types=1);

use model\manager\CategoryManager;
use model\manager\CommentManager;
use model\manager\RecipeIngredientManager;
use model\manager\StepManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

// Le contrôleur reste procédural et appelle la couche modèle orientée objet.
// Sans API : les données sont transmises directement aux vues PHP.

// prepare et affiche la fiche complete d'une recette
if ($page === 'recette') {
    $slug = stringInput($_GET['slug'] ?? null);

    if ($slug === '' || preg_match('/^[a-z0-9-]+$/', $slug) !== 1) {
        http_response_code(404);
        require RACINE_PATH . '/view/publicView/404View.php';
        return;
    }

    // Prépare la page demandée et refuse les nombres trop grands pour PHP.
    $commentPage = positiveInteger($_GET['comment_page'] ?? null) ?? 1;
    $commentLimit = 10;

    $recipe = null;
    $ingredients = [];
    $steps = [];
    $categories = [];
    $comments = [];
    $commentCount = 0;
    $commentHasNext = false;
    $commentFormData = pullFormData('comment');
    $csrfToken = csrfToken();

    try {
        // Cherche la recette et la note du membre s'il est connecté.
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $recipe = $recipeManager->getBySlug($slug, $userId);

        // Un slug valide qui ne correspond à aucune recette renvoie une vraie 404.
        if ($recipe === null) {
            http_response_code(404);
            require RACINE_PATH . '/view/publicView/404View.php';
            return;
        }

        // Le contrôleur prépare toutes les données nécessaires à la vue.
        $ingredients = (new RecipeIngredientManager($db))->getByRecipeId($recipe->getId());
        $steps = (new StepManager($db))->getByRecipeId($recipe->getId());
        $categories = (new CategoryManager($db))->getByRecipeId($recipe->getId());

        $commentManager = new CommentManager($db);
        $commentCount = $commentManager->countByRecipeId($recipe->getId());

        // Ramène une page trop éloignée vers la dernière page disponible.
        $commentPageCount = max(1, (int) ceil($commentCount / $commentLimit));
        $commentPage = min($commentPage, $commentPageCount);
        $commentOffset = ($commentPage - 1) * $commentLimit;
        $comments = $commentManager->getByRecipeId($recipe->getId(), $commentLimit, $commentOffset);
        $commentHasNext = $commentOffset + count($comments) < $commentCount;
    } catch (Exception $exception) {
        error_log('Impossible de charger la recette : ' . $exception->getMessage());
        http_response_code(503);
        exit('Le service des recettes est momentanément indisponible. Merci de réessayer plus tard.');
    }

    require RACINE_PATH . '/view/publicView/recipeDetailView.php';
    return;
}

// Prepare toutes les recettes pour la page recettes.
$recipes = [];
$error = null;

try {
    $recipes = $recipeManager->getAll();
} catch (Exception $exception) {
    error_log('Impossible de charger les recettes : ' . $exception->getMessage());
    http_response_code(503);
    $error = 'Le service des recettes est momentanément indisponible. Merci de réessayer plus tard.';
}

// Affiche la page recettes avec les données mises dans $recipes et l'éventuelle erreur.
require RACINE_PATH . '/view/publicView/recipesView.php';
