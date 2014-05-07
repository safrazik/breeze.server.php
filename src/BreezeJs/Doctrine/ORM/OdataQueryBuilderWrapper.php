<?php

namespace BreezeJs\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use BreezeJs\OdataParameters;
use BreezeJs\ResourceType;
use ODataProducer\UriProcessor\QueryProcessor\ExpressionParser\ExpressionParser2;
use BreezeJs\CallbackExpressionProvider;
use BreezeJs\Doctrine\ORM\QueryProcessor\DoctrineExpression;
use Doctrine\ORM\Mapping\ClassMetadata;

class OdataQueryException extends \Exception {
    
}

class OdataQueryBuilderWrapper {

    const PROPERTY_DATA = 'DataProperty';
    const PROPERTY_NAVIGATION = 'NavigationProperty';
    const PROPERTY_NONE = 'None';

    private $queryBuilder;
    private $selects = array();
    private $expandSelects = array();
    private $joins = array();

    public function __construct(QueryBuilder &$queryBuilder) {
        $this->setQueryBuilder($queryBuilder);
    }

    public function getQueryBuilder() {
        return $this->queryBuilder;
    }

    public function setQueryBuilder(QueryBuilder &$queryBuilder) {
        $this->queryBuilder = $queryBuilder;
        $this->selects = array();
        $this->expandSelects = array();
        $this->joins = array();
        return $this;
    }

    public function getRootEntity() {
        $rootEntities = $this->queryBuilder->getRootEntities();
        return $rootEntities[0];
    }

    public function getRootAlias() {
        $rootAliases = $this->queryBuilder->getRootAliases();
        return $rootAliases[0];
    }

    public function getEntityManager() {
        return $this->queryBuilder->getEntityManager();
    }

    public function getClassMetadata() {
        return $this->getEntityManager()->getClassMetadata($this->getRootEntity());
    }

    private function getResourceType() {
//        $entity = $this->getRootEntity();
        $meta = $this->getClassMetadata();
        return ResourceType::getResourceType($this->getEntityManager(), $meta);
    }

    // Temporary workaround for virtual foreign keys to work
    private function parseSystemQueryValue($value) {
        $value = $this->replaceForeignKeyFields($value);
        return $value;
    }
    
    private function replaceForeignKeyFields($value){
        $value = preg_replace('/(.+?)Id([^A-Za-z0-9])/', '$1/id$2', $value);
        $value = preg_replace('/(.+?)Id$/', '$1/id$2', $value);
        return $value;        
    }

    private function invalid($message) {
        throw new OdataQueryException($message);
    }

    public function getPropertyType($properyName, ClassMetadata $meta = null) {
        if ($meta === null) {
            $meta = $this->getClassMetadata();
        }
        if (strpos($properyName, '/')) {
            $associations = explode('/', $properyName);
            foreach ($associations as $properyName) {
                $type = $this->getPropertyType($properyName, $meta);
                if ($type == self::PROPERTY_NAVIGATION) {
                    $meta = $this->getEntityManager()->getClassMetadata(
                            $meta->associationMappings[$properyName]['targetEntity']);
                } else if ($type == self::PROPERTY_DATA) {
                    return self::PROPERTY_DATA;
                } else {
                    return false;
                }
            }
            return self::PROPERTY_NAVIGATION;
        }
        if (isset($meta->associationMappings[$properyName])) {
            return self::PROPERTY_NAVIGATION;
        }
        if (isset($meta->fieldMappings[$properyName])) {
            return self::PROPERTY_DATA;
        }
        return false;
    }

    public function addTop($top) {
        if (!is_numeric($top)) {
            return $this->invalid('Invalid $top value "' . $top . '"');
        }
        $this->queryBuilder->setMaxResults(intval($top));
        return $this;
    }

    public function addSkip($skip) {
        if (!is_numeric($skip)) {
            return $this->invalid('Invalid $skip value "' . $skip . '"');
        }
        $this->queryBuilder->setFirstResult(intval($skip));
        return $this;
    }

    public function addFilter($filter) {
        $filter = $this->parseSystemQueryValue($filter);
        $resourceType = $this->getResourceType();

        $internalFilterInfo = ExpressionParser2::parseExpression2(
                        $filter, $resourceType
                        , new CallbackExpressionProvider('$ex->'));
                
        
        $ex = new DoctrineExpression();
        $ex->setQueryBuilder($this->queryBuilder);
        $rootAlias = $this->getRootAlias();
        $ex->setAlias($rootAlias);
        $exprStr = $internalFilterInfo->getExpressionAsString() . ';';

//        $exprStr = $this->replaceForeignKeyFields($exprStr);

        $exprStr = str_replace(array("'\'"), array("'\\\'"), $exprStr);

        $expression = eval('return ' . $exprStr . ';');

        if ($expression === false) {
            return $this->invalid('Invalid filter expression "' . $filter . '"');
        }

        $this->queryBuilder->andWhere($expression);

        foreach ($ex->getParameters() as $key => $value) {
            $this->queryBuilder->setParameter($key, $value);
        }

//        return $this;

        $associations = $ex->getAssociations();
        if (!empty($associations)) {
            foreach ($associations as $association) {
//                        exit($association);
                if (!$this->getPropertyType($association)) {
                    return $this->invalid('invalid navigation property "' . $association . '"');
                }

                $this->joinAssociationsLoop($this->queryBuilder, $association, false);
            }
        }


        return $this;
    }

