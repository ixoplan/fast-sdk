<?php

namespace Ixolit\CDE\Exceptions;

class PageNotFoundException extends \Exception implements CDEException {

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var string
	 */
	private $lang;

	/**
	 * @var string
	 */
	private $vhost;

	/**
	 * @var string
	 */
	private $layout;

	/**
	 * @var string
	 */
	private $scheme;

	public function __construct($path, $lang, $vhost, $layout, $scheme) {
		parent::__construct('Page not found: ' . $path);

		$this->path = $path;
		$this->lang = $lang;
		$this->vhost = $vhost;
		$this->layout = $layout;
		$this->scheme = $scheme;
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
	public function getLang() {
		return $this->lang;
	}

	/**
	 * @return string
	 */
	public function getVhost() {
		return $this->vhost;
	}

	/**
	 * @return string
	 */
	public function getLayout() {
		return $this->layout;
	}

	/**
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}
}