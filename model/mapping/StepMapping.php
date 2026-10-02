<?php
// path: model/mapping/StepMapping.php
declare(strict_types=1);

namespace model\mapping;

use Exception; 
use model\abstract\AbstractMapping;

class StepMapping extends AbstractMapping{
    protected ?int $id = null; 
    protected ?int $recipe_id = null; 
    protected ?int $step_number = null; 
    protected ?string $title = null; 
    protected ?string $instructions = null; 
    protected ?string $image = null; 
    
    public function getId(): ?int{
            return $this->id; 
        }
    
    public function setId(int $id): void{
        if($id<=0){
            throw new Exception("L'identifiant doit être positif");
            }
        $this->id = $id;
        }

    public function getRecipeId(): ?int{
        return $this->recipe_id; 
        }
    
    public function setRecipeId(int $recipeId): void{
        if($recipeId <= 0){
            throw new Exception("L'identifiant de la recette doit être positif");
            }
            $this->recipe_id = $recipeId;
        }

    public function getStepNumber(): ?int{
        return $this->step_number; 
        }
    
    public function setStepNumber(int $stepNumber): void{
        if($stepNumber <= 0){
            throw new Exception("Le numéro d'étape doit être positif");
            }
            $this->step_number = $stepNumber;
        }

    
    public function getTitle(): ?string{
        return $this->title;
    }

    public function setTitle(string $title):void{
        $title = trim($title);
        $length = mb_strlen($title); 
        if($length === 0 || $length > 120){
            throw new Exception("Le titre de l'étape doit faire entre 1 et 120 caractères");
        }
        $this->title = $title;
    }

    public function getInstructions(): ?string{
        return $this->instructions;
    }

    public function setInstructions(string $instructions):void{
        $instructions = trim($instructions);
        $length = mb_strlen($instructions); 
        if($length === 0 || $length > 1000){
            throw new Exception("Les instructions de l'étape doivent faire entre 1 et 1000 caractères");
        }
        $this->instructions = $instructions;
    }

    public function getImage(): ?string{
        return $this->image;
    }

    public function setImage(string $image):void{
        $image = trim($image);
        $length = mb_strlen($image); 
        if($length === 0 || $length > 500){
            throw new Exception("Le chemin de l'image doit faire entre 1 et 500 caractères");
        }
        $this->image = $image; 
    }
}

