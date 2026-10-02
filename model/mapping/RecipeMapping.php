<?php
// path: model/mapping/RecipeMapping.php
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
    protected ?string $updated_at = null;
    protected ?float $average_rating = null;
    protected ?int $rating_count = null;
    protected ?int $user_rating = null;
    protected ?string $category_titles = null; 
    

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

    public function getDifficultyLabel(): string{
        return match ($this->difficulty) {
            'easy' => 'Facile',
            'medium' => 'Moyen',
            'hard' => 'Difficile',
            default => '',
        };
    }

    public function getDescription(): ?string{
        return $this->description; 
    }

    public function setDescription(string $description):void{
        $description = trim($description);
        if($description === ''){
            throw new Exception('La description ne peut être vide');
        }
        $this->description = $description; 
    }

    public function getMainImage(): ?string{
        return $this->main_image; 
    }

    public function setMainImage(string $mainImage):void{
        $mainImage = trim($mainImage);
        $length = mb_strlen($mainImage);
        if($length === 0 || $length > 500){
            throw new Exception("Le chemin de l'image doit faire entre 1 et 500 caractères");
        }
        $this->main_image = $mainImage;
    }

    public function getServings(): ?int{
        return $this->servings; 
    }

    public function setServings(int $servings):void{
        if($servings <= 0){
            throw new Exception("Le nombre de portions doit être supérieur à 0");
        }
        $this->servings = $servings;
    }

    public function getAuthorId(): ?int{
        return $this->author_id; 
    }

    public function setAuthorId(int $authorId): void{
        if($authorId <= 0){
            throw new Exception("L'identifiant de l'auteur doit être positif");
        }
        $this->author_id = $authorId;
    }

    public function getCreatedAt(): ?string{
        return $this->created_at; 
    }

    public function setCreatedAt(string $createdAt): void{
        $this->created_at = $createdAt;
    }

    public function getUpdatedAt(): ?string{
        return $this->updated_at; 
    }

    public function setUpdatedAt(string $updatedAt): void{
        $this->updated_at = $updatedAt;
    }

    public function setAverageRating(?string $averageRating): void {
        $this->average_rating = $averageRating === null ? null : (float) $averageRating;
    }

    public function setRatingCount(int $ratingCount): void{
        if ($ratingCount < 0){
            throw new Exception("Le nombre de votes ne peut pas être négatif");
        }
        $this->rating_count = $ratingCount; 
    }

    public function setUserRating(?int $userRating): void{
        if ($userRating !== null && ($userRating < 1 || $userRating > 5)){
            throw new Exception("La note doit être comprise entre 1 et 5");
        }
        $this->user_rating = $userRating; 
    }

    public function getAverageRating(): ?float{
        return $this->average_rating;
    }

    public function getRatingCount(): ?int{
        return $this->rating_count; 
    }

    public function getUserRating(): ?int{
        return $this->user_rating;
    }

    public function getFormattedAverage(): string{
        if($this->average_rating === null){
            return '';
        }
        return number_format($this->average_rating, 1,',','');
    }

    public function getCategoryTitles(): ?string{
        return $this->category_titles; 
    }

    public function setCategoryTitles(?string $categoryTitles): void{
        $this->category_titles = $categoryTitles; 
    }
}
