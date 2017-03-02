<?php

namespace Ixolit\CDE\Form;

use Ixolit\CDE\Validator\EmailValidator;
use Ixolit\CDE\Validator\SingleLineValidator;

/**
 * This class was ported from the Piccolo form library with permission.
 */
class EmailField extends TextField {
	public function __construct($name) {
		parent::__construct($name);

		$this->addValidator(new SingleLineValidator());
		$this->addValidator(new EmailValidator());
	}

	public function getType() {
		return 'email';
	}
}