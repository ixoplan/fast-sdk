<?php

namespace Ixolit\CDE\Form;

/**
 * This class was ported from the Piccolo form library with permission.
 */
class TextArea extends FormField {

	const TYPE_TEXT_AREA = 'textarea';

	public function getType() {
		return self::TYPE_TEXT_AREA;
	}
}
