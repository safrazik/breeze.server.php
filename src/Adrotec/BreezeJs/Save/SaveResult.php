<?php

namespace Adrotec\BreezeJs\Save;

class SaveResult {
    
    private $entities = array();
    private $keyMappings = array();
//    private $errors = array();

    public function __construct(array $entities, array $keyMappings) {
        $this->entities = $entities;
        $this->keyMappings = $keyMappings;        
    }
    
    public function getEntities(){
        return $this->entities;
    }
    
    public function setEntities($entities){
        $this->entities = $entities;
    }
    
    public function getKeyMappings(){
        return $this->keyMappings;
    }
    
    public function setKeyMappings($keyMappings){
        $this->keyMappings = $keyMappings;
    }
    
}
