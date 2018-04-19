<?php

namespace Ixolit\CDE\Form;

/**
 * Interface FormCustomInterface
 *
 * @package Ixolit\CDE\Form
 */
interface FormCustomInterface {

    /**
     * @return string
     */
    public function getKey();

    /**
     * @param Form $form
     *
     * @return $this
     */
    public function setForm(Form $form);

    /**
     * @return Form
     */
    public function getForm();

    /**
     * @param array $requestParameters
     *
     * @return bool
     */
    public function isFormPost(array $requestParameters);
}