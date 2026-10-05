<?php
// path: model/manager/CommentManager.php 

declare(strict_types = 1);

namespace model\manager; 

use model\interface\ManagerInterface;
use model\mapping\CommentMapping;
use model\MyPDO; 
use PDO; 

// com des recettes
class CommentManager implements ManagerInterface{
    private MyPDO $db;

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    //Nb com publie/recette (pagination)
    public function countByRecipeId(int $recipeId): int{
        $query = $this->db->prepare(
            "SELECT COUNT(*) FROM comments WHERE recipe_id = :recipe_id AND publication_status = 'published'"
        );
        $query->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT); 
        $query->execute();

        return (int) $query->fetchColumn();
    }

    // com publié (plus récent à ancien)
    public function getByRecipeId(int $recipeId, int $limit, int $offset): array {
        $query = $this->db->prepare(
            "SELECT c.id, c.author_id, u.username AS author_username, c.recipe_id, c.subject, c.message, c.publication_status, c.created_at
            FROM comments c INNER JOIN users u ON u.id = c.author_id
            WHERE c.recipe_id = :recipe_id AND c.publication_status = 'published'
            ORDER BY c.created_at DESC, c.id DESC
            LIMIT :limit OFFSET :offset"
        ); 
        $query->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        $comments = [];
        foreach ($query->fetchAll() AS $row){
            $comments[] = new CommentMapping($row);
        }
        return $comments;
    }
}
