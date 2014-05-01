<?php

namespace BreezeJs\Serializer;

use BreezeJs\MetadataInterceptorInterface;
use BreezeJs\Metadata\StructuralType;
use BreezeJs\Metadata\NavigationProperty;
use BreezeJs\Metadata\DataProperty;
use JMS\Serializer\Serializer;
use BreezeJs\Doctrine\ORM\DataTypeMapper;
use BreezeJs\TextUtil;

class MetadataInterceptor implements MetadataInterceptorInterface {

    private $serializer;
    private $metaFactory;
    private $foreignKeys = array();

    public function __construct(Serializer $serializer) {
        $this->serializer = $serializer;
        $this->metaFactory = $this->serializer->getMetadataFactory();
    }

    public function modifyStructuralType(StructuralType &$structuralType, \ReflectionClass $class) {
        $meta = $this->metaFactory->getMetadataForClass($class->getName());
        $availableProperties = array();
        $fkFieldNames = array();
        foreach (array('navigationProperties', 'dataProperties') as $i => $properties) {
            if (!$structuralType->$properties) {
                continue;
            }
            $processedProperties = array();
            foreach ($structuralType->$properties as $i => &$property) {
                if (isset($meta->propertyMetadata[$property->name])) {
                    $availableProperties[] = $property->name;
                    if ($meta->propertyMetadata[$property->name]->serializedName) {
                        $property->name = $meta->propertyMetadata[$property->name]->serializedName;
                    }
                    $processedProperties[] = $property;
                    if ($properties == 'navigationProperties') {
                        $fkey = $property->entityTypeName.'_'.$property->name;
//                        echo $fkey.'<br>';
                        if (isset($this->foreignKeys[$fkey])) {
                            $fkFieldNames[] = $this->foreignKeys[$fkey];
                        }
                    }
                } else if ($properties == 'dataProperties') {
                    if (in_array($property->name, $fkFieldNames)) {
                        $processedProperties[] = $property;
                    }
                }
            }
            $structuralType->$properties = $processedProperties;
        }

        foreach ($meta->propertyMetadata as $propertyName => $propertyMeta) {
            if (!$propertyMeta->reflection || in_array($propertyName, $availableProperties)) {
                continue;
            }
            $dataProperty = new DataProperty();
            $dataProperty->name = $propertyMeta->serializedName ? $propertyMeta->serializedName : $propertyName;
            if ($propertyMeta->type && isset($propertyMeta->type['name'])) {
                $type = $propertyMeta->type['name'];
                $dataProperty->dataType = DataTypeMapper::fromDoctrineToOData($type, false);
            }
            if (!$dataProperty->dataType) {
                $dataProperty->dataType = DataTypeMapper::fromDoctrineToOData('string');
            }
            $structuralType->dataProperties[] = $dataProperty;
        }

        return $structuralType;
//        print_r($meta);
//        exit;
    }

    public function createVirtualForeignKeyProperty(NavigationProperty $navigationProperty) {
        $fkey = $navigationProperty->entityTypeName . '_' . $navigationProperty->name;
//        echo 'FFF: '.$fkey.'<br>';
        $foreignKeyProperty = $navigationProperty->name . 'Id';
        $this->foreignKeys[$fkey] = $foreignKeyProperty;
        return $foreignKeyProperty;
    }

    public function getDefaultResourceName(StructuralType $structuralType) {
        return TextUtil::pluralize($structuralType->shortName);
    }

    public function getNamespace(\ReflectionClass $class) {
        return $class->getNamespaceName();
    }

}
