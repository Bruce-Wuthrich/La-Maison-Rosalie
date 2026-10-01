<?php

declare(strict_types=1);

namespace model\mapping;

use Exception; 
use model\abstract\AbstractMapping;

class RecipeMapping extends AbstractMapping{
    protected ?int $id = null;
    protected ?string $title = null;
    protected ?string $slug = null;
    protected ?int $prep_time_minutes = null;
    protected ?int $cook_time_minutes = null;
    protected ?string $difficulty = null;
    protected ?string $description = null;
    protected ?string $main_image = null;
    protected ?int $servings = null;
    protected ?int $author_id = null;
    protected ?string $created_at = null;


    public function getId(): ?int{
        return $this->id; 
    }

    public function setId(int $id): void{
        if($id<=0){
            throw new Exception("L'identifiant doit être positif");
        }
        $this->id = $id;
    }

    public function getTitle(): ?string{
        return $this->title;
    }

    public function setTitle(string $title):void{
        $title = trim($title);
        $length = mb_strlen($title);
        if($length < 3 || $length > 150){
            throw new Exception('Le titre doit faire entre 3 et 150 caractères');
        }
        $this->title = $title; 
    }

    public function getSlug(): ?string{
        return $this->slug;
    }

    public function setSlug(string $slug):void{
        if(!preg_match('/^[a-z0-9-]{1,170}$/', $slug)){
            throw new Exception('Slug invalide');
        }
        $this->slug = $slug;
    }

    public function getPrepTimeMinutes(): ?int{
        return $this->prep_time_minutes;
    }

    public function setPrepTimeMinutes(int $minutes):void{
        if ($minutes < 0){
            throw new Exception('Le temps de préparation ne peut pas être négatif');
        }
        $this->prep_time_minutes = $minutes;
    }

    public function getCookTimeMinutes(): ?int{
        return $this->cook_time_minutes;
    }

    public function setCookTimeMinutes(int $minutes):void{
        if ($minutes < 0){
            throw new Exception('Le temps de cuisson ne peut pas être négatif');
        }
        $this->cook_time_minutes = $minutes;
    }

    public function getTotalTime(): int
    {
        return ($this->prep_time_minutes ?? 0) + ($this->cook_time_minutes ?? 0);
    }

    public function getDifficulty(): ?string{
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty):void{
        if (!in_array($difficulty, ['easy', 'medium', 'hard'], true)){
            throw new Exception('La difficulté doit être easy, medium ou hard');
        }
        $this->difficulty = $difficulty;
    }

    public function getDescription(): ?string{
        return $this->description; 
    }

    public function setDescription(string $description):void{
        if($description === ''){
            throw new Exception('La description ne peut être vide');
        }
        $this->description = $description; 
    }

    public function getMainImage(): ?string{
        return $this->main_image; 
    }

    public function setMainImage(string $mainImage):void{
        if(mb_strlen($main_image)>500){
            throw new Exception('');
        }
        
    }
   

}
