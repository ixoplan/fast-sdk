<?php

namespace Ixolit\CDE\Exceptions;

class ResourceNotFoundException extends \Exception implements CDEException {

	/**
	 * @var string
	 */
	private $resource;

	public function __construct($resource) {
		parent::__construct('Resource not found: ' . $resource);

		$this->resource = $resource;
	}

	/**
	 * @return string
	 */
	public function getResource() {
		return $this->resource;
	}
}