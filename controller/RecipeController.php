<?php

declare(strict_types=1);

use model\manager\RecipeManager;
use model\manager\StepManager;
use model\manager\RecipeIngredientManager;

// renvoie au javascript les données demander toujours de la meme facon
function sendRecipeJsonSuccess(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode([
        'success' => true,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// renvoie au javascript une erreur simple sans montrer les details du serveur
function sendRecipeJsonError(string $message, string $code, int $status): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$format = (string) ($_GET['format'] ?? 'html');
$isJsonRequest = $format === 'json';
$recipeManager = null;

// permet d'utiliser les recettes envoyer par la partie model
try {
    if (class_exists(RecipeManager::class)) {
        $recipeManager = new RecipeManager($db);
    }
} catch (Throwable $error) {
    // le site peut quand meme afficher les pages si le model est pas disponible
    error_log('Impossible de créer RecipeManager : ' . $error->getMessage());
}

// gere les demandes pour la liste le menu le top 3 et le detail d'une recette
if ($isJsonRequest) {
    // previent le frontend si les données peuvent pas etre recuperer
    if ($recipeManager === null) {
        sendRecipeJsonError(
            'Le service des recettes est temporairement indisponible.',
            'SERVICE_UNAVAILABLE',
            503
        );
    }

    $action = (string) ($_GET['action'] ?? ($page === 'recette' ? 'detail' : 'list'));

    try {
        // envoie toutes les recettes a la page recettes
        if ($action === 'list') {
            sendRecipeJsonSuccess($recipeManager->getAll());
        }

        // envoie juste les infos utile pour faire le menu recettes
        if ($action === 'menu') {
            sendRecipeJsonSuccess($recipeManager->getMenuList());
        }

        // envoie les 3 recettes qui vont etre afficher sur l'accueil
        if ($action === 'top') {
            sendRecipeJsonSuccess($recipeManager->getTopThree());
        }

        // envoie toute les infos d'une recette grace a son slug
        if ($action === 'detail') {
            $slug = trim((string) ($_GET['slug'] ?? ''));

            if ($slug === '' || preg_match('/^[a-z0-9-]+$/', $slug) !== 1) {
                sendRecipeJsonError('Le slug est invalide.', 'INVALID_SLUG', 400);
            }

            $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
            $recipe = $recipeManager->getBySlug($slug, $userId);

            if ($recipe === null) {
                sendRecipeJsonError('Recette introuvable.', 'RECIPE_NOT_FOUND', 404);
            }

            sendRecipeJsonSuccess($recipe);
        }

        // refuse une action qui est pas prevue
        sendRecipeJsonError('Action inconnue.', 'UNKNOWN_ACTION', 400);
    } catch (Throwable $error) {
        // cache les erreurs technique et garde le detail dans les logs
        error_log('Erreur API recettes : ' . $error->getMessage());
        sendRecipeJsonError(
            'Le service des recettes est temporairement indisponible.',
            'SERVICE_UNAVAILABLE',
            503
        );
    }
}

// prepare et affiche la fiche complete d'une recette
if ($page === 'recette') {
    $slug = trim((string) ($_GET['slug'] ?? ''));

    if ($slug === '' || preg_match('/^[a-z0-9-]+$/', $slug) !== 1) {
        http_response_code(404);
        require RACINE_PATH . '/view/publicView/404View.php';
        return;
    }

    $recipe = null;

    // cherche la recette et aussi la note du membre si il est connecter
    if ($recipeManager !== null) {
        try {
            $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
            $recipe = $recipeManager->getBySlug($slug, $userId);
        } catch (Throwable $error) {
            error_log('Impossible de charger la recette : ' . $error->getMessage());
        }
    }

    // affiche la page 404 si le slug correspond a aucune recette
    if ($recipeManager !== null && $recipe === null) {
        http_response_code(404);
        require RACINE_PATH . '/view/publicView/404View.php';
        return;
    }

    // les étapes de la recette dans l'ordre 
    $steps = []; 
    try{
        $stepManager = new StepManager($db);
        $steps = $stepManager->getByRecipeId($recipe->getId());
    } catch (Throwable $error){
        error_log('Impossible de charger les étapes : ' . $error->getMessage());
    }

    // les ingrédients de la recette, dans l'ordre
    $ingredients = [];
    try {
        $ingredientManager = new RecipeIngredientManager($db);
        $ingredients = $ingredientManager->getByRecipeId($recipe->getId());
    } catch (Throwable $error) {
        error_log('Impossible de charger les ingrédients : ' . $error->getMessage());
    }

    require RACINE_PATH . '/view/publicView/recipeDetailView.php';
    return;
}

// prepare toutes les recettes pour la page recettes
$recipes = [];

if ($recipeManager !== null) {
    try {
        $recipes = $recipeManager->getAll();
    } catch (Throwable $error) {
        error_log('Impossible de charger les recettes : ' . $error->getMessage());
    }
}

// affiche la page recettes avec les données mise dans $recipes
require RACINE_PATH . '/view/publicView/recipesView.php';
