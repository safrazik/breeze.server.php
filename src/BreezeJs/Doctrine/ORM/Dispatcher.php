<?php

namespace BreezeJs\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use BreezeJs\Doctrine\ORM\MetadataBuilder;
use BreezeJs\MetadataInterceptorInterface;
use BreezeJs\Doctrine\ORM\SaveService;

use Doctrine\ORM\Mapping\ClassMetadata;

use BreezeJs\TextUtil;

class Dispatcher {

    private $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function getResults($resourceName, $params = null, array $classes = null) {
        $className = false;
        if($classes === null){
            $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        }
        foreach ($classes as $class) {
            if($class instanceof ClassMetadata){
                $refl = $className->getReflectionClass();
            }
            else {
                $refl = new \ReflectionClass($class);
            }
            $shortName = $refl->getShortName();
            if ($resourceName == $shortName || $resourceName == TextUtil::pluralize($shortName)) {
                $className = $refl->getName();
                break;
            }
        }
        if (!$className) {
            throw new \Exception('Resource Not Found');
        }
        $queryService = new QueryService($this->entityManager);
        $result = $queryService->getQueryResult($className, $params);
        return $result;
    }

    public function saveChanges($saveBundleString) {
        $saveService = new SaveService($this->entityManager);
        $saveBundle = $saveService->createSaveBundleFromString($saveBundleString);
        $result = $saveService->saveChanges($saveBundle);
        return $result;
    }

    public function getMetadata(array $classes = null, MetadataInterceptorInterface $interceptor = null) {
        $builder = new MetadataBuilder($this->entityManager, $interceptor);
        $metadata = $builder->buildMetadata($classes);
        return $metadata;
    }

}
