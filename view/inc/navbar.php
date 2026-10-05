<nav class="navbar ">
    <div class="container">

        <div class="navbar-actions navbar-actions-left">
            <a class="navbar-icon-link" href="?pg=accueil" aria-label="Accueil">
                <img class="navbar-icon" src="assets/img/interface/home.svg" alt="Accueil">
            </a>
            <button type="button" class="navbar-icon-button" aria-label="Rechercher">
                <img class="navbar-icon" src="assets/img/interface/search.svg" alt="Rechercher">
            </button>
        </div>

        <a href="?pg=accueil" class="navbar-brand">
            <img src="assets/img/logo/picto.svg" alt="Maison Rosalie" class="navbar-logo">
        </a>

        <div class="navbar-actions navbar-actions-right">

            <button type="button" class="navbar-icon-button" aria-label="Se connecter">
                <img class="navbar-icon" src="assets/img/interface/account.svg" alt="Compte">
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
                    <a href="?pg=recettes" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">Recettes</a>
                    <ul class="dropdown-menu">
                        <li><a href="?pg=recettes" class="dropdown-item">Toutes les recettes</a></li>
                        <?php
                        if ($menuRecipesError) {
                            echo '<li>Erreur lors de la récupération des recettes</li>';
                        } elseif (empty($menuRecipes)) {
                            echo '<li>Aucune recette disponible</li>';
                        } else {
                            foreach ($menuRecipes as $menuRecipe) {
                                ?>
                                <li><a href="?pg=recette&slug=<?php echo htmlspecialchars($menuRecipe->getSlug()) ?>"
                                        class="dropdown-item"><?php echo htmlspecialchars($menuRecipe->getTitle()) ?></a></li><?php
                            }
                        }
                        ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="?pg=contact" class="nav-link">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="?pg=a-propos" class="nav-link">À propos</a>
                </li>


            </ul>
        </div>
    </div>
</nav>