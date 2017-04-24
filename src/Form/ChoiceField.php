<?php

namespace Ixolit\CDE\Form;

use Ixolit\CDE\Validator\InArrayValidator;

abstract class ChoiceField extends FormField {
	/**
	 * @var string[]
	 */
	private $values = [];
	/**
	 * @var null|InArrayValidator
	 */
	private $valuesValidator = null;

	/**
	 * @param array $values
	 *
	 * @return $this
	 */
	public function setValues($values) {
		$this->values = $values;
		if ($this->valuesValidator) {
			$this->removeValidator($this->valuesValidator);
		}
		$this->valuesValidator = new InArrayValidator(\array_keys($values));
		$this->addValidator($this->valuesValidator);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getValues() {
		return $this->values;
	}
}
