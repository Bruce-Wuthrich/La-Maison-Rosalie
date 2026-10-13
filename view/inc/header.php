<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $iconVersion = filemtime(RACINE_PATH . '/public/assets/img/interface/home.svg');
    // Donne un titre précis à chaque page et à chaque recette.
    $pageTitles = [
        'accueil' => 'Accueil',
        'recettes' => 'Nos recettes',
        'a-propos' => 'À propos',
        'contact' => 'Contact',
        'mentions-legales' => 'Mentions légales',
        'admin' => 'Administration',
    ];
    $pageDescriptions = [
        'accueil' => 'Découvrez Maison Rosalie, ses recettes au chocolat et son savoir-faire artisanal belge.',
        'recettes' => 'Explorez les recettes chocolatées de Maison Rosalie et filtrez-les par catégorie, difficulté ou temps de préparation.',
        'a-propos' => 'Découvrez l’histoire, l’équipe et les valeurs de Maison Rosalie, chocolaterie belge fondée à Liège.',
        'contact' => 'Contactez Maison Rosalie pour poser une question ou obtenir des informations sur ses recettes au chocolat.',
        'mentions-legales' => 'Consultez les mentions légales, les informations sur les données personnelles et les conditions d’utilisation de Maison Rosalie.',
        'admin' => 'Espace d’administration de Maison Rosalie.',
    ];
    $documentTitle = $pageTitles[$page] ?? 'Page introuvable';
    $documentDescription = $pageDescriptions[$page]
        ?? 'La page demandée est introuvable. Retrouvez les recettes et les actualités de Maison Rosalie.';

    if ($page === 'recette' && isset($recipe)) {
        $documentTitle = (string) $recipe->getTitle();
        $documentDescription = (string) $recipe->getDescription();
    }

    // La vue peut préciser un style différent de la route (par exemple une recette introuvable).
    $stylesheetPage = $stylePage ?? $page;
    $pageStylesheets = match ($stylesheetPage) {
        'accueil' => ['home.css'],
        'recettes' => ['recipes.css'],
        'recette' => ['recipe.css'],
        'a-propos' => ['about.css'],
        'contact' => ['contact.css'],
        'mentions-legales' => ['legal.css'],
        'admin' => ['recipes.css', 'recipe.css', 'admin.css'],
        'error' => ['home.css', 'error.css'],
        default => ['home.css', 'error.css'],
    };
    $stylesheets = array_merge(['base.css'], $pageStylesheets);
    $metaRobots = in_array($stylesheetPage, ['admin', 'error'], true)
        ? 'noindex, nofollow'
        : 'index, follow';
    ?>
    <title><?= htmlspecialchars($documentTitle . ' | Maison Rosalie', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($documentDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="<?= $metaRobots ?>">
    <link rel="icon" href="assets/img/logo/picto.svg?v=<?= (int) $iconVersion ?>" type="image/svg+xml">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <?php foreach ($stylesheets as $stylesheet): ?>
        <link rel="stylesheet" href="css/<?= $stylesheet ?>?v=<?= filemtime(RACINE_PATH . '/public/css/' . $stylesheet) ?>">
    <?php endforeach; ?>
</head>

<body id="top" data-icon-version="<?= (int) $iconVersion ?>">
