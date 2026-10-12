<?php
// path: controller/ControllerHelpers.php

declare(strict_types=1);

// Redirige après un POST pour éviter un nouvel envoi au rafraîchissement.
function redirectTo(string $location): never
{
    header('Location: ' . $location);
    exit;
}

// True quand Js demande reponse Json
function isJsonRequest(): bool{
    return str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
}

// Réponse Json commune puis arrete le script
function jsonResponse(int $status, bool $success, string $message, ?array $data = null): never{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        ['success' => $success, 'message' => $message, 'data' => $data],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

// Json pour Js message flash + redirection formulaire classique
function respond(bool $success, string $message, string $redirect, int $status = 200, ?array $data = null): never{
    if (isJsonRequest()){
        jsonResponse($status, $success, $message, $success ? $data : null);
    }

    setFlashMessage($success ? 'success' : 'error', $message);
    redirectTo($redirect);
}

// Code HTTP d'une erreur 
function errorStatus(Throwable $exception): int
{
    $code = $exception->getCode();

    return in_array($code, [403, 404, 429], true) ? $code : 400;
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
        respond(false, 'La session du formulaire a expiré. Rechargez la page puis réessayez.', $redirect, 400);
    }
}

// Bloque l'action si aucun membre n'est connecté.
function requireLogin(string $redirect): array
{
    if (!isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'])) {
        respond(false, 'Vous devez être connecté pour effectuer cette action.', $redirect, 401);
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
        header('Allow: POST');
        respond(false, 'Cette action doit être envoyée depuis un formulaire.', $redirect, 405);
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
