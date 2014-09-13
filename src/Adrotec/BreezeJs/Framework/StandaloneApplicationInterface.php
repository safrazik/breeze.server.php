<?php

namespace Adrotec\BreezeJs\Framework;

interface StandaloneApplicationInterface
{

    public function enableDebug();

    public function enableAnnotations();

    public function setAutoloader($loader);

    public function setConnection($connection);

    public function addMapping($mapping);

    public function addResources($resources);

    public function enableCors();

    public function build();

    public function run();
}
