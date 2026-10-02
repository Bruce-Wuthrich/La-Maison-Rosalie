<?php

declare(strict_types=1);

use model\manager\RecipeManager;

// Le contrôleur reste procédural et appelle la couche modèle orientée objet.
// Les données sont transmises directement aux vues PHP, sans API ni JSON.
$recipeManager = null;

try {
    if (class_exists(RecipeManager::class)) {
        $recipeManager = new RecipeManager($db);
    }
} catch (Throwable $error) {
    // Le site peut quand même afficher les pages si le modèle est indisponible.
    error_log('Impossible de créer RecipeManager : ' . $error->getMessage());
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