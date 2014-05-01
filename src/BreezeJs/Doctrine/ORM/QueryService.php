<?php

namespace BreezeJs\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use BreezeJs\OdataParameters;

use Doctrine\ORM\Tools\Pagination\Paginator;

class QueryService {

    private $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function getQueryResult($class, $params) {
        if ($params instanceof \Traversable || $params instanceof \ArrayAccess) {
            
        }
        if (!($params instanceof OdataParameters)) {
            $params = OdataParameters::parse($params);
        }

//        print_r($params);
//        exit;

        $repository = $this->entityManager->getRepository($class);
        $queryBuilder = $repository->createQueryBuilder('X');

//        print_r($queryBuilder->getRootEntities());
//        print_r($queryBuilder->getRootAliases());
//        exit;
//        $queryBuilder = 
//                OdataQueryBuilder::applyParameters($queryBuilder, $params);

        $odataBuilder = new OdataQueryBuilderWrapper($queryBuilder);
        $odataBuilder->applyOdataParameters($params);


//        $odataBuilder
//                ->addExpand('branchCourse/course')
//                ->addFilter('branchCourse/course/code eq \'RY\'')
//                ;
//        echo '<html><head></head><body>';
        if ($params->inlinecount) {
//            $count = $odataBuilder->getInlineCount();
//            echo 'INline: ' . $count . '<br>';
//            exit;
        }

//        $odataBuilder->addFilter('serialNo eq 9');
//                print_r($queryBuilder->getParameters());
//        echo $queryBuilder->getDQL(); exit;


        $query = $queryBuilder->getQuery();

        $inlineCount = null;

        $fetchJoinCollection = isset($params->expand);
        $fetchCount = $params->inlinecount;
        
        if ($fetchJoinCollection || $fetchCount) {
            $paginator = new Paginator($query, $fetchJoinCollection);
            if ($fetchCount) {
                $inlineCount = count($paginator);
            }
            $result = array();
            foreach ($paginator as $object) {
                $result[] = $object;
            }
        }
        else {
            $result = $query->getResult();
        }
        if($inlineCount !== null){
            return array(
                'Results' => $result,
                'InlineCount' => $inlineCount
            );
        }
        return $result;
    }

}
