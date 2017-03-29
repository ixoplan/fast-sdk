<?php

namespace Ixolit\CDE\Form;

/**
 * This class was ported from the Piccolo form library with permission.
 */
class HiddenField extends FormField {

	const TYPE_HIDDEN = 'hidden';

	public function getType() {
		return self::TYPE_HIDDEN;
	}
}