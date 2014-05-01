<?php

namespace BreezeJs;

use BreezeJs\Metadata\StructuralType;
use BreezeJs\Metadata\NavigationProperty;

interface MetadataInterceptorInterface {
    
    function modifyStructuralType(StructuralType &$structuralType, \ReflectionClass $class);
    
    function createVirtualForeignKeyProperty(NavigationProperty $navigationProperty);
    
    function getDefaultResourceName(StructuralType $structuralType);
    
    function getNamespace(\ReflectionClass $class);
    
}
