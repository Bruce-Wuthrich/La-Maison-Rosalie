<?php

declare(strict_types=1);

if ($page === 'a-propos'){
    require RACINE_PATH . '/view/PublicView/AboutView.php'; 
} else{
    require RACINE_PATH . '/view/PublicView/homepageView.php';
}
