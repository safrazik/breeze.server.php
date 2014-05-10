<?php

namespace Adrotec\BreezeJs\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Adrotec\BreezeJs\Metadata\Metadata;
use Doctrine\ORM\Mapping\ClassMetadata;
//
use ODataProducer\Providers\Metadata\ResourceType;
use ODataProducer\Providers\Metadata\ResourceProperty;
use ODataProducer\Providers\Metadata\ResourceTypeKind;
use ODataProducer\Providers\Metadata\ResourcePropertyKind;
//
//
use Adrotec\BreezeJs\Metadata\StructuralType;
use Adrotec\BreezeJs\Metadata\DataType;
use Adrotec\BreezeJs\Metadata\DataProperty;
use Adrotec\BreezeJs\Metadata\NavigationProperty;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Adrotec\BreezeJs\MetadataInterceptorInterface;

class MetadataBuilder {

    private $entityManager;
    private $interceptor;

    private function isInheritanceEnabled() {
        return true;
    }

    public function __construct(EntityManager $entityManager, MetadataInterceptorInterface $interceptor = null) {
        $this->entityManager = $entityManager;
        $this->interceptor = $interceptor;
    }

    public function getEntityTypeName(\ReflectionClass $class) {
        return $class->getShortName() . ':#' . $this->getNamespace($class);
    }

    /**
     * @return Metadata build and return metadata
     */
    public function buildMetadata(array $classes = null) {
        $metadata = new Metadata();
        $metadata->metadataVersion = '1.0.5';
        $structuralTypes = array();
        $resourceEntityTypeMap = array();
        if ($classes && !empty($classes)) {
            $classMeta = array();
            foreach ($classes as $class) {
                $classMeta[] = $this->entityManager->getClassMetadata($class);
            }
        } else {
            $classMeta = $this->entityManager->getMetadataFactory()->getAllMetadata();
        }
        $i = 0;
        foreach ($classMeta as $meta) {
//            $className = $meta->getName();
            $structuralType = $this->createStructuralType($meta);
            if ($this->interceptor) {
                $this->interceptor->modifyStructuralType($structuralType);
            }
            $structuralTypes[] = $structuralType;
            $resourceEntityTypeMap[$structuralType->defaultResourceName] = $this->getEntityTypeName($meta->getReflectionClass());
        }


        $metadata->structuralTypes = $structuralTypes;
        $metadata->resourceEntityTypeMap = $resourceEntityTypeMap;

        return $metadata;
    }

    function getNamespace(\ReflectionClass $class) {
        $namespace = null;
        if ($this->interceptor) {
            $namespace = $this->interceptor->getNamespace($class);
        }
        if (!$namespace) {
            $namespace = $class->getNamespaceName();
        }
        return strtr($namespace, '\\', '.');
    }

    public function createStructuralType(ClassMetadata $meta) {
        $refClass = $meta->getReflectionClass();

        $structuralType = new StructuralType();
        $structuralType->reflectionClass = $refClass;
        $structuralType->shortName = $refClass->getShortName();
        $structuralType->defaultResourceName = $structuralType->shortName;
        if ($this->interceptor) {
            $structuralType->defaultResourceName = $this->interceptor->getDefaultResourceName($structuralType);
        }

        if ($this->isInheritanceEnabled()) {
            if ($meta->inheritanceType == ClassMetadataInfo::INHERITANCE_TYPE_JOINED || $meta->inheritanceType == ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE) {
                if (count($meta->parentClasses)) {
//                    $structuralType->parentClass = $meta->parentClasses[0];
                    $structuralType->baseTypeName = $this->getEntityTypeName(new \ReflectionClass($meta->parentClasses[0]));
                } else if (count($meta->subClasses)) {
//                    $structuralType->isAbstract = true;
                    $structuralType->isAbstract = false;
                }
            }
        }

        $dataProperties = array();
        $navigationProperties = array();

        foreach ($meta->associationMappings as $propertyName => $associationMapping) {
            $navigationProperty = $this->createNavigationProperty($structuralType, $associationMapping);
            if ($navigationProperty) {
                $navigationProperties[$propertyName] = $navigationProperty;
            }
        }

        foreach ($meta->fieldMappings as $propertyName => $fieldMapping) {
            $dataProperty = $this->createDataProperty($structuralType, $fieldMapping);
            if ($dataProperty instanceof DataProperty) {
                $dataProperties[$propertyName] = $dataProperty;
//                    $structuralType->addDataProperty($dataProperty);
            } else if ($dataProperty instanceof NavigationProperty) {
                $navigationProperties[$propertyName] = $dataProperty;
//                    $structuralType->addNavigationProperty($dataProperty);
            }
        }


        if (!empty($navigationProperties)) {
            foreach ($navigationProperties as $navigationName => $navigationProperty) {
                if (!empty($navigationProperty->foreignKeyNames)) {
                    foreach ($navigationProperty->foreignKeyNames as $foreignKeyFieldName) {
                        if (!isset($dataProperties[$foreignKeyFieldName])) {
                            $dataProperties[$foreignKeyFieldName] = $this->createDataProperty($structuralType, array('type' => 'integer', 'fieldName' => $foreignKeyFieldName, 'nullable' => true));
                        }
                    }
                }
            }
            $structuralType->navigationProperties = array_values($navigationProperties);
        }

        $structuralType->dataProperties = array_values($dataProperties);
        $structuralType->namespace = $this->getNamespace($refClass);

        switch ($meta->generatorType) {
            case ClassMetadataInfo::GENERATOR_TYPE_IDENTITY:
                $structuralType->autoGeneratedKeyType = 'Identity';
                break;
            case ClassMetadataInfo::GENERATOR_TYPE_CUSTOM:
            case ClassMetadataInfo::GENERATOR_TYPE_NONE:
                $structuralType->autoGeneratedKeyType = 'None';
                break;
            case ClassMetadataInfo::GENERATOR_TYPE_AUTO:
            case ClassMetadataInfo::GENERATOR_TYPE_SEQUENCE:
            case ClassMetadataInfo::GENERATOR_TYPE_TABLE:
            case ClassMetadataInfo::GENERATOR_TYPE_UUID:
                $structuralType->autoGeneratedKeyType = 'KeyGenerator';
                break;
        }

        return $structuralType;
    }

