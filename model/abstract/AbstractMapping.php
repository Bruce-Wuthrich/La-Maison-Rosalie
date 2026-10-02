<?php
// path : \model\abstract\AbstractMapping.php

namespace model\abstract; 

abstract class AbstractMapping{
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
}