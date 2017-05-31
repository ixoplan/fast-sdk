<?php

namespace Ixolit\CDE\View\Html;


/**
 * Empty HTML element
 *
 * Extends generic element and implements specific code generation
 *
 * @package Ixolit\CDE\View\Html
 */
class ElementEmpty extends Element {

	/** @inheritdoc */
	public function getCode() {
		$code = '<' . $this->getName();
		foreach ($this->getAttributes() as $key => $value) {
			$code .= ' ' . $key . '="' . html($value) . '"';
		}
		$code .= ' />';
		return $code;
	}
}