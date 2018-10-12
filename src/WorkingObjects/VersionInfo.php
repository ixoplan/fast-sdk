<?php

namespace Ixolit\CDE\WorkingObjects;


class VersionInfo {

	/** @var int */
	private $major;

	/** @var int */
	private $minor;

	/** @var string */
	private $tag;

	/** @var string */
	private $version;

	/**
	 * @param int $major
	 * @param int $minor
	 * @param string $tag
	 * @param string $version
	 */
	public function __construct($major, $minor, $tag, $version) {
		$this->major = $major;
		$this->minor = $minor;
		$this->tag = $tag;
		$this->version = $version;
	}

	/**
	 * @return int
	 */
	public function getMajor() {
		return $this->major;
	}

	/**
	 * @return int
	 */
	public function getMinor() {
		return $this->minor;
	}

	/**
	 * @return string
	 */
	public function getTag() {
		return $this->tag;
	}

	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}
}