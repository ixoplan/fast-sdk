<?php

namespace Ixolit\CDE\Interfaces;

use Ixolit\CDE\Form\Form;

/**
 * Interface FormProcessorExtendedInterface
 * @package Ixolit\CDE\Interfaces
 */
interface FormProcessorExtendedInterface extends FormProcessorInterface {

	/**
	 * @param Form  $form
     * @param array $data
	 *
	 * @return bool
	 */
	public function setRestoredFormData(Form $form, $data);

}
