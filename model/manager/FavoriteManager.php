<?php
// path: model/manager/FavoriteManager.php

declare(strict_types=1);

namespace model\manager;

use model\interface\ManagerInterface;
use model\MyPDO;
use PDO;

// Bonus recettes favorites : une ligne par membre et par recette (clé primaire user_id + recipe_id).
class FavoriteManager implements ManagerInterface{
    private MyPDO $db;

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    // La recette est-elle dans les favoris du membre ?
    public function isFavorite(int $userId, int $recipeId): bool{
        $query = $this->db->prepare(
            'SELECT 1 FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id'
        );
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchColumn() !== false;
    }

    // Ajoute ou retire selon l'état demandé : renvoyer deux fois le même état ne crée jamais de doublon.
    public function setFavorite(int $userId, int $recipeId, bool $isFavorite): void{
        $query = $this->db->prepare($isFavorite
            ? 'INSERT IGNORE INTO favorites (user_id, recipe_id) VALUES (:user_id, :recipe_id)'
            : 'DELETE FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id'
        );
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $query->execute();
    }
}