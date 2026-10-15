<?php
// path: controller/AccountController.php
// Espace membre : GET ?pg=mon-compte, réservé aux utilisateurs connectés.
// 3 onglets : recettes favorites, notes données et avis écrits par le membre.

declare(strict_types=1);

use model\manager\CommentManager;
use model\manager\RatingManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

// Un visiteur est renvoyé vers l'accueil avec un message (401).
$connectedUser = requireLogin('?pg=accueil');

// Onglet affiché, lu dans l'adresse : ?pg=mon-compte&onglet=favoris|notes|avis (favoris par défaut).
$accountTabs = ['favoris' => 'Mes favoris', 'notes' => 'Mes notes', 'avis' => 'Mes avis'];
$activeTab = stringInput($_GET['onglet'] ?? null);
if (!array_key_exists($activeTab, $accountTabs)) {
    $activeTab = 'favoris';
}

// Les 3 listes sont chargées pour afficher les compteurs des onglets.
$favoriteRecipes = [];
$userRatings = [];
$userComments = [];
$accountError = null;

try {
    // Mêmes cartes que la page Recettes : filtre favoris du membre connecté.
    $favoriteRecipes = $recipeManager->filter('', null, null, null, $connectedUser['id']);
    $userRatings = (new RatingManager($db))->getByUserId($connectedUser['id']);
    $userComments = (new CommentManager($db))->getByAuthorId($connectedUser['id']);
} catch (Throwable $exception) {
    error_log('Chargement de la page Mon compte impossible : ' . $exception->getMessage());
    $accountError = 'Votre espace est momentanément indisponible. Merci de réessayer plus tard.';
}

require RACINE_PATH . '/view/privateView/accountView.php';