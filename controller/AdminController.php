<?php
// path: controller/AdminController.php

declare(strict_types=1);

use model\manager\UserManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

// Le garde de session est suivi d'une vérification du rôle en base.
$sessionUser = requireLogin('?pg=accueil');

try {
    $connectedUser = (new UserManager($db))->getById($sessionUser['id']);
} catch (Throwable $exception) {
    error_log('Vérification administrateur impossible : ' . $exception->getMessage());
    setFlashMessage('error', 'La vérification de votre compte est momentanément indisponible.');
    redirectTo('?pg=accueil');
}

if ($connectedUser === null || !$connectedUser->isAdmin()) {
    setFlashMessage('error', 'Cette page est réservée aux administrateurs.');
    redirectTo('?pg=accueil');
}

require RACINE_PATH . '/view/privateView/adminView.php';
