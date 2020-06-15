<?php

namespace App\Library;

class Autoloader
{
    static function autoload($class)
    {
        $className = $class;
        $class = str_replace('\\', '/', $class);
        $class = str_replace('App/', '', $class);
        $class .= '.php';
        $class = __DIR__ . '/../' . $class;/*On part du dossier courant pour être sur de l'itinairaire*/
        
        if(file_exists($class)){
            require $class;
        }
    }
    
    static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
}
