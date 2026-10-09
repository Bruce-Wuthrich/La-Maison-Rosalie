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

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    // liste des recette menu déroulant 
    public function getMenuList(): array{
        $query = $this->db->query('SELECT id, title, slug FROM recipes ORDER BY title');

        $recipes = [];
        foreach ($query->fetchAll() as $row){
            $recipes[] = new RecipeMapping($row);
        }
        return $recipes; 
    }

    // Recette depuis Slug 

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

    public function getTopThree(): array{
        $query = $this->db->query(
            'SELECT r.id, r.title, r.slug, r.main_image, r.prep_time_minutes, r.cook_time_minutes,
                    r.difficulty, r.created_at,
                    ROUND(AVG(ra.rating), 1) AS average_rating,
                    COUNT(ra.id) AS rating_count
             FROM recipes r
             LEFT JOIN ratings ra ON ra.recipe_id = r.id
             GROUP BY r.id
             ORDER BY COUNT(ra.id) > 0 DESC,
                      AVG(ra.rating) DESC,
                      COUNT(ra.id) DESC,
                      r.created_at DESC
             LIMIT 3'
        );

        $recipes = []; 
        foreach ($query->fetchAll() as $row){
            $recipes[] = new RecipeMapping($row);
        }
        return $recipes; 
    }

    // Cinq accès rapides classés par durée totale pour l'accueil.
    public function getTimeShortcuts(): array{
        $query = $this->db->query(
            'SELECT r.id, r.title, r.slug, r.description, r.main_image, r.prep_time_minutes, r.cook_time_minutes,
                    r.difficulty, r.created_at,
                    ROUND(AVG(ra.rating), 1) AS average_rating,
                    COUNT(ra.id) AS rating_count,
                    (SELECT GROUP_CONCAT(c.title ORDER BY c.title SEPARATOR \', \')
                     FROM categories c
                     JOIN recipe_categories rc ON rc.category_id = c.id
                     WHERE rc.recipe_id = r.id) AS category_titles
             FROM recipes r
             LEFT JOIN ratings ra ON ra.recipe_id = r.id
             GROUP BY r.id
             ORDER BY (r.prep_time_minutes + r.cook_time_minutes), r.title
             LIMIT 5'
        );

        $recipes = [];
        foreach ($query->fetchAll() as $row){
            $recipes[] = new RecipeMapping($row);
        }
        return $recipes;
    }


    // Toutes les recettes + cat + Moyenne + Nb Vote
    public function getAll(): array{
        $query = $this->db->query(
            'SELECT r.id, r.title, r.slug, r.description, r.main_image, r.prep_time_minutes, r.cook_time_minutes,
                    r.difficulty, r.created_at,
                    ROUND(AVG(ra.rating), 1) AS average_rating,
                    COUNT(ra.id) AS rating_count,
                    (SELECT GROUP_CONCAT(c.title ORDER BY c.title SEPARATOR \', \')
                     FROM categories c
                     JOIN recipe_categories rc ON rc.category_id = c.id
                     WHERE rc.recipe_id = r.id) AS category_titles
             FROM recipes r
             LEFT JOIN ratings ra ON ra.recipe_id = r.id
             GROUP BY r.id
             ORDER BY r.title'
        );

        $recipes = [];
        foreach ($query->fetchAll() as $row){
            $recipes[] = new RecipeMapping($row);
        }
        return $recipes; 
    }

    public function getByCategorySlug(string $categorySlug): array{
        $query = $this->db->prepare(
            'SELECT r.id, r.title, r.slug, r.description, r.main_image, r.prep_time_minutes, r.cook_time_minutes,
                    r.difficulty, r.created_at,
                    ROUND(AVG(ra.rating), 1) AS average_rating,
                    COUNT(ra.id) AS rating_count,
                    (SELECT GROUP_CONCAT(c.title ORDER BY c.title SEPARATOR \', \')
                     FROM categories c
                     JOIN recipe_categories rc ON rc.category_id = c.id
                     WHERE rc.recipe_id = r.id) AS category_titles
             FROM recipes r
             JOIN recipe_categories rc_filter ON rc_filter.recipe_id = r.id
             JOIN categories c_filter ON c_filter.id = rc_filter.category_id
             LEFT JOIN ratings ra ON ra.recipe_id = r.id
             WHERE c_filter.slug = :category_slug
             GROUP BY r.id
             ORDER BY r.title'
        );
        $query->bindValue(':category_slug', $categorySlug, PDO::PARAM_STR);
        $query->execute();

        $recipes = [];
        foreach ($query->fetchAll() as $row){
            $recipes[] = new RecipeMapping($row);
        }
        return $recipes;
    }

}