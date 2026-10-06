<?php
// path: model/mapping/CategoryMapping.php
declare(strict_types=1);

namespace model\mapping;

use InvalidArgumentException;
use model\abstract\AbstractMapping;

class CategoryMapping extends AbstractMapping{
    protected ?int $id = null;
    protected ?string $title = null;
    protected ?string $slug = null;
    protected ?string $description = null;

    public function getId(): ?int{
            return $this->id; 
        }
    
    public function setId(int $id): void{
        if($id<=0){
            throw new InvalidArgumentException("L'identifiant de catégorie doit être positif");
            }
        $this->id = $id;
        }
    
    public function getTitle(): ?string{
        return $this->title;
    }

    public function setTitle(string $title):void {
        $title = trim($title);
        $length = mb_strlen($title); 
        if($length === 0 || $length > 80){
            throw new InvalidArgumentException("Le titre de catégorie doit faire entre 1 et 80 caractères");
        }
        $this->title = $title;
    }

    public function getSlug(): ?string{
        return $this->slug;
    }

    public function setSlug(string $slug):void{
        if(!preg_match('/^[a-z0-9-]{1,100}$/', $slug)){
            throw new InvalidArgumentException('Slug invalide');
        }
        $this->slug = $slug;
    }

    public function getDescription(): ?string{
        return $this->description; 
    }

    public function setDescription(?string $description):void{
        if($description !== null && mb_strlen($description) > 500){
            throw new InvalidArgumentException('La description ne peut dépasser 500 caractères');
        }
        $this->description = $description; 
    }
}