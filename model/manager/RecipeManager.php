<?php
//path: model/manager/RecipeManager.php
declare(strict_types=1); 

namespace model\manager; 

use model\interface\ManagerInterface; 
use model\mapping\RecipeMapping; 
use model\MyPDO; 

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
            'SELECT id, title, slug, description, main_image, prep_time_minutes, cook_time_minutes, servings,
            difficulty, author_id, created_at, updated_at FROM recipes WHERE slug = :slug' 
        );
        $query->bindValue(':slug', $slug);
        $query->execute();

        $row = $query->fetch();

        //aucune recette avec ce slug 
        if ($row === false){
            return null;
        }
        return new RecipeMapping($row); 
    }
}