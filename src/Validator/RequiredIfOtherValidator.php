<?php

namespace Ixolit\CDE\Validator;

use Ixolit\CDE\Form\FormField;

/**
 * Set a field to quired if another field has a certain value.
 */
class RequiredIfOtherValidator extends RequiredValidator {
	private $otherField;
	private $otherValue;

	/**
	 * @param FormField $otherField
	 * @param string $otherValue
	 */
	public function __construct(FormField $otherField, $otherValue) {
		$this->otherField = $otherField;
		$this->otherValue = $otherValue;
	}

	/**
	 * Returns false if the validator failed to validate $value.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function isValid($value) {
		if ($this->otherField->getValue() != $this->otherValue) {
			return true;
		}
		return parent::isValid($value);
	}
}