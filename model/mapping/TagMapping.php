<?php
// path: model/mapping/TagMapping.php
declare(strict_types=1);

namespace model\mapping;

use InvalidArgumentException;
use model\abstract\AbstractMapping;

class TagMapping extends AbstractMapping
{
    protected ?int $id = null;
    protected ?string $name = null;
    protected ?string $slug = null;
    protected int $recipe_count = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("L'identifiant du tag doit être positif.");
        }
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 80) {
            throw new InvalidArgumentException('Le nom du tag doit faire entre 1 et 80 caractères.');
        }
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        if (preg_match('/^[a-z0-9-]{1,100}$/', $slug) !== 1) {
            throw new InvalidArgumentException('Slug de tag invalide.');
        }
        $this->slug = $slug;
    }

    public function getRecipeCount(): int
    {
        return $this->recipe_count;
    }

    public function setRecipeCount(int $recipeCount): void
    {
        if ($recipeCount < 0) {
            throw new InvalidArgumentException('Le nombre de recettes ne peut pas être négatif.');
        }
        $this->recipe_count = $recipeCount;
    }
}
