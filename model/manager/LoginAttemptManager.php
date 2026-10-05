<?php
// path : model/manager/LoginAttemptManager.php

declare(strict_types = 1);

namespace model\manager; 

use model\interface\ManagerInterface; 
use model\MyPDO; 
use PDO; 

//limit tentatives connexion 
class LoginAttemptManager implements ManagerInterface{
    private MyPDO $db;

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    // Nb echec mail/ip 
    public function countRecent(string $email, string $ip, int $minutes): int{
        $query = $this->db->prepare(
            'SELECT COUNT(*) FROM login_attempts
            WHERE (email = :email OR ip_address = :ip)
            AND attempted_at > NOW() - INTERVAL :minutes MINUTE'
        );
        $query->bindValue(':email', $email);
        $query->bindValue(':ip', $ip);
        $query->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $query->execute();

        return (int) $query->fetchColumn();
    }

    // enregistre l'echec 
    public function add(string $email, string $ip):void {
        $query = $this->db->prepare(
            'INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)'
        );
        $query->bindValue(':email', mb_substr(trim($email), 0, 254));
        $query->bindValue(':ip', mb_substr($ip, 0, 45));
        $query->execute();
    }

    // efface echec apres reussite 
    public function clear(string $email):void {
        $query = $this->db->prepare(
            'DELETE FROM login_attempts WHERE email = :email'
        );
        $query->bindValue(':email', trim($email));
        $query->execute(); 
    }
}