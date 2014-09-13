<?php

namespace Adrotec\BreezeJs\Framework;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Adrotec\BreezeJs\MetadataInterceptorInterface as InterceptorInterface;

interface ApplicationInterface extends HttpKernelInterface
{

    public function setObjectManager(ObjectManager $objectManager);

    public function getObjectManager();

    public function setSerializer(SerializerInterface $serializer);

    public function getSerializer();

    public function setValidator(ValidatorInterface $validator);

    public function getValidator();

//    public function addInterceptor($interceptor);

    public function addResource($resourceName, $className = null);

    public function addResources($resources);

    public function setResources($resources);

    public function getMetadata();

    public function saveChanges($payload);

    public function getQueryResults($className, $params);
}
