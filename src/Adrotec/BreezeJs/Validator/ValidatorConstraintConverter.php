<?php

namespace Adrotec\BreezeJs\Validator;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

use Adrotec\BreezeJs\Metadata\Validator\Validator;

class ValidatorConstraintConverter {

    const VALIDATOR_CREDIT_CARD = Validator::CREDIT_CARD;
    const VALIDATOR_EMAIL_ADDRESS = Validator::EMAIL_ADDRESS;
    const VALIDATOR_MAX_LENGTH = Validator::MAX_LENGTH;
    const VALIDATOR_PHONE = Validator::PHONE;
    const VALIDATOR_REGULAR_EXPRESSION = Validator::REGULAR_EXPRESSION;
    const VALIDATOR_REQUIRED = Validator::REQUIRED;
    const VALIDATOR_URL = Validator::URL;

    public function convert(Constraint $constraint, &$options = array()) {
        if ($constraint instanceof Constraints\Luhn) {
            return self::VALIDATOR_CREDIT_CARD;
        }
        if ($constraint instanceof Constraints\Email) {
            return self::VALIDATOR_EMAIL_ADDRESS;
        }
        if ($constraint instanceof Constraints\Length) {
            $options = array(
                'minLength' => $constraint->min,
                'maxLength' => $constraint->max,
            );
            return self::VALIDATOR_MAX_LENGTH;
        }
        if ($constraint instanceof Constraints\Regex) {
            $options = array(
//                'expression' => $constraint->getHtmlPattern(),
                'expression' => trim($constraint->pattern, '/'),
            );
            return self::VALIDATOR_REGULAR_EXPRESSION;
        }
        if ($constraint instanceof Constraints\NotBlank) {
            return self::VALIDATOR_REQUIRED;
        }
        if ($constraint instanceof Constraints\Url) {
            return self::VALIDATOR_URL;
        }
    }

}