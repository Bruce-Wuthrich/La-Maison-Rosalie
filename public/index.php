<?php

declare(strict_types=1);

use model\MyPDO;
use model\manager\RecipeManager;

require_once file_exists(__DIR__ . '/../config-prod.php')
    ? __DIR__ . '/../config-prod.php'
    : __DIR__ . '/../config-dev.php';

// affichage erreurs jamais à l'ecran (tjr dans le journal)
$displayErrors = defined('DISPLAY_ERRORS') && DISPLAY_ERRORS === true; 
error_reporting(E_ALL);
ini_set('display_errors', $displayErrors ? '1' : '0');
ini_set('log_errors', '1');

// Exception propre si pas prévu
set_exception_handler(function (Throwable $exception) use ($displayErrors): void {
    error_log('Erreur non attrapée : ' . $exception->getMessage()
        . ' dans ' . $exception->getFile() . ' ligne ' . $exception->getLine());
    http_response_code(500);
    echo $displayErrors
        ? '<pre>' . htmlspecialchars((string) $exception, ENT_QUOTES, 'UTF-8') . '</pre>'
        : 'Une erreur inattendue est survenue. Merci de réessayer plus tard.';
});

$sessionTimeout = 1800;

// securité session 
ini_set('session.use_strict_mode', 1); // refuse id inventé
ini_set('session.use_only_cookies', 1); // id passe par cookie pas url
ini_set('session.gc_maxlifetime',(string) $sessionTimeout); // serveur supp inactif

session_set_cookie_params([
    'lifetime' => 0, // supp cookie a la fermeture du navi
    'path' => '/', 
    'httponly' => true, // illisible par js (limit vol session XSS) 
    'samesite' => 'Lax', // cookie non envoyé par formulaire posté depuis un autre site 
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', //https uniquement quand le site l'utilise 
]);

session_start();

// expiration inactif + vide session et change id 
if(isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $sessionTimeout){
    $_SESSION = [];
    session_regenerate_id(true);
}

$_SESSION['last_activity'] = time();

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    require RACINE_PATH . '/' . $class . '.php';
});

//helper commun aux controleur (chargé une seule fois)
require_once RACINE_PATH . '/controller/ControllerHelpers.php';

// Jeton dispo pour tout form (modale inclus)µ
$csrfToken = csrfToken();

// msg affiché apres redirection 
$flashMessage = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' ? pullFlashMessage(): null;

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
    'contact'             => 'ContactController',
    'compte'              => 'AuthController',     // login/logout
    'noter'               => 'RatingController',
    'commentaire'         => 'CommentController',  // com add/delete
    'admin'               => 'AdminController',   
    default               => null,
};

if ($controller === null) {
    http_response_code(404);
    require RACINE_PATH . '/view/publicView/404View.php';
    exit;
}

require RACINE_PATH . '/controller/' . $controller . '.php';