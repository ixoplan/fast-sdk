<?php

namespace Ixolit\CDE\Form;

class RadioField extends ChoiceField  {
	/**
	 * {@inheritdoc}
	 */
	public function getType() {
		return 'radio';
	}
}