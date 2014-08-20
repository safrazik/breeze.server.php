<?php

namespace Adrotec\BreezeJs\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Adrotec\BreezeJs\Metadata\Metadata;
use Adrotec\BreezeJs\Save\SaveBundle;
use Adrotec\BreezeJs\Save\SaveResult;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\Proxy\Proxy as ORMProxy;

class SaveContextProvider {

    const PROPERTY_TYPE_NONE = 0;
    const PROPERTY_TYPE_PROPERTY = 1;
    const PROPERTY_TYPE_NAVIGATION = 2;

    private $entityManager;
    private $metadata;
    private $interceptor;

    public function __construct(EntityManager $entityManager, Metadata $metadata = null, $interceptor = null) {
        $this->entityManager = $entityManager;
        $this->metadata = $metadata;
        $this->interceptor = $interceptor;
    }

    public function saveChanges(SaveBundle $saveBundle) {
        $result = $this->saveChangesTemp($saveBundle);
        return $result;
//        $entities = array();
//        $keyMappings = array();
//        $saveResult = new SaveResult($entities, $keyMappings);
//        return $saveResult;
    }

    function isProxyObject($object) {
        if ($object instanceof Proxy || $object instanceof ORMProxy) {
            return true;
        }
        return false;
    }

    function setObjectValue($object, $property, $value, $setter = false) {
        if (!$setter) {
            $setter = 'set' . ucfirst($property);
        }
        if (method_exists($object, $setter)) {
            $object->$setter($value);
            return;
        }
        $refl = new \ReflectionObject($object);
        if ($this->isProxyObject($object)) {
            $refl = $refl->getParentClass();
        }
        try {
            $prop = $refl->getProperty($property);
            $prop->setAccessible(true);
            $prop->setValue($object, $value);
        }
        catch(\ReflectionException $e){
            
        }
    }

    function getEntityClass($entity) {
        if ($this->isProxyObject($entity)) {
            return get_parent_class($entity);
        }
        return get_class($entity);
    }

