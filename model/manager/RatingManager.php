<?php
// path: model/manager/RatingManager.php

declare(strict_types = 1); 

namespace model\manager; 

use model\interface\ManagerInterface;
use model\mapping\RatingMapping; 
use model\MyPDO; 
use PDO;
use PDOException; 

//notation des recettes 
class RatingManager implements ManagerInterface{
    private MyPDO $db; 

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    //enregistre ou update note + exception
    public function rate(int $userId, int $recipeId, int $rating): bool{
        $note = new RatingMapping([
            'user_id' => $userId,
            'recipe_id' => $recipeId, 
            'rating' => $rating,
        ]);

        // une seule requete, pas de doublons mm avec envoi simultané
        try{
            $query = $this->db->prepare(
                'INSERT INTO ratings (user_id, recipe_id, rating) VALUES (:user_id, :recipe_id, :rating)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating)'
            ); 
            $query->bindValue(':user_id', $note->getUserId(), PDO::PARAM_INT);
            $query->bindValue(':recipe_id', $note->getRecipeId(), PDO::PARAM_INT);
            $query->bindValue(':rating', $note->getRating(), PDO::PARAM_INT);
            $query->execute();
        } catch (PDOException $e){
            error_log('Notation impossible : ' . $e->getMessage());
            return false; 
        }
        return true; 
    }

    // moyenne et nombre de vote (calculé depuis les notes) vaut null si aucune note 
    public function getStats(int $recipeId): array{
        $query = $this->db->prepare(
            'SELECT ROUND(AVG(rating),1) AS average, COUNT(*) AS count
            FROM ratings WHERE recipe_id = :recipe_id'
        );
        $query->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $query->execute(); 
        $row = $query->fetch();

        return [
            'average' => $row['average'] === null ? null : (float) $row['average'],
            'count' => (int) $row['count'],
        ];
    }
}