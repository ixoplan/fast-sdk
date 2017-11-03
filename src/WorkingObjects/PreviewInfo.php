<?php

namespace Ixolit\CDE\WorkingObjects;

class PreviewInfo {

	/** @var string */
	private $type;

	/** @var int */
	private $timestamp;

	/** @var string */
	private $leaveUrl;

	/**
	 * @param string $type
	 * @param int $timestamp
	 * @param string $leaveUrl
	 */
	public function __construct($type, $timestamp, $leaveUrl) {
		$this->type = $type;
		$this->timestamp = $timestamp;
		$this->leaveUrl = $leaveUrl;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return int
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * @return string
	 */
	public function getLeaveUrl() {
		return $this->leaveUrl;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateTime() {
		$date = new \DateTime();
		$date->setTimestamp($this->getTimestamp() / 1000);
		return $date;
	}
}