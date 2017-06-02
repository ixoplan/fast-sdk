<?php

namespace Ixolit\CDE\WorkingObjects;

class Page {
	/**
	 * @var string
	 */
	private $pageUrl;

	/**
	 * @var string
	 */
	private $pagePath;

	/**
	 * @var bool|null
	 */
	private	$generic;

	/**
	 * @param string $pageUrl
	 * @param string $pagePath
	 * @param bool|null $generic
	 */
	public function __construct($pageUrl, $pagePath, $generic = null) {
		$this->pageUrl  = $pageUrl;
		$this->pagePath = $pagePath;
		$this->generic = $generic;
	}

	/**
	 * @return string
	 */
	public function getPageUrl() {
		return $this->pageUrl;
	}

	/**
	 * @return string
	 */
	public function getPagePath() {
		return $this->pagePath;
	}

	/**
	 * @return bool
	 */
	public function isGeneric() {
		return $this->generic;
	}
}
