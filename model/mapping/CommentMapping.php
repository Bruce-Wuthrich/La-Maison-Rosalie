<?php
// path: model/mapping/CommentMapping.php

declare(strict_types=1);

namespace model\mapping;

use Exception;
use model\abstract\AbstractMapping;

class CommentMapping extends AbstractMapping{
    protected ?int $id = null;
    protected ?int $author_id = null;
    protected ?string $author_username = null;
    protected ?int $recipe_id = null;
    protected ?string $subject = null;
    protected ?string $message = null;
    protected ?string $publication_status = null;
    protected ?string $created_at = null;

    public function getId(): ?int{
        return $this->id; 
    }

    public function setId(int $id): void{
        if($id <= 0){
            throw new Exception("L'identifiant doit être positif.");
        }
        $this->id = $id;
    }

    public function getAuthorId(): ?int{
        return $this->author_id;
    }

    public function setAuthorId(int $authorId): void{
        if($authorId <= 0){
            throw new Exception("L'identifiant de l'auteur doit être positif.");
        }
        $this->author_id = $authorId;
    }

    public function getAuthorUsername(): ?string{
        return $this->author_username;
    }

    public function setAuthorUsername(string $authorUsername): void{
        $this->author_username = $authorUsername;
    }

    public function getRecipeId(): ?int{
        return $this->recipe_id;
    }

    public function setRecipeId(int $recipeId):void {
        if($recipeId <= 0){
            throw new Exception("L'identifiant de la recette doit être positif.");
        }
        $this->recipe_id = $recipeId;
    }

    public function getSubject(): ?string{
        return $this->subject; 
    }

    public function setSubject(?string $subject): void{
        // sujet vide ou juste espace devient null 
        if ($subject !== null){
            $subject = trim($subject);
            if ($subject === ''){
                $subject = null;
            } elseif (mb_strlen($subject) > 120){
                throw new Exception('Le sujet ne doit pas dépasser 120 caractères.');
            }
        }
        $this->subject = $subject; 
    }

    public function getMessage(): ?string {
        return $this->message; 
    }

    public function setMessage(string $message):void {
        // entre 3 et 500 cara 
        $message = trim($message);
        $length = mb_strlen($message);
        if ($length < 3 || $length > 500){
            throw new Exception('Le message doit faire entre 3 et 500 caractères.');
        }
        $this->message = $message;         
    }

    public function getPublicationStatus(): ?string{
        return $this->publication_status;
    }

    public function setPublicationStatus(string $publicationStatus):void {
        if (!in_array($publicationStatus, ['pending', 'published', 'hidden'], true)){
            throw new Exception('Le status doit être pending, published ou hidden.');
        }
        $this->publication_status = $publicationStatus;
    }

    public function getCreatedAt(): ?string{
        return $this->created_at;
    }

    public function setCreatedAt(string $createdAt): void{
        $this->created_at = $createdAt; 
    }
}