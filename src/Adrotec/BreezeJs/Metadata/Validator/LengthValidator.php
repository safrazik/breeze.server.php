<?php

namespace Adrotec\BreezeJs\Metadata\Validator;

use Adro\WebApi\Metadata\Validator;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ExclusionPolicy("none")	
 */
class LengthValidator {

    /**
     * @SerializedName("name")
     */
    public $name = Validator::STRING_LENGTH;

    /**
     * @SerializedName("minLength")
     */
    public $minLength;

    /**
     * @SerializedName("maxLength")
     */
    public $maxLength;

    public function __construct($minLength, $maxLength) {
//        $this->name = 'stringLength';
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
    }

}
