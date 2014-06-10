<?php

namespace Adrotec\BreezeJs\Metadata;

// temporariy fix for Doctrine annotations to work
new \JMS\Serializer\Annotation\Exclude();
new \JMS\Serializer\Annotation\AccessType();
new \JMS\Serializer\Annotation\Accessor();
new \JMS\Serializer\Annotation\ReadOnly();

class Metadata {

    public $metadataVersion;
    public $namingConvention;
    public $localQueryComparisonOptions // = 'caseInsensitiveSQL'
    ;
    public $dataServices;
    public $structuralTypes = array();
    public $resourceEntityTypeMap = array();
}
