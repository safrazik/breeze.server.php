# breeze.server.php

This project is a PHP library that facilitates building [Breeze JS](http://www.breezejs.com/) compatible backends using
[Doctrine](http://www.doctrine-project.org/projects/orm.html)

## Features:

- Framework agnostic
- Generates [Breeze metadata](http://www.breezejs.com/documentation/metadata) from Doctrine mappings
- Expands graphs of related entites using EAGER loading with joins
- Serializes query results to JSON with [JMSSerializer](http://jmsyst.com/libs/serializer)
- Handles saving Breeze payloads with Doctrine Unit of Work
- Supports breeze validations with [Symfony Validator Component](http://symfony.com/components/Validator)
- Supports breeze inheritance and polymorphic queries. Only [Single Table Inheritance](http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#single-table-inheritance)
and [Class Table Inheritance](http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#class-table-inheritance) are supported in both server and client. But you can use [Mapped Superclasses](http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#mapped-superclasses) in the server without any fear.

### [Doctrine](http://www.doctrine-project.org/projects/orm.html)

a well documented, feature rich and popular Object Relational Mapper for PHP which supports several database systems

#### Why use Doctrine? (extracted from doctrine [website](http://www.doctrine-project.org/))
  - Around since 2006 with very stable, high-quality codebase.
  - Extremely flexible and powerful object-mapping and query features.
  - Support for both high-level and low-level database programming for all your use-cases.
  - Large Community and integrations with many different frameworks (Symfony, Zend Framework, CodeIgniter, FLOW3, Lithium and more

Currently this library supports Doctrine ORM only. Future versions should support [Doctrine MongoDB ODM](http://www.doctrine-project.org/projects/mongodb-odm.html) too.

Some of the [Doctrine Types](http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html) are converted into [Breeze Data Types](http://www.breezejs.com/sites/all/apidocs/classes/DataType.html)
#### Built in Doctrine types with their breeze equivalent types

* `string` - `String` - SQL VARCHAR to a PHP string.
* `integer` - `Int32` - SQL INT to a PHP integer.
* `smallint` - `Int32` - SMALLINT to a PHP integer.
* `bigint` - `Int32` - BIGINT to a PHP string.
* `boolean` - `Boolean` - SQL boolean to a PHP boolean.
* `decimal` - `Decimal` - SQL DECIMAL to a PHP double.
* `date` - `DateTime` - SQL DATETIME to a PHP DateTime object.
* `time` - `DateTime` - SQL TIME to a PHP DateTime object.
* `datetime` - `DateTime` - SQL DATETIME/TIMESTAMP to a PHP DateTime object.
* `float` - `Double` - SQL Float (Double Precision) to a PHP double. IMPORTANT: Works only with locale settings that use decimal points as separator.
* Other data types fall back to `String`
  
### [JMS Serializer](http://jmsyst.com/libs/serializer)
 a powerful serialization library for PHP. Provides more control over your serialized results. e.g: if you want to exclude a property from returned results, you may use the [@Exclude](http://jmsyst.com/libs/serializer/master/reference/annotations#exclude) annotation. Read the [documentation](http://jmsyst.com/libs/serializer) to find out more.

### [Symfony Validator Component](http://symfony.com/components/Validator)

(Optional, if you want to support validation) a powerful validation service for PHP with out of box support for Doctrine.

Please note that, by using the Symfony components, it does not necessarily mean you have to use the full stack symfony framework, since they are decoupled and standalone [components](http://symfony.com/components).

Some of the [Validation Constraints](http://symfony.com/doc/current/reference/constraints.html) are converted to equivalent [breeze validators](http://www.breezejs.com/documentation/validation). 

#### Built in Validation Constraints with their Breeze equivalent validators

* [Luhn](http://symfony.com/doc/current/reference/constraints/Luhn.html) - creditCard
* [Email](http://symfony.com/doc/current/reference/constraints/Email.html) - emailAddress
* [Length](http://symfony.com/doc/current/reference/constraints/Length.html) - maxLength
* [Regex](http://symfony.com/doc/current/reference/constraints/Regex.html) - regularExpression
* [NotBlank](http://symfony.com/doc/current/reference/constraints/NotBlank.html) - required
* [Url](http://symfony.com/doc/current/reference/constraints/Url.html) - url



## Example/Demo

- **Featured** - [EmpDirectory sample application](https://github.com/adrotec/emp-directory)
- **Basic** - [https://github.com/adrotec/breeze.server.php.demo](https://github.com/adrotec/breeze.server.php.demo)

## Installation

The library uses [composer](https://getcomposer.org/), the package manager for PHP.

add these lines to your `composer.json` and run `composer update`

```json
    "require": {
        "adrotec/breeze.server.php": "dev-master"
    }
```

Please note that `symfony/validator - 2.6+` is required by `"adrotec/breeze.server.php"` since the library relies on [`ConstraintViolation::getConstraint()`](https://github.com/symfony/Validator/blob/master/ConstraintViolation.php#L199) method which is not (yet) available in the older versions.

## Usage

The library provides a basic framework to easily bootstrap the API. You may use either `Application` or `StandaloneApplication` class.

### Using the `Application` class

```php
/* @var $entityManager instanceof \Doctrine\ORM\EntityManager */
/* @var $serializer instanceof \JMS\Serializer\SerializerInterface */
/* @var $validator instanceof \Symfony\Component\Validator\Validator\ValidatorInterface */

$app = new Adrotec\BreezeJs\Framework\Application(
  $entityManager,
  $serializer,
  $validator
);

$app->addResources(array(
    'Employees' => 'EmpDirectory\Model\Employee',
    'Departments' => 'EmpDirectory\Model\Department',
    'Jobs' => 'EmpDirectory\Model\Job',
));

/* @var $request instanceof \Symfony\Component\HttpFoundation\Request */

$response = $app->handle($request);
```

### Using the `StandaloneApplication` class

```php

$loader = require 'vendor/autoload.php';

$app = new Adrotec\BreezeJs\Framework\StandaloneApplication();

$app->setConnection(array(
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'dbname' => 'employees',
    'user' => 'root',
    'password' => ''
));

// configuring doctrine, serializer and validator
// using xml mappings
$app->addMapping(array(
    'namespace' => 'EmpDirectory\Model',
    'type' => 'xml',
    'extension' => '.orm.xml', // default ".dcm.xml"
    'doctrine' => __DIR__ . '/src/EmpDirectory/config/doctrine', // doctrine directory
    'serializer' => __DIR__ . '/src/EmpDirectory/config/serializer', // [optional] serializer metadata directory
    'validation' => __DIR__ . '/src/EmpDirectory/config/validation.xml', // [optional] validation file
));

// limiting the api to certain classes
$app->addResources(array(
    // Resource name => Class name
    'Employees' => 'EmpDirectory\Model\Employee',
    'Jobs' => 'EmpDirectory\Model\Job',
    'Departments' => 'EmpDirectory\Model\Department',
));

$app->build();

$app->run();

```

#### With Symfony 2

There's a [bundle](https://github.com/adrotec/AdrotecwebapiBundle) for that!
