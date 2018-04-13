<?php

namespace Ixolit\CDE\Api;


use Ixolit\CDE\Exceptions\CDEFeatureNotSupportedException;
use Ixolit\CDE\Exceptions\MetadataNotAvailableException;
use Ixolit\CDE\Interfaces\MetaAPI;


/**
 * Class CDEMetaAPI
 *
 * @package Ixolit\CDE\Api
 */
class CDEMetaAPI implements MetaAPI {

    /**
     * @param string      $name
     * @param string|null $language
     * @param string|null $pagePath
     * @param string|null $layout
     *
     * @return array|string
     *
     * @throws CDEFeatureNotSupportedException
     * @throws MetadataNotAvailableException
     */
    private function getMetaInternal($name, $language = null, $pagePath = null, $layout = null) {
        if (!\function_exists('getMeta')) {
            throw new CDEFeatureNotSupportedException('getMeta');
        }
        $data = \getMeta($name, $language, $pagePath, $layout);
        if ($data === null) {
            throw new MetadataNotAvailableException($name);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllMeta($language = null, $pagePath = null, $layout = null) {
        return $this->getMetaInternal(null, $language, $pagePath, $layout);
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta($name, $language = null, $pagePath = null, $layout = null) {
        if (empty($name)) {
            throw new MetadataNotAvailableException($name); // TODO: InvalidArgumentException ?
        }
        return $this->getMetaInternal($name, $language, $pagePath, $layout);
    }

    /**
     * {@inheritdoc}
     */
    public function setMeta($name, $value, $language = null) {
        if (!\function_exists('setMeta')) {
            throw new CDEFeatureNotSupportedException('getMeta');
        }
        if (!setMeta($name, $value, $language)) {
            throw new MetadataNotAvailableException($name); // TODO: RuntimeException ?
        }
    }

}