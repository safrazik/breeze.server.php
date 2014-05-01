<?php

namespace BreezeJsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use BreezeJsBundle\Dispatcher\Dispatcher;
use Symfony\Component\HttpFoundation\Response;

class BreezeJsController extends Controller {

    public function getClientClasses() {
        throw new \Exception('getClientClasses method should be implemented by sub classes');
    }

    public function apiAction($route) {
        $dispatcher = new Dispatcher($this->getDoctrine()->getManager(), $this->container->get('serializer'));
        $result = $dispatcher->dispatch($route, $_GET, $this->getClientClasses());
        return new Response($result);
    }

}
