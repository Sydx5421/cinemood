<?php
namespace App\Library;

class Route{
    
    protected $action;
    protected $controller;
    protected $url;
    protected $varsNames;
    protected $vars = [];
    
    
    public function __construct($url, $controller, $action, array $varsNames = []){
        $this->setUrl($url);
        $this->setController($controller);
        $this->setAction($action);
        $this->setVarsNames($varsNames);
    }

    public function hasVars(){
        return !empty($this->varsNames);
    }

    public function match($url)
    {
        if (preg_match('`^'.$this->url.'$`', $url, $matches))
        {
            return $matches;
        }
        else
        {
            return false;
        }
    }
    
    public function getAction() {
        return $this->action;
    }

    public function getController() {
        return $this->controller;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getVarsNames() {
        return $this->varsNames;
    }

    public function getVars() {
        return $this->vars;
    }

    // ********** SETTERS ************
    
    public function setAction($action) {
        $this->action = $action;
        return $this;
    }

    public function setController($controller) {
        $this->controller = $controller;
        return $this;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    public function setVarsNames($varsNames) {
        $this->varsNames = $varsNames;
        return $this;
    }

    public function setVars($vars) {
        $this->vars = $vars;
        return $this;
    }
    
}