<?php

namespace Adrotec\BreezeJs\Metadata\Validator;

use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * @ExclusionPolicy("none")	
 */
class RegularExpressionValidator {

    public $name = 'regularExpression';

    public $expression;

    public function __construct($expression) {
        $this->expression = $expression;
    }

}
