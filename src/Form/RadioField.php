<?php

namespace Ixolit\CDE\Form;

class RadioField extends ChoiceField  {

	const TYPE_RADIO = 'radio';

	/**
	 * {@inheritdoc}
	 */
	public function getType() {
		return self::TYPE_RADIO;
	}
}