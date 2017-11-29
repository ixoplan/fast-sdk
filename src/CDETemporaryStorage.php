<?php

namespace Ixolit\CDE;

/**
 * Class CDETemporaryStorage
 *
 * @package Ixolit\CDE
 */
class CDETemporaryStorage {

	/** @var CDETemporaryStorage[] */
//    private static $instances = [];

	/** @var array */
	private $dataStorage;

	/** @var string */
	private $dataStorageName;

	/** @var int */
	private $dataStorageTimeout;

	/** @var string */
	private $dataStoragePath;

	/** @var string */
	private $dataStorageDomain;

	/**
	 * @param string $dataStorageName
	 * @param int $dataStorageTimeout
	 * @param string $dataStoragePath
	 * @param string $dataStorageDomain
	 */
	protected function __construct($dataStorageName, $dataStorageTimeout, $dataStoragePath = null, $dataStorageDomain = null) {
		$this->dataStorageName = $dataStorageName;
		$this->dataStorageTimeout = $dataStorageTimeout;
		$this->dataStoragePath = $dataStoragePath;
		$this->dataStorageDomain = $dataStorageDomain;
		$this->dataStorage = $this->restoreDataStorage();
	}

	protected function __clone() {}

	/**
	 * @param string $dataStorageName
	 * @param int $dataStorageTimeout
	 *
	 * @return $this
	 */
	/*
	protected static function getInstanceInternal($dataStorageName, $dataStorageTimeout) {
		if (self::$instances[$dataStorageName] === null) {
			$class = \get_called_class();
			self::$instances[$dataStorageName] = new $class($dataStorageName, $dataStorageTimeout);
		}

		return self::$instances[$dataStorageName];
	}
	*/

	/**
	 * @return array
	 */
	protected function getDataStorage() {
		return $this->dataStorage;
	}

	/**
	 * @param string $dataKey
	 *
	 * @return $this
	 */
	protected function unsetDataStorageValue($dataKey) {
		unset($this->dataStorage[$dataKey]);

		return $this->storeDataStorage();
	}

	/**
	 * @param string $dataKey
	 * @param mixed $dataValue
	 *
	 * @return $this
	 */
	protected function setDataStorageValue($dataKey, $dataValue) {
		$this->dataStorage[$dataKey] = $dataValue;

		return $this->storeDataStorage();
	}

	/**
	 * @param string $dataKey
	 *
	 * @return mixed|null
	 */
	protected function getDataStorageValue($dataKey) {
		if (!isset($this->dataStorage[$dataKey])) {
			return null;
		}

		return $this->dataStorage[$dataKey];
	}

	/**
	 * @return $this
	 */
	protected function storeDataStorage() {
		$encodedDataStorage = \base64_encode(\json_encode($this->dataStorage));

		CDECookieCache::getInstance()->write(
			$this->dataStorageName,
			$encodedDataStorage,
			$this->dataStorageTimeout,
			$this->dataStoragePath,
			$this->dataStorageDomain
		);

		return $this;
	}

	/**
	 * @return array
	 */
	protected function restoreDataStorage() {
		$dataStorage = CDECookieCache::getInstance()->read($this->dataStorageName);

		if (empty($dataStorage)) {
			return [];
		}

		$dataStorage = \json_decode(\base64_decode($dataStorage), true);

		if (!\is_array($dataStorage)) {
			return [];
		}

		return $dataStorage;
	}
}