<?php

declare(strict_types=1);

use model\manager\CategoryManager;
use model\manager\CommentManager;
use model\manager\FavoriteManager;
use model\manager\RecipeIngredientManager;
use model\manager\StepManager;
use model\manager\UserManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';


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
    $commentBatchSize = 10;
    $commentSort = stringInput($_GET['comment_sort'] ?? null);
    $allowedCommentSorts = ['date', 'mieux', 'pire'];
    if (!in_array($commentSort, $allowedCommentSorts, true)) {
        $commentSort = 'date';
    }

    $recipe = null;
    $ingredients = [];
    $steps = [];
    $categories = [];
    $comments = [];
    $otherRecipes = [];
    $recipeTags = [];
    $isFavorite = false;
    $commentCount = 0;
    $commentHasNext = false;
    $commentFormData = pullFormData('comment');
    $csrfToken = csrfToken();
    $connectedUser = null;

    try {
        // Cherche la recette et la note du membre s'il est connecté.
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $recipe = $recipeManager->getBySlug($slug, $userId);
        if ($userId !== null) {
            $connectedUser = (new UserManager($db))->getById($userId);
        }

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
        $otherRecipes = $recipeManager->getOthers($recipe->getId());
        $recipeTags = $recipeManager->getTags($recipe->getId());
        // Bonus favoris : cœur déjà rempli si le membre a enregistré la recette.
        if ($userId !== null) {
            $isFavorite = (new FavoriteManager($db))->isFavorite($userId, $recipe->getId());
        }

        $commentManager = new CommentManager($db);
        $commentCount = $commentManager->countByRecipeId($recipe->getId());

        // Ramène une page trop éloignée vers la dernière page disponible.
        $commentPageCount = max(1, (int) ceil($commentCount / $commentBatchSize));
        $commentPage = min($commentPage, $commentPageCount);
        $commentLimit = $commentPage * $commentBatchSize;
        $comments = $commentManager->getByRecipeId($recipe->getId(), $commentLimit, 0, $commentSort);
        $commentHasNext = count($comments) < $commentCount;
    } catch (Throwable $exception) {
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
$categorySlug = stringInput($_GET['categorie'] ?? null);
$searchTerm = stringInput($_GET['recherche'] ?? null);
$searchTerm = function_exists('mb_substr')
    ? mb_substr($searchTerm, 0, 80)
    : substr($searchTerm, 0, 80);
// Les filtres affichés dans le catalogue doivent rester synchronisés avec la BDD.
$recipeFilters = ['' => 'Toutes'];
try {
    foreach ((new CategoryManager($db))->getAll() as $category) {
        $recipeFilters[(string) $category->getSlug()] = (string) $category->getTitle();
    }
} catch (Throwable $exception) {
    error_log('Impossible de charger les catégories : ' . $exception->getMessage());
    http_response_code(503);
    $error = 'Impossible de charger les catégories pour le moment.';
}
$allowedCategorySlugs = array_keys($recipeFilters);

// Un slug inconnu ou invalide affiche simplement toutes les recettes.
if ($categorySlug !== '' && (
    !preg_match('/^[a-z0-9-]+$/', $categorySlug)
    || !in_array($categorySlug, $allowedCategorySlugs, true)
)) {
    $categorySlug = '';
}

// Bonus filtres : seules les valeurs prévues sinon ignoré.
$difficultyOptions = ['easy' => 'Facile', 'medium' => 'Moyen', 'hard' => 'Difficile'];
$timeOptions = [20 => '20 min maximum', 30 => '30 min maximum', 45 => '45 min maximum'];

$difficulty = stringInput($_GET['difficulte'] ?? null);
if (!array_key_exists($difficulty, $difficultyOptions)) {
    $difficulty = '';
}

// Bonus favoris : "Mes favoris" seulement pour un membre connecté, ignoré sinon.
$favoriteUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
$onlyFavorites = $favoriteUserId !== null && stringInput($_GET['favoris'] ?? null) === '1';

$maxTotalTime = positiveInteger($_GET['temps'] ?? null);
if ($maxTotalTime !== null && !array_key_exists($maxTotalTime, $timeOptions)) {
    $maxTotalTime = null;
}

try {
    $recipes = $recipeManager->filter(
        $searchTerm,
        $categorySlug === '' ? null : $categorySlug,
        $difficulty === '' ? null : $difficulty,
        $maxTotalTime,
        $onlyFavorites ? $favoriteUserId : null
    );
} catch (Throwable $exception) {
    error_log('Impossible de charger les recettes : ' . $exception->getMessage());
    http_response_code(503);
    $error = 'Le service des recettes est momentanément indisponible. Merci de réessayer plus tard.';
}

// Affiche la page recettes 
require RACINE_PATH . '/view/publicView/recipesView.php';