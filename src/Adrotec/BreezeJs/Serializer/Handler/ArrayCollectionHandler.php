<?php

namespace Adrotec\BreezeJs\Serializer\Handler;

//use Doctrine\Common\Collections\ArrayCollection;
//use JMS\Serializer\GraphNavigator;
use JMS\Serializer\VisitorInterface;
use Doctrine\Common\Collections\Collection;
//use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Handler\ArrayCollectionHandler as JMSArrayCollectionHandler;

use JMS\Serializer\Context;

class ArrayCollectionHandler extends JMSArrayCollectionHandler {

	
	public function serializeCollection(VisitorInterface $visitor, Collection $collection, array $type, Context $context) {
		
		if ($collection instanceof \Doctrine\ORM\PersistentCollection) {
			if(!$collection->isInitialized()){
				return;
			}
		}

		return parent::serializeCollection($visitor, $collection, $type, $context);
	}
}
