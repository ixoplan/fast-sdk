<?php

namespace Ixolit\CDE\View\Html;

class Raw implements Html {

	/** @var string */
	private $code;

	/**
	 * Creates and initializes a new raw code
	 *
	 * @param string $code
	 */
	public function __construct($code) {
		$this->code = $code;
	}

	/**
	 * Returns the raw code
	 *
	 * @return string
	 */
	function getCode() {
		return $this->code;
	}
}