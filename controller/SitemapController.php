<?php
// path: controller/SitemapController.php
// GET ?pg=sitemap : plan du site XML pour les moteurs de recherche, généré depuis la base
// (une recette ajoutée en base y apparaît sans modifier le code). Déclaré dans public/robots.txt.

declare(strict_types=1);

$siteUrl = defined('RACINE_URL') ? rtrim(RACINE_URL, '/') . '/' : '/';
$urls = [
    $siteUrl,
    $siteUrl . '?pg=recettes',
    $siteUrl . '?pg=a-propos',
    $siteUrl . '?pg=contact',
    $siteUrl . '?pg=mentions-legales',
];
// $menuRecipes : liste légère déjà chargée par index.php (menu déroulant)
foreach ($menuRecipes as $menuRecipe) {
    $urls[] = $siteUrl . '?' . http_build_query(['pg' => 'recette', 'slug' => (string) $menuRecipe->getSlug()]);
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as $url) {
    echo '  <url><loc>', htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8'), '</loc></url>', "\n";
}
echo '</urlset>', "\n";