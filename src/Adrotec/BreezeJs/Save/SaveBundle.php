<?php

namespace Adrotec\BreezeJs\Save;

class SaveBundle {
    
    private $entities = array();
    
    public function __construct() {
    }
    
    public function setEntities($entities){
        $this->entities = $entities;
    }
    
    public function getEntities(){
        return $this->entities;
    }
    
}
