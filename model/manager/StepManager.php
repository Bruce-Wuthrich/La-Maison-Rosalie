<?php
//path: model/manager/StepManager.php 
declare(strict_types=1); 

namespace model\manager; 

use PDO;
use model\interface\ManagerInterface;
use model\mapping\StepMapping; 
use model\MyPDO;


class StepManager implements ManagerInterface{
    private MyPDO $db;

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    // Etape d'un recette dans l'ordre 
    public function getByRecipeId(int $recipeId):array{
        $query= $this->db->prepare(
            'SELECT id, recipe_id, step_number, title, instructions, image 
            FROM steps WHERE recipe_id = :recipe_id ORDER BY step_number'
        );
    $query->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
    $query->execute();

    $steps = [];
    foreach ($query->fetchAll() as $row) {
        $steps[] = new StepMapping($row);
    }
    return $steps;
    }
}