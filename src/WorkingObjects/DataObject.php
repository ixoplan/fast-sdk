<?php

namespace Ixolit\CDE\WorkingObjects;

/**
 * Class DataObject
 *
 * @package Ixolit\CDE\WorkingObjects
 */
class DataObject {

	// TODO: nested objects

	private $data = [];

	private function getData() {
		return $this->data;
	}

	private function setData($data) {
		$this->data = $data;
		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	protected function get($name) {
		if (isset($this->data[$name]))
			return $this->data[$name];
		return null;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return $this
	 */
	protected function set($name, $value) {
		$this->data[$name] = $value;
		return $this;
	}

	public function __get($name) {
		return $this->get($name);
	}

	public function __set($name, $value) {
		$this->set($name, $value);
	}

	/**
	 * @param $data
	 *
	 * @return $this
	 */
	public static function createFromArray($data) {
		/** @var DataObject $result */
		$class = \get_called_class();
		$result = new $class();
		$result->fromArray($data);
		return $result;
	}

	/**
	 * @param $data
	 *
	 * @return $this
	 */
	public function fromArray($data) {
		return $this->setData($data);
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return $this->getData();
	}
}