<?php

namespace Ixolit\CDE\Form;

class PasswordField extends TextField {

	const TYPE_PASSWORD = 'password';

	public function __construct($name) {
		parent::__construct($name);
		$this->setMasked(true);
	}

	public function getType() {
		return self::TYPE_PASSWORD;
	}
}
