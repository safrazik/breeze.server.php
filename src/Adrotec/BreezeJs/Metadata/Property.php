<?php

namespace Adrotec\BreezeJs\Metadata;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ExclusionPolicy("none")	
 */
class Property {
    
    /**
     * @SerializedName("name")
     */
    public $name;

    /**
     * @SerializedName("nameOnServer")
     */
    public $nameOnServer;

    /**
     * @SerializedName("validators")
     */
    public $validators;

    /**
     * @Exclude
     */
    public $structuralType;

}
