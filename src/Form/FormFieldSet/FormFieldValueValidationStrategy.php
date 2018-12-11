<?php


namespace Ixolit\CDE\Form\FormFieldSet;

use Ixolit\CDE\Form\FormField;

/**
 * Class FormFieldValueValidationStrategy
 * @package Ixolit\CDE\Form\FormFieldSet
 */
class FormFieldValueValidationStrategy implements ValidationStrategy {

    /**
     * The FormField from which to retrieve the value and compare it with the provided
     * $this->>valuesToPerformvalidation values. Only when one of those match $this->shallValidate() will return true
     * @var FormField
     */
    private $formField;

    /**
     *
     * @var array
     */
    private $valuesToPerformValidation;

    /**
     * FormFieldValueValidationStrategy constructor.
     * @param FormField $formField
     * @param mixed|array $valuesToPerformValidation
     *
     */
    public function __construct(FormField $formField, $valuesToPerformValidation) {
        $this->formField = $formField;

        if (!is_array($valuesToPerformValidation)) {
            $valuesToPerformValidation = [$valuesToPerformValidation];
        }

        $this->valuesToPerformValidation = $valuesToPerformValidation;
    }


    /**
     * Whether or not to perform validation
     * @return boolean
     */
    public function shallValidate() {
        return in_array($this->formField->getValue(), $this->valuesToPerformValidation, true);
    }

}