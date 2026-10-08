<?php

// path: model/mapping/RatingMapping.php

declare(strict_types=1);

namespace model\mapping;

use InvalidArgumentException; 
use model\abstract\AbstractMapping;

class RatingMapping extends AbstractMapping{
    protected ?int $id = null; 
    protected ?int $user_id = null; 
    protected ?int $recipe_id = null; 
    protected ?int $rating = null; 
    protected ?string $rated_at = null;  

    public function getId(): ?int{
        return $this->id; 
    }

    public function setId(int $id): void{
        if($id <= 0){
            throw new InvalidArgumentException("L'identifiant doit être positif.");
        }
        $this->id = $id;
    }

    public function getUserId(): ?int{
        return $this->user_id; 
    }

    public function setUserId(int $userId): void{
        if($userId <= 0){
            throw new InvalidArgumentException("L'identifiant de l'utilisateur doit être positif.");
        }
        $this->user_id = $userId;
    }

    public function getRecipeId(): ?int{
        return $this->recipe_id; 
    }

    public function setRecipeId(int $recipeId): void{
        if($recipeId <= 0){
            throw new InvalidArgumentException("L'identifiant de la recette doit être positif.");
        }
        $this->recipe_id = $recipeId;
    }

    public function getRating(): ?int{
        return $this->rating; 
    }

    public function setRating(int $rating): void{
        if($rating < 1 || $rating > 5){
            throw new InvalidArgumentException("La note doit se situer entre 1 et 5.");
        }
        $this->rating = $rating;
    }

    public function getRatedAt(): ?string{
        return $this->rated_at; 
    }

    public function setRatedAt(string $ratedAt): void{
        $this->rated_at = $ratedAt;
    }
}