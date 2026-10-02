<?php
//path: model/interface/ManagerInterface.php
declare(strict_types=1); 

namespace model\interface;

use model\MyPDO;

interface ManagerInterface{
    public function __construct(MyPDO $connect);
}