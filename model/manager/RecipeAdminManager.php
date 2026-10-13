<?php
// path: model/manager/RecipeAdminManager.php
// validation et écriture d'une recette complète (infos, catégories, ingrédients, étapes).
// La lecture reste dans RecipeManager, StepManager, RecipeIngredientManager et CategoryManager.

declare(strict_types=1);

namespace model\manager;

use InvalidArgumentException;
use model\interface\ManagerInterface;
use model\mapping\RecipeIngredientMapping;
use model\mapping\RecipeMapping;
use model\mapping\StepMapping;
use model\MyPDO;
use PDO;
use Throwable;

class RecipeAdminManager implements ManagerInterface{
    private MyPDO $db;

    // FE-33 : 4 étapes minimum par recette
    public const MIN_STEPS = 4;
    private const MAX_STEPS = 20;
    private const MAX_INGREDIENTS = 50;
    private const MAX_DESCRIPTION = 2000;

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    // Valide tout le formulaire sauf les images, lève InvalidArgumentException au premier problème.
    public function validate(array $data, array $allowedCategoryIds, array $existingImages): array{
        // titre et description contrôlés par le mapping
        $recipe = new RecipeMapping([
            'title' => self::text($data['title'] ?? null),
            'description' => self::text($data['description'] ?? null),
        ]);
        if (mb_strlen((string) $recipe->getDescription()) > self::MAX_DESCRIPTION){
            throw new InvalidArgumentException('La description ne doit pas dépasser ' . self::MAX_DESCRIPTION . ' caractères.');
        }

        $prepTime = self::integer($data['prep_time'] ?? null, 0, 1440, 'Le temps de préparation doit être compris entre 0 et 1440 minutes.');
        $cookTime = self::integer($data['cook_time'] ?? null, 0, 1440, 'Le temps de cuisson doit être compris entre 0 et 1440 minutes.');
        $servings = self::integer($data['servings'] ?? null, 1, 50, 'Le nombre de portions doit être compris entre 1 et 50.');

        $difficulty = self::text($data['difficulty'] ?? null);
        if (!in_array($difficulty, ['easy', 'medium', 'hard'], true)){
            throw new InvalidArgumentException('Choisissez une difficulté.');
        }

        // seules les catégories qui existent en base sont gardées
        $categoryIds = [];
        foreach (is_array($data['categories'] ?? null) ? $data['categories'] : [] as $value){
            if (is_string($value) && ctype_digit($value) && in_array((int) $value, $allowedCategoryIds, true)){
                $categoryIds[] = (int) $value;
            }
        }
        $categoryIds = array_values(array_unique($categoryIds));
        if ($categoryIds === []){
            throw new InvalidArgumentException('Choisissez au moins une catégorie.');
        }

        return [
            'title' => (string) $recipe->getTitle(),
            'description' => (string) $recipe->getDescription(),
            'prep_time' => $prepTime,
            'cook_time' => $cookTime,
            'servings' => $servings,
            'difficulty' => $difficulty,
            'category_ids' => $categoryIds,
            'ingredients' => $this->validateIngredients($data['ingredients'] ?? null),
            'steps' => $this->validateSteps($data['steps'] ?? null, $existingImages),
        ];
    }

