<?php

namespace Ixolit\CDE\Form;

/**
 * This class was ported from the Piccolo form library with permission.
 */
class SubmitButton extends FormField {

	const TYPE_SUBMIT_BUTTON = 'submit';

	public function getType() {
		return self::TYPE_SUBMIT_BUTTON;
	}
}
