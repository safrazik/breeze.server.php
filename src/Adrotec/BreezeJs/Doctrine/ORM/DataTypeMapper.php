<?php

namespace Adrotec\BreezeJs\Doctrine\ORM;

use ODataProducer\Providers\Metadata\Type\EdmPrimitiveType;
use ODataProducer\Common\ODataConstants as Constants;

class DataType extends EdmPrimitiveType {
    
    public static function getName($code, $defaultName = null){
        $names = array(
            DataType::BINARY => Constants::EDM_BINARYTYPE_NAME,
            DataType::BOOLEAN => Constants::EDM_BOOLEANTYPE_NAME,
            DataType::BYTE => Constants::EDM_BYTETYPE_NAME,
            DataType::DATETIME => Constants::EDM_DATETIMETYPE_NAME,
            DataType::DECIMAL => Constants::EDM_DECIMALTYPE_NAME,
            DataType::DOUBLE => Constants::EDM_DOUBLETYPE_NAME,
            DataType::GUID => Constants::EDM_GUIDTYPE_NAME,
            DataType::INT16 => Constants::EDM_INT16TYPE_NAME,
            DataType::INT32 => Constants::EDM_INT32TYPE_NAME,
            DataType::INT64 => Constants::EDM_INT64TYPE_NAME,
            DataType::SBYTE => Constants::EDM_SBYTETYPE_NAME,
            DataType::SINGLE => Constants::EDM_SINGLETYPE_NAME,
            DataType::STRING => Constants::EDM_STRINGTYPE_NAME,
        );
        $name = isset($names[$code]) ? $names[$code] : ($defaultName ? $defaultName : $names[DataType::STRING]);
        return str_replace('Edm.', '', $name);
    }
}

class DataTypeMapper {

//	public static function fromDoctrineToBreezeJs($dataType, $defaultDataType = "String") {
//        return self::fromDoctrineToODataName($dataType, $defaultDataType);
//	}
//
//	public static function fromDoctrineToODataName($dataType, $defaultDataType = "String") {
//        $code = self::fromDoctrineToOData($dataType, $defaultDataType);
//        return DataType::getName($code);
//	}
//
//	public static function fromDoctrineToEdmType($dataType, $defaultDataType = DataType::STRING) {
//        return self::fromDoctrineToOData($dataType, $defaultDataType);
//	}

	public static function fromDoctrineToOData($dataType, $defaultDataType = DataType::STRING) {
		$dataTypes = array(
			"string" => DataType::STRING, // Type that maps an SQL VARCHAR to a PHP string.
			"integer" => DataType::INT32, // Type that maps an SQL INT to a PHP integer.
			"smallint" => DataType::INT32, // Type that maps a database SMALLINT to a PHP integer.
			"bigint" => DataType::INT32, // Type that maps a database BIGINT to a PHP string.
			"boolean" => DataType::BOOLEAN, // Type that maps an SQL boolean to a PHP boolean.
			"decimal" => DataType::DECIMAL, // Type that maps an SQL DECIMAL to a PHP double.
			"date" => DataType::DATETIME, // Type that maps an SQL DATETIME to a PHP DateTime object.
			"time" => DataType::DATETIME,// Type that maps an SQL TIME to a PHP DateTime object.
			"datetime" => DataType::DATETIME, // Type that maps an SQL DATETIME/TIMESTAMP to a PHP DateTime object.
//			"text" => "String", // Type that maps an SQL CLOB to a PHP string.
//			"object" => "String", // Type that maps a SQL CLOB to a PHP object using serialize() and unserialize()
//			"array" => "String", // Type that maps a SQL CLOB to a PHP object using serialize() and unserialize()
			"float" => DataType::DOUBLE, // Type that maps a SQL Float (Double Precision) to a PHP double. IMPORTANT: Works only with locale settings that use decimal points as separator.
		);
		return isset($dataTypes[$dataType]) ? $dataTypes[$dataType] : $defaultDataType;
	}    
    
	public static function fromODataToDoctrine($dataType, $defaultDataType = null) {
// OData 
//			"Null", // the absence of a value
//			"DateTimeOffset", // date and time as an Offset in minutes from GMT, with values ranging from 12:00:00 midnight, January 1, 1753 A.D. through 11:59:59 P.M, December 9999 A.D
//			"Undefined"
		$dataTypes = array(
//            DataType::BINARY => "", // fixed- or variable- length binary data
            DataType::BOOLEAN => "boolean", // the mathematical concept of binary-valued logic
//            DataType::BYTE => "", // Unsigned 8-bit integer value
            DataType::DATETIME => "datetime", // date and time with values ranging from 12:00:00 midnight, January 1, 1753 A.D. through 11:59:59 P.M, December 9999 A.D.
            DataType::DECIMAL => "decimal", // numeric values with fixed precision and scale. This type can describe a numeric value ranging from negative 10^255 + 1 to positive 10^255 -1
            DataType::DOUBLE => "float", // a floating point number with 15 digits precision that can represent values with approximate range of Â± 2.23e -308 through Â± 1.79e +308
//            DataType::GUID => "", // a 16-byte (128-bit) unique identifier value
            DataType::INT16 => "smallint", // a signed 16-bit integer value
            DataType::INT32 => "integer", // a signed 32-bit integer value
            DataType::INT64 => "bigint", // a signed 64-bit integer value
//            DataType::SBYTE => "", // a signed 8-bit integer value
            DataType::SINGLE => "float", // a floating point number with 7 digits precision that can represent values with approximate range of Â± 1.18e -38 through Â± 3.40e +38
            DataType::STRING => "string", // fixed- or variable-length character data
		);
		return isset($dataTypes[$dataType]) ? $dataTypes[$dataType] : ($defaultDataType ? $defaultDataType : $dataTypes[DataType::STRING]);
	}

}