<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Context\Page;

/**
 * Class CDETemporaryDataStorage
 *
 * @package Ixolit\CDE
 */
class CDETemporaryDataStorage extends CDETemporaryStorage {

	const DATA_STORAGE_TIMEOUT_THIRTY_DAYS = CDECookieCache::COOKIE_TIMEOUT_THIRTY_DAYS;
	const COOKIE_NAME_TEMPORARY_DATA = 'temporary-data';

	/** @var CDETemporaryDataStorage */
	private static $instance;

    /**
     * @param int $dataStorageTimeout
     * @param null $dataStoragePath
     * @param null $dataStorageDomain
     *
     * @return CDETemporaryDataStorage
     */
	public static function getInstance($dataStorageTimeout = self::DATA_STORAGE_TIMEOUT_THIRTY_DAYS, $dataStoragePath = null, $dataStorageDomain = null) {
		if (self::$instance === null) {
			self::$instance = new self(
				self::COOKIE_NAME_TEMPORARY_DATA,
				$dataStorageTimeout,
				$dataStoragePath,
				$dataStorageDomain,
				Page::configValue(Page::APP_CFG_KEY_DATA_SECRET),
				Page::get()->getTemporaryStorageSecure(),
				Page::get()->getTemporaryStorageHttpOnly()
			);
		}

		return self::$instance;
	}

	/**
	 * @param string $dataKey
	 * @param mixed $dataValue
	 *
	 * @return $this
	 */
	public function write($dataKey, $dataValue) {
		return $this->setDataStorageValue($dataKey, $dataValue);
	}

	/**
	 * @param string $dataKey
	 *
	 * @return mixed|null
	 */
	public function read($dataKey) {
		return $this->getDataStorageValue($dataKey);
	}

	/**
	 * @param string $dataKey
	 *
	 * @return $this
	 */
	public function delete($dataKey) {
		return $this->unsetDataStorageValue($dataKey);
	}

	/**
	 * @param string $dataKey
	 *
	 * @return mixed|null
	 */
	public function consume($dataKey) {
		$dataValue = $this->read($dataKey);
		$this->delete($dataKey);

		return $dataValue;
	}
}