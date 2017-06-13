<?php

namespace Ixolit\CDE\Context;


use Ixolit\CDE\CDE;
use Ixolit\CDE\Exceptions\InvalidValueException;
use Ixolit\CDE\Exceptions\MetadataNotAvailableException;
use Ixolit\CDE\Exceptions\ResourceNotFoundException;
use Ixolit\CDE\Interfaces\MetaAPI;
use Ixolit\CDE\Interfaces\PagesAPI;
use Ixolit\CDE\Interfaces\RequestAPI;
use Ixolit\CDE\Interfaces\ResourceAPI;
use Ixolit\CDE\Interfaces\ResponseAPI;
use Ixolit\CDE\PSR7\Uri;
use Ixolit\CDE\WorkingObjects\Layout;
use Psr\Http\Message\UriInterface;

/**
 * Context instance to be initialized once, providing utilities related to pages requests
 *
 * @package Ixolit\CDE\View
 */
class Page {

	/** @var self */
	private static $instance;

	/** @var RequestAPI */
	private $requestAPI;

	/** @var ResponseAPI */
	private $responseAPI;

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

	protected function __construct() {
	}

	private function __clone() {
	}

	/**
	 * @return self
	 *
	 * @throws \Exception
	 */
	public static function get() {

		if (!isset(self::$instance)) {
			throw new \Exception('not set'); // TODO: specific exception
		}

		return self::$instance;
	}

	/**
	 * @param self $instance
	 *
	 * @throws \Exception
	 */
	protected static function set($instance) {

		if (isset(self::$instance)) {
			throw new \Exception('already set'); // TODO: specific exception
		}

		self::$instance = $instance;
	}

//	public static function __callStatic($name, $arguments) {
//		return call_user_func_array([self::get(), 'get' . $name], $arguments);
//	}

	private $test;

	protected function newTest() {
		return \date('r');
	}

	public function getTest() {
		if (!isset($this->test)) {
			$this->test = $this->newTest();
		}
		return $this->test;
	}

	public static function test() {
		return self::get()->getTest();
	}

	// region factory methods

	/**
	 * @return RequestAPI
	 */
	protected function newRequestAPI() {
		return CDE::getRequestAPI();
	}

	/**
	 * @return ResponseAPI
	 */
	protected function newResponseAPI() {
		return CDE::getResponseAPI();
	}

	/**
	 * @return ResourceAPI
	 */
	protected function newResourceAPI() {
		return CDE::getResourceAPI();
	}

	/**
	 * @return PagesAPI
	 */
	protected function newPagesAPI() {
		return CDE::getPagesAPI();
	}

	/**
	 * @return MetaAPI
	 */
	protected function newMetaAPI() {
		return CDE::getMetaAPI();
	}

	// endregion

	/**
	 * @return RequestAPI
	 */
	public function getRequestAPI() {

		if (!isset($this->requestAPI)) {
			$this->requestAPI = $this->newRequestAPI();
		}

		return $this->requestAPI;
	}

	/**
	 * @return ResponseAPI
	 */
	public function getResponseAPI() {

		if (!isset($this->responseAPI)) {
			$this->responseAPI = $this->newResponseAPI();
		}

		return $this->responseAPI;
	}

	/**
	 * @return ResourceAPI
	 */
	public function getResourceAPI() {

		if (!isset($this->resourceAPI)) {
			$this->resourceAPI = $this->newResourceAPI();
		}

		return $this->resourceAPI;
	}

	/**
	 * @return PagesAPI
	 */
	public function getPagesAPI() {

		if (!isset($this->pagesAPI)) {
			$this->pagesAPI = $this->newPagesAPI();
		}

		return $this->pagesAPI;
	}

