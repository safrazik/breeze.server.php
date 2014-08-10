<?php

namespace Adrotec\BreezeJs\Metadata\Validator;

use Adrotec\BreezeJs\Metadata\Validator\Validator;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ExclusionPolicy("none")	
 */
class LengthValidator {

    /**
     * @SerializedName("name")
     */
    public $name = Validator::MAX_LENGTH;

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
