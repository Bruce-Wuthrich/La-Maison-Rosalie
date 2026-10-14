<?php
// path: controller/AuthController.php

declare(strict_types=1);

use model\Mailer;
use model\manager\EmailVerificationManager;
use model\manager\LoginAttemptManager;
use model\manager\UserManager;

require_once RACINE_PATH . '/controller/ControllerHelpers.php';

$redirect = '?pg=accueil';
$action = stringInput($_GET['action'] ?? null);
// Après une erreur d'inscription ou de connexion, le formulaire classique rouvre la fenêtre modale.
$errorRedirect = $redirect . (in_array($action, ['register', 'login'], true) ? '#authModal' : '');

// Toutes les actions d'authentification viennent d'un formulaire protégé.
requirePostMethod($redirect);
validateCsrfToken($_POST, $redirect);

$userManager = new UserManager($db);
$loginAttemptManager = new LoginAttemptManager($db);

try {
    if ($action === 'register') {
        // La saisie n'est gardée que pour réafficher un formulaire classique après une erreur.
        if (!isJsonRequest()) {
            storeFormData('register', $_POST);
        }

        $registeredUser = $userManager->register($_POST);
        if ($registeredUser === false) {
            throw new RuntimeException('La création du compte a échoué.');
        }

        unset($_SESSION['form_data']['register']);

        // Confirmation d'adresse : lien envoyé par e-mail. Sans SMTP configuré, le compte est confirmé tout de suite.
        $emailVerificationManager = new EmailVerificationManager($db);
        if (Mailer::isConfigured()) {
            $token = $emailVerificationManager->createToken((int) $registeredUser->getId());
            Mailer::sendRegistrationConfirmation($registeredUser, verificationUrl($token));
            $registerMessage = 'Votre compte a bien été créé. Un e-mail vient de vous être envoyé : cliquez sur le lien pour confirmer votre adresse, puis connectez-vous.';
        } else {
            $emailVerificationManager->markVerified((int) $registeredUser->getId());
            $registerMessage = 'Votre compte a bien été créé. Vous pouvez maintenant vous connecter.';
        }

        respond(
            true,
            $registerMessage,
            $redirect . '#authModal',
            201,
            ['username' => $registeredUser->getUsername()]
        );
    }

    if ($action === 'login') {
        if (!isJsonRequest()) {
            storeFormData('login', $_POST);
        }

        $email = stringInput($_POST['email'] ?? null);
        $loginMode = stringInput($_POST['login_mode'] ?? 'user');
        $ipAddress = clientIpAddress();

        if (!in_array($loginMode, ['user', 'admin'], true)) {
            $loginMode = 'user';
        }

        if ($loginAttemptManager->countRecent($email, $ipAddress, 15) >= 5) {
            throw new DomainException('Trop de tentatives. Merci de patienter 15 minutes.', 429);
        }

        if (!$userManager->connect($_POST)) {
            // Bon mot de passe mais adresse pas confirmée : un nouveau lien est envoyé
            $unverifiedUser = $userManager->getUnverifiedUser();
            if ($unverifiedUser !== null) {
                $token = (new EmailVerificationManager($db))->createToken((int) $unverifiedUser->getId());
                Mailer::sendRegistrationConfirmation($unverifiedUser, verificationUrl($token));
                throw new DomainException('Confirmez d’abord votre adresse e-mail : un nouveau lien de confirmation vient de vous être envoyé.', 403);
            }

            $loginAttemptManager->add($email, $ipAddress);
            throw new DomainException('L’adresse e-mail ou le mot de passe est incorrect.', 401);
        }

        if ($loginMode === 'admin' && ($_SESSION['role'] ?? '') !== 'admin') {
            $userManager->disconnect();
            session_start();
            session_regenerate_id(true);
            throw new DomainException('Ce compte ne possède pas les droits administrateur.', 403);
        }

        $loginAttemptManager->clear($email);
        unset($_SESSION['form_data']['login']);

        // Le JavaScript reçoit l'utilisateur et le jeton CSRF pour mettre la page à jour sans la recharger (FE-60, FE-53).
        respond(
            true,
            'Connexion réussie. Bienvenue ' . $_SESSION['username'] . ' !',
            ($_SESSION['role'] ?? '') === 'admin' ? '?pg=admin' : $redirect,
            200,
            [
                'user' => [
                    'username' => $_SESSION['username'],
                    'role' => $_SESSION['role'],
                ],
                'csrfToken' => csrfToken(),
            ]
        );
    }

    if ($action === 'logout') {
        requireLogin($redirect);

        $userManager->disconnect();
        session_start();
        session_regenerate_id(true);

        // Nouvelle session, donc nouveau jeton CSRF pour les formulaires encore affichés.
        respond(true, 'Vous êtes maintenant déconnecté.', $redirect, 200, ['csrfToken' => csrfToken()]);
    }

    respond(false, 'Action d’authentification introuvable.', $redirect, 404);
} catch (InvalidArgumentException|DomainException $exception) {
    respond(false, $exception->getMessage(), $errorRedirect, errorStatus($exception));
} catch (Throwable $exception) {
    error_log('Erreur dans AuthController : ' . $exception->getMessage());
    respond(false, 'Le service des comptes est momentanément indisponible.', $errorRedirect, 503);
}