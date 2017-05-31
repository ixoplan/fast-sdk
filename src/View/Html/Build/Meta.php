<?php

namespace Ixolit\CDE\View\Html\Build;


use Ixolit\CDE\Exceptions\MetadataNotAvailableException;
use Ixolit\CDE\Interfaces\MetaAPI;
use Ixolit\CDE\View\Html\Element;
use Ixolit\CDE\View\Html\ElementEmpty;

/**
 * Meta builder
 *
 * Builds HTML element structures for meta data
 *
 * @package Ixolit\CDE\View\Html\Build
 */
class Meta {

	/** @var MetaAPI */
	private $metaAPI;

	/**
	 * @param MetaAPI $metaAPI
	 */
	public function __construct(MetaAPI $metaAPI) {
		$this->metaAPI = $metaAPI;
	}

	private function getElementMeta($attr, $name, $key = null) {
		if (!isset($key)) {
			$key = $name;
		}
		try {
			$meta = $this->metaAPI->getMeta($key);
			return (new ElementEmpty(Element::NAME_META))
				->setAttribute($attr, $name)
				->setAttribute(Element::ATTRIBUTE_NAME_CONTENT, $meta);
		}
		catch (MetadataNotAvailableException $e) {
			return null;
		}
	}

	/**
	 * @param string $name
	 * @param string|null $key
	 *
	 * @return Element|null
	 */
	public function getName($name, $key = null) {
		return $this->getElementMeta(Element::ATTRIBUTE_NAME_NAME, $name, $key);
	}

	/**
	 * @param string $name
	 * @param string|null $key
	 *
	 * @return Element|null
	 */
	public function getProperty($name, $key = null) {
		return $this->getElementMeta(Element::ATTRIBUTE_NAME_PROPERTY, $name, $key);
	}

	/**
	 * @param string $name
	 * @param string|null $key
	 *
	 * @return Element|null
	 */
	public function getHttpEquiv($name, $key = null) {
		return $this->getElementMeta(Element::ATTRIBUTE_NAME_HTTPEQUIV, $name, $key);
	}
}