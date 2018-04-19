<?php

namespace Ixolit\CDE\Form;

/**
 * Interface FormFieldSetCustomInterface
 *
 * @package Ixolit\CDE\Form
 */
interface FormFieldSetCustomInterface {

    /**
     * @return string
     */
    public function getKey();

    /**
     * @param FormFieldSet $formFieldSet
     *
     * @return $this
     */
    public function setFormFieldSet(FormFieldSet $formFieldSet);

    /**
     * @return FormFieldSet
     */
    public function getFormFieldSet();

}