<?php

namespace Ixolit\CDE\View\Html;


/**
 * Basic HTML content
 *
 * Manages a list of contents, concatenates them to a string
 *
 * @package Ixolit\CDE\View\Html
 */
class Content {

	/**
	 * @var array
	 */
	protected $content = [];

	/**
	 * Creates and initializes a new content
	 *
	 * @param mixed $content
	 */
	public function __construct($content = null) {
		$this->addContent($content);
	}

	/**
	 * @inheritdoc
	 */
	public function __toString() {
		$string = '';
		$this->writeContent($string);
		return $string;
	}

	/**
	 * @return array
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Adds the given contents
	 *
	 * @param mixed $content
	 *
	 * @return $this
	 */
	public function addContent($content) {
		if (is_array($content)) {
			foreach ($content as $item) {
				$this->addContent($item);
			}
		} elseif (isset($content)) {
			$this->content[] = $content;
		}
		return $this;
	}

	/**
	 * Clears the contents
	 *
	 * @return $this
	 */
	public function clearContent() {
		$this->content = [];

		return $this;
	}

	/**
	 * Writes the contents to the passed variable
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	protected function writeContent(&$html) {
		foreach ($this->content as $child) {
			$html .= $child;
		}
		return $this;
	}
}