	/**
	 * @return MetaAPI
	 */
	public function getMetaAPI() {

		if (!isset($this->metaAPI)) {
			$this->metaAPI = $this->newMetaAPI();
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
	private static function parseUri($uri) {
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
	private static function buildPath() {
		return implode('/', array_filter(array_map(function ($i) {return \trim($i, '/');}, func_get_args())));
	}

	/**
	 * Build a query string from name value pairs or return the passed value as is
	 *
	 * @param mixed $query
	 *
	 * @return string
	 */
	private static function buildQuery($query) {
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
			$this->url = $this->getRequestAPI()->getPageLink();
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
			$this->scheme = $this->getRequestAPI()->getScheme();
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
			$this->vhost = $this->getRequestAPI()->getVhost();
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
			$this->language = $this->getRequestAPI()->getLanguage();
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
			$this->layout = $this->getRequestAPI()->getLayout();
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
			$this->path = $this->getRequestAPI()->getPagePath();
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
	 * @param string|null $default
	 * @param string|null $lang
	 * @param string|null $page
	 * @param string|null $layout
	 *
	 * @return null|string
	 */
	public function getMeta($name, $default = null, $lang = null, $page = null, $layout = null) {
		try {
			return $this->getMetaAPI()->getMeta($name, $lang, $page, $layout);
		}
		catch (MetadataNotAvailableException $e) {
			return $default;
		}
	}

	/**
	 * Returns the translation for the given string and language
	 *
	 * @param string $string
	 * @param string|null $lang
	 *
	 * @return null|string
	 */
	public function getTranslation($string, $lang = null) {
		return $this->getMeta('t-' . $string, $string, $lang);
	}

	/**
	 * Returns the path for the given page and language, based on the current request
	 *
	 * @param string|null $page
	 * @param string|null $lang
	 *
	 * @return string
	 */
	public function getPagePath($page = null, $lang = null) {
		return '/' . self::buildPath(
			$this->getValidLanguage($lang),
			$page === null ? self::getPath() : $page
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
		$uri = self::parseUri($this->getUrl());

		$uri = $uri->withPath($this->getPagePath($page, $lang));

		$uri = $uri->withQuery(self::buildQuery($query === null ? $this->getQuery() : $query));

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
	 * @param string $path
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
	 * @param string $path
	 *
	 * @return null|string
	 */
	public function getStaticLayoutUrl($path) {
		return $this->getStaticUrl(self::buildPath($this->getLayout()->getName(), $path));
	}

	/**
	 * Returns the content of the current page
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->getPagesAPI()->getContent();
	}

	/**
	 * Sends a redirect response and exits
	 *
	 * @param string $location
	 * @param bool $permanent
	 */
	public function doRedirectTo($location, $permanent = false) {
		$this->getResponseAPI()->redirectTo($location, $permanent);
		exit;
	}

	/**
	 * Compares the current scheme to the given, redirects if different
	 *
	 * @param string $scheme
	 */
	public function doEnforceScheme($scheme) {
		$scheme = strtolower($scheme);
		if (strtolower($this->getScheme()) != $scheme) {
			$this->doRedirectTo($this->getPageUri()->withScheme($scheme));
		}
	}

	/**
	 * Checks for HTTPS, redirects if not
	 */
	public function doEnforceHttps() {
		$this->doEnforceScheme('https');
	}

	// region static shortcuts

	/** @see getRequestAPI */
	public static function requestAPI() {
		return self::get()->getRequestAPI();
	}

	/** @see getResponseAPI */
	public static function responseAPI() {
		return self::get()->getResponseAPI();
	}

	/** @see getResourceAPI */
	public static function resourceAPI() {
		return self::get()->getResourceAPI();
	}

	/** @see getPagesAPI */
	public static function pagesAPI() {
		return self::get()->getPagesAPI();
	}

	/** @see getMetaAPI */
	public static function metaAPI() {
		return self::get()->getMetaAPI();
	}

	/** @see getUrl */
	public static function url() {
		return self::get()->getUrl();
	}

	/** @see getScheme */
	public static function scheme() {
		return self::get()->getScheme();
	}

	/** @see getVhost */
	public static function vhost() {
		return self::get()->getVhost();
	}

	/** @see getLanguage */
	public static function language() {
		return self::get()->getLanguage();
	}

	/** @see getLayout */
	public static function layout() {
		return self::get()->getLayout();
	}

	/** @see getPath */
	public static function path() {
		return self::get()->getPath();
	}

	/** @see getQuery */
	public static function query() {
		return self::get()->getQuery();
	}

	/** @see getLanguages */
	public static function languages() {
		return self::get()->getLanguages();
	}

	/**
	 * @see getMeta
	 * @param string $name
	 * @param string|null $default
	 * @param string|null $lang
	 * @param string|null $page
	 * @param string|null $layout
	 * @return null|string
	 */
	public static function meta($name, $default = null, $lang = null, $page = null, $layout = null) {
		return self::get()->getMeta($name, $default, $lang, $page, $layout);
	}

	/**
	 * @see getTranslation
	 * @param string $string
	 * @param string|null $lang
	 * @return null|string
	 */
	public static function translation($string, $lang = null) {
		return self::get()->getTranslation($string, $lang);
	}

	/**
	 * @see getPagePath
	 * @param string|null $page
	 * @param string|null $lang
	 * @return string
	 */
	public static function pagePath($page = null, $lang = null) {
		return self::get()->getPagePath($page, $lang);
	}

	/**
	 * @see getPageUri
	 * @param string|null $page
	 * @param string|null $lang
	 * @param mixed|null $query
	 * @param string|null $host
	 * @param string|null $scheme
	 * @param int|null $port
	 * @return UriInterface
	 */
	public static function pageUri($page = null, $lang = null, $query = null, $host = null, $scheme = null, $port = null) {
		return self::get()->getPageUri($page, $lang, $query, $host, $scheme, $port);
	}

	/**
	 * @see getStaticUrl
	 * @param string $path
	 * @return null|string
	 */
	public static function staticUrl($path) {
		return self::get()->getStaticUrl($path);
	}

	/**
	 * @see getStaticLayoutUrl
	 * @param string $path
	 * @return null|string
	 */
	public static function staticLayoutUrl($path) {
		return self::get()->getStaticLayoutUrl($path);
	}

	/** @see getContent */
	public static function content() {
		return self::get()->getContent();
	}

	/**
	 * @see doRedirectTo
	 * @param string $location
	 * @param bool $permanent
	 */
	public static function redirectTo($location, $permanent) {
		self::get()->doRedirectTo($location, $permanent);
	}

	/**
	 * @see getContent
	 * @param string $scheme
	 */
	public static function enforceScheme($scheme) {
		self::get()->doEnforceScheme($scheme);
	}

	/** @see getContent */
	public static function enforceHttps() {
		self::get()->doEnforceHttps();
	}

	// endregion

}