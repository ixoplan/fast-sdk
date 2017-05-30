<?php

namespace Ixolit\CDE\View\Html;


class ElementEmpty extends Element {

	/**
	 * Returns the element's code representation
	 *
	 * @return string
	 */
	public function getCode() {
		$code = '<' . $this->getName();
		foreach ($this->getAttributes() as $key => $value) {
			$code .= ' ' . $key . '="' . html($value) . '"';
		}
		$code .= ' />';
		return $code;
	}
}