    function convertToDoctrineValue($string, $dataType) {
        if ($string === null) {
            return null;
        }
        switch ($dataType) {
            // integers
            case "smallint":
            case "integer":
                return intval($string);
            // double
            case "float":
            case "decimal":
                return doubleval($string);
            case "boolean":
                return (bool) $string;
            // DateTime
            case "date":
            case "time":
            case "datetime":
//                exit($string);
                return new \DateTime($string);
//                return new \DateTime(strtotime($string));
            case "object":
            case "array":
                return unserialize($string);
            // strings
            case "bigint":
            case "text":
            case "string":
                return $string;
        }
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

    protected function getPropertyType(\Doctrine\ORM\Mapping\ClassMetadata $meta, $propertyName) {
        if (!$this->metadata) {
//            return self::PROPERTY_TYPE_NONE;
        }
        foreach ($this->metadata->structuralTypes as $structuralType) {
            if ($structuralType->shortName == $meta->getReflectionClass()->getShortName()) {
                if($structuralType->dataProperties) {
                    foreach ($structuralType->dataProperties as $dataProperty) {
                        if ($dataProperty->name == $propertyName) {
                            return self::PROPERTY_TYPE_PROPERTY;
                        }
                    }
                }
                if($structuralType->navigationProperties){
                    foreach ($structuralType->navigationProperties as $navProperty) {
                        if ($navProperty->name == $propertyName) {
                            return self::PROPERTY_TYPE_NAVIGATION;
                        }
                    }
                }
            }
        }
        return self::PROPERTY_TYPE_NONE;
    }
    
    public function validateEntity($entity){
        if($this->interceptor){
            return $this->interceptor->validateEntity($entity);
        }
    }
    
    protected function formatErrors($errors, $entity, $idValue){
        /* @var $errors \Symfony\Component\Validator\ConstraintViolationList */
        $validationErrors = array();
        $converter = new \Adrotec\BreezeJs\Validator\ValidatorConstraintConverter();
        foreach($errors as $error){
            /* @var $error \Symfony\Component\Validator\ConstraintViolation */
            $validationErrors[] = array(
//                'ErrorName' => 'HELY:'.var_export($error->getCode(), true),
                'ErrorName' => $converter->convert($error->getConstraint()),
                'ErrorMessage' => $error->getMessage(),
                'PropertyName' => $error->getPropertyPath(),
                'EntityTypeName' => strtr(get_class($entity), '\\', '.'),
                'KeyValues' => $idValue,
            );
        }
        return $validationErrors;
    }

    public function saveChangesTemp(SaveBundle $saveBundle) {
        $entitiesModified = array();
        $addedEntities = array();

//        print_r($saveBundle->getEntities()); exit;

        foreach ($saveBundle->getEntities() as $i => $entityArr) {
            $entityAspect = $entityArr->entityAspect;
            unset($entityArr->entityAspect);
            $exploded = explode(':#', $entityAspect->entityTypeName);
            $className = strtr($exploded[1] . '\\' . $exploded[0], '.', '\\');
            $meta = $this->entityManager->getClassMetadata($className);
            if (!$meta) {
                break; // - not continue, but break, because every entity should be valid
            }

            $repository = $this->entityManager->getRepository($className);

            $idProperty = isset($entityAspect->autoGeneratedKey->propertyName) ? $entityAspect->autoGeneratedKey->propertyName : $meta->identifier[0];

            $idPropertyRefl = $meta->getReflectionProperty($idProperty);

            $idPropertyName = $idProperty;
            $idGetter = ('get' . ucfirst($idPropertyName));
            $idSetter = ('set' . ucfirst($idPropertyName));
//            $idValue = false;
            $idValue = $entityArr->$idProperty;

//            if (($entityAspect->entityState == 'Modified' || $entityAspect->entityState == 'Deleted') && isset($entityArr->$idProperty)) {
//                $entity = $repository->find($entityArr->$idProperty);
//            } else if ($entityAspect->entityState == 'Added') {
//                unset($entityArr->$idProperty);
//                $entity = new $className();
//            }

            if ($entityAspect->entityState == 'Added') {
                unset($entityArr->$idProperty);
                $entity = new $className();
            } else if (isset($entityArr->$idProperty)) {
                $entity = $repository->find($entityArr->$idProperty);
            }

            $associations = array();

            if ($entityAspect->entityState == 'Modified' || $entityAspect->entityState == 'Added') {
                $processedProperties = array();
                foreach ($meta->fieldMappings as $propertyName => $fieldMapping) {
                    if (property_exists($entityArr, $propertyName)) {
                        $setter = false; //('set' . ucfirst($propertyName));
                        $propertyValue = $entityArr->$propertyName;
                        try {
                            $propertyValue = $this->convertToDoctrineValue($propertyValue, 
                                    $fieldMapping ? $fieldMapping['type'] : 'string');
                        } catch (\Exception $e) {
                            throw $e;
                        }
                        $this->setObjectValue($entity, $propertyName, $propertyValue, $setter);
                        $processedProperties[] = $propertyName;
                    }
                }
                foreach ($meta->associationMappings as $associationName => $associationFieldMapping) {
//                    $associationFieldMapping = $meta->associationMappings[$propertyName];
                    $fkFieldName = $this->getForeignKeyFieldName($associationFieldMapping);
                    if (!$fkFieldName) {
                        $fkFieldName = $associationFieldMapping['fieldName'] . 'Id';
                    }
                    if (property_exists($entityArr, $fkFieldName)) {
                        $associationSetter = false; //('set' . ucfirst($associationFieldMapping['fieldName']));

                        $associations[$fkFieldName] = array(
                            'targetEntity' => $associationFieldMapping['targetEntity'],
                            'fieldName' => $associationFieldMapping['fieldName'],
                            'referencedFieldValue' => $entityArr->$fkFieldName,
                            'setter' => $associationSetter,
                        );
                        $processedProperties[] = $fkFieldName;
                    }
                }
                foreach ($entityArr as $propertyName => $propertyValue) {
                    if(in_array($propertyName, $processedProperties)){
                        continue;
                    }
                    $propertyType = $this->getPropertyType($meta, $propertyName);
                    if ($propertyType === self::PROPERTY_TYPE_NONE) {
                        continue;
                    }
                    $setter = false; //('set' . ucfirst($propertyName));
                    $propertyValue = $entityArr->$propertyName;
                    $this->setObjectValue($entity, $propertyName, $propertyValue, $setter);
                }

            } else if ($entityAspect->entityState == 'Deleted') {
                
            } else {
                // continue; // DONT continue for unchanged. Causes trouble on the client
            }
            $entityModified = array(
                'entity' => $entity,
                'state' => $entityAspect->entityState,
                'entityTypeName' => strtr($className, '\\', '.'),
//                'idProperty' => $idProperty,
//                'idPropertyName' => $idPropertyName,
                'idGetter' => $idGetter,
                'idSetter' => $idSetter,
                'idValue' => $idValue ? $idValue : 0,
                'associations' => isset($associations) && !empty($associations) ? $associations : false,
                'persisted' => false,
                'deleted' => false,
                'validationErrors' => false,
            );
            $entitiesModified[$this->getEntityClass($entityModified['entity']) . '_' . $entityModified['idValue']] = $entityModified;
        }

        $keyMappings = array();
        $entities = array();
        $validationErrors = false;
        if (!empty($entitiesModified)) {
            foreach ($entitiesModified as $key => $entityModified) {
                if ($entityModified['associations'] && !empty($entityModified['associations'])) {
//                    exit;
                    foreach ($entityModified['associations'] as $associationData) {

                        $association = false;
                        if (isset($entitiesModified[$associationData['targetEntity'] . '_' . $associationData['referencedFieldValue']])) {
                            $association = $entitiesModified[$associationData['targetEntity'] . '_' . $associationData['referencedFieldValue']]['entity'];
                        } else {
                            if ($associationData['referencedFieldValue']) {
                                $association = $this->entityManager->getReference($associationData['targetEntity'], $associationData['referencedFieldValue']);
                            } else if (array_key_exists('referencedFieldValue', $associationData)) {
                                $association = null;
                            }
                        }
                        if ($association !== false) {
                            $this->setObjectValue($entityModified['entity'], $associationData['fieldName'], $association, $associationData['setter']);
                        }
                    }
                }
                $errors = false;
                if ($entityModified['state'] == 'Added' || $entityModified['state'] == 'Modified') {
                    $errors = $this->validateEntity($entityModified['entity']);
                    if ($entityModified['entity']) {
                        $this->entityManager->persist($entityModified['entity']);
                        $entityModified['persisted'] = true;
                    }
                } else if ($entityModified['state'] == 'Deleted') {
                    if ($entityModified['entity']) {
                        $entityCopy = clone $entityModified['entity'];
                        $this->entityManager->remove($entityModified['entity']);
                        $entityModified['entity'] = $entityCopy;
                        $entityModified['deleted'] = true;
                    }
                }
                $entitiesModified[$key] = $entityModified;

                if ($errors && count($errors) > 0) {
                    if (!is_array($validationErrors)) {
                        $validationErrors = array();
                    }
//                    $entitiesModified['validationErrors'] = $errors;
                    $validationErrors = array_merge($validationErrors, 
                            $this->formatErrors($errors, $entityModified['entity'], $entityModified['idValue']));
//                    $validationErrors[$this->getEntityClass($entityModified['entity']) . '_' . $entityModified['idValue']] = $errors;
                }
            }

            if ($validationErrors) {
                $this->entityManager->clear();
                return array(
                    'Errors' => $validationErrors,
                );
                print_r($validationErrors);
                exit;
                throw new ValidationException($validationErrors, 'Validation failed');
            }

//            print_r($entitiesModified); exit;

            $this->entityManager->flush();
//            return $entitiesModified; // so far, so good

            foreach ($entitiesModified as $entityModified) {
                if ($this->isProxyObject($entityModified['entity'])) {
                    if (!$entityModified['entity']->__isInitialized()) {
                        $entityModified['entity']->__load();
                    }
                }
                if ($entityModified['state'] == 'Added' && $entityModified['idValue'] && method_exists($entityModified['entity'], $entityModified['idGetter'])) {
                    $realValue = $entityModified['entity']->{$entityModified['idGetter']}();

                    $tempValue = $entityModified['idValue'];
                    if ($tempValue != $realValue) {
                        $keyMappings[$this->getEntityClass($entityModified['entity']) . '_' . $tempValue] = array(
//                        $keyMappings[] = array(
                            'TempValue' => $tempValue,
                            'RealValue' => $realValue,
                            'EntityTypeName' => $entityModified['entityTypeName'],
                        );
                    }
                }
                $entities[] = $entityModified['entity'];
            }
        }

        $keyMappings = array_values($keyMappings);

        return array(
            'Entities' => $entities, 'KeyMappings' => $keyMappings);
    }

}
