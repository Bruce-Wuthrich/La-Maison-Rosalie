<?php
// path: controller/AdminRecipeController.php
// Bonus panneau d'administration, onglet Recettes : ajouter, modifier ou supprimer une recette.
// Inclus uniquement par AdminController, après requireAdmin() : $connectedUser est l'administrateur connecté.
// GET  ?pg=admin&onglet=recettes&recette=nouvelle            formulaire vide
// GET  ?pg=admin&onglet=recettes&recette={slug}              formulaire prérempli
// POST ?pg=admin&onglet=recettes&action=save[&recette={slug}] enregistre (JSON si le JS le demande)
// POST ?pg=admin&onglet=recettes&action=delete&recette={slug} supprime

declare(strict_types=1);

use model\manager\CategoryManager;
use model\manager\RecipeAdminManager;
use model\manager\RecipeIngredientManager;
use model\manager\StepManager;

$recipeAdminManager = new RecipeAdminManager($db);
$action = stringInput($_GET['action'] ?? null);
$recipeParam = stringInput($_GET['recette'] ?? null);
$recipesTabUrl = '?pg=admin&onglet=recettes';

// Aucun formulaire ni action demandé : l'onglet affiche simplement la liste.
if ($action === '' && $recipeParam === '') {
    return;
}

// Recette modifiée (null pour une nouvelle) et images déjà en base, seules réutilisables par le formulaire.
$recipe = null;
$recipeSteps = [];
$recipeIngredients = [];
$recipeCategoryIds = [];
$existingImages = [];

try {
    $allCategories = (new CategoryManager($db))->getAll();

    if ($recipeParam !== '' && $recipeParam !== 'nouvelle') {
        $recipe = preg_match('/^[a-z0-9-]+$/', $recipeParam) === 1 ? $recipeManager->getBySlug($recipeParam) : null;

        if ($recipe === null) {
            respond(false, 'Cette recette est introuvable.', $recipesTabUrl, 404);
        }

        $recipeSteps = (new StepManager($db))->getByRecipeId($recipe->getId());
        $recipeIngredients = (new RecipeIngredientManager($db))->getByRecipeId($recipe->getId());
        $recipeCategoryIds = array_map(static fn($category): int => (int) $category->getId(), (new CategoryManager($db))->getByRecipeId($recipe->getId()));

        $existingImages[] = (string) $recipe->getMainImage();
        foreach ($recipeSteps as $step) {
            $existingImages[] = (string) $step->getImage();
        }
    }
} catch (Throwable $exception) {
    error_log('Chargement de la recette admin impossible : ' . $exception->getMessage());
    respond(false, 'La gestion des recettes est momentanément indisponible.', $recipesTabUrl, 503);
}

$allowedCategoryIds = array_map(static fn($category): int => (int) $category->getId(), $allCategories);
$recipeFormUrl = $recipesTabUrl . '&recette=' . ($recipe !== null ? rawurlencode((string) $recipe->getSlug()) : 'nouvelle');
$formError = null;

// Saisie renvoyée telle quelle quand le formulaire classique (sans JS) contient une erreur.
$formFromInput = static function (array $input) use ($existingImages): array {
    $text = static fn(mixed $value): string => is_string($value) ? trim($value) : '';
    $rows = static fn(mixed $value): array => is_array($value) ? array_values(array_filter($value, 'is_array')) : [];

    return [
        'title' => $text($input['title'] ?? null),
        'description' => $text($input['description'] ?? null),
        'prep_time' => $text($input['prep_time'] ?? null),
        'cook_time' => $text($input['cook_time'] ?? null),
        'servings' => $text($input['servings'] ?? null),
        'difficulty' => $text($input['difficulty'] ?? null),
        'categories' => array_map('intval', array_filter(
            is_array($input['categories'] ?? null) ? $input['categories'] : [],
            static fn($value): bool => is_string($value) && ctype_digit($value)
        )),
        'ingredients' => array_map(static fn(array $row): array => [
            'name' => $text($row['name'] ?? null),
            'quantity' => $text($row['quantity'] ?? null),
            'unit' => $text($row['unit'] ?? null),
            'details' => $text($row['details'] ?? null),
        ], $rows($input['ingredients'] ?? null)),
        'steps' => array_map(static fn(array $row): array => [
            'title' => $text($row['title'] ?? null),
            'instructions' => $text($row['instructions'] ?? null),
            'current_image' => in_array($text($row['current_image'] ?? null), $existingImages, true) ? $text($row['current_image']) : '',
        ], $rows($input['steps'] ?? null)),
    ];
};

