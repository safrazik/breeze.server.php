<?php

namespace Adrotec\BreezeJs\Metadata;

class Metadata {

    public $metadataVersion;
    public $namingConvention;
    public $localQueryComparisonOptions // = 'caseInsensitiveSQL'
    ;
    public $dataServices;
    public $structuralTypes = array();
    public $resourceEntityTypeMap = array();
}
