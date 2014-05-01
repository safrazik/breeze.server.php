<?php

namespace BreezeJsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use BreezeJs\Doctrine\ORM\Dispatcher;
use Symfony\Component\HttpFoundation\Response;
use BreezeJs\Serializer\MetadataInterceptor;

class BreezeJsController extends Controller {

    public function getClientClasses() {
        throw new \Exception('getClientClasses method should be implemented by sub classes');
    }

    public function apiAction($route) {
        
        $dispatcher = new Dispatcher($this->getDoctrine()->getManager());
        
        $classes = $this->getClientClasses();
        
        $response = null;
        /* @var $serializer \JMS\Serializer\Serializer */
        $serializer = $this->container->get('serializer');
        
        if ($route == 'Metadata') {
            $interceptor = new MetadataInterceptor($serializer);
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
