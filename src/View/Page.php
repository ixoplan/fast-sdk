<?php

namespace Ixolit\CDE\View;


use Ixolit\CDE\CDE;
use Ixolit\CDE\Exceptions\InvalidValueException;
use Ixolit\CDE\Exceptions\MetadataNotAvailableException;
use Ixolit\CDE\Exceptions\ResourceNotFoundException;
use Ixolit\CDE\Interfaces\MetaAPI;
use Ixolit\CDE\Interfaces\PagesAPI;
use Ixolit\CDE\Interfaces\RequestAPI;
use Ixolit\CDE\Interfaces\ResourceAPI;
use Ixolit\CDE\PSR7\Uri;
use Ixolit\CDE\WorkingObjects\Layout;
use Psr\Http\Message\UriInterface;

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

	/** @var MetaAPI */
	private $metaAPI;

	/** @var string */
	private $url;

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

	/** @var array */
	private $query;

	/** @var string[] */
	private $languages;

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
	 * @return MetaAPI
	 */
	protected function getMetaAPI() {

		if (!isset($this->metaAPI)) {
			$this->metaAPI = CDE::getMetaAPI();
		}

		return $this->metaAPI;
	}

	/**
	 * Returns an URI instance for the given string
	 *
	 * @param string $uri
	 *
	 * @return Uri
	 *
	 * @throws InvalidValueException
	 */
	// TODO: move to \Ixolit\CDE\PSR7\Uri ?
	private function parseUri($uri) {
		if (\preg_match('~^(?:(.*?):)(?://(?:(.*?)(?:\:(.*?))?@)?(.*?)(?:\:(\d+))?(?=[/?#]|$))?((?:.*?)?)(?:\?(.*?))?(?:\#(.*?))?$~', $uri, $matches)) {
			return new Uri(
				!empty($matches[1]) ? $matches[1] : null,
				!empty($matches[4]) ? $matches[4] : null,
				!empty($matches[5]) ? $matches[5] : null,
				!empty($matches[6]) ? $matches[6] : null,
				!empty($matches[7]) ? $matches[7] : null,
				!empty($matches[8]) ? $matches[8] : null
			);
		}
		throw new InvalidValueException($uri);
	}

	/**
	 * Build a path from it's elements by removing empty ones and redundant slashes
	 *
	 * @param ...
	 *
	 * @return string
	 */
	private function buildPath() {
		return implode('/', array_filter(array_map(function ($i) {return \trim($i, '/');}, func_get_args())));
	}

	/**
	 * Build a query string from name value pairs or return the passed value as is
	 *
	 * @param mixed $query
	 *
	 * @return string
	 */
	private function buildQuery($query) {
		if (\is_array($query)) {
			$params = [];
			foreach ($query as $key => $value) {
				$params[] = \urlencode($key) . '=' . \urlencode($value);
			}
			return \implode('&', $params);
		}
		return $query;
	}

	/**
	 * Returns a valid language for the given one, defaults to the request's language
	 *
	 * @param string|null $lang
	 *
	 * @return string
	 */
	private function getValidLanguage($lang = null) {
		if (!empty($lang)) {
			foreach ($this->getLanguages() as $item) {
				if (\strtolower($item) === \strtolower($lang)) {
					return $item;
				}
			}
		}
		return $this->getLanguage();
	}

	/**
	 * Returns the request's url
	 *
	 * @return string
	 */
	public function getUrl() {
		if (!isset($this->url)) {
			$this->url = $this->getRequestApi()->getPageLink();
		}
		return $this->url;
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
	 * @return array
	 */
	public function getQuery() {
		if (!isset($this->query)) {
			$this->query = $this->getRequestAPI()->getRequestParameters();
		}
		return $this->query;
	}

	/**
	 * Returns the languages supported by the current host
	 *
	 * @return string[]
	 */
	public function getLanguages() {
		if (!isset($this->languages)) {
			$this->languages = $this->getPagesAPI()->getLanguages();
		}
		return $this->languages;
	}

	/**
	 * Returns the meta data value for the given name, language, page and layout
	 *
	 * @param string $name
	 * @param string|null $lang
	 * @param string|null $page
	 * @param string|null $layout
	 *
	 * @return null|string
	 */
	public function getMeta($name, $lang = null, $page = null, $layout = null) {
		try {
			return $this->getMetaAPI()->getMeta($name, $lang, $page, $layout);
		}
		catch (MetadataNotAvailableException $e) {
			return null;
		}
	}

	/**
	 * Returns the path for the given page and language, based on the current request
	 *
	 * @param null $page
	 * @param null $lang
	 *
	 * @return string
	 */
	public function getPagePath($page = null, $lang = null) {
		return '/' . $this->buildPath(
			$this->getValidLanguage($lang),
			$page === null ? $this->getPath() : $page
		);
	}

	/**
	 * Returns the URL for the given page, language, query, host and scheme, based on the current request
	 *
	 * @param string|null $page
	 * @param string|null $lang
	 * @param mixed|null $query
	 * @param string|null $host
	 * @param string|null $scheme
	 * @param int|null $port
	 *
	 * @return UriInterface
	 */
	public function getPageUri($page = null, $lang = null, $query = null, $host = null, $scheme = null, $port = null) {

		/** @var UriInterface $uri */
		$uri = $this->parseUri($this->getUrl());

		$uri = $uri->withPath($this->getPagePath($page, $lang));

		$uri = $uri->withQuery($this->buildQuery($query === null ? $this->getQuery() : $query));

		if ($host !== null) {
			$uri = $uri->withHost($host);
		}

		if ($scheme !== null) {
			$uri = $uri->withScheme($scheme);
		}

		if ($port !== null) {
			$uri = $uri->withPort($port);
		}

		// remove scheme if host is missing since we are dealing with hierarchical URLs like HTTP(S) here ...
		if (empty($uri->getHost())) {
			$uri = $uri->withScheme(null);
		}

		return $uri;
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