    function createDataProperty(StructuralType &$structuralType, $fieldMapping) {

        $dataProperty = new DataProperty();
        $dataProperty->structuralType = $structuralType;
        $dataProperty->name = $fieldMapping['fieldName'];
        $dataProperty->dataType = DataTypeMapper::fromDoctrineToOData($fieldMapping['type']);

        if (isset($fieldMapping['nullable'])) {
            $dataProperty->isNullable = $fieldMapping['nullable'];
        }
        if (isset($fieldMapping['id'])) {
            $dataProperty->isPartOfKey = $fieldMapping['id'];
        }
        if (isset($fieldMapping['length'])) {
            $dataProperty->maxLength = $fieldMapping['length'];
        }
        
        if ($this->isInheritanceEnabled()) {
            if (isset($fieldMapping['inherited'], $fieldMapping['declared']) && $fieldMapping['inherited']
            //&& $fieldMapping['declared'] != $classInfo->className
            ) {
                if($this->interceptor){
                    $this->interceptor->excludeProperty($dataProperty);
                }
                return false;
            }
        }

        return $dataProperty;
    }

    private function createForeignKeyFieldName($associationName) {
        return $associationName . 'Id';
    }

    public function getForeignKeyFieldName(array $associationMapping) {
        $fieldName = false;
        if (isset($associationMapping['joinColumns'])) {
            $meta = $this->entityManager->getClassMetadata($associationMapping['sourceEntity']);
            $associationMeta = $this->entityManager->getClassMetadata($associationMapping['targetEntity']);
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

    function createNavigationProperty(StructuralType &$structuralType, $associationMapping) {
//print_r($associationMapping);
//exit;
        $meta = $this->entityManager->getClassMetadata($associationMapping['sourceEntity']);
        $associationMeta = $this->entityManager->getClassMetadata($associationMapping['targetEntity']);

        $navigationProperty = new NavigationProperty();

        $navigationProperty->structuralType = $structuralType;

        $navigationProperty->name = $associationMapping['fieldName'];

        $navigationProperty->entityTypeName = $this->getEntityTypeName($associationMeta->getReflectionClass());

        $navigationProperty->isScalar = in_array((int) $associationMapping['type'], array(ClassMetadata::ONE_TO_ONE, ClassMetadata::MANY_TO_ONE));

        $isOwningSide = isset($associationMapping['isOwningSide']) ? $associationMapping['isOwningSide'] : false;

        if ($navigationProperty->isScalar && $isOwningSide) {
            $navigationProperty->foreignKeyNames = array();
            if ($fieldName = $this->getForeignKeyFieldName($associationMapping)) {
                
            } else if ($this->interceptor) {
                $fieldName = $this->interceptor->createVirtualForeignKeyProperty($navigationProperty);
            }
            if ($fieldName) {
                $navigationProperty->foreignKeyNames[] = $fieldName;
            }
        } else {
            
        }

        if ($navigationProperty->isScalar && $isOwningSide) {
            $entityName = $structuralType !== null ? $structuralType->shortName : $meta->getReflectionClass()->getShortName();
            $navigationProperty->associationName = 'FK_' . $entityName . '_' . $associationMeta->getReflectionClass()->getShortName();
        } else {
            $addAssociationName = true;
//            if (!$navigationProperty->isScalar && $this->isInheritanceEnabled()) {
//                if ($associationMeta->inheritanceType == ClassMetadataInfo::INHERITANCE_TYPE_JOINED || $associationMeta->inheritanceType == ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE) {
//                    if (count($associationMeta->parentClasses)) {
//                        if ($associationParentMeta = $this->entityManager->getClassMetadata($associationMeta->parentClasses[0])) {
//                            $addAssociationName = false;
//                            $navigationProperty->associationName = 'FK_' .
//                                    $associationParentMeta->getReflectionClass()->getShortName() . '_' . $meta->getReflectionClass()->getShortName();
//                        }
//                    }
//                }
//            }
            if ($addAssociationName) {
                $navigationProperty->associationName = 'FK_' . $associationMeta->getReflectionClass()->getShortName()
                        . '_' . $meta->getReflectionClass()->getShortName();
            }
        }

        return $navigationProperty;
    }

    /**
     * @return Metadata return singleton metadata
     */
    public function getMetadata() {
        
    }

}
