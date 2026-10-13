<?php

declare(strict_types=1);

use model\manager\CommentManager;
use model\manager\RatingManager;
use model\manager\UserManager;

// Lecture publique uniquement. Les écritures restent dans CommentController.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    jsonResponse(405, false, 'Cette lecture nécessite une requête GET.');
}

header('Cache-Control: no-store');
$slug = stringInput($_GET['slug'] ?? null);
$sort = stringInput($_GET['sort'] ?? null);
$sort = in_array($sort, ['date', 'mieux', 'pire'], true) ? $sort : 'date';
$requestedPage = positiveInteger($_GET['page'] ?? null) ?? 1;

if (preg_match('/^[a-z0-9-]+$/', $slug) !== 1) {
    jsonResponse(400, false, 'La recette demandée est invalide.');
}

try {
    $member = isset($_SESSION['user_id'])
        ? (new UserManager($db))->getById((int) $_SESSION['user_id'])
        : null;
    $recipe = $recipeManager->getBySlug($slug, $member?->getId());
    if ($recipe === null) {
        jsonResponse(404, false, 'Cette recette est introuvable.');
    }

    $manager = new CommentManager($db);
    $total = $manager->countByRecipeId($recipe->getId());
    $page = min($requestedPage, max(1, (int) ceil($total / 10)));
    $comments = $manager->getByRecipeId($recipe->getId(), 10, ($page - 1) * 10, $sort);
    $items = [];
    foreach ($comments as $comment) {
        $item = $comment->toArray();
        $item['canDelete'] = $member !== null
            && ($comment->getAuthorId() === $member->getId() || $member->isAdmin());
        $items[] = $item;
    }

    jsonResponse(200, true, 'Commentaires chargés.', [
        'comments' => $items,
        'total' => $total,
        'page' => $page,
        'hasNext' => $page * 10 < $total,
        'user' => $member === null ? null : [
            'id' => $member->getId(),
            'username' => $member->getUsername(),
            'email' => $member->getEmail(),
        ],
        'csrfToken' => csrfToken(),
        'userRating' => $recipe->getUserRating(),
        'canRate' => $member !== null && $member->getId() !== $recipe->getAuthorId(),
        'rating' => (new RatingManager($db))->getStats($recipe->getId()),
    ]);
} catch (Throwable $exception) {
    error_log('Lecture des commentaires : ' . $exception->getMessage());
    jsonResponse(503, false, 'Les commentaires sont momentanément indisponibles.');
}
