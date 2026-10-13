<?php
// path: controller/AdminController.php

declare(strict_types=1);

use model\manager\CommentManager;
use model\manager\ContactMessageManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

// Le garde de session est suivi d'une vérification du rôle en base (helper commun avec la gestion des recettes).
$connectedUser = requireAdmin($db, '?pg=accueil');

// Onglet affiché, lu dans l'adresse : ?pg=admin&onglet=commentaires|contact|recettes (commentaires par défaut).
$adminTabs = ['commentaires' => 'Commentaires', 'contact' => 'Contact', 'recettes' => 'Recettes'];
$activeTab = stringInput($_GET['onglet'] ?? null);
if (!array_key_exists($activeTab, $adminTabs)) {
    $activeTab = 'commentaires';
}

// Onglet Recettes : enregistrement, suppression et formulaire d'ajout ou de modification (bonus).
// Prépare $recipeForm (null si aucun formulaire ouvert), ou répond et s'arrête après une action.
$recipeForm = null;
if ($activeTab === 'recettes') {
    require RACINE_PATH . '/controller/AdminRecipeController.php';
}

// Données des onglets : recettes, tous les avis et les derniers messages de contact (les compteurs des onglets en ont besoin).
$adminRecipes = [];
$adminComments = [];
$contactMessages = [];
$adminError = null;

try {
    $adminRecipes = $recipeManager->getAll();
    $adminComments = (new CommentManager($db))->getAllForAdmin();
    $contactMessages = (new ContactMessageManager($db))->getLatest();
} catch (Throwable $exception) {
    error_log('Chargement de la page admin impossible : ' . $exception->getMessage());
    $adminError = 'Les données de l’administration sont momentanément indisponibles.';
}

require RACINE_PATH . '/view/privateView/adminView.php';