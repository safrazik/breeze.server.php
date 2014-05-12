<?php

namespace Adrotec\BreezeJs\Metadata;

use Adrotec\BreezeJs\Metadata\DataType;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("none")	
 */
class DataProperty extends Property {

    /**
     * @Serializer\AccessType("public_method")
     * @Serializer\Accessor(getter="getDataTypeName")
     * @Serializer\ReadOnly
     */
    public $dataType = DataType::STRING;
    public $isNullable;
    public $defaultValue;
    public $isPartOfKey;
    public $maxLength;

    public function getDataTypeName() {
        return DataType::getName($this->dataType);
    }

}
