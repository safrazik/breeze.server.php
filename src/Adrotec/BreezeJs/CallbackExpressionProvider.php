<?php

namespace Adrotec\BreezeJs;

use ODataProducer\UriProcessor\QueryProcessor\ExpressionParser\Expressions\ExpressionType;
use ODataProducer\Providers\Metadata\Type\IType;
//use ODataProducer\Common\NotImplementedException;
use ODataProducer\Common\ODataConstants;
use ODataProducer\UriProcessor\QueryProcessor\ExpressionParser\IExpressionProvider;
use ODataProducer\Providers\Metadata\ResourceType as OResourceType;

class CallbackExpressionProvider implements IExpressionProvider {
//
//	const ADD = '+';
//	const CLOSE_BRACKET = ')';
//	const COMMA = ',';
//	const DIVIDE = '/';
//	const SUBTRACT = '-';
//	const EQUAL = '=';
//	const GREATERTHAN = '>';
//	const GREATERTHAN_OR_EQUAL = '>=';
//	const LESSTHAN = '<';
//	const LESSTHAN_OR_EQUAL = '<=';
//	const LOGICAL_AND = '&&';
//	const LOGICAL_NOT = '!';
//	const LOGICAL_OR = '||';
//	const MEMBERACCESS = '';
//	const MODULO = '%';
//	const MULTIPLY = '*';
//	const NEGATE = '-';
//	const NOTEQUAL = '!=';
//	const OPEN_BRAKET = '(';

    const ADD = 'arithmeticAdd';
    const CLOSE_BRACKET = ')';
    const COMMA = ',';
    const DIVIDE = 'arithmeticDivide';
    const SUBTRACT = 'arithmeticSubtract';
    const EQUAL = 'compareEqual';
    const GREATERTHAN = 'compareGreaterThan';
    const GREATERTHAN_OR_EQUAL = 'compareGreaterThanOrEqual';
    const LESSTHAN = 'compareLessThan';
    const LESSTHAN_OR_EQUAL = 'compareLessThanOrEqual';
    const LOGICAL_AND = 'logicalAnd';
    const LOGICAL_NOT = 'logicalNot';
    const LOGICAL_OR = 'logicalOr';
    const MEMBERACCESS = '/';
    const MODULO = 'arithmeticModulo';
    const MULTIPLY = 'arithmeticMultiply';
    const NEGATE = 'arithmeticNegate';
    const NOTEQUAL = 'compareNotEqual';
    const OPEN_BRAKET = '(';
    //
    const STRFUN_COMPARE = 'str_compare';
    const STRFUN_ENDSWITH = 'str_endswith';
    const STRFUN_INDEXOF = 'str_indexof';
    const STRFUN_REPLACE = 'str_replace';
    const STRFUN_STARTSWITH = 'str_startswith';
    const STRFUN_TOLOWER = 'str_tolower';
    const STRFUN_TOUPPER = 'str_toupper';
    const STRFUN_TRIM = 'str_trim';
    const STRFUN_SUBSTRING = 'str_substring';
    const STRFUN_SUBSTRINGOF = 'str_substringof';
    const STRFUN_CONCAT = 'str_concat';
    const STRFUN_LENGTH = 'str_length';
    //
    const GUIDFUN_EQUAL = 'str_strcmp';
    //
    const DATETIME_COMPARE = 'datetime_compare';
    const DATETIME_YEAR = 'datetime_year';
    const DATETIME_MONTH = 'datetime_month';
    const DATETIME_DAY = 'datetime_day';
    const DATETIME_HOUR = 'datetime_hour';
    const DATETIME_MINUTE = 'datetime_minute';
    const DATETIME_SECOND = 'datetime_second';
    //
    const MATHFUN_ROUND = 'math_round';
    const MATHFUN_CEILING = 'math_ceil';
    const MATHFUN_FLOOR = 'math_floor';

    /**
     * The type of the resource pointed by the resource path segement
     *
     * @var OResourceType
     */
    private $_resourceType;

    /**
     *
     * @var array(string, array(string, string))
     */
    private $_iterName;

    /**
     * Get the name of the iterator
     *
     * @return string
     */
    public function getIteratorName() {
//        return null;
        return $this->_iterName;
    }

    /**
     * Constructs new instance of WordPressDSExpressionProvider
     *
     */
    public function __construct($iterName = '') {
        $this->_iterName = $iterName;
    }

    /**
     * call-back for setting the resource type.
     *
     * @param OResourceType $resourceType The resource type on which the filter
     *                                   is going to be applied.
     */
    public function setResourceType(OResourceType $resourceType) {
        $this->_resourceType = $resourceType;
    }

