<?php
// path: model/mapping/UserMapping.php
declare(strict_types=1);

namespace model\mapping;

use InvalidArgumentException;
use model\abstract\AbstractMapping;

class UserMapping extends AbstractMapping{
    protected ?int $id = null; 
    protected ?string $username = null; 
    protected ?string $email = null; 
    protected ?string $password_hash = null; 
    protected ?string $role = null; 
    protected ?string $created_at = null; 


    public function getId(): ?int{
        return $this->id;
    }

    public function setId(int $id): void{
        if($id<=0){
            throw new InvalidArgumentException("L'identifiant de l'utilisateur doit être positif.");
        }
        $this->id = $id;
    }

    public function getUsername(): ?string{
        return $this->username;
    }

    public function setUsername(string $username):void{
        $username = trim($username);
        $length = mb_strlen($username);
        if (!preg_match('/^[\p{L}\p{N}_-]{3,50}$/u', $username)) {
            throw new InvalidArgumentException("Le nom d'utilisateur doit faire entre 3 et 50 caractères : lettres, chiffres, - et _ uniquement.");
        }
        $this->username = $username; 
    }

    public function getEmail(): ?string{
        return $this->email;
    }

    public function setEmail(string $email): void{
        $email = trim($email);
        if(filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 254){
            throw new InvalidArgumentException("L'email de l'utilisateur doit être valide et ne pas dépasser 254 caractères.");
        }
        $this->email = $email;
    }

    public function getPasswordHash(): ?string{
        return $this->password_hash;
    }

    public function setPasswordHash(string $passwordHash):void {
        $this->password_hash = $passwordHash;
    }

    public function getRole(): ?string{
        return $this->role;
    }

    public function setRole(string $role):void{
        if (!in_array($role, ['member', 'admin'], true)){
            throw new InvalidArgumentException('Votre rôle doit être soit member ou admin.');
        }
        $this->role = $role;
    }

    public function getCreatedAt(): ?string{
        return $this->created_at;
    }

    public function setCreatedAt(string $createdAt):void {
        $this->created_at = $createdAt;
    }

    // verif admin 
    public function isAdmin():bool{
        return $this->role === 'admin';
    }
    
    // retire hash mdp + envoi json 
    public function toArray(): array{
        $data = parent::toArray();
        unset($data['password_hash']);
        return $data;
    }
}