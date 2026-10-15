<?php require RACINE_PATH . '/view/inc/header.php'; ?>
<?php require RACINE_PATH . '/view/inc/navbar.php'; ?>
<?php
// $recipeFilters est préparé dans RecipeController à partir de la BDD.

// Utilise le chemin de la BDD s'il existe, sinon l'image locale de la recette.
$recipeImagePath = static function ($recipe): string {
    $image = (string) $recipe->getMainImage();

    if (!is_file(RACINE_PATH . '/public/' . $image)) {
        $image = 'assets/img/recettes/' . $recipe->getSlug() . '/principale.webp';
    }

    return $image;
};
?>

<main class="recipes-page">

    <section class="recipes-header">
        <div class="container">
            <p class="recipes-eyebrow">Maison Rosalie</p>

            <h1>Nos recettes</h1>

            <p class="recipes-introduction">
                Découvrez nos créations chocolatées et trouvez la recette qui vous fera plaisir.
            </p>

            <?php if ($searchTerm !== '') { ?>
                <p class="recipes-search-summary">
                    <?php echo count($recipes); ?> résultat<?php echo count($recipes) > 1 ? 's' : ''; ?> pour
                    « <?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?> »
                </p>
            <?php } ?>
        </div>
    </section>

    <div class="recipes-catalog">
        <div class="container">
        <?php
            // filtre qui garde les autres critères actifs (catégorie, recherche, difficulté, temps).
            $filterUrl = static function (array $changes) use ($categorySlug, $searchTerm, $difficulty, $maxTotalTime, $onlyFavorites): string {
                $params = array_merge([
                    'pg' => 'recettes',
                    'categorie' => $categorySlug,
                    'recherche' => $searchTerm,
                    'difficulte' => $difficulty,
                    'temps' => $maxTotalTime,
                    'favoris' => $onlyFavorites ? '1' : null,
                ], $changes);
                $params = array_filter($params, static fn($value): bool => $value !== '' && $value !== null);

                return '?' . htmlspecialchars(http_build_query($params), ENT_QUOTES, 'UTF-8');
            };
            ?>

            <nav class="recipe-filters" aria-label="Filtrer les recettes par catégorie">
                <?php if ($favoriteUserId !== null) { ?>
                    <!-- Bonus favoris : recettes enregistrées par le membre connecté -->
                    <a class="recipe-filter<?php echo $onlyFavorites ? ' is-active' : ''; ?>"
                        href="<?php echo $filterUrl(['favoris' => $onlyFavorites ? null : '1']); ?>"
                        <?php echo $onlyFavorites ? 'aria-current="page"' : ''; ?>>
                        Mes favoris
                    </a>
                <?php } ?>
                <?php foreach ($recipeFilters as $filterSlug => $filterLabel) { ?>
                    <?php $isActive = $categorySlug === $filterSlug; ?>
                    <a class="recipe-filter<?php echo $isActive ? ' is-active' : ''; ?>"
                        href="<?php echo $filterUrl(['categorie' => $filterSlug]); ?>"
                        <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                        <?php echo htmlspecialchars($filterLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php } ?>
            </nav>

            <form class="recipe-extra-filters" method="get" aria-label="Filtrer par difficulté et par temps">
                <input type="hidden" name="pg" value="recettes">
                <?php if ($categorySlug !== '') { ?>
                    <input type="hidden" name="categorie" value="<?php echo htmlspecialchars($categorySlug, ENT_QUOTES, 'UTF-8'); ?>">
                <?php } ?>
                <?php if ($searchTerm !== '') { ?>
                    <input type="hidden" name="recherche" value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
                <?php } ?>
                <?php if ($onlyFavorites) { ?>
                    <input type="hidden" name="favoris" value="1">
                <?php } ?>

                <label>
                    <span>Difficulté</span>
                    <select name="difficulte" data-auto-submit>
                        <option value="">Toutes</option>
                        <?php foreach ($difficultyOptions as $value => $label) { ?>
                            <option value="<?php echo $value; ?>"<?php echo $difficulty === $value ? ' selected' : ''; ?>><?php echo $label; ?></option>
                        <?php } ?>
                    </select>
                </label>

                <label>
                    <span>Temps total</span>
                    <select name="temps" data-auto-submit>
                        <option value="">Peu importe</option>
                        <?php foreach ($timeOptions as $value => $label) { ?>
                            <option value="<?php echo $value; ?>"<?php echo $maxTotalTime === $value ? ' selected' : ''; ?>><?php echo $label; ?></option>
                        <?php } ?>
                    </select>
                </label>

                <button class="recipe-filter" type="submit">Filtrer</button>
            </form>

            <?php if ($error !== null) { ?>
                <p role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } elseif (empty($recipes)) { ?>
                <p>
                    <?php echo $onlyFavorites
                        ? 'Vous n’avez pas encore de recette favorite : touchez le cœur d’une fiche recette pour l’enregistrer.'
                        : ($searchTerm === ''
                            ? 'Aucune recette n’est disponible pour le moment.'
                            : 'Aucune recette ne correspond à votre recherche.'); ?>
                </p>
            <?php } else { ?>
                <p class="visually-hidden" data-live-search-status aria-live="polite"></p>
                <div class="recipes-grid">
                    <?php foreach ($recipes as $recipe) { ?>
                        <?php require RACINE_PATH . '/view/inc/recipeCard.php'; ?>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</main>
<?php require RACINE_PATH . '/view/inc/footer.php'; ?>