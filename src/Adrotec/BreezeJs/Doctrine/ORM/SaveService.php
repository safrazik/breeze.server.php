<?php

namespace Adrotec\BreezeJs\Doctrine\ORM;

use Doctrine\ORM\EntityManager;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\ParameterBag;

use Adrotec\BreezeJs\Metadata\Metadata;

use Adrotec\BreezeJs\Save\SaveBundle;

class SaveService {
    
    private $entityManager;
    private $metadata;

    public function __construct(EntityManager $entityManager, Metadata $metadata = null) {
        $this->entityManager = $entityManager;
        $this->metadata = $metadata;
    }
    
    public function createSaveBundleFromString($saveBundleString){
        $saveBundleArr = json_decode($saveBundleString);
        $saveBundle = new SaveBundle();
        $saveBundle->setEntities($saveBundleArr->entities);
        return $saveBundle;
    }
    
    public function saveChanges($saveBundle){
        if(!$saveBundle instanceof SaveBundle){
            $saveBundle = $this->createSaveBundleFromString($saveBundle);
        }
        $context = new SaveContextProvider($this->entityManager, $this->metadata);
        $saveResult = $context->saveChanges($saveBundle);
        return $saveResult;
    }
}
