<?php


namespace App\Model\Entity;


abstract class AbstractEntity
{

    public function hydrate($data = null){
        foreach ($data as $key => $value) {
            // On récupère le nom du setter correspondant à l'attribut.
            $method = 'set'.ucfirst(ucwords(str_replace('_', '', $key)));

            // Si le setter correspondant existe.
            if (method_exists($this, $method))
            {
                // On appelle le setter.
                $this->$method($value);
            }
        }
    }

}