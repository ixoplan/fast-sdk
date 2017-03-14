<?php

namespace Ixolit\CDE;


use Ixolit\CDE\Exceptions\CookieNotSetException;
use Ixolit\CDE\Interfaces\RequestAPI;
use Ixolit\CDE\Interfaces\ResponseAPI;

/**
 * Class CDECookieCache
 *
 * @package Ixolit\CDE
 */
class CDECookieCache {

	const COOKIE_TIMEOUT_SESSION = 0;
	const COOKIE_TIMEOUT_THIRTY_DAYS = 2592000;

	/** @var CDECookieCache */
	private static $instance;

	/** @var RequestAPI */
	private $requestApi;

	/** @var ResponseAPI */
	private $responseApi;

	/** @var array */
	private $cookieCache;

	protected function __construct() {
		$this->requestApi = CDE::getRequestAPI();
		$this->responseApi = CDE::getResponseAPI();
		$this->cookieCache = [];
	}

	protected function __clone() {}

	/**
	 * @return CDECookieCache
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param string $cookieName
	 * @param string $value
	 * @param int    $cookieTimeout
	 * @param string $path
	 * @param string $domain
	 * @param bool   $secure
	 * @param bool   $httponly
	 *
	 * @return $this
	 */
	public function write($cookieName, $value, $cookieTimeout = self::COOKIE_TIMEOUT_THIRTY_DAYS, $path = null, $domain = null, $secure = false, $httponly = false) {
		return $this
			->setCookieValue($cookieName, $value)
			->storeCookieData($cookieName, $value, $cookieTimeout, $path, $domain, $secure, $httponly);
	}

	/**
	 * @param string $cookieName
	 *
	 * @return string|null
	 */
	public function read($cookieName) {
		$cookieValue = $this->getCookieValue($cookieName);

		if ($cookieValue === null) {
			$cookieValue = $this->restoreCookieData($cookieName);

			if ($cookieValue) {
				$this->setCookieValue($cookieName, $cookieValue);
			}
		}

		return $cookieValue;
	}

	/**
	 * @param string $cookieName
	 * @param int    $cookieTimeout
	 * @param string $path
	 * @param string $domain
	 * @param bool   $secure
	 * @param bool   $httponly
	 *
	 * @return $this
	 */
	public function renew($cookieName, $cookieTimeout = self::COOKIE_TIMEOUT_THIRTY_DAYS, $path = null, $domain = null, $secure = false, $httponly = false) {
		return $this->write($cookieName, $this->read($cookieName), $cookieTimeout, $path, $domain, $secure, $httponly);
	}

	/**
	 * @param string $cookieName
	 * @param string $path
	 * @param string $domain
	 *
	 * @return $this
	 */
	public function delete($cookieName, $path = null, $domain = null) {
		return $this->write($cookieName, null, -1, $path, $domain);
	}

	/**
	 * @param string $cookieName
	 *
	 * @return string
	 */
	public function consume($cookieName) {
		$cookieValue = $this->read($cookieName);

		$this->delete($cookieName);

		return $cookieValue;
	}

	/**
	 * @return RequestApi
	 */
	protected function getRequestApi() {
		return $this->requestApi;
	}

	/**
	 * @return ResponseAPI
	 */
	protected function getResponseApi() {
		return $this->responseApi;
	}

	/**
	 * @param string $cookieName
	 * @param string $cookieValue
	 *
	 * @return $this
	 */
	protected function setCookieValue($cookieName, $cookieValue) {
		$this->cookieCache[$cookieName] = $cookieValue;

		return $this;
	}

	/**
	 * @param string $cookieName
	 *
	 * @return string|null
	 */
	protected function getCookieValue($cookieName) {
		if (!isset($this->cookieCache[$cookieName])) {
			return null;
		}

		return $this->cookieCache[$cookieName];
	}

	/**
	 * @param string $cookieName
	 * @param string $cookieValue
	 * @param int    $cookieTimeout
	 * @param string $path
	 * @param string $domain
	 * @param bool   $secure
	 * @param bool   $httponly
	 *
	 * @return $this
	 */
	protected function storeCookieData($cookieName, $cookieValue, $cookieTimeout, $path = null, $domain = null, $secure = false, $httponly = false) {
		$this->getResponseApi()->setCookie($cookieName, $cookieValue, $cookieTimeout, $path, $domain, $secure, $httponly);

		return $this;
	}

	/**
	 * @param string $cookieName
	 *
	 * @return null|string
	 */
	protected function restoreCookieData($cookieName) {
		try {
			$cookieValue = $this->getRequestApi()->getCookie($cookieName)->getValue();
		} catch (CookieNotSetException $e) {
			$cookieValue = null;
		}

		return $cookieValue;
	}

}