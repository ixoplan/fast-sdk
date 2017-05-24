<?php

namespace Ixolit\CDE\Form;


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
     * @return $this
     */
    public function validate() {
        $errors = [];

        foreach ($this->getFields() as $field) {
            $field = $field->validate();

            $errors[$field->getName()] = $field->getErrors();
        }

        $this->setErrors($errors);

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
                \array_key_exists($fieldName, $parsedBody) ? $parsedBody[$fieldName] : null
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