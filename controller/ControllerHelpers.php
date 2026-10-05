<?php
// path: controller/ControllerHelpers.php

declare(strict_types=1);

// Redirige après un POST pour éviter un nouvel envoi au rafraîchissement.
function redirectTo(string $location): never
{
    header('Location: ' . $location);
    exit;
}

// Place un message temporaire dans la session pour la page suivante.
function setFlashMessage(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

// Récupère le message temporaire puis le retire de la session.
function pullFlashMessage(): ?array
{
    $flash = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);

    return is_array($flash) ? $flash : null;
}

// Conserve les champs d'un formulaire, sauf les données sensibles.
function storeFormData(string $formName, array $data): void
{
    unset($data['password'], $data['password_confirm'], $data['csrf_token']);

    // Un formulaire HTML classique envoie des chaînes ou des tableaux.
    // Les tableaux sont refusés pour éviter leur affichage forcé dans les vues.
    $_SESSION['form_data'][$formName] = array_filter($data, 'is_string');
}

// Récupère une ancienne saisie puis la retire de la session.
function pullFormData(string $formName): array
{
    $data = $_SESSION['form_data'][$formName] ?? [];
    unset($_SESSION['form_data'][$formName]);

    return is_array($data) ? $data : [];
}

// Crée une seule fois le jeton qui protège les formulaires.
function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Vérifie le jeton du formulaire avant une action sensible.
function validateCsrfToken(array $data, string $redirect): void
{
    $receivedToken = $data['csrf_token'] ?? '';

    if (!is_string($receivedToken) || !hash_equals(csrfToken(), $receivedToken)) {
        setFlashMessage('error', 'La session du formulaire a expiré. Rechargez la page puis réessayez.');
        redirectTo($redirect);
    }
}

// Bloque l'action si aucun membre n'est connecté.
function requireLogin(string $redirect): array
{
    if (!isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'])) {
        setFlashMessage('error', 'Vous devez être connecté pour effectuer cette action.');
        redirectTo($redirect);
    }

    return [
        'id' => (int) $_SESSION['user_id'],
        'username' => (string) $_SESSION['username'],
        'role' => (string) $_SESSION['role'],
    ];
}

// Accepte uniquement les données envoyées par un formulaire POST.
function requirePostMethod(string $redirect): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        setFlashMessage('error', 'Cette action doit être envoyée depuis un formulaire.');
        redirectTo($redirect);
    }
}

// Transforme une valeur en identifiant positif ou renvoie null.
function positiveInteger(mixed $value): ?int
{
    if (is_int($value)) {
        return $value > 0 ? $value : null;
    }

    if (!is_string($value) || $value === '' || !ctype_digit($value)) {
        return null;
    }

    // FILTER_VALIDATE_INT refuse aussi les nombres supérieurs à PHP_INT_MAX.
    $integer = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    return $integer === false ? null : $integer;
}

// Récupère une chaîne simple sans convertir un tableau reçu dans l'URL.
function stringInput(mixed $value): string
{
    return is_string($value) ? trim($value) : '';
}

// Récupère une adresse IP valide sans faire confiance aux en-têtes proxy.
function clientIpAddress(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    return filter_var($ip, FILTER_VALIDATE_IP) === false ? '0.0.0.0' : $ip;
}

// Construit une redirection sûre vers la recette demandée.
function recipeRedirect(string $slug): string
{
    return preg_match('/^[a-z0-9-]+$/', $slug) === 1
        ? '?pg=recette&slug=' . rawurlencode($slug)
        : '?pg=recettes';
}
