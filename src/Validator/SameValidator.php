<?php

namespace Ixolit\CDE\Validator;

use Ixolit\CDE\Form\FormField;

class SameValidator implements FormValidator {
	/**
	 * @var FormField
	 */
	private $field1;
	/**
	 * @var FormField
	 */
	private $field2;

	/**
	 * @param FormField $field1
	 * @param FormField $field2
	 */
	public function __construct(FormField $field1, FormField $field2) {
		$this->field1 = $field1;
		$this->field2 = $field2;
	}

	public function getKey() {
		return 'matching';
	}

	public function isValid($value) {
		return ($this->field1->getValue() == $this->field2->getValue());
	}
}