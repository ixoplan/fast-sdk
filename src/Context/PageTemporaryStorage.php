<?php

namespace Ixolit\CDE\Context;

use Ixolit\CDE\CDECookieCache;
use Ixolit\CDE\CDETemporaryStorage;

/**
 * Class PageTemporaryStorage
 *
 * @package Ixolit\CDE\Context
 */
class PageTemporaryStorage extends CDETemporaryStorage {

	const COOKIE_NAME = 'temporary-page';

	const COOKIE_TIMEOUT = CDECookieCache::COOKIE_TIMEOUT_SESSION;

	public function __construct($dataStorageName, $dataStorageTimeout, $dataStoragePath = null, $dataStorageDomain = null, $dataStorageSecret = null, $dataStorageSecure = false, $dataStorageHttpOnly = false) {
		parent::__construct($dataStorageName, $dataStorageTimeout, $dataStoragePath, $dataStorageDomain, $dataStorageSecret, $dataStorageSecure, $dataStorageHttpOnly);
	}

//	/** @var self */
//	private static $instance;
//
//	/**
//	 * @return $this
//	 */
//	public static function getInstance() {
//		if (self::$instance === null) {
//			self::$instance = new self(self::COOKIE_NAME_PAGE_STORE, CDECookieCache::COOKIE_TIMEOUT_SESSION);
//		}
//		return self::$instance;
//	}

	/**
	 * @return array
	 */
	public function getVariables() {
		return $this->getDataStorage();
	}

	/**
	 * @param string $name
	 * @return mixed|null
	 */
	public function getVariable($name) {
		return $this->getDataStorageValue($name);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setVariable($name, $value) {
		$this->setDataStorageValue($name, $value);
		return $this;
	}
}