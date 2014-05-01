<?php

namespace BreezeJs\Doctrine\ORM\QueryProcessor;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr; // as ExprBase;

class DoctrineProperty {

    private $name;

    function __construct($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

}

//class ExprExtended extends Expr {
//	
//}

class UnSupportedExpression extends \Exception {
    
}

/*
 * NOT IMPLEMENTED SO FAR:
 * 
 * * STRING FUNCTIONS
 * indexof()
 * replace()
 * 
 * * DATE FUNCTIONS
 * 
 */

class NotImplementedException extends \Exception {
    
}

class DoctrineExpression {
//	const ASSERT_EQUAL_DEFAULT = -33333344493831;

    /**
     *
     * @var	\Doctrine\ORM\QueryBuilder
     */
    private $queryBuilder;

    /**
     *
     * @var \Doctrine\ORM\Query\Expr
     */
    private $expr;
    private $alias;
    private $parameterNum = 500; // just a random number
    private $parametersMap = array();
//	private $comparisonCallback; // = self::ASSERT_EQUAL_DEFAULT;
    private $comparisonCallbacks = array();
    private $associations = array();

    public function setQueryBuilder(QueryBuilder $queryBuilder) {
        $this->queryBuilder = $queryBuilder;
        $this->expr = $this->queryBuilder->expr();
    }

    public function setAlias($alias) {
        $this->alias = $alias;
    }

    public function getQueryBuilder() {
        return $this->queryBuilder;
    }

    public function getParameters() {
        return $this->parametersMap;
    }

    public function getExpression() {
        return $this->expression;
    }

    public function getAssociations() {
        return $this->associations;
    }

    public function getProperty($propertyName) {
//        print_r($this->queryBuilder->getRootEntities());
//        $propertyName = preg_replace('/(.+?)Id/', '$1/id', $propertyName);
//        $propertyName = str_replace('/', '.', trim($propertyName, '/'));
        $propertyName = trim($propertyName, '/');
        return new DoctrineProperty($propertyName);
    }

    public function getPropertyName(DoctrineProperty $property) {
        $propertyName = trim($property->getName(), '/');
        if (strpos($propertyName, '/')) {
            $parts = explode('/', $propertyName);
            $propertyName = array_pop($parts);
            $alias = $this->alias . '_' . implode('_', $parts);
            $this->associations[] = implode('/', $parts);
            return $alias . '.' . $propertyName;
        }
        return $this->alias . '.' . $propertyName;
    }

    public function getParameterName($name = null) {
        if ($name !== null) {
            return ':' . $name;
        }
        return '?' . $this->parameterNum;
    }

    public function setParameterValue($value, $name = null) {
        if ($name !== null) {
            $this->parametersMap[$name] = $value;
            return;
        }
//		$this->queryBuilder->setParameter($this->parameterNum, $value);
        $this->parametersMap[$this->parameterNum] = $value;
//        echo '<pre>'; print_r($this->parametersMap); echo '</pre>';
        $this->parameterNum++;
    }

    public function parameter($value, $name = null) {
        $parameter = $this->getParameterName($name);
        $this->setParameterValue($value, $name);
        return $parameter;
    }

    public function process($expr) {
        return $expr;
    }

    public function logical($function, $left, $right = null) {
        $expr = $this->expr();
        $function .= $function == 'or' || $function == 'and' ? 'X' : '';
        if ($right === null) {
            return $expr->$function($left);
        }
        return $expr->$function($left, $right);
    }

    public function logicalAnd($left, $right) {
        return $this->logical('and', $left, $right);
    }

    public function logicalOr($left, $right) {
        return $this->logical('or', $left, $right);
    }

    public function logicalNot($left) {
        return $this->logical('not', $left);
    }

    public function expr() {
        return new Expr();
//		return $this->expr;
    }

    private function swap(&$left, &$right) {
//		$temp = $right;
//		$right = $left;
//		$left = $temp;
    }

    protected function getParamProcessed($param, $name = null) {
        if ($this->isExpr($param)) {
            return $param;
        } else if ($param instanceof DoctrineProperty) {
            return $this->getPropertyName($param);
        }
        return $this->parameter($param, $name);
    }

    protected function getDateTimeParamProcessed($param, $name = null) {
        if(is_string($param)){
            $param = new \DateTime($param);
            $param->setTimezone(new \DateTimeZone('UTC'));
        }
        return $this->getParamProcessed($param, $name);
    }

    public function onComparison($expr, $callback) {
//		$this->comparisonCallback = $callback;
        $this->comparisonCallbacks[serialize($expr)] = $callback;
        return $expr;
    }

