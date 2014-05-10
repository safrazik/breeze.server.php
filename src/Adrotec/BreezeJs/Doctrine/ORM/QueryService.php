<?php

namespace Adrotec\BreezeJs\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Adrotec\BreezeJs\OdataParameters;

use Doctrine\ORM\Tools\Pagination\Paginator;

class QueryService {

    private $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function getQueryResult($class, $params) {
        
        if (!($params instanceof OdataParameters)) {
            $params = OdataParameters::parse($params);
        }

        $repository = $this->entityManager->getRepository($class);
        $queryBuilder = $repository->createQueryBuilder('X');

        $odataBuilder = new OdataQueryBuilderWrapper($queryBuilder);
        $odataBuilder->applyOdataParameters($params);

        if ($params->inlinecount) {
//            $count = $odataBuilder->getInlineCount();
        }

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
