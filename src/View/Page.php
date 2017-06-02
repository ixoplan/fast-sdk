<?php

namespace Ixolit\CDE\View;


use Ixolit\CDE\CDE;
use Ixolit\CDE\Exceptions\PageNotFoundException;
use Ixolit\CDE\Exceptions\ResourceNotFoundException;
use Ixolit\CDE\Interfaces\PagesAPI;
use Ixolit\CDE\Interfaces\RequestAPI;
use Ixolit\CDE\Interfaces\ResourceAPI;
use Ixolit\CDE\WorkingObjects\Layout;

/**
 * Singleton providing utilities related to requests, pages, links, metadata, etc.
 *
 * @package Ixolit\CDE\View
 */
class Page {

	/** @var self */
	private static $instance = null;

	/** @var RequestAPI */
	private $requestAPI;

	/** @var ResourceAPI */
	private $resourceAPI;

	/** @var PagesAPI */
	private $pagesAPI;

	/** @var string */
	private $scheme;

	/** @var string */
	private $vhost;

	/** @var string */
	private $language;

	/** @var Layout */
	private $layout;

	/** @var string */
	private $path;

	private function __construct() {
	}

	private function __clone() {
	}

	/**
	 * @return self
	 */
	public static function get() {

		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return RequestAPI
	 */
	protected function getRequestAPI() {

		if (!isset($this->requestAPI)) {
			$this->requestAPI = CDE::getRequestAPI();
		}

		return $this->requestAPI;
	}

	/**
	 * @return ResourceAPI
	 */
	protected function getResourceAPI() {

		if (!isset($this->resourceAPI)) {
			$this->resourceAPI = CDE::getResourceAPI();
		}

		return $this->resourceAPI;
	}

	/**
	 * @return PagesAPI
	 */
	protected function getPagesAPI() {

		if (!isset($this->pagesAPI)) {
			$this->pagesAPI = CDE::getPagesAPI();
		}

		return $this->pagesAPI;
	}

	/**
	 * Build a path from it's elements by removing empty ones and redundant slashes
	 *
	 * @return string
	 */
	private function buildPath() {
		return implode('/', array_filter(array_map(function ($i) {return \trim($i, '/');}, func_get_args())));
	}

	/**
	 * Returns the request's url scheme
	 *
	 * @return string
	 */
	public function getScheme() {
		if (!isset($this->scheme)) {
			$this->scheme = $this->getRequestApi()->getScheme();
		}
		return $this->scheme;
	}

	/**
	 * Returns the request's virtual host name
	 *
	 * @return string
	 */
	public function getVhost() {
		if (!isset($this->vhost)) {
			$this->vhost = $this->getRequestApi()->getVhost();
		}
		return $this->vhost;
	}


	/**
	 * Returns the request's language code
	 *
	 * @return string
	 */
	public function getLanguage() {
		if (!isset($this->language)) {
			$this->language = $this->getRequestApi()->getLanguage();
		}
		return $this->language;
	}

	/**
	 * Returns the request's layout data
	 *
	 * @return Layout
	 */
	public function getLayout() {
		if (!isset($this->layout)) {
			$this->layout = $this->getRequestApi()->getLayout();
		}
		return $this->layout;
	}

	/**
	 * Returns the request's path
	 *
	 * @return string
	 */
	public function getPath() {
		if (!isset($this->path)) {
			$this->path = $this->getRequestApi()->getPagePath();
		}
		return $this->path;
	}

	/**
	 * Returns the URL for the given page path and language, optionally with scheme and host
	 *
	 * @param string $path
	 * @param string|null $lang
	 * @param bool $withHost
	 * @param bool $withScheme
	 *
	 * @return string
	 */
	public function getPageUrl($path, $lang = null, $withHost = false, $withScheme = false) {
		return
			($withScheme ? $this->getScheme() . '://' : '') .
			$this->buildPath(
				$withHost ? $this->getVhost() : null,
				($lang === null) ? $this->getLanguage() : $lang,
				$path
			);
	}

	// TODO: cleanup!
	public function getPageUrl2($path, $lang = null, $full = false) {
		try {
			$page = $this->getPagesAPI()->getPage($path, null, $lang, null);
			return $full ? $page->getPageUrl() : $page->getPagePath();
		}
		catch (PageNotFoundException $e) {
			return null;
		}
	}

	/**
	 * Returns the URL for the given static path
	 *
	 * @param $path
	 *
	 * @return null|string
	 */
	public function getStaticUrl($path) {
		try {
			return $this->getResourceAPI()->getStaticUrl($path);
		}
		catch (ResourceNotFoundException $e) {
			return null;
		}
	}

	/**
	 * Returns the URL for the given static path prefixed by the request's layout name
	 *
	 * @param $path
	 *
	 * @return null|string
	 */
	public function getStaticLayoutUrl($path) {
		return $this->getStaticUrl($this->buildPath($this->getLayout()->getName(), $path));
	}
}