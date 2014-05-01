<?php

namespace BreezeJsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use BreezeJs\Doctrine\ORM\MetadataBuilder;
use BreezeJs\Doctrine\ORM\SaveService;
use BreezeJs\Doctrine\ORM\QueryService;
//
use BreezeJs\TextUtil;

use BreezeJs\Metadata\StructuralType;

class MetadataInterceptor extends \BreezeJs\Serializer\MetadataInterceptor {
    public function getDefaultResourceName(StructuralType $structuralType) {
        return $structuralType->shortName;
    }
    public function modifyStructuralTypes(StructuralType &$structuralType, \ReflectionClass $class) {
        return $structuralType;//parent::modifyStructuralType($structuralType, $class);
    }
}
class BreezeJsController extends Controller {

    /**
     * 
     * @return \JMS\Serializer\Serializer
     */
    protected function getSerializer() {
        return $this->container->get('serializer');
    }

    public function getClientClasses() {
        return array();
    }

    public function getClientNamespace() {
        return '';
    }

    public function apiAction($route) {
//        print_r($this->getClientClasses()); exit;
        $response = '';
        if ($route == 'Metadata') {
            $builder = new MetadataBuilder($this->getDoctrine()->getManager(), new MetadataInterceptor($this->getSerializer()));
            $metadata = $builder->buildMetadata(
                    $this->getClientClasses()
            );
            $response = $this->getSerializer()->serialize($metadata, 'json');
        } else if ($route == 'SaveChanges') {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new \Exception('Method not allowed');
            }
//        $builder = new MetadataBuilder($this->getDoctrine()->getManager());
            $metadata = null; //$builder->buildMetadata();
            $saveService = new SaveService($this->getDoctrine()->getManager(), $metadata);
            $saveBundle = $saveService->createSaveBundleFromString(file_get_contents('php://input'));
            $result = $saveService->saveChanges($saveBundle);
            $response = $this->getSerializer()->serialize($result, 'json');
        } else {
            $class = false;
            foreach ($this->getClientClasses() as $className) {
                $class = new \ReflectionClass($className);
                $shortName = $class->getShortName();
                if ($route == $shortName || $route == TextUtil::pluralize($shortName)) {
                    break;
                }
            }
            if (!$class) {
                throw new \Exception('Resource Not Found');
            }
            $qs = new QueryService($this->getDoctrine()->getManager());
            $result = $qs->getQueryResult($class->getName(), $_GET);
            $serializer = $this->getSerializer();
            $response = $serializer->serialize($result, 'json');
        }
        
        return new Response($response);
    }

}
