<?php

namespace Ixolit\CDE\View\Html;


class ElementEmpty extends Element {

	/**
	 * Writes the element's code representation to the passed variable
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	protected  function writeCode(&$html) {
		$html .= '<';
		$html .= $this->getName();
		foreach ($this->getAttributes() as $key => $value) {
			$html .= ' ' . $key . '="' . html($value) . '"';
		}
		$html .= ' />';
		return $this;
	}
}