<?php


namespace Ixolit\CDE\Form\FormFieldSet;

/**
 * Interface ValidationStrategy
 * @package Ixolit\CDE\Form\FormFieldSet
 */
interface ValidationStrategy {

    /**
     * Whether or not to perform the validation
     * @return boolean
     */
    public function shallValidate();

}