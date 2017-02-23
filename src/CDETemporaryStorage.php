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

    /**
     * @param string $dataStorageName
     * @param int $dataStorageTimeout
     */
    protected function __construct($dataStorageName, $dataStorageTimeout) {
        $this->dataStorageName = $dataStorageName;
        $this->dataStorageTimeout = $dataStorageTimeout;
        $this->dataStorage = $this->restoreDataStorage();
    }

    protected function __clone() {}

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
            $this->dataStorageTimeout
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