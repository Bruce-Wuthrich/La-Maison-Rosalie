<?php
// path: model/mapping/ContactMessageMapping.php

declare(strict_types=1);

namespace model\mapping;

use InvalidArgumentException;
use model\abstract\AbstractMapping;

class ContactMessageMapping extends AbstractMapping{
    protected ?int $id = null;
    protected ?string $name = null;
    protected ?string $email = null;
    protected ?string $subject = null;
    protected ?string $message = null;
    protected ?string $ip_address = null;
    protected ?string $status = null; 
    protected ?string $created_at = null;

    public function getId(): ?int{
        return $this->id; 
    }

    public function setId(int $id): void{
        if($id <= 0){
            throw new InvalidArgumentException("L'identifiant doit être positif.");
        }
        $this->id = $id;
    }

    public function getName(): ?string{
        return $this->name;
    }

    public function setName(string $name):void{
        $name = trim($name);
        $length = mb_strlen($name);
        if ($length < 2 || $length > 100){
            throw new InvalidArgumentException('Le nom doit faire entre 2 et 100 caractères.');
        }
        $this->name = $name;  
    }

    public function getEmail(): ?string{
        return $this->email;
    }

    public function setEmail(string $email): void{
        $email = trim($email);
        if(filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 254){
            throw new InvalidArgumentException("L'email doit être valide et ne pas dépasser 254 caractères.");
        }
        $this->email = $email;
    }

    public function getSubject(): ?string{
        return $this->subject; 
    }

    public function setSubject(string $subject): void{
        // obligatoire pour un message de contact : 3 à 120 caractères
        $subject = trim($subject);
        $length = mb_strlen($subject);
        if ($length < 3 || $length > 120){
            throw new InvalidArgumentException('Le sujet doit faire entre 3 et 120 caractères.');
        }
        $this->subject = $subject;
    }

    public function getMessage(): ?string {
        return $this->message; 
    }

    public function setMessage(string $message):void {
        // entre 10 et 2000 cara 
        $message = trim($message);
        $length = mb_strlen($message);
        if ($length < 10 || $length > 2000){
            throw new InvalidArgumentException('Le message doit faire entre 10 et 2000 caractères.');
        }
        $this->message = $message;         
    }

    public function getIpAddress(): ?string{
        return $this->ip_address; 
    }

    public function setIpAddress(?string $ipAddress): void{
        // facultative ; si elle est fournie, IPv4 ou IPv6 valide
        if ($ipAddress !== null && filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
            throw new InvalidArgumentException("L'adresse IP est invalide.");
        }
        $this->ip_address = $ipAddress;
    }

    public function getStatus(): ?string{
        return $this->status;
    }

    public function setStatus(string $status):void {
        if (!in_array($status, ['new', 'read', 'processed'], true)){
            throw new InvalidArgumentException('Le statut du message ne peut être que new, read ou processed.');
        }
        $this->status = $status; 
    }

    public function getCreatedAt(): ?string{
        return $this->created_at;
    }

    public function setCreatedAt(?string $createdAt){
        $this->created_at = $createdAt;
    }
}