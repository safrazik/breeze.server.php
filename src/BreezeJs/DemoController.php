<?php

namespace BreezeJs;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class DemoController extends Controller {
    
    public function MetadataAction(){
        $metadata = $this->container->get('breezejs.metadatabuilder')->getMetadata();
        $jsonResponse = $this->container->get('serializer')->serialize($metadata, 'json');
        return new Response($jsonResponse);
    }
    
    public function CustomersAction(Request $request){
        $qs = new QueryService($this->getDoctrine()->getManager());
        $result = $qs->getQueryResult('Entity\Customers', $request->query);
        $jsonResponse = $this->container->get('serializer')->serialize($result, 'json');
        return new Response($jsonResponse);
    }
    
    public function SaveChangesAction(SaveBundle $saveBundle) {
        $ss = new SaveService($this->getDoctrine()->getManager(), $metadata);
        return $ss->saveChanges($saveBundle);
    }
    
}
