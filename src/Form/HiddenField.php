<?php

namespace Ixolit\CDE\Form;

/**
 * This class was ported from the Piccolo form library with permission.
 */
class HiddenField extends FormField {
	public function getType() {
		return 'hidden';
	}
}