    /**
     * Call-back for logical expression
     *
     * @param ExpressionType $expressionType The type of logical expression.
     * @param string         $left           The left expression.
     * @param string         $right          The left expression.
     *
     * @return string
     */
    public function onLogicalExpression($expressionType, $left, $right) {
        switch ($expressionType) {
            case ExpressionType::AND_LOGICAL:
//				return '$logicalAnd('.$left.', '.$right.')';
                return $this->_prepareBinaryExpression(self::LOGICAL_AND, $left, $right);
                break;
            case ExpressionType::OR_LOGICAL:
//				return '$logicalOr('.$left.', '.$right.')';
                return $this->_prepareBinaryExpression(self::LOGICAL_OR, $left, $right);
                break;
            default:
                throw new \InvalidArgumentException('onLogicalExpression');
        }
    }

    /**
     * Call-back for arithmetic expression
     *
     * @param ExpressionType $expressionType The type of arithmetic expression.
     * @param string         $left           The left expression.
     * @param string         $right          The left expression.
     *
     * @return string
     */
    public function onArithmeticExpression($expressionType, $left, $right) {
        switch ($expressionType) {
            case ExpressionType::MULTIPLY:
                return $this->_prepareBinaryExpression(self::MULTIPLY, $left, $right);
                break;
            case ExpressionType::DIVIDE:
                return $this->_prepareBinaryExpression(self::DIVIDE, $left, $right);
                break;
            case ExpressionType::MODULO:
                return $this->_prepareBinaryExpression(self::MODULO, $left, $right);
                break;
            case ExpressionType::ADD:
                return $this->_prepareBinaryExpression(self::ADD, $left, $right);
                break;
            case ExpressionType::SUBTRACT:
                return $this->_prepareBinaryExpression(self::SUBTRACT, $left, $right);
                break;
            default:
                throw new \InvalidArgumentException('onArithmeticExpression');
        }
    }

    /**
     * Call-back for relational expression
     *
     * @param ExpressionType $expressionType The type of relation expression
     * @param string         $left           The left expression
     * @param string         $right          The right expression
     *
     * @return string
     */
    public function onRelationalExpression($expressionType, $left, $right) {
        switch ($expressionType) {
            case ExpressionType::GREATERTHAN:
                return $this->_prepareBinaryExpression(self::GREATERTHAN, $left, $right);
                break;
            case ExpressionType::GREATERTHAN_OR_EQUAL:
                return $this->_prepareBinaryExpression(
                                self::GREATERTHAN_OR_EQUAL, $left, $right
                );
                break;
            case ExpressionType::LESSTHAN:
                return $this->_prepareBinaryExpression(self::LESSTHAN, $left, $right);
                break;
            case ExpressionType::LESSTHAN_OR_EQUAL:
                return $this->_prepareBinaryExpression(
                                self::LESSTHAN_OR_EQUAL, $left, $right
                );
                break;
            case ExpressionType::EQUAL:
                return $this->_prepareBinaryExpression(self::EQUAL, $left, $right);
                break;
            case ExpressionType::NOTEQUAL:
                return $this->_prepareBinaryExpression(self::NOTEQUAL, $left, $right);
                break;
            default:
                throw new \InvalidArgumentException('onArithmeticExpression');
        }
    }

    /**
     * Call-back for unary expression
     *
     * @param ExpressionType $expressionType The type of unary expression
     * @param string         $child          The child expression
     *
     * @return string
     */
    public function onUnaryExpression($expressionType, $child) {
        switch ($expressionType) {
            case ExpressionType::NEGATE:
                return $this->_prepareUnaryExpression(self::NEGATE, $child);
                break;
            case ExpressionType::NOT_LOGICAL:
                return $this->_prepareUnaryExpression(self::LOGICAL_NOT, $child);
                break;
            default:
                throw new \InvalidArgumentException('onUnaryExpression');
        }
    }

    /**
     * Call-back for constant expression
     *
     * @param IType  $type  The type of constant
     * @param objetc $value The value of the constant
     *
     * @return string
     */
    public function onConstantExpression(IType $type, $value) {
        if (is_bool($value)) {
            return var_export($value, true);
        } else if (is_null($value)) {
            return var_export(null, true);
        }

        return $value;
    }

    public function onPropertyAccessExpression($expression) {
        $parent = $expression;
        $variable = null;

        do {
            $variable
                    = $parent->getResourceProperty()->getName()
                    . self::MEMBERACCESS
                    . $variable;
            $parent = $parent->getParent();
        } while ($parent != null);

        $variable = rtrim($variable, self::MEMBERACCESS);
//        $variable = self::MEMBERACCESS . $variable;
//        exit($variable);
        return $this->_iterName.'getProperty(\'' . $variable . '\')';
    }

