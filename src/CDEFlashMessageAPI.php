<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Exceptions\MetadataNotAvailableException;
use Ixolit\CDE\WorkingObjects\FlashMessage;

/**
 * Class CDEFlashMessageAPI
 *
 * @package Ixolit\CDE
 */
class CDEFlashMessageAPI extends CDETemporaryStorage {

	const COOKIE_NAME_MESSAGE_STORE = 'temporary-messages';

	/** @var CDEFlashMessageAPI */
	private static $instance;

	/**
	 * @return $this
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self(self::COOKIE_NAME_MESSAGE_STORE, CDECookieCache::COOKIE_TIMEOUT_SESSION);
		}
		return self::$instance;
	}

	/**
	 * Find messages by pattern
	 *
	 * @param string|null $pattern Empty to get all messages, regular expression without delimiters, put between /^...$/ automatically
	 *
	 * @return FlashMessage[]
	 */
	public function getMessages($pattern = null) {
		$result = [];
		foreach ($this->getDataStorage() as $key => $value) {
			if (empty($pattern) || preg_match('/^'.$pattern.'$/', $key)) {
				$result[$key] = $this->getMessage($key);
			}
		}
		return $result;
	}

	/**
	 * Find a message by name
	 *
	 * @param string $name
	 *
	 * @return FlashMessage|null
	 */
	public function getMessage($name) {
		$data = $this->getDataStorageValue($name);
		$this->unsetDataStorageValue($name);
		if (is_array($data)) {
			$msg = new FlashMessage();
			$msg->fromArray($data);
			return $msg;
		}
		return null;
	}

	/**
	 * Add a message with given name, type and text
	 *
	 * @param string $name
	 * @param string $type
	 * @param string $text
	 *
	 * @return $this
	 */
	public function setMessage($name, $type = FlashMessage::TYPE_INFO, $text = null) {
		$msg = new FlashMessage();
		$msg->setText($text);
		$msg->setType($type);
		return $this->setDataStorageValue($name, $msg->toArray());
	}

	/**
	 * Return messages data, mainly for rendering, filtered by name, merged with optionally passed data
	 *
	 * @param mixed $filter Message names to search for and optional data to merge.
	 * A string, an array of strings or an associative array of strings or arrays, following this conventions:
	 * 'name' to get messages with matching name,
	 * ['name-one', 'name-two', …] to match multiple names,
	 * ['name' => 'my text', …] to match name and override message text,
	 * ['name' => ['text' => 'my text', 'custom' => [], …], …] to match name, override text and set additional data
	 *
	 * @return array[]|null
	 */
	public function getMessageData($filter) {
		$result = [];

		if (is_string($filter))
			$filter = [$filter];

		foreach ($filter as $key => $value) {

			$init = [];

			// evaluate filter item
			if (is_string($key)) {
				$pattern = $key;
				if (is_array($value))
					$init = $value;
				elseif (is_string($value))
					$init = ['text' => $value];
				else
					throw new \InvalidArgumentException();
			}
			elseif (is_string($value)) {
				$pattern = $value;
			}
			else
				throw new \InvalidArgumentException();

			// find messages, merge data
			foreach ($this->getMessages($pattern) as $name => $msg) {

				$data = $init;
				$data['type'] = $msg->getType();

				// precedence: arguments ($filter), translation ($meta), default ($msg)
				if (!isset($data['text'])) {
					try {
						$data['text'] = CDE::getPagesAPI()->getMetadata('t-msg-' . $name);
					}
					catch (MetadataNotAvailableException $e) {
						$text = $msg->getText();
						if (isset($text)) {
							$data['text'] = $text;
						}
						else {
							$data['text'] = $name;
						}
					}
				}

				$result[$name] = $data;
			}
		}
		return $result;
	}

	/**
	 * Render messages defined by filter, using given partial and optional data
	 *
	 * @param mixed $filter
	 * @param string $partial
	 * @param array $data
	 *
	 * @return int
	 */
	public function render($filter, $partial, $data = []) {
		$messageData = $this->getMessageData($filter);
		foreach ($messageData as $msg) {
			Partials::load($partial, array_replace_recursive($data, $msg));
		}

		return count($messageData);
	}
}