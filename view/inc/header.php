<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
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
    ?>
    <title><?= htmlspecialchars($documentTitle . ' | Maison Rosalie', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" href="assets/img/logo/picto.svg" type="image/svg+xml">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body id="top">