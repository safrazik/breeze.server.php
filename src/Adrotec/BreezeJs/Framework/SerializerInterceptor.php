<?php

namespace Adrotec\BreezeJs\Framework;

use Adrotec\BreezeJs\Serializer\MetadataInterceptor as SerializerInterceptorBase;

class SerializerInterceptor extends SerializerInterceptorBase
{
    private $resources = array();
    
    public function setResources($resources){
        $this->resources = array_flip($resources);
    }
    
    public function getDefaultResourceName(\Adrotec\BreezeJs\Metadata\StructuralType $structuralType)
    {
        $className = $structuralType->reflectionClass->getName();
        if(isset($this->resources[$className])){
            return $this->resources[$className];
        }
        return parent::getDefaultResourceName($structuralType);
    }
    
}
