<?php

namespace BreezeJs\Serializer\EventSubscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;


class BreezeDoctrineSubscriber implements EventSubscriberInterface {

    private $entityManager;

//    private 

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents() {
        return array(
            array('event' => 'serializer.post_serialize', 'method' => 'onPostSerialize'),
        );
    }

    public function onPostSerialize(ObjectEvent $event) {
        return;
        $object = $event->getObject();
        $type = $event->getType();
        try {
            $meta = $this->entityManager->getClassMetadata($type['name']);
        }
        catch(\Exception $e){
            return;
        }
        if ($meta) {
            $visitor = $event->getVisitor();
            /* @var $visitor \JMS\Serializer\JsonSerializationVisitor */
            $visitor->addData('$type', strtr($type['name'], '\\', '.'));
            foreach ($meta->associationMappings as $associationMapping) {
                $isScalar = in_array((int) $associationMapping['type'], array(ClassMetadata::ONE_TO_ONE, ClassMetadata::MANY_TO_ONE));
                $isOwningSide = isset($associationMapping['isOwningSide']) ? $associationMapping['isOwningSide'] : false;
                if(!($isScalar && $isOwningSide)){
                    continue;
                }
                try {
                    $refl = new \ReflectionObject($object);
                    $prop = $refl->getProperty($associationMapping['fieldName']);
                    $prop->setAccessible(true);
                    $association = $prop->getValue($object);
                    if ($association) {
                        $id = $association->getId();
                        $foreignKey = $associationMapping['fieldName'] . 'Id';
                        try {
                            $visitor->addData($foreignKey, $id);
                        } catch (InvalidArgumentException $e) {
                            
                        }
                    }
                } catch (\ReflectionException $e) {
//                    continue;
                }
            }
        }
    }

}