    /**
     * Call-back for function call expression
     *
     * @param FunctionDescription $functionDescription Description of the function.
     * @param array<string>       $params              Paameters to the function.
     *
     * @return string
     */
    public function onFunctionCallExpression($functionDescription, $params) {
        $prefix = $this->_iterName;
        switch ($functionDescription->functionName) {
            case ODataConstants::STRFUN_COMPARE:
                return $prefix.self::STRFUN_COMPARE.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1].self::CLOSE_BRACKET;
                break;
            case ODataConstants::STRFUN_ENDSWITH:
                return $prefix.self::STRFUN_ENDSWITH.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1].self::CLOSE_BRACKET;
                break;
            case ODataConstants::STRFUN_INDEXOF:
                return $prefix.self::STRFUN_INDEXOF.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1].self::CLOSE_BRACKET;
                break;
            case ODataConstants::STRFUN_REPLACE:
                return $prefix.self::STRFUN_REPLACE.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1].self::COMMA.$params[2].self::CLOSE_BRACKET;
                break;
            case ODataConstants::STRFUN_STARTSWITH:
                return $prefix.self::STRFUN_STARTSWITH.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1].self::CLOSE_BRACKET;
                break;
            case ODataConstants::STRFUN_TOLOWER:
                 return $prefix.self::STRFUN_TOLOWER.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::STRFUN_TOUPPER:
                return $prefix.self::STRFUN_TOUPPER.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::STRFUN_TRIM:
                return $prefix.self::STRFUN_TRIM.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::STRFUN_SUBSTRING:
                return $prefix.self::STRFUN_SUBSTRING.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1]
                    .(count($params) == 3 ? (self::COMMA.$params[2]) : '').self::CLOSE_BRACKET;
                break;
            case ODataConstants::STRFUN_SUBSTRINGOF:
                return $prefix.self::STRFUN_SUBSTRINGOF.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1].self::CLOSE_BRACKET;
                break;
            case ODataConstants::STRFUN_CONCAT:
                return $prefix.self::STRFUN_CONCAT.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1].self::CLOSE_BRACKET;
                break; 
            case ODataConstants::STRFUN_LENGTH:
                return $prefix.self::STRFUN_LENGTH.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::GUIDFUN_EQUAL:
                return $prefix.self::GUIDFUN_EQUAL.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1].self::CLOSE_BRACKET;
                break;
            case ODataConstants::DATETIME_COMPARE:
//                exit($prefix.self::DATETIME_COMPARE.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1].self::CLOSE_BRACKET);
                return $prefix.self::DATETIME_COMPARE.self::OPEN_BRAKET.$params[0].self::COMMA.$params[1].self::CLOSE_BRACKET;
                break;
            case ODataConstants::DATETIME_YEAR:
                return $prefix.self::DATETIME_YEAR.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::DATETIME_MONTH:
                return $prefix.self::DATETIME_MONTH.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::DATETIME_DAY:
                return $prefix.self::DATETIME_DAY.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::DATETIME_HOUR:
                return $prefix.self::DATETIME_HOUR.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::DATETIME_MINUTE:
                return $prefix.self::DATETIME_MINUTE.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::DATETIME_SECOND:
                return $prefix.self::DATETIME_SECOND.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::MATHFUN_ROUND:
                return $prefix.self::MATHFUN_ROUND.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::MATHFUN_CEILING:
                return $prefix.self::MATHFUN_CEILING.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::MATHFUN_FLOOR:
                return $prefix.self::MATHFUN_FLOOR.self::OPEN_BRAKET.$params[0].self::CLOSE_BRACKET;
                break;
            case ODataConstants::BINFUL_EQUAL:
                return "($params[0] = $params[1])";
                break;
            case 'is_null':
                return "is_null($params[0])";
                break;

            default:
                throw new \InvalidArgumentException('onFunctionCallExpression');
        }
    }

    private function _prepareBinaryExpression($operator, $left, $right) {
//echo '"'.$left.'", "'.$right.'"'.'<br>';
//exit;
        // Special handling for DATETIMECMP
        if (false && !substr_compare($left, self::DATETIME_COMPARE, 0, strlen(self::DATETIME_COMPARE))) {
            $str = explode(';', $left, 2);
            $str[0] = str_replace(self::DATETIME_COMPARE, '', $str[0]);
            return self::OPEN_BRAKET
                    . $str[0] . ' ' . $operator
                    . ' ' . $str[1] . self::CLOSE_BRACKET;
        }

        return $this->_iterName.$operator.self::OPEN_BRAKET.$left.self::COMMA.$right.self::CLOSE_BRACKET;
//				self::OPEN_BRAKET
//				. $left . ' ' . $operator
//				. ' ' . $right . self::CLOSE_BRACKET;
                "\r\n" . $operator . self::OPEN_BRAKET
                . $left . ', '// . $operator
                . ' ' . $right . self::CLOSE_BRACKET . "\r\n";
    }

    /**
     * To format unary expression
     *
     * @param string $operator The unary operator.
     * @param string $child    The operand.
     *
     * @return string
     */
    private function _prepareUnaryExpression($operator, $child) {
        return $this->_iterName.$operator.self::OPEN_BRAKET.$child.self::CLOSE_BRACKET;
//        return $operator . self::OPEN_BRAKET . $child . self::CLOSE_BRACKET;
    }

}