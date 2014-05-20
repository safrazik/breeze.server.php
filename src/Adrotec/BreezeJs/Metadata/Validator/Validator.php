<?php

namespace Adrotec\BreezeJs\Metadata\Validator;

class Validator {
    
    const CREDIT_CARD = "creditCard";
    const EMAIL_ADDRESS = "emailAddress";
    const MAX_LENGTH = "maxLength";
    const PHONE = "phone";
    const REGULAR_EXPRESSION = "regularExpression";
    const REQUIRED = "required";
    const URL = "url";
    const NONE = 'none';

    public $name;
    
    public function __construct($name = self::NONE) {
        $this->name = $name;
    }
    
}