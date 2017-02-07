<?php

namespace Ixolit\CDE\Validator;

class CSRFTokenValidator extends ExactValueValidator {
	public function getKey() {
		return 'csrf';
	}
}
