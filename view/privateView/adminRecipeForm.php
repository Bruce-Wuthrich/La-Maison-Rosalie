<?php
// path: view/privateView/adminRecipeForm.php
// Onglet Recettes de l'admin : formulaire d'ajout ou de modification d'une recette (bonus).
// Inclus par adminView.php quand $recipeForm est préparé par AdminRecipeController.

$formRecipe = $recipeForm['recipe'];
$formData = $recipeForm['data'];
$isEdit = $formRecipe !== null;
$difficultyLabels = ['easy' => 'Facile', 'medium' => 'Moyen', 'hard' => 'Difficile'];

// Ligne d'ingrédient : sert aux lignes existantes et au modèle cloné par le JS (__INDEX__).
$ingredientRow = static function (string $index, array $ingredient) use ($escape): void { ?>
    <div class="admin-recipe-row" data-row>
        <label>
            <span>Ingrédient</span>
            <input type="text" name="ingredients[<?= $index ?>][name]" maxlength="120" value="<?= $escape($ingredient['name']) ?>">
        </label>
        <label>
            <span>Quantité</span>
            <input type="text" name="ingredients[<?= $index ?>][quantity]" inputmode="decimal" maxlength="11"
                pattern="\d{1,7}([.,]\d{1,3})?" title="Un nombre, par exemple 200 ou 1,5" value="<?= $escape($ingredient['quantity']) ?>">
        </label>
        <label>
            <span>Unité</span>
            <input type="text" name="ingredients[<?= $index ?>][unit]" maxlength="40" value="<?= $escape($ingredient['unit']) ?>">
        </label>
        <label>
            <span>Précision</span>
            <input type="text" name="ingredients[<?= $index ?>][details]" maxlength="120" value="<?= $escape($ingredient['details']) ?>">
        </label>
        <button class="comment-delete" type="button" data-remove-row>Retirer</button>
    </div>
<?php };

