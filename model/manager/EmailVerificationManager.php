<?php
// path: model/manager/EmailVerificationManager.php
// Confirmation d'adresse e-mail par lien : jeton aléatoire envoyé par e-mail,
// seule son empreinte SHA-256 est gardée en base (comme un mot de passe), valable 24 heures.

declare(strict_types=1);

namespace model\manager;

use model\interface\ManagerInterface;
use model\MyPDO;
use PDO;

class EmailVerificationManager implements ManagerInterface{
    private MyPDO $db;

    private const VALIDITY_HOURS = 24;

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    // Nouveau jeton pour ce compte (remplace l'ancien s'il y en avait un). Renvoie le jeton en clair, à mettre dans le lien.
    public function createToken(int $userId): string{
        $token = bin2hex(random_bytes(32));

        $query = $this->db->prepare(
            'REPLACE INTO email_verifications (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, NOW() + INTERVAL ' . self::VALIDITY_HOURS . ' HOUR)'
        );
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->bindValue(':token_hash', hash('sha256', $token));
        $query->execute();

        return $token;
    }

    // Lien cliqué : confirme l'adresse si le jeton existe et n'a pas expiré. Le jeton ne sert qu'une fois.
    public function verify(string $token): bool{
        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            return false;
        }

        $query = $this->db->prepare(
            'SELECT user_id FROM email_verifications WHERE token_hash = :token_hash AND expires_at > NOW()'
        );
        $query->bindValue(':token_hash', hash('sha256', $token));
        $query->execute();
        $userId = $query->fetchColumn();

        if ($userId === false) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $update = $this->db->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = :id');
            $update->bindValue(':id', (int) $userId, PDO::PARAM_INT);
            $update->execute();

            $delete = $this->db->prepare('DELETE FROM email_verifications WHERE user_id = :id');
            $delete->bindValue(':id', (int) $userId, PDO::PARAM_INT);
            $delete->execute();

            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }

        return true;
    }

    // Sans mailer configuré, le compte est confirmé tout de suite (installation sans SMTP).
    public function markVerified(int $userId): void{
        $query = $this->db->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = :id');
        $query->bindValue(':id', $userId, PDO::PARAM_INT);
        $query->execute();
    }
}