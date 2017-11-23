<?php

namespace Ixolit\CDE\Exceptions;

class HeaderSetFailedException extends \Exception implements CDEException {

	/** @var string */
	private $name;

	/** @var int */
	private $value;

	public function __construct($name, $value) {
		parent::__construct('Failed setting header ' . $name . ' with value ' . $value);
		$this->name = $name;
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getValue() {
		return $this->value;
	}
}