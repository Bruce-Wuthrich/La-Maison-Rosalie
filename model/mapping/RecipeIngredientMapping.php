<?php
// path: model/mapping/RecipeIngredientMapping.php
declare(strict_types=1);

namespace model\mapping;

use Exception; 
use model\abstract\AbstractMapping;

class RecipeIngredientMapping extends AbstractMapping{
    protected ?int $id = null;
    protected ?int $recipe_id = null;
    protected ?int $ingredient_id = null;
    protected ?string $ingredient_name = null;
    protected ?string $quantity = null;
    protected ?string $unit = null;
    protected ?string $details = null;
    protected ?int $sort_order = null;

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
        if($recipeId<=0){
            throw new Exception("L'identifiant de la recette doit être positif");
        }
        $this->recipe_id = $recipeId;
    }

    public function getIngredientId(): ?int{
        return $this->ingredient_id; 
    }

    public function setIngredientId(int $ingredientId): void{
        if($ingredientId<=0){
            throw new Exception("L'identifiant de l'ingrédient doit être positif");
        }
        $this->ingredient_id = $ingredientId;
    }

    public function getIngredientName(): ?string{
        return $this->ingredient_name; 
    }

    public function setIngredientName(string $ingredientName):void{
        $ingredientName = trim($ingredientName);
        $length = mb_strlen($ingredientName);
        if($length === 0 || $length > 120){
            throw new Exception("Le nom de l'ingrédient doit faire entre 1 et 120 caractères");
        }
        $this->ingredient_name = $ingredientName;
    }

    public function getQuantity(): ?string{
        return $this->quantity;
    }

    public function setQuantity(?string $quantity):void{
        $this->quantity = $quantity;
    }

    public function getUnit(): ?string{
        return $this->unit;
    }

    public function setUnit(?string $unit) : void {
        if ($unit !== null && mb_strlen($unit) > 40) {
            throw new Exception("L'unité ne doit pas dépasser 40 caractères");
        }
        $this->unit = $unit;
    }

    public function getDetails(): ?string {
        return $this->details;
    }

    public function setDetails(?string $details): void {
        if ($details !== null && mb_strlen($details) > 120){
            throw new Exception("Les précisions ne doivent pas dépasser les 120 caractères");
        }
        $this->details = $details;
    }

    public function getFormattedQuantity(): string {
        if ($this->quantity === null){
            return '';
        }
        $quantity = rtrim(rtrim($this->quantity, '0'), '.');
        return str_replace('.',',', $quantity);
    }

    public function getSortOrder(): ?int{
        return $this->sort_order; 
    }

    public function setSortOrder(int $sortOrder): void{
        if($sortOrder<=0){
            throw new Exception("L'ordre d'affichage doit être positif");
        }
        $this->sort_order = $sortOrder;
    }
}