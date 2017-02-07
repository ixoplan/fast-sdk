<?php

namespace Ixolit\CDE\Form;

class DropDownField extends ChoiceField  {
	/**
	 * {@inheritdoc}
	 */
	public function getType() {
		return 'dropdown';
	}
}