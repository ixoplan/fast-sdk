<?php

namespace Ixolit\CDE\Form;

class CheckboxField extends FormField {
	public function __construct($name) {
		parent::__construct($name);
	}

	public function getType() {
		return 'checkbox';
	}
}