<?php

namespace Adrotec\BreezeJs\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Adrotec\BreezeJs\Doctrine\ORM\MetadataBuilder;
use Adrotec\BreezeJs\MetadataInterceptorInterface;
use Adrotec\BreezeJs\Doctrine\ORM\SaveService;

use Doctrine\ORM\Mapping\ClassMetadata;

use Adrotec\BreezeJs\TextUtil;

class Dispatcher {

    private $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }
    
    protected function getClass($class){
        if (strpos($class, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $class);
            $class = $this->entityManager->getConfiguration()->getEntityNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }
        return $class;
    }

    public function getResults($resourceName, $params = null, array $classes = null) {
        $className = false;
        if($classes === null){
            $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        }
        foreach ($classes as $class) {
            if($class instanceof ClassMetadata){
                $refl = $class->getReflectionClass();
            }
            else {
                $refl = new \ReflectionClass($this->getClass($class));
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
        return $this->getQueryResults($className, $params);
    }
    
    public function getQueryResults($className, $params = null){
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
