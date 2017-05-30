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
		$string = '';
		$this->writeStart($string);
		return $string;
	}

	/**
	 * Returns the element's end tag
	 *
	 * @return string
	 */
	public function getEnd() {
		$string = '';
		$this->writeEnd($string);
		return $string;
	}

	/**
	 * Writes the element's start tag to the passed variable
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	protected function writeStart(&$html) {
		$html .= '<';
		$html .= $this->getName();
		foreach ($this->getAttributes() as $key => $value) {
			$html .= ' ' . $key . '="' . html($value) . '"';
		}
		$html .= '>';
		return $this;
	}

	/**
	 * Writes the element's end tag to the passed variable
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	private function writeEnd(&$html) {
		$html .= '</';
		$html .= $this->getName();
		$html .= '>';
		return $this;
	}

	/**
	 * Writes the element's content to the passed variable
	 *
	 * @param $html
	 *
	 * @return $this
	 */
	private function writeContent(&$html) {
		$this->content->writeContent($html);
		return $this;
	}

	/**
	 * Writes the element's code representation to the passed variable
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	protected function writeCode(&$html) {
		return $this->writeStart($html)->writeContent($html)->writeEnd($html);
	}

}