<?php

namespace Adrotec\BreezeJs\Validator;

use Adrotec\BreezeJs\Metadata\StructuralType;
use Adrotec\BreezeJs\Metadata\Property;
use Adrotec\BreezeJs\Metadata\DataProperty;
use Adrotec\BreezeJs\Metadata\NavigationProperty;
use Symfony\Component\Validator\ValidatorInterface;
use Adrotec\BreezeJs\Validator\ValidatorConstraintConverter as Converter;

use Adrotec\BreezeJs\Metadata\Validator as MetaValidator;

class ValidatorInterceptor {

    private $validator;
    private $validatorMetadataFactory;

    public function __construct(ValidatorInterface $validator) {
        $this->validator = $validator;
        $this->validatorMetadataFactory = $validator->getMetadataFactory();
    }

    public function modifyStructuralType(StructuralType &$structuralType) {
        
        $validatorMetadata = $this->validatorMetadataFactory->getMetadataFor($structuralType->reflectionClass->getName());

        if(!$validatorMetadata){
            return;
        }
        
        foreach ($structuralType->dataProperties as &$dataProperty) {
            $propertyName = $dataProperty->name;
            if (isset($validatorMetadata->properties[$propertyName])) {
                $propertyMetadata = $validatorMetadata->properties[$propertyName];
                foreach ($propertyMetadata->constraints as $constraint) {
                    if ($validator = $this->convertValidationConstraint($constraint)) {
                        if (empty($dataProperty->validators)) {
                            $dataProperty->validators = array();
                        }
                        $dataProperty->validators[] = $validator;
                    }
                }
            }
        }
    }

    protected function convertValidationConstraint($constraint) {
        /*
         * _____required
         * maxLength
         * _____stringLength
         * _____string
         * guid
         * duration
         * _____number, double
         * _____integer, int64
         * _____int32
         * _____int16
         * _____byte
         * _____bool
         * _____none
         * date
         *
         */
        $validator = null;

        $converter = new Converter();
        $validator = $converter->convert($constraint, $options);
        if (!$validator) {
            return null;
        }
        if ($validator == Converter::VALIDATOR_MAX_LENGTH) {
            return new MetaValidator\LengthValidator($options['minLength'], $options['maxLength']);
        }
        if($validator == Converter::VALIDATOR_REGULAR_EXPRESSION){
            return new MetaValidator\RegularExpressionValidator($options['expression']);
        }
        return new MetaValidator\Validator($validator);
    }

    public function validateEntity($entity) {
        return $this->validator->validate($entity);
    }

}