    public function addOrderBy($orderby) {

        $orderby = $this->parseSystemQueryValue($orderby);
        
        $expr = explode(',', $orderby);

        $orderBy = array();

        foreach ($expr as $str) {
            $exploded = explode(' ', trim($str));
            $column = $exploded[0];
            $propertyType = $this->getPropertyType($column);
            if (!$propertyType) {
                $this->invalid('Invalid orderby expression "' . $orderby . '"');
            }
            if (strpos($column, '/')) {
                $exploded2 = explode('/', $column);
                $columnName = array_pop($exploded2);
                $columnPrefix = implode('/', $exploded2);
                $alias = $this->joinAssociationsLoop($this->queryBuilder, $columnPrefix, false);
            } else {
                $columnName = $column;
                $alias = $this->getRootAlias();
            }

            $orderByColumn = ($alias . '.') . $columnName;
            $order = null;
            if (isset($exploded[1])) {
                $order = strtolower($exploded[1]);
                if (!in_array($order, array('asc', 'desc'))) {
                    $this->invalid('sort order should be either ASC or DESC');
                }
            }
            $orderBy[] = $orderByColumn . ($order == 'desc' ? ' desc' : '');
        }
        if (!empty($orderBy)) {
            $this->queryBuilder->add('orderBy', implode(',', $orderBy));
//							$q->orderBy(implode(',', $orderBy));
        }
        return $this;
    }

    public function addSelect($select) {

        if (!empty($this->expandSelects)) {
            return $this->invalid('Using both $expand and $select is not supported. Either one could be used');
        }
        // $select=id,firstName,lastName,age
        $expr = explode(',', $select);
        foreach ($expr as $column) {
            $propertyType = $this->getPropertyType($column);
            if (!$propertyType) {
                $this->invalid('Invalid select expression "' . $select . '"');
            }
            $column = trim($column);
            if (strpos($column, '/')) {
                $exploded2 = explode('/', $column);
                $columnName = array_pop($exploded2);
                $columnPrefix = implode('/', $exploded2);
                $alias = $this->joinAssociationsLoop($this->queryBuilder, $columnPrefix, false);
            } else {
                $columnName = $column;
                $alias = $this->getRootAlias();
            }
//			$this->queryBuilder->leftJoin($this->alias.'.'.$columnName, $this->alias.'_'.$association);
            $select = $alias . '.' . $columnName;
//            $this->queryBuilder->add('select', $select, true);
            $this->selects[] = $select;
        }
        $this->applyProjection();

        return $this;
    }

    public function addExpand($expand) {

        if (!empty($this->selects)) {
//            return $this->invalid('Using both $expand and $select is not supported. Either one could be used');
        }
//				$this->queryBuilder->leftJoin($this->alias.'.job ', $this->alias.'3');
//		$this->queryBuilder->select($this->alias.','.$this->alias.'3');
        $expr = explode(',', $expand);
        $expands = array();
//		echo '<pre>';
//		print_r($this->classInfo->doctrineMetadata->associationMappings);
//exit;
        foreach ($expr as $association) {
            $association = trim($association);
//            if($association == 'courseFee'){
//                continue;
//            }
            $propertyType = $this->getPropertyType($association);
            if ($propertyType != self::PROPERTY_NAVIGATION) {
                return $this->invalid('invalid expand expression "' . $expand . '"');
            }

            $alias = $this->joinAssociationsLoop($this->queryBuilder, $association, true);
//            $this->expandSelects[] = $alias;
        }

        if (!empty($expr)) {
            $this->expandSelects[] = $this->getRootAlias();
            $this->applyProjection();
        }
        return $this;
    }

    protected function applyProjection() {
        $selects = implode(',', array_unique(array_merge($this->selects, $this->expandSelects)));
        $this->queryBuilder->select($selects);
    }

    public function getInlineCount() {
        $qb = clone $this->queryBuilder;
        $qb->select('COUNT(' . $this->getRootAlias() . ')');
        $qb->setMaxResults(1);
        $qb->setFirstResult(0);
        $qb->add('join', array(), false);
        $query = $qb->getQuery();
        $count = $query->getSingleScalarResult();
        return $count;
    }

    public function applyOdataParameters(OdataParameters $params) {
        if (isset($params->filter)) {
            $this->addFilter($params->filter);
        }
        if (isset($params->expand)) {
            $this->addExpand($params->expand);
        }
        //
        if (isset($params->orderby)) {
            $this->addOrderBy($params->orderby);
        }
        if (isset($params->top)) {
            $this->addTop($params->top);
        }
        if (isset($params->skip)) {
            $this->addSkip($params->skip);
        }
        if (isset($params->select)) {
            $this->addSelect($params->select);
        }
        return $this;
    }

    protected function joinAssociations(QueryBuilder $queryBuilder, $join, $alias, $alsoSelect = true) {
        if (!isset($this->joins[$alias])) {
            $queryBuilder->leftJoin($join, $alias);
        }
        $this->joins[$alias] = true;
        if ($alsoSelect) {
            $this->selects[] = $alias;
        }
    }

    protected function joinAssociationsLoop(QueryBuilder $queryBuilder, $associations, $alsoSelect = false, $prefix = null) {
        $associations = explode('/', $associations);
        $previousAlias = $prefix === null ? $this->getRootAlias() : $prefix;
        foreach ($associations as $propertyName) {
//                $this->joinAssociations($propertyName, $previousAlias, true, $this->queryBuilder);
            $this->joinAssociations($queryBuilder, $previousAlias . '.' . $propertyName, $previousAlias . '_' . $propertyName, $alsoSelect);
            $previousAlias .= '_' . $propertyName;
        }
        return $previousAlias;
    }

}