    protected function getComparisonCallback($expr) {
//		$callback = $this->comparisonCallback;
//		$this->comparisonCallback = null;
//		return $callback;
//		$param = !$this->isExpr($left) && $this->isExpr($right) ? $right : $left;
        $key = serialize($expr);
        if (isset($this->comparisonCallbacks[$key])) {
            $callback = $this->comparisonCallbacks[$key];
            unset($this->comparisonCallbacks[$key]);
            return $callback;
        }
        return false;
    }

    function isExpr($expr) {
        return $expr instanceof Expr\Comparison || $expr instanceof Expr\Func || $expr instanceof Expr\Andx || $expr instanceof Expr\Orx
        ;
//        Expr\Func
//        Expr\Orx
//        Expr\Andx
    }

    public function compareEquality($left, $right, $notEqual = false) {
        $true = !$notEqual;
        $false = !$true;
        $expr = $this->expr();
        if ($this->isExpr($left) && $right === $true) {
            return $left;
        } else if ($this->isExpr($right) && $left === $true) {
            return $right;
        } else if ($this->isExpr($left) && $right === $false) {
            return $expr->not($left);
        } else if ($this->isExpr($right) && $left === $false) {
            return $expr->not($right);
        }
        return null;
    }

    protected function triggerComparison(&$left, &$right, $function) {
//		return;
//		$comparisonCallback = $this->comparisonCallback;
//		$this->comparisonCallback = null;
//		if (is_callable($comparisonCallback)) {
//			$result = $comparisonCallback($left, $right);
//			if ($result === true) {
//				return $left;
//			} else if ($result === false) {
//				return $expr->not($left);
//			} else {
////					return $expr->eq($left, $this->parameter($result));
//				return $expr->eq($left, $result);
//			}
//		}
        $comparisonCallback = $this->getComparisonCallback($left);

        if (is_callable($comparisonCallback)) {
//            $result = $comparisonCallback($left, $right, $function);
            $result = call_user_func($comparisonCallback, $left, $right, $function);
            if ($result) {
                return $result;
            }
//			if ($result === true) {
//				return $left;
//			} else if ($result === false) {
//				return $expr->not($left);
//			} else {
////					return $expr->eq($left, $this->parameter($result));
//				return $expr->$function($left, $result);
//			}
        }
    }

    public function compare($function, $left, $right) {
        $compared = $this->triggerComparison($left, $right, $function);
        if ($this->isExpr($compared)) {
            return $compared;
        }

        if ($function == 'eq' || $function == 'neq') {
            $compared = $this->compareEquality($left, $right, $function == 'neq');
            if ($this->isExpr($compared)) {
                return $compared;
            }
        }

        $expr = $this->expr();
        if (($function == 'eq' || $function == 'neq') 
                && ($left === null || $right === null)) {
            $nullFunc = $function == 'neq' ? 'isNotNull' : 'isNull';
            return $expr->$nullFunc($this->getParamProcessed($left !== null ? $left : $right));
        }
        return $expr->$function($this->getParamProcessed($left), $this->getParamProcessed($right));
    }

    public function compareNotEqual($left, $right) {
        return $this->compare('neq', $left, $right);
    }

    public function compareEqual($left, $right) {
        return $this->compare('eq', $left, $right);
    }

    public function compareGreaterThan($left, $right) {
        return $this->compare('gt', $left, $right);
    }

    public function compareGreaterThanOrEqual($left, $right) {
        return $this->compare('gte', $left, $right);
    }

    public function compareLessThan($left, $right) {
        return $this->compare('lt', $left, $right);
    }

    public function compareLessThanOrEqual($left, $right) {
        return $this->compare('lte', $left, $right);
    }

    public function str_replace($search, $find, $replace) {
        throw new NotImplementedException('replace method not implemented');
    }

    public function str_indexof($left, $right) {
//        throw new NotImplementedException('indexof method not implemented');

        $expr = $this->expr();

//		$leftParam = $this->getParamProcessed($left, 'strIndxOfLeftOp');
//		$rightParam = $this->getParamProcessed($right, 'strIndxOfRightOp');
        $leftParam = $this->getParamProcessed($left, 'left');
        $rightParam = $this->getParamProcessed($right, 'right');

        $callback = function(&$left, &$right, $function) use ($rightParam, $expr) {
            $args = $left->getArguments();
            $args[1] = $right + 1;
            $left = new Expr\Func($left->getName(), $args);
            return $expr->$function($left, $rightParam);
        };

//        return $this->onComparison(new Expr\Func('SUBSTRING', array($leftParam, 0, $expr->length($rightParam))), $callback);
        return $this->onComparison($expr->substring($leftParam, 0, $expr->length($rightParam)), $callback);
//		return new Expr\Func('INSTR', array($params['left'], $params['right']));
    }

    public function str_startswith($left, $right) {
        return $this->str_like($left, $right, 'startswith');
    }

    public function str_endswith($left, $right) {
        return $this->str_like($left, $right, 'endswith');
    }

