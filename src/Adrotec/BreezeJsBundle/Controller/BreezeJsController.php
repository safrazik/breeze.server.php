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
        
        $dispatcher = new Dispatcher($this->getDoctrine()->getManager());
        
        $classes = $this->getClientClasses();
        
        $response = null;
        /* @var $serializer \JMS\Serializer\Serializer */
        $serializer = $this->container->get('serializer');
        $validator = $this->container->get('validator');
        
        if ($route == 'Metadata') {
            $interceptor = new MetadataInterceptor();
            $interceptor->add(new SerializerInterceptor($serializer));
            $interceptor->add(new ValidatorInterceptor($validator));
            $response = $dispatcher->getMetadata($classes, $interceptor);
        }
        else if ($route == 'SaveChanges') {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new \Exception('Method not allowed');
            }
            $input = file_get_contents('php://input');
            $response = $dispatcher->saveChanges($input);
        }
        else {
            $response = $dispatcher->getResults($route, $_GET, $classes);
        }
        
        $response = $serializer->serialize($response, 'json');
        
        return new Response($response);
    }

}
