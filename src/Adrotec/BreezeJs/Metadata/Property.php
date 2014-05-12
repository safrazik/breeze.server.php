<?php

namespace Adrotec\BreezeJs\Metadata;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("none")	
 */
class Property {

    public $name;
    public $nameOnServer;
    public $validators;

    /**
     * @Serializer\Exclude
     */
    public $structuralType;

}
