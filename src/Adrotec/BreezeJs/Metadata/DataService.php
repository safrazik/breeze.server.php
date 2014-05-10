<?php

namespace Adrotec\BreezeJs\Metadata;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ExclusionPolicy("none")	
 */
class DataService {

	/**
	 * @SerializedName("serviceName")
	 */
	public $serviceName;

	/**
	 * @SerializedName("hasServerMetadata")
	 */
	public $hasServerMetadata = true;

	/**
	 * @SerializedName("jsonResultsAdapter")
	 */
	public $jsonResultsAdapter;

	/**
	 * @SerializedName("useJsonp")
	 */
	public $useJsonp = false;

}