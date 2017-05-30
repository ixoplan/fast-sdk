<?php

namespace Ixolit\CDE\View\Html;


/**
 * Generic HTML element
 *
 * Manages name, attributes and contents, concatenates them to a string
 *
 * @package Ixolit\CDE\View\Html
 */
class Element extends Content {

	const NAME_DIV = 'div';
	const NAME_FORM = 'form';
	const NAME_LABEL = 'label';
	const NAME_INPUT = 'input';
	const NAME_SELECT = 'select';
	const NAME_OPTION = 'option';

	const ATTRIBUTE_NAME_ID = 'id';
	const ATTRIBUTE_NAME_CLASS = 'class';

	const ATTRIBUTE_NAME_ACTION = 'action';
	const ATTRIBUTE_NAME_METHOD = 'method';
	const ATTRIBUTE_NAME_NAME = 'name';
	const ATTRIBUTE_NAME_TYPE = 'type';
	const ATTRIBUTE_NAME_VALUE = 'value';
	const ATTRIBUTE_NAME_FOR = 'for';
	const ATTRIBUTE_NAME_CHECKED = 'checked';
	const ATTRIBUTE_NAME_SELECTED = 'selected';

	const ATTRIBUTE_VALUE_TYPE_HIDDEN = 'hidden';
	const ATTRIBUTE_VALUE_TYPE_TEXT = 'text';
	const ATTRIBUTE_VALUE_TYPE_EMAIL = 'email';
	const ATTRIBUTE_VALUE_TYPE_PASSWORD = 'password';
	const ATTRIBUTE_VALUE_TYPE_CHECKBOX = 'checkbox';
	const ATTRIBUTE_VALUE_TYPE_RADIO = 'radio';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $attributes;

	/**
	 * @return string
	 */
	// TODO: check not empty?
	public function getName() {
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * Creates and initializes a new element
	 *
	 * @param string $name
	 * @param array $attributes
	 * @param mixed $content
	 */
	public function __construct($name, $attributes = [], $content = null) {
		$this->name = $name;
		$this->attributes = $attributes;
		parent::__construct($content);
	}

	/**
	 * @inheritdoc
	 */
	public function __toString() {
		return $this->getCode();
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
	 * Returns the element's code representation
	 *
	 * @return string
	 */
	public function getCode() {
		$string = '';
		$this->writeCode($string);
		return $string;
	}

	/**
	 * Writes the element's start tag to the passed variable
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	private function writeStart(&$html) {
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
	 * Writes the element's code representation to the passed variable
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	private function writeCode(&$html) {
		return $this->writeStart($html)->writeContent($html)->writeEnd($html);
	}

	/**
	 * Sets an element's attributes, optionally keep existing ones
	 *
	 * @param array $attributes
	 * @param bool $keep
	 *
	 * @return $this
	 */
	public function setAttributes($attributes, $keep = false) {
		foreach ($attributes as $name => $value) {
			$this->setAttribute($name, $value, $keep);
		}
		return $this;
	}

	/**
	 * Sets an element's attribute, optionally keep existing ones
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param bool $keep
	 *
	 * @return $this
	 */
	public function setAttribute($name, $value, $keep = false) {
		if (!($keep && $this->hasAttribute($name))) {
			$this->attributes[$name] = $value;
		}
		return $this;
	}

	/**
	 * Deletes an element's attribute
	 *
	 * @param $name
	 *
	 * @return $this
	 */
	public function deleteAttribute($name) {
		unset($this->attributes[$name]);
		return $this;
	}

	/**
	 * Sets or deletes an element's boolean attribute
	 *
	 * @param string $name
	 * @param bool $value
	 *
	 * @return $this
	 */
	public function booleanAttribute($name, $value) {
		if ($value) {
			return $this->setAttribute($name, $name);
		}
		else {
			return $this->deleteAttribute($name);
		}
	}

	private function hasAttribute($name) {
		return isset($this->attributes[$name]);
	}

	/**
	 * Shortcut to set the element's id attribute
	 *
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function setId($value) {
		return $this->setAttribute(self::ATTRIBUTE_NAME_ID, $value);
	}

	/**
	 * Add the given classes to the element's attribute, appending to existing ones
	 *
	 * @param string[]|string $class
	 *
	 * @return $this
	 */
	public function addClass($class) {
		if (is_array($class)) {
			foreach ($class as $item) {
				$this->addClass($item);
			}
		}
		elseif (isset($class)) {
			if (empty($this->attributes[self::ATTRIBUTE_NAME_CLASS])) {
				$this->attributes[self::ATTRIBUTE_NAME_CLASS] = $class;
			}
			else {
				$this->attributes[self::ATTRIBUTE_NAME_CLASS] .= ' ' . $class;
			}
		}
		return $this;
	}
}