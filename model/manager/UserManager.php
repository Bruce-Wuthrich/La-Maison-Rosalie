<?php
// path: model/manager/UserManager.php

declare(strict_types=1); 

namespace model\manager; 

use model\interface\ManagerInterface; 
use model\mapping\UserMapping; 
use model\MyPDO;
use PDO;  
use Exception; 
use PDOException; 

class UserManager implements ManagerInterface{
    private MyPDO $db; 

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    // get by id / pas de mdp hash 
    public function getById(int $id): ?UserMapping{
        $query = $this->db->prepare(
            'SELECT id, username, email, role, created_at FROM users WHERE id = :id'
        );
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch(); 

        //aucun user avec cet id 
        if ($row === false){
            return null;
        }
        return new UserMapping($row); 
    }

    public function emailExists(string $email): bool{
        $query = $this->db->prepare(
            'SELECT 1 FROM users WHERE email = :email LIMIT 1'
        );
        $query->bindValue(':email', $email);
        $query->execute();

        return $query->fetch() !== false; 
    }

    public function usernameExists(string $username): bool{
        $query = $this->db->prepare(
            'SELECT 1 FROM users WHERE username = :username LIMIT 1'
        );
        $query->bindValue(':username', $username);
        $query->execute();

        return $query->fetch() !== false; 
    }

    // inscritpion nouvel user 
    public function register(array $data): UserMapping|bool{
        foreach (['username', 'email', 'password', 'password_confirm'] as $field) {
            if (!isset($data[$field]) || !is_string($data[$field])){
                throw new Exception('Tous les champs sont obligatoires.');
            }
        }
        // mdp identique 
        if ($data['password'] !== $data['password_confirm']){
            throw new Exception('Les mots de passe ne correspondent pas.');
        }

        // spec mdp 
        $password = $data['password'];
        if (mb_strlen($password) < 10
        || !preg_match('/[A-Z]/', $password)
        || !preg_match('/[a-z]/', $password)
        || !preg_match('/[0-9]/', $password)){
            throw new Exception('Le mot de passe doit faire au moins 10 caractères, avec une majuscule, une minuscule et un chiffre.');
        }

        // valide username + email
        $user = new UserMapping([
            'username' => $data['username'],
            'email'    => $data['email'],
        ]);

        // check doublons 
        if ($this->usernameExists($user->getUsername())) {
            throw new Exception('Ce nom d\'utilisateur est déjà pris.');
        }
        if ($this->emailExists($user->getEmail())) {
            throw new Exception('Cet email est déjà utilisé.');
        }

        // Hash mdp 
        $user->setPasswordHash(password_hash($password, PASSWORD_DEFAULT));

        // insertion 
        try{
            $query = $this->db->prepare(
                'INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)'
            );
            $query->bindValue(':username', $user->getUsername());
            $query->bindValue(':email', $user->getEmail());
            $query->bindValue(':password_hash', $user->getPasswordHash());
            $query->execute();
            }   catch (PDOException $e) {
                //détail journal 
                error_log('Inscription impossible : ' . $e->getMessage());
                return false; 
                }

        // map + envoie controlleur 
        $user->setId((int) $this->db->lastInsertId());
        $user->setRole('member');
        return $user; 
    }
}