<?php

namespace BreezeJs;

//use ODataProducer\Providers\Metadata\ResourceType;
use ODataProducer\Providers\Metadata\ResourceProperty;
use ODataProducer\Providers\Metadata\ResourceTypeKind;
use ODataProducer\Providers\Metadata\ResourcePropertyKind;
use Doctrine\ORM\Mapping\ClassMetadata;


use BreezeJs\Doctrine\ORM\DataTypeMapper;



class ResourceType extends \ODataProducer\Providers\Metadata\ResourceType {

    const LEVEL_MAX = 5;

    private static $resourceTypes = array();
    private static $level = 1;

    static public function getForeignKeyFieldName(array $associationMapping, $entityManager) {
        $fieldName = false;
        if (isset($associationMapping['joinColumns'])) {
            $meta = $entityManager->getClassMetadata($associationMapping['sourceEntity']);
            $associationMeta = $entityManager->getClassMetadata($associationMapping['targetEntity']);
            foreach ($associationMapping['joinColumns'] as $column) {
                foreach ($meta->fieldMappings as $fieldMapping) {
                    if ($fieldMapping['columnName'] == $column['name']) {
                        $fieldName = $fieldMapping['fieldName'];
                    }
                }
                if (!$fieldName) {
//                    if($this->interceptor){
//                        $this->interceptor->createVirtualForeignKeyProperty($navigationProperty);
//                    }
//                    $fieldName = $this->createForeignKeyFieldName($associationMapping['fieldName']);
                }
            }
        }
        return $fieldName;
    }
    
    public static function getResourceType($entityManager, ClassMetadata $meta, $level = 0) {

        if (isset(self::$resourceTypes[$meta->getName()])) {
            return self::$resourceTypes[$meta->getName()];
        }

        $refClass = $meta->getReflectionClass();

        $namespace = null;

        $resourceType = new ResourceType($refClass, ResourceTypeKind::ENTITY, $refClass->getShortName(), $namespace);

        $addedProperties = array();
        foreach ($meta->fieldMappings as $fieldName => $data) {
            $type = $resourceType->getPrimitiveResourceType(DataTypeMapper::fromDoctrineToOData($data['type']));
            $kind = ResourcePropertyKind::PRIMITIVE;
            $resourceProperty = new ResourceProperty($fieldName, null, $kind, $type);
            $resourceType->addProperty($resourceProperty);
            $addedProperties[$fieldName] = $resourceProperty;
        }

        if ($level < self::LEVEL_MAX) {
            foreach ($meta->associationMappings as $fieldName => $data) {
                if ($associationMeta = $entityManager->getClassMetadata($data['targetEntity'])) {
                    $type = self::getResourceType($entityManager, $associationMeta, self::$level++);
                    $kind = ResourcePropertyKind::RESOURCE_REFERENCE;
                    $resourceProperty = new ResourceProperty($fieldName, null, $kind, $type);
                    $resourceType->addProperty($resourceProperty);

                    $fkFieldName = self::getForeignKeyFieldName($data, $entityManager);
                    if(!$fkFieldName){
                        $fkFieldName = $data['fieldName'].'Id';
                    }
                    if ($fkFieldName) {
                        if (!isset($addedProperties[$fkFieldName])) {
                            //*
                              $type = $resourceType->getPrimitiveResourceType(DataTypeMapper::fromDoctrineToOData('integer'));
                              $kind = ResourcePropertyKind::PRIMITIVE;
                              $resourceProperty = new ResourceProperty($fkFieldName, null, $kind, $type);
                              $resourceType->addProperty($resourceProperty);
                              $addedProperties[$fieldName] = $resourceProperty;
                             //*/
                        }
                    }
//                        break;
                }
            }
        }

        self::$level = 1;
        return self::$resourceTypes[$meta->getName()] = $resourceType;
    }

}