    // Identifiant lisible unique tiré du titre 
    public function uniqueSlug(string $title): string{
        $slug = strtr(mb_strtolower(trim($title)), [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ç' => 'c',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'î' => 'i', 'ï' => 'i', 'í' => 'i',
            'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
            'ÿ' => 'y', 'œ' => 'oe', 'æ' => 'ae',
        ]);
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $slug), '-');
        $slug = substr($slug, 0, 150);
        if ($slug === ''){
            $slug = 'recette';
        }

        $candidate = $slug;
        $suffix = 2;
        while ($this->slugExists($candidate)){
            $candidate = $slug . '-' . $suffix++;
        }
        return $candidate;
    }

    // Crée ($recipeId null) ou remplace une recette complète en une seule transaction : tout ou rien.
    public function save(?int $recipeId, string $slug, array $recipe, string $mainImage, array $stepImages, int $authorId): int{
        $this->db->beginTransaction();

        try{
            $params = [
                ':title' => $recipe['title'],
                ':description' => $recipe['description'],
                ':main_image' => $mainImage,
                ':prep_time' => $recipe['prep_time'],
                ':cook_time' => $recipe['cook_time'],
                ':servings' => $recipe['servings'],
                ':difficulty' => $recipe['difficulty'],
            ];

            if ($recipeId === null){
                $query = $this->db->prepare(
                    'INSERT INTO recipes (title, slug, description, main_image, prep_time_minutes, cook_time_minutes, servings, difficulty, author_id)
                    VALUES (:title, :slug, :description, :main_image, :prep_time, :cook_time, :servings, :difficulty, :author_id)'
                );
                $params[':slug'] = $slug;
                $params[':author_id'] = $authorId;
                $this->execute($query, $params);
                $recipeId = (int) $this->db->lastInsertId();
            } else {
                // le slug ne change jamais : les liens et le dossier d'images restent valides
                $query = $this->db->prepare(
                    'UPDATE recipes SET title = :title, description = :description, main_image = :main_image,
                        prep_time_minutes = :prep_time, cook_time_minutes = :cook_time, servings = :servings, difficulty = :difficulty
                    WHERE id = :id'
                );
                $params[':id'] = $recipeId;
                $this->execute($query, $params);
            }

            // catégories, ingrédients et étapes réécrits entièrement (noms de tables fixes, jamais saisis)
            foreach (['recipe_categories', 'recipe_ingredients', 'steps'] as $table){
                $this->execute($this->db->prepare("DELETE FROM {$table} WHERE recipe_id = :recipe_id"), [':recipe_id' => $recipeId]);
            }

            $categoryQuery = $this->db->prepare('INSERT INTO recipe_categories (recipe_id, category_id) VALUES (:recipe_id, :category_id)');
            foreach ($recipe['category_ids'] as $categoryId){
                $this->execute($categoryQuery, [':recipe_id' => $recipeId, ':category_id' => $categoryId]);
            }

            $ingredientQuery = $this->db->prepare(
                'INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit, details, sort_order)
                VALUES (:recipe_id, :ingredient_id, :quantity, :unit, :details, :sort_order)'
            );
            foreach ($recipe['ingredients'] as $position => $ingredient){
                $this->execute($ingredientQuery, [
                    ':recipe_id' => $recipeId,
                    ':ingredient_id' => $this->ingredientId($ingredient['name']),
                    ':quantity' => $ingredient['quantity'],
                    ':unit' => $ingredient['unit'],
                    ':details' => $ingredient['details'],
                    ':sort_order' => $position + 1,
                ]);
            }

            $stepQuery = $this->db->prepare(
                'INSERT INTO steps (recipe_id, step_number, title, instructions, image)
                VALUES (:recipe_id, :step_number, :title, :instructions, :image)'
            );
            foreach ($recipe['steps'] as $position => $step){
                $this->execute($stepQuery, [
                    ':recipe_id' => $recipeId,
                    ':step_number' => $position + 1,
                    ':title' => $step['title'],
                    ':instructions' => $step['instructions'],
                    ':image' => $stepImages[$position],
                ]);
            }

            $this->db->commit();
        } catch (Throwable $exception){
            if ($this->db->inTransaction()){
                $this->db->rollBack();
            }
            throw $exception;
        }

        return $recipeId;
    }

    // Supprime une recette : notes, avis, favoris, étapes, ingrédients et catégories suivent (ON DELETE CASCADE).
    public function delete(int $recipeId): bool{
        $query = $this->db->prepare('DELETE FROM recipes WHERE id = :id');
        $query->bindValue(':id', $recipeId, PDO::PARAM_INT);
        $query->execute();

        return $query->rowCount() > 0;
    }

    private function validateIngredients(mixed $rows): array{
        $ingredients = [];
        $seenNames = [];

        foreach (is_array($rows) ? $rows : [] as $row){
            if (!is_array($row)){
                continue;
            }

            $name = self::text($row['name'] ?? null);
            $quantity = str_replace(',', '.', self::text($row['quantity'] ?? null));
            $unit = self::text($row['unit'] ?? null);
            $details = self::text($row['details'] ?? null);

            // ligne laissée vide dans le formulaire : ignorée
            if ($name === '' && $quantity === '' && $unit === '' && $details === ''){
                continue;
            }

            // nom, unité et précisions contrôlés par le mapping
            $line = new RecipeIngredientMapping(['ingredient_name' => $name]);
            $line->setUnit($unit === '' ? null : $unit);
            $line->setDetails($details === '' ? null : $details);

            // quantité facultative 
            if ($quantity !== '' && (preg_match('/^\d{1,7}(\.\d{1,3})?$/', $quantity) !== 1 || (float) $quantity <= 0)){
                throw new InvalidArgumentException('La quantité de « ' . $line->getIngredientName() . ' » doit être un nombre positif (ex. 200 ou 1,5).');
            }

            // UNIQUE (recipe_id, ingredient_id) en base : un ingrédient une seule fois par recette (accents et majuscules ignorés)
            $key = self::normalizedName((string) $line->getIngredientName());
            if (isset($seenNames[$key])){
                throw new InvalidArgumentException('L’ingrédient « ' . $line->getIngredientName() . ' » apparaît deux fois. Regroupez les quantités sur une seule ligne.');
            }
            $seenNames[$key] = true;

            $ingredients[] = [
                'name' => (string) $line->getIngredientName(),
                'quantity' => $quantity === '' ? null : $quantity,
                'unit' => $line->getUnit(),
                'details' => $line->getDetails(),
            ];
        }

        if ($ingredients === []){
            throw new InvalidArgumentException('Ajoutez au moins un ingrédient.');
        }
        if (count($ingredients) > self::MAX_INGREDIENTS){
            throw new InvalidArgumentException('Une recette compte ' . self::MAX_INGREDIENTS . ' ingrédients maximum.');
        }
        return $ingredients;
    }

    private function validateSteps(mixed $rows, array $existingImages): array{
        $steps = [];

        foreach (is_array($rows) ? $rows : [] as $index => $row){
            if (!is_int($index) || !is_array($row)){
                continue;
            }

            $title = self::text($row['title'] ?? null);
            $instructions = self::text($row['instructions'] ?? null);

            // étape laissée vide dans le formulaire : ignorée
            if ($title === '' && $instructions === ''){
                continue;
            }

            try{
                $step = new StepMapping(['title' => $title, 'instructions' => $instructions]);
            } catch (InvalidArgumentException $exception){
                throw new InvalidArgumentException('Étape ' . (count($steps) + 1) . ' : ' . $exception->getMessage());
            }

            $currentImage = self::text($row['current_image'] ?? null);

            $steps[] = [
                'index' => $index, // retrouve le fichier step_images[index] envoyé avec cette étape
                'title' => (string) $step->getTitle(),
                'instructions' => (string) $step->getInstructions(),
                'current_image' => in_array($currentImage, $existingImages, true) ? $currentImage : null,
            ];
        }

        if (count($steps) < self::MIN_STEPS){
            throw new InvalidArgumentException('Une recette compte au moins ' . self::MIN_STEPS . ' étapes.');
        }
        if (count($steps) > self::MAX_STEPS){
            throw new InvalidArgumentException('Une recette compte ' . self::MAX_STEPS . ' étapes maximum.');
        }
        return $steps;
    }

    // Réutilise l'ingrédient s'il existe déjà (nom unique, sans tenir compte des accents ni majuscules), sinon le crée.
    private function ingredientId(string $name): int{
        $query = $this->db->prepare(
            'INSERT INTO ingredients (name) VALUES (:name)
            ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
        );
        $query->bindValue(':name', $name);
        $query->execute();

        return (int) $this->db->lastInsertId();
    }

    private function slugExists(string $slug): bool{
        $query = $this->db->prepare('SELECT 1 FROM recipes WHERE slug = :slug LIMIT 1');
        $query->bindValue(':slug', $slug);
        $query->execute();

        return $query->fetchColumn() !== false;
    }

    // bindValue avec le bon type pour chaque valeur (entier, texte ou NULL)
    private function execute(\PDOStatement $query, array $params): void{
        foreach ($params as $name => $value){
            $type = match (true) {
                $value === null => PDO::PARAM_NULL,
                is_int($value) => PDO::PARAM_INT,
                default => PDO::PARAM_STR,
            };
            $query->bindValue($name, $value, $type);
        }
        $query->execute();
    }

    // "Crème" et "creme" désignent le même ingrédient pour la base (collation sans accents ni majuscules)
    private static function normalizedName(string $name): string{
        return strtr(mb_strtolower($name), [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ç' => 'c',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'î' => 'i', 'ï' => 'i', 'í' => 'i',
            'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
            'ÿ' => 'y', 'œ' => 'oe', 'æ' => 'ae',
        ]);
    }

    private static function text(mixed $value): string{
        return is_string($value) ? trim($value) : '';
    }

    private static function integer(mixed $value, int $min, int $max, string $message): int{
        $value = self::text($value);
        if ($value === '' || !ctype_digit($value) || (int) $value < $min || (int) $value > $max){
            throw new InvalidArgumentException($message);
        }
        return (int) $value;
    }
}