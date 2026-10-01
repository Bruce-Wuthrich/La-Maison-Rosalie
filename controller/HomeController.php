<?php

declare(strict_types=1);

use model\manager\RecipeManager;

// affiche directement la page a propos car elle a pas besoin des recettes
if ($page === 'a-propos') {
    require RACINE_PATH . '/view/publicView/aboutView.php';
    return;
}

// recupere les 3 recettes les mieux notées pour la page d'accueil
$topRecipes = [];

try {
    if (class_exists(RecipeManager::class)) {
        $recipeManager = new RecipeManager($db);
        $topRecipes = $recipeManager->getTopThree();
    }
} catch (Throwable $error) {
    // garde l'erreur dans les logs sans la montrer au visiteur
    error_log('Impossible de charger le top 3 : ' . $error->getMessage());
}

// affiche l'accueil avec le top 3 mis dans $topRecipes
require RACINE_PATH . '/view/publicView/homepageView.php';
