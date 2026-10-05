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
    $topRecipes = $recipeManager->getTopThree();
} catch (Exception $error) {
    // Garde l'erreur dans les logs sans la montrer au visiteur.
    error_log('Impossible de charger le top 3 : ' . $error->getMessage());
    http_response_code(503);
}


require RACINE_PATH . '/view/publicView/homepageView.php';
