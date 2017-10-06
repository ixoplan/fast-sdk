<?php

namespace Ixolit\CDE\Validator;


use Ixolit\CDE\Form\FormField;

/**
 * Class CallbackValidator
 *
 * @package Ixolit\CDE\Validator
 */
class CallbackValidator implements FormValidator {

    /** @var FormField */
    private $field;

    /** @var string */
    private $key;

    /** @var string */
    private $callback;

    /**
     * CallbackValidator constructor.
     *
     * @param FormField $field
     * @param string    $key
     * @param string    $callback
     */
    public function __construct(FormField $field, $key, $callback) {
        $this->field    = $field;
        $this->key      = $key;
        $this->callback = $callback;
    }

    /**
     * Return a unique key for the error type.
     *
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return FormField
     */
    protected function getField() {
        return $this->field;
    }

    /**
     * @return string
     */
    protected function getCallback() {
        return $this->callback;
    }

    /**
     * Returns false if the validator failed to validate $value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value) {
        $callback = $this->getCallback();

        if (\is_callable($callback)) {
            return \call_user_func($callback, $value);
        }

        return false;
    }
}