if ($action === 'save') {
    requirePostMethod($recipeFormUrl);

    // Envoi plus lourd que la limite de PHP (post_max_size) : $_POST arrive vide, on l'explique au lieu d'un faux "session expirée".
    if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        respond(false, 'L’envoi est trop lourd : chaque image doit peser 300 Ko maximum.', $recipeFormUrl, 400);
    }
    validateCsrfToken($_POST, $recipeFormUrl);

    // Images enregistrées pendant cet envoi : effacées si la suite échoue.
    $newImages = [];

    try {
        $cleanRecipe = $recipeAdminManager->validate($_POST, $allowedCategoryIds, $existingImages);

        // Photo principale et une photo par étape (FE-33) : nouvelle ou déjà en place. Tout est vérifié avant d'écrire un fichier.
        $mainUpload = uploadedFile('main_image');
        if ($mainUpload === null && $recipe === null) {
            throw new InvalidArgumentException('La photo principale est obligatoire.');
        }
        foreach ($cleanRecipe['steps'] as $position => $step) {
            if ($step['current_image'] === null && uploadedFile('step_images', $step['index']) === null) {
                throw new InvalidArgumentException('Ajoutez une photo pour l’étape ' . ($position + 1) . '.');
            }
        }

        $recipeSlug = $recipe !== null ? (string) $recipe->getSlug() : $recipeAdminManager->uniqueSlug($cleanRecipe['title']);
        $imageDirectory = 'uploads/recettes/' . $recipeSlug;

        $mainImage = (string) $recipe?->getMainImage();
        if ($mainUpload !== null) {
            $mainImage = $newImages[] = storeUploadedImage($mainUpload, $imageDirectory, 'principale');
        }

        $stepImages = [];
        foreach ($cleanRecipe['steps'] as $position => $step) {
            $stepUpload = uploadedFile('step_images', $step['index']);
            $stepImages[] = $stepUpload === null
                ? $step['current_image']
                : ($newImages[] = storeUploadedImage($stepUpload, $imageDirectory, 'etape-' . ($position + 1)));
        }

        $recipeAdminManager->save($recipe?->getId(), $recipeSlug, $cleanRecipe, $mainImage, $stepImages, (int) $connectedUser->getId());

        // Anciennes images remplacées : supprimées seulement si elles venaient de public/uploads.
        deleteUploadedImages(array_diff($existingImages, [$mainImage, ...$stepImages]));

        $recipeUrl = '?pg=recette&slug=' . rawurlencode($recipeSlug);
        respond(
            true,
            $recipe !== null ? 'La recette a bien été modifiée.' : 'La recette a bien été ajoutée.',
            $recipeUrl,
            $recipe !== null ? 200 : 201,
            ['url' => $recipeUrl, 'slug' => $recipeSlug]
        );
    } catch (InvalidArgumentException $exception) {
        deleteUploadedImages($newImages);

        if (isJsonRequest()) {
            jsonResponse(400, false, $exception->getMessage());
        }

        // Sans JS : l'onglet est réaffiché avec la saisie et l'erreur (les fichiers sont à choisir à nouveau).
        http_response_code(400);
        $formError = $exception->getMessage();
        $formData = $formFromInput($_POST);
    } catch (Throwable $exception) {
        deleteUploadedImages($newImages);
        error_log('Erreur dans AdminRecipeController : ' . $exception->getMessage());
        respond(false, 'L’enregistrement de la recette est momentanément indisponible.', $recipeFormUrl, 503);
    }
} elseif ($action === 'delete') {
    requirePostMethod($recipesTabUrl);
    validateCsrfToken($_POST, $recipesTabUrl);

    // L'identifiant envoyé doit correspondre au slug de l'adresse : pas de suppression d'une autre recette par erreur.
    if ($recipe === null || positiveInteger($_POST['recipe_id'] ?? null) !== $recipe->getId()) {
        respond(false, 'Cette recette est introuvable.', $recipesTabUrl, 404);
    }

    try {
        if (!$recipeAdminManager->delete((int) $recipe->getId())) {
            throw new RuntimeException('La recette n’a pas été supprimée.');
        }
        deleteUploadedImages($existingImages);

        // dossier d'images de la recette retiré s'il est vide
        $imageDirectory = RACINE_PATH . '/public/uploads/recettes/' . $recipe->getSlug();
        if (is_dir($imageDirectory) && count(scandir($imageDirectory)) === 2) {
            rmdir($imageDirectory);
        }
        respond(true, 'La recette a bien été supprimée.', $recipesTabUrl, 200, ['recipeId' => $recipe->getId()]);
    } catch (Throwable $exception) {
        error_log('Suppression de recette impossible : ' . $exception->getMessage());
        respond(false, 'La suppression de la recette est momentanément indisponible.', $recipesTabUrl, 503);
    }
} elseif ($action !== '') {
    respond(false, 'Action introuvable.', $recipesTabUrl, 404);
}

// Formulaire prérempli : saisie renvoyée après erreur, recette en base, ou formulaire vide.
if (!isset($formData)) {
    $formData = $recipe === null
        ? [
            'title' => '', 'description' => '', 'prep_time' => '', 'cook_time' => '', 'servings' => '', 'difficulty' => '',
            'categories' => [],
            'ingredients' => array_fill(0, 3, ['name' => '', 'quantity' => '', 'unit' => '', 'details' => '']),
            'steps' => array_fill(0, RecipeAdminManager::MIN_STEPS, ['title' => '', 'instructions' => '', 'current_image' => '']),
        ]
        : [
            'title' => (string) $recipe->getTitle(),
            'description' => (string) $recipe->getDescription(),
            'prep_time' => (string) $recipe->getPrepTimeMinutes(),
            'cook_time' => (string) $recipe->getCookTimeMinutes(),
            'servings' => (string) $recipe->getServings(),
            'difficulty' => (string) $recipe->getDifficulty(),
            'categories' => $recipeCategoryIds,
            'ingredients' => array_map(static fn($ingredient): array => [
                'name' => (string) $ingredient->getIngredientName(),
                'quantity' => $ingredient->getFormattedQuantity(),
                'unit' => (string) $ingredient->getUnit(),
                'details' => (string) $ingredient->getDetails(),
            ], $recipeIngredients),
            'steps' => array_map(static fn($step): array => [
                'title' => (string) $step->getTitle(),
                'instructions' => (string) $step->getInstructions(),
                'current_image' => (string) $step->getImage(),
            ], $recipeSteps),
        ];
}

// Tout ce dont le formulaire de l'onglet a besoin, lu par adminRecipeForm.php.
$recipeForm = [
    'recipe' => $recipe,
    'data' => $formData,
    'error' => $formError,
    'categories' => $allCategories,
    'action' => $recipeFormUrl . '&action=save',
];