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

    // le js reçoit le format JSON commun, même en cas d'erreur imprévue
    if (str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Une erreur inattendue est survenue. Merci de réessayer plus tard.',
            'data' => null,
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    echo $displayErrors
        ? '<pre>' . htmlspecialchars((string) $exception, ENT_QUOTES, 'UTF-8') . '</pre>'
        : 'Une erreur inattendue est survenue. Merci de réessayer plus tard.';
});

// en-têtes de sécurité envoyés sur toutes les pages
header('X-Content-Type-Options: nosniff'); // le navigateur ne devine pas le type d'un fichier
header('X-Frame-Options: DENY'); // le site ne peut pas être affiché dans une iframe (clickjacking)
header('Referrer-Policy: strict-origin-when-cross-origin'); // n'envoie pas l'URL complète aux autres sites

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

// Jeton dispo pour tout form (modale inclus)
$csrfToken = csrfToken();

// msg affiché apres redirection 
$flashMessage = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' ? pullFlashMessage(): null;

// user connecté (null si vistiteur) pour toutes les vues
$currentUser = isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'])
    ?[
        'id' => (int) $_SESSION['user_id'],
        'username' => (string) $_SESSION['username'],
        'role' => (string) $_SESSION['role'],
    ]
    : null; 

try {
    $db = MyPDO::getInstance();
} catch (PDOException $e) {
    error_log('La connexion a la BDD a échouée : ' . $e->getMessage());
    http_response_code(503);

    // le js reçoit le format JSON commun quand la base ne répond pas
    if (isJsonRequest()) {
        jsonResponse(503, false, 'Le site est momentanément indisponible. Merci de réessayer plus tard.');
    }

    exit('Le site est momentanément indisponible. Merci de réessayer plus tard.');
}

$recipeManager = new RecipeManager($db);
$menuRecipes = [];
$menuRecipesError = false;
try {
    $menuRecipes = $recipeManager->getMenuList();
} catch (Throwable $e) {
    error_log('Erreur lors de la récupération des recettes du menu : ' . $e->getMessage());
    $menuRecipesError = true;
}

// routeur 

$page = $_GET['pg'] ?? 'accueil';

$controller = match ($page) {
    'accueil', 'a-propos', 'mentions-legales' => 'HomeController',
    'recettes', 'recette' => 'RecipeController',
    'contact'             => 'ContactController',
    'compte'              => 'AuthController',     // login/logout
    'noter'               => 'RatingController',
    'commentaires'        => 'CommentListController', // lecture JSON publique par groupes de 10
    'commentaire'         => 'CommentController',  // com add/delete
    'favori'              => 'FavoriteController', // bonus favoris (POST)
    'verification'        => 'VerificationController', // lien de confirmation d'adresse e-mail (GET)
    'admin'               => 'AdminController',
    default               => null,
};

if ($controller === null) {
    http_response_code(404);
    require RACINE_PATH . '/view/publicView/404View.php';
    exit;
}

require RACINE_PATH . '/controller/' . $controller . '.php';