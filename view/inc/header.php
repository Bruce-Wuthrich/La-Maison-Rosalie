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
        'admin' => 'Administration',
    ];
    $documentTitle = $pageTitles[$page] ?? 'Page introuvable';

    if ($page === 'recette' && isset($recipe)) {
        $documentTitle = (string) $recipe->getTitle();
    }

    // La vue peut préciser un style différent de la route (par exemple une recette introuvable).
    $stylesheetPage = $stylePage ?? $page;
    $pageStylesheets = match ($stylesheetPage) {
        'accueil' => ['home.css'],
        'recettes' => ['recipes.css'],
        'recette' => ['recipe.css'],
        'a-propos' => ['about.css'],
        'contact' => ['contact.css'],
        'admin' => ['recipes.css', 'recipe.css', 'admin.css'],
        'error' => ['home.css', 'error.css'],
        default => ['home.css', 'error.css'],
    };
    $stylesheets = array_merge(['base.css'], $pageStylesheets);
    ?>
    <title><?= htmlspecialchars($documentTitle . ' | Maison Rosalie', ENT_QUOTES, 'UTF-8') ?></title>
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
