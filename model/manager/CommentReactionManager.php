<?php

// path: model/manager/CommentReactionManager.php

declare(strict_types=1);

namespace model\manager;

use InvalidArgumentException;
use model\interface\ManagerInterface;
use model\MyPDO;
use PDO;
use PDOException;

// Enregistre une seule réaction par membre et par commentaire.
class CommentReactionManager implements ManagerInterface
{
    private MyPDO $db;

    public function __construct(MyPDO $connect)
    {
        $this->db = $connect;
    }

    public function react(int $userId, int $commentId, string $reaction): bool
    {
        if (!in_array($reaction, ['like', 'dislike'], true)) {
            throw new InvalidArgumentException('La réaction doit être positive ou négative.');
        }

        try {
            $query = $this->db->prepare(
                'INSERT INTO comment_reactions (comment_id, user_id, reaction)
                 VALUES (:comment_id, :user_id, :reaction)
                 ON DUPLICATE KEY UPDATE reaction = VALUES(reaction), reacted_at = CURRENT_TIMESTAMP'
            );
            $query->bindValue(':comment_id', $commentId, PDO::PARAM_INT);
            $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $query->bindValue(':reaction', $reaction);
            $query->execute();
        } catch (PDOException $exception) {
            error_log('Réaction au commentaire impossible : ' . $exception->getMessage());
            return false;
        }

        return true;
    }
}
