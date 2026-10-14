<?php
//path: model/manager/CategoryManager.php 
declare(strict_types=1);

namespace model\manager;

use PDO;
use model\interface\ManagerInterface; 
use model\mapping\CategoryMapping;
use model\MyPDO; 

class CategoryManager implements ManagerInterface{
    private MyPDO $db;

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    public function getByRecipeId(int $recipeId): array {
        $query = $this->db->prepare(
            'SELECT c.id, c.title, c.slug, c.description
                FROM categories c
                JOIN recipe_categories rc ON rc.category_id = c.id
                WHERE rc.recipe_id = :recipe_id
                ORDER BY c.title'
        );
        $query->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $query->execute();
        
        $categories = [];
        foreach ($query->fetchAll() as $row) {
            $categories[] = new CategoryMapping($row);
        }
        return $categories; 
    }

    /** Catégories utilisées par au moins une recette, avec leur image représentative. */
    public function getHeroList(): array {
        $query = $this->db->query(
            'SELECT c.title, c.slug, c.description,
                    r.main_image AS image, r.title AS image_alt, r.slug AS recipe_slug
             FROM categories c
             JOIN (
                 SELECT category_id, MIN(recipe_id) AS recipe_id
                 FROM recipe_categories
                 GROUP BY category_id
             ) first_recipe ON first_recipe.category_id = c.id
             JOIN recipes r ON r.id = first_recipe.recipe_id
             ORDER BY c.title'
        );

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array{
        $query = $this->db->query(
            'SELECT id, title, slug, description FROM categories ORDER BY title'
        ); 

        $categories = [];
        foreach ($query->fetchAll() as $row){
            $categories[] = new CategoryMapping($row);
        }
        return $categories; 
    }
}