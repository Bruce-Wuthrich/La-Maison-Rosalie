<?php
//path: model/manager/RecipeIngredientManager.php 
declare(strict_types=1);

namespace model\manager;

use PDO;
use model\interface\ManagerInterface; 
use model\mapping\RecipeIngredientMapping;
use model\MyPDO; 

class RecipeIngredientManager implements ManagerInterface{
    private MyPDO $db;

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    // Ingrédients avec QTE dans l'ordre 
    public function getByRecipeId(int $recipeId): array {
        $query = $this->db->prepare(
            'SELECT ri.id, ri.recipe_id, ri.ingredient_id, ri.quantity, ri.unit, ri.details, ri.sort_order, i.name AS ingredient_name 
            FROM recipe_ingredients ri 
            JOIN ingredients i 
            ON i.id = ri.ingredient_id
            WHERE ri.recipe_id = :recipe_id 
            ORDER BY ri.sort_order'
        );
        $query->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $query->execute();
        
        $ingredients = [];
        foreach ($query->fetchAll() as $row) {
            $ingredients[] = new RecipeIngredientMapping($row);
        }
        return $ingredients; 
    }
}