<?php

namespace Ixolit\CDE\Form;

class CheckboxField extends FormField {

	const TYPE_CHECKBOX = 'checkbox';

	public function __construct($name) {
		parent::__construct($name);
	}

	public function getType() {
		return self::TYPE_CHECKBOX;
	}
}