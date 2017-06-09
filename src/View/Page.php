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
 * Static class providing utilities related to requests, pages, links, metadata, etc.
 *
 * @package Ixolit\CDE\View
 */
class Page {

	/** @var RequestAPI */
	private static $requestAPI;

	/** @var ResourceAPI */
	private static $resourceAPI;

	/** @var PagesAPI */
	private static $pagesAPI;

	/** @var MetaAPI */
	private static $metaAPI;

	/** @var string */
	private static $url;

	/** @var string */
	private static $scheme;

	/** @var string */
	private static $vhost;

	/** @var string */
	private static $language;

	/** @var Layout */
	private static $layout;

	/** @var string */
	private static $path;

	/** @var array */
	private static $query;

	/** @var string[] */
	private static $languages;

	private function __construct() {
	}

	private function __clone() {
	}

	/**
	 * @return RequestAPI
	 */
	public static function getRequestAPI() {

		if (!isset(self::$requestAPI)) {
			self::$requestAPI = CDE::getRequestAPI();
		}

		return self::$requestAPI;
	}

	/**
	 * @return ResourceAPI
	 */
	public static function getResourceAPI() {

		if (!isset(self::$resourceAPI)) {
			self::$resourceAPI = CDE::getResourceAPI();
		}

		return self::$resourceAPI;
	}

	/**
	 * @return PagesAPI
	 */
	public static function getPagesAPI() {

		if (!isset(self::$pagesAPI)) {
			self::$pagesAPI = CDE::getPagesAPI();
		}

		return self::$pagesAPI;
	}

	/**
	 * @return MetaAPI
	 */
	public static function getMetaAPI() {

		if (!isset(self::$metaAPI)) {
			self::$metaAPI = CDE::getMetaAPI();
		}

		return self::$metaAPI;
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
	private static function getValidLanguage($lang = null) {
		if (!empty($lang)) {
			foreach (self::getLanguages() as $item) {
				if (\strtolower($item) === \strtolower($lang)) {
					return $item;
				}
			}
		}
		return self::getLanguage();
	}

	/**
	 * Returns the request's url
	 *
	 * @return string
	 */
	public static function getUrl() {
		if (!isset(self::$url)) {
			self::$url = self::getRequestApi()->getPageLink();
		}
		return self::$url;
	}

	/**
	 * Returns the request's url scheme
	 *
	 * @return string
	 */
	public static function getScheme() {
		if (!isset(self::$scheme)) {
			self::$scheme = self::getRequestApi()->getScheme();
		}
		return self::$scheme;
	}

	/**
	 * Returns the request's virtual host name
	 *
	 * @return string
	 */
	public static function getVhost() {
		if (!isset(self::$vhost)) {
			self::$vhost = self::getRequestApi()->getVhost();
		}
		return self::$vhost;
	}

	/**
	 * Returns the request's language code
	 *
	 * @return string
	 */
	public static function getLanguage() {
		if (!isset(self::$language)) {
			self::$language = self::getRequestApi()->getLanguage();
		}
		return self::$language;
	}

	/**
	 * Returns the request's layout data
	 *
	 * @return Layout
	 */
	public static function getLayout() {
		if (!isset(self::$layout)) {
			self::$layout = self::getRequestApi()->getLayout();
		}
		return self::$layout;
	}

	/**
	 * Returns the request's path
	 *
	 * @return string
	 */
	public static function getPath() {
		if (!isset(self::$path)) {
			self::$path = self::getRequestApi()->getPagePath();
		}
		return self::$path;
	}

	/**
	 * @return array
	 */
	public static function getQuery() {
		if (!isset(self::$query)) {
			self::$query = self::getRequestAPI()->getRequestParameters();
		}
		return self::$query;
	}

	/**
	 * Returns the languages supported by the current host
	 *
	 * @return string[]
	 */
	public static function getLanguages() {
		if (!isset(self::$languages)) {
			self::$languages = self::getPagesAPI()->getLanguages();
		}
		return self::$languages;
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
	 * @return string|null
	 */
	public static function getMeta($name, $default = null, $lang = null, $page = null, $layout = null) {
		try {
			return self::getMetaAPI()->getMeta($name, $lang, $page, $layout);
		}
		catch (MetadataNotAvailableException $e) {
			return $default;
		}
	}

//	/**
//	 * @param $string
//	 * @param string|null $lang
//	 *
//	 * @return string|null
//	 */
//	public static function getTranslation($string, $lang = null) {
//		return self::getMeta('t-' . $string, $string, $lang);
//	}

	/**
	 * Returns the path for the given page and language, based on the current request
	 *
	 * @param null $page
	 * @param null $lang
	 *
	 * @return string
	 */
	public static function getPagePath($page = null, $lang = null) {
		return '/' . self::buildPath(
			self::getValidLanguage($lang),
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
	public static function getPageUri($page = null, $lang = null, $query = null, $host = null, $scheme = null, $port = null) {

		/** @var UriInterface $uri */
		$uri = self::parseUri(self::getUrl());

		$uri = $uri->withPath(self::getPagePath($page, $lang));

		$uri = $uri->withQuery(self::buildQuery($query === null ? self::getQuery() : $query));

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
	public static function getStaticUrl($path) {
		try {
			return self::getResourceAPI()->getStaticUrl($path);
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
	public static function getStaticLayoutUrl($path) {
		return self::getStaticUrl(self::buildPath(self::getLayout()->getName(), $path));
	}

	/**
	 * Returns the content of the current page
	 *
	 * @return string
	 */
	public static function getContent() {
		return self::getPagesAPI()->getContent();
	}

//	public static function enforceHttps() {
//		if (strtolower(self::getScheme()) != 'https') {
//			CDE::getResponseAPI()->redirectTo(self::getPageUri()->withScheme('https'));
//			exit;
//		}
//	}
}