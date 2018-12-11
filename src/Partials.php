<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Exceptions\PartialNotFoundException;

/**
 * Helper class for accessing partials
 */
class Partials {

	private static $cache = [];

	/**
	 * Try to load a partial from the layout, or if it doesn't exist, from the vhost.
	 *
	 * @param string $name e.g. partial-name or dir/partial-name or dir/dir/partial-name
     *      where effectiveVhost/layouts/currentLayout/partials/partial-name[.currentLanguage].php is default
     *      and fallsback to effectiveVhost/partials/partial-name[.currentLanguage].php
	 * @param array  $data
     * @param int $fallbackDirLevels
     *      when > 0 and $name includes subdirectories this parameter defines
     *      how many directory levels shall be resolved each time the partial couln't be loaded within the current dir
	 *
	 * @throws PartialNotFoundException
	 */
	public static function load($name, $data = [], $fallbackDirLevels=0) {

        $name = ltrim($name, '/');
        $names = [$name];
        if (strpos($name, '/') !== 0 and $fallbackDirLevels > 0) {
            $parts = explode('/', $name);
            $basename = array_pop($parts);

            while(count($parts) >= 1 and $fallbackDirLevels > 0) {
                array_pop($parts);
                $fallbackDirLevels--;
                $names[] = implode('/', $parts) . '/' . $basename;
            }
        }

		$language = getCurrentLanguage();
		$cacheKey = implode('-', $names) . $language;

		$vhostsDir = defined('VHOSTS_DIR') ? VHOSTS_DIR : '/vhosts/';

		// build list of possible paths
		$tryFiles = [];
		if (!empty(self::$cache[$cacheKey])) {
			// previously resolved and cached
			$tryFiles = [self::$cache[$cacheKey]];
		} else {
			// add possible combinations of layout & language
			foreach($names as $name) {
                if (function_exists('getCurrentLayout') && \getCurrentLayout()) {
                    $tryFiles[] = $vhostsDir . getEffectiveVhost() . '/layouts/' . getCurrentLayout() . '/partials/' . $name . '.' . $language . '.php';
                    $tryFiles[] = $vhostsDir . getEffectiveVhost() . '/layouts/' . getCurrentLayout() . '/partials/' . $name . '.php';
                }
                $tryFiles[] = $vhostsDir . getEffectiveVhost() . '/partials/' . $name . '.' . $language . '.php';
                $tryFiles[] = $vhostsDir . getEffectiveVhost() . '/partials/' . $name . '.php';
            }
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
