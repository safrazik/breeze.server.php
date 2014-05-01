<?php

namespace BreezeJs\Serializer;

use JMS\Serializer\Metadata\ClassMetadata as JMSClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\Proxy\Proxy as ORMProxy;
use JMS\Serializer\Context;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use Doctrine\ORM\EntityManager;

class JsonSerializationVisitor extends \JMS\Serializer\JsonSerializationVisitor {

    private $entityManager;

    public function __construct(PropertyNamingStrategyInterface $namingStrategy, EntityManager $entityManager) {
        parent::__construct($namingStrategy);
        $this->entityManager = $entityManager;
    }

    public function visitProperty(PropertyMetadata $propertyMetadata, $data, Context $context) {
        if (!$propertyMetadata->reflection) {
            return;
        }
        return parent::visitProperty($propertyMetadata, $data, $context);
    }

    public function endVisitingObject(JMSClassMetadata $metadata, $data, array $type, Context $context) {
        $rs = parent::endVisitingObject($metadata, $data, $type, $context);
        if (empty($rs)) {
            return null;
        }

        if (($data instanceof Proxy || $data instanceof ORMProxy) && !$data->__isInitialized()) {
            return null;
        }

        try {
            $doctrineMeta = $this->entityManager->getClassMetadata($metadata->name);
            if ($doctrineMeta) {
                $rs['$type'] = strtr($type['name'], '\\', '.');
                foreach ($doctrineMeta->associationMappings as $associationMapping) {
                    $foreignKey = $associationMapping['fieldName'] . 'Id';
                    if(isset($rs[$foreignKey])){
                        continue;
                    }
                            
                    $isScalar = in_array((int) $associationMapping['type'], array(ClassMetadata::ONE_TO_ONE, ClassMetadata::MANY_TO_ONE));
                    $isOwningSide = isset($associationMapping['isOwningSide']) ? $associationMapping['isOwningSide'] : false;
                    if (!($isScalar && $isOwningSide)) {
                        continue;
                    }
                    try {
                        $refl = new \ReflectionObject($data);
                        $prop = $refl->getProperty($associationMapping['fieldName']);
                        $prop->setAccessible(true);
                        $association = $prop->getValue($data);
                        if ($association) {
                            try {
                                $id = $association->getId();
                                $rs[$foreignKey] = $id;
                            } catch (\Exception $e) {
                                
                            }
                        }
                    } catch (\ReflectionException $e) {
//                    continue;
                    }
                }
            }
        } catch (\Exception $e) {
            return $rs;
        }


        return $rs;
    }

}
