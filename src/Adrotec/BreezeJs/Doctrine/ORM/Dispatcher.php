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
    private $interceptor;
    private $classes;
    
    public function __construct(EntityManager $entityManager, MetadataInterceptorInterface $interceptor = null, array $classes = null) {
        $this->entityManager = $entityManager;
        $this->interceptor = $interceptor;
        $this->classes = $classes;
    }
    
    public function setClasses(array $classes = null){
        $this->classes = $classes;
    }
    
    protected function getClass($class){
        if (strpos($class, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $class);
            $class = $this->entityManager->getConfiguration()->getEntityNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }
        return $class;
    }

    public function getResults($resourceName, $params = null) {
        $className = false;
        if($this->classes === null){
            $this->classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        }
        foreach ($this->classes as $class) {
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
        $saveService = new SaveService($this->entityManager, $this->getMetadata(), $this->interceptor);
        $saveBundle = $saveService->createSaveBundleFromString($saveBundleString);
        $result = $saveService->saveChanges($saveBundle);
        return $result;
    }

    public function getMetadata() {
        $builder = new MetadataBuilder($this->entityManager, $this->interceptor);
        $metadata = $builder->buildMetadata($this->classes);
        return $metadata;
    }

}