// Étape : titre, texte et photo (photo actuelle gardée si aucune nouvelle n'est choisie).
$stepRow = static function (string $index, array $step) use ($escape): void { ?>
    <div class="admin-recipe-row admin-recipe-step" data-row>
        <label>
            <span>Titre de l’étape</span>
            <input type="text" name="steps[<?= $index ?>][title]" maxlength="120" value="<?= $escape($step['title']) ?>">
        </label>
        <label class="admin-recipe-wide">
            <span>Instructions (températures et durées précises)</span>
            <textarea name="steps[<?= $index ?>][instructions]" rows="3" maxlength="1000"><?= $escape($step['instructions']) ?></textarea>
        </label>
        <?php if ($step['current_image'] !== '') { ?>
            <img class="admin-recipe-preview" src="<?= $escape($step['current_image']) ?>" alt="Photo actuelle de l’étape" loading="lazy">
            <input type="hidden" name="steps[<?= $index ?>][current_image]" value="<?= $escape($step['current_image']) ?>">
        <?php } ?>
        <label>
            <span><?= $step['current_image'] !== '' ? 'Remplacer la photo' : 'Photo de l’étape' ?> (300 Ko max)</span>
            <input type="file" name="step_images[<?= $index ?>]" accept="image/webp,image/jpeg,image/png">
        </label>
        <button class="comment-delete" type="button" data-remove-row>Retirer</button>
    </div>
<?php };
?>
<section class="admin-section" aria-labelledby="adminRecipeFormTitle">
    <h2 id="adminRecipeFormTitle"><?= $isEdit ? 'Modifier « ' . $escape($formRecipe->getTitle()) . ' »' : 'Ajouter une recette' ?></h2>

    <p>
        <a class="recipe-filter" href="?pg=admin&amp;onglet=recettes">Retour à la liste</a>
        <?php if ($isEdit) { ?>
            <a class="recipe-filter" href="?pg=recette&amp;slug=<?= rawurlencode((string) $formRecipe->getSlug()) ?>">Voir la recette</a>
        <?php } ?>
    </p>

    <p class="admin-meta">
        Tous les champs sont obligatoires, sauf la quantité, l’unité et la précision d’un ingrédient.
        Une recette compte au moins <?= \model\manager\RecipeAdminManager::MIN_STEPS ?> étapes, chacune avec sa photo.
    </p>

    <?php if ($recipeForm['error'] !== null) { ?>
        <p class="admin-recipe-error" role="alert"><?= $escape($recipeForm['error']) ?></p>
    <?php } ?>

    <form class="admin-recipe-form" action="<?= $escape($recipeForm['action']) ?>" method="post" enctype="multipart/form-data" data-admin-recipe-form>
        <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">

        <fieldset>
            <legend>Informations</legend>

            <label>
                <span>Titre</span>
                <input type="text" name="title" minlength="3" maxlength="150" required value="<?= $escape($formData['title']) ?>">
            </label>

            <label>
                <span>Description courte</span>
                <textarea name="description" rows="3" maxlength="2000" required><?= $escape($formData['description']) ?></textarea>
            </label>

            <div class="admin-recipe-row">
                <label>
                    <span>Préparation (min)</span>
                    <input type="number" name="prep_time" min="0" max="1440" required value="<?= $escape($formData['prep_time']) ?>">
                </label>
                <label>
                    <span>Cuisson (min)</span>
                    <input type="number" name="cook_time" min="0" max="1440" required value="<?= $escape($formData['cook_time']) ?>">
                </label>
                <label>
                    <span>Portions</span>
                    <input type="number" name="servings" min="1" max="50" required value="<?= $escape($formData['servings']) ?>">
                </label>
                <label>
                    <span>Difficulté</span>
                    <select name="difficulty" required>
                        <option value="">Choisir</option>
                        <?php foreach ($difficultyLabels as $value => $label) { ?>
                            <option value="<?= $value ?>"<?= $formData['difficulty'] === $value ? ' selected' : '' ?>><?= $label ?></option>
                        <?php } ?>
                    </select>
                </label>
            </div>

            <div class="admin-recipe-checks" role="group" aria-label="Catégories">
                <span>Catégories</span>
                <?php foreach ($recipeForm['categories'] as $category) { ?>
                    <label>
                        <input type="checkbox" name="categories[]" value="<?= (int) $category->getId() ?>"
                            <?= in_array((int) $category->getId(), $formData['categories'], true) ? 'checked' : '' ?>>
                        <?= $escape($category->getTitle()) ?>
                    </label>
                <?php } ?>
            </div>

            <?php if ($isEdit) { ?>
                <img class="admin-recipe-preview" src="<?= $escape($formRecipe->getMainImage()) ?>" alt="Photo principale actuelle">
            <?php } ?>
            <label>
                <span><?= $isEdit ? 'Remplacer la photo principale' : 'Photo principale' ?> (WebP, JPEG ou PNG, 300 Ko max)</span>
                <input type="file" name="main_image" accept="image/webp,image/jpeg,image/png"<?= $isEdit ? '' : ' required' ?>>
            </label>
        </fieldset>

        <fieldset>
            <legend>Ingrédients</legend>
            <div class="admin-recipe-rows" data-rows="ingredients">
                <?php foreach (array_values($formData['ingredients']) as $index => $ingredient) {
                    $ingredientRow((string) $index, $ingredient);
                } ?>
            </div>
            <button class="recipe-filter" type="button" data-add-row="ingredients">Ajouter un ingrédient</button>
        </fieldset>

        <fieldset>
            <legend>Étapes</legend>
            <div class="admin-recipe-rows" data-rows="steps" data-min-rows="<?= \model\manager\RecipeAdminManager::MIN_STEPS ?>">
                <?php foreach (array_values($formData['steps']) as $index => $step) {
                    $stepRow((string) $index, $step);
                } ?>
            </div>
            <button class="recipe-filter" type="button" data-add-row="steps">Ajouter une étape</button>
        </fieldset>

        <button class="recipe-filter" type="submit"><?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter la recette' ?></button>
    </form>

    <!-- Modèles clonés par admin-recette.js (__INDEX__ remplacé par un numéro libre) -->
    <template data-template="ingredients">
        <?php $ingredientRow('__INDEX__', ['name' => '', 'quantity' => '', 'unit' => '', 'details' => '']); ?>
    </template>
    <template data-template="steps">
        <?php $stepRow('__INDEX__', ['title' => '', 'instructions' => '', 'current_image' => '']); ?>
    </template>
</section>