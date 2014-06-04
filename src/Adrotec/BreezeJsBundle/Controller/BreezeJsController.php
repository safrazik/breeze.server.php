<?php

namespace Adrotec\BreezeJsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Adrotec\BreezeJs\Doctrine\ORM\Dispatcher;
use Symfony\Component\HttpFoundation\Response;
use Adrotec\BreezeJs\MetadataInterceptor;
use Adrotec\BreezeJs\Serializer\MetadataInterceptor as SerializerInterceptor;
use Adrotec\BreezeJs\Validator\ValidatorInterceptor;

//use Symfony\Component\Validator\Validator;

class BreezeJsController extends Controller {

    public function getClientClasses() {
        //throw new \Exception('getClientClasses method should be implemented by sub classes');
        return null;
    }

    public function apiAction($route) {
        
        $response = null;
        /* @var $serializer \JMS\Serializer\Serializer */
        $serializer = $this->container->get('serializer');
        $validator = $this->container->get('validator');
        
        $interceptor = new MetadataInterceptor();
        $interceptor->add(new SerializerInterceptor($serializer));
        $interceptor->add(new ValidatorInterceptor($validator));
            
        $dispatcher = new Dispatcher($this->getDoctrine()->getManager(), $interceptor);
        
        $classes = $this->getClientClasses();
        
        $dispatcher->setClasses($classes);
        
        if ($route == 'Metadata') {
            $response = $dispatcher->getMetadata();
        }
        else if ($route == 'SaveChanges') {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new \Exception('Method not allowed');
            }
            $input = file_get_contents('php://input');
            $response = $dispatcher->saveChanges($input);
        }
        else {
            $response = $dispatcher->getResults($route, $_GET);
        }
        
        $response = $serializer->serialize($response, 'json');
        
        return new Response($response);
    }

}
