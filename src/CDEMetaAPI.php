<?php

namespace Ixolit\CDE;


use Ixolit\CDE\Exceptions\CDEFeatureNotSupportedException;
use Ixolit\CDE\Exceptions\MetadataNotAvailableException;
use Ixolit\CDE\Interfaces\MetaAPI;

/**
 * This API implements the meta API using the CDE API calls.
 *
 * @deprecated
 */
class CDEMetaAPI implements MetaAPI {

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