<?php
// path: model/manager/UserManager.php

declare(strict_types=1); 

namespace model\manager; 

use model\interface\ManagerInterface; 
use model\mapping\UserMapping; 
use model\MyPDO;
use PDO;  

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
}