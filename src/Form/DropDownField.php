<?php

namespace Ixolit\CDE\Form;

class DropDownField extends ChoiceField  {

	const TYPE_DROP_DOWN = 'dropdown';

	/**
	 * {@inheritdoc}
	 */
	public function getType() {
		return self::TYPE_DROP_DOWN;
	}
}