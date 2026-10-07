<?php 

// path: model/manager/ContactMessageManager.php

declare(strict_types=1);

namespace model\manager;

use model\interface\ManagerInterface;
use model\mapping\ContactMessageMapping;
use model\MyPDO;
use PDO;
use PDOException;
use InvalidArgumentException;

class ContactMessageManager implements ManagerInterface{
    private MyPDO $db; 

    public function __construct(MyPDO $connect){
        $this->db = $connect;
    }

    public function create(array $data, string $ip): ContactMessageMapping|bool{
        // verif des 4 champs
        foreach (['name', 'email', 'subject', 'message'] as $field){
            if (!isset($data[$field]) || !is_string($data[$field])){
                throw new InvalidArgumentException('Tous les champs sont obligatoires.');
            }
        }

    // Mapping avec les champs select
    $contact = new ContactMessageMapping([
        'name' => $data['name'],
        'email' => $data['email'],
        'subject' => $data['subject'],
        'message' => $data['message'],
        'ip_address' => $ip,
    ]);

    // insert statut et created_at par bdd 
    try {
        $query = $this->db->prepare(
            'INSERT INTO contact_messages (name, email, subject, message, ip_address)
            VALUES(:name, :email, :subject, :message, :ip_address)'
        );
        $query->bindValue(':name', $contact->getName());
        $query->bindValue(':email', $contact->getEmail());
        $query->bindValue(':subject', $contact->getSubject());
        $query->bindValue(':message', $contact->getMessage());
        $query->bindValue(':ip_address', $contact->getIpAddress());
        $query->execute();
    } catch (PDOException $e){
        error_log('Enregistrement du message de contact impossible : ' . $e->getMessage());
        return false; 
    }
    // complete mapping
    $contact->setId((int) $this->db->lastInsertId()); 
    $contact->setStatus('new');
    return $contact; 
    }

    // Nb de msg envoyé par ip sur délais
    public function countRecentByIp(string $ip, int $minutes): int{
        $query = $this->db->prepare(
            'SELECT COUNT(*) FROM contact_messages
            WHERE ip_address = :ip
            AND created_at > NOW() - INTERVAL :minutes MINUTE'
        );
        $query->bindValue(':ip', $ip);
        $query->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $query->execute();

        return (int) $query->fetchColumn();
    }
}