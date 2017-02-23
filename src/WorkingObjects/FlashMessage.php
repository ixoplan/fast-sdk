<?php

namespace Ixolit\CDE\WorkingObjects;

/**
 * Class FlashMessage
 *
 * @package Ixolit\CDE\WorkingObjects
 */
class FlashMessage extends DataObject {

	const TYPE_INFO = 'info';
	const TYPE_ERROR = 'error';
	const TYPE_WARNING = 'warning';
	const TYPE_SUCCESS = 'success';

	/**
	 * @return string
	 */
	public function getText() {
		return $this->get('text');
	}

	/**
	 * @param string $text
	 * @return $this
	 */
	public function setText($text) {
		return $this->set('text', $text);
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->get('type');
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType($type) {
		return $this->set('type', $type);
	}

	function __toString() {
		return $this->getText();
	}
}