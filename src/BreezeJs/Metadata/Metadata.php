<?php

namespace BreezeJs\Metadata;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ExclusionPolicy("none")	
 */
class Metadata {

	/**
	 * @SerializedName("metadataVersion")
	 */
	public $metadataVersion;

	/**
	 * @SerializedName("namingConvention")
	 */
	public $namingConvention;

	/**
	 * @SerializedName("localQueryComparisonOptions")
	 */
	public $localQueryComparisonOptions // = 'caseInsensitiveSQL'
	;

	/**
	 * @SerializedName("dataServices")
	 */
	public $dataServices;

	/**
	 * @SerializedName("structuralTypes")
	 */
	public $structuralTypes = array();

	/**
	 * @SerializedName("resourceEntityTypeMap")
	 */
	public $resourceEntityTypeMap = array();

}