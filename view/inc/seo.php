<?php
// path: view/inc/seo.php
// Référencement (SEO) et moteurs génératifs (GEO) : adresse canonique, aperçus de partage (Open Graph)
// et données structurées schema.org en JSON-LD. Inclus dans <head> par header.php.
// Variables lues : $page, $documentTitle, $documentDescription, $metaRobots, et sur une fiche $recipe, $ingredients, $steps, $categories.

$siteUrl = defined('RACINE_URL') ? rtrim(RACINE_URL, '/') . '/' : '/';
$absoluteUrl = static fn(string $path): string => $siteUrl . ltrim($path, '/');

// Adresse canonique : seulement les paramètres qui identifient la page (jamais les filtres ni la pagination)
$canonicalQuery = ['pg' => $page];
if ($page === 'recette' && isset($recipe)) {
    $canonicalQuery['slug'] = (string) $recipe->getSlug();
}
$canonicalUrl = $page === 'accueil' ? $siteUrl : $siteUrl . '?' . http_build_query($canonicalQuery);

$shareImage = 'assets/img/partage.jpg'; // 1200 x 630, format attendu par les réseaux sociaux (pas de SVG)
if ($page === 'recette' && isset($recipe)) {
    $shareImage = (string) $recipe->getMainImage();
    if (!is_file(RACINE_PATH . '/public/' . $shareImage)) {
        $shareImage = 'assets/img/recettes/' . $recipe->getSlug() . '/principale.webp';
    }
}

$jsonLd = [];

// Toute page : l'organisation (chocolaterie fictive de Liège, mêmes informations que la page Contact)
$organization = [
    '@type' => 'Organization',
    '@id' => $siteUrl . '#organisation',
    'name' => 'Maison Rosalie',
    'url' => $siteUrl,
    'logo' => $absoluteUrl('assets/img/logo/logo.svg'),
    'foundingDate' => '1987',
    'email' => 'contact@maisonrosalie.be',
    'telephone' => '+32 4 200 00 00',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Rue Léopold 12',
        'postalCode' => '4000',
        'addressLocality' => 'Liège',
        'addressCountry' => 'BE',
    ],
];

if ($page === 'accueil') {
    $jsonLd[] = $organization;
    $jsonLd[] = [
        '@type' => 'WebSite',
        'name' => 'Maison Rosalie',
        'url' => $siteUrl,
        'inLanguage' => 'fr-BE',
        'publisher' => ['@id' => $siteUrl . '#organisation'],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $siteUrl . '?pg=recettes&recherche={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

// Fiche recette : schéma Recipe complet (temps ISO 8601, ingrédients, étapes avec photo, note moyenne)
if ($page === 'recette' && isset($recipe)) {
    $recipeUrl = $canonicalUrl;
    $recipeData = [
        '@type' => 'Recipe',
        'name' => (string) $recipe->getTitle(),
        'description' => (string) $recipe->getDescription(),
        'image' => [$absoluteUrl($shareImage)],
        'url' => $recipeUrl,
        'author' => ['@type' => 'Organization', 'name' => 'Maison Rosalie'],
        'datePublished' => substr((string) $recipe->getCreatedAt(), 0, 10),
        'prepTime' => 'PT' . (int) $recipe->getPrepTimeMinutes() . 'M',
        'cookTime' => 'PT' . (int) $recipe->getCookTimeMinutes() . 'M',
        'totalTime' => 'PT' . (int) $recipe->getTotalTime() . 'M',
        'recipeYield' => (int) $recipe->getServings() . ' personnes',
        'recipeCuisine' => 'Belge',
        'inLanguage' => 'fr-BE',
    ];
    if (!empty($categories)) {
        $recipeData['recipeCategory'] = array_map(static fn($category): string => (string) $category->getTitle(), $categories);
    }
    if (!empty($ingredients)) {
        $recipeData['recipeIngredient'] = array_map(static function ($ingredient): string {
            $label = trim(implode(' ', array_filter([
                $ingredient->getFormattedQuantity(),
                (string) ($ingredient->getUnit() ?? ''),
                (string) ($ingredient->getIngredientName() ?? ''),
            ], static fn(string $part): bool => $part !== '')));
            $details = (string) ($ingredient->getDetails() ?? '');
            return $details !== '' ? $label . ' (' . $details . ')' : $label;
        }, $ingredients);
    }
    if (!empty($steps)) {
        $recipeData['recipeInstructions'] = array_map(static function ($step) use ($absoluteUrl): array {
            $howToStep = [
                '@type' => 'HowToStep',
                'position' => (int) $step->getStepNumber(),
                'name' => (string) $step->getTitle(),
                'text' => (string) $step->getInstructions(),
            ];
            if ((string) $step->getImage() !== '') {
                $howToStep['image'] = $absoluteUrl((string) $step->getImage());
            }
            return $howToStep;
        }, $steps);
    }
    if ($recipe->getAverageRating() !== null && (int) $recipe->getRatingCount() > 0) {
        $recipeData['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (float) $recipe->getAverageRating(),
            'ratingCount' => (int) $recipe->getRatingCount(),
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }
    $jsonLd[] = $recipeData;

    // Fil d'Ariane (même chemin que celui affiché en haut de la fiche)
    $jsonLd[] = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => $siteUrl],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Recettes', 'item' => $siteUrl . '?pg=recettes'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => (string) $recipe->getTitle(), 'item' => $recipeUrl],
        ],
    ];
}

if (in_array($page, ['contact', 'a-propos'], true)) {
    $jsonLd[] = $organization;
}
?>
    <?php if ($metaRobots === 'index, follow') { ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php } ?>
    <meta name="theme-color" content="#5a392b">
    <meta property="og:site_name" content="Maison Rosalie">
    <meta property="og:locale" content="fr_BE">
    <meta property="og:type" content="<?= $page === 'recette' ? 'article' : 'website' ?>">
    <meta property="og:title" content="<?= htmlspecialchars($documentTitle . ' | Maison Rosalie', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($documentDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($absoluteUrl($shareImage), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <?php if ($jsonLd !== [] && $metaRobots === 'index, follow') { ?>
    <script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $jsonLd], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?php } ?>