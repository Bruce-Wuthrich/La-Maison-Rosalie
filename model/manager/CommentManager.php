<?php
// path: model/manager/CommentManager.php 

declare(strict_types = 1);

namespace model\manager; 

use model\interface\ManagerInterface;
use model\mapping\CommentMapping;
use model\MyPDO; 
use PDO; 
use PDOException; 
use InvalidArgumentException;

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

    // Commentaires publiés avec la note laissée par leur auteur.
    public function getByRecipeId(int $recipeId, int $limit, int $offset, string $sort = 'date'): array {
        $orderBy = match ($sort) {
            'mieux' => 'author_rating DESC, c.created_at DESC, c.id DESC',
            'pire' => 'author_rating ASC, c.created_at DESC, c.id DESC',
            default => 'c.created_at DESC, c.id DESC',
        };
        $query = $this->db->prepare(
            "SELECT c.id, c.author_id, u.username AS author_username, c.recipe_id,
                    c.subject, c.message, c.publication_status, c.created_at,
                    COALESCE(r.rating, 0) AS author_rating
             FROM comments c
             INNER JOIN users u ON u.id = c.author_id
             LEFT JOIN ratings r ON r.user_id = c.author_id AND r.recipe_id = c.recipe_id
             WHERE c.recipe_id = :recipe_id AND c.publication_status = 'published'
             ORDER BY {$orderBy}
             LIMIT :limit OFFSET :offset"
        );
        $query->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();
        $comments = [];
        foreach ($query->fetchAll() as $row){
            $comments[] = new CommentMapping($row);
        }
        return $comments;
    }

    // com par id (qu'import le status)
    public function getById(int $id): ?CommentMapping{
        $query = $this->db->prepare(
            "SELECT c.id, c.author_id, u.username AS author_username, c.recipe_id,
                    c.subject, c.message, c.publication_status, c.created_at
             FROM comments c
             INNER JOIN users u ON u.id = c.author_id
             WHERE c.id = :id"
        );
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $row = $query->fetch();

        if ($row === false){
            return null;
        }
        return new CommentMapping($row);
    }

    // un seul avis par personne
    public function hasCommented(int $authorId, int $recipeId): bool{
        $query = $this->db->prepare(
            'SELECT 1 FROM comments WHERE author_id = :author_id AND recipe_id = :recipe_id'
        );
        $query->bindValue(':author_id', $authorId, PDO::PARAM_INT);
        $query->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchColumn() !== false;
    }

    // ajout com + renvoi complet (affiche sans recharge) + Exception et renvoi false si recette n'existe pas 
    public function create(int $authorId, int $recipeId, array $data): CommentMapping|bool{
        //msg obligatoirement text et suj facult.
        if (!isset($data['message']) || !is_string($data['message'])){
            throw new InvalidArgumentException("Le message est obligatoire.");
        }

        $subject = $data['subject'] ?? null;
        if ($subject !== null && !is_string($subject)) {
            throw new InvalidArgumentException('Le sujet est invalide.');
        }

        // Mapping avec champs selectionnés (jms de new CommentMapping sur $data)
        $comment = new CommentMapping([
            'author_id' => $authorId,
            'recipe_id' => $recipeId,
            'subject' => $subject, 
            'message' => $data['message'],
        ]);

        // status et created_at remplis par la bdd 
        try{
            $query = $this->db->prepare(
                // Un seul avis par personne et par recette
                "INSERT INTO comments (author_id, recipe_id, subject, message)
                VALUES (:author_id, :recipe_id, :subject, :message)
                ON DUPLICATE KEY UPDATE
                    id = LAST_INSERT_ID(id),
                    subject = VALUES(subject),
                    message = VALUES(message),
                    created_at = CURRENT_TIMESTAMP"
            );
            $query->bindValue(':author_id', $comment->getAuthorId(), PDO::PARAM_INT);
            $query->bindValue(':recipe_id', $comment->getRecipeId(), PDO::PARAM_INT);
            $query->bindValue(':subject', $comment->getSubject(), $comment->getSubject() === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindValue(':message', $comment->getMessage());
            $query->execute();
        }catch (PDOException $e) {
            error_log("Ajout de commentaire impossible : " . $e->getMessage());
            return false;
        }

        //relit com + revient avec son id, sa date et le nom de l'auteur
        return $this->getById((int) $this->db->lastInsertId()) ?? false;
    }

    // sup com (si auteur ou admin) verif dans la requete, peut pas supp ce des autres et renvoi false si id n'existe pas ou pas de droit 
    public function delete(int $commentId, int $userId, bool $isAdmin): bool{
        $query = $this->db->prepare(
            'DELETE FROM comments 
            WHERE id = :id AND (author_id = :user_id OR :is_admin =1)'
        );
        $query->bindValue(':id', $commentId, PDO::PARAM_INT);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->bindValue(':is_admin', $isAdmin ? 1 : 0, PDO::PARAM_INT);
        $query->execute(); 

        return $query->rowCount() > 0;
    }

    // Tous les avis, du plus récent au plus ancien avec recette pour modération (page admin).
    public function getAllForAdmin(int $limit = 100): array{
        $query = $this->db->prepare(
            "SELECT c.id, c.author_id, u.username AS author_username, c.recipe_id,
                    r.title AS recipe_title, r.slug AS recipe_slug,
                    c.subject, c.message, c.publication_status, c.created_at,
                    COALESCE(ra.rating, 0) AS author_rating
             FROM comments c
             INNER JOIN users u ON u.id = c.author_id
             INNER JOIN recipes r ON r.id = c.recipe_id
             LEFT JOIN ratings ra ON ra.user_id = c.author_id AND ra.recipe_id = c.recipe_id
             ORDER BY c.created_at DESC, c.id DESC
             LIMIT :limit"
        );
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->execute();

        $comments = [];
        foreach ($query->fetchAll() as $row){
            $comments[] = new CommentMapping($row);
        }
        return $comments;
    }

    // Nb de com par user (sur une période)
    public function countRecentByAuthor(int $authorId, int $minutes): int{
        $query = $this->db->prepare(
            'SELECT COUNT(*) FROM comments
            WHERE author_id = :author_id
            AND created_at > NOW() - INTERVAL :minutes MINUTE'
        );
        $query->bindValue(':author_id', $authorId, PDO::PARAM_INT);
        $query->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $query->execute();

        return (int) $query->fetchColumn(); 
    }
}
