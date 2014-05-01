<?php

namespace BreezeJs\Serializer\Handler;

use JMS\Serializer\JsonDeserializationVisitor;
use Symfony\Component\Yaml\Inline;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\GraphNavigator;

use JMS\Serializer\Context;

use JMS\Serializer\Handler\DateHandler as JMSDateHandler;

class DateHandler extends JMSDateHandler
{
    private $defaultFormat;
    private $defaultTimezone;

    public function __construct($defaultFormat = \DateTime::ISO8601, $defaultTimezone = 'UTC')
    {
        $this->defaultFormat = $defaultFormat;
//        $defaultTimezone = 'UTC';
        $this->defaultTimezone = new \DateTimeZone($defaultTimezone);
    }

    public function serializeDateTime(VisitorInterface $visitor, \DateTime $date, array $type, Context $context)
    {
        return $visitor->visitString($date->format($this->getFormat($type)), $type, $context);
    }

    /**
     * @return string
     * @param array $type
     */
    private function getFormat(array $type)
    {
        return //isset($type['params'][0]) ? $type['params'][0] : //'Y-m-d\TH:i:s';//
            \DateTime::W3C;
//        $this->defaultFormat;
    }
}
