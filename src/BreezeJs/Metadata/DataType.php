<?php

namespace BreezeJs\Metadata;

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