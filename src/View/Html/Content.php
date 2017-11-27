<?php

namespace Ixolit\CDE\View\Html;


/**
 * Basic HTML content
 *
 * Manages a list of contents, concatenates them to a string
 *
 * @package Ixolit\CDE\View\Html
 */
class Content implements Html {

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
		$this->add($content);
	}

	/**
	 * @inheritdoc
	 */
	public function __toString() {
		return $this->getCode();
	}

	/**
	 * Adds the given contents
	 *
	 * @param mixed $content
	 *
	 * @return static
	 */
	public function add($content) {
		if (is_array($content)) {
			foreach ($content as $item) {
				$this->add($item);
			}
		} elseif (isset($content)) {
			$this->content[] = $content;
		}
		return $this;
	}

	/**
	 * Clears the contents
	 *
	 * @return static
	 */
	public function clear() {
		$this->content = [];

		return $this;
	}

	/**
	 * Calls the given callable for each content item
	 *
	 * @param callable $callable
	 *
	 * @return static
	 */
	public function doForEach(callable $callable) {
		if (is_callable($callable)) {
			foreach ($this->content as $item) {
				$callable($item);
			}
		}

		return $this;
	}

	/**
	 * Returns the code representation
	 *
	 * @return string
	 */
	public function getCode() {
		$code = '';
		foreach ($this->content as $item) {
			$code .= ($item instanceof Html) ? $item->getCode() : \html($item);
		}
		return $code;
	}
}