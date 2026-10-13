<?php
//path: model/manager/RecipeManager.php
declare(strict_types=1);

namespace model\manager;

use model\interface\ManagerInterface;
use model\mapping\RecipeMapping;
use model\MyPDO;
use PDO;

class RecipeManager implements ManagerInterface{
    private MyPDO $db;

    // Base de tt les listes de recettes
    private const LIST_SELECT =
        'SELECT r.id, r.title, r.slug, r.description, r.main_image, r.prep_time_minutes, r.cook_time_minutes,
                r.difficulty, r.created_at,
                ROUND(AVG(ra.rating), 1) AS average_rating,
                COUNT(ra.id) AS rating_count,
                (SELECT GROUP_CONCAT(c.title ORDER BY c.title SEPARATOR \', \')
                 FROM categories c
                 JOIN recipe_categories rc ON rc.category_id = c.id
                 WHERE rc.recipe_id = r.id) AS category_titles
         FROM recipes r
         LEFT JOIN ratings ra ON ra.recipe_id = r.id';

    // Filtre catégorie 
    private const CATEGORY_FILTER =
        'EXISTS (
            SELECT 1
            FROM recipe_categories rc_filter
            JOIN categories c_filter ON c_filter.id = rc_filter.category_id
            WHERE rc_filter.recipe_id = r.id
            AND c_filter.slug = :category_slug
        )';

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    // recette menu déroulant
    public function getMenuList(): array{
        $query = $this->db->query('SELECT id, title, slug FROM recipes ORDER BY title');

        $recipes = [];
        foreach ($query->fetchAll() as $row){
            $recipes[] = new RecipeMapping($row);
        }
        return $recipes;
    }

    // Recette via Slug

    public function getBySlug(string $slug, ?int $userId = null): ?RecipeMapping{
        $query = $this->db->prepare(
            'SELECT r.id, r.title, r.slug, r.description, r.main_image, r.prep_time_minutes,
                    r.cook_time_minutes, r.servings, r.difficulty, r.author_id, r.created_at, r.updated_at,
                    ROUND(AVG(ra.rating), 1) AS average_rating,
                    COUNT(ra.id) AS rating_count,
                    (SELECT rating FROM ratings WHERE recipe_id = r.id AND user_id = :user_id) AS user_rating
             FROM recipes r
             LEFT JOIN ratings ra ON ra.recipe_id = r.id
             WHERE r.slug = :slug
             GROUP BY r.id'
        );
        $query->bindValue(':slug', $slug);
        $query->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch();

        //aucune recette avec ce slug
        if ($row === false){
            return null;
        }
        return new RecipeMapping($row);
    }

    // Top 3
    public function getTopThree(): array{
        return $this->fetchList(
            '',
            'COUNT(ra.id) > 0 DESC, AVG(ra.rating) DESC, COUNT(ra.id) DESC, r.created_at DESC',
            3
        );
    }

    public function getTimeShortcuts(): array{
        return $this->fetchList('', '(r.prep_time_minutes + r.cook_time_minutes), r.title', 5);
    }

    // Toutes les recettes + cat + Moyenne + Nb Vote
    public function getAll(): array{
        return $this->fetchList();
    }

    // recettes fin de fiche
    public function getOthers(int $recipeId, int $limit = 4): array{
        return $this->fetchList('r.id <> :recipe_id', 'r.title', $limit, [
            ':recipe_id' => $recipeId,
        ]);
    }

    // liste filtré de la page recettes (bonus) recherche, catégorie, difficulté et temps total.
    //Chaque critère est facultatif et toutes les valeurs passent par bindValue
    public function filter(string $searchTerm = '', ?string $categorySlug = null, ?string $difficulty = null, ?int $maxTotalTime = null): array {
        $conditions = [];
        $params = [];

        if ($searchTerm !== ''){
            // % et _ tapés par le visiteur sont cherchés comme du texte pas comme LIKE
            $searchPattern = '%' . addcslashes($searchTerm, '%_\\') . '%';
            $conditions[] = '(
            r.title LIKE :search_title
                OR r.description LIKE :search_description
                OR EXISTS (
                    SELECT 1
                    FROM recipe_categories rc_search
                    JOIN categories c_search ON c_search.id = rc_search.category_id
                    WHERE rc_search.recipe_id = r.id
                    AND c_search.title LIKE :search_category
            )
        )';
        $params[':search_title'] = $searchPattern;
        $params[':search_description'] = $searchPattern;
        $params[':search_category'] = $searchPattern;
        }

        if ($categorySlug !== null){
            $conditions[] = self::CATEGORY_FILTER; 
            $params[':category_slug'] = $categorySlug;
        }

        if ($difficulty !== null){
            $conditions[] = 'r.difficulty = :difficulty';
            $params[':difficulty'] = $difficulty;
        }

        if ($maxTotalTime !== null){
            // calculé jamais stocké 
            $conditions[] = '(r.prep_time_minutes + r.cook_time_minutes) <= :max_total_time';
            $params[':max_total_time'] = $maxTotalTime;
        }

        return $this->fetchList(implode(' AND ', $conditions), 'r.title', null, $params);
    }

    // Exécute LIST_SELECT avec filtre + $where $orderBy via classe, jamais de saisie
    private function fetchList(string $where = '', string $orderBy = 'r.title', ?int $limit = null, array $params = []): array{
        $sql = self::LIST_SELECT
            . ($where !== '' ? ' WHERE ' . $where : '')
            . ' GROUP BY r.id'
            . ' ORDER BY ' . $orderBy
            . ($limit !== null ? ' LIMIT ' . $limit : '');

        $query = $this->db->prepare($sql);
        foreach ($params as $name => $value) {
            $query->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $query->execute();

        $recipes = [];
        foreach ($query->fetchAll() as $row){
            $recipes[] = new RecipeMapping($row);
        }
        return $recipes;
    }

}