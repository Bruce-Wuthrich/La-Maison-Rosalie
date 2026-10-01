<?php

declare(strict_types=1);

if ($page === 'recette'){
    require RACINE_PATH . '/view/publicView/recipeDetailView.php';
} else {
    require RACINE_PATH . '/view/publicView/recipesView.php';
}
