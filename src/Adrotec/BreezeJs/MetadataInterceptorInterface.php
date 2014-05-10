<?php

namespace Adrotec\BreezeJs;

use Adrotec\BreezeJs\Metadata\StructuralType;

use Adrotec\BreezeJs\Metadata\Property;
use Adrotec\BreezeJs\Metadata\DataProperty;
use Adrotec\BreezeJs\Metadata\NavigationProperty;

interface MetadataInterceptorInterface {
    
    function modifyStructuralType(StructuralType &$structuralType);
    
    function createVirtualForeignKeyProperty(NavigationProperty $navigationProperty);
    
    function getDefaultResourceName(StructuralType $structuralType);
    
    function getNamespace(\ReflectionClass $class);
    
    function excludeProperty(Property $property);
    
}