    public function str_compare($left, $right) {
        $expr = $this->expr();
//        return $expr->eq($left, $right);
        return $this->onComparison(
//                $expr->like($this->getParamProcessed($left), $this->getParamProcessed($right)),
                        $expr->eq($this->getParamProcessed($left), $this->getParamProcessed($right)), function($left, $right, $function) use ($expr) {
                    if ($function == 'eq') {
                        return $left;
                    } else if ($function == 'neq') {
                        return $expr->not($left);
                    }
                    throw new UnSupportedExpression('Cannot compare with that operator');
                }
        );
//        return $this->onComparison(
//                        $this->str_like($left, $right, 'strict', true), function($left, $right, $function) use($expr) {
//                            if ($function == 'eq') {
//                                return $left;
//                            } else if ($function == 'neq') {
//                                return $expr->not($left);
//                            }
//                            return $left;
//                        });
    }

    public function datetime_compare($left, $right) {
        $expr = $this->expr();
//        return $expr->eq($left, $right);
        if (true ||
                false
        ) {
            $exprId = serialize($left) . '.' . serialize($right);

            $leftExpr = $this->getDateTimeParamProcessed($left);
            $rightExpr = $this->getDateTimeParamProcessed($right);
            return $this->onComparison(
//                $expr->like($this->getParamProcessed($left), $this->getParamProcessed($right)),
//                        $expr->lt($this->getParamProcessed($left), $this->getParamProcessed($right)), function($left, $right, $function) use ($expr) {
                            $exprId, function($left, $right, $function) use ($expr, $leftExpr, $rightExpr) {
                        if ($function == 'eq') {
//                                return $left;
                        } else if ($function == 'neq') {
//                                return $expr->not($left);
                        }
                        return $expr->$function($leftExpr, $rightExpr);
//                            throw new UnSupportedExpression('Cannot compare with that operator');
                    }
            );
        }
        return $this->onComparison(
                        $this->str_like($left, $right, 'strict', true), function($left, $right, $function) use($expr) {
                    if ($function == 'eq') {
                        return $left;
                    } else if ($function == 'neq') {
                        return $expr->not($left);
                    }
                    return $left;
                });
    }

    public function str_substringof($left, $right) {
        return $this->str_like($right, $left, 'any');
    }

    public function str_like($left, $right, $type = 'any', $swappable = false) {

        $expr = $this->expr();

        $params = array();
        if ($swappable && $right instanceof DoctrineProperty) {
            $this->swap($left, $right);
        }
        foreach (array('left', 'right') as $param) {
            if ($$param instanceof DoctrineProperty) {
                $params[$param] = $this->getPropertyName($$param);
            } else {
                $params[$param] = $this->parameter(
                        (($type == 'any' || ($type == 'endswith'/* && $param == 'left' */)) ? '%' : '')
                        . $$param .
                        (($type == 'any' || ($type == 'startswith'/* && $param == 'right' */)) ? '%' : ''));
            }
        }
//		$p = $expr->like($params['left'], $params['right'].'%');
        $p = $expr->like($params['left'], $params['right']);
        return $p;
    }

    // Misc functions

    public function str_substring($str, $index, $length = null) {
//		throw new NotImplementedException('replace not implemented yet');
        $expr = $this->expr();
        $strParam = $this->getParamProcessed($str);

        $indexParam = (int) $index + 1;
        $lengthParam = (int) $length;

//        $indexParam = $this->getParamProcessed((int)$index + 1);
//        $ex =
        if ($length !== null) {
//    		$lengthParam = $this->getParamProcessed($length);
            return $expr->substring($strParam, $indexParam, $lengthParam);
        }
        return $expr->substring($strParam, $indexParam);

//        print_r($ex);
//        exit;
    }

    public function str_func($function, $left, $right = null) {
        $expr = $this->expr();
        if ($right !== null) {
            return $expr->$function($this->getParamProcessed($left), $this->getParamProcessed($right));
        }
        return $expr->$function($this->getParamProcessed($left));
    }

    public function str_tolower($str) {
        return $this->str_func('lower', $str);
    }

    public function str_toupper($str) {
        return $this->str_func('upper', $str);
    }

    public function str_trim($str) {
        return $this->str_func('trim', $str);
    }

    public function str_concat($left, $right) {
        return $this->str_func('concat', $left, $right);
    }

    public function str_length($str) {
        return $this->str_func('length', $str);
    }

    public function __call($function, $args) {
        if (strpos($function, 'datetime_') === 0) {
            throw new NotImplementedException(str_ireplace('datetime_', '', $function) . ' method not implemented');
        }
        if (strpos($function, 'math_') === 0) {
            throw new NotImplementedException(str_ireplace('math_', '', $function) . ' method not implemented');
        }
        throw new NotImplementedException('method not implemented');
    }

}
