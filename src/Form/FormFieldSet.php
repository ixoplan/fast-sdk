<?php

namespace Ixolit\CDE\Form;


use Ixolit\CDE\Form\FormFieldSet\ValidationStrategy;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class FormFieldSet
 *
 * @package Ixolit\CDE\Form
 */
abstract class FormFieldSet {

    /** @var FormField[] */
    private $fields = [];

    /** @var array */
    private $errors = [];

    /**
     * Enables deciding on whether to perform validation of the underlying fields or not
     * This is especially useful when complete fieldsets shall be ignored when certain conditions are met
     * Example: selecting a certain select option from a different FormField requires to skip validation on a FormFieldSet and it's entire fields
     * @var ValidationStrategy
     */
    private $validationStrategy = null;

    /**
     * @return string
     */
    public abstract function getKey();

    /**
     * @param FormField[] $fields
     *
     * @return $this
     */
    public function setFields($fields) {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return FormField[]
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * @param array $errors
     *
     * @return $this
     */
    public function setErrors($errors) {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getPrefixedErrors() {
        $errors = [];
        foreach ($this->getErrors() as $fieldName => $error) {
            $errors[$this->getFormFieldNameWithPrefix($fieldName)] = $error;
        }

        return $errors;
    }

    /**
     * @param FormField $field
     *
     * @return $this
     */
    protected function addField(FormField $field) {
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return FormField|null
     */
    public function getFieldByName($fieldName) {
        $fields = $this->getFields();

        if (isset($fields[$fieldName]) && $fields[$fieldName] instanceof FormField) {
            return $fields[$fieldName];
        }

        return null;
    }

    public function removeField($fieldName) {
        unset($this->fields[$fieldName]);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    public function getValueByName($name) {
        $field = $this->getFieldByName($name);

        return $field ? $field->getValue() : null;
    }

    /**
     * @return $this
     */
    public function validate() {
        $errors = [];

        if (!$this->validationStrategy || $this->validationStrategy->shallValidate()) {

            foreach ($this->getFields() as $field) {
                $field = $field->validate();

                if (!empty($field->getErrors())) {
                    $errors[$field->getName()] = $field->getErrors();
                }
            }

        }

        $this->setErrors($errors);

        return $this;
    }

    /**
     * @param ValidationStrategy $strategy
     * @return $this
     */
    public function setValidationStrategy(ValidationStrategy $strategy) {
        $this->validationStrategy = $strategy;

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return $this
     */
    public function setFromRequest(ServerRequestInterface $request) {
        $parsedBody = $request->getParsedBody();

        foreach ($this->getFields() as $field) {
            $fieldName = $this->getFormFieldNameWithPrefix($field->getName());

            $field->setValue(
                isset($parsedBody[$fieldName]) ? $parsedBody[$fieldName] : null
            );
        }

        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return string
     */
    protected function getFormFieldNameWithPrefix($fieldName) {
        return $this->getFormFieldPrefix() . $fieldName;
    }

    /**
     * @return string
     */
    protected function getFormFieldPrefix() {
        return $this->getKey() . '_';
    }

}