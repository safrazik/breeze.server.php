<?php

namespace Adrotec\BreezeJs;

use Adrotec\BreezeJs\MetadataInterceptorInterface;

use Adrotec\BreezeJs\Validator\ValidatorInterceptor;

use Adrotec\BreezeJs\Serializer\MetadataInterceptor as SerializerInterceptor;

class MetadataInterceptor implements MetadataInterceptorInterface {
    
    private $interceptors;

    public function __construct(array $interceptors = null) {
        $this->interceptors = $interceptors;
    }
    
    public function add($interceptor){
        $this->interceptors[] = $interceptor;
        return $this;
    }
    
    protected function delegate($method, $args, $return = true){
        if(empty($this->interceptors)){
            return;
        }
        foreach ($this->interceptors as $interceptor){
            if(method_exists($interceptor, $method)){
                $returnValue = call_user_func_array(array($interceptor, $method), $args);
                if($return){
                    return $returnValue;
                }
            }
        }
    }
    
    public function createVirtualForeignKeyProperty(Metadata\NavigationProperty $navigationProperty) {
        return $this->delegate(__FUNCTION__, array($navigationProperty));
    }

    public function excludeProperty(Metadata\Property $property) {
        return $this->delegate(__FUNCTION__, array($property));
    }

    public function getDefaultResourceName(Metadata\StructuralType $structuralType) {
        return $this->delegate(__FUNCTION__, array($structuralType));
    }

    public function getNamespace(\ReflectionClass $class) {
        return $this->delegate(__FUNCTION__, array($class));
    }

    public function modifyStructuralType(Metadata\StructuralType &$structuralType) {
//        $this->interceptors[0]->modifyStructuralType($structuralType);
//        $this->interceptors[1]->modifyStructuralType($structuralType);
        $this->delegate(__FUNCTION__, array(&$structuralType), false);
    }

}