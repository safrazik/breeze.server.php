<?php

namespace Adrotec\BreezeJs;

class OdataParameters {

    const PARAM_EXPAND = '$expand';
    const PARAM_FILTER = '$filter';
    const PARAM_FORMAT = '$format';
    const PARAM_INLINECOUNT = '$inlinecount';
    const PARAM_ORDERBY = '$orderby';
    const PARAM_SELECT = '$select';
    const PARAM_SKIP = '$skip';
    const PARAM_TOP = '$top';

    private static $systemQueryOptions = array(self::PARAM_EXPAND, self::PARAM_FILTER,
        self::PARAM_FORMAT, self::PARAM_INLINECOUNT, self::PARAM_ORDERBY,
        self::PARAM_SELECT, self::PARAM_SKIP, self::PARAM_TOP);
    public $skip;
    public $top;
    public $expand;
    public $filter;
    public $orderby;
    public $format;
    public $inlinecount;
    public $select;

    /**
     * @param mixed $params
     * @return OdataParameters
     */
    static public function parse($params) {
        if ($params instanceof \Traversable || is_array($params)) {
            
        }
        else if(is_string($params)){
            parse_str($params, $output);
            $params = $output;
        }
        $op = new self();
        foreach ($params as $name => $value) {
            if (!in_array($name, self::$systemQueryOptions)) {
                continue;
            }
            self::apply($op, $name, $value);
        }
        return $op;
    }

    static public function apply(OdataParameters &$op, $name, $value) {
        switch ($name) {
            case self::PARAM_EXPAND:
                $op->expand = $value;
                return;
            case self::PARAM_FILTER:
                $op->filter = $value;
                return;
            case self::PARAM_FORMAT:
                $op->format = $value;
                return;
            case self::PARAM_INLINECOUNT:
                $op->inlinecount = $value;
                return;
            case self::PARAM_ORDERBY:
                $op->orderby = $value;
                return;
            case self::PARAM_SELECT:
                $op->select = $value;
                return;
            case self::PARAM_SKIP:
                $op->skip = $value;
                return;
            case self::PARAM_TOP:
                $op->top = $value;
                return;
        }
        throw new \Exception('Unsupported System Query Option "' . $name . '"');
    }

}
