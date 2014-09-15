<?php

namespace Adrotec\BreezeJs\Framework;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Debug;
use JMS\Serializer\SerializerBuilder;
use Adrotec\BreezeJs\Serializer\SerializerBuilder as BreezeSerializerBuilder;
use Symfony\Component\Validator\ValidatorBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class StandaloneApplication extends Application implements StandaloneApplicationInterface
{

    private $debugEnabled = false;
    private $autoloader;
    private $connection = array();
    private $mappings = array();
    private $corsEnabled = false;
    private $annotationsEnabled = false;

    /**
     * @var SerializerBuilder
     */
    private $serializerBuilder;

    /**
     * @var ValidatorBuilder
     */
    private $validatorBuilder;

    public function enableDebug()
    {
        $this->debugEnabled = true;
        Debug::enable();
    }

    public function setAutoloader($loader)
    {
        $this->autoloader = $loader;
    }
    
    public function enableAnnotations()
    {
        $this->annotationsEnabled = true;
        AnnotationRegistry::registerLoader(array($this->autoloader, 'loadClass'));
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function addMapping($mapping)
    {
        $this->mappings[] = $mapping;
    }

    public function enableCors()
    {
        $this->corsEnabled = true;
    }

    protected function createEntityManager()
    {
        $driverChain = new MappingDriverChain();
        $config = Setup::createConfiguration($this->debugEnabled);
        $useSimpleAnnotationReader = false;

        foreach ($this->mappings as $key => $mapping) {
            $driver = null;
            if ($mapping['type'] == 'xml') {
                if (isset($mapping['extension'])) {
                    $driver = new XmlDriver(array($mapping['doctrine']), $mapping['extension']);
                } else {
                    $driver = new XmlDriver(array($mapping['doctrine']));
                }
            } else if ($mapping['type'] == 'yaml') {
                if (isset($mapping['extension'])) {
                    $driver = new YamlDriver(array($mapping['doctrine']), $mapping['extension']);
                } else {
                    $driver = new YamlDriver(array($mapping['doctrine']));
                }
            } else if ($mapping['type'] == 'annotation') {
                $paths = array();
                if (isset($mapping['doctrine'])) {
                    $paths[] = $mapping['doctrine'];
                }
                $driver = $config->newDefaultAnnotationDriver($paths, $useSimpleAnnotationReader);
            }
            if ($driver) {
                $driverChain->addDriver($driver, $mapping['namespace']);
            }
        }
        // default to annotations
        if(empty($this->mappings)){
            $driverChain = $config->newDefaultAnnotationDriver(array(), $useSimpleAnnotationReader);
        }

        $config->setMetadataDriverImpl($driverChain);

        $entityManager = EntityManager::create($this->connection, $config);

        return $entityManager;
    }

//    public function handle(Request $request)
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $response = parent::handle($request);
        if ($this->corsEnabled) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With');
        }
        return $response;
    }

    public function build()
    {
        $this->setObjectManager($this->createEntityManager());

        $this->serializerBuilder = BreezeSerializerBuilder::create($this->getObjectManager());

        $this->validatorBuilder = new ValidatorBuilder();
        if($this->annotationsEnabled){
            $this->validatorBuilder->enableAnnotationMapping();
        }

        foreach ($this->mappings as $mapping) {
            if (isset($mapping['serializer'])) {
                $this->serializerBuilder->addMetadataDir($mapping['serializer']);
            }
            if (isset($mapping['validation'])) {
                if ($mapping['type'] == 'xml') {
                    $this->validatorBuilder->addXmlMapping($mapping['validation']);
                } else if ($mapping['type'] == 'yaml') {
                    $this->validatorBuilder->addYamlMapping($mapping['validation']);
                }
//                else if ($mapping['type'] == 'annotation') {
//                    $this->validatorBuilder->enableAnnotationMapping();
//                }
            }
        }

        $serializer = $this->serializerBuilder->build();
        $this->setSerializer($serializer);

        $validator = $this->validatorBuilder->getValidator();
        $this->setValidator($validator);
    }

    public function run()
    {
        try {
            $request = Request::createFromGlobals();
            $request->attributes->set('resource', trim($request->getPathInfo(), '/'));
            $response = $this->handle($request);
            $response->send();
        } catch (\Exception $e) {
            if ($this->debugEnabled) {
                throw $e;
            } else {
                $response = new Response(json_encode(array(
                            'error' => $e->getMessage(),
                        )), 500);
                $response->headers->set('Content-Type', 'application/json');
                $response->send();
            }
        }
    }

}
