<?php
namespace App\library;

class RouterPOO{
    /**
     *
     * @var Route[] 
     */
    protected $routes = [];
    
    const NO_ROUTE = 1;
    
    public function addRoute(Route $route){
        if (!in_array($route, $this->routes)){
            $this->routes[] = $route;
        }
    }
    
    public function getRoute($url){
        foreach ($this->routes as $route){
            // Si la route correspond à l'URL
            if (($varsValues = $route->match($url)) !== false){
                // Si elle a des variables
                if ($route->hasVars()){
                    $varsNames = $route->varsNames();
                    $listVars = [];

                    // On crée un nouveau tableau clé/valeur
                    foreach ($varsValues as $key => $match){
                        // La première valeur contient entièrement la chaine capturée
                        if ($key !== 0){
                          $listVars[$varsNames[$key - 1]] = $match;
                        }
                    }
                    // On assigne ce tableau de variables à la route
                    $route->setVars($listVars);
                }
                return $route;
            }
        }
        throw new \RuntimeException('Aucune route ne correspond à l\'URL', self::NO_ROUTE);
    }
    
    public function getRoutes() {
        return $this->routes;
    }
     
    public function run(){
        // vérifie l'url demandé
        //Récupère dynamiquement l'URL relative demandée indépendamment du dossier contenant le site 
        $contextDocumentRoot = $_SERVER["CONTEXT_DOCUMENT_ROOT"];      
        $rootDir = str_replace('\\', '/', realpath(__DIR__.'/../'));
        $relativeRootDir = str_replace($contextDocumentRoot, '', $rootDir);   
        
        $urlRequested = str_replace($relativeRootDir, '', $_SERVER['REQUEST_URI']);
        
        // vérifie si une de ces routes match avec l'url
        $matches = false;
        
        foreach($this->routes as $route){
            $matches = $route->match($urlRequested);
            // Si match, appelle le controller et l'action correspondant à la route
            if ($matches !== false){
                $controllerName = 'App\Controller\\' . ucfirst($route->getController()) . 'Controller';
                $action = $route->getAction();
                $controller = new $controllerName();
                if(isset($matches[1]) && isset($matches[2]) && isset($matches[3])) {
                    $controller->$action($matches[1], $matches[2], $matches[3]);
                }elseif(isset($matches[1]) && isset($matches[2])){
                    $controller->$action($matches[1], $matches[2]);
                }elseif(isset($matches[1])){
                    $controller->$action($matches[1]);
                }else{
                    $controller->$action();    
                }
                exit;
            }  
        }    
        if($matches === false){
            $controller = new \App\Controller\MainController();
            $controller->notFound();
            exit;
        }
    }
}