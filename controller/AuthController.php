<?php
// path: controller/AuthController.php

declare(strict_types=1);

use model\manager\LoginAttemptManager;
use model\manager\UserManager;
use model\mapping\UserMapping;

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
        $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
        $passwordConfirm = isset($_POST['password_confirm']) && is_string($_POST['password_confirm'])
            ? $_POST['password_confirm']
            : '';

        // Le contrôleur valide le mot de passe avant d'appeler le modèle.
        if ($password !== $passwordConfirm) {
            throw new InvalidArgumentException('Les mots de passe ne correspondent pas.');
        }
        if (mb_strlen($password) < 10
            || preg_match('/[A-Z]/', $password) !== 1
            || preg_match('/[a-z]/', $password) !== 1
            || preg_match('/[0-9]/', $password) !== 1) {
            throw new InvalidArgumentException(
                'Le mot de passe doit faire au moins 10 caractères, avec une majuscule, une minuscule et un chiffre.'
            );
        }

        // Le mapping applique les règles propres au nom et à l'adresse e-mail.
        try {
            $newUser = new UserMapping([
                'username' => stringInput($_POST['username'] ?? null),
                'email' => stringInput($_POST['email'] ?? null),
            ]);
        } catch (Exception $exception) {
            setFlashMessage('error', $exception->getMessage());
            redirectTo($redirect . '#authModal');
        }

        if ($userManager->usernameExists((string) $newUser->getUsername())) {
            throw new InvalidArgumentException('Ce nom d’utilisateur est déjà pris.');
        }
        if ($userManager->emailExists((string) $newUser->getEmail())) {
            throw new InvalidArgumentException('Cet e-mail est déjà utilisé.');
        }

        $userManager->register($newUser, $password);
        unset($_SESSION['form_data']['register']);
        setFlashMessage('success', 'Votre compte a bien été créé. Vous pouvez maintenant vous connecter.');
        redirectTo($redirect . '#authModal');
    }

    if ($action === 'login') {
        storeFormData('login', $_POST);
        $email = stringInput($_POST['email'] ?? null);
        $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
        $ipAddress = clientIpAddress();

        if ($loginAttemptManager->countRecent($email, $ipAddress, 15) >= 5) {
            setFlashMessage('error', 'Trop de tentatives. Merci de patienter 15 minutes.');
            redirectTo($redirect . '#authModal');
        }

        $authenticatedUser = $userManager->checkCredentials($email, $password);
        if ($authenticatedUser === null) {
            $loginAttemptManager->add($email, $ipAddress);
            setFlashMessage('error', 'L’adresse e-mail ou le mot de passe est incorrect.');
            redirectTo($redirect . '#authModal');
        }

        // La session est créée dans le contrôleur, pas dans la couche modèle.
        session_regenerate_id(true);
        $_SESSION['user_id'] = $authenticatedUser->getId();
        $_SESSION['username'] = $authenticatedUser->getUsername();
        $_SESSION['role'] = $authenticatedUser->getRole();
        $loginAttemptManager->clear($email);
        unset($_SESSION['form_data']['login']);
        setFlashMessage('success', 'Connexion réussie. Bienvenue ' . $authenticatedUser->getUsername() . ' !');
        redirectTo($redirect);
    }

    if ($action === 'logout') {
        requireLogin($redirect);

        // La déconnexion ne sollicite que la session courante.
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }
        session_destroy();
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
