<?php

namespace Ixolit\CDE;

/**
 * Class CDETemporaryStorage
 *
 * @package Ixolit\CDE
 */
class CDETemporaryStorage {

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

	/** @var string */
	private $dataStorageSecret;

	/**
	 * @param string $dataStorageName
	 * @param int $dataStorageTimeout
	 * @param string $dataStoragePath
	 * @param string $dataStorageDomain
	 * @param string $dataStorageSecret leave empty to omit cookie signing
	 */
	protected function __construct($dataStorageName, $dataStorageTimeout, $dataStoragePath = null, $dataStorageDomain = null, $dataStorageSecret = null) {
		$this->dataStorageName = $dataStorageName;
		$this->dataStorageTimeout = $dataStorageTimeout;
		$this->dataStoragePath = $dataStoragePath;
		$this->dataStorageDomain = $dataStorageDomain;
		$this->dataStorageSecret = $dataStorageSecret;
		$this->dataStorage = $this->restoreDataStorage();
	}

	protected function __clone() {}

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
	 * @param $data
	 * @param $secret
	 * @return string
	 */
	protected function getSignature($data, $secret) {
		return \base64_encode(\hash_hmac('sha256', $data, $secret, true));
	}

	/**
	 * @return $this
	 */
	protected function storeDataStorage() {
		$encodedDataStorage = \base64_encode(\json_encode($this->dataStorage));

		if (!empty($this->dataStorageSecret)) {
			$encodedDataStorage .= '.' . $this->getSignature($encodedDataStorage, $this->dataStorageSecret);
		}

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

		if (!empty($this->dataStorageSecret)) {
			list($dataStorage, $signature) = explode('.', $dataStorage);
			if ($this->getSignature($dataStorage, $this->dataStorageSecret) !== $signature) {
				return [];
			}
		}

		$dataStorage = \json_decode(\base64_decode($dataStorage), true);

		if (!\is_array($dataStorage)) {
			return [];
		}

		return $dataStorage;
	}
}