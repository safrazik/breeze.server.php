<?php

namespace Adrotec\BreezeJs\Framework;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Adrotec\BreezeJs\Doctrine\ORM\MetadataBuilder;
use Adrotec\BreezeJs\Doctrine\ORM\QueryService;
use Adrotec\BreezeJs\Doctrine\ORM\SaveService;
//
use Adrotec\BreezeJs\Serializer\SerializerBuilder;
//
use Adrotec\BreezeJs\MetadataInterceptor as InterceptorChain;
use Adrotec\BreezeJs\Validator\ValidatorInterceptor;

class Application implements ApplicationInterface
{

    const RESOURCE_METADATA_DEFAULT = 'Metadata';
    const RESOURCE_SAVE_CHANGES_DEFAULT = 'SaveChanges';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var array resource names for this api
     */
    private $resources = array();

    /**
     * @var InterceptorChain
     */
    private $interceptor;
    protected $metadataResource = self::RESOURCE_METADATA_DEFAULT;
    protected $saveChangesResource = self::RESOURCE_SAVE_CHANGES_DEFAULT;

    public function __construct(ObjectManager $objectManager = null, SerializerInterface $serializer = null, ValidatorInterface $validator = null)
    {
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        return $this;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    public function getSerializer()
    {
        return $this->serializer;
    }

    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        return $this;
    }

    public function getValidator()
    {
        return $this->validator;
    }

    public function addInterceptor($interceptor)
    {
        if (!$this->interceptor) {
            $this->interceptor = new InterceptorChain();
        }
        $this->interceptor->add($interceptor);
        return $this;
    }

    public function addResource($resourceName, $className = null)
    {
        if (!is_string($className)) {
            $className = $resourceName;
            $resourceName = null;
        }
        if (!is_string($resourceName)) {
            $refl = new \ReflectionClass($className);
            $this->resources[$refl->getShortName()] = $className;
        }
        if (is_string($resourceName)) {
            $this->resources[$resourceName] = $className;
        }
    }

    public function addResources($resources)
    {
        foreach ($resources as $resourceName => $className) {
            if (is_numeric($resourceName)) {
                $resourceName = null;
            }
            $this->addResource($resourceName, $className);
        }
    }

    public function setResources($resources)
    {
        $this->resources = array();
        return $this->addResources($resources);
    }

    public function getMetadata()
    {
        $builder = new MetadataBuilder($this->objectManager, $this->interceptor);
        $metadata = $builder->buildMetadata($this->resources);
        return $metadata;
    }

    public function saveChanges($payload)
    {
        $metadata = $this->getMetadata();
        $saveService = new SaveService($this->objectManager, $metadata, $this->interceptor);
        $saveBundle = $saveService->createSaveBundleFromString($payload);
        $result = $saveService->saveChanges($saveBundle);
        return $result;
    }

    public function getQueryResults($className, $params)
    {
        $queryService = new QueryService($this->objectManager);
        $result = $queryService->getQueryResult($className, $params);
        return $result;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
//        sleep(2);
        $interceptor = new InterceptorChain();
        $serializerInterceptor = new SerializerInterceptor($this->serializer);
        $serializerInterceptor->setResources($this->resources);
        $interceptor->add($serializerInterceptor);

        if ($this->validator) {
            $interceptor->add(new ValidatorInterceptor($this->validator));
        }
        $this->interceptor = $interceptor;


        $path = $request->attributes->get('resource');

        if ($path == $this->metadataResource) {
            $response = new Response();
            $metadata = $this->getMetadata($interceptor);
            $response->setContent($this->serializer->serialize($metadata, 'json'));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        if ('POST' === $request->getMethod() && $path == $this->saveChangesResource) {
            $response = new Response();
            $result = $this->saveChanges($request->getContent());
            $response->setContent($this->serializer->serialize($result, 'json'));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        if (isset($this->resources[$path])) {
            $className = $this->resources[$path];
            $response = new Response();
            $result = $this->getQueryResults($className, $request->query->all());
            $response->setContent($this->serializer->serialize($result, 'json'));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        throw new ResourceNotFoundException('No resource found for "' . $path . '"');
    }

}
