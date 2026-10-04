<?php
// path: model/interface/UserInterface.php
declare(strict_types=1); 

namespace model\interface;

interface UserInterface {
    //verif id + session
    public function connect(array $tab):bool ;

    // détruit session 
    public function disconnect():bool;
}
