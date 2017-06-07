<?php

namespace Ixolit\CDE\View;


use Ixolit\CDE\CDE;
use Ixolit\CDE\Exceptions\InvalidValueException;
use Ixolit\CDE\Exceptions\PageNotFoundException;
use Ixolit\CDE\Exceptions\ResourceNotFoundException;
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
	 * Returns an URI's parts as associative array
	 *
	 * @param string $uri
	 *
	 * @return array
	 */
	private function parseUri($uri) {
		$result = [];
		if (\preg_match('~^(?:(.*?):)(?://(?:(.*?)(?:\:(.*?))?@)?(.*?)(?:\:(\d+))?(?=[/?#]|$))?((?:.*?)?)(?:\?(.*?))?(?:\#(.*?))?$~', $uri, $matches)) {
			foreach	([1 => 'scheme', 2 => 'user', 3 => 'pass', 4 => 'host', 5 => 'port', 6 => 'path', 7 => 'query', 8 => 'fragment'] as $index => $key) {
				if (!empty($matches[$index])) {
					$result[$key] = $matches[$index];
				}
			}
		}
		return $result;
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
	private function parseUri2($uri) {
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
	 * Build a URI string from it's parts
	 *
	 * @param array $uri
	 *
	 * @return string
	 */
	private function buildUri($uri) {
		$result = '';
		if (!empty($uri['host'])) {
			if (!empty($uri['scheme'])) {
				$result .= $uri['scheme'] . ':';
			}
			$result .= '//' . $uri['host'];
			if (isset($uri['port']) && is_numeric($uri['port'])) {
				$result .= ':' . $uri['port'];
			}
		}
		if (!empty($uri['path'])) {
			$result .= $uri['path'];
		}
		if (!empty($uri['query'])) {
			$result .= '?' . $uri['query'];
		}
		if (!empty($uri['fragment'])) {
			$result .= '#' . $uri['fragment'];
		}
		return $result;
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
	 * Returns the URL for the given path and optionally language, query, host and scheme, based on the request's url
	 *
	 * @param string|null $path
	 * @param string|null $lang
	 * @param mixed|null $query
	 * @param string|null $host
	 * @param string|null $scheme
	 * @param int|null $port
	 *
	 * @return string
	 */
	// TODO: cleanup!
	public function getPageUrl3($path = null, $lang = null, $query = null, $host = null, $scheme = null, $port = null) {

		$uri = $this->parseUri($this->getUrl());

		$uri['path'] = '/' . $this->buildPath(
			$this->getValidLanguage($lang),
			$path === null ? $path = $this->getPath() : $path
		);

		$uri['query'] = $this->buildQuery($query === null ? $this->getQuery() : $query);

		if ($host !== null) {
			$uri['host'] = $host;
		}

		if ($scheme !== null) {
			$uri['scheme'] = $scheme;
		}

		if ($port !== null) {
			$uri['port'] = $port;
		}

		return $this->buildUri($uri);
	}

	// TODO: cleanup!
	/**
	 * Returns the URL for the given path, language, query, host and scheme, based on the current request
	 *
	 * @param string|null $path
	 * @param string|null $lang
	 * @param mixed|null $query
	 * @param string|null $host
	 * @param string|null $scheme
	 * @param int|null $port
	 *
	 * @return UriInterface
	 */
	public function getPageUrl4($path = null, $lang = null, $query = null, $host = null, $scheme = null, $port = null) {

		/** @var UriInterface $uri */
		$uri = $this->parseUri2($this->getUrl());

		$uri = $uri->withPath($this->getPagePath($path, $lang));

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
	 * Returns the string for the given path and language, based on the current request
	 *
	 * @param null $path
	 * @param null $lang
	 *
	 * @return string
	 */
	public function getPagePath($path = null, $lang = null) {
		return '/' . $this->buildPath(
			$this->getValidLanguage($lang),
			$path === null ? $this->getPath() : $path
		);
	}

	// TODO: cleanup!
	public function getPageTest($path, $lang = null, $query = null) {
		return $this->buildUri([
			'path' => '/' . $this->buildPath(
				$this->getValidLanguage($lang),
				$path
			),
			'query' => $this->buildQuery($query),
		]);
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