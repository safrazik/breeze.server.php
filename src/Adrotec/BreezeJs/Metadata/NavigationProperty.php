<?php

namespace Adrotec\BreezeJs\Metadata;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;

use ODataProducer\Providers\Metadata\ResourceType;
use ODataProducer\Providers\Metadata\ResourceProperty;
use ODataProducer\Providers\Metadata\ResourceTypeKind;
use ODataProducer\Providers\Metadata\ResourcePropertyKind;
/**
 * @ExclusionPolicy("none")	
 */
class NavigationProperty extends Property {

	/**
	 * @SerializedName("name")
	 */
	public $name;

	/**
	 * @SerializedName("entityTypeName")
	 */
	public $entityTypeName;

	/**
	 * @SerializedName("nameOnServer")
	 */
	public $nameOnServer;

	/**
	 * @SerializedName("isScalar")
	 */
	public $isScalar = false;

	/**
	 * @SerializedName("associationName")
	 */
	public $associationName;

	/**
	 * @SerializedName("foreignKeyNames")
	 */
	public $foreignKeyNames;

	/**
	 * @SerializedName("foreignKeyNamesOnServer")
	 */
	public $foreignKeyNamesOnServer;

	/**
	 * @SerializedName("validators")
	 */
	public $validators;
        
        
        /**
         * @Exclude
         */        
        public $structuralType;

}