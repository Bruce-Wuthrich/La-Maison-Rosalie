<?php
// path: model/abstract/AbstractMapping.php

declare(strict_types=1); 

namespace model\abstract; 

use JsonSerializable; 

abstract class AbstractMapping implements JsonSerializable{
    public function __construct(array $datas){
        $this->hydrate($datas);
    }

    protected function hydrate(array $datas):void{
        foreach ($datas as $key => $value) {
            $setterName = 'set' . str_replace('_', '', ucwords($key,'_')); 

            if (method_exists($this, $setterName)){
                $this->$setterName($value);
            }
        }
    } 

    // toutes propriété de l'object en tab 
    public function toArray(): array{
        return get_object_vars($this);
    }

    //appel auto par json_encode
    public function jsonSerialize(): array{
        return $this->toArray();
    }
}