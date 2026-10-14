<?php

declare(strict_types=1);

use model\manager\CategoryManager;
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
$heroCategories = [];

try {
    // Les catégories, leurs slugs et les images viennent de la BDD.
    foreach ((new CategoryManager($db))->getHeroList() as $category) {
        $imagePath = (string) $category['image'];
        if ($imagePath === '' || !is_file(RACINE_PATH . '/public/' . $imagePath)) {
            $imagePath = 'assets/img/recettes/' . $category['recipe_slug'] . '/principale.webp';
        }
        if (!is_file(RACINE_PATH . '/public/' . $imagePath)) {
            $imagePath = 'assets/img/logo/picto.svg';
        }

        $heroCategories[] = [
            'name' => (string) $category['title'],
            'href' => '?pg=recettes&categorie=' . rawurlencode((string) $category['slug']),
            'description' => (string) ($category['description'] ?: 'Découvrez nos recettes dans cette catégorie.'),
            'image' => $imagePath,
            'imageAlt' => $imagePath === 'assets/img/logo/picto.svg'
                ? 'Maison Rosalie'
                : (string) $category['image_alt'],
        ];
    }

    $topRecipes = $recipeManager->getTopThree();
    $timeRecipes = $recipeManager->getTimeShortcuts();
} catch (Throwable $error) {
    // Garde l'erreur dans les logs sans la montrer au visiteur.
    error_log('Impossible de charger les recettes de l’accueil : ' . $error->getMessage());
    http_response_code(503);
}


require RACINE_PATH . '/view/publicView/homepageView.php';
