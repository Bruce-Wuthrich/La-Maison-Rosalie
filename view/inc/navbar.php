<?php
$isHomeNavbar = $page === 'accueil' || ($navbarHome ?? false);
$isRecipesPage = in_array($page, ['recettes', 'recette'], true);
$activeRecipeSlug = $page === 'recette' ? stringInput($_GET['slug'] ?? null) : '';
?>
<nav class="navbar navbar-home">
    
    <div class="container">

        <div class="navbar-actions navbar-actions-left">
            <a class="navbar-icon-link<?= $page === 'accueil' ? ' is-active' : '' ?>" href="?pg=accueil"
                aria-label="Accueil" <?= $page === 'accueil' ? 'aria-current="page"' : '' ?>>
                <img class="navbar-icon" src="assets/img/interface/home.svg" alt="Accueil">
            </a>

            <?php $navbarSearch = stringInput($_GET['recherche'] ?? null); ?>
            <form class="navbar-search<?= $navbarSearch !== '' ? ' is-open' : '' ?>" action="" method="get"
                role="search" data-navbar-search>
                <input type="hidden" name="pg" value="recettes">
                <button type="button" class="navbar-search-toggle" data-search-toggle
                    aria-label="Ouvrir la recherche" aria-expanded="<?= $navbarSearch !== '' ? 'true' : 'false' ?>"
                    aria-controls="navbarSearchInput">
                    <img class="navbar-icon" src="assets/img/interface/search.svg" alt="">
                </button>
                <input id="navbarSearchInput" class="navbar-search-input" type="search" name="recherche"
                    value="<?= htmlspecialchars($navbarSearch, ENT_QUOTES, 'UTF-8') ?>" placeholder="Chercher..."
                    maxlength="80" autocomplete="off" aria-label="Chercher une recette">
            </form>
        </div>

        <?php if (!$isHomeNavbar): ?>
            <a href="?pg=accueil" class="navbar-brand">
                <img src="assets/img/logo/picto.svg" alt="Maison Rosalie" class="navbar-logo">
            </a>
        <?php endif; ?>

        <div class="navbar-actions navbar-actions-right">

            <?php // Les deux états sont toujours présents : le JavaScript bascule de l'un à l'autre sans recharger la page ?>
            <span class="navbar-user" data-auth-member<?= $currentUser === null ? ' hidden' : '' ?>>
                <img class="navbar-icon" src="assets/img/interface/account.svg" alt="" aria-hidden="true">
                <span class="navbar-user-name" data-auth-username><?= htmlspecialchars($currentUser['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            </span>

            <form class="navbar-logout-form" action="?pg=compte&amp;action=logout" method="post" data-auth-logout data-auth-member<?= $currentUser === null ? ' hidden' : '' ?>>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="navbar-logout-button">Déconnexion</button>
            </form>

            <button type="button" class="navbar-icon-button navbar-account-button" data-auth-open data-auth-guest<?= $currentUser !== null ? ' hidden' : '' ?>
                aria-label="Se connecter" aria-haspopup="dialog" aria-controls="authModal">
                <img class="navbar-icon" src="assets/img/interface/account.svg" alt="Compte">
                <span class="navbar-icon-tooltip" aria-hidden="true">Login</span>
            </button>

            <button class="navbar-toggler navbar-icon-button" type="button" data-bs-toggle="collapse"
                data-bs-target="#navigationPrincipale" aria-controls="navigationPrincipale" aria-expanded="false"
                aria-label="Ouvrir le menu">
                <img class="navbar-icon" src="assets/img/interface/menu.svg" alt="Ouvrir le menu">
            </button>

        </div>
        <div class="collapse navbar-collapse" id="navigationPrincipale">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a href="?pg=recettes" class="nav-link dropdown-toggle<?= $isRecipesPage ? ' is-active' : '' ?>"
                        data-bs-toggle="dropdown" aria-expanded="false"
                        <?= $isRecipesPage ? 'aria-current="page"' : '' ?>><span>Recettes</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="?pg=recettes" class="dropdown-item<?= $page === 'recettes' ? ' is-active' : '' ?>"
                                <?= $page === 'recettes' ? 'aria-current="page"' : '' ?>>Toutes les recettes</a></li>
                        <?php
                        if ($menuRecipesError) {
                            echo '<li>Erreur lors de la récupération des recettes</li>';
                        } elseif (empty($menuRecipes)) {
                            echo '<li>Aucune recette disponible</li>';
                        } else {
                            foreach ($menuRecipes as $menuRecipe) {
                                ?>
                                <?php $isCurrentRecipe = $activeRecipeSlug === $menuRecipe->getSlug(); ?>
                                <li><a href="?pg=recette&slug=<?php echo htmlspecialchars($menuRecipe->getSlug()) ?>"
                                        class="dropdown-item<?= $isCurrentRecipe ? ' is-active' : '' ?>"
                                        <?= $isCurrentRecipe ? 'aria-current="page"' : '' ?>><?php echo htmlspecialchars($menuRecipe->getTitle()) ?></a></li><?php
                            }
                        }
                        ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="?pg=a-propos" class="nav-link<?= $page === 'a-propos' ? ' is-active' : '' ?>"
                        <?= $page === 'a-propos' ? 'aria-current="page"' : '' ?>>À propos</a>
                </li>
                <li class="nav-item">
                    <a href="?pg=contact" class="nav-link<?= $page === 'contact' ? ' is-active' : '' ?>"
                        <?= $page === 'contact' ? 'aria-current="page"' : '' ?>>Contact</a>
                </li>


            </ul>
        </div>
    </div>
</nav>

<?php require RACINE_PATH . '/view/inc/flash.php'; ?>