<?php

namespace Adrotec\BreezeJs\Serializer;

class SerializerBuilder {

    /**
     * 
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @return \JMS\Serializer\SerializerBuilder
     */
    public static function create(\Doctrine\ORM\EntityManager $entityManager) {

        $builder = \JMS\Serializer\SerializerBuilder::create();
        $propertyNamingStrategy = new \Adrotec\BreezeJs\Serializer\CamelCaseNamingStrategy();
//$propertyNamingStrategy = new \JMS\Serializer\Naming\CamelCaseNamingStrategy();
        $builder->setPropertyNamingStrategy($propertyNamingStrategy);
        $visitor = new \Adrotec\BreezeJs\Serializer\JsonSerializationVisitor($propertyNamingStrategy, $entityManager);

        $builder->configureHandlers(function(\JMS\Serializer\Handler\HandlerRegistry $registry) {
            $registry->registerSubscribingHandler(
                    new \Adrotec\BreezeJs\Serializer\Handler\DateHandler()
            );
            $registry->registerSubscribingHandler(
                    new \Adrotec\BreezeJs\Serializer\Handler\ArrayCollectionHandler()
            );
        });

        $builder->configureListeners(function(\JMS\Serializer\EventDispatcher\EventDispatcher $dispatcher) {
            $dispatcher->addSubscriber(new \Adrotec\BreezeJs\Serializer\EventSubscriber\DoctrineProxySubscriber());
        });

        $builder->setSerializationVisitor('json', $visitor);

        return $builder;
    }

}
