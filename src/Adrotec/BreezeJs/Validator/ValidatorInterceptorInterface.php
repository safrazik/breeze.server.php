<?php

namespace Adrotec\BreezeJs\Validator;

use Adrotec\BreezeJs\Metadata\StructuralType;

interface ValidatorInterceptorInterface {
    
    public function modifyStructuralType(StructuralType &$structuralType);

}