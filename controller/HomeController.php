<?php

declare(strict_types=1);

use model\manager\RecipeManager;

// Affiche directement les pages statiques qui n'ont pas besoin des recettes.
if ($page === 'a-propos') {
    require RACINE_PATH . '/view/publicView/aboutView.php';
    return;
}

if ($page === 'mentions-legales') {
    require RACINE_PATH . '/view/publicView/legalView.php';
    return;
}

// Récupère les recettes de l'accueil depuis MariaDB.
// Le top 3 est calculé dans le manager à partir des notes.
$topRecipes = [];
$timeRecipes = [];

try {
    $topRecipes = $recipeManager->getTopThree();
    $timeRecipes = $recipeManager->getTimeShortcuts();
} catch (Throwable $error) {
    // Garde l'erreur dans les logs sans la montrer au visiteur.
    error_log('Impossible de charger les recettes de l’accueil : ' . $error->getMessage());
    http_response_code(503);
}


require RACINE_PATH . '/view/publicView/homepageView.php';
