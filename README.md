# breeze.server.php (Doctrine 2)

(Inspired by [breeze.server.java](https://github.com/Breeze/breeze.server.java))

This project is a PHP library that facilitates building [Breeze](http://www.breezejs.com/)-compatible backends using
[Doctrine](http://hibernate.org/orm/).

### Features:

- Generates [Breeze metadata](http://www.breezejs.com/documentation/metadata) from Doctrine mappings
- Parses (a subset of) [OData](http://www.odata.org/documentation/odata-version-3-0/url-conventions/) queries into [QueryBuilder](http://docs.doctrine-project.org/en/2.0.x/reference/query-builder.html) instance
- Executes queries using Doctrine Entity Manager
- Expands graphs of related entites using EAGER loading with joins
- Serializes query results to [JSON](http://www.json.org/) with [JMSSerializer](http://jmsyst.com/libs/serializer)
- Handles saving Breeze payloads in Doctrine

To see these features in action, please see the [NorthBreeze sample](https://github.com/Breeze/breeze.js.samples/tree/master/java/NorthBreeze).

## Usage

### Basic Usage

```php

<?php

// Demo implementation

use Adrotec\BreezeJs\Serializer\MetadataInterceptor;
use Adrotec\BreezeJs\Doctrine\ORM\Dispatcher;

class Controller {
    protected function getSerializer(){
        // get/create the serializer instace
        /* @var $serializer \JMS\Serializer\Serializer */
        return $serializer;
    }
    protected function getEntityManager(){
        // get/create the Entity Manager instance
        /* @var $entityManager \Doctrine\ORM\EntityManager */
        return $entityManager;
    }
    protected function getDispatcher(){
        $dispatcher = new Dispatcher($this->getEntityManager());
        return $dispatcher;
    }
    
    protected function sendResponse($response){
        // set response headers here
        echo $this->getSerializer()->serialize($response, 'json');
    }

    public function Metadata(){
        // limit the api to certain classes. If you want to expose all classes 
        // from the entity manager, pass null for $classes parameter;
        $classes = array(
            'Acme\Entity\Customer',
            'Acme\Entity\Order',
            'Acme\Entity\Payment'
        );
        $interceptor = new MetadataInterceptor($serializer);
        $response = $this->getDispatcher()->getMetadata($classes, $interceptor);
        return $this->sendResponse($response);
    }
    public function SaveChanges(){
        $input = file_get_contents('php://input');
        $response = $this->getDispatcher()->saveChanges($input);
        return $this->sendResponse($response);
    }
    //
    public function Customers(){
        $params = $_GET;
        $response = $this->getDispatcher()->getQueryResults('Acme\Entity\Customer', $params);
        return $this->sendResponse($response);
    }
}

```
