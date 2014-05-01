<?php

namespace BreezeJs;

class QueryResult {

    private $results = array();
    private $inlineCount;

    public function __construct($results = array(), $inlineCount = null) {
        $this->results = $results;
        $this->inlineCount = null;
    }
    
    public function getResults(){
        return $this->results;
    }
    
    public function setResults($results){
        $this->results = $results;
        return $this;
    }
    
    public function getInlineCount(){
        return $this->inlineCount;
    }
    
    public function setInlineCount($inlineCount){
        $this->inlineCount = $inlineCount;
        return $this;
    }
}
