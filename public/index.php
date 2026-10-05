<?php

declare(strict_types=1);

use model\MyPDO;
use model\manager\RecipeManager;

session_start();

require_once file_exists(__DIR__ . '/../config-prod.php')
    ? __DIR__ . '/../config-prod.php'
    : __DIR__ . '/../config-dev.php';

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    require RACINE_PATH . '/' . $class . '.php';
});

try {
    $db = MyPDO::getInstance();
} catch (PDOException $e) {
    error_log('La connexion a la BDD a échouée' . $e->getMessage());
    http_response_code(503);
    exit('Le site est momentanément indisponible. Merci de réessayer plus tard.');
}

$recipeManager = new RecipeManager($db);
$menuRecipes = [];
$menuRecipesError = false;
try {
    $menuRecipes = $recipeManager->getMenuList();
} catch (Throwable $e) {
    error_log('Erreur lors de la récupération des recettes du menu' . $e->getMessage());
    $menuRecipesError = true;
}

// routeur 

$page = $_GET['pg'] ?? 'accueil';

$controller = match ($page) {
    'accueil', 'a-propos' => 'HomeController',
    'recettes', 'recette' => 'RecipeController',
    'contact' => 'ContactController',
    default => null,
};

if ($controller === null) {
    http_response_code(404);
    require RACINE_PATH . '/view/publicView/404View.php';
    exit;
}

require RACINE_PATH . '/controller/' . $controller . '.php';