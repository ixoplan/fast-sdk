<?php

namespace Ixolit\CDE\View\Html;


class ElementContent extends Element {

	/**
	 * @var Content
	 */
	// TODO: getter?
	private $content;

	/**
	 * Creates and initializes a new element
	 *
	 * @param string $name
	 * @param array $attributes
	 * @param mixed $content
	 */
	public function __construct($name, array $attributes = [], $content = null) {
		parent::__construct($name, $attributes);
		$this->content = new Content($content);
	}

	public function addContent($content) {
		$this->content->addContent($content);
		return $this;
	}

	/**
	 * Returns the element's start tag
	 *
	 * @return string
	 */
	public function getStart() {
		$code = '<' . $this->getName();
		foreach ($this->getAttributes() as $key => $value) {
			$code .= ' ' . $key . '="' . html($value) . '"';
		}
		$code .= '>';
		return $code;
	}

	/**
	 * Returns the element's end tag
	 *
	 * @return string
	 */
	public function getEnd() {
		return '</' . $this->getName() . '>';
	}

	/**
	 * Returns the element's code representation
	 *
	 * @return string
	 */
	public  function getCode() {
		return $this->getStart() . $this->content->getCode() . $this->getEnd();
	}
}