<?php

namespace Ixolit\CDE\Exceptions;

class CookieSetFailedException extends \Exception implements CDEException {
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var int
	 */
	private $value;
	/**
	 * @var int
	 */
	private $maxAge;
	/**
	 * @var string
	 */
	private $path;
	/**
	 * @var string
	 */
	private $domain;
	/**
	 * @var bool
	 */
	private $secure;
	/**
	 * @var bool
	 */
	private $httponly;

	public function __construct($name, $value, $maxAge, $path, $domain, $secure, $httponly) {
		parent::__construct('Failed setting cookie ' . $name . ' with maxAge ' . $maxAge . ' and value ' . $value);
		$this->name = $name;
		$this->value = $value;
		$this->maxAge = $maxAge;
		$this->path = $path;
		$this->domain = $domain;
		$this->secure = $secure;
		$this->httponly = $httponly;
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

	/**
	 * @return \Exception
	 */
	public function getMaxAge() {
		return $this->maxAge;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * @return bool
	 */
	public function isSecure() {
		return $this->secure;
	}

	/**
	 * @return bool
	 */
	public function isHttponly() {
		return $this->httponly;
	}



}