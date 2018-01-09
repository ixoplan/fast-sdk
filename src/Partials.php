<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Exceptions\PartialNotFoundException;
use Ixolit\CDE\WorkingObjects\ViewModel;

/**
 * Helper class for accessing partials
 */
class Partials {

	private static $cache = [];

	/**
	 * Try to load a partial from the layout, or if it doesn't exist, from the vhost.
	 *
	 * @param string $name
	 * @param array  $data
	 *
	 * @throws PartialNotFoundException
	 */
	public static function load($name, $data = []) {

		$language = getCurrentLanguage();
		$cacheKey = $name . $language;

		$vhostsDir = defined('VHOSTS_DIR') ? VHOSTS_DIR : '/vhosts/';

		// build list of possible paths
		$tryFiles = [];
		if (!empty(self::$cache[$cacheKey])) {
			// previously resolved and cached
			$tryFiles = [self::$cache[$cacheKey]];
		} else {
			// add possible combinations of layout & language
			if (function_exists('getCurrentLayout') && \getCurrentLayout()) {
				$tryFiles[] = $vhostsDir . getEffectiveVhost() . '/layouts/' . getCurrentLayout() . '/partials/' . $name . '.' . $language . '.php';
				$tryFiles[] = $vhostsDir . getEffectiveVhost() . '/layouts/' . getCurrentLayout() . '/partials/' . $name . '.php';
			}
			$tryFiles[] = $vhostsDir . getEffectiveVhost() . '/partials/' . $name . '.' . $language . '.php';
			$tryFiles[] = $vhostsDir . getEffectiveVhost() . '/partials/' . $name . '.php';
		}

		// resolve, use first possible, cache
		foreach ($tryFiles as $tryFile) {
			if (\file_exists($tryFile)) {
				self::$cache[$cacheKey] = $tryFile;
				\extract($data);
				include($tryFile);
				return;
			}
		}

		throw new PartialNotFoundException($name);
	}
}
