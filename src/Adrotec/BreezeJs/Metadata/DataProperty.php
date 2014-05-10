<?php

namespace Adrotec\BreezeJs\Metadata;

use Adrotec\BreezeJs\Metadata\DataType;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\AccessType;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\ReadOnly;
use ODataProducer\Providers\Metadata\ResourceType;
use ODataProducer\Providers\Metadata\ResourceProperty;
use ODataProducer\Providers\Metadata\ResourceTypeKind;
use ODataProducer\Providers\Metadata\ResourcePropertyKind;

/**
 * @ExclusionPolicy("none")	
 */
class DataProperty extends Property {

    /**
     * @SerializedName("name")
     */
    public $name;

    /**
     * @SerializedName("nameOnServer")
     */
    public $nameOnServer;

    /**
     * @SerializedName("dataType")
     * @AccessType("public_method")
     * @Accessor(getter="getDataTypeName")
     * @ReadOnly
     */
    public $dataType = DataType::STRING;

    /**
     * @SerializedName("isNullable")
     */
    public $isNullable;

    /**
     * @SerializedName("defaultValue")
     */
    public $defaultValue;

    /**
     * @SerializedName("isPartOfKey")
     */
    public $isPartOfKey;

    /**
     * @SerializedName("maxLength")
     */
    public $maxLength;

    /**
     * @SerializedName("validators")
     */
    public $validators;

    /**
     * @Exclude
     */
    public $structuralType;

    public function getDataTypeName() {
        return DataType::getName($this->dataType);
    }

}
