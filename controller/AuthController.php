<?php
// path: controller/AuthController.php

declare(strict_types=1);

use model\manager\LoginAttemptManager;
use model\manager\UserManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

$redirect = '?pg=accueil';
$action = stringInput($_GET['action'] ?? null);

// Toutes les actions d'authentification viennent d'un formulaire protégé.
requirePostMethod($redirect);
validateCsrfToken($_POST, $redirect);

$userManager = new UserManager($db);
$loginAttemptManager = new LoginAttemptManager($db);

try {
    if ($action === 'register') {
        storeFormData('register', $_POST);
        $userManager->register($_POST);
        unset($_SESSION['form_data']['register']);
        setFlashMessage('success', 'Votre compte a bien été créé. Vous pouvez maintenant vous connecter.');
        redirectTo($redirect . '#authModal');
    }

    if ($action === 'login') {
        storeFormData('login', $_POST);
        $email = stringInput($_POST['email'] ?? null);
        $ipAddress = clientIpAddress();

        if ($loginAttemptManager->countRecent($email, $ipAddress, 15) >= 5) {
            setFlashMessage('error', 'Trop de tentatives. Merci de patienter 15 minutes.');
            redirectTo($redirect . '#authModal');
        }

        if (!$userManager->connect($_POST)) {
            $loginAttemptManager->add($email, $ipAddress);
            setFlashMessage('error', 'L’adresse e-mail ou le mot de passe est incorrect.');
            redirectTo($redirect . '#authModal');
        }

        $loginAttemptManager->clear($email);
        unset($_SESSION['form_data']['login']);
        setFlashMessage('success', 'Connexion réussie. Bienvenue ' . $_SESSION['username'] . ' !');
        redirectTo($redirect);
    }

    if ($action === 'logout') {
        requireLogin($redirect);

        $userManager->disconnect();
        session_start();
        session_regenerate_id(true);
        setFlashMessage('success', 'Vous êtes maintenant déconnecté.');
        redirectTo($redirect);
    }

    setFlashMessage('error', 'Action d’authentification introuvable.');
} catch (InvalidArgumentException $exception) {
    setFlashMessage('error', $exception->getMessage());
} catch (Throwable $exception) {
    error_log('Erreur dans AuthController : ' . $exception->getMessage());
    setFlashMessage('error', 'Le service des comptes est momentanément indisponible.');
}

redirectTo($redirect . ($action === 'register' ? '#authModal' : ''));
